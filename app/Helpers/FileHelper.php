<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;

class FileHelper
{
    /**
     * Simpan file dengan struktur:
     * customer_code/model/part_number/filename.ext
     *
     * @return string Hanya nama file untuk disimpan ke database
     */
    public static function storeDrawingFile(
        UploadedFile $file,
        string $customerCode,
        string $model,
        string $partNumber,
        string $filename
    ) {
        $folder = "{$customerCode}/{$model}/{$partNumber}";
        $disk = Storage::disk('public');

        // pastiin folder ada
        $disk->makeDirectory($folder);

        $path = "{$folder}/{$filename}";

        // kalau file sudah ada → hapus
        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        // upload baru
        if (! $disk->putFileAs($folder, $file, $filename)) {
            throw new \Exception("Upload failed: {$filename}");
        }

        return $filename;
    }

    public static function storeTempDrawing(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        // Cek apakah file adalah TIF / TIFF
        if (in_array($ext, ['tif', 'tiff'])) {
            try {
                // 1. Setup Image Manager dengan driver Imagick
                // Pastikan extension imagick sudah aktif di PHP server kamu
                $manager = new ImageManager(new Driver());

                // 2. Baca file
                $image = $manager->read($file->getRealPath());

                // 3. Encode ke JPG (quality 90)
                $encoded = $image->toJpeg(90);

                // 4. Siapkan nama & path baru
                $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . '.jpg';
                $path = 'temp/drawings/' . $filename;

                // 5. Simpan hasilnya
                Storage::disk('public')->put($path, (string) $encoded);

                return $path;
            } catch (\Exception $e) {
                // Fallback jika gagal convert, simpan file aslinya saja
                Log::error('Gagal convert TIFF (V3): ' . $e->getMessage());
                return $file->store('temp/drawings', 'public');
            }
        }

        // Untuk file PDF, JPG, PNG biasa
        return $file->store('temp/drawings', 'public');
    }

    public static function moveTempToFinal(
        string $tempPath,
        string $customerCode,
        string $model,
        string $partNumber,
        string $filename
    ): string {
        $disk = Storage::disk('public');

        $folder = "{$customerCode}/{$model}/{$partNumber}";
        $disk->makeDirectory($folder);

        $finalPath = "{$folder}/{$filename}";

        if ($disk->exists($finalPath)) {
            $disk->delete($finalPath);
        }

        if (! $disk->move($tempPath, $finalPath)) {
            throw new \Exception("Move failed: {$filename}");
        }

        return $filename;
    }
}
