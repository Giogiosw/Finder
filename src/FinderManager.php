<?php

namespace Giogiosw\Finder;

use Giogiosw\Finder\Drivers\DriverInterface;
use Giogiosw\Finder\Drivers\LocalDriver;
use Giogiosw\Finder\Drivers\S3Driver;
use Giogiosw\Finder\Support\FileInfo;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class FinderManager
{
    protected array $config;
    protected array $drivers = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function driver(string $disk = null): DriverInterface
    {
        $disk = $disk ?? $this->config['default_disk'] ?? 'local';

        if (!isset($this->drivers[$disk])) {
            $this->drivers[$disk] = $this->createDriver($disk);
        }

        return $this->drivers[$disk];
    }

    protected function createDriver(string $disk): DriverInterface
    {
        $diskConfig = config("filesystems.disks.{$disk}");

        if (!$diskConfig) {
            throw new InvalidArgumentException("Disk [{$disk}] not configured.");
        }

        $storage = Storage::disk($disk);

        return match ($diskConfig['driver']) {
            's3' => new S3Driver($storage, $disk, $this->config),
            default => new LocalDriver($storage, $disk, $this->config),
        };
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getAllDisks(): array
    {
        return $this->config['disks'] ?? [$this->config['default_disk'] ?? 'local'];
    }
}
