<div>
    @if($open)
    <div class="finder-modal-overlay" x-data x-on:keydown.escape.window="$wire.close()">
        <div class="finder-modal finder-modal-md">
            <div class="finder-modal-header">
                <h3 class="finder-modal-title">{{ __('finder::finder.properties_modal_title') }}</h3>
                <button wire:click="close" class="finder-modal-close" title="{{ __('finder::finder.close') }}">&times;</button>
            </div>

            <div class="finder-modal-body">
                @if($fileInfo)
                <div class="finder-properties">

                    {{-- Icon / Preview --}}
                    <div class="finder-properties-header">
                        <div class="finder-properties-icon">
                            @if(isset($fileInfo['is_dir']) && $fileInfo['is_dir'])
                                <svg viewBox="0 0 48 48" fill="none">
                                    <path d="M6 10C6 8.895 6.895 8 8 8h10l4 4h18c1.105 0 2 .895 2 2v24c0 1.105-.895 2-2 2H8c-1.105 0-2-.895-2-2V10z" fill="#FFB900"/>
                                    <path d="M6 14h36v24c0 1.105-.895 2-2 2H8c-1.105 0-2-.895-2-2V14z" fill="#FFD352"/>
                                </svg>
                            @else
                                <svg viewBox="0 0 48 48" fill="none">
                                    <path d="M10 6a4 4 0 014-4h14l12 12v28a4 4 0 01-4 4H14a4 4 0 01-4-4V6z" fill="#9E9E9E"/>
                                    <path d="M38 14H24V6l14 8z" fill="rgba(0,0,0,0.2)"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="finder-properties-name">{{ $fileInfo['name'] }}</h4>
                            @if(isset($fileInfo['mime']) && $fileInfo['mime'] !== 'multiple')
                                <p class="finder-properties-mime">{{ $fileInfo['mime'] }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Details Table --}}
                    <table class="finder-properties-table">
                        @if(isset($fileInfo['path']))
                        <tr>
                            <th>{{ __('finder::finder.prop_location') }}</th>
                            <td><code>{{ dirname($fileInfo['path']) }}</code></td>
                        </tr>
                        @endif

                        <tr>
                            <th>{{ __('finder::finder.prop_size') }}</th>
                            <td>
                                {{ $fileInfo['size_human'] ?? '' }}
                                @if(isset($fileInfo['is_dir']) && $fileInfo['is_dir'] && !$directorySize)
                                    <button
                                        wire:click="calculateDirectorySize"
                                        class="finder-btn finder-btn-xs"
                                    >{{ __('finder::finder.prop_calculate') }}</button>
                                @endif
                                @if($calculatingSize)
                                    <span class="finder-spinner finder-spinner-sm"></span>
                                @endif
                            </td>
                        </tr>

                        @if(!empty($fileInfo['modified_human']))
                        <tr>
                            <th>{{ __('finder::finder.prop_modified') }}</th>
                            <td>{{ $fileInfo['modified_human'] }}</td>
                        </tr>
                        @endif

                        @if(isset($fileInfo['extension']) && $fileInfo['extension'])
                        <tr>
                            <th>{{ __('finder::finder.prop_type') }}</th>
                            <td>{{ __('finder::finder.prop_file_type', ['ext' => strtoupper($fileInfo['extension'])]) }}</td>
                        </tr>
                        @endif

                        @if(isset($fileInfo['dimensions']))
                        <tr>
                            <th>{{ __('finder::finder.prop_dimensions') }}</th>
                            <td>{{ $fileInfo['dimensions'] }}</td>
                        </tr>
                        @endif

                        <tr>
                            <th>{{ __('finder::finder.prop_permissions') }}</th>
                            <td>
                                @if($fileInfo['read'] ?? true) {{ __('finder::finder.prop_read') }} @endif
                                @if($fileInfo['write'] ?? true) {{ __('finder::finder.prop_write') }} @endif
                                @if($fileInfo['locked'] ?? false) {{ __('finder::finder.prop_locked') }} @endif
                            </td>
                        </tr>
                    </table>
                </div>
                @else
                <div class="finder-spinner-center">
                    <div class="finder-spinner"></div>
                    <p>{{ __('finder::finder.loading') }}</p>
                </div>
                @endif
            </div>

            <div class="finder-modal-footer">
                <button wire:click="close" class="finder-btn">{{ __('finder::finder.close') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
