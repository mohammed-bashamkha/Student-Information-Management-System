<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Services\UserServices\UserService;

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

    public function update(UpdateUserRequest $request, string $id)
    {
        $validate = $request->validated();
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