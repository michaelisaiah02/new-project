<?php

namespace App\Http\Controllers\Engineering;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectDocument;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\DB;

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
            'created_by_id' => auth()->id(),
            'created_by_name' => auth()->user()->name,
            'created_date' => now()->toDateString(),
            'checked_by_id' => null,
            'checked_by_name' => null,
            'checked_date' => null,
            'approved_by_id' => null,
            'approved_by_name' => null,
            'approved_date' => null,
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
            'checked_by_id' => auth()->id(),
            'checked_by_name' => auth()->user()->name,
            'checked_date' => now()->toDateString(),
        ]);

        return response()->json(['message' => 'Document checked']);
    }

    public function approved(ProjectDocument $projectDocument)
    {
        if (! $projectDocument->file_name) {
            return response()->json(['message' => 'No file uploaded'], 422);
        }
        if (! $projectDocument->checked_by_id || ! $projectDocument->checked_date) {
            return response()->json(['message' => 'Not checked yet'], 422);
        }

        $user = auth()->user();
        $projectDocument->update([
            'approved_by_id' => (string) $user->id,
            'approved_by_name' => $user->name,
            'approved_date' => now()->toDateString(),
        ]);

        // attach QR to file
        $ext = strtolower(pathinfo($projectDocument->file_name, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $this->stampQrToPdf($projectDocument->fresh());
        }

        return response()->json(['message' => 'Approved ok']);
    }

    private function makeQrPng(string $text, string $relPath): string
    {
        $qr = new QrCode(data: $text, size: 220, margin: 1);

        $png = (new PngWriter())->write($qr)->getString();

        Storage::disk('public')->put($relPath, $png);

        return storage_path("app/public/{$relPath}");
    }

    private function qrText(ProjectDocument $pd): string
    {
        $fmt = fn($d) => $d ? Carbon::parse($d)->format('d-m-Y') : '-';

        return implode("\n", [
            "Dibuat oleh {$pd->created_by_name} - " . $fmt($pd->created_date),
            "Diperiksa oleh {$pd->checked_by_name} - " . $fmt($pd->checked_date),
            "Disetujui oleh {$pd->approved_by_name} - " . $fmt($pd->approved_date),
        ]);
    }

    private function stampQrToPdf(ProjectDocument $pd): void
    {
        $project = $pd->project;

        $folder = "{$project->customer->code}/{$project->model}/{$project->part_number}";
        $relPdf = "{$folder}/{$pd->file_name}";
        $absPdf = storage_path("app/public/{$relPdf}");

        // 1) isi QR sesuai permintaan klien
        $qrText = $this->qrText($pd);

        // 2) generate QR PNG
        $qrRel = "qrcodes/{$pd->id}.png";
        $qrAbs = $this->makeQrPng($qrText, $qrRel);

        // 3) ambil posisi dari DB
        $pos = $this->getQrPosition($pd);

        $outAbs = storage_path("app/tmp/stamped_{$pd->id}.pdf");
        if (!is_dir(dirname($outAbs))) mkdir(dirname($outAbs), 0777, true);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($absPdf);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tpl = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tpl);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            // biasanya stamp cukup di page terakhir (lebih “approval-ish”)
            if ($pageNo === $pageCount) {
                $qrW = 25;   // mm
                $m   = 8;    // margin mm
                [$x, $y] = $this->calcQrXY($pos, $size['width'], $size['height'], $qrW, $m);

                $pdf->Image($qrAbs, $x, $y, $qrW, $qrW);
            }
        }

        $pdf->Output($outAbs, 'F');

        // overwrite file asli (jadi View/Download udah include QR)
        Storage::disk('public')->put($relPdf, file_get_contents($outAbs));
    }

    private function getQrPosition(ProjectDocument $pd): string
    {
        return DB::table('customer_stage_documents')
            ->where('customer_stage_id', $pd->customer_stage_id)
            ->where('document_type_code', $pd->document_type_code)
            ->value('qr_position') ?? 'bottom_right';
    }

    private function calcQrXY(string $pos, float $pageW, float $pageH, float $qrW, float $m): array
    {
        return match ($pos) {
            'top_left'     => [$m, $m],
            'top_right'    => [$pageW - $qrW - $m, $m],
            'bottom_left'  => [$m, $pageH - $qrW - $m],
            default        => [$pageW - $qrW - $m, $pageH - $qrW - $m], // bottom_right
        };
    }
}
