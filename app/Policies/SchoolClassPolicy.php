<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SchoolClassPolicy
{
    /**
     * Determine whether the user can view any models.
     * البيانات المرجعية: أي مستخدم مصادق يمكنه عرض قائمة الصفوف
     * (مطلوبة كقوائم منسدلة في إضافة/تعديل الطلاب، الدرجات، المواد، إلخ)
     */
    public function viewAny(User $user): bool
    {
        return true; // Reference data — accessible to all authenticated users
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SchoolClass $schoolClass): bool
    {
        return $user->hasAnyPermission(['الصفوف.عرض', 'الصفوف.ادارة']);
    }

    public function manageSchoolClass(User $user): bool
    {
        return $user->can('الصفوف.ادارة');
    }
}
