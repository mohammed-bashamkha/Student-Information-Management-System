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
        return $user->hasAnyPermission(['بدل_فاقد.ادارة', 'بدل_فاقد.عرض', 'بدل_فاقد.انشاء', 'بدل_فاقد.تحديث', 'بدل_فاقد.حذف']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CertificateReplacement $certificateReplacement): bool
    {
        return $user->hasAnyPermission(['بدل_فاقد.ادارة', 'بدل_فاقد.عرض', 'بدل_فاقد.تحديث', 'بدل_فاقد.حذف']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['بدل_فاقد.ادارة', 'بدل_فاقد.انشاء']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CertificateReplacement $certificateReplacement): bool
    {
        return $user->hasAnyPermission(['بدل_فاقد.ادارة', 'بدل_فاقد.تحديث']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CertificateReplacement $certificateReplacement): bool
    {
        return $user->hasAnyPermission(['بدل_فاقد.ادارة', 'بدل_فاقد.حذف']);
    }

    public function generateReport(User $user): bool
    {
        return $user->hasAnyPermission(['بدل_فاقد.ادارة', 'بدل_فاقد.توليد_تقارير']);
    }
}