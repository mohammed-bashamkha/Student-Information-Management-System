<?php

namespace App\Policies;

use App\Models\FinalResult;
use App\Models\User;

class FinalResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'النتائج.عرض',
            'النتائج.ادارة',
            'النتائج.انشاء',
            'النتائج.تحديث',
            'النتائج.حذف',
            'النتائج.استيراد',
            'النتائج.تصدير',
            'النتائج.توليد_تقارير',
        ]);
    }

    public function view(User $user, FinalResult $finalResult): bool
    {
        return $user->hasAnyPermission([
            'النتائج.عرض',
            'النتائج.ادارة',
            'النتائج.تحديث',
            'النتائج.حذف',
        ]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['النتائج.ادارة', 'النتائج.انشاء']);
    }

    public function update(User $user, FinalResult $finalResult): bool
    {
        return $user->hasAnyPermission(['النتائج.ادارة', 'النتائج.تحديث']);
    }

    public function delete(User $user, FinalResult $finalResult): bool
    {
        return $user->hasAnyPermission(['النتائج.ادارة', 'النتائج.حذف']);
    }

    public function finalResultImport(User $user): bool
    {
        return $user->hasAnyPermission(['النتائج.ادارة', 'النتائج.استيراد']);
    }

    public function finalResultExport(User $user): bool
    {
        return $user->hasAnyPermission([
            'النتائج.تصدير',
            'النتائج.توليد_تقارير',
            'النتائج.ادارة',
            'الدرجات.ادارة',
            'الدرجات.توليد_تقارير',
        ]);
    }
}
