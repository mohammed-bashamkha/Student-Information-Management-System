<?php

namespace App\Policies;

use App\Models\TransfersAdmission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransfersAdmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['التحويلات_القبول.عرض', 'التحويلات_القبول.ادارة']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransfersAdmission $transfersAdmission): bool
    {
        return $user->hasAnyPermission(['التحويلات_القبول.عرض', 'التحويلات_القبول.ادارة']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['التحويلات_القبول.انشاء', 'التحويلات_القبول.ادارة']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TransfersAdmission $transfersAdmission): bool
    {
        return $user->can('التحويلات_القبول.ادارة')
        || ($user->can('التحويلات_القبول.تحديث') && $user->id == $transfersAdmission->created_by);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransfersAdmission $transfersAdmission): bool
    {
        return $user->can('التحويلات_القبول.ادارة')
        || ($user->can('التحويلات_القبول.حذف') && $user->id == $transfersAdmission->created_by);
    }
    
    public function transfersAdmissionsGenerateReport(User $user): bool
    {
        return $user->hasAnyPermission(['التحويلات_القبول.ادارة', 'التحويلات_القبول.توليد_تقارير']);
    }
}
