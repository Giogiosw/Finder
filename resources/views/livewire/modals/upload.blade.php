<div>
    @if($open)
    <div class="finder-modal-overlay" x-data x-on:keydown.escape.window="$wire.close()">
        <div class="finder-modal finder-modal-lg">
            <div class="finder-modal-header">
                <h3 class="finder-modal-title">Upload Files</h3>
                <button wire:click="close" class="finder-modal-close" title="Close">&times;</button>
            </div>

            <div class="finder-modal-body">
                {{-- Drop Zone --}}
                <div
                    class="finder-upload-zone"
                    x-data="{ dragging: false }"
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave="dragging = false"
                    x-on:drop.prevent="dragging = false; $wire.upload('uploads', $event.dataTransfer.files)"
                    :class="{ 'finder-upload-zone--drag': dragging }"
                >
                    <svg class="finder-upload-icon" viewBox="0 0 48 48" fill="none">
                        <path d="M24 8v24M14 18l10-10 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 36h32" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <p class="finder-upload-text">Drag & drop files here</p>
                    <p class="finder-upload-subtext">or</p>
                    <label class="finder-btn finder-btn-primary">
                        Browse files
                        <input
                            type="file"
                            wire:model="uploads"
                            multiple
                            class="finder-upload-input"
                        >
                    </label>
                    <p class="finder-upload-limit">
                        Max file size: {{ round(config('finder.upload.max_size', 52428800) / 1024 / 1024, 0) }}MB
                    </p>
                </div>

                {{-- Upload Queue --}}
                @if(!empty($uploads))
                <div class="finder-upload-queue">
                    <h4>Ready to upload:</h4>
                    <ul class="finder-upload-list">
                        @foreach($uploads as $file)
                        <li class="finder-upload-item">
                            <span class="finder-upload-filename">{{ $file->getClientOriginalName() }}</span>
                            <span class="finder-upload-filesize">{{ round($file->getSize() / 1024, 1) }} KB</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Uploaded files --}}
                @if(!empty($uploadedFiles))
                <div class="finder-upload-done">
                    <h4>Uploaded successfully:</h4>
                    <ul class="finder-upload-list">
                        @foreach($uploadedFiles as $file)
                        <li class="finder-upload-item finder-upload-item--done">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="finder-icon-sm finder-icon-success">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $file['name'] }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Errors --}}
                @if(!empty($errors))
                <div class="finder-alert finder-alert-danger">
                    <strong>Failed to upload:</strong>
                    <ul>
                        @foreach($errors as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Progress indicator --}}
                @if($isUploading)
                <div class="finder-upload-progress">
                    <div class="finder-spinner"></div>
                    <span>Uploading...</span>
                </div>
                @endif
            </div>

            <div class="finder-modal-footer">
                <button wire:click="close" class="finder-btn">Cancel</button>
                @if(!empty($uploads) && !$isUploading)
                    <button wire:click="uploadFiles" class="finder-btn finder-btn-primary">
                        Upload {{ count($uploads) }} file(s)
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
