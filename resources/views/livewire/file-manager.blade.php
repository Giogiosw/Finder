<div
    class="finder-root"
    x-data="{
        selected: @entangle('selected'),
        viewMode: @entangle('viewMode'),
        showDeleteConfirm: false,
        showClipboardBanner: false,
        clipboardCount: 0,
        clipboardAction: '',
        activeModal: '',
        clipboardCopy: '{{ __('finder::finder.clipboard_copy') }}',
        clipboardCut: '{{ __('finder::finder.clipboard_cut') }}',
    }"
    x-on:finder:open-modal.window="activeModal = $event.detail.modal"
    x-on:finder:selection-changed.window="selected = $event.detail.selected"
    x-on:finder:confirm-delete.window="showDeleteConfirm = true"
    x-on:finder:clipboard-updated.window="
        clipboardCount = $event.detail.count;
        clipboardAction = $event.detail.action;
        showClipboardBanner = true;
    "
    x-on:finder:copy-selected.window="$wire.copySelected()"
    x-on:finder:cut-selected.window="$wire.cutSelected()"
    x-on:finder:paste.window="$wire.paste()"
    x-on:finder:items-changed.window="$wire.$refresh()"
    x-on:keydown.escape.window="activeModal = ''"
>

    {{-- Top Bar --}}
    <div class="finder-topbar">
        <div class="finder-logo">
            <svg class="finder-logo-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 7a2 2 0 012-2h4.586a1 1 0 01.707.293L12 7h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            </svg>
            <span>Finder</span>
        </div>

        {{-- Search --}}
        <div class="finder-search">
            <form wire:submit="search" class="finder-search-form">
                <input
                    type="text"
                    wire:model="searchQuery"
                    placeholder="{{ __('finder::finder.search_placeholder') }}"
                    class="finder-search-input"
                >
                @if($isSearching)
                    <button type="button" wire:click="clearSearch" class="finder-search-clear" title="{{ __('finder::finder.clear_search') }}">
                        ✕
                    </button>
                @else
                    <button type="submit" class="finder-search-btn" title="{{ __('finder::finder.search') }}">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-icon-sm">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif
            </form>
        </div>

        {{-- Disk switcher --}}
        @if(count($this->availableDisks) > 1)
        <div class="finder-disk-switcher">
            <select wire:change="switchDisk($event.target.value)" class="finder-disk-select">
                @foreach($this->availableDisks as $d)
                    <option value="{{ $d }}" @selected($d === $disk)>{{ ucfirst($d) }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- Main Layout --}}
    <div class="finder-layout">

        {{-- Sidebar --}}
        <div class="finder-sidebar">
            @livewire('finder::file-tree', [
                'disk' => $disk,
                'currentPath' => $currentPath,
            ], key('tree-'.$disk))
        </div>

        {{-- Content Area --}}
        <div class="finder-content">

            {{-- Toolbar --}}
            <div class="finder-toolbar-wrapper">
                @livewire('finder::toolbar', [
                    'selected' => $selected,
                    'viewMode' => $viewMode,
                ], key('toolbar'))
            </div>

            {{-- Breadcrumbs --}}
            <nav class="finder-breadcrumb" aria-label="breadcrumb">
                @foreach($this->breadcrumbs as $crumb)
                    @if(!$loop->last)
                        <button
                            wire:click="navigateTo('{{ $crumb['path'] }}')"
                            class="finder-breadcrumb-item finder-breadcrumb-link"
                        >{{ $crumb['name'] }}</button>
                        <span class="finder-breadcrumb-sep">/</span>
                    @else
                        <span class="finder-breadcrumb-item finder-breadcrumb-current">{{ $crumb['name'] }}</span>
                    @endif
                @endforeach

                @if($isSearching)
                    <span class="finder-search-badge">{{ __('finder::finder.search_badge', ['query' => $searchQuery]) }}</span>
                @endif
            </nav>

            {{-- Clipboard Banner --}}
            <div
                x-show="showClipboardBanner"
                x-transition
                class="finder-clipboard-banner"
            >
                <span x-text="(clipboardAction === 'copy' ? clipboardCopy : clipboardCut).replace(':count', clipboardCount)"></span>
                <button wire:click="paste" class="finder-btn finder-btn-sm finder-btn-primary">{{ __('finder::finder.paste_here') }}</button>
                <button x-on:click="showClipboardBanner = false; clipboardCount = 0" class="finder-btn finder-btn-sm">{{ __('finder::finder.cancel') }}</button>
            </div>

            {{-- File List --}}
            <div class="finder-filelist-wrapper">
                @livewire('finder::file-list', [
                    'disk' => $disk,
                    'currentPath' => $currentPath,
                    'viewMode' => $viewMode,
                ], key('list-'.$disk.'-'.$currentPath))
            </div>

        </div>
    </div>

    {{-- Status Bar --}}
    <div class="finder-statusbar">
        <span class="finder-statusbar-info">
            {{ count($selected) > 0
                ? __('finder::finder.n_items_selected', ['count' => count($selected)])
                : __('finder::finder.n_items', ['count' => count($this->items)]) }}
        </span>
        @if($isSearching)
            <span class="finder-statusbar-search">{{ __('finder::finder.search_results', ['query' => $searchQuery]) }}</span>
        @endif
        <div class="finder-statusbar-about">
            <span>{{ __('finder::finder.about_version', ['version' => \Giogiosw\Finder\FinderServiceProvider::VERSION]) }}</span>
            <span class="finder-statusbar-dot">·</span>
            <a href="https://devgd.it" target="_blank" rel="noopener noreferrer" class="finder-statusbar-link">devgd.it</a>
            <span class="finder-statusbar-dot">·</span>
            <span>Giovanni D'Ippolito</span>
        </div>
    </div>

    {{-- Modals --}}
    @livewire('finder::upload-modal', ['disk' => $disk, 'targetPath' => $currentPath], key('modal-upload'))
    @livewire('finder::create-folder-modal', ['disk' => $disk, 'targetPath' => $currentPath], key('modal-create'))
    @livewire('finder::rename-modal', ['disk' => $disk], key('modal-rename'))
    @livewire('finder::properties-modal', ['disk' => $disk], key('modal-properties'))
    @livewire('finder::archive-modal', ['disk' => $disk, 'targetPath' => $currentPath], key('modal-archive'))
    @livewire('finder::edit-text-modal', ['disk' => $disk], key('modal-edit-text'))

    {{-- Delete Confirmation --}}
    <div
        x-show="showDeleteConfirm"
        x-transition
        class="finder-modal-overlay"
        x-cloak
    >
        <div class="finder-modal finder-modal-sm">
            <div class="finder-modal-header">
                <h3 class="finder-modal-title">{{ __('finder::finder.confirm_delete_title') }}</h3>
            </div>
            <div class="finder-modal-body">
                <p>{{ __('finder::finder.confirm_delete_body', ['count' => count($selected)]) }}</p>
            </div>
            <div class="finder-modal-footer">
                <button x-on:click="showDeleteConfirm = false" class="finder-btn">{{ __('finder::finder.cancel') }}</button>
                <button
                    x-on:click="showDeleteConfirm = false; $wire.deleteSelected()"
                    class="finder-btn finder-btn-danger"
                >{{ __('finder::finder.delete') }}</button>
            </div>
        </div>
    </div>

    {{-- Event Listeners --}}
    <div
        x-on:finder:navigate.window="$wire.navigateTo($event.detail.path)"
        x-on:finder:view-mode-changed.window="$wire.setViewMode($event.detail.mode)"
    ></div>

</div>
