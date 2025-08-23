<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;
use PDO;

class UserProfile extends BaseModel
{
    protected string $table      = 'user_profiles';
    protected string $primaryKey = 'user_id';

    public function createUserProfile(int $userId, array $data): bool
    {
        $data['user_id'] = $userId;
        $columns         = array_keys($data);
        $placeholders    = array_map(fn($c) => ":{$c}", $columns);
        $sql             = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateUserProfile(int $userId, array $data): bool
    {
        $sets            = implode(', ', array_map(fn($c) => "{$c} = :{$c}", array_keys($data)));
        $sql             = sprintf("UPDATE %s SET %s WHERE user_id = :user_id", $this->table, $sets);
        $data['user_id'] = $userId;
        $stmt            = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function findByUserId(int $userId): ?array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
