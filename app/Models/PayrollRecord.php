<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    protected $table = 'payroll_records';

    protected $fillable = [
        'staff_id', 'month', 'year', 'basic_amount', 'one_day_salary',
        'days_in_month', 'days_absent', 'absent_deduction', 'days_overtime',
        'overtime_amount', 'advance_amount', 'final_pay', 'paid_in_bank',
        'paid_pf', 'paid_cash', 'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'staff_id' => 'integer',
        'month' => 'integer',
        'year' => 'integer',
        'basic_amount' => 'decimal:2',
        'one_day_salary' => 'decimal:2',
        'absent_deduction' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'final_pay' => 'decimal:2',
        'paid_in_bank' => 'decimal:2',
        'paid_pf' => 'decimal:2',
        'paid_cash' => 'decimal:2',
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
