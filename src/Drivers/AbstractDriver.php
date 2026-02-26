<?php

namespace Giogiosw\Finder\Drivers;

use Giogiosw\Finder\Support\FileInfo;
use Giogiosw\Finder\Support\MimeDetector;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;

abstract class AbstractDriver implements DriverInterface
{
    protected FilesystemAdapter $storage;
    protected string $disk;
    protected array $config;

    public function __construct(FilesystemAdapter $storage, string $disk, array $config)
    {
        $this->storage = $storage;
        $this->disk = $disk;
        $this->config = $config;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function encodeHash(string $path): string
    {
        return base64_encode($this->disk . ':' . $path);
    }

    public function decodeHash(string $hash): string
    {
        $decoded = base64_decode($hash);
        $colonPos = strpos($decoded, ':');
        if ($colonPos === false) {
            return $decoded;
        }
        return substr($decoded, $colonPos + 1);
    }

    public function decodeDiskFromHash(string $hash): string
    {
        $decoded = base64_decode($hash);
        $colonPos = strpos($decoded, ':');
        if ($colonPos === false) {
            return $this->disk;
        }
        return substr($decoded, 0, $colonPos);
    }

    public function exists(string $path): bool
    {
        return $this->storage->exists($path);
    }

    public function get(string $path): ?string
    {
        try {
            return $this->storage->get($path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function put(string $path, string $content): bool
    {
        try {
            return $this->storage->put($path, $content);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function mkfile(string $path): bool
    {
        try {
            return $this->storage->put($path, '');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function mkdir(string $path): bool
    {
        try {
            return $this->storage->makeDirectory($path);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function rename(string $from, string $to): bool
    {
        try {
            return $this->storage->move($from, $to);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function move(string $from, string $to): bool
    {
        try {
            return $this->storage->move($from, $to);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function copy(string $from, string $to): bool
    {
        try {
            if ($this->storage->directoryExists($from)) {
                return $this->copyDirectory($from, $to);
            }
            return $this->storage->copy($from, $to);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function copyDirectory(string $from, string $to): bool
    {
        $this->storage->makeDirectory($to);

        $files = $this->storage->allFiles($from);
        foreach ($files as $file) {
            $relativePath = substr($file, strlen($from) + 1);
            $this->storage->copy($file, $to . '/' . $relativePath);
        }

        return true;
    }

    public function delete(array $paths): bool
    {
        $success = true;

        foreach ($paths as $path) {
            try {
                if ($this->storage->directoryExists($path)) {
                    if (!$this->storage->deleteDirectory($path)) {
                        $success = false;
                    }
                } else {
                    if (!$this->storage->delete($path)) {
                        $success = false;
                    }
                }
            } catch (\Throwable $e) {
                $success = false;
            }
        }

        return $success;
    }

    public function upload(string $directory, UploadedFile $file, ?string $name = null): ?FileInfo
    {
        try {
            $filename = $name ?? $file->getClientOriginalName();
            $filename = $this->sanitizeFilename($filename);

            // Check denied extensions
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $denied = $this->config['upload']['denied_extensions'] ?? [];
            if (in_array($ext, $denied)) {
                return null;
            }

            // Check file size
            $maxSize = $this->config['upload']['max_size'] ?? 52428800;
            if ($file->getSize() > $maxSize) {
                return null;
            }

            $path = ($directory === '/' || $directory === '') ? $filename : trim($directory, '/') . '/' . $filename;

            // Handle duplicate names
            $path = $this->getUniqueFilename($path);

            $this->storage->put($path, file_get_contents($file->getRealPath()));

            return $this->info($path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function sanitizeFilename(string $filename): string
    {
        // Remove dangerous characters
        $filename = preg_replace('/[^\w\s\-_.()[\]{}]/', '', $filename);
        $filename = trim($filename);

        if (empty($filename)) {
            $filename = 'file_' . uniqid();
        }

        return $filename;
    }

    protected function getUniqueFilename(string $path): string
    {
        if (!$this->storage->exists($path)) {
            return $path;
        }

        $dir = dirname($path);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $counter = 1;

        do {
            $newPath = ($dir === '.' ? '' : $dir . '/') . $filename . '_' . $counter . ($ext ? '.' . $ext : '');
            $counter++;
        } while ($this->storage->exists($newPath));

        return $newPath;
    }

    protected function buildFileInfo(string $path): ?FileInfo
    {
        try {
            $isDir = $this->storage->directoryExists($path);
            $name = basename($path);
            $parentPath = dirname($path);
            $parentHash = $parentPath === '.' ? $this->encodeHash('/') : $this->encodeHash($parentPath);

            if ($isDir) {
                $size = 0;
            } else {
                $size = $this->storage->size($path);
            }

            $modified = $this->storage->lastModified($path);
            $mime = $isDir ? 'directory' : MimeDetector::fromPath($path);

            $hidden = $this->config['permissions']['hidden'] ?? [];
            $locked = in_array($name, $this->config['permissions']['locked'] ?? []);

            // Skip hidden files
            if (in_array($name, $hidden) || (str_starts_with($name, '.') && !config('finder.ui.show_hidden', false))) {
                if (!in_array($name, ['.', '..'])) {
                    // Only skip dots-prefixed items based on show_hidden config
                    if (str_starts_with($name, '.') && !config('finder.ui.show_hidden', false)) {
                        return null;
                    }
                }
            }

            return new FileInfo(
                hash: $this->encodeHash($path),
                name: $name,
                path: $path,
                disk: $this->disk,
                isDir: $isDir,
                size: $size,
                modified: $modified,
                mime: $mime,
                readable: true,
                writable: (bool) ($this->config['permissions']['write'] ?? true),
                locked: $locked,
                parentHash: $parentHash,
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function ls(string $path): array
    {
        $path = $path === '/' ? '' : trim($path, '/');

        $items = [];

        try {
            $directories = $this->storage->directories($path);
            foreach ($directories as $dir) {
                $info = $this->buildFileInfo($dir);
                if ($info !== null) {
                    $items[] = $info;
                }
            }

            $files = $this->storage->files($path);
            foreach ($files as $file) {
                $info = $this->buildFileInfo($file);
                if ($info !== null) {
                    $items[] = $info;
                }
            }
        } catch (\Throwable $e) {
            // Return empty on error
        }

        return $items;
    }

    public function info(string $path): ?FileInfo
    {
        if (!$this->storage->exists($path) && !$this->storage->directoryExists($path)) {
            return null;
        }

        return $this->buildFileInfo($path);
    }

    public function search(string $query, string $path = '/'): array
    {
        $path = $path === '/' ? '' : trim($path, '/');
        $results = [];
        $query = strtolower($query);

        try {
            $allFiles = $this->storage->allFiles($path);
            foreach ($allFiles as $file) {
                if (str_contains(strtolower(basename($file)), $query)) {
                    $info = $this->buildFileInfo($file);
                    if ($info !== null) {
                        $results[] = $info;
                    }
                }
            }

            $allDirs = $this->storage->allDirectories($path);
            foreach ($allDirs as $dir) {
                if (str_contains(strtolower(basename($dir)), $query)) {
                    $info = $this->buildFileInfo($dir);
                    if ($info !== null) {
                        $results[] = $info;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Return empty on error
        }

        return $results;
    }
}
