<?php

namespace PakPromo\FileManager;

use Illuminate\Http\UploadedFile;
use PakPromo\FileManager\Jobs\ThumbnailConversion;
use PakPromo\FileManager\Jobs\WebpConversion;
use PakPromo\FileManager\Models\Media;
use PakPromo\FileManager\Traits\MediaHelper;

class MediaUploadForSetting
{
    use MediaHelper;

    public function __construct()
    {
        $this->disk = config('filemanager.disk_name');
    }

    public function toMediaCollection(string $collection = '')
    {
        $this->collection = $collection;

        return $this;
    }

    public function handle(array $request, string $type, string $option_name)
    {
        $this->deleteOldFileIfRequested($request, $type, $option_name);

        if (! isset($request[$type])) {
            return null;
        }

        $media = $this->upload($request[$type], $type);

        return $media['media_id'];
    }

    public function upload(UploadedFile $file, string $type): array
    {
        $this->checkMaxFileUploadSize($file);

        $filename = $this->getUploadedFileUniqueName($file);

        $file->storeAs($this->getFileUploadPath(), $filename, $this->disk);

        $media = Media::create([
            'type' => $type,
            'file_name' => $filename,
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'collection_name' => $this->getCollection(),
        ]);

        $this->setDefaultConversions($media);

        if (in_array($media->mime_type, $this->allowedMimeTypesForConversion())) {
            $webp_conversion = config('filemanager.webp_conversion');

            if ($webp_conversion && $media->mime_type !== 'image/webp') {
                WebpConversion::dispatch($media->id, []);
            } else {
                ThumbnailConversion::dispatch($media->id);
            }
        }

        return [
            'media_id' => $media->id,
            'file_name' => $filename,
        ];
    }

    protected function deleteOldFileIfRequested(array $request, string $type, string $option_name): void
    {
        if (! isset($request['remove_' . $type])) {
            return;
        }

        if ($request['remove_' . $type] === 'no') {
            return;
        }

        if (isset($request[$type])) {
            $this->checkMaxFileUploadSize($request[$type]);
        }

        $media = Media::find(setting()->get($option_name));

        if ($media) {
            $media->delete();
        }
    }
}
