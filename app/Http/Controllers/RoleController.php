<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
      $this->authorize('manageRole');
      $roles = Role::with('permissions')->paginate(5);
      return response()->json($roles,200);
    }

    public function create()
    {
      //
    }

    public function store(Request $request)
    {
      $this->authorize('manageRole');
      $validated = $request->validate([
        'name' => 'required|string|unique:roles,name',
        'permissions' => 'array|nullable',
        'permissions.*' => 'string|exists:permissions,name'
      ]);

      if(Role::where('name', $validated['name'])->exists()) {
        return back()->withErrors(['name' => 'هذا الدور موجود بالفعل.'])->withInput();
      }
      $role = Role::create($validated);
      if(!empty($validated['permissions'])) {
        $role->syncPermissions($validated['permissions']);
      }
      return response()->json([
        'message' => 'تم إنشاء الدور بنجاح',
        'role' => $role->load('permissions')
      ],201);
    }

    public function edit(Role $role)
    {
      //
    }

    public function update(Request $request, Role $role)
    {
      $this->authorize('manageRole');
      $validated = $request->validate([
        'name' => 'required|string|unique:roles,name,' . $role->id,
        'permissions' => 'array|nullable',
        'permissions.*' => 'string|exists:permissions,name'
      ]);

      if(Role::where('name', $validated['name'])->where('id', '!=', $role->id)->exists()) {
        return back()->withErrors(['name' => 'هذا الدور موجود بالفعل.'])->withInput();
      }
      $role->update($validated);

      if(!empty($validated['permissions'])) {
        $role->syncPermissions($validated['permissions']);
      } else {
        $role->syncPermissions([]);
      }
      return response()->json([
        'message' => 'تم تعديل الدور بنجاح',
        'role' => $role->load('permissions')
      ],200);
    }

    public function show($id)
    {
      $role = Role::with('permissions')->findOrFail($id);
      $this->authorize('manageRole');
      return response()->json($role,200);
    }
    public function destroy(Role $role)
    {
      $this->authorize('manageRole');
      $role->delete();
      return redirect()->route('roles.index')->with('success', 'تم حذف الدور بنجاح.');
    }
}
