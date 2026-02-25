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
        return $user->can('الصفوف.عرض');
    }

    public function manageSchoolClass(User $user): bool
    {
        return $user->can('الصفوف.ادارة');
    }

}
