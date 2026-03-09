<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(['الطلاب.ادارة','الطلاب.عرض']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Student $student): bool
    {
        return $user->can(['الطلاب.ادارة','الطلاب.عرض']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(['الطلاب.ادارة','الطلاب.انشاء']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Student $student): bool
    {
        return $user->can('الطلاب.ادارة')
        || $user->can('الطلاب.تحديث')
        && $user->id === $student->created;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        return $user->can('الطلاب.ادارة')
        || $user->can('الطلاب.حدف')
        && $user->id === $student->created;
    }

    public function studentImport(User $user)
    {
        return $user->can(['الطلاب.ادارة','الطلاب.استيراد']);
    }

    public function studentExport(User $user)
    {
        return $user->can(['الطلاب.ادارة','الطلاب.تصدير']);
    }

    public function studentGenerateReport(User $user)
    {
        return $user->can(['الطلاب.ادارة','الطلاب.توليد_تقارير']);
    }
}
