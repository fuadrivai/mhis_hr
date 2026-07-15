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

        $(document).on('change', '.audience-option', toggleAudienceCard);

        toggleAudienceCard();

        $('#announcementForm').on('submit', function() {
            if (editorInstance) {
                editorInstance.updateSourceElement();
            }
        });
    });
</script>
