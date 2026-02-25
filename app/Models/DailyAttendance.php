<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAttendance extends Model
{
    protected $table = 'daily_attendances';

    protected $fillable = [
        'staff_id', 'date', 'check_in', 'check_out', 'total_hours',
        'status', 'is_ot', 'ot_hours', 'base_wage', 'ot_wage', 'is_deleted',
    ];

    protected $casts = [
        'date' => 'date',
        'is_deleted' => 'boolean',
        'is_ot' => 'boolean',
        'staff_id' => 'integer',
        'total_hours' => 'decimal:2',
        'ot_hours' => 'decimal:2',
        'base_wage' => 'decimal:2',
        'ot_wage' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
