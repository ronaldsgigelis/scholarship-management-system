<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class Subject
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = (new Database())->connect();
    }

    public function getAll(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, subject_name, category_type, created_at
             FROM `subjects`
             ORDER BY subject_name ASC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function existsByName(string $subjectName): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id FROM `subjects` WHERE subject_name = :subject_name LIMIT 1'
        );
        $statement->bindValue(':subject_name', $subjectName);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function existsByNameExcludingId(string $subjectName, int $subjectId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id
             FROM `subjects`
             WHERE subject_name = :subject_name AND id != :id
             LIMIT 1'
        );
        $statement->bindValue(':subject_name', $subjectName);
        $statement->bindValue(':id', $subjectId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function create(string $subjectName, string $categoryType): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO `subjects` (subject_name, category_type)
             VALUES (:subject_name, :category_type)'
        );

        return $statement->execute([
            ':subject_name' => $subjectName,
            ':category_type' => $categoryType,
        ]);
    }

    public function findById(int $subjectId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, subject_name, category_type, created_at
             FROM `subjects`
             WHERE id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $subjectId, PDO::PARAM_INT);
        $statement->execute();

        $subject = $statement->fetch();

        return $subject !== false ? $subject : null;
    }

    public function update(int $subjectId, string $subjectName, string $categoryType): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE `subjects`
             SET subject_name = :subject_name,
                 category_type = :category_type
             WHERE id = :id'
        );

        return $statement->execute([
            ':subject_name' => $subjectName,
            ':category_type' => $categoryType,
            ':id' => $subjectId,
        ]);
    }

    public function delete(int $subjectId): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM `subjects` WHERE id = :id'
        );
        $statement->bindValue(':id', $subjectId, PDO::PARAM_INT);

        return $statement->execute();
    }
}
