<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffGroup extends Model
{
    protected $fillable = ['name', 'is_deleted'];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'group_id');
    }
}
