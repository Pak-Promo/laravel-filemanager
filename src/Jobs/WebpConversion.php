<?php

namespace PakPromo\FileManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PakPromo\FileManager\Conversions\ConversionHelper;

class WebpConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $media_id;

    protected array $media_conversions;

    public function __construct(int $media_id, array $media_conversions)
    {
        $this->onQueue(config('filemanager.queue_name'));

        $this->media_id = $media_id;
        $this->media_conversions = $media_conversions;
    }

    public function handle()
    {
        (new ConversionHelper)->convertOriginalImageToWebp($this->media_id, $this->media_conversions);
    }
}
