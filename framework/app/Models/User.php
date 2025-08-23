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
}
