<?php

namespace Giogiosw\Finder\Http\Controllers;

use Giogiosw\Finder\FinderManager;
use Giogiosw\Finder\Support\ImageHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ThumbnailController extends Controller
{
    public function __construct(
        protected FinderManager $manager,
    ) {}

    /**
     * Serve a thumbnail for an image file.
     */
    public function show(Request $request): Response
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);

        $imageHandler = new ImageHandler(config('finder'));
        $thumbPath = $imageHandler->createThumbnail($disk, $path);

        if (!$thumbPath) {
            return response('Thumbnail not available', 404);
        }

        $storage = Storage::disk($disk);
        $contents = $storage->get($thumbPath);

        return response($contents, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Serve the full image for preview (with caching headers).
     */
    public function preview(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|Response
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);

        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            return response('File not found', 404);
        }

        $mime = $storage->mimeType($path) ?: 'application/octet-stream';
        $lastModified = $storage->lastModified($path);

        // Check If-Modified-Since header for caching
        if ($request->hasHeader('If-Modified-Since')) {
            $ifModifiedSince = strtotime($request->header('If-Modified-Since'));
            if ($lastModified <= $ifModifiedSince) {
                return response('', 304);
            }
        }

        return $storage->response($path, null, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=3600',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
        ]);
    }
}
