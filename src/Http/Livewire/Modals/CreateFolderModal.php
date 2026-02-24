<?php

namespace Giogiosw\Finder\Http\Livewire\Modals;

use Giogiosw\Finder\FinderManager;
use Livewire\Component;

class CreateFolderModal extends Component
{
    public bool $open = false;
    public string $disk = '';
    public string $targetPath = '/';
    public string $name = '';
    public string $mode = 'folder'; // 'folder' or 'file'
    public ?string $error = null;

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
        'finder:open-modal' => 'onOpenModal',
        'finder:navigate'   => 'onNavigate',
    ];

    public function onOpenModal(string $modal): void
    {
        if ($modal === 'create-folder') {
            $this->mode = 'folder';
            $this->name = '';
            $this->error = null;
            $this->open = true;
        } elseif ($modal === 'create-file') {
            $this->mode = 'file';
            $this->name = '';
            $this->error = null;
            $this->open = true;
        }
    }

    public function onNavigate(string $path): void
    {
        $this->targetPath = $path;
    }

    public function create(): void
    {
        $this->error = null;

        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($this->name);

        // Sanitize name
        $name = basename($name);
        $name = preg_replace('/[<>:"|?*\x00-\x1f\\\\\/]/', '', $name);

        if (empty($name)) {
            $this->error = 'Invalid name.';
            return;
        }

        $driver = $this->finderManager->driver($this->disk);
        $parent = $this->targetPath === '/' ? '' : trim($this->targetPath, '/');
        $newPath = $parent === '' ? $name : $parent . '/' . $name;

        $success = $this->mode === 'file'
            ? $driver->mkfile($newPath)
            : $driver->mkdir($newPath);

        if (!$success) {
            $this->error = "Unable to create {$this->mode}.";
            return;
        }

        $this->dispatch('finder:items-changed');
        $this->close();
    }

    public function close(): void
    {
        $this->open = false;
        $this->name = '';
        $this->error = null;
    }

    public function render()
    {
        return view('finder::livewire.modals.create-folder');
    }
}
