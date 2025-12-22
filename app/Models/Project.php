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

    public function statusOngoing(): string
    {
        // Mengembalikan status berdasarkan berikut:
        // Default: On Going (jika tidak memenuhi kondisi di bawah)
        // Some Doc Delay: ada beberapa document dengan status Delay (actual_date > due_date) hanya jika semua dokumen belum finish
        // Not Yet Checked: project dengan semua dokumen sudah finish saat on going tapi belum di cek
        // Not Yet Approved: project dengan semua dokumen sudah finish saat on going belum di approve

        $documents = $this->documents;
        $now = now();
        $allSubmitted = $documents->every(fn($doc) => $doc->file_name !== null);
        $anyDelay = $documents->contains(
            fn($doc) =>
            $doc->actual_date !== null &&
                $doc->due_date !== null &&
                $doc->actual_date->gt($doc->due_date) &&
                $doc->approved_date === null
        );
        if ($anyDelay) {
            return 'Some Doc Delay';
        }
        if ($allSubmitted) {
            $allDocumentsFinished = $documents->every(
                fn($doc) =>
                $doc->actual_date !== null &&
                    $doc->checked_date !== null &&
                    $doc->approved_date !== null &&
                    $this->remark === 'on going'
            );

            $notChecked = $allDocumentsFinished && $this->approvalStatus->ongoing_checked_date === null;
            if ($notChecked) {
                return 'Not Yet Checked';
            }

            $notApproved = $allDocumentsFinished &&
                $this->approvalStatus->ongoing_checked_date !== null &&
                $this->approvalStatus->ongoing_approved_date === null;
            if ($notApproved) {
                return 'Not Yet Approved';
            }

            $notApprovedManagement = $allDocumentsFinished &&
                $this->approvalStatus->ongoing_approved_date !== null &&
                $this->approvalStatus->ongoing_management_approved_date === null;
            if ($notApprovedManagement) {
                return 'Not Yet Approved Management';
            }
        }
        return 'On Going';
    }
}
