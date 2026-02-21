<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function schools() {
        return $this->hasMany(School::class, 'created_by');
    }

    public function levels() {
        return $this->hasMany(Level::class, 'created_by');
    }

    public function classes() {
        return $this->hasMany(SchoolClass::class, 'created_by');
    }

    public function subjects() {
        return $this->hasMany(Subject::class, 'created_by');
    }

    public function students() {
        return $this->hasMany(Student::class, 'created_by');
    }

    public function grades() {
        return $this->hasMany(Grade::class, 'created_by');
    }

    public function finalResults() {
        return $this->hasMany(FinalResult::class, 'created_by');
    }
}
