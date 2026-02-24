<?php

namespace Giogiosw\Finder\Support;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArchiveHandler
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create a ZIP archive from a list of paths on a given disk.
     */
    public function createZip(string $disk, array $paths, string $destination): bool
    {
        try {
            $storage = Storage::disk($disk);
            $zipPath = sys_get_temp_dir() . '/' . uniqid('finder_', true) . '.zip';

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return false;
            }

            foreach ($paths as $path) {
                if ($storage->directoryExists($path)) {
                    $this->addDirectoryToZip($zip, $storage, $path, basename($path));
                } elseif ($storage->fileExists($path)) {
                    $contents = $storage->get($path);
                    $zip->addFromString(basename($path), $contents);
                }
            }

            $zip->close();

            $storage->put($destination, file_get_contents($zipPath));
            unlink($zipPath);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Extract a ZIP archive to a directory.
     */
    public function extractZip(string $disk, string $archivePath, string $destination): bool
    {
        try {
            $storage = Storage::disk($disk);
            $archiveContents = $storage->get($archivePath);

            $tmpFile = sys_get_temp_dir() . '/' . uniqid('finder_zip_', true) . '.zip';
            $tmpDir = sys_get_temp_dir() . '/' . uniqid('finder_extract_', true);

            file_put_contents($tmpFile, $archiveContents);
            mkdir($tmpDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($tmpFile) !== true) {
                unlink($tmpFile);
                return false;
            }

            $zip->extractTo($tmpDir);
            $zip->close();
            unlink($tmpFile);

            // Upload extracted files to storage
            $this->uploadDirectory($storage, $tmpDir, $destination);

            // Cleanup temp directory
            $this->deleteDirectory($tmpDir);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Create a tar.gz archive.
     */
    public function createTarGz(string $disk, array $paths, string $destination): bool
    {
        try {
            $storage = Storage::disk($disk);
            $tmpDir = sys_get_temp_dir() . '/' . uniqid('finder_tar_', true);
            mkdir($tmpDir, 0755, true);

            // Copy files to temp directory
            foreach ($paths as $path) {
                if ($storage->directoryExists($path)) {
                    $this->downloadDirectory($storage, $path, $tmpDir . '/' . basename($path));
                } elseif ($storage->fileExists($path)) {
                    file_put_contents($tmpDir . '/' . basename($path), $storage->get($path));
                }
            }

            $tarPath = sys_get_temp_dir() . '/' . uniqid('finder_', true) . '.tar.gz';

            $phar = new \PharData($tarPath);
            $phar->buildFromDirectory($tmpDir);
            $phar->compress(\Phar::GZ);

            $gzPath = $tarPath . '.gz';
            if (file_exists($gzPath)) {
                $storage->put($destination, file_get_contents($gzPath));
                unlink($gzPath);
            }

            unlink($tarPath);
            $this->deleteDirectory($tmpDir);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Extract a tar.gz archive.
     */
    public function extractTarGz(string $disk, string $archivePath, string $destination): bool
    {
        try {
            $storage = Storage::disk($disk);
            $archiveContents = $storage->get($archivePath);

            $tmpFile = sys_get_temp_dir() . '/' . uniqid('finder_', true) . '.tar.gz';
            $tmpDir = sys_get_temp_dir() . '/' . uniqid('finder_extract_', true);

            file_put_contents($tmpFile, $archiveContents);
            mkdir($tmpDir, 0755, true);

            $phar = new \PharData($tmpFile);
            $phar->decompress();

            $tarFile = str_replace('.gz', '', $tmpFile);
            $tarPhar = new \PharData($tarFile);
            $tarPhar->extractTo($tmpDir);

            unlink($tmpFile);
            if (file_exists($tarFile)) {
                unlink($tarFile);
            }

            $this->uploadDirectory($storage, $tmpDir, $destination);
            $this->deleteDirectory($tmpDir);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function addDirectoryToZip(ZipArchive $zip, $storage, string $path, string $zipBasePath): void
    {
        $zip->addEmptyDir($zipBasePath);

        $files = $storage->allFiles($path);
        foreach ($files as $file) {
            $relativePath = str_replace($path . '/', '', $file);
            $contents = $storage->get($file);
            $zip->addFromString($zipBasePath . '/' . $relativePath, $contents);
        }
    }

    protected function uploadDirectory($storage, string $localDir, string $remotePath): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($localDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($localDir . '/', '', $item->getPathname());
            $remoteDest = $remotePath . '/' . $relativePath;

            if ($item->isDir()) {
                $storage->makeDirectory($remoteDest);
            } else {
                $storage->put($remoteDest, file_get_contents($item->getPathname()));
            }
        }
    }

    protected function downloadDirectory($storage, string $remotePath, string $localDir): void
    {
        mkdir($localDir, 0755, true);

        $files = $storage->allFiles($remotePath);
        foreach ($files as $file) {
            $relativePath = str_replace($remotePath . '/', '', $file);
            $localFile = $localDir . '/' . $relativePath;

            $dir = dirname($localFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($localFile, $storage->get($file));
        }
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
