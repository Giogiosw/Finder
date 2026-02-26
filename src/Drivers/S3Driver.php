<?php

namespace Giogiosw\Finder\Drivers;

use Carbon\Carbon;

class S3Driver extends AbstractDriver
{
    public function url(string $path): ?string
    {
        try {
            return $this->storage->url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function temporaryUrl(string $path, int $minutes = 60): ?string
    {
        try {
            return $this->storage->temporaryUrl($path, Carbon::now()->addMinutes($minutes));
        } catch (\Throwable $e) {
            return $this->url($path);
        }
    }
}
