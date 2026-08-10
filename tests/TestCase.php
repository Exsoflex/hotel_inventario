<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getTestPdo(): \PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            $pdo = new \PDO(
                'mysql:host=localhost;dbname=' . TEST_DB_NAME . ';charset=utf8',
                'root',
                'root123'
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return $pdo;
    }

    protected function cleanTable(string $table, array $conditions = []): void
    {
        $sql = "DELETE FROM `$table`";
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "`$column` = :$column";
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        } else {
            $sql .= ' WHERE 1=1';
        }

        $stmt = $this->getTestPdo()->prepare($sql);
        foreach ($conditions as $column => $value) {
            $stmt->bindValue(":$column", $value);
        }
        $stmt->execute();
    }

    protected function insertRow(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) 
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->getTestPdo()->prepare($sql);
        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        $stmt->execute();

        return (int)$this->getTestPdo()->lastInsertId();
    }

    protected function assertDatabaseHas(string $table, array $data): void
    {
        $columns = array_keys($data);
        $where = [];
        foreach ($columns as $col) {
            $where[] = "`$col` = :$col";
        }

        $sql = "SELECT COUNT(*) FROM `$table` WHERE " . implode(' AND ', $where);
        $stmt = $this->getTestPdo()->prepare($sql);
        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        $stmt->execute();

        $this->assertEquals(1, $stmt->fetchColumn(), "Expected row in $table not found.");
    }

    protected function assertDatabaseMissing(string $table, array $data): void
    {
        $columns = array_keys($data);
        $where = [];
        foreach ($columns as $col) {
            $where[] = "`$col` = :$col";
        }

        $sql = "SELECT COUNT(*) FROM `$table` WHERE " . implode(' AND ', $where);
        $stmt = $this->getTestPdo()->prepare($sql);
        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        $stmt->execute();

        $this->assertEquals(0, $stmt->fetchColumn(), "Unexpected row found in $table.");
    }

    protected function getRow(string $table, array $conditions): ?array
    {
        $where = [];
        foreach ($conditions as $col => $val) {
            $where[] = "`$col` = :$col";
        }

        $sql = "SELECT * FROM `$table` WHERE " . implode(' AND ', $where);
        $stmt = $this->getTestPdo()->prepare($sql);
        foreach ($conditions as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    protected function resetSession(): void
    {
        $_SESSION = [];
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
            session_start();
        }
    }

    protected function setSessionUser(array $user): void
    {
        $this->resetSession();
        $_SESSION['usuario'] = $user;
    }
}
