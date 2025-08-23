<?php
namespace Gerald\Framework\Models;

use Gerald\Framework\Database\Connection;
use PDO;

abstract class BaseModel
{
    protected PDO $pdo;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $conn      = Connection::create();
        $this->pdo = $conn->pdo;
    }

    public function find(int $id): ?array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function all(): array
    {
        $sql  = "SELECT * FROM {$this->table}";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): bool
    {
        $sql  = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    protected function insert(array $data): int
    {
        $columns      = array_keys($data);
        $placeholders = array_map(fn($c) => ":{$c}", $columns);
        $sql          = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    protected function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($c) => "{$c} = :{$c}", array_keys($data)));
        $sql  = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            $this->table,
            $sets,
            $this->primaryKey
        );
        $data['id'] = $id;
        $stmt       = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
}
