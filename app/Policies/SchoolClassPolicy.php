<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SchoolClassPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['الصفوف.عرض', 'الصفوف.ادارة']);
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
