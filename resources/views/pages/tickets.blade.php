@extends('layout.master')

@section('title')
    Tickets
@endsection

@push('plugin-styles')
    {{-- <link href="{{ asset('assets/plugins/datatables-net-2/datatables.min.css') }}" rel="stylesheet" /> --}}
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@push('style')
    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            min-height: 36px;
        }

        .dt-buttons {
            float: right;
            margin-top: 1px;
            margin-left: 9px;
        }

        .dataTables_filter {
            float: right;
        }

        .dataTables_length {
            display: inline-block;
        }

        .swal2-popup {
            width: 45em;
        }

        .swal2-input-label{
            margin-bottom: -12px;
        }

        .table{
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Main</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tickets</li>
        </ol>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h6 class="card-title mb-0">Tickets</h6>
                            <div class="btn-group">
                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="action-btn"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <button class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target=".bd-example-modal-lg"><svg xmlns="http://www.w3.org/2000/svg"
                                            width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-plus-circle me-1">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="16"></line>
                                            <line x1="8" y1="12" x2="16" y2="12"></line>
                                        </svg>Add Ticket</button>
                                    <button class="dropdown-item" id="print-report">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-printer me-1">
                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                            <path
                                                d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                            </path>
                                            <rect x="6" y="14" width="12" height="8"></rect>
                                        </svg>
                                        Generate Report</button>
                                    <button class="dropdown-item disabled" id="bulk-collect">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-check-square me-1">
                                            <polyline points="9 11 12 14 22 4"></polyline>
                                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                        </svg>
                                        Bulk Collection</button>
                                </div>
                            </div>
                        </div>

                        <div class="accordion mb-3" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                        FILTERS
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">

                                        <div class="row align-items-start g-2">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <label class="col-md-3 col-form-label">
                                                        Care of:</label>
                                                    <div class="col-md-9">
                                                        <select class="multiple-select form-select" multiple="multiple"
                                                            name="filter_references[]" required>
                                                            @foreach ($references as $reference)
                                                                <option value="{{ $reference->id }}">
                                                                    {{ $reference->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <label class="col-md-3 col-form-label">Supplier:</label>
                                                    <div class="col-md-9">
                                                        <select class="multiple-select form-select" multiple="multiple"
                                                            name="filter_suppliers[]" required>
                                                            @foreach ($suppliers as $supplier)
                                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <label class="col-md-3 col-form-label">Departure Date:</label>
                                                    <div class="col-md-9">
                                                        <div class="btn-group mb-3 mb-md-0" role="group"
                                                            aria-label="Basic example">
                                                            <button type="button" data-type="dep_dt"
                                                                class="filter_dt btn btn-sm btn-outline-primary"
                                                                data-filter-id="1">Today</button>
                                                            <button type="button" data-type="dep_dt"
                                                                class="filter_dt btn btn-sm btn-outline-primary"
                                                                data-filter-id="2">This
                                                                Week</button>
                                                            <button type="button" data-type="dep_dt"
                                                                class="filter_dt btn btn-sm btn-outline-primary"
                                                                data-filter-id="3">This
                                                                Month</button>
                                                            <button type="button" data-type="dep_dt"
                                                                class="filter_dt btn btn-sm btn-outline-primary"
                                                                data-filter-id="4">Custom</button>
                                                        </div>
                                                        <input type="text" placeholder="Select Date Range.."
                                                            data-type="dep_dt"
                                                            class="form-control form-control-sm filter_dt_date_range mt-2"
                                                            readonly="readonly" style="display: none;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <label class="col-md-3 col-form-label">Created at:</label>
                                                    <div class="col-md-9">
                                                        <div class="btn-group mb-3 mb-md-0" role="group"
                                                            aria-label="Basic example">
                                                            <button type="button" data-type="cre_dt"
                                                                class="filter_dt btn btn-sm btn-outline-primary"
                                                                data-filter-id="1">Today</button>
                                                            <button type="button" data-type="cre_dt"
                                                                class="filter_dt btn btn-sm btn-outline-primary"
                                                                data-filter-id="2">This
                                                                Week</button>
                                                            <button type="button" data-type="cre_dt"
                                                                class="filter_dt btn btn-sm btn-outline-primary"
                                                                data-filter-id="3">This
                                                                Month</button>
                                                            <button type="button" data-type="cre_dt"
                                                                class="filter_dt btn btn-sm btn-outline-primary"
                                                                data-filter-id="4">Custom</button>
                                                        </div>
                                                        <input type="text" placeholder="Select Date Range.."
                                                            data-type="cre_dt"
                                                            class="form-control form-control-sm filter_dt_date_range mt-2"
                                                            readonly="readonly" style="display: none;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive perfect-scrollbar">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Care Of</th>
                                        <th>Name</th>
                                        <th>Ticket No</th>
                                        <th>Departure Date</th>
                                        <th>Return Date</th>
                                        <th>Supplier</th>
                                        <th>Cost</th>
                                        <th>Profit</th>
                                        <th>Total</th>
                                        <th>Collected Amount</th>
                                        <th>Balance Amount</th>
                                        <th>Refunded At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr style="vertical-align: top;">
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th style="text-align: right !important;">Total:</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-ticket-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('ticket.edit') }}" method="post" autocomplete="off">
                @csrf
                <input type="hidden" name="ticket_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit Ticket</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row align-items-start g-2">
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Date:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="date" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Care of:</label>
                                    <div class="col-md-9">
                                        <select class="form-select input_care_of_2" data-tags="true"
                                            name="filter_references" required>
                                            <option value="">Select..</option>
                                            @foreach ($references as $reference)
                                                <option value="{{ $reference->id }}">
                                                    {{ $reference->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted ms-1" id="careof-bal-amt-2"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Customer Name:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="customer_name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Ticket No:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="ticket_no" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Departure date:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="departure_date" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Return date:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="return_date" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Supplier:</label>
                                    <div class="col-md-9">
                                        <select class="multiple-select-modal2 form-select" data-tags="true"
                                            name="filter_suppliers" required>
                                            <option value="">Select..</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">
                                                    {{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Cost (in SAR):</label>
                                    <div class="col-md-9">
                                        <input name="cost" id="cost-input2" class="form-control inputmask"
                                            data-inputmask="'alias': 'currency', 'prefix':'SAR '" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Total (in SAR):</label>
                                    <div class="col-md-9">
                                        <input name="total" id="total-input2" class="form-control inputmask"
                                            data-inputmask="'alias': 'currency', 'prefix':'SAR '" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Collection Amount (in SAR):</label>
                                    <div class="col-md-9">
                                        <input name="collection_amount" class="form-control inputmask"
                                            data-inputmask="'alias': 'currency', 'prefix':'SAR '" required>
                                        <label id="collection_amount-error" class="error invalid-feedback"
                                            for="collection_amount">The amount collected exceeds the total amount.</label>
                                        <small class="text-muted pending-collection float-end pe-3 pt-1"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Profit (in SAR):</label>
                                    <div class="col-md-9">
                                        <input name="profit" id="profit-output2" class="form-control inputmask"
                                            data-inputmask="'alias': 'currency', 'prefix':'SAR '" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade bd-example-modal-lg" tabindex="-1" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('ticket.store') }}" method="post" autocomplete="off">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add Ticket</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row align-items-start g-2">
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Date:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="date" class="form-control flatpickr"
                                            value="{{ now()->format('F j, Y h:i A') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Care of:</label>
                                    <div class="col-md-9">
                                        <select class="form-select input_care_of" data-tags="true"
                                            name="filter_references" required>
                                            <option value="">Select..</option>
                                            @foreach ($references as $reference)
                                                <option value="{{ $reference->id }}"
                                                    @if ($reference->id == 1) @selected(true) @endif>
                                                    {{ $reference->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted ms-1" id="careof-bal-amt"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Customer Name:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="customer_name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Ticket No:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="ticket_no" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Departure date:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="departure_date" class="form-control flatpickr">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Return date:</label>
                                    <div class="col-md-9">
                                        <input type="text" name="return_date" class="form-control flatpickr">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Supplier:</label>
                                    <div class="col-md-9">
                                        <select class="multiple-select-modal form-select" data-tags="true"
                                            name="filter_suppliers" required>
                                            <option value="">Select..</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">
                                                    {{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Cost (in SAR):</label>
                                    <div class="col-md-9">
                                        <input name="cost" id="cost-input" class="form-control inputmask"
                                            data-inputmask="'alias': 'currency', 'prefix':'SAR '" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Total (in SAR):</label>
                                    <div class="col-md-9">
                                        <input name="total" id="total-input" class="form-control inputmask"
                                            data-inputmask="'alias': 'currency', 'prefix':'SAR '" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Collection Amount (in
                                        SAR):</label>
                                    <div class="col-md-9">
                                        <input name="collection_amount" class="form-control inputmask"
                                            data-inputmask="'alias': 'currency', 'prefix':'SAR '">
                                        <label id="collection_amount-error" class="error invalid-feedback"
                                            for="collection_amount">The amount collected exceeds the total amount.</label>
                                        <small class="text-muted pending-collection float-end pe-3 pt-1"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <label class="col-md-3 col-form-label">Profit (in SAR):</label>
                                    <div class="col-md-9">
                                        <input name="profit" id="profit-output" class="form-control inputmask"
                                            data-inputmask="'alias': 'currency', 'prefix':'SAR '" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    {{-- <script src="{{ asset('assets/plugins/datatables-net-2/datatables.min.js') }}"></script> --}}
    <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script src="{{ asset('assets/js/pages/tickets.js') }}"></script>
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
