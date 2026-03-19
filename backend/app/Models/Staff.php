<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'full_name', 'phone_number', 'phone_number_2', 'email', 'address',
        'profile_photo', 'qr_code', 'group_id', 'account_name', 'bank_account', 'ifsc_code',
        'basic_salary', 'wage_calc_type', 'shift_hours', 'ot_type',
        'ot_max_hours', 'ot_max_minutes', 'pf_enabled', 'pf_amount',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'pf_enabled' => 'boolean',
        'basic_salary' => 'decimal:2',
        'pf_amount' => 'decimal:2',
        'group_id' => 'integer',
        'shift_hours' => 'integer',
        'ot_max_hours' => 'integer',
        'ot_max_minutes' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function group()
    {
        return $this->belongsTo(StaffGroup::class, 'group_id');
    }
}
