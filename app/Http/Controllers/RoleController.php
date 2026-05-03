<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest\StoreRoleRequest;
use App\Http\Requests\RoleRequest\UpdateRoleRequest;
use App\Services\RoleServices\RoleService;

class RoleController extends Controller
{
  protected $roleService;
  public function __construct(RoleService $roleService)
  {
    $this->roleService = $roleService;
  }
  public function index()
  {
    $roles = $this->roleService->getRoles();
    return response()->json($roles, 200);
  }

  public function store(StoreRoleRequest $request)
  {
    $validated = $request->validated();
    $role = $this->roleService->createRole($validated);
    return response()->json([
      'message' => 'تم إنشاء الدور بنجاح',
      'role' => $role->load('permissions')
    ], 201);
  }

  public function update(UpdateRoleRequest $request, string $id)
  {
    $validated = $request->validated();
    $role = $this->roleService->updateRole($validated, $id);
    return response()->json([
      'message' => 'تم تعديل الدور بنجاح',
      'role' => $role->load('permissions')
    ], 200);
  }

  public function show(string $id)
  {
    $role = $this->roleService->getRole($id);
    return response()->json($role, 200);
  }
  
  public function destroy(string $id)
  {
    $role = $this->roleService->deleteRole($id);
    return response()->json([
      'message' => 'تم حذف الدور بنجاح',
      'role' => $role
    ], 200);
  }
}
