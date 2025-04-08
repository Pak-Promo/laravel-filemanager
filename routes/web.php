<?php

use Illuminate\Support\Facades\Route;
use PakPromo\FileManager\Http\Controllers\FileLibraryController;

Route::get('/filemanager/uploader.min.js', [FileLibraryController::class, 'uploader'])->name('filemanager.uploader');

Route::post('/dropzone/upload', [FileLibraryController::class, 'upload'])->name('filemanager.dropzone.upload');
Route::post('/dropzone/delete', [FileLibraryController::class, 'delete'])->name('filemanager.dropzone.delete');
