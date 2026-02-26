<?php

namespace Giogiosw\Finder\Http\Livewire\Modals;

use Giogiosw\Finder\FinderManager;
use Giogiosw\Finder\Support\FileInfo;
use Giogiosw\Finder\Support\ImageHandler;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class PropertiesModal extends Component
{
    public bool $open = false;
    public string $disk = '';
    public array $targetHashes = [];
    public ?array $fileInfo = null;
    public ?string $directorySize = null;
    public bool $calculatingSize = false;

    protected FinderManager $finderManager;

    public function boot(FinderManager $finderManager): void
    {
        $this->finderManager = $finderManager;
    }

    public function mount(string $disk = ''): void
    {
        $this->disk = $disk ?: config('finder.default_disk', 'local');
    }

    protected $listeners = [
        'finder:open-modal'        => 'onOpenModal',
        'finder:selection-changed' => 'onSelectionChanged',
    ];

    public function onOpenModal(string $modal): void
    {
        if ($modal === 'properties' && !empty($this->targetHashes)) {
            $this->loadFileInfo();
            $this->open = true;
        }
    }

    public function onSelectionChanged(array $selected): void
    {
        $this->targetHashes = $selected;
    }

    protected function loadFileInfo(): void
    {
        if (empty($this->targetHashes)) {
            return;
        }

        $driver = $this->finderManager->driver($this->disk);

        if (count($this->targetHashes) === 1) {
            $path = $driver->decodeHash($this->targetHashes[0]);
            $info = $driver->info($path);

            if ($info) {
                $data = $info->toArray();
                $data['path'] = $info->path;
                $data['is_dir'] = $info->isDir;
                $data['size_human'] = $info->sizeForHumans();
                $data['modified_human'] = $info->modifiedForHumans();
                $data['extension'] = $info->extension();
                $data['icon'] = $info->iconClass();

                if ($info->isImage()) {
                    $imageHandler = new ImageHandler(config('finder'));
                    $dims = $imageHandler->getDimensions($this->disk, $info->path);
                    if ($dims) {
                        $data['dimensions'] = $dims['width'] . ' × ' . $dims['height'] . ' px';
                    }
                }

                $this->fileInfo = $data;
            }
        } else {
            // Multiple files selected
            $this->fileInfo = [
                'name'          => count($this->targetHashes) . ' items selected',
                'is_dir'        => false,
                'size_human'    => 'Calculating...',
                'modified_human'=> '',
                'mime'          => 'multiple',
            ];
        }
    }

    public function calculateDirectorySize(): void
    {
        if (empty($this->targetHashes)) {
            return;
        }

        $this->calculatingSize = true;
        $driver = $this->finderManager->driver($this->disk);
        $storage = Storage::disk($this->disk);
        $totalSize = 0;

        foreach ($this->targetHashes as $hash) {
            $path = $driver->decodeHash($hash);

            if ($storage->directoryExists($path)) {
                $files = $storage->allFiles($path);
                foreach ($files as $file) {
                    $totalSize += $storage->size($file);
                }
            } else {
                $totalSize += $storage->size($path);
            }
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $totalSize;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        $this->directorySize = round($size, 2) . ' ' . $units[$i];
        $this->calculatingSize = false;

        if ($this->fileInfo) {
            $this->fileInfo['size_human'] = $this->directorySize;
        }
    }

    public function close(): void
    {
        $this->open = false;
        $this->fileInfo = null;
        $this->directorySize = null;
    }

    public function render()
    {
        return view('finder::livewire.modals.properties');
    }
}
