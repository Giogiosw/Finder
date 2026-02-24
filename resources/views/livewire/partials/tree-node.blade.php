<div class="finder-tree-node" style="padding-left: {{ $depth * 16 }}px">
    <div class="finder-tree-node-row">
        <button
            wire:click="toggle('{{ $node['path'] }}')"
            class="finder-tree-toggle {{ empty($node['children']) && !$node['expanded'] ? 'finder-tree-toggle--empty' : '' }}"
            title="{{ $node['expanded'] ? 'Collapse' : 'Expand' }}"
        >
            @if($node['expanded'])
                <svg viewBox="0 0 20 20" fill="currentColor" class="finder-icon-xs">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 000 1.06l4 4a.75.75 0 001.06 0l4-4a.75.75 0 00-1.06-1.06L10 11.44 6.28 7.72a.75.75 0 00-1.06.5z" clip-rule="evenodd" />
                </svg>
            @else
                <svg viewBox="0 0 20 20" fill="currentColor" class="finder-icon-xs">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
            @endif
        </button>

        <button
            wire:click="select('{{ $node['path'] }}')"
            class="finder-tree-item {{ $currentPath === $node['path'] ? 'finder-tree-item--active' : '' }}"
        >
            <svg class="finder-tree-icon finder-tree-icon--folder" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            </svg>
            <span class="finder-tree-name" title="{{ $node['name'] }}">{{ $node['name'] }}</span>
        </button>
    </div>

    @if($node['expanded'] && !empty($node['children']))
        <div class="finder-tree-children">
            @foreach($node['children'] as $child)
                @include('finder::livewire.partials.tree-node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
