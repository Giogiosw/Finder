<?php

namespace Giogiosw\Finder\Http\Livewire\Modals;

use Giogiosw\Finder\FinderManager;
use Livewire\Component;

class RenameModal extends Component
{
    public bool $open = false;
    public string $disk = '';
    public string $targetHash = '';
    public string $name = '';
    public string $originalName = '';
    public ?string $error = null;

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
        if ($modal === 'rename' && !empty($this->targetHash)) {
            $driver = $this->finderManager->driver($this->disk);
            $path = $driver->decodeHash($this->targetHash);
            $info = $driver->info($path);

            if ($info) {
                $this->name = $info->name;
                $this->originalName = $info->name;
                $this->error = null;
                $this->open = true;
            }
        }
    }

    public function onSelectionChanged(array $selected): void
    {
        $this->targetHash = count($selected) === 1 ? $selected[0] : '';
    }

    public function rename(): void
    {
        $this->error = null;

        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $newName = trim($this->name);
        $newName = basename($newName);
        $newName = preg_replace('/[<>:"|?*\x00-\x1f\\\\\/]/', '', $newName);

        if (empty($newName)) {
            $this->error = 'Invalid name.';
            return;
        }

        if ($newName === $this->originalName) {
            $this->close();
            return;
        }

        $driver = $this->finderManager->driver($this->disk);
        $path = $driver->decodeHash($this->targetHash);
        $dir = dirname($path);
        $newPath = ($dir === '.' ? '' : $dir . '/') . $newName;

        if (!$driver->rename($path, $newPath)) {
            $this->error = 'Unable to rename. The name may already be taken.';
            return;
        }

        $this->dispatch('finder:items-changed');
        $this->close();
    }

    public function close(): void
    {
        $this->open = false;
        $this->name = '';
        $this->originalName = '';
        $this->error = null;
    }

    public function render()
    {
        return view('finder::livewire.modals.rename');
    }
}
