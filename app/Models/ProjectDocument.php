<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'document_type_code',
        'customer_stage_id',
        'due_date',
        'actual_date',
        'file_name',
        'created_by_id',
        'created_by_name',
        'created_date',
        'checked_by_id',
        'checked_by_name',
        'checked_date',
        'approved_by_id',
        'approved_by_name',
        'approved_date',
        'remark',
    ];

    protected $casts = [
        'due_date' => 'date',
        'actual_date' => 'date',
        'created_date' => 'date',
        'checked_date' => 'date',
        'approved_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
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
