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
        return $user->can(['التحويلات_القبول.عرض','التحويلات_القبول.ادارة']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransfersAdmission $transfersAdmission): bool
    {
        return $user->can(['التحويلات_القبول.عرض','التحويلات_القبول.ادارة']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(['التحويلات_القبول.انشاء','التحويلات_القبول.ادارة']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TransfersAdmission $transfersAdmission): bool
    {
        return $user->can('التحويلات_القبول.ادارة')
        || $user->can('التحويلات_القبول.تحديث')
        && $user->id == $transfersAdmission->createdBy;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransfersAdmission $transfersAdmission): bool
    {
        return $user->can('التحويلات_القبول.ادارة')
        || $user->can('التحويلات_القبول.حدف')
        && $user->id == $transfersAdmission->createdBy;
    }
    
    public function transfersAdmissionsGenerateReport(User $user)
    {
        return $user->can(['التحويلات_القبول.ادارة','التحويلات_القبول.توليد_تقارير']);
    }
}
