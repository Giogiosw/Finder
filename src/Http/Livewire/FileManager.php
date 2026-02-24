<?php

namespace Giogiosw\Finder\Http\Livewire;

use Giogiosw\Finder\FinderManager;
use Giogiosw\Finder\Support\FileInfo;
use Livewire\Component;

class FileManager extends Component
{
    // Current disk and directory
    public string $disk = '';
    public string $currentPath = '/';

    // View mode: 'grid' or 'list'
    public string $viewMode = 'grid';

    // Sort settings
    public string $sortBy = 'name';
    public string $sortDir = 'asc';

    // Selected items (hashes)
    public array $selected = [];

    // Search
    public string $searchQuery = '';
    public bool $isSearching = false;

    // Clipboard
    public array $clipboard = [];
    public string $clipboardAction = ''; // 'copy' or 'cut'

    // UI state
    public bool $showHidden = false;
    public bool $isLoading = false;

    // Active modal
    public string $activeModal = '';

    protected FinderManager $finderManager;

    public function boot(FinderManager $finderManager): void
    {
        $this->finderManager = $finderManager;
    }

    public function mount(): void
    {
        $this->disk = config('finder.default_disk', 'local');
        $this->viewMode = config('finder.ui.default_view', 'grid');
        $this->sortBy = config('finder.ui.sort_by', 'name');
        $this->sortDir = config('finder.ui.sort_dir', 'asc');
        $this->showHidden = config('finder.ui.show_hidden', false);
    }

    public function getItemsProperty(): array
    {
        $driver = $this->finderManager->driver($this->disk);
        $path = $this->currentPath === '/' ? '' : trim($this->currentPath, '/');

        if ($this->isSearching && !empty($this->searchQuery)) {
            $items = $driver->search($this->searchQuery, $this->currentPath);
        } else {
            $items = $driver->ls($path);
        }

        // Filter hidden files
        if (!$this->showHidden) {
            $items = array_filter($items, fn(FileInfo $item) => !str_starts_with($item->name, '.'));
        }

        // Sort
        usort($items, function (FileInfo $a, FileInfo $b) {
            // Directories first
            if ($a->isDir !== $b->isDir) {
                return $a->isDir ? -1 : 1;
            }

            $result = match ($this->sortBy) {
                'size'     => $a->size <=> $b->size,
                'modified' => $a->modified <=> $b->modified,
                'type'     => strcmp($a->mime, $b->mime),
                default    => strcasecmp($a->name, $b->name),
            };

            return $this->sortDir === 'desc' ? -$result : $result;
        });

        return array_values($items);
    }

    public function getBreadcrumbsProperty(): array
    {
        $breadcrumbs = [
            ['name' => 'Home', 'path' => '/'],
        ];

        if ($this->currentPath !== '/') {
            $parts = explode('/', trim($this->currentPath, '/'));
            $accumulated = '';

            foreach ($parts as $part) {
                $accumulated .= '/' . $part;
                $breadcrumbs[] = [
                    'name' => $part,
                    'path' => $accumulated,
                ];
            }
        }

        return $breadcrumbs;
    }

    public function getAvailableDisksProperty(): array
    {
        return $this->finderManager->getAllDisks();
    }

    public function navigateTo(string $path): void
    {
        $this->currentPath = $path;
        $this->selected = [];
        $this->isSearching = false;
        $this->searchQuery = '';
    }

    public function navigateToHash(string $hash): void
    {
        $driver = $this->finderManager->driver($this->disk);
        $path = $driver->decodeHash($hash);
        $this->navigateTo($path);
    }

    public function selectItem(string $hash, bool $multi = false): void
    {
        if ($multi) {
            if (in_array($hash, $this->selected)) {
                $this->selected = array_values(array_filter($this->selected, fn($h) => $h !== $hash));
            } else {
                $this->selected[] = $hash;
            }
        } else {
            $this->selected = [$hash];
        }
    }

    public function selectAll(): void
    {
        $this->selected = array_map(fn($item) => $item->hash, $this->items);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function openItem(string $hash): void
    {
        $driver = $this->finderManager->driver($this->disk);
        $path = $driver->decodeHash($hash);
        $info = $driver->info($path);

        if ($info && $info->isDir) {
            $this->navigateTo($path);
        } else {
            // Dispatch event for preview/open
            $this->dispatch('finder:open-file', hash: $hash);
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['grid', 'list']) ? $mode : 'grid';
    }

    public function setSortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function switchDisk(string $disk): void
    {
        $this->disk = $disk;
        $this->currentPath = '/';
        $this->selected = [];
        $this->clipboard = [];
    }

    public function copySelected(): void
    {
        $this->clipboard = $this->selected;
        $this->clipboardAction = 'copy';
        $this->dispatch('finder:clipboard-updated', count: count($this->clipboard), action: 'copy');
    }

    public function cutSelected(): void
    {
        $this->clipboard = $this->selected;
        $this->clipboardAction = 'cut';
        $this->dispatch('finder:clipboard-updated', count: count($this->clipboard), action: 'cut');
    }

    public function paste(): void
    {
        if (empty($this->clipboard)) {
            return;
        }

        $driver = $this->finderManager->driver($this->disk);
        $destination = $this->currentPath === '/' ? '' : trim($this->currentPath, '/');

        $added = [];
        $removed = [];

        foreach ($this->clipboard as $hash) {
            $path = $driver->decodeHash($hash);
            $name = basename($path);
            $newPath = $destination === '' ? $name : $destination . '/' . $name;

            if ($this->clipboardAction === 'cut') {
                if ($driver->move($path, $newPath)) {
                    $removed[] = $hash;
                    $info = $driver->info($newPath);
                    if ($info) $added[] = $info;
                }
            } else {
                if ($driver->copy($path, $newPath)) {
                    $info = $driver->info($newPath);
                    if ($info) $added[] = $info;
                }
            }
        }

        if ($this->clipboardAction === 'cut') {
            $this->clipboard = [];
            $this->clipboardAction = '';
        }

        $this->dispatch('finder:items-changed');
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $driver = $this->finderManager->driver($this->disk);
        $paths = array_map(fn($hash) => $driver->decodeHash($hash), $this->selected);

        $driver->delete($paths);
        $this->selected = [];
        $this->dispatch('finder:items-changed');
    }

    public function search(): void
    {
        if (!empty($this->searchQuery)) {
            $this->isSearching = true;
        } else {
            $this->isSearching = false;
        }
    }

    public function clearSearch(): void
    {
        $this->searchQuery = '';
        $this->isSearching = false;
    }

    public function openModal(string $modal): void
    {
        $this->activeModal = $modal;
        $this->dispatch('finder:open-modal', modal: $modal);
    }

    public function closeModal(): void
    {
        $this->activeModal = '';
    }

    public function toggleHidden(): void
    {
        $this->showHidden = !$this->showHidden;
    }

    public function getSelectedItemsProperty(): array
    {
        if (empty($this->selected)) {
            return [];
        }

        $driver = $this->finderManager->driver($this->disk);
        $items = [];

        foreach ($this->selected as $hash) {
            $path = $driver->decodeHash($hash);
            $info = $driver->info($path);
            if ($info) {
                $items[] = $info;
            }
        }

        return $items;
    }

    public function refreshItems(): void
    {
        // Re-render will be triggered automatically
        $this->dispatch('finder:refreshed');
    }

    public function render()
    {
        return view('finder::livewire.file-manager');
    }
}
