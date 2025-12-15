<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'firstName',
        'middleName',
        'lastName',
        'suffix',
        'email',
        'contact',
        'position',
        'department',
        'gender',
        'status',
        'address',
        'birthdate',
        'salary',
        'employeeType',
        'avatar',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'salary' => 'decimal:2',
    ];

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? url($this->avatar) : null;
    }
}
