<?php

namespace Giogiosw\Finder\Drivers;

class LocalDriver extends AbstractDriver
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
        // Local driver does not support temporary URLs in the same way
        // Fall back to regular URL
        return $this->url($path);
    }
}
