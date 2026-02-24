<?php

use Giogiosw\Finder\Http\Controllers\FinderController;
use Giogiosw\Finder\Http\Controllers\ThumbnailController;
use Illuminate\Support\Facades\Route;

$routeConfig = config('finder.route', [
    'prefix'     => 'finder',
    'middleware' => ['web', 'auth'],
    'name'       => 'finder.',
]);

Route::group([
    'prefix'     => $routeConfig['prefix'],
    'middleware' => $routeConfig['middleware'],
    'as'         => $routeConfig['name'],
], function () {

    // Main file manager page
    Route::get('/', [FinderController::class, 'index'])->name('index');

    // File operations API
    Route::prefix('api')->group(function () {
        Route::get('ls',           [FinderController::class, 'ls'])->name('ls');
        Route::get('info',         [FinderController::class, 'info'])->name('info');
        Route::get('get',          [FinderController::class, 'getContent'])->name('get');
        Route::get('download',     [FinderController::class, 'download'])->name('download');
        Route::get('url',          [FinderController::class, 'url'])->name('url');
        Route::get('search',       [FinderController::class, 'search'])->name('search');
        Route::get('size',         [FinderController::class, 'size'])->name('size');

        Route::post('mkdir',       [FinderController::class, 'mkdir'])->name('mkdir');
        Route::post('mkfile',      [FinderController::class, 'mkfile'])->name('mkfile');
        Route::post('rename',      [FinderController::class, 'rename'])->name('rename');
        Route::post('copy',        [FinderController::class, 'copy'])->name('copy');
        Route::post('move',        [FinderController::class, 'move'])->name('move');
        Route::post('rm',          [FinderController::class, 'rm'])->name('rm');
        Route::post('upload',      [FinderController::class, 'upload'])->name('upload');
        Route::post('put',         [FinderController::class, 'putContent'])->name('put');
        Route::post('archive',     [FinderController::class, 'archive'])->name('archive');
        Route::post('extract',     [FinderController::class, 'extract'])->name('extract');
        Route::post('edit-image',  [FinderController::class, 'editImage'])->name('edit-image');
    });

    // Thumbnail / Preview
    Route::get('thumbnail', [ThumbnailController::class, 'show'])->name('thumbnail');
    Route::get('preview',   [ThumbnailController::class, 'preview'])->name('preview');

});
