<div>
    @if($open)
    <div class="finder-modal-overlay" x-data x-on:keydown.escape.window="$wire.close()">
        <div class="finder-modal finder-modal-md">
            <div class="finder-modal-header">
                <h3 class="finder-modal-title">
                    {{ $mode === 'create' ? 'Create Archive' : 'Extract Archive' }}
                </h3>
                <button wire:click="close" class="finder-modal-close" title="Close">&times;</button>
            </div>

            <div class="finder-modal-body">
                @if($error)
                    <div class="finder-alert finder-alert-danger">{{ $error }}</div>
                @endif

                @if($mode === 'create')
                    <div class="finder-form-group">
                        <label class="finder-label">Archive Name</label>
                        <input
                            type="text"
                            wire:model="archiveName"
                            class="finder-input"
                            placeholder="archive.zip"
                        >
                    </div>

                    <div class="finder-form-group">
                        <label class="finder-label">Format</label>
                        <select wire:model.live="archiveType" class="finder-select">
                            <option value="zip">ZIP</option>
                            <option value="tar.gz">TAR.GZ</option>
                        </select>
                    </div>

                    <div class="finder-form-group">
                        <label class="finder-label">Items to archive</label>
                        <p class="finder-hint">{{ count($targetHashes) }} item(s) selected</p>
                    </div>

                @else
                    <p>Extract the selected archive to the current directory?</p>
                    <p class="finder-hint">Contents will be extracted to: <code>{{ $targetPath }}</code></p>
                @endif
            </div>

            <div class="finder-modal-footer">
                <button wire:click="close" class="finder-btn" @disabled($isProcessing)>Cancel</button>
                <button
                    wire:click="process"
                    class="finder-btn finder-btn-primary"
                    @disabled($isProcessing || empty($targetHashes))
                >
                    @if($isProcessing)
                        <span class="finder-spinner finder-spinner-sm"></span>
                    @endif
                    {{ $mode === 'create' ? 'Create Archive' : 'Extract' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
