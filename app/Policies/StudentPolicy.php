<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     * أي مستخدم لديه أي صلاحية على الطلاب يمكنه رؤية القائمة
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'الطلاب.ادارة',
            'الطلاب.عرض',
            'الطلاب.انشاء',
            'الطلاب.تحديث',
            'الطلاب.حذف',
            'الطلاب.استيراد',
            'الطلاب.تصدير',
            'الطلاب.توليد_تقارير',
        ]);
    }

    /**
     * Determine whether the user can view the model.
     * من يملك تحديث أو حذف يحتاج عرض البيانات أولاً
     */
    public function view(User $user, Student $student): bool
    {
        return $user->hasAnyPermission([
            'الطلاب.ادارة',
            'الطلاب.عرض',
            'الطلاب.تحديث',
            'الطلاب.حذف',
        ]);
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
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب.تحديث']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب.حذف']);
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

    public function viewAllSuspendedStudents(User $user): bool
    {
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب_الموقوفين.عرض']);
    }

    public function activateSuspendedStudent(User $user): bool
    {
        return $user->hasAnyPermission(['الطلاب.ادارة', 'الطلاب_الموقوفين.تفعيل']);
    }
}
