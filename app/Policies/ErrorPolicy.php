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
       return $user->can(['الاخطاء.عرض','الاخطاء.ادارة']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Error $error): bool
    {
        return $user->can(['الاخطاء.عرض','الاخطاء.ادارة']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Error $error): bool
    {
        return $user->can('الاخطاء.ادارة')
        || $user->can('الاخطاء.حدف')
        && $user->id === $error->createdBy;
    }

    public function errorGenerateReport(User $user)
    {
        return $user->can(['الاخطاء.ادارة','الاخطاء.توليد_تقارير']);
    }
}
