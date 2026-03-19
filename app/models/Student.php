<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class Student
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = (new Database())->connect();
    }

    public function getAll(): array
    {
        $statement = $this->connection->prepare(
            'SELECT s.id, s.first_name, s.last_name, s.personal_code, s.created_at, g.group_name
             FROM `students` s
             INNER JOIN `groups` g ON g.id = s.group_id
             ORDER BY s.id DESC'
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

    public function create(int $groupId, string $firstName, string $lastName, string $personalCode): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO `students` (group_id, first_name, last_name, personal_code)
             VALUES (:group_id, :first_name, :last_name, :personal_code)'
        );

        return $statement->execute([
            ':group_id' => $groupId,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':personal_code' => $personalCode,
        ]);
    }

    public function findById(int $studentId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, group_id, first_name, last_name, personal_code, created_at
             FROM `students`
             WHERE id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $studentId, PDO::PARAM_INT);
        $statement->execute();

        $student = $statement->fetch();

        return $student !== false ? $student : null;
    }

    public function update(int $studentId, int $groupId, string $firstName, string $lastName, string $personalCode): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE `students`
             SET group_id = :group_id,
                 first_name = :first_name,
                 last_name = :last_name,
                 personal_code = :personal_code
             WHERE id = :id'
        );

        return $statement->execute([
            ':group_id' => $groupId,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':personal_code' => $personalCode,
            ':id' => $studentId,
        ]);
    }

    public function delete(int $studentId): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM `students` WHERE id = :id'
        );
        $statement->bindValue(':id', $studentId, PDO::PARAM_INT);

        return $statement->execute();
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

    public function personalCodeExists(string $personalCode, ?int $excludeStudentId = null): bool
    {
        $sql = 'SELECT id
                FROM `students`
                WHERE personal_code = :personal_code';

        if ($excludeStudentId !== null) {
            $sql .= ' AND id != :exclude_id';
        }

        $sql .= ' LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':personal_code', $personalCode, PDO::PARAM_STR);

        if ($excludeStudentId !== null) {
            $statement->bindValue(':exclude_id', $excludeStudentId, PDO::PARAM_INT);
        }

        $statement->execute();

        return $statement->fetch() !== false;
    }
}
