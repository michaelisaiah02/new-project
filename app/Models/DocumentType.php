<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
    ];

    public function customerStages()
    {
        return $this->belongsToMany(
            CustomerStage::class,
            'customer_stage_documents'
        );
    }

    public function projectDocuments()
    {
        return $this->hasMany(ProjectDocument::class, 'document_type_code', 'code');
    }
}
