<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class StipendPeriod
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = (new Database())->connect();
    }

    public function getAll(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, `year`, period, period_group, created_at
             FROM `stipend_periods`
             ORDER BY `year` DESC, period ASC, period_group ASC, id DESC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function existsByPeriodData(int $year, string $period, string $periodGroup): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id
             FROM `stipend_periods`
             WHERE `year` = :year
               AND period = :period
               AND period_group = :period_group
             LIMIT 1'
        );
        $statement->bindValue(':year', $year, PDO::PARAM_INT);
        $statement->bindValue(':period', $period);
        $statement->bindValue(':period_group', $periodGroup);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function existsByPeriodDataExcludingId(int $year, string $period, string $periodGroup, int $periodId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id
             FROM `stipend_periods`
             WHERE `year` = :year
               AND period = :period
               AND period_group = :period_group
               AND id != :id
             LIMIT 1'
        );
        $statement->bindValue(':year', $year, PDO::PARAM_INT);
        $statement->bindValue(':period', $period);
        $statement->bindValue(':period_group', $periodGroup);
        $statement->bindValue(':id', $periodId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    public function create(int $year, string $period, string $periodGroup): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO `stipend_periods` (`year`, period, period_group)
             VALUES (:year, :period, :period_group)'
        );

        return $statement->execute([
            ':year' => $year,
            ':period' => $period,
            ':period_group' => $periodGroup,
        ]);
    }

    public function findById(int $periodId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, `year`, period, period_group, created_at
             FROM `stipend_periods`
             WHERE id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $periodId, PDO::PARAM_INT);
        $statement->execute();

        $period = $statement->fetch();

        return $period !== false ? $period : null;
    }

    public function update(int $periodId, int $year, string $period, string $periodGroup): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE `stipend_periods`
             SET `year` = :year,
                 period = :period,
                 period_group = :period_group
             WHERE id = :id'
        );

        return $statement->execute([
            ':year' => $year,
            ':period' => $period,
            ':period_group' => $periodGroup,
            ':id' => $periodId,
        ]);
    }

    public function delete(int $periodId): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM `stipend_periods` WHERE id = :id'
        );
        $statement->bindValue(':id', $periodId, PDO::PARAM_INT);

        return $statement->execute();
    }
}
