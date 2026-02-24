<div>
    @if($open)
    <div class="finder-modal-overlay" x-data x-on:keydown.escape.window="$wire.close()">
        <div class="finder-modal finder-modal-sm">
            <div class="finder-modal-header">
                <h3 class="finder-modal-title">
                    {{ $mode === 'folder' ? 'New Folder' : 'New File' }}
                </h3>
                <button wire:click="close" class="finder-modal-close" title="Close">&times;</button>
            </div>

            <div class="finder-modal-body">
                <div class="finder-form-group">
                    <label class="finder-label">
                        {{ $mode === 'folder' ? 'Folder Name' : 'File Name' }}
                    </label>
                    <input
                        type="text"
                        wire:model="name"
                        wire:keydown.enter="create"
                        class="finder-input {{ $error ? 'finder-input--error' : '' }}"
                        placeholder="{{ $mode === 'folder' ? 'New Folder' : 'new-file.txt' }}"
                        autofocus
                        x-init="$el.focus(); $el.select()"
                    >
                    @if($error)
                        <p class="finder-error-msg">{{ $error }}</p>
                    @endif
                </div>
            </div>

            <div class="finder-modal-footer">
                <button wire:click="close" class="finder-btn">Cancel</button>
                <button wire:click="create" class="finder-btn finder-btn-primary">Create</button>
            </div>
        </div>
    </div>
    @endif
</div>
