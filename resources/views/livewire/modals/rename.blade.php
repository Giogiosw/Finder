<div>
    @if($open)
    <div class="finder-modal-overlay" x-data x-on:keydown.escape.window="$wire.close()">
        <div class="finder-modal finder-modal-sm">
            <div class="finder-modal-header">
                <h3 class="finder-modal-title">{{ __('finder::finder.rename_modal_title') }}</h3>
                <button wire:click="close" class="finder-modal-close" title="{{ __('finder::finder.close') }}">&times;</button>
            </div>

            <div class="finder-modal-body">
                <div class="finder-form-group">
                    <label class="finder-label">{{ __('finder::finder.new_name') }}</label>
                    <input
                        type="text"
                        wire:model="name"
                        wire:keydown.enter="rename"
                        class="finder-input {{ $error ? 'finder-input--error' : '' }}"
                        autofocus
                        x-init="
                            $el.focus();
                            const ext = $el.value.lastIndexOf('.');
                            if (ext > 0) {
                                $el.setSelectionRange(0, ext);
                            } else {
                                $el.select();
                            }
                        "
                    >
                    @if($error)
                        <p class="finder-error-msg">{{ $error }}</p>
                    @endif
                </div>
            </div>

            <div class="finder-modal-footer">
                <button wire:click="close" class="finder-btn">{{ __('finder::finder.cancel') }}</button>
                <button wire:click="rename" class="finder-btn finder-btn-primary">{{ __('finder::finder.rename') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
