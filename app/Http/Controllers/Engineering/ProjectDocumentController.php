<?php

namespace App\Http\Controllers\Engineering;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        // 1. Validasi Awal
        if (! $projectDocument->file_name) {
            return response()->json(['message' => 'No file uploaded'], 422);
        }

        // Folder sesuai dengan format: Model/PartNumber/nama_file.pdf
        $relativePath = $projectDocument->project->customer_code.'/'.$projectDocument->project->model.'/'.$projectDocument->project->part_number.'/'.$projectDocument->file_name;
        $fullPath = storage_path('app/public/'.$relativePath);

        if (! file_exists($fullPath)) {
            return response()->json(['message' => 'File PDF fisik tidak ditemukan di server.'.$fullPath], 404);
        }

        if (! $projectDocument->checked_by_id || ! $projectDocument->checked_date) {
            return response()->json(['message' => 'Not checked yet'], 422);
        }

        // 2. Update Database Dulu (Supaya data 'Approved By' masuk)
        $user = auth()->user();
        $now = now();

        $projectDocument->update([
            'approved_by_id' => (string) $user->id,
            'approved_by_name' => $user->name,
            'approved_date' => $now->toDateString(),
        ]);

        // 3. Siapkan Konten QR Code
        // Format tanggal: d-m-Y (sesuai request)
        // Gunakan Carbon::parse() untuk jaga-jaga kalau format di DB string/date
        $createdDate = Carbon::parse($projectDocument->created_date)->format('d-m-Y');
        $checkedDate = Carbon::parse($projectDocument->checked_date)->format('d-m-Y');
        $approvedDate = $now->format('d-m-Y');

        $qrContent = "Dibuat oleh {$projectDocument->created_by_name} - {$createdDate}\n";
        $qrContent .= "Diperiksa oleh {$projectDocument->checked_by_name} - {$checkedDate}\n";
        $qrContent .= "Disetujui oleh {$user->name} - {$approvedDate}";

        // 4. Cari Posisi QR dari Tabel Pivot
        // Kita query manual ke tabel pivot karena lebih cepet daripada lewat relasi complex
        $qrPosition = DB::table('customer_stage_documents')
            ->where('customer_stage_id', $projectDocument->customer_stage_id)
            ->where('document_type_code', $projectDocument->document_type_code)
            ->value('qr_position'); // Mengembalikan string 'top_left', 'bottom_right', dll.

        // Default posisi kalau di setting gak ketemu
        $qrPosition = $qrPosition ?? 'bottom_right';

        // 5. Generate QR Code Image (Temp)
        $qrTempPath = sys_get_temp_dir().'/qr_'.uniqid().'.png';

        // Karena Imagick aktif, kita bisa pake format 'png' dengan aman
        QrCode::format('png')
            ->size(400) // Resolusi tinggi biar tajem pas dikecilin di PDF
            ->margin(1)
            ->errorCorrection('L')
            ->backgroundColor(255, 255, 255) // Putih biar kontras
            ->color(0, 0, 0)
            ->generate($qrContent, $qrTempPath);

        // 6. Proses Stamping ke PDF (FPDI)
        try {
            $pdf = new Fpdi;
            $pageCount = $pdf->setSourceFile($fullPath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Tambah halaman & pakai template lama
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                // Logika Posisi Dinamis
                $qrSize = 25; // Ukuran QR di PDF (mm)
                $margin = 10; // Jarak dari pinggir kertas (mm)
                $x = 0;
                $y = 0;

                // Hitung X dan Y berdasarkan konfigurasi DB
                switch ($qrPosition) {
                    case 'top_left':
                        $x = $margin;
                        $y = $margin;
                        break;
                    case 'top_right':
                        $x = $size['width'] - $qrSize - $margin;
                        $y = $margin;
                        break;
                    case 'bottom_left':
                        $x = $margin;
                        $y = $size['height'] - $qrSize - $margin;
                        break;
                    case 'bottom_right':
                    default:
                        $x = $size['width'] - $qrSize - $margin;
                        $y = $size['height'] - $qrSize - $margin;
                        break;
                }

                // Tempel Image
                $pdf->Image($qrTempPath, $x, $y, $qrSize, $qrSize);
            }

            // Simpan (Overwrite file asli)
            $pdf->Output('F', $fullPath);
        } catch (\Exception $e) {
            // Hapus file temp kalau gagal biar gak nyampah
            if (file_exists($qrTempPath)) {
                unlink($qrTempPath);
            }

            // Revert database update kalau PDF gagal (Opsional, tapi best practice)
            $projectDocument->update([
                'approved_by_id' => null,
                'approved_by_name' => null,
                'approved_date' => null,
            ]);

            return response()->json(['message' => 'Gagal memproses PDF: '.$e->getMessage()], 500);
        }

        // 7. Cleanup
        if (file_exists($qrTempPath)) {
            unlink($qrTempPath);
        }

        return response()->json(['message' => 'Approved ok & PDF Signed']);
    }
}
