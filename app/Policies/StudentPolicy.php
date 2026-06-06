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
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب.عرض']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Student $student): bool
    {
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب.عرض']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب.انشاء']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Student $student): bool
    {
        return $user->can('الطلاب.ادارة')
        || ($user->can('الطلاب.تحديث') && $user->id === $student->created_by);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        return $user->can('الطلاب.ادارة')
        || ($user->can('الطلاب.حذف') && $user->id === $student->created_by);
    }

    public function studentImport(User $user): bool
    {
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب.استيراد']);
    }

    public function studentExport(User $user): bool
    {
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب.تصدير']);
    }

    public function studentGenerateReport(User $user): bool
    {
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب.توليد_تقارير']);
    }
}
