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
        // Join with users table to respect user status
        $sql = "SELECT up.*
                FROM {$this->table} up
                JOIN users u ON up.user_id = u.id
                WHERE up.user_id = :user_id AND u.status = 'active'
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Get profile data regardless of user status (for admin purposes)
    public function findByUserIdIncludingArchived(int $userId): ?array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getFullName(array $profileData): string
    {
        $firstName  = $profileData['first_name'] ?? '';
        $middleName = $profileData['middle_name'] ?? '';
        $lastName   = $profileData['last_name'] ?? '';

        return trim("{$firstName} {$middleName} {$lastName}");
    }

    // public function deleteUserProfile(int $userId): bool
    // {
    //     $sql  = "DELETE FROM {$this->table} WHERE user_id = :user_id";
    //     $stmt = $this->pdo->prepare($sql);
    //     return $stmt->execute(['user_id' => $userId]);
    // }
}
