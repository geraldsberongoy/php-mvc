<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;

class PostComment extends BaseModel
{
    protected string $table      = 'post_comments';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'post_id', 'author_id', 'content', 'parent_id',
    ];

    /**
     * Create a new comment
     */
    public function create(array $data): int
    {
        $now                = (new \DateTime())->format('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        return $this->insert($data);
    }

    /**
     * Update a comment
     */
    public function updateComment(int $id, array $data): bool
    {
        $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /**
     * Get comments for a post with author details
     */
    public function getByPost(int $postId): array
    {
        $sql = "SELECT pc.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role
                FROM {$this->table} pc
                LEFT JOIN users u ON pc.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE pc.post_id = :post_id
                ORDER BY pc.created_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get threaded comments for a post (organized by parent-child relationships)
     */
    public function getThreadedComments(int $postId): array
    {
        $sql = "SELECT pc.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role
                FROM {$this->table} pc
                LEFT JOIN users u ON pc.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE pc.post_id = :post_id
                ORDER BY
                    CASE WHEN pc.parent_id IS NULL THEN pc.id ELSE pc.parent_id END,
                    pc.parent_id IS NOT NULL,
                    pc.created_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Organize comments into parent-child structure
        $threaded = [];
        $replies  = [];

        foreach ($comments as $comment) {
            if ($comment['parent_id'] === null) {
                $comment['replies']       = [];
                $threaded[$comment['id']] = $comment;
            } else {
                $replies[$comment['parent_id']][] = $comment;
            }
        }

        // Add replies to their parent comments
        foreach ($replies as $parentId => $parentReplies) {
            if (isset($threaded[$parentId])) {
                $threaded[$parentId]['replies'] = $parentReplies;
            }
        }

        return array_values($threaded);
    }

    /**
     * Count comments for a post
     */
    public function countByPost(int $postId): int
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE post_id = :post_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get recent comments for a classroom
     */
    public function getRecentByClassroom(int $classroomId, int $limit = 5): array
    {
        $sql = "SELECT pc.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role,
                       cp.content as post_content
                FROM {$this->table} pc
                LEFT JOIN users u ON pc.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN classroom_posts cp ON pc.post_id = cp.id
                WHERE cp.classroom_id = :classroom_id
                ORDER BY pc.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':classroom_id', $classroomId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check if user can modify comment (author or teacher)
     */
    public function canUserModifyComment(int $commentId, int $userId): bool
    {
        $sql = "SELECT pc.author_id, c.teacher_id
                FROM {$this->table} pc
                JOIN classroom_posts cp ON pc.post_id = cp.id
                JOIN classrooms c ON cp.classroom_id = c.id
                WHERE pc.id = :comment_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['comment_id' => $commentId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (! $result) {
            return false;
        }

        // User can modify if they are the author or the classroom teacher
        return $result['author_id'] == $userId || $result['teacher_id'] == $userId;
    }

    /**
     * Delete a comment
     */
    public function deleteComment(int $commentId): bool
    {
        return $this->delete($commentId);
    }

    /**
     * Get comment with full details
     */
    public function getCommentWithDetails(int $commentId): array | false
    {
        $sql = "SELECT pc.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role,
                       cp.content as post_content,
                       c.name as classroom_name
                FROM {$this->table} pc
                LEFT JOIN users u ON pc.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN classroom_posts cp ON pc.post_id = cp.id
                LEFT JOIN classrooms c ON cp.classroom_id = c.id
                WHERE pc.id = :comment_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['comment_id' => $commentId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
