<?php

namespace PakPromo\FileManager\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PakPromo\FileManager\Exceptions\FileSizeTooBigException;
use PakPromo\FileManager\Exceptions\InvalidConversionException;
use PakPromo\FileManager\Jobs\MediaConversion;
use PakPromo\FileManager\Jobs\ThumbnailConversion;
use PakPromo\FileManager\Jobs\WebpConversion;
use PakPromo\FileManager\Models\Media;

trait MediaHelper
{
    public string $type;
    public string $collection;
    public string $disk;
    public bool $without_conversions = false;
    public array $request;
    public $model;

    public function useDisk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function withoutConversions(bool $value)
    {
        $this->without_conversions = $value;

        return $this;
    }

    protected function getFileUploadPath(): string
    {
        return $this->getCollection() . DIRECTORY_SEPARATOR . 'original';
    }

    protected function getCollection(): string
    {
        if ($this->collection) {
            return $this->collection;
        }

        return $this->getCollectionFromModel();
    }

    protected function getCollectionFromModel(): string
    {
        if (! $this->model) {
            return '';
        }

        $collection = $this->model->defaultCollection();

        return Str::kebab($collection);
    }

    protected function setDefaultConversions(Media $media)
    {
        $conversions = [
            'original' => $media->getFilePath(),
            'thumbnail' => $media->getFilePath(),
        ];

        $media->conversions = $conversions;

        $media->save();
    }

    protected function checkMaxFileUploadSize(UploadedFile $file)
    {
        if ($file->getSize() > config('filemanager.max_file_size')) {
            throw new FileSizeTooBigException();
        }
    }

    protected function validateModelRegisteredConversions(): void
    {
        if ($this->without_conversions) {
            return;
        }

        $this->model->registerMediaConversions();

        if (empty($this->model->mediaConversions)) {
            return;
        }

        foreach ($this->model->mediaConversions as $conversion) {
            if (! property_exists($conversion, 'width')) {
                throw InvalidConversionException::width();
            }

            if (! property_exists($conversion, 'height')) {
                throw InvalidConversionException::height();
            }
        }
    }

    protected function dispatchConversionJobs(Media $media)
    {
        if (! in_array($media->mime_type, $this->allowedMimeTypesForConversion())) {
            return;
        }

        $webp_conversion = config('filemanager.webp_conversion');

        if ($webp_conversion && $media->mime_type !== 'image/webp') {
            $mediaConversions = $this->model->mediaConversions;

            if ($this->without_conversions) {
                $mediaConversions = [];
            }

            WebpConversion::dispatch($media->id, $mediaConversions);

            return;
        }

        ThumbnailConversion::dispatch($media->id);

        if ($this->without_conversions) {
            return;
        }

        if (empty($this->model->mediaConversions)) {
            return;
        }

        MediaConversion::dispatch($media->id, $this->model->mediaConversions);
    }

    protected function allowedMimeTypesForConversion()
    {
        return ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    }

    protected function getUploadedFileUniqueName(UploadedFile $file)
    {
        $originalName = $file->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);

        return $this->makeFilenameUnique($filename, $file->getClientOriginalExtension());
    }

    protected function makeFilenameUnique(string $filename, string $extension)
    {
        $filename = Str::slug($filename);
        $filename = Str::limit($filename, 200, '');

        return $filename . '_' . time() . '.' . $extension;
    }
}
