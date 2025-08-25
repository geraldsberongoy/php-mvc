<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;

class User extends BaseModel
{
    protected string $table      = 'users';
    protected string $primaryKey = 'id';

    public function create(array $data): int
    {
        $now                = (new \DateTime())->format('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return $this->insert($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    public function findByRole(string $role): array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE role = :role";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRole(string $key): ?string
    {
        return $this->data[$key] ?? null;
    }

    // Method 1: Get user's role by ID
    public function getUserRole(int $userId): ?string
    {
        $sql  = "SELECT role FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['role'] : null;
    }

    // Method 2: Get any specific field by user ID
    public function getUserField(int $userId, string $field): mixed
    {
        $sql  = "SELECT {$field} FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result[$field] : null;
    }

    // Method 3: Get multiple fields for a user
    public function getUserData(int $userId, array $fields = ['*']): ?array
    {
        $fieldsList = $fields[0] === '*' ? '*' : implode(', ', $fields);
        $sql        = "SELECT {$fieldsList} FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt       = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // Method 4: Get users with specific conditions
    public function getUsersWhere(array $conditions, int $limit = null): array
    {
        $whereClause = [];
        $params      = [];

        foreach ($conditions as $field => $value) {
            $whereClause[]  = "{$field} = :{$field}";
            $params[$field] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
