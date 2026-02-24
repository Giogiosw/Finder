<div class="finder-toolbar">

    {{-- Upload --}}
    @if($canUpload)
    <button
        wire:click="openUpload"
        class="finder-toolbar-btn"
        title="Upload files"
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path fill-rule="evenodd" d="M13.75 6.75l-3.75-3.75-3.75 3.75M10 3v10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M3.75 13.75v1.5a2 2 0 002 2h8.5a2 2 0 002-2v-1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <span class="finder-toolbar-label">Upload</span>
    </button>
    @endif

    {{-- New Folder --}}
    @if($canWrite)
    <button
        wire:click="openCreateFolder"
        class="finder-toolbar-btn"
        title="New folder"
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            <path stroke="white" stroke-width="1.5" stroke-linecap="round" d="M10 10v4M8 12h4"/>
        </svg>
        <span class="finder-toolbar-label">New Folder</span>
    </button>

    <button
        wire:click="openCreateFile"
        class="finder-toolbar-btn"
        title="New file"
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
            <path stroke="white" stroke-width="1.5" stroke-linecap="round" d="M10 9v4M8 11h4"/>
        </svg>
        <span class="finder-toolbar-label">New File</span>
    </button>
    @endif

    <div class="finder-toolbar-sep"></div>

    {{-- Copy / Cut / Paste --}}
    <button
        wire:click="triggerCopy"
        class="finder-toolbar-btn {{ empty($selected) ? 'finder-toolbar-btn--disabled' : '' }}"
        title="Copy"
        @disabled(empty($selected))
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"/>
            <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/>
        </svg>
        <span class="finder-toolbar-label">Copy</span>
    </button>

    <button
        wire:click="triggerCut"
        class="finder-toolbar-btn {{ empty($selected) ? 'finder-toolbar-btn--disabled' : '' }}"
        title="Cut"
        @disabled(empty($selected))
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path fill-rule="evenodd" d="M5.5 2a3.5 3.5 0 101.665 6.58L8.585 10l-1.42 1.42a3.5 3.5 0 101.414 1.414l1.42-1.42 1.42 1.42a3.5 3.5 0 101.414-1.414L11.415 10l1.42-1.42A3.5 3.5 0 1011.17 7.17L10 8.586 8.83 7.42a3.5 3.5 0 00-3.33-5.42z" clip-rule="evenodd" />
        </svg>
        <span class="finder-toolbar-label">Cut</span>
    </button>

    <button
        wire:click="triggerPaste"
        class="finder-toolbar-btn {{ $clipboardCount === 0 ? 'finder-toolbar-btn--disabled' : '' }}"
        title="Paste"
        @disabled($clipboardCount === 0)
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
        </svg>
        <span class="finder-toolbar-label">Paste</span>
    </button>

    <div class="finder-toolbar-sep"></div>

    {{-- Rename --}}
    @if($canWrite)
    <button
        wire:click="openRename"
        class="finder-toolbar-btn {{ count($selected) !== 1 ? 'finder-toolbar-btn--disabled' : '' }}"
        title="Rename"
        @disabled(count($selected) !== 1)
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
        </svg>
        <span class="finder-toolbar-label">Rename</span>
    </button>
    @endif

    {{-- Delete --}}
    @if($canDelete)
    <button
        wire:click="triggerDelete"
        class="finder-toolbar-btn finder-toolbar-btn--danger {{ empty($selected) ? 'finder-toolbar-btn--disabled' : '' }}"
        title="Delete"
        @disabled(empty($selected))
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <span class="finder-toolbar-label">Delete</span>
    </button>
    @endif

    <div class="finder-toolbar-sep"></div>

    {{-- Archive --}}
    @if($canArchive)
    <button
        wire:click="openArchive"
        class="finder-toolbar-btn {{ empty($selected) ? 'finder-toolbar-btn--disabled' : '' }}"
        title="Create archive"
        @disabled(empty($selected))
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/>
            <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
        </svg>
        <span class="finder-toolbar-label">Archive</span>
    </button>
    @endif

    {{-- Edit Text --}}
    <button
        wire:click="openEditText"
        class="finder-toolbar-btn {{ count($selected) !== 1 ? 'finder-toolbar-btn--disabled' : '' }}"
        title="Edit text file"
        @disabled(count($selected) !== 1)
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
        </svg>
        <span class="finder-toolbar-label">Edit</span>
    </button>

    {{-- Properties --}}
    <button
        wire:click="openProperties"
        class="finder-toolbar-btn {{ empty($selected) ? 'finder-toolbar-btn--disabled' : '' }}"
        title="Properties"
        @disabled(empty($selected))
    >
        <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <span class="finder-toolbar-label">Info</span>
    </button>

    {{-- Spacer --}}
    <div class="finder-toolbar-spacer"></div>

    {{-- View mode --}}
    <div class="finder-toolbar-viewmode">
        <button
            wire:click="setViewMode('grid')"
            class="finder-toolbar-btn finder-toolbar-btn--icon {{ $viewMode === 'grid' ? 'finder-toolbar-btn--active' : '' }}"
            title="Grid view"
        >
            <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
        </button>
        <button
            wire:click="setViewMode('list')"
            class="finder-toolbar-btn finder-toolbar-btn--icon {{ $viewMode === 'list' ? 'finder-toolbar-btn--active' : '' }}"
            title="List view"
        >
            <svg viewBox="0 0 20 20" fill="currentColor" class="finder-toolbar-icon">
                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

</div>
