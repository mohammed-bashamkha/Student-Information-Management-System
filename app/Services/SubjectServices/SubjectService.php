<?php

namespace App\Services\SubjectServices;

use App\Models\Subject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class SubjectService
{
    use AuthorizesRequests;
    public function getSubjects(array $filters = [])
    {
        $this->authorize('viewAny', Subject::class);
        
        $query = Subject::with(['schoolClasses', 'level']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['level_id'])) {
            $query->where('level_id', $filters['level_id']);
        }

        return $query->get();
    }
    
    public function storeSubject(array $validateData)
    {
        $this->authorize('create', Subject::class);

        $subject = Subject::create([
            'name'       => $validateData['name'],
            'level_id'   => $validateData['level_id'],
            'created_by' => Auth::id(),
        ]);

        if (!empty($validateData['school_class_id'])) {
            $subject->schoolClasses()->sync($validateData['school_class_id']);
        }

        return $subject;
    }

    public function updateSubject(array $validateData, string $id)
    {
        $subject = Subject::findOrFail($id);
        $this->authorize('update', $subject);
        $subject->update([
            'name'       => $validateData['name'],
            'level_id'   => $validateData['level_id'],
        ]);
        
        if (!empty($validateData['school_class_id'])) {
            $subject->schoolClasses()->sync($validateData['school_class_id']);
        }
        return $subject;
    }

    public function getSubjectById(string $id)
    {
        $this->authorize('view', Subject::class);
        $subject = Subject::with(['schoolClasses', 'level'])->findOrFail($id);
        return $subject;
    }

    public function deleteSubject(string $id)
    {
        $subject = Subject::withCount('grades')->findOrFail($id);
        $this->authorize('delete', $subject);

        if ($subject->grades_count > 0) {
            throw new \Exception('لا يمكن حذف المادة الدراسية لوجود درجات مسجلة بها', 403);
        }

        $subject->delete();
        return $subject;
    }
}
