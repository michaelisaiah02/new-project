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
                ->errorCorrection('L')
                ->generate($qrContent, $qrTempPath);

            // 3. CABANG LOGIKA BERDASARKAN EKSTENSI
            try {
                // === KONFIGURASI UMUM (10mm) ===
                $boxWidth_mm = 12;
                $headerHeight_mm = 3.6; // Tinggi header 3.6mm (cukup buat 2 baris)
                $qrPadding_mm = 0.5;   // Padding aman buat scanner

                // === SKENARIO A: JIKA PDF (FPDI) ===
                if ($ext === 'pdf') {
                    // 1. Setup FPDI 'mm'
                    $pdf = new Fpdi('P', 'mm');
                    $pageCount = $pdf->setSourceFile($fullPath);

                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($templateId);

                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

                        // Mantra Anti Ngaco
                        $pdf->SetAutoPageBreak(false);
                        $pdf->SetMargins(0, 0, 0);

                        $pdf->useTemplate($templateId);

                        if ($pageNo === 1) {
                            // Setup Ukuran
                            $boxWidth = $boxWidth_mm;
                            $headerHeight = $headerHeight_mm;
                            $boxHeight = $headerHeight + $boxWidth;
                            $margin = 10;

                            // Posisi (Default Kanan Bawah)
                            $x = $size['width'] - $boxWidth - $margin;
                            $y = $size['height'] - $boxHeight - $margin;

                            // Gambar Kotak
                            $pdf->SetDrawColor(0, 0, 0);
                            $pdf->SetLineWidth(0.1);
                            $pdf->SetFillColor(255, 255, 255);
                            $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'DF');
                            $pdf->Line($x, $y + $headerHeight, $x + $boxWidth, $y + $headerHeight);

                            // Teks 2 Baris (MultiCell)
                            $pdf->SetFont('Arial', 'B', 5);
                            $pdf->SetTextColor(0, 0, 0);
                            $pdf->SetXY($x, $y + 0.3); // Padding atas dikit 0.3mm
                            $pdf->MultiCell($boxWidth, 1.5, "CAR Digital\nApproved", 0, 'C');

                            // Tempel QR
                            $qrSize = $boxWidth - ($qrPadding_mm * 2);
                            $pdf->Image($qrTempPath, $x + $qrPadding_mm, $y + $headerHeight + $qrPadding_mm, $qrSize, $qrSize);
                        }
                    }
                    $pdf->Output('F', $fullPath);
                }

                // === SKENARIO B: JIKA GAMBAR (GD Library) ===
                elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                    if ($ext === 'png') $image = imagecreatefrompng($fullPath);
                    else $image = imagecreatefromjpeg($fullPath);

                    if (!$image) throw new \Exception("Gagal load gambar.");

                    $imgWidth = imagesx($image);
                    $imgHeight = imagesy($image);

                    // 1. UKURAN TARGET DI GAMBAR ASLI (12mm)
                    // Rasio 12mm ke A4 (210mm) ~ 0.057
                    $targetBoxW = $imgWidth * 0.057;

                    // LIMITASI MINIMUM:
                    // Kita kunci minimal 120px.
                    // Apapun resolusi gambarnya, stempel gak boleh lebih kecil dari ini.
                    $targetBoxW = max($targetBoxW, 120);

                    // Tinggi Proporsional (Header + Body)
                    // Header 3.8mm / Body 12mm -> Ratio Total Tinggi ~1.32 dari lebar
                    $targetBoxH = $targetBoxW * 1.32;

                    $margin_px = $imgWidth * 0.02; // Margin 2%

                    $dst_x = $imgWidth - $targetBoxW - $margin_px;
                    $dst_y = $imgHeight - $targetBoxH - $margin_px;

                    // 2. BIKIN MASTER KANVAS (TIGHT FIT)
                    // Kita hitung lebar text "CAR Digital" pake Font 5
                    // Font 5 itu lebar per hurufnya sekitar 9px. "CAR Digital" = 11 huruf.
                    // Total lebar text ~ 100px.
                    // Jadi kita set lebar Master Canvas 130px aja (biar text keliatan GEDE dan PENUH).
                    $hdW = 130;
                    $hdH = $hdW * 1.32;

                    $stampHD = imagecreatetruecolor($hdW, $hdH);
                    $white = imagecolorallocate($stampHD, 255, 255, 255);
                    $black = imagecolorallocate($stampHD, 0, 0, 0);

                    // Fill Putih & Border
                    imagefilledrectangle($stampHD, 0, 0, $hdW, $hdH, $white);
                    // Ketebalan garis disesuaikan sama kanvas kecil (2px cukup)
                    imagesetthickness($stampHD, 2);
                    imagerectangle($stampHD, 0, 0, $hdW, $hdH, $black);

                    // Garis Pemisah Header (24% dari atas)
                    $lineY = $hdH * 0.24;
                    imageline($stampHD, 0, $lineY, $hdW, $lineY, $black);

                    // 3. TULIS TEXT (FONT 5 - JADI TERLIHAT RAKSASA SEKARANG)
                    $font = 5;
                    $fh = imagefontheight($font);

                    // Baris 1: "CAR Digital"
                    $text1 = 'CAR Digital';
                    $tx1 = ($hdW - (imagefontwidth($font) * strlen($text1))) / 2;
                    // Posisi Y: Center di area header
                    $ty1 = ($lineY / 2) - $fh + 2;

                    imagestring($stampHD, $font, $tx1, $ty1, $text1, $black);

                    // Baris 2: "Approved"
                    $text2 = 'Approved';
                    $tx2 = ($hdW - (imagefontwidth($font) * strlen($text2))) / 2;
                    $ty2 = $ty1 + $fh + 3; // Jarak dikit ke bawah

                    imagestring($stampHD, $font, $tx2, $ty2, $text2, $black);

                    // 4. TEMPEL QR KE KANVAS
                    $qrSrc = imagecreatefrompng($qrTempPath);
                    $qrPad = $hdW * 0.05; // Padding 5%
                    $qrTargetSize = $hdW - ($qrPad * 2);

                    imagecopyresampled($stampHD, $qrSrc, $qrPad, $lineY + $qrPad, 0, 0, $qrTargetSize, $qrTargetSize, imagesx($qrSrc), imagesy($qrSrc));

                    // 5. RESIZE KE TARGET
                    // Karena $hdW (130px) mungkin lebih kecil dari $targetBoxW (misal 200px di gambar HD),
                    // dia bakal di-UPSCALE. Tulisannya bakal kebaca JELAS (walau agak kotak-kotak dikit style retro).
                    imagecopyresampled($image, $stampHD, $dst_x, $dst_y, 0, 0, $targetBoxW, $targetBoxH, $hdW, $hdH);

                    if ($ext === 'png') imagepng($image, $fullPath);
                    else imagejpeg($image, $fullPath, 100);

                    imagedestroy($image);
                    imagedestroy($stampHD);
                    imagedestroy($qrSrc);
                }
            } catch (\Exception $e) {
                if (file_exists($qrTempPath)) unlink($qrTempPath);
                return response()->json(['message' => 'Gagal stamp: ' . $e->getMessage()], 500);
            }

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
