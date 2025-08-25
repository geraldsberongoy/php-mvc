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
}
