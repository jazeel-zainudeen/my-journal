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
                created_custom_date: $('.filter_dt_date_range[data-type="cre_dt"]').val()
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
            { data: 'balance_amount' },
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
            $('.mark-collected-action').click(function () {
                let pending_balance = $(this).data('balance');
                let ticket_id = $(this).data('ticket-id');
                Swal.fire({
                    title: 'Collect Balance<br><p class="fs-6 mt-2">(Pending: ' + formatCurrency(pending_balance) + ')</p>',
                    input: 'text',
                    inputAttributes: {
                        'data-inputmask': "'alias': 'currency', 'prefix':'SAR '",
                        autocapitalize: 'off'
                    },
                    didOpen: () => {
                        $(".swal2-input").val(pending_balance);
                        $(".swal2-input").inputmask();
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Mark Collected',
                    showLoaderOnConfirm: true,
                    preConfirm: (value) => {
                        value = value.replace(/[^0-9.-]+/g, "");

                        if (pending_balance < 0) {
                            if (parseFloat(value) < pending_balance) {
                                Swal.showValidationMessage('The entered value is less than the balance.');
                                return false;
                            }

                            if (parseFloat(value) > 0){
                                Swal.showValidationMessage('The entered value exceeds the maximum balance.');
                                return false;
                            }
                        } else {
                            if (parseFloat(value) > pending_balance) {
                                Swal.showValidationMessage('The entered value exceeds the maximum balance.');
                                return false;
                            }
                        }

                        if (value == 0) {
                            Swal.showValidationMessage('The entered value is invalid.');
                            return false;
                        }
                        return fetch(`/collect/${ticket_id}?amount=${value}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(response.statusText)
                                }

                                return response.json()
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error}`
                                )
                            })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                })
            });

            $('.refund-link').on('click', function (e) {
                let ticket_id = $(this).data('ticket-id');
                Swal.fire({
                    title: 'Refund Ticket',
                    text: "Are you sure you want to refund this ticket?",
                    input: 'text',
                    inputLabel: 'Extra Charges:',
                    inputAttributes: {
                        'data-inputmask': "'alias': 'currency', 'prefix':'SAR '",
                        autocapitalize: 'off'
                    },
                    didOpen: () => {
                        $(".swal2-input").inputmask();
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Mark Refunded!',
                    showLoaderOnConfirm: true,
                    preConfirm: (value) => {
                        value = value.replace(/[^0-9.-]+/g, "");

                        if (!value)
                            value = 0;

                        return fetch(`/mark_refunded/${ticket_id}?amount=${value}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(response.statusText)
                                }

                                return response.json()
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error}`
                                )
                            })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                })
            });
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
                            dateFormat: 'F j, Y'
                        });
                        flatpickrInstance1.setDate(response.created_at, true);

                        var flatpickrInstance2 = flatpickr('#edit-ticket-modal [name="departure_date"]', {
                            dateFormat: 'F j, Y'
                        });
                        flatpickrInstance2.setDate(response.departure_date, true);

                        var flatpickrInstance3 = flatpickr('#edit-ticket-modal [name="return_date"]', {
                            dateFormat: 'F j, Y'
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

                        let pendingHTML = '';
                        let balance = response.total - response.collection_amount;
                        if (balance > 0 && response.collection_amount < response.total) {
                            pendingHTML = 'Balance: SAR ' + balance.toFixed(2);
                        }
                        $('#edit-ticket-modal .pending-collection').html(pendingHTML);

                        $('#edit-ticket-modal').modal('show');
                    }
                });
            })
        },
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;

            let costTotal = 0.0, collectionTotal = 0.0, grossTotal = 0.0, profitTotal = 0.0, pendingTotal = 0.0;
            $.each(data, function (i, value) {
                collectionTotal += parseFloat(value.collect_amount);
                costTotal += parseFloat(value.cost_amount);
                grossTotal += parseFloat(value.total_amount);
                profitTotal += parseFloat(value.profit_amount);
                pendingTotal += parseFloat(value.balance_amt);
            })

            $(api.column(7).footer()).html(formatCurrency(costTotal));
            $(api.column(8).footer()).html(formatCurrency(profitTotal));
            $(api.column(9).footer()).html(formatCurrency(grossTotal));
            $(api.column(10).footer()).html(formatCurrency(collectionTotal));
            $(api.column(11).footer()).html(formatCurrency(pendingTotal));
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
    setFilterSessions();
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

    $('input[name="collection_amount"],input[name="total"]').on('input', () => {
        let elem = document.activeElement;
        let form = elem.closest('form');

        let total = parseFloat($('input[name=total]', form).val().replace('SAR ', '').replace(',', ''));
        total = isNaN(total) ? 0 : total;

        let collection_amount = parseFloat($('input[name=collection_amount]', form).val().replace('SAR ', '').replace(',', ''));
        collection_amount = isNaN(collection_amount) ? 0 : collection_amount;

        let html = '';
        let balance = total - collection_amount;
        if (balance > 0 && collection_amount < total) {
            html = 'Balance: SAR ' + balance.toFixed(2);
        }

        if (collection_amount > total) {
            $('input[name=collection_amount]', form).addClass('is-invalid');
        } else {
            $('input[name=collection_amount]', form).removeClass('is-invalid');
        }

        $('.pending-collection', form).html(html);
    });
});


function calculateProfit() {
    let cost = parseFloat($('#cost-input').val().replace('SAR ', '').replace(',', ''));
    let total = parseFloat($('#total-input').val().replace('SAR ', '').replace(',', ''));

    if (isNaN(cost))
        cost = 0;

    if (isNaN(total))
        total = 0;

    let profit = total - cost;
    $('#profit-output').val('SAR ' + profit.toFixed(2));
}
function calculateProfit2() {
    let cost = parseFloat($('#cost-input2').val().replace('SAR ', '').replace(',', ''));
    let total = parseFloat($('#total-input2').val().replace('SAR ', '').replace(',', ''));

    if (isNaN(cost))
        cost = 0;

    if (isNaN(total))
        total = 0;

    let profit = total - cost;
    $('#profit-output2').val('SAR ' + profit.toFixed(2));
}

$("[name='filter_suppliers[]']").on("change", function (e) {
    loadTable();
    setFilterSessions()
});

$("[name='filter_references[]']").on("change", function (e) {
    if ($(this).val().length == 1 && $(this).val()[0] != 1) {
        $("#bulk-collect").removeClass('disabled');
    } else {
        $("#bulk-collect").addClass('disabled');
    }
    loadTable();
    setFilterSessions()
});

$('.filter_dt_date_range').change(function () {
    loadTable();
    setFilterSessions()
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
        beforeSend: function () {
            $('#action-btn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actions')
        },
        complete: function () {
            $('#action-btn').html('Actions')
        },
        success: function (response) {
            var pdfUrl = response.url;
            window.open(pdfUrl, '_blank');
        }
    });
});

function setFilterSessions() {
    let filters = {
        referred_by: $('[name="filter_references[]"]').val(),
        suppliers: $('[name="filter_suppliers[]"]').val(),
        departure_at: $('.filter_dt[data-type="dep_dt"].btn-primary').data('filter-id'),
        departure_custom_date: $('.filter_dt_date_range[data-type="dep_dt"]').val(),
        created_at: $('.filter_dt[data-type="cre_dt"].btn-primary').data('filter-id'),
        created_custom_date: $('.filter_dt_date_range[data-type="cre_dt"]').val()
    };
    sessionStorage.setItem('filters', JSON.stringify(filters));
}


$(function ($) {
    'use strict';
    let filters = JSON.parse(sessionStorage.getItem('filters'));
    if (filters) {
        if (filters.referred_by) {
            if (filters.referred_by.length != 0)
                $('[name="filter_references[]"]').val(filters.referred_by).trigger('change');
        }


        if (filters.suppliers) {
            if (filters.suppliers.length != 0)
                $('[name="filter_suppliers[]"]').val(filters.suppliers).trigger('change');
        }


        if (filters.departure_at) {
            $('.filter_dt[data-type="dep_dt"][data-filter-id="' + filters.departure_at + '"]').click();
            if (filters.departure_at == 4) {
                $('.filter_dt_date_range[data-type="dep_dt"]').val(filters.departure_custom_date)
            }
        }

        if (filters.created_at) {
            $('.filter_dt[data-type="cre_dt"][data-filter-id="' + filters.created_at + '"]').click();
            if (filters.created_at == 4) {
                $('.filter_dt_date_range[data-type="cre_dt"]').val(filters.created_custom_date)
            }
        }
    }

    if (!$.fn.DataTable.isDataTable('#dataTableExample')) {
        loadTable();
    }
});

$('#bulk-collect').on('click', () => {
    let reference_id = $('[name="filter_references[]"]').val()[0];
    $.ajax({
        url: '/list_ticket_by_reference/' + reference_id,
        method: 'get',
        success: function (response) {
            if (response.length == 0) {
                return false;
            }

            let swal_html = `<div class="table-responsive">
                        <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Balance</th>
                                <th>Collection Amount</th>
                            </tr>
                        </thead>
                        <tbody>`;

            let total = 0;
            response.forEach(row => {
                let balance = row.total - row.collection_amount;
                swal_html += `<tr>
                    <td>${row.customer_name}</td>
                    <td>${balance} of ${row.total}</td>
                    <td><input type="text" class="form-control form-control-xs swal-custom-input" data-inputmask="'alias': 'currency', 'prefix':'SAR '" data-id="${row.id}" data-balance="${balance}" data-name="${row.customer_name}"></td>
                </tr>`;
                total += balance;
            });

            swal_html += `</tbody></table></div>`;
            let swal_title = 'Collect Balance<br><p class="fs-6 mt-2">(Pending: ' + formatCurrency(total) + ')</p>';

            Swal.fire({
                title: swal_title,
                input: 'text',
                inputAttributes: {
                    'data-inputmask': "'alias': 'currency', 'prefix':'SAR '",
                    autocapitalize: 'off'
                },
                didOpen: () => {
                    $(".swal2-input").inputmask();
                    $(".swal-custom-input").inputmask();
                    $('.swal2-input').on('input', () => {
                        let totalSettlement = $('.swal2-input').val();
                        totalSettlement = totalSettlement.replace(/[^0-9.-]+/g, "");

                        let inputFields = $('.swal-custom-input');
                        let remainingBalance = totalSettlement;
                        inputFields.each(function (index) {
                            let balance = parseFloat($(this).data('balance'));
                            let amountToFill = Math.min(balance, remainingBalance);
                            $(this).val(amountToFill.toFixed(2));
                            remainingBalance -= amountToFill;
                        });
                    })

                    $('.swal-custom-input').on('input', () => {
                        let inputFields = $('.swal-custom-input');
                        let sum = 0;
                        inputFields.each(function () {
                            let value = $(this).val().replace(/[^0-9.-]+/g, "");
                            if (value)
                                sum += parseFloat(value);
                        });

                        $('.swal2-input').val(sum.toFixed(2));
                    });
                },
                showCancelButton: true,
                confirmButtonText: 'Collect Amount',
                showLoaderOnConfirm: true,
                html: swal_html,
                preConfirm: (value) => {
                    value = value.replace(/[^0-9.-]+/g, "");

                    let inputFields = $('.swal-custom-input');
                    let flag = false;
                    let flag_name = '';
                    inputFields.each(function () {
                        let input = parseFloat($(this).val().replace(/[^0-9.-]+/g, ""));
                        let balance = parseFloat($(this).data('balance'));
                        if (input > balance) {
                            flag = true;
                            flag_name = $(this).data('name');
                            return false;
                        }
                    });

                    if (flag) {
                        Swal.showValidationMessage('Invalid Collection Amount for&nbsp;<b>' + flag_name + '</b>');
                        return false;
                    }

                    if (parseFloat(value) > total) {
                        Swal.showValidationMessage('The entered value exceeds the maximum balance.');
                        return false;
                    }

                    if (value == 0) {
                        Swal.showValidationMessage('Please enter a collection amount.');
                        return false;
                    }

                    var params = new URLSearchParams();
                    $('.swal-custom-input').each(function () {
                        var dataId = $(this).data('id');
                        var amount = $(this).val().replace(/[^0-9.-]+/g, "");
                        if (amount != 0) {
                            params.append(`collections[${dataId}]`, amount);
                        }
                    });

                    return fetch(`/bulk_collect/${reference_id}?${params.toString()}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }

                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            })

        }
    })
});

function formatCurrency(value) {
    const formattedValue = Math.abs(value).toLocaleString('en-SA', {
        style: 'currency',
        currency: 'SAR',
    });

    const symbol = 'SAR';
    const valueWithoutSymbol = formattedValue.replace(symbol, '').trim();

    return value < 0 ? `${symbol} -${valueWithoutSymbol}` : `${symbol} ${valueWithoutSymbol}`;
}
