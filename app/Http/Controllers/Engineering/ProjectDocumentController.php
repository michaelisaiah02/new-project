<?php

namespace App\Http\Controllers\Engineering;

use Carbon\Carbon;
use setasign\Fpdi\Fpdi;
use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use App\Models\ProjectDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Process;
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
            $pdf = new Fpdi();
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
                \Illuminate\Support\Facades\Log::error("FPDI Check Error: " . $errorMsg);
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
        $relativePath = $projectDocument->project->customer_code . '/' . $projectDocument->project->model . '/' . $projectDocument->project->part_number . '/' . $projectDocument->file_name;
        $fullPath = storage_path('app/public/' . $relativePath);

        if (! file_exists($fullPath)) {
            return response()->json(['message' => 'File PDF fisik tidak ditemukan di server.' . $fullPath], 404);
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
        $qrTempPath = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';

        // Karena Imagick aktif, kita bisa pake format 'png' dengan aman
        QrCode::format('png')
            ->size(400) // Resolusi tinggi biar tajem pas dikecilin di PDF
            ->margin(0)
            ->errorCorrection('H')
            ->backgroundColor(255, 255, 255) // Putih biar kontras
            ->color(0, 0, 0)
            ->generate($qrContent, $qrTempPath);

        // 6. Proses Stamping ke PDF (FPDI)
        try {
            $pdf = new Fpdi();

            // Coba load file asli dulu
            try {
                $pageCount = $pdf->setSourceFile($fullPath);
            } catch (\Exception $e) {
                // --- MASUK LOGIC REPAIR GHOSTSCRIPT ---

                // 1. Path EXE & Folder Temp (Sama kayak tadi)
                $gsBin = 'C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64c.exe';
                $tempDir = storage_path('app/temp_pdf'); // Folder temp kita

                if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

                // 2. Setup Nama File
                $repairedPath = $tempDir . '/repair_' . time() . '.pdf';

                // 3. Normalisasi Path (Wajib Backslash buat Windows)
                $fixedGsBin  = str_replace('/', '\\', $gsBin);
                $fixedOutput = str_replace('/', '\\', $repairedPath);
                $fixedInput  = str_replace('/', '\\', $fullPath);
                $fixedTemp   = str_replace('/', '\\', $tempDir); // <--- Path Temp Folder juga dinormalisasi

                // 4. Command Sakti
                // Tambahan: -sTMPDIR="..." buat maksa GS pake folder temp kita, jangan folder system yg restricted.
                $command = sprintf(
                    '"%s" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sTMPDIR="%s" -sOutputFile="%s" "%s"',
                    $fixedGsBin,
                    $fixedTemp, // <--- Param baru
                    $fixedOutput,
                    $fixedInput
                );

                // 5. Eksekusi Pake 'shell_exec' (Lebih Barbar tapi Jujur)
                // Tambahin '2>&1' di belakang buat nangkep pesan error (stderr) ke output biasa
                $output = shell_exec($command . ' 2>&1');

                // 6. Validasi
                // Cek apakah file output beneran jadi?
                if (!file_exists($repairedPath) || filesize($repairedPath) === 0) {
                    // Kalau gagal, lempar error beserta output asli dari CMD
                    // Ini bakal ngasih tau lo error sebenernya apa (misal: Access Denied, dll)
                    throw new \Exception("Ghostscript Failed! Output CMD: " . $output);
                }

                // 7. Sukses? Lanjut load pake FPDI
                $pageCount = $pdf->setSourceFile($repairedPath);
                $tempRepairedFile = $repairedPath;
            }

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Tambah halaman & pakai template lama
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                if ($pageNo === 1) {
                    // Logika Posisi Dinamis (Copy yang tadi)
                    $boxWidth = 25;
                    $textHeight = 5; // Tinggi area tulisan
                    $boxHeight = $boxWidth + $textHeight; // Tinggi total (Text + QR)
                    $margin = 10; // Jarak dari pinggir kertas
                    $x = 0;
                    $y = 0;

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
                    $pdf->SetDrawColor(0, 0, 0); // Warna Garis Hitam
                    $pdf->SetLineWidth(0.3);     // Ketebalan Garis
                    $pdf->SetFillColor(255, 255, 255); // Background Putih (biar tulisan gak numpuk konten PDF)

                    // Rect(x, y, w, h, style). 'DF' = Draw & Fill (Isi putih, garis hitam)
                    $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'DF');

                    // 2. TULIS TEXT "CAR Digital Approved"
                    // Pake Font Arial Bold, Ukuran kecil (misal 6 atau 7)
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->SetTextColor(0, 0, 0); // Teks Hitam

                    // Set kursor ke pojok kiri atas kotak
                    $pdf->SetXY($x, $y);
                    // Cell(width, height, text, border, ln, align)
                    // Border 1 di bawah cell text biar ada garis pemisah antara text & QR
                    $pdf->Cell($boxWidth, $textHeight, 'CAR Digital Approved', 'B', 0, 'C');

                    // 3. TEMPEL QR CODE (Di bawah teks)
                    // Kita kasih padding dikit biar gak nempel garis
                    $qrPadding = 1;
                    $qrSize = $boxWidth - ($qrPadding * 2);

                    // TEMPEL IMAGE CUMA DI SINI
                    $pdf->Image($qrTempPath, $x + $qrPadding, $y + $textHeight + $qrPadding, $qrSize, $qrSize);
                }
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

            return response()->json(['message' => 'Gagal memproses PDF: ' . $e->getMessage()], 500);
        } finally {
            // CLEANUP
            if (isset($qrTempPath) && file_exists($qrTempPath)) unlink($qrTempPath);

            // Hapus file hasil repair GS kalau ada
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
