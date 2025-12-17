<?php

namespace App\Http\Controllers\Engineering;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProjectDocumentController extends Controller
{
    public function view(ProjectDocument $projectDocument)
    {
        return view('engineering.projects.view-document', compact('projectDocument'));
    }

    public function upload(Request $request, ProjectDocument $projectDocument)
    {
        $validator = validator(
            $request->all(),
            [
                'file' => 'required|file|mimes:pdf|max:5120', // max 5MB
            ],
            [
                'file.required' => 'File wajib diunggah.',
                'file.file' => 'File tidak valid.',
                'file.mimes' => 'File harus berformat PDF.',
                'file.max' => 'Ukuran file maksimal 5MB.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('file'),
            ], 422);
        }

        $file = $request->file('file');

        // ambil data project
        $project = $projectDocument->project;

        $ext = strtolower($file->getClientOriginalExtension());

        // document_type_code-part_number.ext
        $filename = "{$projectDocument->document_type_code}-{$project->part_name}-{$project->suffix}-{$project->minor_change}.{$ext}";

        // SIMPAN FILE VIA HELPER
        FileHelper::storeDrawingFile(
            $file,
            $project->customer->code,   // customer_code
            $project->model,            // model
            $project->part_number,      // part_number
            $filename
        );

        // UPDATE DATABASE
        $projectDocument->update([
            'file_name' => $filename,
            'actual_date' => now(),
            // checked & approved tetap false
        ]);

        return response()->json([
            'message' => 'Upload success',
            'actual_date' => now()->format('d/m/Y'),
            'status' => $this->statusLabel($projectDocument->refresh()),
        ]);
    }

    public function updateRemark(Request $request, ProjectDocument $projectDocument)
    {
        $projectDocument->update([
            'remark' => $request->remark,
        ]);

        return response()->json(['message' => 'Remark updated']);
    }

    private function statusLabel(ProjectDocument $pd)
    {
        $now = Carbon::now();

        if (! $pd->file_name && $pd->due_date && $now->gt($pd->due_date)) {
            return 'Not Submitted';
        }

        if (
            $pd->file_name &&
            $pd->actual_date &&
            $pd->due_date &&
            $pd->actual_date->gt($pd->due_date)
        ) {
            return 'Delay';
        }

        if ($pd->file_name && ! $pd->checked) {
            return 'Not Yet Checked';
        }

        if ($pd->checked && ! $pd->approved) {
            return 'Not Yet Approved';
        }

        if ($pd->approved) {
            return 'Finish';
        }

        return '-';
    }

    public function checked(ProjectDocument $projectDocument)
    {
        if (! $projectDocument->file_name) {
            return response()->json(['message' => 'No file uploaded'], 422);
        }

        $projectDocument->update([
            'checked' => true,
        ]);

        return response()->json(['message' => 'Document checked']);
    }

    public function approved(ProjectDocument $projectDocument)
    {
        if (! $projectDocument->checked) {
            return response()->json(['message' => 'Document not checked yet'], 422);
        }

        $projectDocument->update([
            'approved' => true,
        ]);

        return response()->json(['message' => 'Document approved']);
    }
}
