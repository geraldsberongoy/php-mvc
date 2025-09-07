<?php
namespace App\Models;

use Gerald\Framework\Models\BaseModel;

class Classroom extends BaseModel
{
    protected string $table      = 'classrooms';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'teacher_id', 'name', 'code', 'description',
    ];

    protected array $guarded = ['id', 'created_at'];

    public function create(array $data): int
    {
        $now                = (new \DateTime())->format('Y-m-d H:i:s');
        $data['created_at'] = $now;
        return $this->insert($data);
    }

    public function updateClassroom(int $id, array $data): bool
    {
        $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    // Get classroom with teacher info
    public function getWithTeacher(int $id): ?array
    {
        $sql = "SELECT c.*, u.* FROM {$this->table} c
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE c.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    // Get classroom with teacher details (including profile)
    public function getWithTeacherDetails(int $id): ?array
    {
        $sql = "SELECT c.*,
                       u.role as teacher_role, u.status as teacher_status,
                       up.first_name as teacher_first_name,
                       up.last_name as teacher_last_name,
                       up.middle_name as teacher_middle_name,
                       uc.email as teacher_email
                FROM {$this->table} c
                LEFT JOIN users u ON c.teacher_id = u.id AND u.status = 'active'
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN user_credentials uc ON u.id = uc.user_id
                WHERE c.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    // Get all classrooms with pagination
    public function getAllWithPagination(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT c.*,
                       up.first_name as teacher_first_name,
                       up.last_name as teacher_last_name,
                       uc.email as teacher_email,
                       (SELECT COUNT(*) FROM classroom_students cs WHERE cs.classroom_id = c.id) as student_count
                FROM {$this->table} c
                LEFT JOIN users u ON c.teacher_id = u.id AND u.status = 'active'
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN user_credentials uc ON u.id = uc.user_id
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Count total classrooms
    public function count(): int
    {
        $sql  = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

// Get classroom students
    public function getStudents(int $classroomId): array
    {
        $sql = "SELECT u.* FROM users u
            INNER JOIN classroom_students cs ON u.id = cs.student_id
            WHERE cs.classroom_id = :classroom_id AND u.status = 'active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['classroom_id' => $classroomId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Get classroom students with their profiles
    public function getStudentsWithProfiles(int $classroomId): array
    {
        $sql = "SELECT u.*,
                       up.first_name, up.last_name, up.middle_name, up.gender, up.birthdate,
                       uc.email,
                       cs.enrolled_at
                FROM users u
                INNER JOIN classroom_students cs ON u.id = cs.student_id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN user_credentials uc ON u.id = uc.user_id
                WHERE cs.classroom_id = :classroom_id AND u.status = 'active'
                ORDER BY up.last_name, up.first_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['classroom_id' => $classroomId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Get available students (not in this classroom)
    public function getAvailableStudents(int $classroomId): array
    {
        $sql = "SELECT u.id, up.first_name, up.last_name, up.middle_name, uc.email
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN user_credentials uc ON u.id = uc.user_id
                WHERE u.role = 'student'
                AND u.status = 'active'
                AND u.id NOT IN (
                    SELECT student_id FROM classroom_students
                    WHERE classroom_id = :classroom_id
                )
                ORDER BY up.last_name, up.first_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['classroom_id' => $classroomId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Find classroom by unique code
    public function findByCode(string $code): ?array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE code = :code LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['code' => $code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

// Get classrooms for a specific teacher
    public function getByTeacher(int $teacherId): array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE teacher_id = :teacher_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

// Get classrooms for a specific student
    public function getByStudent(int $studentId): array
    {
        $sql = "SELECT c.*,
                   up.first_name as teacher_first_name,
                   up.last_name as teacher_last_name,
                   cs.enrolled_at
            FROM {$this->table} c
            INNER JOIN classroom_students cs ON c.id = cs.classroom_id
            LEFT JOIN users u ON c.teacher_id = u.id AND u.status = 'active'
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE cs.student_id = :student_id
            ORDER BY c.name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

// Add student to classroom
    public function addStudent(int $classroomId, int $studentId): bool
    {
        $sql = "INSERT INTO classroom_students (classroom_id, student_id, enrolled_at)
            VALUES (:classroom_id, :student_id, :enrolled_at)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'classroom_id' => $classroomId,
            'student_id'   => $studentId,
            'enrolled_at'  => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

// Remove student from classroom
    public function removeStudent(int $classroomId, int $studentId): bool
    {
        $sql = "DELETE FROM classroom_students
            WHERE classroom_id = :classroom_id AND student_id = :student_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'classroom_id' => $classroomId,
            'student_id'   => $studentId,
        ]);
    }


}
