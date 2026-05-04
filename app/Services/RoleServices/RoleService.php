<?php

namespace App\Services\RoleServices;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Permission\Models\Role;

class RoleService
{
    use AuthorizesRequests;
    public function getRoles()
    {
        $this->authorize('manageRole');
        $roles = Role::with('permissions')->get();
        return $roles;
    }

    public function createRole($validated)
    {
        $this->authorize('manageRole');
        if (Role::where('name', $validated['name'])->exists()) {
            throw new Exception("هذا الدور موجود بالفعل");
        }
        $role = Role::create($validated);
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }
        return $role;
    }

    public function updateRole($validated, string $id)
    {
        $role = Role::findOrFail($id);
        if (Role::where('name', $validated['name'])->where('id', '!=', $id)->exists()) {
            throw new Exception("هذا الدور موجود بالفعل");
        }
        $this->authorize('manageRole', $role);
        $role->update($validated);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }
        return $role;
    }

    public function getRole(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->authorize('manageRole', $role);
        return $role;
    }

    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('manageRole', $role);
        $role->delete();
        return $role;
    }
}
