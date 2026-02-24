<?php

namespace Giogiosw\Finder\Http\Livewire;

use Livewire\Component;

class Toolbar extends Component
{
    public array $selected = [];
    public string $viewMode = 'grid';
    public string $clipboardAction = '';
    public int $clipboardCount = 0;
    public bool $canWrite = true;
    public bool $canDelete = true;
    public bool $canUpload = true;
    public bool $canArchive = true;

    protected $listeners = [
        'finder:clipboard-updated' => 'onClipboardUpdated',
    ];

    public function mount(array $selected = [], string $viewMode = 'grid'): void
    {
        $this->selected = $selected;
        $this->viewMode = $viewMode;
        $this->canWrite = config('finder.permissions.write', true);
        $this->canDelete = config('finder.permissions.rm', true);
        $this->canUpload = config('finder.permissions.upload', true);
        $this->canArchive = config('finder.permissions.archive', true);
    }

    public function onClipboardUpdated(int $count, string $action): void
    {
        $this->clipboardCount = $count;
        $this->clipboardAction = $action;
    }

    public function openUpload(): void
    {
        $this->dispatch('finder:open-modal', modal: 'upload');
    }

    public function openCreateFolder(): void
    {
        $this->dispatch('finder:open-modal', modal: 'create-folder');
    }

    public function openCreateFile(): void
    {
        $this->dispatch('finder:open-modal', modal: 'create-file');
    }

    public function openRename(): void
    {
        $this->dispatch('finder:open-modal', modal: 'rename');
    }

    public function openProperties(): void
    {
        $this->dispatch('finder:open-modal', modal: 'properties');
    }

    public function openArchive(): void
    {
        $this->dispatch('finder:open-modal', modal: 'archive');
    }

    public function openExtract(): void
    {
        $this->dispatch('finder:open-modal', modal: 'extract');
    }

    public function openEditText(): void
    {
        $this->dispatch('finder:open-modal', modal: 'edit-text');
    }

    public function openEditImage(): void
    {
        $this->dispatch('finder:open-modal', modal: 'edit-image');
    }

    public function triggerDelete(): void
    {
        $this->dispatch('finder:confirm-delete');
    }

    public function triggerCopy(): void
    {
        $this->dispatch('finder:copy-selected');
    }

    public function triggerCut(): void
    {
        $this->dispatch('finder:cut-selected');
    }

    public function triggerPaste(): void
    {
        $this->dispatch('finder:paste');
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->dispatch('finder:view-mode-changed', mode: $mode);
    }

    public function render()
    {
        return view('finder::livewire.toolbar');
    }
}
