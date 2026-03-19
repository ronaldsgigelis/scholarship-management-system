<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\GradeCalculator;
use App\Services\StipendCalculator;
use Config\Database;
use PDO;

class StipendEntry
{
    private PDO $connection;
    private GradeCalculator $gradeCalculator;
    private StipendCalculator $stipendCalculator;

    public function __construct()
    {
        $this->connection = (new Database())->connect();
        $this->gradeCalculator = new GradeCalculator();
        $this->stipendCalculator = new StipendCalculator();
    }

    public function getPeriods(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, `year`, period, period_group
             FROM `stipend_periods`
             ORDER BY `year` DESC, period ASC, period_group ASC, id DESC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getGroups(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, group_name
             FROM `groups`
             ORDER BY group_name ASC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getPeriodById(int $periodId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, `year`, period, period_group
             FROM `stipend_periods`
             WHERE id = :period_id
             LIMIT 1'
        );
        $statement->bindValue(':period_id', $periodId, PDO::PARAM_INT);
        $statement->execute();

        $period = $statement->fetch();

        return $period !== false ? $period : null;
    }

    public function getGroupById(int $groupId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, group_name
             FROM `groups`
             WHERE id = :group_id
             LIMIT 1'
        );
        $statement->bindValue(':group_id', $groupId, PDO::PARAM_INT);
        $statement->execute();

        $group = $statement->fetch();

        return $group !== false ? $group : null;
    }

    public function getStudentsByGroupId(int $groupId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, first_name, last_name, personal_code
             FROM `students`
             WHERE group_id = :group_id
             ORDER BY first_name ASC, last_name ASC'
        );
        $statement->bindValue(':group_id', $groupId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getSubjectsByGroupId(int $groupId): array
    {
        $statement = $this->connection->prepare(
            'SELECT s.id, s.subject_name, s.category_type
             FROM `group_subjects` gs
             INNER JOIN `subjects` s ON s.id = gs.subject_id
             WHERE gs.group_id = :group_id
             ORDER BY s.subject_name ASC'
        );
        $statement->bindValue(':group_id', $groupId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getSavedEntriesByGroupAndPeriod(int $groupId, int $periodId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, student_id, absences, activity_bonus, average_grade, failed_subjects_count, base_stipend, total_stipend
             FROM `student_stipend_records`
             WHERE group_id = :group_id AND period_id = :period_id'
        );
        $statement->bindValue(':group_id', $groupId, PDO::PARAM_INT);
        $statement->bindValue(':period_id', $periodId, PDO::PARAM_INT);
        $statement->execute();

        $records = $statement->fetchAll();
        $recordsByStudent = [];
        $recordIds = [];

        foreach ($records as $record) {
            $studentId = (int) $record['student_id'];
            $recordsByStudent[$studentId] = [
                'stipend_record_id' => (int) $record['id'],
                'absence_count' => $record['absences'],
                'activity_bonus' => $record['activity_bonus'],
                'average_grade' => $record['average_grade'],
                'failed_subjects_count' => $record['failed_subjects_count'],
                'base_stipend' => $record['base_stipend'],
                'total_stipend' => $record['total_stipend'],
                'has_grades' => false,
                'grades' => [],
            ];
            $recordIds[] = (int) $record['id'];
        }

        if ($recordIds === []) {
            return $recordsByStudent;
        }

        $placeholders = implode(',', array_fill(0, count($recordIds), '?'));
        $gradeStatement = $this->connection->prepare(
            "SELECT sg.stipend_record_id, sr.student_id, sg.subject_id, sg.grade
             FROM `student_grades` sg
             INNER JOIN `student_stipend_records` sr ON sr.id = sg.stipend_record_id
             WHERE sg.stipend_record_id IN ($placeholders)"
        );

        foreach ($recordIds as $index => $recordId) {
            $gradeStatement->bindValue($index + 1, $recordId, PDO::PARAM_INT);
        }

        $gradeStatement->execute();
        $grades = $gradeStatement->fetchAll();

        foreach ($grades as $grade) {
            $studentId = (int) $grade['student_id'];
            $subjectId = (int) $grade['subject_id'];
            $recordsByStudent[$studentId]['grades'][$subjectId] = $grade['grade'];
            $recordsByStudent[$studentId]['has_grades'] = true;
        }

        return $recordsByStudent;
    }

    public function saveEntries(int $periodId, int $groupId, array $entries, array $period): void
    {
        $this->connection->beginTransaction();

        try {
            $subjects = $this->getSubjectsByGroupId($groupId);

            foreach ($entries as $studentId => $entry) {
                $studentId = (int) $studentId;
                $absenceCount = (int) $entry['absence_count'];
                $activityBonus = (float) $entry['activity_bonus'];
                $grades = is_array($entry['grades'] ?? null) ? $entry['grades'] : [];
                $hasGrades = $this->gradeCalculator->hasEnteredGrades($grades);
                $averageGrade = $this->gradeCalculator->calculateAverageGrade($grades);
                $failedSubjectsCount = $hasGrades
                    ? $this->gradeCalculator->countFailedSubjects($grades, $subjects)
                    : 0;
                $baseStipend = $hasGrades
                    ? $this->stipendCalculator->calculateBaseStipend(
                        $averageGrade,
                        $failedSubjectsCount,
                        $absenceCount
                    )
                    : 0.00;
                $totalStipend = $hasGrades
                    ? $this->stipendCalculator->calculateTotalStipend($baseStipend, $activityBonus)
                    : 0.00;

                $stipendRecordId = $this->upsertStipendRecord(
                    $studentId,
                    $groupId,
                    $periodId,
                    $absenceCount,
                    $activityBonus,
                    $averageGrade ?? 0.00,
                    $failedSubjectsCount,
                    $baseStipend,
                    $totalStipend
                );

                $this->replaceGrades($stipendRecordId, $grades);
                $this->replaceActivityBonusRecord($stipendRecordId, $activityBonus, $period);
            }

            $this->connection->commit();
        } catch (\Throwable $throwable) {
            $this->connection->rollBack();
            throw $throwable;
        }
    }

    private function upsertStipendRecord(
        int $studentId,
        int $groupId,
        int $periodId,
        int $absenceCount,
        float $activityBonus,
        float $averageGrade,
        int $failedSubjectsCount,
        float $baseStipend,
        float $totalStipend
    ): int {
        $selectStatement = $this->connection->prepare(
            'SELECT id
             FROM `student_stipend_records`
             WHERE student_id = :student_id AND period_id = :period_id
             LIMIT 1'
        );
        $selectStatement->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $selectStatement->bindValue(':period_id', $periodId, PDO::PARAM_INT);
        $selectStatement->execute();

        $existingId = $selectStatement->fetchColumn();

        if ($existingId !== false) {
            $updateStatement = $this->connection->prepare(
                'UPDATE `student_stipend_records`
                 SET group_id = :group_id,
                     absences = :absences,
                     activity_bonus = :activity_bonus,
                     average_grade = :average_grade,
                     failed_subjects_count = :failed_subjects_count,
                     base_stipend = :base_stipend,
                     total_stipend = :total_stipend
                 WHERE id = :id'
            );
            $updateStatement->execute([
                ':group_id' => $groupId,
                ':absences' => $absenceCount,
                ':activity_bonus' => $activityBonus,
                ':average_grade' => $averageGrade,
                ':failed_subjects_count' => $failedSubjectsCount,
                ':base_stipend' => $baseStipend,
                ':total_stipend' => $totalStipend,
                ':id' => (int) $existingId,
            ]);

            return (int) $existingId;
        }

        $insertStatement = $this->connection->prepare(
            'INSERT INTO `student_stipend_records`
             (student_id, group_id, period_id, average_grade, failed_subjects_count, absences, base_stipend, activity_bonus, total_stipend)
             VALUES
             (:student_id, :group_id, :period_id, :average_grade, :failed_subjects_count, :absences, :base_stipend, :activity_bonus, :total_stipend)'
        );
        $insertStatement->execute([
            ':student_id' => $studentId,
            ':group_id' => $groupId,
            ':period_id' => $periodId,
            ':average_grade' => $averageGrade,
            ':failed_subjects_count' => $failedSubjectsCount,
            ':absences' => $absenceCount,
            ':base_stipend' => $baseStipend,
            ':activity_bonus' => $activityBonus,
            ':total_stipend' => $totalStipend,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function replaceGrades(int $stipendRecordId, array $grades): void
    {
        $deleteStatement = $this->connection->prepare(
            'DELETE FROM `student_grades` WHERE stipend_record_id = :stipend_record_id'
        );
        $deleteStatement->bindValue(':stipend_record_id', $stipendRecordId, PDO::PARAM_INT);
        $deleteStatement->execute();

        $insertStatement = $this->connection->prepare(
            'INSERT INTO `student_grades` (stipend_record_id, subject_id, grade)
             VALUES (:stipend_record_id, :subject_id, :grade)'
        );

        foreach ($grades as $subjectId => $grade) {
            if ($grade === '' || $grade === null) {
                continue;
            }

            $insertStatement->execute([
                ':stipend_record_id' => $stipendRecordId,
                ':subject_id' => (int) $subjectId,
                ':grade' => (float) $grade,
            ]);
        }
    }

    private function replaceActivityBonusRecord(int $stipendRecordId, float $activityBonus, array $period): void
    {
        $deleteStatement = $this->connection->prepare(
            'DELETE FROM `activity_bonus_records` WHERE stipend_record_id = :stipend_record_id'
        );
        $deleteStatement->bindValue(':stipend_record_id', $stipendRecordId, PDO::PARAM_INT);
        $deleteStatement->execute();

        [$periodStart, $periodEnd] = $this->buildPeriodDateRange(
            (string) $period['period'],
            (int) $period['year']
        );

        $insertStatement = $this->connection->prepare(
            'INSERT INTO `activity_bonus_records`
             (stipend_record_id, activity_description, bonus_amount, period_start, period_end)
             VALUES (:stipend_record_id, :activity_description, :bonus_amount, :period_start, :period_end)'
        );
        $insertStatement->execute([
            ':stipend_record_id' => $stipendRecordId,
            ':activity_description' => 'Manual activity bonus',
            ':bonus_amount' => $activityBonus,
            ':period_start' => $periodStart,
            ':period_end' => $periodEnd,
        ]);
    }

    private function buildPeriodDateRange(string $periodName, int $year): array
    {
        $monthValue = date_parse($periodName)['month'] ?? false;

        if (! is_int($monthValue) || $monthValue < 1 || $monthValue > 12) {
            $monthValue = 1;
        }

        $periodStart = sprintf('%04d-%02d-01', $year, $monthValue);
        $periodEnd = date('Y-m-t', strtotime($periodStart . ' +5 months'));

        return [$periodStart, $periodEnd];
    }
}
