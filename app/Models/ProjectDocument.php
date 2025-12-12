<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_part_number',
        'document_type_code',
        'customer_stage_id',
        'due_date',
        'actual_date',
        'checked',
        'approved',
    ];

    protected $casts = [
        'due_date' => 'date',
        'actual_date' => 'date',
        'checked' => 'boolean',
        'approved' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_part_number', 'part_number');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_code', 'code');
    }

    public function stage()
    {
        return $this->belongsTo(CustomerStage::class, 'customer_stage_id');
    }
}
