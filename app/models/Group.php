<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class Group
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = (new Database())->connect();
    }

    public function getAll(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, group_name, created_at FROM `groups` ORDER BY group_name ASC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function existsByName(string $groupName): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id FROM `groups` WHERE group_name = :group_name LIMIT 1'
        );
        $statement->bindValue(':group_name', $groupName);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function existsByNameExcludingId(string $groupName, int $groupId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id
             FROM `groups`
             WHERE group_name = :group_name AND id != :id
             LIMIT 1'
        );
        $statement->bindValue(':group_name', $groupName);
        $statement->bindValue(':id', $groupId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function create(string $groupName): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO `groups` (group_name) VALUES (:group_name)'
        );

        return $statement->execute([
            ':group_name' => $groupName,
        ]);
    }

    public function findById(int $groupId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, group_name, created_at
             FROM `groups`
             WHERE id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $groupId, PDO::PARAM_INT);
        $statement->execute();

        $group = $statement->fetch();

        return $group !== false ? $group : null;
    }

    public function update(int $groupId, string $groupName): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE `groups`
             SET group_name = :group_name
             WHERE id = :id'
        );

        return $statement->execute([
            ':group_name' => $groupName,
            ':id' => $groupId,
        ]);
    }

    public function delete(int $groupId): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM `groups` WHERE id = :id'
        );
        $statement->bindValue(':id', $groupId, PDO::PARAM_INT);

        return $statement->execute();
    }
}
