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
        // 1. VALIDASI FORMAT (Bawaan Laravel)
        $validator = validator(
            $request->all(),
            [
                'file' => 'required|file|mimes:pdf|max:5120',
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

        // --- 🚀 LOGIKA BARU: CEK PASSWORD PAKE FPDI (NATIVE PHP) ---
        // Kita coba buka file-nya pake FPDI.
        // FPDI Versi Gratis itu GAK BISA baca file password.
        // Jadi kalau dia gagal baca, kita cek alasan gagalnya apa.

        try {
            $pdf = new Fpdi;
            // Coba baca file temp upload langsung
            $pdf->setSourceFile($file->getPathname());

            // Kalau sampai sini gak error, berarti file-nya PDF Jadul & Polos (Aman)

        } catch (\Exception $e) {
            $errorMsg = strtolower($e->getMessage()); // Jadiin huruf kecil biar gampang dicek

            // KASUS 1: File Beneran Dipassword (Encrypted)
            // FPDI biasanya bilang: "File is encrypted!"
            if (str_contains($errorMsg, 'encrypt') || str_contains($errorMsg, 'password')) {
                return response()->json([
                    'message' => 'File PDF terkunci Password. Harap upload file yang tidak dikunci.',
                ], 422);
            }

            // KASUS 2: File Versi Baru / Terkompresi (TAPI GAK DIPASSWORD)
            // Error yang kemarin lo dapet: "probably uses a compression technique"
            // Kalau errornya ini, BERARTI FILE AMAN (Cuma canggih aja).
            // Kita BIARKAN LOLOS, karena nanti pas "Approved" bakal kita repair pake Ghostscript.
            if (str_contains($errorMsg, 'compression') || str_contains($errorMsg, 'parser')) {
                // Lanjut, gak usah diapa-apain. Ini file baik-baik.
            } else {
                // KASUS 3: Error Lain (File Corrupt / Bukan PDF beneran)
                // Opsional: Mau ditolak atau dilolosin terserah lo.
                // Gue saranin tolak kalau errornya aneh-aneh.
                \Illuminate\Support\Facades\Log::error('FPDI Check Error: '.$errorMsg);

                return response()->json(['message' => 'File PDF corrupt atau tidak valid.'], 422);
            }
        }
        // --- END CEK PASSWORD ---

        // 2. LANJUT SIMPAN FILE (Logic Lama)
        $project = $projectDocument->project;
        $ext = strtolower($file->getClientOriginalExtension());

        $filename = "{$projectDocument->document_type_code}-{$project->part_number}-{$project->suffix}-{$project->minor_change}.{$ext}";

        FileHelper::storeDrawingFile(
            $file,
            $project->customer->code,
            $project->model,
            $project->part_number,
            $filename
        );

        // 3. UPDATE DATABASE
        $projectDocument->update([
            'file_name' => $filename,
            'actual_date' => now(),
            'created_by_id' => auth()->id(),
            'created_by_name' => auth()->user()->name,
            'created_date' => now()->toDateString(),
            // Reset status approval karena upload file baru
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
            ->size(500) // Resolusi tinggi biar tajem pas dikecilin di PDF
            ->margin(0)
            ->errorCorrection('L')
            ->backgroundColor(255, 255, 255) // Putih biar kontras
            ->color(0, 0, 0)
            ->generate($qrContent, $qrTempPath);

        // 6. Proses Stamping ke PDF (FPDI)
        try {
            // REVISI PENTING: Paksa constructor 'mm' biar 1 unit = 1 milimeter.
            // Pake backslash \setasign\Fpdi\Fpdi biar yakin class yg bener
            $pdf = new Fpdi('P', 'mm');

            // Coba load file asli dulu
            try {
                $pageCount = $pdf->setSourceFile($fullPath);
            } catch (\Exception $e) {
                // 1. Path EXE & Folder Temp
                $gsBin = 'C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64c.exe';
                $tempDir = storage_path('app/temp_pdf');

                if (! file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $repairedPath = $tempDir.'/repair_'.time().'.pdf';
                $fixedGsBin = str_replace('/', '\\', $gsBin);
                $fixedOutput = str_replace('/', '\\', $repairedPath);
                $fixedInput = str_replace('/', '\\', $fullPath);
                $fixedTemp = str_replace('/', '\\', $tempDir);

                $command = sprintf(
                    '"%s" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sTMPDIR="%s" -sOutputFile="%s" "%s"',
                    $fixedGsBin,
                    $fixedTemp,
                    $fixedOutput,
                    $fixedInput
                );

                $output = shell_exec($command.' 2>&1');

                if (! file_exists($repairedPath) || filesize($repairedPath) === 0) {
                    throw new \Exception('Ghostscript Failed! Output CMD: '.$output);
                }

                $pageCount = $pdf->setSourceFile($repairedPath);
                $tempRepairedFile = $repairedPath;
            }

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Tambah halaman & pakai template lama
                // Karena constructor dipaksa 'mm', size template akan otomatis dikonversi ke mm oleh FPDI
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
                $pdf->SetAutoPageBreak(false);
                $pdf->SetMargins(0, 0, 0);

                if ($pageNo === 1) {
                    // === KONFIGURASI UKURAN 10mm (1cm) ===
                    $boxWidth = 12;     // LEBAR TOTAL 10mm
                    $headerHeight = 3.6; // Tinggi Header 2.5mm
                    $boxHeight = $headerHeight + $boxWidth; // Total Tinggi 12.5mm
                    $margin = 10; // Jarak dari pinggir kertas
                    $qrPadding = 1.5; // Padding super tipis

                    $x = 0;
                    $y = 0;

                    // Logika Posisi
                    switch ($qrPosition) {
                        case 'top_left':
                            $x = $margin;
                            $y = $margin;
                            break;
                        case 'top_right':
                            $x = $size['width'] - $boxWidth - $margin;
                            $y = $margin;
                            break;
                        case 'bottom_left':
                            $x = $margin;
                            $y = $size['height'] - $boxHeight - $margin;
                            break;
                        case 'bottom_right':
                        default:
                            $x = $size['width'] - $boxWidth - $margin;
                            $y = $size['height'] - $boxHeight - $margin;
                            break;
                    }

                    // 1. GAMBAR KOTAK (BORDER)
                    $pdf->SetDrawColor(0, 0, 0);
                    $pdf->SetLineWidth(0.1);     // Garis tipis 0.1mm biar gak tebel menuhin kotak
                    $pdf->SetFillColor(255, 255, 255);

                    // Gambar Kotak Utama
                    $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'DF');

                    // Gambar Garis Pemisah Header
                    $pdf->Line($x, $y + $headerHeight, $x + $boxWidth, $y + $headerHeight);

                    // 2. TULIS TEXT "CAR Digital Approved" (MICRO SIZE)
                    // Pake Font Size 3 biar muat di lebar 10mm
                    $pdf->SetFont('Arial', 'B', 5);
                    $pdf->SetTextColor(0, 0, 0);

                    $pdf->SetXY($x, $y + 0.2);
                    $pdf->Cell($boxWidth, 1.5, 'CAR Digital', 0, 0, 'C');
                    $pdf->SetXY($x, $y + 1.6);
                    $pdf->Cell($boxWidth, 1.5, 'Approved', 0, 0, 'C');

                    // 3. TEMPEL QR CODE
                    $qrSize = $boxWidth - ($qrPadding * 2);
                    $pdf->Image($qrTempPath, $x + $qrPadding, $y + $headerHeight + $qrPadding, $qrSize, $qrSize);
                }
            }

            // Simpan (Overwrite file asli)
            $pdf->Output('F', $fullPath);
        } catch (\Exception $e) {
            // Revert status DB jika gagal
            $projectDocument->update([
                'approved_by_id' => null,
                'approved_by_name' => null,
                'approved_date' => null,
            ]);

            return response()->json(['message' => 'Gagal memproses PDF: '.$e->getMessage()], 500);
        } finally {
            // CLEANUP
            if (isset($qrTempPath) && file_exists($qrTempPath)) {
                unlink($qrTempPath);
            }
            if (isset($tempRepairedFile) && file_exists($tempRepairedFile)) {
                unlink($tempRepairedFile);
            }
        }

        // 7. Cleanup
        if (file_exists($qrTempPath)) {
            unlink($qrTempPath);
        }

        return response()->json(['message' => 'Approved ok & PDF Signed']);
    }
}
