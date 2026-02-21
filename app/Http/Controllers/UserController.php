<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('roles')->paginate(5);
        return response()->json($users,200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $validate = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'roles' => 'array|nullable',
        'roles.*' => 'string|exists:roles,name'
        ]);

        $user = User::create([
        'name' => $validate['name'],
        'email' => $validate['email'],
        'password' => Hash::make($validate['password']),
        ]);

        if(!empty($validate['roles']))
        {
            $user->assignRole($validate['roles']);
        }
        return response()->json([
            'message' => 'تم إنشاء المستخدم بنجاح',
            'user' => $user->load('roles')
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        $this->authorize('view', $user);
        return response()->json($user,200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|max:255|unique:users,email,' .$id,
        'password' => 'nullable|string|min:8|confirmed',
        'roles' => 'array|nullable',
        'roles.*' => 'string|exists:roles,name'
        ]);
        $user = User::with('roles')->findOrFail($id);
        $this->authorize('update', $user);

        if($request->filled(['name','email','password']))
        {
            $user->update([
                'name' => $validate['name'],
                'email' => $validate['email'],
                'password' => Hash::make($validate['password']),
            ]);
        }
        if(!empty($validate['roles']))
        {
            $user->assignRole($validate['roles']);
        }
        return response()->json([
            'message' => 'تم تعديل المستخدم بنجاح',
            'user' => $user->load('roles')
        ],202);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json([
            'message' => 'تم حذف المستخدم بنجاح'
        ],200);
    }
}
