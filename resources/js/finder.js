/**
 * Finder - File Manager for Laravel + Livewire
 * Finder — Laravel & Livewire File Manager
 */

(function () {
    'use strict';

    /**
     * Keyboard shortcut handler
     */
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function (e) {
            // Skip if inside an input/textarea/editor
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            const isMeta = e.ctrlKey || e.metaKey;

            if (isMeta && e.key === 'a') {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('finder:select-all'));
            }

            if (isMeta && e.key === 'c') {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('finder:copy-selected'));
            }

            if (isMeta && e.key === 'x') {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('finder:cut-selected'));
            }

            if (isMeta && e.key === 'v') {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('finder:paste'));
            }

            if (e.key === 'Delete' || e.key === 'Backspace' && isMeta) {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('finder:confirm-delete'));
            }

            if (e.key === 'F2') {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('finder:open-modal', { detail: { modal: 'rename' } }));
            }

            if (e.key === 'F5') {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('finder:items-changed'));
            }

            if (e.key === 'Escape') {
                // Handled by Alpine.js
            }
        });
    }

    /**
     * Drag and drop file upload to the file list area
     */
    function initDragAndDrop() {
        document.addEventListener('dragover', function (e) {
            const fileList = e.target.closest('.finder-filelist');
            if (fileList && e.dataTransfer.types.includes('Files')) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            }
        });

        document.addEventListener('drop', function (e) {
            const fileList = e.target.closest('.finder-filelist');
            if (fileList && e.dataTransfer.files.length > 0) {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('finder:drop-files', {
                    detail: { files: e.dataTransfer.files }
                }));
                window.dispatchEvent(new CustomEvent('finder:open-modal', {
                    detail: { modal: 'upload' }
                }));
            }
        });
    }

    /**
     * Item drag between folders
     */
    function initItemDrag() {
        let draggedHash = null;

        document.addEventListener('dragstart', function (e) {
            const item = e.target.closest('[data-hash]');
            if (item) {
                draggedHash = item.dataset.hash;
            }
        });

        document.addEventListener('dragend', function () {
            draggedHash = null;
        });
    }

    /**
     * Double-click prevention (allow single click selection + double click open)
     */
    function initClickHandling() {
        let clickTimer = null;

        document.addEventListener('click', function (e) {
            const gridItem = e.target.closest('.finder-grid-item');
            const listRow = e.target.closest('.finder-list-row');
            const item = gridItem || listRow;

            if (!item) return;

            if (clickTimer) {
                clearTimeout(clickTimer);
                clickTimer = null;
            } else {
                clickTimer = setTimeout(function () {
                    clickTimer = null;
                }, 300);
            }
        });
    }

    /**
     * Auto-resize textarea in editor
     */
    function initEditorEnhancements() {
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('finder-editor-textarea')) {
                // Tab key support
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.target.classList.contains('finder-editor-textarea') && e.key === 'Tab') {
                e.preventDefault();
                const start = e.target.selectionStart;
                const end = e.target.selectionEnd;
                e.target.value = e.target.value.substring(0, start) + '    ' + e.target.value.substring(end);
                e.target.selectionStart = e.target.selectionEnd = start + 4;
                // Trigger Livewire model update
                e.target.dispatchEvent(new Event('input'));
            }
        });
    }

    /**
     * Notification / Toast helper
     */
    window.FinderToast = {
        show: function (message, type = 'info', duration = 3000) {
            const toast = document.createElement('div');
            toast.className = 'finder-toast finder-toast--' + type;
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                bottom: 40px;
                right: 20px;
                z-index: 99999;
                padding: 10px 18px;
                background: var(--finder-surface);
                border: 1px solid var(--finder-border);
                border-radius: var(--finder-radius);
                box-shadow: var(--finder-shadow-lg);
                font-size: 13px;
                color: var(--finder-text);
                transition: all 0.3s ease;
                max-width: 320px;
                animation: finder-toast-in 0.3s ease;
            `;

            if (type === 'success') {
                toast.style.borderColor = 'var(--finder-success)';
                toast.style.color = '#065f46';
                toast.style.background = '#d1fae5';
            } else if (type === 'error') {
                toast.style.borderColor = 'var(--finder-danger)';
                toast.style.color = '#991b1b';
                toast.style.background = '#fee2e2';
            }

            document.body.appendChild(toast);

            setTimeout(function () {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                setTimeout(function () {
                    toast.remove();
                }, 300);
            }, duration);
        }
    };

    /**
     * Livewire event listeners for toasts
     */
    function initLivewireListeners() {
        window.addEventListener('finder:upload-complete', function (e) {
            const count = e.detail.files ? e.detail.files.length : 1;
            FinderToast.show(count + ' file(s) uploaded successfully', 'success');
        });

        window.addEventListener('finder:items-changed', function () {
            // Global refresh event
        });
    }

    /**
     * Theme management (dark / light)
     * The actual data-theme attribute is managed by Alpine.js (x-bind:data-theme="theme")
     * so it survives Livewire morphing automatically.
     * finderToggleTheme() simply dispatches an event that Alpine listens to.
     */
    window.finderToggleTheme = function () {
        window.dispatchEvent(new CustomEvent('finder:toggle-theme'));
    };

    function initTheme() {
        // Nothing to do: Alpine.js reads localStorage on init and binds data-theme reactively.
    }

    /**
     * Initialize everything
     */
    function init() {
        initTheme();
        initKeyboardShortcuts();
        initDragAndDrop();
        initItemDrag();
        initClickHandling();
        initEditorEnhancements();
        initLivewireListeners();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-init after Livewire navigations
    if (typeof window.Livewire !== 'undefined') {
        window.Livewire.hook('morph.added', function ({ el }) {
            // Re-apply any enhancements to new elements
        });
    }

})();
