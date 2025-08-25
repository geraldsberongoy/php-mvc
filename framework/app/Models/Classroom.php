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

// Get classroom students
    public function getStudents(int $classroomId): array
    {
        $sql = "SELECT u.* FROM users u
            INNER JOIN classroom_students cs ON u.id = cs.student_id
            WHERE cs.classroom_id = :classroom_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['classroom_id' => $classroomId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Find classroom by unique code
public function findByCode(string $code): ?array
{
    $sql = "SELECT * FROM {$this->table} WHERE code = :code LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['code' => $code]);
    return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
}

// Get classrooms for a specific teacher
public function getByTeacher(int $teacherId): array
{
    $sql = "SELECT * FROM {$this->table} WHERE teacher_id = :teacher_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['teacher_id' => $teacherId]);
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
        'student_id' => $studentId,
        'enrolled_at' => (new \DateTime())->format('Y-m-d H:i:s')
    ]);
}
}
