<?php

namespace App\Services\SubjectServices;

use App\Models\Subject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class SubjectService
{
    use AuthorizesRequests;
    public function getSubjects()
    {
        $this->authorize('viewAny',Subject::class);
        $subjects = Subject::with('schoolClass')->paginate(5);
        return $subjects;
    }
    
    public function storeSubject(array $validateData)
    {
        $this->authorize('create',Subject::class);
        $validateData['created_by'] = Auth::id();
        $subject = Subject::create($validateData);
        return $subject;
    }

    public function updateSubject(array $validateData, string $id)
    {
        $subject = Subject::findOrFail($id);
        $this->authorize('update',$subject);
        $validateData['created_by'] = Auth::id();
        $subject->update($validateData);
        return $subject;
    }

    public function deleteSubject(string $id)
    {
        $subject = Subject::findOrFail($id);
        $this->authorize('delete',$subject);
        $subject->delete();
        return $subject;
    }
}
