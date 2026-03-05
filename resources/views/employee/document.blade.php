@extends('layouts.info')
@section('content-class')
    <style>
        #fileInfo {
            animation: slideIn 0.3s ease;
        }

        #filePreview img {
            max-height: 150px;
            display: block;
            margin-bottom: 0.5rem;
        }

        #filePreview embed {
            width: 100%;
            height: 200px;
        }

        .pdf-preview {
            width: 100%;
            height: 200px;
            /* tinggi konsisten */
            overflow: hidden;
            border-radius: 8px;
        }

        .pdf-preview embed {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
@endsection

@section('content-employee')
    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    <div class="row">
        <div class="col-12 text-right">
            <button data-toggle="modal" data-target="#documentUploadModal" class="btn btn-sm btn-success"><i
                    class="fa fa-plus"></i> Add Document</button>
        </div>
    </div>
    <div class="x_panel">
        <div class="row">
            @if ($data->documents->isEmpty())
                <div class="col-12">
                    <p class="text-muted text-center">No documents available for this employee.</p>
                </div>
            @else
                @foreach ($data->documents as $doc)
                    <div class="col-md-4 col-sm-12">
                        <div class="card">
                            <img src="https://drive.google.com/thumbnail?id={{ $doc->latestVersion->drive_file_id }}"
                                class="card-img-top" alt="{{ $doc->category->name ?? 'Uncategorized' }}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <label for="">{{ $doc->category->name ?? 'Uncategorized' }}</label><br>
                                        <small><i>{{ $doc->latestVersion->file_name }}</i></small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 justify-content-center d-flex">
                                        <div class="btn-group" role="group" aria-label="Basic outlined example">
                                            <form action="/profile/document/{{ $doc->id }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            <a href="{{ $doc->latestVersion->file_url }}" class="btn btn-outline-primary"
                                                target="_blank"><i class="fa fa-download"></i> Download</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <x-modal-upload-document :categories="$categories" id="documentUploadModal" />
@endsection

@section('content-employee-script')
    <script>
        let employeeId = {{ $data->id }};
        let _file = null;
        $(document).ready(function() {
            $('#documentFile').change(handleFileSelect);
            $('#documentForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                if (_file) {
                    formData.append('category_id', $('#docCategorySelect').val());
                    formData.append('category_name', $('#docCategorySelect option:selected').text());
                    formData.append('document_number', $('#documentNumber').val());
                    formData.append(`issued_date`, $('#issuedDate').val() ? moment($(
                        '#issuedDate').val(), 'DD MMMM YYYY').format("YYYY-MM-DD") : "");
                    formData.append('expiry_date', $('#expiryDate').val() ? moment($(
                        '#expiryDate').val(), "DD MMMM YYYY").format("YYYY-MM-DD") : "");
                    formData.append(`notes`, $('#docNotes').val());
                    formData.append('documentFile', _file);
                } else {
                    sweetAlert("Error", "Please select a file to upload", "error");
                    return;
                }
                $.ajax({
                    url: `/employee/${employeeId}/document/upload`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function(response) {
                        $('#documentUploadModal').modal('hide');
                        sweetAlert("Success", "Document uploaded successfully", "success");
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message ||
                            "An error occurred while uploading the document";
                        sweetAlert("Error", errorMsg, "error");
                    }
                });
            });
        });

        function handleFileSelect() {
            const file = $('#documentFile')[0].files[0];
            const fileInfo = $('#fileInfo');
            const fileName = $('#fileName');

            if (file) {
                const maxSize = 5 * 1024 * 1024
                if (file.size > maxSize) {
                    sweetAlert("Warning", "File size exceeds 5MB limit", "warning");
                    $('#documentFile').val('');
                    fileInfo.hide();
                    return;
                }

                const preview = $('#filePreview');
                preview.empty();

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.html(`<img src="${e.target.result}" alt="preview" />`);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.html(`<embed src="${e.target.result}" type="application/pdf" />`);
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.html(`<p>${file.name}</p>`);
                }

                fileName.text(`${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
                fileInfo.show();
                _file = file;
            } else {
                fileInfo.hide();
                _file = null;
            }
        }
    </script>
@endsection
