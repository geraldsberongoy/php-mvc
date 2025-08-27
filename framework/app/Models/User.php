<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;

class User extends BaseModel
{
    protected string $table      = 'users';
    protected string $primaryKey = 'id';

    // Create a new user
    public function create(array $data): int
    {
        $now                = (new \DateTime())->format('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        // Set default status to active if not specified
        if (! isset($data['status'])) {
            $data['status'] = 'active';
        }

        return $this->insert($data);
    }

    // Update user information
    public function updateUser(int $id, array $data): bool
    {
        $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    // Find users by role
    public function findByRole(string $role): array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE role = :role AND status = 'active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Get user role by user ID

    // Get user field by user ID
    // ex: getUserField(1, 'email')
    public function getUserField(int $userId, string $field): mixed
    {
        $sql  = "SELECT {$field} FROM {$this->table} WHERE id = :id AND status = 'active' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result[$field] : null;
    }

    public function getUserData(int $userId, array $fields = ['*']): ?array
    {
        $fieldsList = $fields[0] === '*' ? '*' : implode(', ', $fields);
        $sql        = "SELECT {$fieldsList} FROM {$this->table} WHERE id = :id AND status = 'active' LIMIT 1";
        $stmt       = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

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

    public function count(): int
    {
        $sql  = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    public function countByRole(string $role): int
    {
        $sql  = "SELECT COUNT(*) as total FROM {$this->table} WHERE role = :role AND status = 'active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role' => $role]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    public function getAllWithPagination(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT u.*,
                       up.first_name, up.last_name, up.middle_name, up.gender, up.birthdate,
                       uc.email
                FROM {$this->table} u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN user_credentials uc ON u.id = uc.user_id
                WHERE u.status = 'active'
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT u.*,
                       up.first_name, up.last_name, up.middle_name, up.gender, up.birthdate,
                       uc.email
                FROM {$this->table} u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN user_credentials uc ON u.id = uc.user_id
                WHERE u.id = :id AND u.status = 'active'
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    // Soft delete - archive a user
    public function archiveUser(int $id): bool
    {
        $data = [
            'status'     => 'archived',
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
        return $this->update($id, $data);
    }

    // Restore an archived user
    public function restoreUser(int $id): bool
    {
        $data = [
            'status'     => 'active',
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
        return $this->update($id, $data);
    }

    // Get archived users with pagination
    public function getArchivedWithPagination(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT u.*,
                       up.first_name, up.last_name, up.middle_name, up.gender, up.birthdate,
                       uc.email
                FROM {$this->table} u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN user_credentials uc ON u.id = uc.user_id
                WHERE u.status = 'archived'
                ORDER BY u.updated_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Count archived users
    public function countArchived(): int
    {
        $sql  = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'archived'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    // Get all users (both active and archived) - for admin purposes
    public function getAllUsersIncludingArchived(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT u.*,
                       up.first_name, up.last_name, up.middle_name, up.gender, up.birthdate,
                       uc.email
                FROM {$this->table} u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN user_credentials uc ON u.id = uc.user_id
                ORDER BY u.status DESC, u.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
