<?php

namespace PakPromo\FileManager\Conversions;

use Illuminate\Support\Facades\Storage;
use PakPromo\FileManager\Models\File;
use Intervention\Image\Laravel\Facades\Image;
use PakPromo\FileManager\Jobs\FileConversion;
use PakPromo\FileManager\Jobs\ThumbnailConversion;

class ConversionHelper
{
    protected File $file;

    public function conversions(int $file_id, array $conversions)
    {
        $this->file = File::findOrFail($file_id);

        foreach ($conversions as $conversion) {
            $this->generateConversion($conversion);
        }
    }

    public function generateConversion(Conversion $conversion)
    {
        $this->createDirectory($conversion->name);

        $original_image = Storage::disk($this->file->disk)
            ->get($this->file->getFilePath());

        $image = $this->resizeFile($original_image, $conversion);

        $webp_conversion = config('filemanager.webp_conversion');
        $webp_quality = config('filemanager.webp_quality') ?: 75;

        if ($webp_conversion) {
            $image = $image->toWebp($webp_quality);
        } else {
            $image = $image->encodeByFileType();
        }

        Storage::disk($this->file->disk)
            ->put($this->file->getConversionPath($conversion->name, $webp_conversion), $image->toFilePointer(), 'public');

        $this->updateConversionsAttribute($conversion->name, $webp_conversion);
    }

    protected function resizeFile(string $original_image, Conversion $conversion)
    {
        if ($conversion->crop) {
            return Image::read($original_image)
                ->cover($conversion->width, $conversion->height, $conversion->position);
        }

        $image = Image::read($original_image);

        if ($image->width() >= $image->height()) {
            return $image->scaleDown($conversion->width);
        }

        return $image->scaleDown(height: $conversion->height);
    }

    public function convertOriginalImageToWebp(int $file_id, array $file_conversions = []): void
    {
        $this->file = File::findOrFail($file_id);
        $original_image_path = $this->file->getFilePath();

        $original_image = Storage::disk($this->file->disk)
            ->get($original_image_path);

        $webp_path = $this->file->getConversionPath('original', true);

        $webp_quality = config('filemanager.webp_quality') ?: 75;
        $webp_quality = $webp_quality == 100 ? 99 : $webp_quality;

        $image = Image::read($original_image)->toWebp($webp_quality);

        Storage::disk($this->file->disk)
            ->put($webp_path, $image->toFilePointer(), 'public');

        $this->updateConversionsAttribute('original', true);

        // generate conversions
        ThumbnailConversion::dispatch($file_id);

        if (!empty($file_conversions)) {
            FileConversion::dispatch($file_id, $file_conversions);
        }
    }

    public function generateThumbnailConversion(int $file_id): void
    {
        $thumbnail_enable = config('filemanager.thumbnails.generate', true);

        if (! $thumbnail_enable) {
            return;
        }

        $this->file = File::findOrFail($file_id);

        $this->createDirectory('thumbnail');

        $width = config('filemanager.thumbnails.width', 200);
        $height = config('filemanager.thumbnails.height', 200);

        $webp_conversion = config('filemanager.webp_conversion');
        $webp_quality = config('filemanager.webp_quality') ?: 75;

        $original_image = Storage::disk($this->file->disk)
            ->get($this->file->getFilePath());

        $image = Image::read($original_image)
            ->cover($width, $height);

        if ($webp_conversion) {
            $image = $image->toWebp($webp_quality);
        } else {
            $image = $image->encodeByFileType();
        }

        Storage::disk($this->file->disk)
            ->put($this->file->getConversionPath('thumbnail', $webp_conversion), $image->toFilePointer(), 'public');

        $this->updateConversionsAttribute('thumbnail', $webp_conversion);
    }

    protected function updateConversionsAttribute(string $key = 'original', bool $webp = false): void
    {
        $conversions = $this->file->conversions;
        $conversions[$key] = $this->file->getConversionPath($key, $webp);

        $this->file->conversions = $conversions;
        $this->file->save();
    }

    protected function createDirectory(string $directory): void
    {
        $directory = $this->file->collection_name . DIRECTORY_SEPARATOR . $directory;

        if (! Storage::disk($this->file->disk)->exists($directory)) {
            Storage::disk($this->file->disk)->makeDirectory($directory);
        }
    }
}
