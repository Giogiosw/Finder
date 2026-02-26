<?php

namespace Giogiosw\Finder\Drivers;

use Giogiosw\Finder\Support\FileInfo;
use Illuminate\Http\UploadedFile;

interface DriverInterface
{
    /**
     * List contents of a directory.
     *
     * @return FileInfo[]
     */
    public function ls(string $path): array;

    /**
     * Get info about a single file or directory.
     */
    public function info(string $path): ?FileInfo;

    /**
     * Create a directory.
     */
    public function mkdir(string $path): bool;

    /**
     * Create a new empty file.
     */
    public function mkfile(string $path): bool;

    /**
     * Rename a file or directory.
     */
    public function rename(string $from, string $to): bool;

    /**
     * Copy a file or directory.
     */
    public function copy(string $from, string $to): bool;

    /**
     * Move a file or directory.
     */
    public function move(string $from, string $to): bool;

    /**
     * Delete files or directories.
     */
    public function delete(array $paths): bool;

    /**
     * Upload a file.
     */
    public function upload(string $directory, UploadedFile $file, ?string $name = null): ?FileInfo;

    /**
     * Get file contents.
     */
    public function get(string $path): ?string;

    /**
     * Put file contents.
     */
    public function put(string $path, string $content): bool;

    /**
     * Get a public or temporary URL for a file.
     */
    public function url(string $path): ?string;

    /**
     * Get a temporary download URL (for private disks).
     */
    public function temporaryUrl(string $path, int $minutes = 60): ?string;

    /**
     * Check if a path exists.
     */
    public function exists(string $path): bool;

    /**
     * Search files by name or content pattern.
     *
     * @return FileInfo[]
     */
    public function search(string $query, string $path = '/'): array;

    /**
     * Get the disk name.
     */
    public function getDisk(): string;

    /**
     * Encode a path to a hash.
     */
    public function encodeHash(string $path): string;

    /**
     * Decode a hash back to a path.
     */
    public function decodeHash(string $hash): string;
}
