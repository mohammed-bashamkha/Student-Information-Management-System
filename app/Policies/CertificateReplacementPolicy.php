<?php

namespace App\Policies;

use App\Models\CertificateReplacement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CertificateReplacementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(['بدل_فاقد.ادارة','بدل_فاقد.عرض']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CertificateReplacement $certificateReplacement): bool
    {
        return $user->can(['بدل_فاقد.ادارة','بدل_فاقد.عرض']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(['بدل_فاقد.ادارة','بدل_فاقد.انشاء']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CertificateReplacement $certificateReplacement): bool
    {
        return $user->can('بدل_فاقد.ادارة')
        || $user->can('بدل_فاقد.تحديث')
        && $user->id == $certificateReplacement->createdBy;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CertificateReplacement $certificateReplacement): bool
    {
        return $user->can('بدل_فاقد.ادارة')
        || $user->can('بدل_فاقد.تحديث')
        && $user->id == $certificateReplacement->createdBy;
    }

    public function errorGenerateReport(User $user)
    {
        return $user->can(['بدل_فاقد.ادارة','بدل_فاقد.توليد_تقارير']);
    }
}