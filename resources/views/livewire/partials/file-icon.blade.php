@if($item->isImage())
    <svg viewBox="0 0 32 32" fill="none" class="finder-filetype-icon">
        <rect x="2" y="4" width="28" height="24" rx="2" fill="#4CAF50"/>
        <path d="M2 20l8-8 6 6 4-4 10 10H2z" fill="rgba(255,255,255,0.4)"/>
        <circle cx="10" cy="12" r="2" fill="white"/>
    </svg>
@elseif($item->isVideo())
    <svg viewBox="0 0 32 32" fill="none" class="finder-filetype-icon">
        <rect x="2" y="4" width="28" height="24" rx="2" fill="#F44336"/>
        <polygon points="12,10 24,16 12,22" fill="white"/>
    </svg>
@elseif($item->isAudio())
    <svg viewBox="0 0 32 32" fill="none" class="finder-filetype-icon">
        <rect x="2" y="4" width="28" height="24" rx="2" fill="#9C27B0"/>
        <circle cx="10" cy="20" r="4" stroke="white" stroke-width="2" fill="none"/>
        <path d="M14 20V10l8-2v8" stroke="white" stroke-width="2"/>
        <circle cx="22" cy="18" r="2" stroke="white" stroke-width="2" fill="none"/>
    </svg>
@elseif($item->isPdf())
    <svg viewBox="0 0 32 32" fill="none" class="finder-filetype-icon">
        <rect x="2" y="2" width="28" height="28" rx="2" fill="#F44336"/>
        <text x="5" y="20" font-size="10" fill="white" font-weight="bold">PDF</text>
    </svg>
@elseif($item->isArchive())
    <svg viewBox="0 0 32 32" fill="none" class="finder-filetype-icon">
        <rect x="2" y="4" width="28" height="24" rx="2" fill="#795548"/>
        <rect x="8" y="8" width="16" height="4" rx="1" fill="rgba(255,255,255,0.5)"/>
        <rect x="8" y="14" width="16" height="4" rx="1" fill="rgba(255,255,255,0.5)"/>
        <rect x="8" y="20" width="16" height="4" rx="1" fill="rgba(255,255,255,0.5)"/>
    </svg>
@elseif($item->isText())
    <svg viewBox="0 0 32 32" fill="none" class="finder-filetype-icon">
        <rect x="2" y="2" width="28" height="28" rx="2" fill="#2196F3"/>
        <path d="M8 10h16M8 14h16M8 18h12" stroke="white" stroke-width="2" stroke-linecap="round"/>
    </svg>
@else
    <svg viewBox="0 0 32 32" fill="none" class="finder-filetype-icon">
        <path d="M6 4a2 2 0 012-2h10l8 8v18a2 2 0 01-2 2H8a2 2 0 01-2-2V4z" fill="#9E9E9E"/>
        <path d="M18 2l8 8h-6a2 2 0 01-2-2V2z" fill="rgba(0,0,0,0.3)"/>
        <text x="8" y="24" font-size="6" fill="white" font-weight="bold">{{ strtoupper(substr($item->extension() ?: 'FILE', 0, 3)) }}</text>
    </svg>
@endif
