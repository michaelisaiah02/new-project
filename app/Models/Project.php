<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'customer_name_snapshot',
        'model',
        'part_number',
        'part_name',
        'part_type',
        'drawing_2d',
        'drawing_3d',
        'qty',
        'eee_number',
        'suffix',
        'drawing_number',
        'drawing_revision_date',
        'material_on_drawing',
        'receive_date_sldg',
        'sldg_number',
        'masspro_target',
        'minor_change',
        'remark',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'code');
    }

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class, 'project_id');
    }

    public function approvalStatus()
    {
        return $this->hasOne(ApprovalStatus::class, 'project_id');
    }

    public function allDocumentsApproved(): bool
    {
        return ! $this->documents()->where('approved_date', null)->exists();
    }

    public function canShowCheckedButton(User $user): bool
    {
        if (! $this->allDocumentsApproved()) {
            return false;
        }

        if (! $user->checked) {
            return false;
        }

        $status = $this->approvalStatus;

        return
            $status->ongoing_checked_by_id === null &&
            $status->ongoing_checked_date === null;
    }

    public function canShowApprovedButton(User $user): bool
    {
        if (! $this->allDocumentsApproved()) {
            return false;
        }

        $status = $this->approvalStatus;

        // Engineering approval
        if (
            $user->approved &&
            $user->department->type() === 'engineering'
        ) {
            return
                $status->ongoing_checked_by_id !== null &&
                $status->ongoing_checked_date !== null &&
                $status->ongoing_approved_by_id === null &&
                $status->ongoing_approved_date === null;
        }

        // Management approval
        if (
            $user->approved &&
            $user->department->type() === 'management'
        ) {
            return
                $status->ongoing_approved_by_id !== null &&
                $status->ongoing_approved_date !== null &&
                $status->ongoing_management_approved_by_id === null &&
                $status->ongoing_management_approved_date === null;
        }

        return false;
    }
}
