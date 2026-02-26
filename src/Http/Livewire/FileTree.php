<?php

namespace Giogiosw\Finder\Http\Livewire;

use Giogiosw\Finder\FinderManager;
use Livewire\Component;

class FileTree extends Component
{
    public string $disk = '';
    public string $currentPath = '/';
    public array $expanded = [];
    public array $tree = [];

    protected FinderManager $finderManager;

    public function boot(FinderManager $finderManager): void
    {
        $this->finderManager = $finderManager;
    }

    public function mount(string $disk = '', string $currentPath = '/'): void
    {
        $this->disk = $disk ?: config('finder.default_disk', 'local');
        $this->currentPath = $currentPath;
        $this->expanded = ['/'];
        $this->loadTree();
    }

    protected function loadTree(): void
    {
        $this->tree = $this->buildTree('');
    }

    protected function buildTree(string $path, int $depth = 0): array
    {
        if ($depth > 10) {
            return [];
        }

        $driver = $this->finderManager->driver($this->disk);
        $items = $driver->ls($path === '' ? '/' : $path);

        $tree = [];
        foreach ($items as $item) {
            if (!$item->isDir) {
                continue;
            }

            $normalizedPath = '/' . ltrim($item->path, '/');
            $isExpanded = in_array($normalizedPath, $this->expanded);

            $node = [
                'hash'     => $item->hash,
                'name'     => $item->name,
                'path'     => $normalizedPath,
                'expanded' => $isExpanded,
                'children' => [],
            ];

            if ($isExpanded) {
                $node['children'] = $this->buildTree($item->path, $depth + 1);
            }

            $tree[] = $node;
        }

        return $tree;
    }

    public function toggle(string $path): void
    {
        if (in_array($path, $this->expanded)) {
            $this->expanded = array_values(array_filter($this->expanded, fn($p) => $p !== $path));
        } else {
            $this->expanded[] = $path;
        }

        $this->loadTree();
    }

    public function select(string $path): void
    {
        $this->currentPath = $path;
        $this->dispatch('finder:navigate', path: $path);
    }

    public function refreshTree(): void
    {
        $this->loadTree();
    }

    public function render()
    {
        return view('finder::livewire.file-tree');
    }
}
