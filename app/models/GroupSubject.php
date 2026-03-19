<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class GroupSubject
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = (new Database())->connect();
    }

    public function getAll(): array
    {
        $statement = $this->connection->prepare(
            'SELECT gs.id, gs.created_at, g.group_name, s.subject_name, s.category_type
             FROM `group_subjects` gs
             INNER JOIN `groups` g ON g.id = gs.group_id
             INNER JOIN `subjects` s ON s.id = gs.subject_id
             ORDER BY g.group_name ASC, s.subject_name ASC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getGroups(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, group_name FROM `groups` ORDER BY group_name ASC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getSubjects(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, subject_name, category_type FROM `subjects` ORDER BY subject_name ASC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function groupExists(int $groupId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id FROM `groups` WHERE id = :group_id LIMIT 1'
        );
        $statement->bindValue(':group_id', $groupId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function subjectExists(int $subjectId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id FROM `subjects` WHERE id = :subject_id LIMIT 1'
        );
        $statement->bindValue(':subject_id', $subjectId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function assignmentExists(int $groupId, int $subjectId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id
             FROM `group_subjects`
             WHERE group_id = :group_id AND subject_id = :subject_id
             LIMIT 1'
        );
        $statement->bindValue(':group_id', $groupId, PDO::PARAM_INT);
        $statement->bindValue(':subject_id', $subjectId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function create(int $groupId, int $subjectId): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO `group_subjects` (group_id, subject_id)
             VALUES (:group_id, :subject_id)'
        );

        return $statement->execute([
            ':group_id' => $groupId,
            ':subject_id' => $subjectId,
        ]);
    }

    public function findById(int $assignmentId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, group_id, subject_id, created_at
             FROM `group_subjects`
             WHERE id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $assignmentId, PDO::PARAM_INT);
        $statement->execute();

        $assignment = $statement->fetch();

        return $assignment !== false ? $assignment : null;
    }

    public function delete(int $assignmentId): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM `group_subjects` WHERE id = :id'
        );
        $statement->bindValue(':id', $assignmentId, PDO::PARAM_INT);

        return $statement->execute();
    }
}
