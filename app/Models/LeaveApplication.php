<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $table = 'leave_applications';

    protected $fillable = [
        'staff_id', 'leave_date', 'leave_type', 'reason', 'status', 'is_deleted',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'is_deleted' => 'boolean',
        'staff_id' => 'integer',
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
