<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileHelper
{
    /**
     * Simpan file dengan struktur:
     * customer_code/model/part_number/filename.ext
     *
     * @param UploadedFile $file
     * @param string $customerCode
     * @param string $model
     * @param string $partNumber
     * @param string $filename
     * @return string Hanya nama file untuk disimpan ke database
     */
    public static function storeDrawingFile(
        UploadedFile $file,
        string $customerCode,
        string $model,
        string $partNumber,
        string $filename
    ) {
        // Folder tujuan
        $folder = "{$customerCode}/{$model}/{$partNumber}";

        // Simpan file
        Storage::disk('public')->putFileAs($folder, $file, $filename);

        // Yang masuk DB hanya filename
        return $filename;
    }
}
