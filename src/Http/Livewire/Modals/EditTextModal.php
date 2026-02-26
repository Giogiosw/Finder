<?php

namespace Giogiosw\Finder\Http\Livewire\Modals;

use Giogiosw\Finder\FinderManager;
use Livewire\Component;

class EditTextModal extends Component
{
    public bool $open = false;
    public string $disk = '';
    public string $targetHash = '';
    public string $filename = '';
    public string $content = '';
    public string $mime = 'text/plain';
    public ?string $error = null;
    public bool $isDirty = false;
    public bool $isSaving = false;

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
        'finder:open-file'         => 'onOpenFile',
    ];

    public function onOpenModal(string $modal): void
    {
        if ($modal === 'edit-text' && !empty($this->targetHash)) {
            $this->loadContent();
        }
    }

    public function onSelectionChanged(array $selected): void
    {
        $this->targetHash = count($selected) === 1 ? $selected[0] : '';
    }

    public function onOpenFile(string $hash): void
    {
        $driver = $this->finderManager->driver($this->disk);
        $path = $driver->decodeHash($hash);
        $info = $driver->info($path);

        if ($info && $info->isText()) {
            $this->targetHash = $hash;
            $this->loadContent();
        }
    }

    protected function loadContent(): void
    {
        $this->error = null;

        $driver = $this->finderManager->driver($this->disk);
        $path = $driver->decodeHash($this->targetHash);
        $info = $driver->info($path);

        if (!$info) {
            $this->error = 'File not found.';
            return;
        }

        $maxSize = config('finder.text_editing.max_size', 2097152);
        if ($info->size > $maxSize) {
            $this->error = 'File is too large to edit (max ' . round($maxSize / 1024 / 1024, 1) . 'MB).';
            return;
        }

        $content = $driver->get($path);

        if ($content === null) {
            $this->error = 'Unable to read file.';
            return;
        }

        $this->filename = $info->name;
        $this->content = $content;
        $this->mime = $info->mime;
        $this->isDirty = false;
        $this->open = true;
    }

    public function updatedContent(): void
    {
        $this->isDirty = true;
    }

    public function save(): void
    {
        $this->error = null;
        $this->isSaving = true;

        $driver = $this->finderManager->driver($this->disk);
        $path = $driver->decodeHash($this->targetHash);

        if (!$driver->put($path, $this->content)) {
            $this->error = 'Unable to save file.';
            $this->isSaving = false;
            return;
        }

        $this->isDirty = false;
        $this->isSaving = false;
        $this->dispatch('finder:items-changed');
    }

    public function saveAndClose(): void
    {
        $this->save();
        if (!$this->error) {
            $this->close();
        }
    }

    public function close(): void
    {
        if ($this->isDirty) {
            // Let Alpine.js handle the confirmation in the view
            $this->dispatch('finder:confirm-close-editor');
            return;
        }

        $this->forceClose();
    }

    public function forceClose(): void
    {
        $this->open = false;
        $this->content = '';
        $this->targetHash = '';
        $this->filename = '';
        $this->isDirty = false;
        $this->error = null;
    }

    public function getLanguageProperty(): string
    {
        $ext = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'js'         => 'javascript',
            'ts'         => 'typescript',
            'php'        => 'php',
            'py'         => 'python',
            'rb'         => 'ruby',
            'html', 'htm'=> 'html',
            'css'        => 'css',
            'json'       => 'json',
            'xml'        => 'xml',
            'yaml', 'yml'=> 'yaml',
            'sh'         => 'bash',
            'sql'        => 'sql',
            'md'         => 'markdown',
            default      => 'plaintext',
        };
    }

    public function render()
    {
        return view('finder::livewire.modals.edit-text');
    }
}
