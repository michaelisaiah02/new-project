<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
