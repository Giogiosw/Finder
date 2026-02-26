<?php

namespace Giogiosw\Finder\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageHandler
{
    protected ImageManager $manager;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->manager = new ImageManager(new Driver());
    }

    public function createThumbnail(string $disk, string $path): ?string
    {
        if (!config('finder.thumbnails.enabled')) {
            return null;
        }

        $thumbConfig = config('finder.thumbnails');
        $thumbDir = $thumbConfig['directory'] ?? '.thumbs';
        $dir = dirname($path);
        $filename = basename($path);
        $thumbPath = ($dir === '.' ? '' : $dir . '/') . $thumbDir . '/' . $filename;

        $storage = Storage::disk($disk);

        // Return existing thumbnail if fresh enough
        if ($storage->exists($thumbPath)) {
            $originalTime = $storage->lastModified($path);
            $thumbTime = $storage->lastModified($thumbPath);
            if ($thumbTime >= $originalTime) {
                return $thumbPath;
            }
        }

        try {
            $imageData = $storage->get($path);
            $image = $this->manager->read($imageData);

            $image->cover(
                $thumbConfig['width'] ?? 48,
                $thumbConfig['height'] ?? 48
            );

            $thumbDir2 = ($dir === '.' ? '' : $dir . '/') . $thumbDir;
            if (!$storage->exists($thumbDir2)) {
                $storage->makeDirectory($thumbDir2);
            }

            $encoded = $image->toJpeg($thumbConfig['quality'] ?? 80);
            $storage->put($thumbPath, $encoded);

            return $thumbPath;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function resize(string $disk, string $path, int $width, int $height, bool $keepAspect = true): bool
    {
        try {
            $storage = Storage::disk($disk);
            $imageData = $storage->get($path);
            $image = $this->manager->read($imageData);

            if ($keepAspect) {
                $image->scale($width, $height);
            } else {
                $image->resize($width, $height);
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $quality = config('finder.image_editing.quality', 90);

            $encoded = match ($ext) {
                'jpg', 'jpeg' => $image->toJpeg($quality),
                'png'         => $image->toPng(),
                'gif'         => $image->toGif(),
                'webp'        => $image->toWebp($quality),
                default       => $image->toJpeg($quality),
            };

            $storage->put($path, $encoded);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function rotate(string $disk, string $path, int $degrees): bool
    {
        try {
            $storage = Storage::disk($disk);
            $imageData = $storage->get($path);
            $image = $this->manager->read($imageData);

            $image->rotate($degrees);

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $quality = config('finder.image_editing.quality', 90);

            $encoded = match ($ext) {
                'jpg', 'jpeg' => $image->toJpeg($quality),
                'png'         => $image->toPng(),
                'gif'         => $image->toGif(),
                'webp'        => $image->toWebp($quality),
                default       => $image->toJpeg($quality),
            };

            $storage->put($path, $encoded);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function flip(string $disk, string $path, string $direction = 'horizontal'): bool
    {
        try {
            $storage = Storage::disk($disk);
            $imageData = $storage->get($path);
            $image = $this->manager->read($imageData);

            if ($direction === 'horizontal') {
                $image->flip();
            } else {
                $image->flop();
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $quality = config('finder.image_editing.quality', 90);

            $encoded = match ($ext) {
                'jpg', 'jpeg' => $image->toJpeg($quality),
                'png'         => $image->toPng(),
                'gif'         => $image->toGif(),
                'webp'        => $image->toWebp($quality),
                default       => $image->toJpeg($quality),
            };

            $storage->put($path, $encoded);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function crop(string $disk, string $path, int $x, int $y, int $width, int $height): bool
    {
        try {
            $storage = Storage::disk($disk);
            $imageData = $storage->get($path);
            $image = $this->manager->read($imageData);

            $image->crop($width, $height, $x, $y);

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $quality = config('finder.image_editing.quality', 90);

            $encoded = match ($ext) {
                'jpg', 'jpeg' => $image->toJpeg($quality),
                'png'         => $image->toPng(),
                'gif'         => $image->toGif(),
                'webp'        => $image->toWebp($quality),
                default       => $image->toJpeg($quality),
            };

            $storage->put($path, $encoded);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getDimensions(string $disk, string $path): ?array
    {
        try {
            $storage = Storage::disk($disk);
            $imageData = $storage->get($path);
            $image = $this->manager->read($imageData);

            return [
                'width'  => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function applyWatermark(string $disk, string $path): bool
    {
        $watermarkConfig = config('finder.watermark');

        if (!$watermarkConfig['enabled'] || !$watermarkConfig['image']) {
            return false;
        }

        try {
            $storage = Storage::disk($disk);
            $imageData = $storage->get($path);
            $image = $this->manager->read($imageData);

            $watermark = $this->manager->read(file_get_contents($watermarkConfig['image']));
            $watermark->opacity($watermarkConfig['opacity'] ?? 75);

            $position = $watermarkConfig['position'] ?? 'bottom-right';
            $image->place($watermark, $position);

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $quality = config('finder.image_editing.quality', 90);

            $encoded = match ($ext) {
                'jpg', 'jpeg' => $image->toJpeg($quality),
                'png'         => $image->toPng(),
                default       => $image->toJpeg($quality),
            };

            $storage->put($path, $encoded);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
