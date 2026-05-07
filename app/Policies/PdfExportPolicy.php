<?php

namespace App\Policies;

use App\Models\User;

class PdfExportPolicy
{
    public function certificateReplacementExport(User $user)
    {
        return $user->can('بدل_فاقد.توليد_تقارير') || $user->can('بدل_فاقد.ادارة') || $user->can('admin');
    }

    public function transferExport(User $user)
    {
        return $user->can('التحويلات_القبول.توليد_تقارير') || $user->can('التحويلات_القبول.ادارة') || $user->can('admin');
    }

    public function admissionExport(User $user)
    {
        return $user->can('التحويلات_القبول.توليد_تقارير') || $user->can('التحويلات_القبول.ادارة') || $user->can('admin');
    }

    public function finalResultsExport(User $user)
    {
        return $user->can('النتائج.توليد_تقارير') || $user->can('النتائج.ادارة') || $user->can('admin');
    }
}
