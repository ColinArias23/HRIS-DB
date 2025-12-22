<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'firstName',
        'lastName',
        'middleName',
        'suffix',
        'email',
        'contact',
        'position',
        'department',
        'sex',
        'age',
        'civil_status',
        'pag_ibig',
        'philhealth',
        'tin',
        'landbank',
        'gsis',
        'status',
        'city',
        'region',
        'brgy',
        'zipcode',
        'birthdate',
        'salary',
        'employeeType',
        'employee_type',
        'avatar',
        'incase_of_emergency',
        'blood_type',
        'citizenship',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'salary' => 'decimal:2',
        'age' => 'integer',
    ];

    // Frontend expects these fields
    protected $appends = ['full_name', 'full_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Frontend uses 'avatar' directly (not avatar_url)
    public function getAvatarAttribute($value)
    {
        if ($value) {
            return url($value);
        }
        return null;
    }

    public function getFullNameAttribute()
    {
        $name = trim("{$this->firstName} {$this->middleName} {$this->lastName}");
        return $this->suffix ? "{$name} {$this->suffix}" : $name;
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([$this->brgy, $this->city, $this->region, $this->zipcode]);
        return implode(', ', $parts);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'Inactive');
    }

    public function scopeByEmployeeType($query, $type)
    {
        return $query->where('employee_type', $type);
    }
}