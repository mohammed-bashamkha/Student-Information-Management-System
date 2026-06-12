<?php

namespace App\Policies;

use App\Models\Error;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ErrorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'الاخطاء.عرض',
            'الاخطاء.ادارة',
            'الاخطاء.انشاء',
            'الاخطاء.تحديث',
            'الاخطاء.حذف',
            'الاخطاء.توليد_تقارير',
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Error $error): bool
    {
        return $user->hasAnyPermission([
            'الاخطاء.عرض',
            'الاخطاء.ادارة',
            'الاخطاء.تحديث',
            'الاخطاء.حذف',
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['الاخطاء.ادارة', 'الاخطاء.انشاء']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Error $error): bool
    {
        return $user->hasAnyPermission(['الاخطاء.ادارة', 'الاخطاء.تحديث']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Error $error): bool
    {
        return $user->hasAnyPermission(['الاخطاء.ادارة', 'الاخطاء.حذف']);
    }

    public function errorGenerateReport(User $user): bool
    {
        return $user->hasAnyPermission(['الاخطاء.ادارة', 'الاخطاء.توليد_تقارير']);
    }
}
