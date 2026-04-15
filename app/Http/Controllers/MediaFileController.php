<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\VendorDocument;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaFileController
{
    /**
     * Stream a media file through Laravel to avoid direct /storage dependency.
     */
    public function __invoke(Media $media): StreamedResponse
    {
        $model = $media->model;

        if ($model instanceof Invoice) {
            Gate::authorize('view', $model);
        } elseif ($model instanceof VendorDocument) {
            Gate::authorize('view', $model->vendor);
        } else {
            abort(403);
        }

        $disk = Storage::disk($media->disk);
        $relativePath = $media->getPathRelativeToRoot();

        abort_unless($disk->exists($relativePath), 404);

        return $disk->response(
            $relativePath,
            $media->file_name,
            [
                'Content-Type' => $media->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.$media->file_name.'"',
            ],
        );
    }
}
