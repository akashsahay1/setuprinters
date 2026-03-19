<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['name', 'date', 'is_yearly', 'financial_year', 'is_deleted'];

    protected $casts = [
        'date' => 'date',
        'is_deleted' => 'boolean',
        'is_yearly' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public static function deriveFinancialYear($date): string
    {
        $d = \Carbon\Carbon::parse($date);
        if ($d->month >= 4) {
            return $d->year . '-' . ($d->year + 1);
        }
        return ($d->year - 1) . '-' . $d->year;
    }

    public static function currentFinancialYear(): string
    {
        return self::deriveFinancialYear(now());
    }
}
