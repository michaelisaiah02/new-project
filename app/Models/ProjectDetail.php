<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_part_number',
        'document_type_id',
        'assigned_date',
        'actual_date',
        'checked',
        'approved',
    ];

    protected $casts = [
        'assigned_date' => 'date',
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
        return $this->belongsTo(DocumentType::class);
    }
}
