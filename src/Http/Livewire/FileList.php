<?php

namespace Giogiosw\Finder\Http\Livewire;

use Giogiosw\Finder\FinderManager;
use Giogiosw\Finder\Support\FileInfo;
use Giogiosw\Finder\Support\ImageHandler;
use Livewire\Component;

class FileList extends Component
{
    public string $disk = '';
    public string $currentPath = '/';
    public string $viewMode = 'grid';
    public array $selected = [];
    public string $sortBy = 'name';
    public string $sortDir = 'asc';
    public bool $showHidden = false;
    public string $searchQuery = '';
    public bool $isSearching = false;

    protected FinderManager $finderManager;

    public function boot(FinderManager $finderManager): void
    {
        $this->finderManager = $finderManager;
    }

    public function mount(
        string $disk = '',
        string $currentPath = '/',
        string $viewMode = 'grid'
    ): void {
        $this->disk = $disk ?: config('finder.default_disk', 'local');
        $this->currentPath = $currentPath;
        $this->viewMode = $viewMode;
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

        // Filter hidden
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

    public function getThumbUrlsProperty(): array
    {
        $urls = [];
        $imageHandler = new ImageHandler(config('finder'));

        foreach ($this->items as $item) {
            if ($item->isImage() && config('finder.thumbnails.enabled', true)) {
                $thumbPath = $imageHandler->createThumbnail($this->disk, $item->path);
                if ($thumbPath) {
                    $urls[$item->hash] = route('finder.thumbnail', [
                        'disk'   => $this->disk,
                        'target' => $item->hash,
                    ]);
                }
            }
        }

        return $urls;
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
            if ($this->selected === [$hash]) {
                // Deselect if clicking the same item
                // (keep selected for double-click to open)
            } else {
                $this->selected = [$hash];
            }
        }

        $this->dispatch('finder:selection-changed', selected: $this->selected);
    }

    public function openItem(string $hash): void
    {
        $driver = $this->finderManager->driver($this->disk);
        $path = $driver->decodeHash($hash);
        $info = $driver->info($path);

        if ($info && $info->isDir) {
            $this->currentPath = $path;
            $this->selected = [];
            $this->dispatch('finder:navigate', path: $path);
        } else {
            $this->dispatch('finder:open-file', hash: $hash);
        }
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

    public function selectAll(): void
    {
        $this->selected = array_map(fn($item) => $item->hash, $this->items);
        $this->dispatch('finder:selection-changed', selected: $this->selected);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->dispatch('finder:selection-changed', selected: []);
    }

    public function render()
    {
        return view('finder::livewire.file-list');
    }
}
