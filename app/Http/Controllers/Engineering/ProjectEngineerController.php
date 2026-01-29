<?php

namespace App\Http\Controllers\Engineering;

use App\Http\Controllers\Controller;
use App\Models\ApprovalStatus;
use App\Models\CustomerStage;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use setasign\Fpdi\Fpdi;

use function Symfony\Component\Clock\now;

class ProjectEngineerController extends Controller
{
    public function new(Project $project)
    {
        if (auth()->user()->approved || auth()->user()->checked) {
            if (! $project->approvalStatus) {
                if (auth()->user()->approved) {
                    return redirect()->back()->with('error', 'Belum bisa approve karena proyek belum di-setup.');
                } else {
                    return redirect()->back()->with('error', 'Belum bisa check karena proyek belum di-setup.');
                }
            } else {
                return redirect()->route('engineering.projects.assignDueDates', [
                    'project' => $project->id,
                ]);
            }
        }
        // Ambil semua stage + dokumen master
        $stages = $project->customer->stages()
            ->with([
                'documents' => function ($q) {
                    $q->orderBy('name');
                },
            ])
            ->orderBy('stage_number')
            ->get();

        // Ambil dokumen yang SUDAH dipilih project
        $selectedDocs = ProjectDocument::where('project_id', $project->id)
            ->get()
            ->groupBy('customer_stage_id')
            ->map(function ($rows) {
                return $rows->pluck('document_type_code')->toArray();
            });

        return view('engineering.projects.new', compact(
            'project',
            'stages',
            'selectedDocs'
        ));
    }

    public function saveNew(Request $request, Project $project)
    {
        $docGroups = $request->input('documents_codes', []);

        // Ambil semua stage customer (yang memang ditampilkan di form)
        $stages = CustomerStage::where('customer_code', $project->customer_code)
            ->orderBy('stage_number')
            ->get();

        if ($stages->isEmpty()) {
            return back()->with('error', 'No stages defined for this customer.');
        }

        // 🔴 VALIDASI: setiap stage minimal 1 dokumen
        $errors = [];

        foreach ($stages as $stage) {
            $stageNumber = $stage->stage_number;

            if (
                ! isset($docGroups[$stageNumber]) ||
                empty($docGroups[$stageNumber])
            ) {
                $errors["documents_codes.$stageNumber"] =
                    "Stage {$stageNumber} must have at least 1 document.";
            }
        }

        if (! empty($errors)) {
            return back()
                ->withErrors($errors)
                ->withInput();
        }

        $hasChanges = false;

        foreach ($stages as $stage) {
            $stageNumber = $stage->stage_number;
            $selectedDocs = $docGroups[$stageNumber] ?? [];

            $existingDocs = ProjectDocument::where('project_id', $project->id)
                ->where('customer_stage_id', $stage->id)
                ->pluck('document_type_code')
                ->toArray();

            $toInsert = array_diff($selectedDocs, $existingDocs);
            $toDelete = array_diff($existingDocs, $selectedDocs);

            // 🔴 kalau ada perubahan → tandai
            if (! empty($toInsert) || ! empty($toDelete)) {
                $hasChanges = true;
            }

            // hapus
            if ($toDelete) {
                ProjectDocument::where('project_id', $project->id)
                    ->where('customer_stage_id', $stage->id)
                    ->whereIn('document_type_code', $toDelete)
                    ->delete();
            }

            // insert
            foreach ($toInsert as $docCode) {
                ProjectDocument::create([
                    'project_id' => $project->id,
                    'document_type_code' => $docCode,
                    'customer_stage_id' => $stage->id,
                ]);
            }
        }

        if ($hasChanges) {
            ApprovalStatus::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'created_by_id' => auth()->id(),
                    'created_by_name' => auth()->user()->name,
                    'created_date' => now(),
                    'checked_by_id' => null,
                    'checked_by_name' => null,
                    'checked_date' => null,
                    'approved_by_id' => null,
                    'approved_by_name' => null,
                    'approved_date' => null,
                    'management_approved_by_id' => null,
                    'management_approved_by_name' => null,
                    'management_approved_date' => null,
                ]
            );

            Project::find($project->id)
                ->update(['remark' => 'not checked']);
        }

        return redirect()->route('engineering.projects.assignDueDates', [
            'project' => $project->id,
        ])->with('success', 'Documents updated successfully.');
    }

    public function assignDueDates(Project $project)
    {
        $projectDocuments = ProjectDocument::with([
            'stage',
            'documentType:code,name',
        ])
            ->where('project_id', $project->id)
            ->orderBy('customer_stage_id')
            ->get()
            ->groupBy('customer_stage_id');

        $user = auth()->user();

        $canCheck = $user->checked === true && $project->approvalStatus->checked_date === null;
        $canApprove = $user->approved === true && auth()->user()->department->type() === 'engineering' && $project->approvalStatus->checked_date !== null
            && $project->approvalStatus->approved_date === null;
        $canApproveManagement = $user->approved === true && auth()->user()->department->type() === 'management' && $project->approvalStatus->approved_date !== null
            && $project->approvalStatus->management_approved_date === null;

        return view('engineering.projects.assign-due-dates', compact(
            'project',
            'projectDocuments',
            'canCheck',
            'canApprove',
            'canApproveManagement'
        ));
    }

    public function updateDueDates(Request $request, Project $project)
    {
        $request->validate([
            'due_dates.*' => 'nullable|date',
        ]);

        foreach ($request->due_dates ?? [] as $pdId => $date) {
            ProjectDocument::findOrFail($pdId)
                ->update([
                    'due_date' => $date,
                ]);
        }

        // 🔴 setelah save, status balik ke not checked
        $project->update([
            'remark' => 'not checked',
        ]);

        $project->approvalStatus->update([
            'checked_by_id' => null,
            'checked_by_name' => null,
            'checked_date' => null,
            'approved_by_id' => null,
            'approved_by_name' => null,
            'approved_date' => null,
            'management_approved_by_id' => null,
            'management_approved_by_name' => null,
            'management_approved_date' => null,
        ]);

        return redirect()
            ->route('engineering')
            ->with('success', 'Due dates saved successfully.');
    }

    public function approval(Request $request)
    {
        $status = ApprovalStatus::firstOrCreate([
            'project_id' => $request->project_id,
        ]);

        $user = auth()->user();

        if ($request->action === 'checked' && $user->checked) {
            $status->update([
                'checked_by_id' => $user->id,
                'checked_by_name' => $user->name,
                'checked_date' => now(),
            ]);
        }

        if ($request->action === 'approved' && $user->approved) {
            $status->update([
                'approved_by_id' => $user->id,
                'approved_by_name' => $user->name,
                'approved_date' => now(),
            ]);
        }

        if ($request->action === 'approved_management' && $user->approved) {
            $status->update([
                'management_approved_by_id' => $user->id,
                'management_approved_by_name' => $user->name,
                'management_approved_date' => now(),
            ]);

            $project = Project::findOrFail($request->project_id);

            // 1. CEK FILE DRAWING 2D
            if (!$project->drawing_2d) {
                return response()->json(['message' => 'File Drawing 2D belum diupload.'], 422);
            }

            // Konstruksi Path Lengkap (Sesuaikan dengan folder penyimpanan kamu)
            $relativePath = $project->customer_code . '/' . $project->model . '/' . $project->part_number . '/' . $project->drawing_2d;
            $fullPath = storage_path('app/public/' . $relativePath);

            if (!file_exists($fullPath)) {
                return response()->json(['message' => 'File fisik tidak ditemukan.' . $relativePath . '/' . $fullPath], 404);
            }

            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            // 2. GENERATE QR CODE TEMP (Sama untuk PDF maupun Image)
            // Kita generate QR polos high quality
            $qrTempPath = sys_get_temp_dir() . '/qr_2d_' . uniqid() . '.png';

            // Isi QR: Bisa disesuaikan, misal info part number + status approved
            $qrContent = "Part: {$project->part_number}\nStatus: APPROVED\nDate: " . now()->format('d-m-Y');

            QrCode::format('png')
                ->size(500) // Gedein biar tajem pas di-resize
                ->margin(0)
                ->errorCorrection('H')
                ->generate($qrContent, $qrTempPath);

            // 3. CABANG LOGIKA BERDASARKAN EKSTENSI
            try {
                // === SKENARIO A: JIKA PDF (Pakai FPDI - Logic Stempel Vektor) ===
                if ($ext === 'pdf') {
                    $pdf = new Fpdi();
                    // Gunakan Ghostscript repair logic jika perlu (copy dari method sebelumnya)
                    // Disini kita asumsi file aman/sudah direpair:
                    $pageCount = $pdf->setSourceFile($fullPath);

                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($templateId);
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($templateId);

                        // Tempel Stempel HANYA di Halaman 1
                        if ($pageNo === 1) {
                            $boxWidth = 10; // mm
                            $textHeight = 5;
                            $boxHeight = $boxWidth + $textHeight;
                            $margin = 10;

                            // Posisi Kanan Bawah
                            $x = $size['width'] - $boxWidth - $margin;
                            $y = $size['height'] - $boxHeight - $margin;

                            // Gambar Kotak & Teks
                            $pdf->SetDrawColor(0, 0, 0);
                            $pdf->SetLineWidth(0.3);
                            $pdf->SetFillColor(255, 255, 255);
                            $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'DF');

                            $pdf->SetFont('Arial', 'B', 7);
                            $pdf->SetTextColor(0, 0, 0);
                            $pdf->SetXY($x, $y);
                            $pdf->Cell($boxWidth, $textHeight, 'CAR Digital Approved', 'B', 0, 'C');

                            // Tempel QR
                            $qrPadding = 1;
                            $qrSize = $boxWidth - ($qrPadding * 2);
                            $pdf->Image($qrTempPath, $x + $qrPadding, $y + $textHeight + $qrPadding, $qrSize, $qrSize);
                        }
                    }
                    $pdf->Output('F', $fullPath); // Save Overwrite PDF
                }

                // === SKENARIO B: JIKA GAMBAR (JPG/PNG) - Pakai GD Library ===
                elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                    // A. Load Gambar ke Memori
                    if ($ext === 'png') {
                        $image = imagecreatefrompng($fullPath);
                    } else {
                        $image = imagecreatefromjpeg($fullPath);
                    }

                    if (!$image) throw new \Exception("Gagal load gambar.");

                    // B. Hitung Ukuran & Posisi (Pixel Based)
                    $imgWidth = imagesx($image);
                    $imgHeight = imagesy($image);

                    // Konversi ukuran mm ke pixel (estimasi kasar biar proporsional)
                    // Kita ambil 15% dari lebar gambar, atau minimal 200px biar kebaca
                    $boxSizePixel = max($imgWidth * 0.15, 200);
                    $textHeightPixel = $boxSizePixel * 0.2; // Tinggi teks 20% dari lebar kotak
                    $totalHeightPixel = $boxSizePixel + $textHeightPixel;
                    $marginPixel = $imgWidth * 0.05; // Margin 5% dari pinggir

                    // Koordinat Kanan Bawah
                    $x = $imgWidth - $boxSizePixel - $marginPixel;
                    $y = $imgHeight - $totalHeightPixel - $marginPixel;

                    // C. Bikin Warna
                    $white = imagecolorallocate($image, 255, 255, 255);
                    $black = imagecolorallocate($image, 0, 0, 0);

                    // D. Gambar Kotak Putih (Background) + Border Hitam
                    imagefilledrectangle($image, $x, $y, $x + $boxSizePixel, $y + $totalHeightPixel, $white);
                    imagerectangle($image, $x, $y, $x + $boxSizePixel, $y + $totalHeightPixel, $black);

                    // E. Gambar Garis Pemisah (Bawah Teks)
                    imageline($image, $x, $y + $textHeightPixel, $x + $boxSizePixel, $y + $textHeightPixel, $black);

                    // F. Tulis Teks "CAR Digital Approved"
                    // Karena GD default fontnya jelek, kita pake imagestring (font bawaan 1-5)
                    // Atau kalau mau bagus pake imagettftext (tapi butuh file .ttf)
                    // Kita pake built-in font terbesar (5) dan coba tengahin
                    $font = 5;
                    $text = 'CAR Digital Approved';
                    $fontWidth = imagefontwidth($font) * strlen($text);
                    $fontHeight = imagefontheight($font);

                    // Center text logic
                    $textX = $x + ($boxSizePixel - $fontWidth) / 2;
                    $textY = $y + ($textHeightPixel - $fontHeight) / 2;

                    imagestring($image, $font, $textX, $textY, $text, $black);

                    // G. Tempel QR Code
                    $qrSrc = imagecreatefrompng($qrTempPath);
                    $qrSrcWidth = imagesx($qrSrc);
                    $qrSrcHeight = imagesy($qrSrc);

                    $qrTargetSize = $boxSizePixel - 4; // Kurangi padding dikit (2px kiri kanan)

                    // Copy & Resize QR ke dalam kotak
                    imagecopyresampled(
                        $image,
                        $qrSrc,
                        $x + 2, // Dest X (Padding 2px)
                        $y + $textHeightPixel + 2, // Dest Y (Di bawah teks + padding)
                        0,
                        0,
                        $qrTargetSize,
                        $qrTargetSize,
                        $qrSrcWidth,
                        $qrSrcHeight
                    );

                    // H. Simpan Kembali (Overwrite)
                    if ($ext === 'png') {
                        imagepng($image, $fullPath);
                    } else {
                        imagejpeg($image, $fullPath, 90); // Quality 90
                    }

                    // Bersihkan Memori
                    imagedestroy($image);
                    imagedestroy($qrSrc);
                }
            } catch (\Exception $e) {
                // Cleanup temp
                if (file_exists($qrTempPath)) unlink($qrTempPath);
                return response()->json(['message' => 'Gagal stamp drawing: ' . $e->getMessage()], 500);
            }

            // Cleanup QR Temp
            if (file_exists($qrTempPath)) unlink($qrTempPath);
        }

        Project::findOrFail($request->project_id)
            ->update([
                'remark' => match ($request->action) {
                    'checked' => 'not approved',
                    'approved' => 'not approved management',
                    'approved_management' => 'on going',
                    default => 'new',
                },
            ]);

        return response()->json(['status' => 'success']);
    }

    public function updateToOnGoing(Project $project)
    {
        $project->update([
            'remark' => 'on going',
        ]);

        return redirect()
            ->route('engineering')
            ->with('success', 'Due dates assigned successfully.');
    }

    public function ongoing(Project $project)
    {
        $projectDocuments = ProjectDocument::with([
            'stage:id,stage_number',
            'documentType:code,name',
        ])
            ->where('project_id', $project->id)
            ->orderBy('customer_stage_id')
            ->get()
            ->groupBy('customer_stage_id');

        return view('engineering.projects.ongoing', compact(
            'project',
            'projectDocuments'
        ));
    }

    public function checkedOngoing(Project $project)
    {
        $project->approvalStatus->update([
            'ongoing_checked_by_id' => auth()->id(),
            'ongoing_checked_by_name' => auth()->user()->name,
            'ongoing_checked_date' => now(),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function approvedOngoing(Project $project)
    {
        if (auth()->user()->department->type() === 'management') {
            $project->approvalStatus->update([
                'ongoing_management_approved_by_id' => auth()->id(),
                'ongoing_management_approved_by_name' => auth()->user()->name,
                'ongoing_management_approved_date' => now(),
            ]);

            // Set project as completed
            $project->update([
                'remark' => 'completed',
            ]);

            return response()->json(['status' => 'success']);
        } else {
            $project->approvalStatus->update([
                'ongoing_approved_by_id' => auth()->id(),
                'ongoing_approved_by_name' => auth()->user()->name,
                'ongoing_approved_date' => now(),
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function cancel(Project $project)
    {
        $project->update([
            'remark' => 'canceled',
        ]);

        return redirect()
            ->route('engineering')
            ->with('success', 'Project has been canceled.');
    }
}
