@extends('layout.master')

@section('title')
    Day Sheet
@endsection

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/easymde/easymde.min.css') }}" rel="stylesheet" />
@endpush

@push('style')
    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            min-height: 36px;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Others</a></li>
            <li class="breadcrumb-item active" aria-current="page">Day Sheet</li>
        </ol>
    </nav>

    <div class="row chat-wrapper">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row position-relative">
                        <div class="col-lg-4 chat-aside border-end-lg">
                            <div class="aside-content">
                                <div class="aside-header">
                                    <div class="d-flex justify-content-between align-items-center pb-2 mb-2">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6>Day Sheet</h6>
                                            </div>
                                        </div>
                                        </button>
                                        <button class="btn btn-link p-0 add-daysheet" type="button">
                                            <i class="icon-lg text-muted pb-3px" data-feather="plus-circle"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="aside-body">
                                    <ul class="nav nav-tabs nav-fill mt-3 d-none" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="chats-tab" data-bs-toggle="tab"
                                                data-bs-target="#chats" role="tab" aria-controls="chats"
                                                aria-selected="true">
                                                <div
                                                    class="d-flex flex-row flex-lg-column flex-xl-row align-items-center justify-content-center">
                                                    <i data-feather="message-square"
                                                        class="icon-sm me-sm-2 me-lg-0 me-xl-2 mb-md-1 mb-xl-0"></i>
                                                    <p class="d-none d-sm-block">Chats</p>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane fade show active" id="chats" role="tabpanel"
                                            aria-labelledby="chats-tab">
                                            <div>
                                                @if (count($daysheets) != 0)
                                                    <p class="text-muted mb-1">Recent sheets</p>
                                                @endif
                                                <ul class="list-unstyled chat-list px-1">
                                                    @if (count($daysheets) == 0)
                                                        <li class="mt-3 text-center text-muted">No items found!</li>
                                                    @endif
                                                    @foreach ($daysheets as $daysheet)
                                                        <li class="chat-item pe-1">
                                                            <a href="javascript:;" data-id="{{ $daysheet->id }}"
                                                                class="d-flex align-items-center daysheet-item">
                                                                <div
                                                                    class="d-flex justify-content-between flex-grow-1 border-bottom px-2">
                                                                    <p class="text-body fw-bolder">
                                                                        {{ $daysheet->title }}</p>
                                                                    <div class="d-flex flex-column align-items-end">
                                                                        <p class="text-muted tx-13 mb-1 text-nowrap">
                                                                            {{ \Carbon\Carbon::parse($daysheet->created_at)->format('F d, Y h:i A') }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 chat-content">
                            <div class="chat-body">
                                <form action="{{ route('daysheet.save') }}" method="post" id="daysheet-form">
                                    @csrf
                                    <input type="hidden" name="id">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center w-50">
                                            <i data-feather="corner-up-left" id="backToChatList"
                                                class="icon-lg me-2 ms-n2 text-muted d-lg-none"></i>
                                            <input type="text" class="form-control flex-grow" placeholder="Title"
                                                name="title">
                                        </div>
                                        <div class="">
                                            <a data-id="0"
                                                class="btn btn-outline-danger btn-sm px-2 mb-2 me-1 delete-daysheet"
                                                style="display: none;"><i class="icon-lg pb-3px"
                                                    data-feather="trash-2"></i></a>
                                            <button type="submit" class="btn btn-outline-dark btn-sm px-2 mb-2"><i
                                                    class="icon-lg pb-3px" data-feather="save"></i></button>
                                        </div>
                                    </div>
                                    <textarea class="form-control" name="data" id="easyMdeExample" rows="10"></textarea>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/easymde/easymde.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script src="{{ asset('assets/js/chat.js') }}"></script>
    <script src="{{ asset('assets/js/pages/day-sheet.js') }}"></script>
    @if ($errors->any())
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ $errors->first() }}'
            })
        </script>
    @endif
    @if (\Session::has('message'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: 'success',
                title: 'Success',
                text: '{!! \Session::get('message') !!}'
            })
        </script>
    @endif
@endpush
