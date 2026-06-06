<?php

namespace App\Policies;

use App\Models\FinalResult;
use App\Models\User;

class FinalResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['النتائج.عرض', 'النتائج.ادارة']);
    }

    public function view(User $user, FinalResult $finalResult): bool
    {
        return $user->hasAnyPermission(['النتائج.عرض', 'النتائج.ادارة']);
    }

    public function finalResultExport(User $user, FinalResult $finalResult): bool
    {
        return $user->hasAnyPermission(['النتائج.تصدير', 'النتائج.توليد_تقارير', 'النتائج.ادارة', 'الدرجات.ادارة', 'الدرجات.توليد_تقارير']);
    }
}
