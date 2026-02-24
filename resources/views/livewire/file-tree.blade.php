<div class="finder-tree" x-on:finder:items-changed.window="$wire.refreshTree()">

    <div class="finder-tree-section">
        <div class="finder-tree-header">
            <span>Folders</span>
        </div>

        {{-- Root --}}
        <button
            wire:click="select('/')"
            class="finder-tree-item {{ $currentPath === '/' ? 'finder-tree-item--active' : '' }}"
        >
            <svg class="finder-tree-icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            </svg>
            <span>Root</span>
        </button>

        {{-- Recursive Tree --}}
        @foreach($tree as $node)
            @include('finder::livewire.partials.tree-node', ['node' => $node, 'depth' => 0])
        @endforeach
    </div>

    {{-- Places / Favorites --}}
    <div class="finder-tree-section">
        <div class="finder-tree-header">
            <span>Places</span>
        </div>

        <button
            wire:click="select('/')"
            class="finder-tree-item"
            title="Root directory"
        >
            <svg class="finder-tree-icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 3a1 1 0 01.707.293l6 6a1 1 0 010 1.414l-6 6A1 1 0 019 16V4a1 1 0 011-1z"/>
            </svg>
            <span>Home</span>
        </button>
    </div>

</div>
