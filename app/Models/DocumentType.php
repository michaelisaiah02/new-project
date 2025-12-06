<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function stages()
    {
        return $this->belongsToMany(CustomerStage::class, 'customer_stage_documents')
            ->withPivot('qr_position')
            ->withTimestamps();
    }
}
