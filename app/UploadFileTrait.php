<?php

namespace App\Traits; // يفضل وضعه في مجلد Traits خاص

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UploadFileTrait
{
    protected function uploadFile(
        UploadedFile $file,
        string $folder,
        string $disk = 'public/students-images',
        ?string $oldPath = null
    ): string {
        if ($oldPath) {
            $this->deleteFile($oldPath, $disk);
        }

        $filename = Str::random(20) . '_' . time() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $filename, $disk);
    }

    protected function deleteFile(string $path, string $disk = 'public/students-images'): void
    {
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}