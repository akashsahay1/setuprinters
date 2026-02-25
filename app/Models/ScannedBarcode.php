<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScannedBarcode extends Model
{
    protected $table = 'scanned_barcodes';

    protected $fillable = ['user_id', 'barcode', 'selfie', 'is_deleted'];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'user_id');
    }
}
