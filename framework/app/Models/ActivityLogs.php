<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;

class ActivityLogs extends BaseModel
{
    protected string $table   = 'activity_logs';
    protected array $fillable = ['user_id', 'action', 'description', 'created_at'];

    public function log(?int $userId, string $action, string $description, ?string $ipAddress = null): bool
    {
        $data = [
            'user_id'     => $userId, // Allow NULL for anonymous activities like failed logins
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $ipAddress,
            'created_at'  => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        return $this->insert($data);
    }

    public function getRecentActivities(int $userId, int $limit = 10): array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllActivities(int $limit = 50): array
    {
        $sql = "SELECT al.*, up.first_name, up.last_name, u.role,
                CASE
                    WHEN al.user_id IS NULL THEN 'Anonymous'
                    ELSE CONCAT(COALESCE(up.first_name, ''), ' ', COALESCE(up.last_name, ''))
                END as full_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN user_profiles up ON al.user_id = up.user_id
                ORDER BY al.created_at DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getActivitiesByAction(string $action, int $limit = 20): array
    {
        $sql = "SELECT al.*, up.first_name, up.last_name, u.role,
                CASE
                    WHEN al.user_id IS NULL THEN 'Anonymous'
                    ELSE CONCAT(COALESCE(up.first_name, ''), ' ', COALESCE(up.last_name, ''))
                END as full_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN user_profiles up ON al.user_id = up.user_id
                WHERE al.action = :action
                ORDER BY al.created_at DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':action', $action);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
