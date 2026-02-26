<div
    class="finder-filelist"
    x-data="{
        lastSelected: null,
        dragOver: false,
    }"
    x-on:dragover.prevent="dragOver = true"
    x-on:dragleave="dragOver = false"
    x-on:drop.prevent="dragOver = false"
    x-on:finder:navigate.window="$wire.currentPath = $event.detail.path"
    x-on:finder:items-changed.window="$wire.$refresh()"
    x-on:click.self="$wire.clearSelection()"
    :class="{ 'finder-filelist--drag-over': dragOver }"
>

    {{-- Grid View --}}
    @if($viewMode === 'grid')
    <div class="finder-grid">
        @forelse($this->items as $item)
        <div
            class="finder-grid-item {{ in_array($item->hash, $selected) ? 'finder-grid-item--selected' : '' }}"
            wire:key="grid-{{ $item->hash }}"
            x-on:click="$wire.selectItem('{{ $item->hash }}', $event.ctrlKey || $event.metaKey)"
            x-on:dblclick="$wire.openItem('{{ $item->hash }}')"
            x-on:contextmenu.prevent="
                $wire.selectItem('{{ $item->hash }}', false);
                $dispatch('finder:context-menu', { hash: '{{ $item->hash }}', x: $event.clientX, y: $event.clientY });
            "
            draggable="true"
            x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $item->hash }}')"
            title="{{ $item->name }}"
        >
            <div class="finder-grid-thumb">
                @if($item->isImage() && isset($this->thumbUrls[$item->hash]))
                    <img
                        src="{{ $this->thumbUrls[$item->hash] }}"
                        alt="{{ $item->name }}"
                        class="finder-grid-img"
                        loading="lazy"
                    >
                @elseif($item->isDir)
                    <svg class="finder-grid-icon finder-grid-icon--folder" viewBox="0 0 48 48" fill="none">
                        <path d="M6 10C6 8.895 6.895 8 8 8h10l4 4h18c1.105 0 2 .895 2 2v24c0 1.105-.895 2-2 2H8c-1.105 0-2-.895-2-2V10z" fill="#FFB900"/>
                        <path d="M6 14h36v24c0 1.105-.895 2-2 2H8c-1.105 0-2-.895-2-2V14z" fill="#FFD352"/>
                    </svg>
                @else
                    <div class="finder-grid-filetype finder-filetype-{{ $item->extension() ?: 'file' }}">
                        @include('finder::livewire.partials.file-icon', ['item' => $item])
                    </div>
                @endif
            </div>
            <div class="finder-grid-name" title="{{ $item->name }}">{{ $item->name }}</div>
        </div>
        @empty
        <div class="finder-empty-state">
            <svg class="finder-empty-icon" viewBox="0 0 48 48" fill="none">
                <rect x="4" y="8" width="40" height="32" rx="2" stroke="currentColor" stroke-width="2"/>
                <path d="M4 14h40" stroke="currentColor" stroke-width="2"/>
            </svg>
            <p>{{ __('finder::finder.empty_folder') }}</p>
        </div>
        @endforelse
    </div>

    {{-- List View --}}
    @else
    <table class="finder-list">
        <thead>
            <tr class="finder-list-head">
                <th class="finder-list-th finder-list-th--check">
                    <input type="checkbox" x-on:change="$event.target.checked ? $wire.selectAll() : $wire.clearSelection()">
                </th>
                <th class="finder-list-th finder-list-th--name">
                    <button wire:click="setSortBy('name')" class="finder-sort-btn">
                        {{ __('finder::finder.col_name') }}
                        @if($sortBy === 'name')
                            <span class="finder-sort-arrow">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
                <th class="finder-list-th finder-list-th--size">
                    <button wire:click="setSortBy('size')" class="finder-sort-btn">
                        {{ __('finder::finder.col_size') }}
                        @if($sortBy === 'size')
                            <span class="finder-sort-arrow">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
                <th class="finder-list-th finder-list-th--type">
                    <button wire:click="setSortBy('type')" class="finder-sort-btn">
                        {{ __('finder::finder.col_type') }}
                        @if($sortBy === 'type')
                            <span class="finder-sort-arrow">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
                <th class="finder-list-th finder-list-th--modified">
                    <button wire:click="setSortBy('modified')" class="finder-sort-btn">
                        {{ __('finder::finder.col_modified') }}
                        @if($sortBy === 'modified')
                            <span class="finder-sort-arrow">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($this->items as $item)
            <tr
                class="finder-list-row {{ in_array($item->hash, $selected) ? 'finder-list-row--selected' : '' }}"
                wire:key="list-{{ $item->hash }}"
                x-on:click="$wire.selectItem('{{ $item->hash }}', $event.ctrlKey || $event.metaKey)"
                x-on:dblclick="$wire.openItem('{{ $item->hash }}')"
                x-on:contextmenu.prevent="
                    $wire.selectItem('{{ $item->hash }}', false);
                    $dispatch('finder:context-menu', { hash: '{{ $item->hash }}', x: $event.clientX, y: $event.clientY });
                "
                draggable="true"
                x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $item->hash }}')"
            >
                <td class="finder-list-td finder-list-td--check">
                    <input
                        type="checkbox"
                        :checked="$wire.selected.includes('{{ $item->hash }}')"
                        x-on:click.stop="$wire.selectItem('{{ $item->hash }}', true)"
                    >
                </td>
                <td class="finder-list-td finder-list-td--name">
                    <div class="finder-list-name-cell">
                        @if($item->isDir)
                            <svg class="finder-list-icon finder-list-icon--folder" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                            </svg>
                        @elseif($item->isImage() && isset($this->thumbUrls[$item->hash]))
                            <img src="{{ $this->thumbUrls[$item->hash] }}" alt="" class="finder-list-thumb">
                        @else
                            <svg class="finder-list-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                        @endif
                        <span class="finder-list-filename">{{ $item->name }}</span>
                        @if($item->locked)
                            <svg class="finder-list-lock" viewBox="0 0 20 20" fill="currentColor" title="Locked">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>
                </td>
                <td class="finder-list-td finder-list-td--size">
                    {{ $item->sizeForHumans() }}
                </td>
                <td class="finder-list-td finder-list-td--type">
                    {{ $item->isDir ? __('finder::finder.folder') : strtoupper($item->extension() ?: $item->mime) }}
                </td>
                <td class="finder-list-td finder-list-td--modified">
                    <span title="{{ date('Y-m-d H:i:s', $item->modified) }}">{{ $item->modifiedForHumans() }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="finder-list-empty">
                    <div class="finder-empty-state">
                        <p>{{ __('finder::finder.empty_folder') }}</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    {{-- Context Menu --}}
    <div
        x-data="{ show: false, x: 0, y: 0, hash: '' }"
        x-on:finder:context-menu.window="show = true; x = $event.detail.x; y = $event.detail.y; hash = $event.detail.hash"
        x-on:click.outside="show = false"
        x-on:keydown.escape.window="show = false"
        x-show="show"
        x-cloak
        :style="{ position: 'fixed', left: x + 'px', top: y + 'px', zIndex: 9999 }"
        class="finder-context-menu"
        x-transition
    >
        <button class="finder-context-item" x-on:click="show = false; $wire.openItem(hash)">{{ __('finder::finder.ctx_open') }}</button>
        <button class="finder-context-item" x-on:click="show = false; $dispatch('finder:open-modal', { modal: 'rename' })">{{ __('finder::finder.ctx_rename') }}</button>
        <button class="finder-context-item" x-on:click="show = false; $dispatch('finder:copy-selected')">{{ __('finder::finder.ctx_copy') }}</button>
        <button class="finder-context-item" x-on:click="show = false; $dispatch('finder:cut-selected')">{{ __('finder::finder.ctx_cut') }}</button>
        <div class="finder-context-sep"></div>
        <button class="finder-context-item" x-on:click="show = false; $dispatch('finder:open-modal', { modal: 'archive' })">{{ __('finder::finder.ctx_archive') }}</button>
        <button class="finder-context-item" x-on:click="show = false; $dispatch('finder:open-modal', { modal: 'edit-text' })">{{ __('finder::finder.ctx_edit') }}</button>
        <div class="finder-context-sep"></div>
        <button class="finder-context-item" x-on:click="show = false; $dispatch('finder:open-modal', { modal: 'properties' })">{{ __('finder::finder.ctx_properties') }}</button>
        <div class="finder-context-sep"></div>
        <button class="finder-context-item finder-context-item--danger" x-on:click="show = false; $dispatch('finder:confirm-delete')">{{ __('finder::finder.ctx_delete') }}</button>
    </div>

</div>
