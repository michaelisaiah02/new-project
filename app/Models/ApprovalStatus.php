<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStatus extends Model
{
    protected $fillable = [
        'project_id',
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
        'ongoing_checked_by_id',
        'ongoing_checked_by_name',
        'ongoing_checked_date',
        'ongoing_approved_by_id',
        'ongoing_approved_by_name',
        'ongoing_approved_date',
        'ongoing_management_approved_by_id',
        'ongoing_management_approved_by_name',
        'ongoing_management_approved_date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
