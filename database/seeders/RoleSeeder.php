<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $rolesAndPermissions = [
            'إدارة الطلاب' => [
                'الطلاب.ادارة',
                'الطلاب.عرض',
                'الطلاب.انشاء',
                'الطلاب.تحديث',
                'الطلاب.حذف',
                'الطلاب.استيراد',
                'الطلاب.تصدير',
                'الطلاب.توليد_تقارير',
            ],
            'إدارة التحويلات والقبول المؤقت' => [
                'الطلاب.عرض',
                'التحويلات_القبول.ادارة',
                'التحويلات_القبول.عرض',
                'التحويلات_القبول.انشاء',
                'التحويلات_القبول.تحديث',
                'التحويلات_القبول.حذف',
                'التحويلات_القبول.توليد_تقارير',
            ],
            'ادارة درجات الطلاب' => [
                'الطلاب.عرض',
                'الدرجات.ادارة',
                'الدرجات.عرض',
                'الدرجات.انشاء',
                'الدرجات.تحديث',
                'الدرجات.حذف',
                'الدرجات.توليد_تقارير',
            ],
            'ادارة الطلاب الموقوفين' => [
                'الطلاب.عرض',
                'التحويلات_القبول.توليد_تقارير',
                'التحويلات_القبول.عرض',
                'التحويلات_القبول.انشاء',
                'التحويلات_القبول.تحديث',
                'الطلاب_الموقوفين.عرض',
                'الطلاب_الموقوفين.تفعيل'
            ]
        ];

        foreach ($rolesAndPermissions as $roleName => $permissionsList) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'sanctum'
            ]);

            $permissions = Permission::whereIn('name', array_unique($permissionsList))
                ->where('guard_name', 'sanctum')
                ->get();

            $role->syncPermissions($permissions);
        }
    }
}
