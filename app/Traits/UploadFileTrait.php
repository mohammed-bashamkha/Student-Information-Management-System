<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UploadFileTrait
{
    protected function uploadFile(
        UploadedFile $file,
        string $folder,
        string $disk = 'public',
        ?string $oldPath = null
    ): string {
        if ($oldPath) {
            $this->deleteFile($oldPath, $disk);
        }

        $filename = Str::random(20) . '_' . time() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $filename, $disk);
    }

    protected function deleteFile(string $path, string $disk = 'public'): void
    {
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}