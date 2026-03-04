@extends('layouts.info')
@section('content-class')
    <!-- custom styles for document cards -->
    <style>
        .document-category {
            margin-top: 1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .doc-card {
            border: 1px solid #e1e5eb;
            transition: transform .2s, box-shadow .2s;
            height: 100%;
        }

        .doc-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
        }

        .doc-number {
            font-size: .9rem;
            color: #7f8c8d;
        }

        .doc-notes {
            font-style: italic;
            color: #34495e;
        }

        @media (max-width: 576px) {
            .doc-card {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection

@section('content-employee')
    <div class="row">
        @if ($data->documents->isEmpty())
            <div class="col-12">
                <p class="text-muted text-center">No documents available for this employee.</p>
            </div>
        @else
            @foreach ($data->documents->groupBy('category.name') as $categoryName => $docs)
                <div class="col-12">
                    <h5 class="document-category">
                        {{ $categoryName ?? 'Uncategorized' }}
                    </h5>
                </div>
                @foreach ($docs as $doc)
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card doc-card mb-3">
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title mb-1">{{ $doc->category_name ?? $doc->category->name }}</h6>
                                <p class="doc-number mb-1">No: {{ $doc->document_number ?? '-' }}</p>
                                <p class="mb-1"><small>Issued:
                                        {{ optional($doc->issued_date)->format('Y-m-d') ?? '-' }}</small></p>
                                <p class="mb-1"><small>Expiry:
                                        {{ optional($doc->expiry_date)->format('Y-m-d') ?? '-' }}</small></p>
                                <p class="mb-1">Status:
                                    <span
                                        class="badge badge-{{ $doc->status == 'approved' ? 'success' : ($doc->status == 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                </p>
                                @if ($doc->latestVersion)
                                    <p class="mb-1">
                                        <a href="{{ $doc->latestVersion->file_url }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            v{{ $doc->latestVersion->version }} <i class="fa fa-download"></i>
                                        </a>
                                    </p>
                                @endif
                                @if ($doc->lastApproval)
                                    <p class="mb-1">Approval:
                                        <span
                                            class="text-{{ $doc->lastApproval->status == 'approved' ? 'success' : 'danger' }}">
                                            {{ ucfirst($doc->lastApproval->status) }}
                                        </span>
                                    </p>
                                @endif
                                @if ($doc->notes)
                                    <p class="doc-notes mt-auto">{{ \Illuminate\Support\Str::limit($doc->notes, 80) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @endif
    </div>
@endsection

@section('content-employee-script')
@endsection
