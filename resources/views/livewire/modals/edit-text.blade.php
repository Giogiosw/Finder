<div>
    @if($open)
    <div
        class="finder-modal-overlay finder-modal-overlay--full"
        x-data="{
            confirmClose: false,
        }"
        x-on:finder:confirm-close-editor.window="confirmClose = true"
        x-on:keydown.escape.window="$wire.close()"
    >
        <div class="finder-modal finder-modal-full">
            <div class="finder-modal-header">
                <div class="finder-editor-header">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="finder-icon-sm">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="finder-modal-title">{{ $filename }}</h3>
                    @if($isDirty)
                        <span class="finder-editor-dirty" title="{{ __('finder::finder.unsaved_indicator') }}">●</span>
                    @endif
                    <span class="finder-editor-lang">{{ $this->language }}</span>
                </div>
                <div class="finder-editor-actions">
                    <button
                        wire:click="save"
                        class="finder-btn finder-btn-sm finder-btn-primary"
                        @disabled($isSaving || !$isDirty)
                        title="{{ __('finder::finder.save_title') }}"
                    >
                        @if($isSaving)
                            <span class="finder-spinner finder-spinner-sm"></span>
                        @else
                            {{ __('finder::finder.save') }}
                        @endif
                    </button>
                    <button wire:click="close" class="finder-modal-close" title="{{ __('finder::finder.close') }}">&times;</button>
                </div>
            </div>

            <div class="finder-modal-body finder-editor-body">
                @if($error)
                    <div class="finder-alert finder-alert-danger">{{ $error }}</div>
                @else
                    <textarea
                        wire:model="content"
                        class="finder-editor-textarea"
                        spellcheck="false"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="off"
                        x-data
                        x-on:keydown.ctrl.s.prevent="$wire.save()"
                        x-on:keydown.meta.s.prevent="$wire.save()"
                    ></textarea>
                @endif
            </div>

            <div class="finder-modal-footer finder-editor-footer">
                <span class="finder-editor-status">
                    @if($isDirty)
                        {{ __('finder::finder.unsaved_indicator') }}
                    @else
                        {{ __('finder::finder.saved') }}
                    @endif
                </span>
                <div class="finder-editor-footer-actions">
                    <button wire:click="close" class="finder-btn finder-btn-sm">{{ __('finder::finder.cancel') }}</button>
                    <button wire:click="saveAndClose" class="finder-btn finder-btn-sm finder-btn-primary" @disabled($isSaving)>
                        {{ __('finder::finder.save_and_close') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Unsaved changes confirmation --}}
        <div
            x-show="confirmClose"
            x-transition
            class="finder-modal-overlay"
            style="z-index: 10001"
            x-cloak
        >
            <div class="finder-modal finder-modal-sm">
                <div class="finder-modal-header">
                    <h3 class="finder-modal-title">{{ __('finder::finder.unsaved_title') }}</h3>
                </div>
                <div class="finder-modal-body">
                    <p>{{ __('finder::finder.unsaved_body') }}</p>
                </div>
                <div class="finder-modal-footer">
                    <button x-on:click="confirmClose = false" class="finder-btn">{{ __('finder::finder.continue_editing') }}</button>
                    <button x-on:click="confirmClose = false; $wire.saveAndClose()" class="finder-btn finder-btn-primary">{{ __('finder::finder.save_and_close') }}</button>
                    <button x-on:click="confirmClose = false; $wire.forceClose()" class="finder-btn finder-btn-danger">{{ __('finder::finder.discard_close') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
