document.addEventListener('livewire:init', () => {
    // Handle file picker selection for regular forms
    Livewire.on('file-picker-selected', (data) => {
        try {
            const {
                selected,
                inputName,
                inputId,
                formId,
                multiple,
                autoSubmit,
                callbackFunction
            } = data[0] || data || {};

            if (!selected || !inputName) {
                return;
            }

            // Auto-submit form if enabled
            if (autoSubmit && formId) {
                const form = document.getElementById(formId);
                if (form) {
                    form.submit();
                }
            }

            // Call custom callback function
            if (callbackFunction && typeof window[callbackFunction] === 'function') {
                window[callbackFunction](selected, inputName, inputId);
            }

            // Dispatch custom event
            window.dispatchEvent(new CustomEvent('file-picker:selected', {
                detail: {
                    selected,
                    inputName,
                    inputId,
                    multiple
                }
            }));
        } catch (error) {
            console.error('File picker selection error:', error);
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        const backdrop = document.querySelector('.fp-backdrop');
        if (!backdrop) return;

        // Escape: close the detail sheet first if it's open on a tablet/mobile
        // viewport; otherwise close the whole modal.
        if (e.key === 'Escape') {
            const sheetOpen = document.querySelector('.fp-library-sidebar.fp-library-sidebar--open');
            if (sheetOpen && window.innerWidth <= 1024) {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('close-details'));
                return;
            }

            e.preventDefault();
            Livewire.dispatch('closeModal');
        }
    });

    // Paste-to-upload support
    document.addEventListener('paste', (e) => {
        const backdrop = document.querySelector('.fp-backdrop');
        if (!backdrop) return;

        const items = e.clipboardData?.items;
        if (!items) return;

        const files = [];
        for (const item of items) {
            if (item.kind === 'file') {
                const file = item.getAsFile();
                if (file) files.push(file);
            }
        }

        if (files.length > 0) {
            e.preventDefault();
            // Find the file input and set files
            const fileInput = backdrop.querySelector('input[type="file"]');
            if (fileInput) {
                const dt = new DataTransfer();
                files.forEach(f => dt.items.add(f));
                fileInput.files = dt.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });
});
