<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;
use PDO;

class UserCredential extends BaseModel
{
    protected string $table      = 'user_credentials';
    protected string $primaryKey = 'user_id';

    public function findByEmail(string $email): ?array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createCredential(int $userId, string $email, string $password): int
    {
        $data = [
            'user_id'    => $userId,
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'last_login' => null,
        ];

        // insert uses lastInsertId; but this table uses user_id as PK and not auto increment.
        // We'll perform a manual insert and return the user_id on success.
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
        return $userId;
    }

    public function verify(string $email, string $password): ?array
    {
        $row = $this->findByEmail($email);
        if (! $row) {
            return null;
        }

        if (password_verify($password, $row['password'])) {
            return $row;
        }

        return null;
    }

    public function updateLastLogin(int $userId): bool
    {
        $sql  = "UPDATE {$this->table} SET last_login = :last_login WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'last_login' => (new \DateTime())->format('Y-m-d H:i:s'),
            'user_id'    => $userId,
        ]);
    }
}
