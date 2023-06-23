function loadTable() {
    $('#dataTableExample').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        autoWidth: true,
        dom: 'B<"clear">lfrtip',
        buttons: [
            {
                extend: 'print',
                exportOptions: {
                    columns: [1, 2, 6, 7, 8, 9, 10]
                },
                footer: true,
                text: 'Print <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer ms-1"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>',
                className: 'btn btn-success d-inline-block btn-sm'
            },
        ],
        aLengthMenu: [
            [25, 50, 100, 200, -1],
            [25, 50, 100, 200, "All"]
        ],
        stateSave: true,
        order: [],
        ajax: {
            url: '/list_ticket',
            data: {
                referred_by: $('[name="filter_references[]"]').val(),
                suppliers: $('[name="filter_suppliers[]"]').val(),
                departure_at: $('.filter_dt[data-type="dep_dt"].btn-primary').data('filter-id'),
                departure_custom_date: $('.filter_dt_date_range[data-type="dep_dt"]').val(),
                created_at: $('.filter_dt[data-type="cre_dt"].btn-primary').data('filter-id'),
                created_custom_date: $('.filter_dt_date_range[data-type="cre_dt"]').val(),
            }
        },
        columns: [
            { data: 'created_at', orderable: false },
            { data: 'reference.name' },
            { data: 'customer_name' },
            { data: 'ticket_number' },
            { data: 'departure_date' },
            { data: 'return_date' },
            { data: 'supplier.name' },
            { data: 'cost' },
            { data: 'profit' },
            { data: 'total' },
            { data: 'collection_amount' },
            { data: 'refunded_at' },
            { data: 'action' },
        ],
        columnDefs: [{
            "className": "text-center",
            "targets": "_all"
        },
        {
            "targets": [0, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            "searchable": false
        }],
        createdRow: function (row, data, dataIndex) {
            if (data.refunded_at !== '') {
                $('td', row).addClass('text-warning');
            }
        },
        drawCallback: function (settings) {
            $('.confirm-link').click(function (e) {
                e.preventDefault();
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger me-2'
                    },
                    buttonsStyling: false,
                })

                let msg, confirmButtonText;
                if ($(this).attr('title') == 'Delete') {
                    msg = 'Are you sure you want to delete this ticket?'
                    confirmButtonText = 'Yes, delete it!'
                } else if ($(this).attr('title') == 'Mark as Collected') {
                    msg = 'Are you sure you want to mark this ticket as collected?'
                    confirmButtonText = 'Yes, mark it!'
                } else {
                    msg = 'Are you sure you want to refund this ticket?'
                    confirmButtonText = 'Yes, refund it!'
                }

                swalWithBootstrapButtons.fire({
                    title: msg,
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonClass: 'me-2',
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        window.location.href = $(this).attr('href');
                    }
                })
            })
            $('.edit-ticket-link').click(function (e) {
                $.ajax({
                    url: 'get_ticket/' + $(this).data('ticket-id'),
                    method: 'get',
                    dataType: 'json',
                    success: function (response) {
                        var flatpickrInstance1 = flatpickr('#edit-ticket-modal [name="date"]', {
                            enableTime: true,
                            dateFormat: 'F j, Y h:i K'
                        });
                        flatpickrInstance1.setDate(response.created_at, true);

                        var flatpickrInstance2 = flatpickr('#edit-ticket-modal [name="departure_date"]', {
                            enableTime: true,
                            dateFormat: 'F j, Y h:i K'
                        });
                        flatpickrInstance2.setDate(response.departure_date, true);

                        var flatpickrInstance3 = flatpickr('#edit-ticket-modal [name="return_date"]', {
                            enableTime: true,
                            dateFormat: 'F j, Y h:i K'
                        });
                        flatpickrInstance3.setDate(response.return_date, true);

                        $('#edit-ticket-modal [name="customer_name"]').val(response.customer_name);

                        $('#edit-ticket-modal [name="filter_references"]').val(response.reference_id).trigger('change');
                        $('#careof-bal-amt-2').html('');
                        if (response.reference_id > 1) {
                            $.ajax({
                                url: 'get_collection_balance/' + response.reference_id,
                                method: 'get',
                                dataType: 'json',
                                success: function (response) {
                                    $('#careof-bal-amt-2').html(response.message);
                                }
                            });
                        }

                        $('#edit-ticket-modal [name="filter_suppliers"]').val(response.supplier_id).trigger('change');
                        $('#edit-ticket-modal [name="cost"]').val(response.cost);
                        $('#edit-ticket-modal [name="profit"]').val(response.profit);
                        $('#edit-ticket-modal [name="total"]').val(response.total);
                        $('#edit-ticket-modal [name="collection_amount"]').val(response.collection_amount);
                        $('#edit-ticket-modal [name="ticket_no"]').val(response.ticket_number);
                        $('#edit-ticket-modal [name="ticket_id"]').val(response.id);
                        $('#edit-ticket-modal').modal('show');
                    }
                });
            })
        },
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;

            let costTotal = 0.0, collectionTotal = 0.0, grossTotal = 0.0, profitTotal = 0.0;
            $.each(data, function (i, value) {
                collectionTotal += parseFloat(value.collect_amount);
                costTotal += parseFloat(value.cost_amount);
                grossTotal += parseFloat(value.total_amount);
                profitTotal += parseFloat(value.profit_amount);
            })

            pendingTotal = grossTotal - collectionTotal;

            $(api.column(7).footer()).html(costTotal.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }));
            $(api.column(8).footer()).html(profitTotal.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }));
            $(api.column(9).footer()).html(grossTotal.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }));
            $(api.column(10).footer()).html(collectionTotal.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }) + '<br> (Pending: ' + pendingTotal.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }) + ')');
        }
    });
    $('#dataTableExample').each(function () {
        var datatable = $(this);

        var search_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
        search_input.attr('placeholder', 'Search');
        search_input.removeClass('form-control-sm');

        var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_length] select');
        length_sel.removeClass('form-control-sm');
    });
}

$('.filter_dt').click(function () {
    let filter_type = $(this).data('type');
    if ($(this).html() == $('.filter_dt.btn-primary[data-type="' + filter_type + '"]').html()) {
        $(this).addClass('btn-outline-primary');
        $(this).removeClass('btn-primary');
        $('.filter_dt_date_range[data-type="' + filter_type + '"]').fadeOut();
    } else {
        $('.filter_dt[data-type="' + filter_type + '"]').removeClass('btn-primary');
        $('.filter_dt[data-type="' + filter_type + '"]').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary');
        $(this).addClass('btn-primary');

        if ($(this).html() == 'Custom') {
            $('.filter_dt_date_range[data-type="' + filter_type + '"]').fadeIn();
        } else {
            $('.filter_dt_date_range[data-type="' + filter_type + '"]').fadeOut();
        }
    }

    loadTable();
})

$(function ($) {
    'use strict';

    if ($(".multiple-select").length) {
        $(".multiple-select").select2({
            closeOnSelect: false,
            width: "100%"
        });
    }
    if ($(".multiple-select-modal").length) {
        $(".multiple-select-modal").select2({
            width: "100%",
            dropdownParent: $('.bd-example-modal-lg')
        });
    }
    if ($(".multiple-select-modal2").length) {
        $(".multiple-select-modal2").select2({
            width: "100%",
            dropdownParent: $('#edit-ticket-modal')
        });
    }

    flatpickr(".filter_dt_date_range", {
        mode: 'range',
        dateFormat: 'F j, Y'
    });

    flatpickr(".flatpickr", {
        dateFormat: 'F j, Y'
    });

    flatpickr(".flatpickr_time", {
        enableTime: true,
        dateFormat: 'F j, Y h:i K'
    });

    $(".inputmask").inputmask();

    $('#cost-input, #total-input').on('input', calculateProfit);
    $('#cost-input2, #total-input2').on('input', calculateProfit2);

    if ($('.perfect-scrollbar').length) {
        new PerfectScrollbar('.perfect-scrollbar');
    }

    let care_of_select2 = $('.input_care_of').select2({
        dropdownParent: $('.bd-example-modal-lg'),
        width: "100%"
    });

    care_of_select2.on("select2:select", function (e) {
        $('#careof-bal-amt').html('');
        if (!isNaN(e.params.data.id) && e.params.data.id > 1) {
            $.ajax({
                url: 'get_collection_balance/' + e.params.data.id,
                method: 'get',
                dataType: 'json',
                success: function (response) {
                    $('#careof-bal-amt').html(response.message);
                }
            });
        }
    });

    let care_of_select2_2 = $('.input_care_of_2').select2({
        dropdownParent: $('#edit-ticket-modal'),
        width: "100%"
    });

    care_of_select2_2.on("select2:select", function (e) {
        $('#careof-bal-amt-2').html('');
        if (!isNaN(e.params.data.id) && e.params.data.id > 1) {
            $.ajax({
                url: 'get_collection_balance/' + e.params.data.id,
                method: 'get',
                dataType: 'json',
                success: function (response) {
                    $('#careof-bal-amt-2').html(response.message);
                }
            });
        }
    });

    $('.filter_dt[data-type="cre_dt"][data-filter-id="1"]').click();
    // loadTable()
});


function calculateProfit() {
    const cost = parseFloat($('#cost-input').val().replace('SAR ', '').replace(',', ''));
    const total = parseFloat($('#total-input').val().replace('SAR ', '').replace(',', ''));

    if (!isNaN(cost) && !isNaN(total)) {
        const profit = total - cost;

        $('#profit-output').val('SAR ' + profit.toFixed(2));
    } else {
        $('#profit-output').val('');
    }
}
function calculateProfit2() {
    const cost = parseFloat($('#cost-input2').val().replace('SAR ', '').replace(',', ''));
    const total = parseFloat($('#total-input2').val().replace('SAR ', '').replace(',', ''));

    if (!isNaN(cost) && !isNaN(total)) {
        const profit = total - cost;

        $('#profit-output2').val('SAR ' + profit.toFixed(2));
    } else {
        $('#profit-output2').val('');
    }
}

$("[name='filter_suppliers[]'],[name='filter_references[]']").on("change", function (e) {
    loadTable();
});

$('.filter_dt_date_range').change(function () {
    loadTable();
})

$('#print-report').on('click', () => {
    $.ajax({
        url: 'generate_report',
        method: 'GET',
        data: {
            referred_by: $('[name="filter_references[]"]').val(),
            suppliers: $('[name="filter_suppliers[]"]').val(),
            departure_at: $('.filter_dt[data-type="dep_dt"].btn-primary').data('filter-id'),
            departure_custom_date: $('.filter_dt_date_range[data-type="dep_dt"]').val(),
            created_at: $('.filter_dt[data-type="cre_dt"].btn-primary').data('filter-id'),
            created_custom_date: $('.filter_dt_date_range[data-type="cre_dt"]').val(),
        },
        // dataType: 'json',
        success: function (response) {
            var pdfUrl = response.url;

            // Display the PDF in a new window/tab
            window.open(pdfUrl, '_blank');
        }
    });
});
