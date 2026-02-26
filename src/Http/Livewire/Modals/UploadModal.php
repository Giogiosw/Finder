<?php

namespace Giogiosw\Finder\Http\Livewire\Modals;

use Giogiosw\Finder\FinderManager;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadModal extends Component
{
    use WithFileUploads;

    public bool $open = false;
    public string $disk = '';
    public string $targetPath = '/';
    public array $uploads = [];
    public array $uploadedFiles = [];
    public bool $isUploading = false;
    public array $errors = [];
    public array $uploadProgress = [];
    public bool $overwrite = false;

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
        if ($modal === 'upload') {
            $this->open = true;
            $this->errors = [];
            $this->uploadedFiles = [];
        }
    }

    public function onNavigate(string $path): void
    {
        $this->targetPath = $path;
    }

    public function updatedUploads(): void
    {
        $this->validate([
            'uploads.*' => [
                'file',
                'max:' . (config('finder.upload.max_size', 52428800) / 1024),
            ],
        ]);
    }

    public function uploadFiles(): void
    {
        $this->isUploading = true;
        $this->errors = [];

        try {
            $driver = $this->finderManager->driver($this->disk);
        } catch (\Throwable $e) {
            $this->errors[] = __('finder::finder.upload_storage_error');
            $this->isUploading = false;
            return;
        }

        $added = [];

        foreach ($this->uploads as $file) {
            $info = $driver->upload($this->targetPath, $file);

            if ($info) {
                $added[] = $info->name;
                $this->uploadedFiles[] = $info->toArray();
            } else {
                $this->errors[] = $file->getClientOriginalName();
            }
        }

        $this->uploads = [];
        $this->isUploading = false;

        if (!empty($added)) {
            $this->dispatch('finder:items-changed');
            $this->dispatch('finder:upload-complete', files: $added);
        }

        if (empty($this->errors)) {
            $this->close();
        }
    }

    public function close(): void
    {
        $this->open = false;
        $this->uploads = [];
        $this->errors = [];
        $this->uploadedFiles = [];
    }

    public function render()
    {
        return view('finder::livewire.modals.upload');
    }
}
