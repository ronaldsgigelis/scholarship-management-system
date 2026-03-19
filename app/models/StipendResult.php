<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class StipendResult
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = (new Database())->connect();
    }

    public function findById(int $recordId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT
                sr.id,
                sp.`year`,
                sp.period,
                sp.period_group,
                g.group_name,
                s.first_name,
                s.last_name,
                s.personal_code,
                sr.absences,
                sr.activity_bonus,
                sr.average_grade,
                sr.failed_subjects_count,
                sr.base_stipend,
                sr.total_stipend,
                sr.created_at,
                (
                    SELECT COUNT(*)
                    FROM `student_grades` sg
                    WHERE sg.stipend_record_id = sr.id
                ) AS grade_count
             FROM `student_stipend_records` sr
             INNER JOIN `stipend_periods` sp ON sp.id = sr.period_id
             INNER JOIN `groups` g ON g.id = sr.group_id
             INNER JOIN `students` s ON s.id = sr.student_id
             WHERE sr.id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $recordId, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetch();

        return $result !== false ? $result : null;
    }

    public function getGradesByRecordId(int $recordId): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                sub.subject_name,
                sub.category_type,
                sg.grade
             FROM `student_grades` sg
             INNER JOIN `subjects` sub ON sub.id = sg.subject_id
             WHERE sg.stipend_record_id = :record_id
             ORDER BY sub.subject_name ASC'
        );
        $statement->bindValue(':record_id', $recordId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
