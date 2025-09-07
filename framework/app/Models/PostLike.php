<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;

class PostLike extends BaseModel
{
    protected string $table      = 'post_likes';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'post_id', 'user_id',
    ];

    /**
     * Toggle like for a post
     */
    public function toggleLike(int $postId, int $userId): bool
    {
        // Check if like already exists
        $existingLike = $this->getLike($postId, $userId);

        if ($existingLike) {
            // Unlike - remove the like
            return $this->removeLike($postId, $userId);
        } else {
            // Like - add the like
            return $this->addLike($postId, $userId);
        }
    }

    /**
     * Add a like
     */
    public function addLike(int $postId, int $userId): bool
    {
        $data = [
            'post_id'    => $postId,
            'user_id'    => $userId,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        try {
            $this->insert($data);
            return true;
        } catch (\PDOException $e) {
            // Handle duplicate key error (user already liked this post)
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Remove a like
     */
    public function removeLike(int $postId, int $userId): bool
    {
        $sql  = "DELETE FROM {$this->table} WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Get a specific like
     */
    public function getLike(int $postId, int $userId): array | false
    {
        $sql  = "SELECT * FROM {$this->table} WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Check if user has liked a post
     */
    public function hasUserLiked(int $postId, int $userId): bool
    {
        return $this->getLike($postId, $userId) !== false;
    }

    /**
     * Count likes for a post
     */
    public function countByPost(int $postId): int
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE post_id = :post_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get users who liked a post
     */
    public function getLikesByPost(int $postId): array
    {
        $sql = "SELECT pl.*,
                       up.first_name,
                       up.last_name,
                       up.middle_name,
                       u.role
                FROM {$this->table} pl
                LEFT JOIN users u ON pl.user_id = u.id
                LEFT JOIN profiles up ON u.id = up.user_id
                WHERE pl.post_id = :post_id
                ORDER BY pl.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get posts liked by a user
     */
    public function getLikedPostsByUser(int $userId): array
    {
        $sql = "SELECT pl.*,
                       cp.content as post_content,
                       cp.post_type,
                       c.name as classroom_name
                FROM {$this->table} pl
                LEFT JOIN classroom_posts cp ON pl.post_id = cp.id
                LEFT JOIN classrooms c ON cp.classroom_id = c.id
                WHERE pl.user_id = :user_id
                ORDER BY pl.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get recent likes for a classroom
     */
    public function getRecentByClassroom(int $classroomId, int $limit = 10): array
    {
        $sql = "SELECT pl.*,
                       up.first_name,
                       up.last_name,
                       up.middle_name,
                       u.role,
                       cp.content as post_content
                FROM {$this->table} pl
                LEFT JOIN users u ON pl.user_id = u.id
                LEFT JOIN profiles up ON u.id = up.user_id
                LEFT JOIN classroom_posts cp ON pl.post_id = cp.id
                WHERE cp.classroom_id = :classroom_id
                ORDER BY pl.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':classroom_id', $classroomId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
