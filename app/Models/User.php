<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Append name and avatar for frontend compatibility
    protected $appends = ['name', 'avatar'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    // Return name as "LastName, FirstName"
    public function getNameAttribute()
    {
        return $this->lastName . ', ' . $this->firstName;
    }

    // Get avatar from employee relationship
    public function getAvatarAttribute()
    {
        if ($this->employee && $this->employee->avatar) {
            return $this->employee->avatar_url;
        }
        return null;
    }

    public function isActive()
    {
        return $this->status === 'Active';
    }

    public function isHR()
    {
        return $this->role === 'hr' || $this->role === 'admin';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Inactive');
    }
}