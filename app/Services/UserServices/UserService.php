<?php

namespace App\Services\UserServices;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use AuthorizesRequests;
    public function getUsers()
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('roles')->paginate(5);
        return $users;
    }

    public function createUser($validate)
    {
        $this->authorize('create', User::class);
        $user = User::create([
            'name' => $validate['name'],
            'email' => $validate['email'],
            'password' => Hash::make($validate['password']),
            ]);
    
            if(!empty($validate['roles']))
            {
                $user->assignRole($validate['roles']);
            }
        return $user->load('roles');
    }

    public function getUserById(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        $this->authorize('view', $user);
        return $user;
    }

    public function editUser(Request $request, $validate, string $id)
    {
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
        return $user->load('roles');
    }

    public function deleteUser(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        $user->delete();
        return $user;
    }
}
