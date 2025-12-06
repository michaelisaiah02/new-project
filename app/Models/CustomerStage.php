<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerStage extends Model
{
    protected $fillable = [
        'customer_code',
        'stage_number',
        'stage_name',
    ];

    public function documents()
    {
        return $this->belongsToMany(DocumentType::class, 'customer_stage_documents')
            ->withPivot('qr_position')
            ->withTimestamps();
    }
}
