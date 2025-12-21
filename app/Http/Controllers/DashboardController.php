<?php

namespace App\Http\Controllers;

use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $type = auth()->user()->department->type();

        return match ($type) {
            'marketing' => redirect()->route('marketing'),
            'engineering' => redirect()->route('engineering'),
            'management' => redirect()->route('management'),
            default => abort(403, 'Unauthorized action.'),
        };
    }

    public function marketing()
    {
        return view('marketing');
    }

    public function engineering()
    {
        $user = auth()->user();
        $department = $user->department->type();

        // ---------------------------------------------------------
        // 1. LOGIC NEW PROJECTS (Sort by SQL)
        // ---------------------------------------------------------

        // Tentuin dulu status prioritas berdasarkan role user
        $priorityRemark = null;

        if ($department === 'management') {
            $priorityRemark = 'not approved management';
        } elseif ($user->checked) { // Asumsi: User Checker
            $priorityRemark = 'not checked';
        } elseif ($user->approved) { // Asumsi: User Approver
            $priorityRemark = 'not approved'; // Atau 'approved' sesuai flow lo
        }

        // Base Query untuk New Projects
        $newProjectsQuery = Project::with('customer')
            ->whereIn('remark', ['new', 'not checked', 'not approved', 'not approved management', 'approved']);

        // Filter departemen kalau bukan management
        if ($department !== 'management') {
            $newProjectsQuery->whereHas('customer', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        // APPLY SORTING CUSTOM
        if ($priorityRemark) {
            // Logic: Kalau remark == priority, kasih nilai 0 (paling atas), sisanya 1
            $newProjectsQuery->orderByRaw("CASE WHEN remark = ? THEN 0 ELSE 1 END", [$priorityRemark]);
        }

        // Default sort selanjutnya (misal berdasarkan tanggal atau remark abjad)
        $newProjects = $newProjectsQuery->orderBy('remark')->get();


        // ---------------------------------------------------------
        // 2. LOGIC ONGOING PROJECTS (Sort by Collection)
        // ---------------------------------------------------------

        // Base Query Ongoing
        // PENTING: Wajib Eager Loading relasi yg dipake di func statusOngoing()
        // biar gak kena N+1 Query problem pas looping.
        $ongoingQuery = Project::with(['customer', 'documents', 'approvalStatus'])
            ->where('remark', 'on going');

        if ($department !== 'management') {
            $ongoingQuery->whereHas('customer', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        // Ambil datanya dulu jadi Collection
        $ongoingProjectsRaw = $ongoingQuery->get();

        // Tentuin target status string dari function statusOngoing()
        $targetStatus = null;
        if ($department === 'management') {
            $targetStatus = 'Not Yet Approved Management';
        } elseif ($user->checked) {
            $targetStatus = 'Not Yet Checked';
        } elseif ($user->approved && $user->department->type() === 'engineering') {
            $targetStatus = 'Not Yet Approved';
        } else {
            $targetStatus = 'On Going';
        }

        // Sorting Collection via PHP
        $ongoingProjects = $ongoingProjectsRaw->sortBy(function ($project) use ($targetStatus) {
            // Kalau gak ada target status (user biasa), gak usah di sort aneh-aneh
            if (!$targetStatus) return 0;

            // Cek status kalkulasinya
            $currentStatus = $project->statusOngoing();

            // Kalau statusnya match sama target user, taruh di atas (return 0)
            // Sisanya taruh di bawah (return 1)
            return $currentStatus === $targetStatus ? 0 : 1;
        });

        return view('engineering', compact('newProjects', 'ongoingProjects'));
    }

    public function management()
    {
        return view('management');
    }
}
