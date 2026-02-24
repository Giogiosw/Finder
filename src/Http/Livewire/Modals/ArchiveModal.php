<?php

namespace Giogiosw\Finder\Http\Livewire\Modals;

use Giogiosw\Finder\FinderManager;
use Giogiosw\Finder\Support\ArchiveHandler;
use Livewire\Component;

class ArchiveModal extends Component
{
    public bool $open = false;
    public string $disk = '';
    public string $targetPath = '/';
    public array $targetHashes = [];
    public string $archiveName = 'archive.zip';
    public string $archiveType = 'zip';
    public string $mode = 'create'; // 'create' or 'extract'
    public ?string $error = null;
    public bool $isProcessing = false;

    protected FinderManager $finderManager;

    public function boot(FinderManager $finderManager): void
    {
        $this->finderManager = $finderManager;
    }

    public function mount(string $disk = '', string $targetPath = '/'): void
    {
        $this->disk = $disk ?: config('finder.default_disk', 'local');
        $this->targetPath = $targetPath;
    }

    protected $listeners = [
        'finder:open-modal'        => 'onOpenModal',
        'finder:selection-changed' => 'onSelectionChanged',
        'finder:navigate'          => 'onNavigate',
    ];

    public function onOpenModal(string $modal): void
    {
        if ($modal === 'archive') {
            $this->mode = 'create';
            $this->archiveName = 'archive.zip';
            $this->archiveType = 'zip';
            $this->error = null;
            $this->open = true;
        } elseif ($modal === 'extract') {
            $this->mode = 'extract';
            $this->error = null;
            $this->open = true;
        }
    }

    public function onSelectionChanged(array $selected): void
    {
        $this->targetHashes = $selected;
    }

    public function onNavigate(string $path): void
    {
        $this->targetPath = $path;
    }

    public function updatedArchiveType(): void
    {
        $name = pathinfo($this->archiveName, PATHINFO_FILENAME);
        $this->archiveName = $name . '.' . $this->archiveType;
    }

    public function process(): void
    {
        $this->error = null;
        $this->isProcessing = true;

        $driver = $this->finderManager->driver($this->disk);
        $paths = array_map(fn($hash) => $driver->decodeHash($hash), $this->targetHashes);

        $archiver = new ArchiveHandler(config('finder'));
        $destination = $this->targetPath === '/' ? '' : trim($this->targetPath, '/');

        if ($this->mode === 'create') {
            $archiveDest = ($destination === '' ? '' : $destination . '/') . basename($this->archiveName);

            $success = match ($this->archiveType) {
                'tar.gz' => $archiver->createTarGz($this->disk, $paths, $archiveDest),
                default  => $archiver->createZip($this->disk, $paths, $archiveDest),
            };
        } else {
            // Extract
            $archivePath = $driver->decodeHash($this->targetHashes[0] ?? '');
            $success = $archiver->extractZip($this->disk, $archivePath, $destination);
        }

        $this->isProcessing = false;

        if (!$success) {
            $this->error = $this->mode === 'create'
                ? 'Failed to create archive.'
                : 'Failed to extract archive.';
            return;
        }

        $this->dispatch('finder:items-changed');
        $this->close();
    }

    public function close(): void
    {
        $this->open = false;
        $this->error = null;
        $this->isProcessing = false;
    }

    public function render()
    {
        return view('finder::livewire.modals.archive');
    }
}
