<div>
    @if($open)
    <div class="finder-modal-overlay" x-data x-on:keydown.escape.window="$wire.close()">
        <div class="finder-modal finder-modal-sm">
            <div class="finder-modal-header">
                <h3 class="finder-modal-title">
                    {{ $mode === 'folder' ? __('finder::finder.new_folder_modal') : __('finder::finder.new_file_modal') }}
                </h3>
                <button wire:click="close" class="finder-modal-close" title="{{ __('finder::finder.close') }}">&times;</button>
            </div>

            <div class="finder-modal-body">
                <div class="finder-form-group">
                    <label class="finder-label">
                        {{ $mode === 'folder' ? __('finder::finder.folder_name_label') : __('finder::finder.file_name_label') }}
                    </label>
                    <input
                        type="text"
                        wire:model="name"
                        wire:keydown.enter="create"
                        class="finder-input {{ $error ? 'finder-input--error' : '' }}"
                        placeholder="{{ $mode === 'folder' ? __('finder::finder.folder_name_ph') : __('finder::finder.file_name_ph') }}"
                        autofocus
                        x-init="$el.focus(); $el.select()"
                    >
                    @if($error)
                        <p class="finder-error-msg">{{ $error }}</p>
                    @endif
                </div>
            </div>

            <div class="finder-modal-footer">
                <button wire:click="close" class="finder-btn">{{ __('finder::finder.cancel') }}</button>
                <button wire:click="create" class="finder-btn finder-btn-primary">{{ __('finder::finder.create') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
