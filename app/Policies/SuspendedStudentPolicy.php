<?php

namespace App\Policies;

use App\Models\User;

class SuspendedStudentPolicy
{
    public function viewAllSuspendedStudents(User $user)
    {
        return $user->hasPermissionTo('الطلاب_الموقوفين.عرض');
    }

    public function viewSuspendedStudent(User $user)
    {
        return $user->hasPermissionTo('الطلاب_الموقوفين.عرض');
    }

    public function activateSuspendedStudent(User $user)
    {
        return $user->hasPermissionTo('الطلاب_الموقوفين.تفعيل');
    }
}
