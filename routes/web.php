<?php

use Illuminate\Support\Facades\Route;
use PakPromo\FileManager\Http\Controllers\MediaLibraryController;

Route::get('/filemanager/uploader.min.js', [MediaLibraryController::class, 'uploader'])->name('filemanager.uploader');

Route::post('/dropzone/upload', [MediaLibraryController::class, 'upload'])->name('filemanager.dropzone.upload');
Route::post('/dropzone/delete', [MediaLibraryController::class, 'delete'])->name('filemanager.dropzone.delete');
