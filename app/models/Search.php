<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class Search
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = (new Database())->connect();
    }

    public function getYears(): array
    {
        $statement = $this->connection->prepare(
            'SELECT DISTINCT `year`
             FROM `stipend_periods`
             ORDER BY `year` DESC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getPeriods(): array
    {
        $statement = $this->connection->prepare(
            'SELECT DISTINCT period
             FROM `stipend_periods`
             ORDER BY period ASC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getPeriodGroups(): array
    {
        $statement = $this->connection->prepare(
            'SELECT DISTINCT period_group
             FROM `stipend_periods`
             ORDER BY period_group ASC'
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

    public function getStudents(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, first_name, last_name, personal_code
             FROM `students`
             ORDER BY first_name ASC, last_name ASC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function searchResults(?int $year, ?string $period, ?string $periodGroup, ?int $groupId, ?int $studentId, ?string $studentQuery): array
    {
        $sql = 'SELECT
                    sr.id,
                    sp.`year`,
                    sp.period,
                    sp.period_group,
                    g.group_name,
                    s.first_name,
                    s.last_name,
                    s.personal_code,
                    sr.average_grade,
                    sr.failed_subjects_count,
                    sr.absences,
                    sr.activity_bonus,
                    sr.base_stipend,
                    sr.total_stipend,
                    sr.created_at,
                    COUNT(sg.id) AS grade_count
                FROM `student_stipend_records` sr
                INNER JOIN `stipend_periods` sp ON sp.id = sr.period_id
                INNER JOIN `groups` g ON g.id = sr.group_id
                INNER JOIN `students` s ON s.id = sr.student_id
                LEFT JOIN `student_grades` sg ON sg.stipend_record_id = sr.id';

        $conditions = [];
        $parameters = [];

        if ($year !== null) {
            $conditions[] = 'sp.`year` = :year';
            $parameters[':year'] = $year;
        }

        if ($period !== null && $period !== '') {
            $conditions[] = 'sp.period = :period';
            $parameters[':period'] = $period;
        }

        if ($periodGroup !== null && $periodGroup !== '') {
            $conditions[] = 'sp.period_group = :period_group';
            $parameters[':period_group'] = $periodGroup;
        }

        if ($groupId !== null) {
            $conditions[] = 'sr.group_id = :group_id';
            $parameters[':group_id'] = $groupId;
        }

        if ($studentId !== null) {
            $conditions[] = 'sr.student_id = :student_id';
            $parameters[':student_id'] = $studentId;
        }

        if ($studentQuery !== null && $studentQuery !== '') {
            $conditions[] = '(
                s.first_name LIKE :student_query
                OR s.last_name LIKE :student_query
                OR CONCAT(s.first_name, \' \', s.last_name) LIKE :student_query
            )';
            $parameters[':student_query'] = '%' . $studentQuery . '%';
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY
            sr.id,
            sp.`year`,
            sp.period,
            sp.period_group,
            g.group_name,
            s.first_name,
            s.last_name,
            s.personal_code,
            sr.average_grade,
            sr.failed_subjects_count,
            sr.absences,
            sr.activity_bonus,
            sr.base_stipend,
            sr.total_stipend,
            sr.created_at';

        $sql .= ' ORDER BY sp.`year` DESC, sp.period ASC, g.group_name ASC, s.first_name ASC, s.last_name ASC';

        $statement = $this->connection->prepare($sql);

        foreach ($parameters as $name => $value) {
            if ($name === ':student_query' || $name === ':period' || $name === ':period_group') {
                $statement->bindValue($name, $value, PDO::PARAM_STR);
                continue;
            }

            $statement->bindValue($name, $value, PDO::PARAM_INT);
        }

        $statement->execute();

        return $statement->fetchAll();
    }
}
