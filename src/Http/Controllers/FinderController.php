<?php

namespace Giogiosw\Finder\Http\Controllers;

use Giogiosw\Finder\FinderManager;
use Giogiosw\Finder\Support\ArchiveHandler;
use Giogiosw\Finder\Support\ImageHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class FinderController extends Controller
{
    public function __construct(
        protected FinderManager $manager,
    ) {}

    /**
     * Show the file manager page.
     */
    public function index()
    {
        return view('finder::layouts.app');
    }

    /**
     * List directory contents.
     */
    public function ls(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $path = $this->decodePath($request->input('target', '/'));

        $driver = $this->manager->driver($disk);
        $items = $driver->ls($path);

        return response()->json([
            'list' => array_map(fn($item) => $item->toArray(), $items),
            'cwd' => $driver->info($path) ? $driver->info($path)->toArray() : null,
        ]);
    }

    /**
     * Get info for one or more items.
     */
    public function info(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $targets = $request->input('targets', []);

        if (!is_array($targets)) {
            $targets = [$targets];
        }

        $driver = $this->manager->driver($disk);
        $files = [];

        foreach ($targets as $hash) {
            $path = $driver->decodeHash($hash);
            $info = $driver->info($path);
            if ($info) {
                $files[] = $info->toArray();
            }
        }

        return response()->json(['files' => $files]);
    }

    /**
     * Create a directory.
     */
    public function mkdir(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);

        $disk = $request->input('disk', config('finder.default_disk'));
        $parent = $this->decodePath($request->input('target', '/'));
        $name = $request->input('name');
        $name = $this->sanitizeName($name);

        $driver = $this->manager->driver($disk);
        $newPath = ($parent === '/' || $parent === '') ? $name : rtrim($parent, '/') . '/' . $name;

        if (!$driver->mkdir($newPath)) {
            return response()->json(['error' => 'Unable to create directory'], 422);
        }

        $info = $driver->info($newPath);

        return response()->json([
            'added' => $info ? [$info->toArray()] : [],
        ]);
    }

    /**
     * Create a new empty file.
     */
    public function mkfile(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);

        $disk = $request->input('disk', config('finder.default_disk'));
        $parent = $this->decodePath($request->input('target', '/'));
        $name = $request->input('name');
        $name = $this->sanitizeName($name);

        $driver = $this->manager->driver($disk);
        $newPath = ($parent === '/' || $parent === '') ? $name : rtrim($parent, '/') . '/' . $name;

        if (!$driver->mkfile($newPath)) {
            return response()->json(['error' => 'Unable to create file'], 422);
        }

        $info = $driver->info($newPath);

        return response()->json([
            'added' => $info ? [$info->toArray()] : [],
        ]);
    }

    /**
     * Rename a file or directory.
     */
    public function rename(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);

        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');
        $newName = $request->input('name');
        $newName = $this->sanitizeName($newName);

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);
        $dir = dirname($path);
        $newPath = ($dir === '.' ? '' : $dir . '/') . $newName;

        if (!$driver->rename($path, $newPath)) {
            return response()->json(['error' => 'Unable to rename'], 422);
        }

        $info = $driver->info($newPath);

        return response()->json([
            'added' => $info ? [$info->toArray()] : [],
            'removed' => [$hash],
        ]);
    }

    /**
     * Copy files/directories.
     */
    public function copy(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $targets = $request->input('targets', []);
        $destinationHash = $request->input('dst');

        $driver = $this->manager->driver($disk);
        $destination = $driver->decodeHash($destinationHash);

        $added = [];

        foreach ($targets as $hash) {
            $path = $driver->decodeHash($hash);
            $name = basename($path);
            $newPath = ($destination === '/' || $destination === '') ? $name : rtrim($destination, '/') . '/' . $name;

            if ($driver->copy($path, $newPath)) {
                $info = $driver->info($newPath);
                if ($info) {
                    $added[] = $info->toArray();
                }
            }
        }

        return response()->json(['added' => $added]);
    }

    /**
     * Move files/directories.
     */
    public function move(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $targets = $request->input('targets', []);
        $destinationHash = $request->input('dst');

        $driver = $this->manager->driver($disk);
        $destination = $driver->decodeHash($destinationHash);

        $added = [];
        $removed = [];

        foreach ($targets as $hash) {
            $path = $driver->decodeHash($hash);
            $name = basename($path);
            $newPath = ($destination === '/' || $destination === '') ? $name : rtrim($destination, '/') . '/' . $name;

            if ($driver->move($path, $newPath)) {
                $info = $driver->info($newPath);
                if ($info) {
                    $added[] = $info->toArray();
                }
                $removed[] = $hash;
            }
        }

        return response()->json(['added' => $added, 'removed' => $removed]);
    }

    /**
     * Delete files/directories.
     */
    public function rm(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $targets = $request->input('targets', []);

        $driver = $this->manager->driver($disk);
        $paths = array_map(fn($hash) => $driver->decodeHash($hash), $targets);

        if (!$driver->delete($paths)) {
            return response()->json(['error' => 'Unable to delete some items'], 422);
        }

        return response()->json(['removed' => $targets]);
    }

    /**
     * Upload file(s).
     */
    public function upload(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $target = $this->decodePath($request->input('target', '/'));

        $driver = $this->manager->driver($disk);
        $added = [];
        $errors = [];

        foreach ($request->file('upload', []) as $file) {
            $info = $driver->upload($target, $file);
            if ($info) {
                $added[] = $info->toArray();
            } else {
                $errors[] = $file->getClientOriginalName();
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'added' => $added,
                'warning' => 'Unable to upload: ' . implode(', ', $errors),
            ]);
        }

        return response()->json(['added' => $added]);
    }

    /**
     * Download a file.
     */
    public function download(Request $request): Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);

        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            abort(404);
        }

        return $storage->download($path);
    }

    /**
     * Get file content (for text editing).
     */
    public function getContent(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);

        $content = $driver->get($path);

        if ($content === null) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $maxSize = config('finder.text_editing.max_size', 2097152);
        if (strlen($content) > $maxSize) {
            return response()->json(['error' => 'File too large to edit'], 422);
        }

        return response()->json(['content' => $content]);
    }

    /**
     * Save file content (for text editing).
     */
    public function putContent(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');
        $content = $request->input('content', '');

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);

        if (!$driver->put($path, $content)) {
            return response()->json(['error' => 'Unable to save file'], 422);
        }

        $info = $driver->info($path);

        return response()->json([
            'changed' => $info ? [$info->toArray()] : [],
        ]);
    }

    /**
     * Search for files.
     */
    public function search(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $query = $request->input('q', '');
        $path = $this->decodePath($request->input('target', '/'));

        $driver = $this->manager->driver($disk);
        $results = $driver->search($query, $path);

        return response()->json([
            'files' => array_map(fn($item) => $item->toArray(), $results),
        ]);
    }

    /**
     * Create archive from selection.
     */
    public function archive(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $targets = $request->input('targets', []);
        $name = $request->input('name', 'archive.zip');
        $type = $request->input('type', 'zip');
        $targetDir = $this->decodePath($request->input('target', '/'));

        $driver = $this->manager->driver($disk);
        $paths = array_map(fn($hash) => $driver->decodeHash($hash), $targets);

        $destination = ($targetDir === '/' || $targetDir === '') ? $name : rtrim($targetDir, '/') . '/' . $name;

        $archiver = new ArchiveHandler(config('finder'));

        $success = match ($type) {
            'tar.gz' => $archiver->createTarGz($disk, $paths, $destination),
            default  => $archiver->createZip($disk, $paths, $destination),
        };

        if (!$success) {
            return response()->json(['error' => 'Unable to create archive'], 422);
        }

        $info = $driver->info($destination);

        return response()->json([
            'added' => $info ? [$info->toArray()] : [],
        ]);
    }

    /**
     * Extract an archive.
     */
    public function extract(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');
        $destinationHash = $request->input('dst');

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);
        $destination = $destinationHash ? $driver->decodeHash($destinationHash) : dirname($path);

        $archiver = new ArchiveHandler(config('finder'));
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $success = match ($ext) {
            'gz', 'bz2' => $archiver->extractTarGz($disk, $path, $destination),
            default      => $archiver->extractZip($disk, $path, $destination),
        };

        if (!$success) {
            return response()->json(['error' => 'Unable to extract archive'], 422);
        }

        $items = $driver->ls($destination);

        return response()->json([
            'added' => array_map(fn($item) => $item->toArray(), $items),
        ]);
    }

    /**
     * Resize/rotate/flip an image.
     */
    public function editImage(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');
        $action = $request->input('action');

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);

        $imageHandler = new ImageHandler(config('finder'));
        $success = false;

        switch ($action) {
            case 'resize':
                $success = $imageHandler->resize(
                    $disk, $path,
                    (int) $request->input('width'),
                    (int) $request->input('height'),
                    (bool) $request->input('keepAspect', true)
                );
                break;
            case 'rotate':
                $success = $imageHandler->rotate($disk, $path, (int) $request->input('degrees'));
                break;
            case 'flip':
                $success = $imageHandler->flip($disk, $path, $request->input('direction', 'horizontal'));
                break;
            case 'crop':
                $success = $imageHandler->crop(
                    $disk, $path,
                    (int) $request->input('x'),
                    (int) $request->input('y'),
                    (int) $request->input('width'),
                    (int) $request->input('height')
                );
                break;
        }

        if (!$success) {
            return response()->json(['error' => 'Image edit failed'], 422);
        }

        $info = $driver->info($path);

        return response()->json([
            'changed' => $info ? [$info->toArray()] : [],
        ]);
    }

    /**
     * Get directory size.
     */
    public function size(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $targets = $request->input('targets', []);

        $driver = $this->manager->driver($disk);
        $storage = Storage::disk($disk);

        $totalSize = 0;
        $sizes = [];

        foreach ($targets as $hash) {
            $path = $driver->decodeHash($hash);
            $size = 0;

            if ($storage->directoryExists($path)) {
                $files = $storage->allFiles($path);
                foreach ($files as $file) {
                    $size += $storage->size($file);
                }
            } else {
                $size = $storage->size($path);
            }

            $sizes[$hash] = $size;
            $totalSize += $size;
        }

        return response()->json(['sizes' => $sizes, 'total' => $totalSize]);
    }

    /**
     * Get a temporary URL for a file (for preview).
     */
    public function url(Request $request): JsonResponse
    {
        $disk = $request->input('disk', config('finder.default_disk'));
        $hash = $request->input('target');

        $driver = $this->manager->driver($disk);
        $path = $driver->decodeHash($hash);

        $url = $driver->temporaryUrl($path, 60);

        return response()->json(['url' => $url]);
    }

    protected function decodePath(string $hash): string
    {
        if ($hash === '/' || $hash === '') {
            return '/';
        }

        // Try base64 decode (our format)
        $decoded = base64_decode($hash, true);
        if ($decoded !== false && str_contains($decoded, ':')) {
            $colonPos = strpos($decoded, ':');
            return substr($decoded, $colonPos + 1);
        }

        return $hash;
    }

    protected function sanitizeName(string $name): string
    {
        // Remove path traversal attempts
        $name = basename($name);
        // Remove dangerous characters
        $name = preg_replace('/[<>:"|?*\x00-\x1f]/', '', $name);
        return trim($name);
    }
}
