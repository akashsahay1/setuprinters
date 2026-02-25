<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'employee_id',
        'full_name',
        'phone_number',
        'phone_number_2',
        'email',
        'password',
        'user_role',
        'address',
        'profile_photo',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_deleted' => 'boolean',
            'employee_id' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
