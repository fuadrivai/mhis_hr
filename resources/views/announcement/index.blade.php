@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th class="text-center">Creator</th>
                                    <th class="text-center">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($announcements as $item)
                                    @php
                                        $audience = $item->all_employees
                                            ? 'All Employees'
                                            : collect([
                                                $item->branches->pluck('name')->filter()->values()->all(),
                                                $item->organizations->pluck('name')->filter()->values()->all(),
                                                $item->jobLevels->pluck('name')->filter()->values()->all(),
                                                $item->positions->pluck('name')->filter()->values()->all(),
                                            ])
                                                ->map(function ($values, $index) {
                                                    $labels = ['Branches', 'Organizations', 'Job Levels', 'Positions'];

                                                    if (empty($values)) {
                                                        return null;
                                                    }

                                                    return $labels[$index] . ': ' . implode(', ', $values);
                                                })
                                                ->filter()
                                                ->implode(' | ');

                                        $announcementPayload = [
                                            'title' => $item->title,
                                            'category' => $item->category->name ?? 'N/A',
                                            'creator' =>
                                                $item->creator->personal->fullname ??
                                                ($item->creator->user->name ?? 'N/A'),
                                            'content' => $item->content,
                                            'audience' => $audience ?: 'Custom Audience',
                                            'attachment' => $item->attachment
                                                ? asset('storage/' . $item->attachment)
                                                : null,
                                            'publish_at' => optional($item->publish_at)->format('d M Y H:i') ?: 'N/A',
                                        ];
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                                        <td>{{ $item->creator->personal->fullname ?? ($item->creator->user->name ?? 'N/A') }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info btn-view-announcement"
                                                title="View" data-announcement='@json($announcementPayload)'>
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <a title="Edit" href="/announcement/{{ $item->id }}/edit"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="announcementViewModal" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="announcementViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementViewModalLabel">Announcement Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h4 id="modalAnnouncementTitle" class="mb-1"></h4>
                        <div class="text-muted" id="modalAnnouncementMeta"></div>
                    </div>

                    <div class="mb-3">
                        <strong>Audience</strong>
                        <div id="modalAnnouncementAudience" class="mt-1"></div>
                    </div>

                    <div id="modalAnnouncementAttachmentWrapper" class="mb-3 d-none">
                        <strong>Attachment</strong>
                        <div class="mt-2">
                            <img id="modalAnnouncementAttachment" src="" alt="Announcement attachment"
                                class="img-fluid rounded border" style="max-height: 320px; object-fit: contain;">
                        </div>
                    </div>

                    <div>
                        <strong>Content</strong>
                        <div id="modalAnnouncementContent" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="categoryForm" action="/announcement/category" method="POST">
                    @csrf
                    <div id="form-method">
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">Category Form</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">

                        <div class="form-group">
                            <label for="category_name">Name</label>
                            <input type="text" required class="form-control" id="category_name" name="name"
                                placeholder="Input category name">
                        </div>

                        <div class="form-group">
                            <label for="category_description">Description</label>
                            <textarea required class="form-control" id="category_description" name="description" rows="3"
                                placeholder="Input description"></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label class="d-block mb-2">Status</label>
                            <label class="switch mb-0">
                                <input type="checkbox" id="category_is_active" name="is_active" checked>
                                <span class="slider round"></span>
                            </label>
                            <span class="ml-2">Is Active</span>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="deleteCategoryForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>

    <script>
        $(document).ready(function() {
            tbldata = $("#tbl-datatable").DataTable({
                pageLength: 25,
                ordering: false,
                responsive: true,
                pagingType: 'simple',
                dom: `<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip`,
                buttons: [{
                    text: 'Add Announcement <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-announcement'
                    },
                    className: 'btn btn-success font-weight-bold mx-1',
                    action: function() {
                        window.location.href = '/announcement/create';
                    }
                }],
                language: {
                    info: "Page _PAGE_ of _PAGES_",
                    lengthMenu: "_MENU_ ",
                    search: "",
                    searchPlaceholder: "Search.."
                },
            });

            $(document).on('click', '.btn-view-announcement', function() {
                const announcement = $(this).data('announcement');

                $('#modalAnnouncementTitle').text(announcement.title || 'Announcement');
                $('#modalAnnouncementMeta').text(
                    `Category: ${announcement.category || 'N/A'} | Creator: ${announcement.creator || 'N/A'} | Publish At: ${announcement.publish_at || 'N/A'}`
                );
                $('#modalAnnouncementAudience').text(announcement.audience || 'All Employees');
                $('#modalAnnouncementContent').html(announcement.content || '<em>No content</em>');

                if (announcement.attachment) {
                    $('#modalAnnouncementAttachment').attr('src', announcement.attachment);
                    $('#modalAnnouncementAttachmentWrapper').removeClass('d-none');
                } else {
                    $('#modalAnnouncementAttachment').attr('src', '');
                    $('#modalAnnouncementAttachmentWrapper').addClass('d-none');
                }

                $('#announcementViewModal').modal('show');
            });
        })
    </script>
@endsection
