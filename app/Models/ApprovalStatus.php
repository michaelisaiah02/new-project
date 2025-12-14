<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStatus extends Model
{
    protected $fillable = [
        'part_number',
        'created_by_id',
        'created_by_name',
        'created_date',
        'checked_by_id',
        'checked_by_name',
        'checked_date',
        'approved_by_id',
        'approved_by_name',
        'approved_date',
        'management_approved_by_id',
        'management_approved_by_name',
        'management_approved_date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'part_number', 'part_number');
    }
}
