<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;

class ClassroomPost extends BaseModel
{
    protected string $table      = 'classroom_posts';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'classroom_id', 'author_id', 'content', 'post_type', 'attachments', 'is_pinned',
    ];

    // Post type constants
    public const TYPE_ANNOUNCEMENT    = 'announcement';
    public const TYPE_ASSIGNMENT_LINK = 'assignment_link';
    public const TYPE_MATERIAL        = 'material';
    public const TYPE_DISCUSSION      = 'discussion';

    // Post status constants (for future use)
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';

    /**
     * Create a new classroom post
     */
    public function create(array $data): int
    {
        $now                = (new \DateTime())->format('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        // Set default post type if not specified
        if (! isset($data['post_type'])) {
            $data['post_type'] = 'announcement';
        }

        // Set default pinned status if not specified
        if (! isset($data['is_pinned'])) {
            $data['is_pinned'] = false;
        }

        // Handle attachments as JSON
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $data['attachments'] = json_encode($data['attachments']);
        }

        return $this->insert($data);
    }

    /**
     * Update a classroom post
     */
    public function updatePost(int $id, array $data): bool
    {
        $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');

        // Handle attachments as JSON
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $data['attachments'] = json_encode($data['attachments']);
        }

        return $this->update($id, $data);
    }

    /**
     * Get posts for a specific classroom with author details
     */
    public function getByClassroom(int $classroomId, bool $pinnedFirst = true): array
    {
        $orderBy = $pinnedFirst ? 'cp.is_pinned DESC, cp.created_at DESC' : 'cp.created_at DESC';

        $sql = "SELECT cp.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role,
                       (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = cp.id) as comment_count,
                       (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = cp.id) as like_count
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE cp.classroom_id = :classroom_id
                ORDER BY {$orderBy}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['classroom_id' => $classroomId]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON attachments
        foreach ($posts as &$post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $posts;
    }

    /**
     * Get posts for a specific classroom with pagination
     */
    public function getByClassroomWithPagination(int $classroomId, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT cp.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role,
                       (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = cp.id) as comment_count,
                       (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = cp.id) as like_count
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE cp.classroom_id = :classroom_id
                ORDER BY cp.is_pinned DESC, cp.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':classroom_id', $classroomId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON attachments
        foreach ($posts as &$post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $posts;
    }

    /**
     * Get a post with full details including author and classroom info
     */
    public function getPostWithDetails(int $postId): ?array
    {
        $sql = "SELECT cp.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role,
                       c.name as classroom_name,
                       c.teacher_id as classroom_teacher_id,
                       (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = cp.id) as comment_count,
                       (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = cp.id) as like_count
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN classrooms c ON cp.classroom_id = c.id
                WHERE cp.id = :post_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $post ?: null;
    }

    /**
     * Get pinned posts for a classroom
     */
    public function getPinnedPosts(int $classroomId): array
    {
        $sql = "SELECT cp.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE cp.classroom_id = :classroom_id AND cp.is_pinned = 1
                ORDER BY cp.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['classroom_id' => $classroomId]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON attachments
        foreach ($posts as &$post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $posts;
    }

    /**
     * Get posts by type for a classroom
     */
    public function getByType(int $classroomId, string $postType): array
    {
        $sql = "SELECT cp.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role,
                       (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = cp.id) as comment_count,
                       (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = cp.id) as like_count
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE cp.classroom_id = :classroom_id AND cp.post_type = :post_type
                ORDER BY cp.is_pinned DESC, cp.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'classroom_id' => $classroomId,
            'post_type'    => $postType,
        ]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON attachments
        foreach ($posts as &$post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $posts;
    }

    /**
     * Pin or unpin a post
     */
    public function togglePin(int $postId): bool
    {
        $sql  = "UPDATE {$this->table} SET is_pinned = NOT is_pinned, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id'         => $postId,
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get recent posts across all classrooms (for admin dashboard)
     */
    public function getRecentPosts(int $limit = 10): array
    {
        $sql = "SELECT cp.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role,
                       c.name as classroom_name
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN classrooms c ON cp.classroom_id = c.id
                ORDER BY cp.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON attachments
        foreach ($posts as &$post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $posts;
    }

    /**
     * Get posts by author
     */
    public function getByAuthor(int $authorId, int $limit = null): array
    {
        $sql = "SELECT cp.*,
                       c.name as classroom_name,
                       (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = cp.id) as comment_count,
                       (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = cp.id) as like_count
                FROM {$this->table} cp
                LEFT JOIN classrooms c ON cp.classroom_id = c.id
                WHERE cp.author_id = :author_id
                ORDER BY cp.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':author_id', $authorId, \PDO::PARAM_INT);
        if ($limit) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        $stmt->execute();
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON attachments
        foreach ($posts as &$post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $posts;
    }

    /**
     * Count posts in a classroom
     */
    public function countByClassroom(int $classroomId): int
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE classroom_id = :classroom_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['classroom_id' => $classroomId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count posts by type in a classroom
     */
    public function countByType(int $classroomId, string $postType): int
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE classroom_id = :classroom_id AND post_type = :post_type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'classroom_id' => $classroomId,
            'post_type'    => $postType,
        ]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Search posts in a classroom
     */
    public function searchInClassroom(int $classroomId, string $searchTerm): array
    {
        $sql = "SELECT cp.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       up.middle_name as author_middle_name,
                       u.role as author_role,
                       (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = cp.id) as comment_count,
                       (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = cp.id) as like_count
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE cp.classroom_id = :classroom_id
                AND cp.content LIKE :search_term
                ORDER BY cp.is_pinned DESC, cp.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'classroom_id' => $classroomId,
            'search_term'  => "%{$searchTerm}%",
        ]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON attachments
        foreach ($posts as &$post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $posts;
    }

    /**
     * Check if user can modify post (author or classroom teacher)
     */
    public function canUserModifyPost(int $postId, int $userId): bool
    {
        $sql = "SELECT cp.author_id, c.teacher_id
                FROM {$this->table} cp
                JOIN classrooms c ON cp.classroom_id = c.id
                WHERE cp.id = :post_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (! $result) {
            return false;
        }

        // User can modify if they are the author or the classroom teacher
        return $result['author_id'] == $userId || $result['teacher_id'] == $userId;
    }

    /**
     * Delete a post (also deletes related comments and likes via foreign key constraints)
     */
    public function deletePost(int $postId): bool
    {
        return $this->delete($postId);
    }

    /**
     * Get post activity (recent posts, comments, likes) for a classroom
     */
    public function getClassroomActivity(int $classroomId, int $limit = 20): array
    {
        $sql = "SELECT
                    'post' as activity_type,
                    cp.id as activity_id,
                    cp.created_at as activity_time,
                    cp.content as activity_content,
                    cp.post_type,
                    up.first_name as author_first_name,
                    up.last_name as author_last_name,
                    u.role as author_role
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE cp.classroom_id = :classroom_id

                UNION ALL

                SELECT
                    'comment' as activity_type,
                    pc.id as activity_id,
                    pc.created_at as activity_time,
                    pc.content as activity_content,
                    'comment' as post_type,
                    up.first_name as author_first_name,
                    up.last_name as author_last_name,
                    u.role as author_role
                FROM post_comments pc
                JOIN {$this->table} cp ON pc.post_id = cp.id
                LEFT JOIN users u ON pc.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE cp.classroom_id = :classroom_id

                ORDER BY activity_time DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':classroom_id', $classroomId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get posts with engagement stats for analytics
     */
    public function getPostsWithEngagement(int $classroomId): array
    {
        $sql = "SELECT cp.*,
                       up.first_name as author_first_name,
                       up.last_name as author_last_name,
                       u.role as author_role,
                       COUNT(DISTINCT pc.id) as comment_count,
                       COUNT(DISTINCT pl.id) as like_count,
                       (COUNT(DISTINCT pc.id) + COUNT(DISTINCT pl.id)) as engagement_score
                FROM {$this->table} cp
                LEFT JOIN users u ON cp.author_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN post_comments pc ON cp.id = pc.post_id
                LEFT JOIN post_likes pl ON cp.id = pl.post_id
                WHERE cp.classroom_id = :classroom_id
                GROUP BY cp.id
                ORDER BY engagement_score DESC, cp.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['classroom_id' => $classroomId]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON attachments
        foreach ($posts as &$post) {
            $post['attachments'] = $post['attachments'] ? json_decode($post['attachments'], true) : [];
        }

        return $posts;
    }

    /**
     * Get post with author details (alias for getPostWithDetails for backward compatibility)
     */
    public function findWithAuthor(int $postId): ?array
    {
        return $this->getPostWithDetails($postId);
    }

    /**
     * Get posts by classroom and type (alias for getByType for backward compatibility)
     */
    public function getByClassroomAndType(int $classroomId, string $postType): array
    {
        return $this->getByType($classroomId, $postType);
    }

    /**
     * Get available post types
     */
    public static function getPostTypes(): array
    {
        return [
            self::TYPE_ANNOUNCEMENT    => 'Announcement',
            self::TYPE_ASSIGNMENT_LINK => 'Assignment Link',
            self::TYPE_MATERIAL        => 'Material',
            self::TYPE_DISCUSSION      => 'Discussion',
        ];
    }

    /**
     * Get available post statuses
     */
    public static function getPostStatuses(): array
    {
        return [
            self::STATUS_DRAFT     => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
        ];
    }
}
