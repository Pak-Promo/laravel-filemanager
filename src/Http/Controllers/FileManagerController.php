<?php

namespace PakPromo\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use PakPromo\FileManager\Services\DropzoneService;
use PakPromo\FileManager\Traits\CanPretendToBeAFile;

class FileManagerController extends Controller
{
    use CanPretendToBeAFile;

    public function uploader()
    {
        return $this->pretendResponseIsFile(__DIR__ . '/../../../dist/js/uploader.js');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'array', 'min:1'],
        ]);

        return (new DropzoneService)->upload($request->toArray());
    }

    public function delete(Request $request)
    {
        $request->validate([
            'file_name' => ['required', 'string'],
        ]);

        (new DropzoneService)->delete($request->toArray());

        return response()->json('success');
    }
}
