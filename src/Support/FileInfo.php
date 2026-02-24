<?php

namespace Giogiosw\Finder\Support;

use Carbon\Carbon;

class FileInfo
{
    public function __construct(
        public readonly string $hash,
        public readonly string $name,
        public readonly string $path,
        public readonly string $disk,
        public readonly bool $isDir,
        public readonly int $size,
        public readonly int $modified,
        public readonly string $mime,
        public readonly bool $readable,
        public readonly bool $writable,
        public readonly bool $locked,
        public readonly ?string $parentHash,
        public readonly ?string $thumbnail = null,
        public readonly ?array $dimensions = null,
    ) {}

    public static function make(array $data): self
    {
        return new self(...$data);
    }

    public function toArray(): array
    {
        $data = [
            'hash' => $this->hash,
            'name' => $this->name,
            'phash' => $this->parentHash,
            'mime' => $this->isDir ? 'directory' : $this->mime,
            'size' => $this->size,
            'ts' => $this->modified,
            'read' => (int) $this->readable,
            'write' => (int) $this->writable,
            'locked' => (int) $this->locked,
        ];

        if ($this->thumbnail) {
            $data['tmb'] = $this->thumbnail;
        }

        if ($this->dimensions) {
            $data['dim'] = $this->dimensions['width'] . 'x' . $this->dimensions['height'];
        }

        return $data;
    }

    public function modifiedForHumans(): string
    {
        return Carbon::createFromTimestamp($this->modified)->diffForHumans();
    }

    public function sizeForHumans(): string
    {
        if ($this->isDir) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    public function extension(): string
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime, 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->mime, 'audio/');
    }

    public function isText(): bool
    {
        return str_starts_with($this->mime, 'text/') ||
               in_array($this->mime, [
                   'application/json',
                   'application/xml',
                   'application/javascript',
               ]);
    }

    public function isPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }

    public function isArchive(): bool
    {
        return in_array($this->mime, [
            'application/zip',
            'application/x-tar',
            'application/x-gzip',
            'application/x-bzip2',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
        ]);
    }

    public function iconClass(): string
    {
        if ($this->isDir) {
            return 'icon-folder';
        }

        if ($this->isImage()) return 'icon-image';
        if ($this->isVideo()) return 'icon-video';
        if ($this->isAudio()) return 'icon-audio';
        if ($this->isPdf()) return 'icon-pdf';
        if ($this->isArchive()) return 'icon-archive';
        if ($this->isText()) return 'icon-text';

        return 'icon-file';
    }
}
