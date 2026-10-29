<script src="{{ asset('assets/libs/quill/quill.min.js') }}"></script>
<script>
    (function () {
        const textarea = document.getElementById('body_html');
        const editorElement = document.getElementById('body-editor');
        const modeButtons = document.querySelectorAll('[data-editor-mode]');
        let mode = 'visual';

        const quill = new Quill(editorElement, {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        ['link', 'image'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'clean'],
                    ],
                    handlers: {
                        // Embed images by https URL; Quill's default would inline them as data: URIs, which are stripped.
                        image: function () {
                            const url = window.prompt('Image address (must start with https://)');
                            if (url && url.startsWith('https://')) {
                                const range = this.quill.getSelection(true);
                                this.quill.insertEmbed(range.index, 'image', url, 'user');
                            }
                        },
                    },
                },
            },
        });

        // The server sanitizes this HTML again on save and on send.
        quill.clipboard.dangerouslyPasteHTML(textarea.value);

        function showMode(nextMode) {
            if (nextMode === mode) {
                return;
            }
            if (nextMode === 'html') {
                textarea.value = quill.getSemanticHTML();
            } else {
                quill.setContents([]);
                quill.clipboard.dangerouslyPasteHTML(textarea.value);
            }
            mode = nextMode;
            textarea.classList.toggle('d-none', mode === 'visual');
            editorElement.parentElement.querySelector('.ql-toolbar').classList.toggle('d-none', mode === 'html');
            editorElement.classList.toggle('d-none', mode === 'html');
            modeButtons.forEach((button) => button.classList.toggle('active', button.dataset.editorMode === mode));
        }

        modeButtons.forEach((button) => button.addEventListener('click', () => showMode(button.dataset.editorMode)));

        textarea.form.addEventListener('submit', () => {
            if (mode === 'visual') {
                textarea.value = quill.getSemanticHTML();
            }
        });
    })();
</script>
