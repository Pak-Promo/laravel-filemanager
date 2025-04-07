<?php

namespace PakPromo\FileManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PakPromo\FileManager\Conversions\ConversionHelper;

class MediaConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $media_id;
    protected array $conversions;

    public function __construct(int $media_id, array $conversions)
    {
        $this->onQueue(config('filemanager.queue_name'));

        $this->media_id = $media_id;
        $this->conversions = $conversions;
    }

    public function handle()
    {
        (new ConversionHelper)->conversions($this->media_id, $this->conversions);
    }
}
