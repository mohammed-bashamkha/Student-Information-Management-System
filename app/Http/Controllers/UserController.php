<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Services\UserServices\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index()
    {
        $users = $this->userService->getUsers();
        return response()->json($users,200);
    }

    public function store(StoreUserRequest $request)
    {
        $validate = $request->validated();
        $user = $this->userService->createUser($validate);

        return response()->json([
            'message' => 'تم إنشاء المستخدم بنجاح',
            'data' => $user
        ],201);
    }

    public function show(string $id)
    {
        $user = $this->userService->getUserById($id);
        return response()->json($user,200);
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|max:255|unique:users,email,' .$id,
        'password' => 'nullable|string|min:8|confirmed',
        'roles' => 'array|nullable',
        'roles.*' => 'string|exists:roles,name'
        ]);
        $user = $this->userService->editUser($request, $validate, $id);
        return response()->json([
            'message' => 'تم تعديل المستخدم بنجاح',
            'data' => $user
        ],202);
    }

    public function destroy(string $id)
    {
        $user = $this->userService->deleteUser($id);
        return response()->json([
            'message' => 'تم حذف المستخدم بنجاح',
            'data' => $user->name

        ],200);
    }
}