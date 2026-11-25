<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    $(function() {
        $('.select2').select2({
            width: '100%'
        });

        $('.select2-multiple').select2({
            width: '100%',
            placeholder: 'Select options'
        });

        $('#publish_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            sideBySide: true
        });

        let editorInstance = null;

        if (window.ClassicEditor && document.querySelector('#content')) {
            ClassicEditor.create(document.querySelector('#content'))
                .then(function(editor) {
                    editorInstance = editor;
                })
                .catch(function(error) {
                    console.error(error);
                });
        }

        function clearAudienceSelections() {
            $('.select2-multiple').val(null).trigger('change');
        }

        function toggleAudienceCard() {
            const isAllEmployees = $('#all_employees_yes').is(':checked');

            if (isAllEmployees) {
                $('#customAudienceCard').addClass('is-hidden');
                clearAudienceSelections();
                return;
            }

            $('#customAudienceCard').removeClass('is-hidden');
        }

        let attachmentPreviewUrl = null;
        const $preview = $('#attachmentPreview');
        const originalPreview = $preview.html();

        function toggleAttachmentPreview(file) {
            const $previewWrapper = $('#attachmentPreviewWrapper');

            if (!file) {
                if (attachmentPreviewUrl) {
                    URL.revokeObjectURL(attachmentPreviewUrl);
                    attachmentPreviewUrl = null;
                }
                $preview.html(originalPreview);
                $previewWrapper.toggleClass('d-none', !originalPreview.trim());
                return;
            }

            if (attachmentPreviewUrl) {
                URL.revokeObjectURL(attachmentPreviewUrl);
            }
            attachmentPreviewUrl = URL.createObjectURL(file);

            const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
            $preview.empty();
            if (isPdf) {
                $preview.append($('<iframe>', {
                    src: attachmentPreviewUrl + '#toolbar=0',
                    title: 'PDF attachment preview',
                    class: 'w-100 rounded border',
                    css: { height: '220px' }
                }));
            } else {
                $preview.append($('<img>', {
                    src: attachmentPreviewUrl,
                    alt: 'Attachment preview',
                    class: 'img-fluid rounded border',
                    css: { maxHeight: '220px', objectFit: 'contain' }
                }));
            }
            $preview.append(
                $('<a>', {
                    href: attachmentPreviewUrl,
                    target: '_blank',
                    rel: 'noopener noreferrer',
                    class: 'd-inline-block mt-2'
                }).text('Open attachment')
            );
            $previewWrapper.removeClass('d-none');
        }

        $(document).on('change', '.audience-option', toggleAudienceCard);

        $('#attachment').on('change', function() {
            const file = this.files && this.files[0] ? this.files[0] : null;
            toggleAttachmentPreview(file);
        });

        toggleAudienceCard();

        $('#announcementForm').on('submit', function() {
            if (editorInstance) {
                editorInstance.updateSourceElement();
            }
        });
    });
</script>
