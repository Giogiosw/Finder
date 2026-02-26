<div>
    @if($open)
    <div class="finder-modal-overlay" x-data x-on:keydown.escape.window="$wire.close()">
        <div class="finder-modal finder-modal-md">
            <div class="finder-modal-header">
                <h3 class="finder-modal-title">
                    {{ $mode === 'create' ? __('finder::finder.create_archive_title') : __('finder::finder.extract_archive_title') }}
                </h3>
                <button wire:click="close" class="finder-modal-close" title="{{ __('finder::finder.close') }}">&times;</button>
            </div>

            <div class="finder-modal-body">
                @if($error)
                    <div class="finder-alert finder-alert-danger">{{ $error }}</div>
                @endif

                @if($mode === 'create')
                    <div class="finder-form-group">
                        <label class="finder-label">{{ __('finder::finder.archive_name_label') }}</label>
                        <input
                            type="text"
                            wire:model="archiveName"
                            class="finder-input"
                            placeholder="archive.zip"
                        >
                    </div>

                    <div class="finder-form-group">
                        <label class="finder-label">{{ __('finder::finder.format_label') }}</label>
                        <select wire:model.live="archiveType" class="finder-select">
                            <option value="zip">ZIP</option>
                            <option value="tar.gz">TAR.GZ</option>
                        </select>
                    </div>

                    <div class="finder-form-group">
                        <label class="finder-label">{{ __('finder::finder.items_to_archive') }}</label>
                        <p class="finder-hint">{{ __('finder::finder.n_items_selected_arch', ['count' => count($targetHashes)]) }}</p>
                    </div>

                @else
                    <p>{{ __('finder::finder.extract_confirm') }}</p>
                    <p class="finder-hint">{{ __('finder::finder.extract_to') }} <code>{{ $targetPath }}</code></p>
                @endif
            </div>

            <div class="finder-modal-footer">
                <button wire:click="close" class="finder-btn" @disabled($isProcessing)>{{ __('finder::finder.cancel') }}</button>
                <button
                    wire:click="process"
                    class="finder-btn finder-btn-primary"
                    @disabled($isProcessing || empty($targetHashes))
                >
                    @if($isProcessing)
                        <span class="finder-spinner finder-spinner-sm"></span>
                    @endif
                    {{ $mode === 'create' ? __('finder::finder.create_archive_btn') : __('finder::finder.extract_btn') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
