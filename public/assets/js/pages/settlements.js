function loadTable() {
    $('#dataTableExample').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        autoWidth: true,
        stateSave: true,
        ajax: '/list_settlements',
        columns: [
            { data: 'name' },
            { data: 'total_balance' },
            { data: 'pending_balance' },
            { data: 'settled_balance' },
            { data: 'action' },
        ],
        columnDefs: [{
            "targets": "_all"
        },
        {
            "targets": [1, 2, 3, 4],
            "className": "text-center",
            "width": "1%",
            "searchable": false
        }],
        drawCallback: function (settings) {
            $('.settle-btn').click(function (e) {
                let pending_balance = $(this).data('sbal');
                let supplier_id = $(this).data('id');
                let swal_title = 'Settle Balance<br><p class="fs-6 mt-2">(Pending: ' + pending_balance.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }) + ')</p>';

                $.ajax({
                    url: '/list_unsettled_tickets/' + supplier_id,
                    method: 'get',
                    success: function (response) {
                        // if (response.length == 0) {

                        //     return false;
                        // }

                        let swal_html = `<div class="table-responsive">
                        <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Balance</th>
                                <th>Settlement Amount</th>
                            </tr>
                        </thead>
                        <tbody>`;

                        response.forEach(row => {
                            swal_html += `<tr>
                                <td>${row.customer_name}</td>
                                <td>${row.balance} of ${row.cost}</td>
                                <td><input type="text" class="form-control form-control-xs swal-custom-input" data-inputmask="'alias': 'currency', 'prefix':'SAR '" data-id="${row.id}" data-balance="${row.balance}" data-name="${row.customer_name}"></td>
                            </tr>`;
                        });

                        swal_html += `</tbody></table></div>`;

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
                            confirmButtonText: 'Settle Amount',
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
                                    Swal.showValidationMessage('Invalid Settlement Amount for&nbsp;<b>' + flag_name + '</b>');
                                    return false;
                                }

                                if (parseFloat(value) > pending_balance) {
                                    Swal.showValidationMessage('The entered value exceeds the maximum balance.');
                                    return false;
                                }

                                if (value == 0) {
                                    Swal.showValidationMessage('Please enter a settlement amount.');
                                    return false;
                                }

                                var params = new URLSearchParams();
                                params.append('settlement_amount', value);
                                $('.swal-custom-input').each(function () {
                                    var dataId = $(this).data('id');
                                    var amount = $(this).val().replace(/[^0-9.-]+/g, "");
                                    if (amount != 0) {
                                        params.append(`settlements[${dataId}]`, amount);
                                    }
                                });

                                return fetch(`/settle_balance/${supplier_id}?${params.toString()}`)
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
                                loadTable();
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                });

                                let pending_balance = result.value.total_payable;
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Settlement successfully marked. Pending balance: ' + pending_balance.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' })
                                })
                            }
                        })
                    }
                })

            })

            $('.view-history-btn').click(function () {
                let supplier_id = $(this).data('id');
                $('#history-datatable').DataTable({
                    destroy: true,
                    processing: true,
                    serverSide: true,
                    autoWidth: true,
                    ajax: '/list_transactions/' + supplier_id,
                    columns: [
                        { data: 'ticket.customer_name' },
                        { data: 'amount' },
                        { data: 'created_at' }
                    ],
                    columnDefs: [{
                        "targets": 1,
                        "width": "1%",
                        "searchable": false
                    }],
                    order: []
                });
                $('#view-history-modal').modal('show');
            })

            $('.view-tickets-btn').click(function () {
                let supplier_id = $(this).data('id');
                sessionStorage.clear();

                let filters = {
                    suppliers: [supplier_id]
                };
                sessionStorage.setItem('filters', JSON.stringify(filters));

                window.location.href = '/';
            })
        },
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;

            let grossTotal = 0.0, pendingTotal = 0.0, settledTotal = 0.0;
            $.each(data, function (i, value) {
                grossTotal += parseFloat(value.total_balance_amt);
                pendingTotal += parseFloat(value.pending_balance_amt);
                settledTotal += parseFloat(value.settled_balance_amt);
            })

            $(api.column(1).footer()).html(grossTotal.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }));
            $(api.column(2).footer()).html(pendingTotal.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }));
            $(api.column(3).footer()).html(settledTotal.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }));
        }
    });
}

$(function ($) {
    'use strict';

    loadTable();

    flatpickr(".flatpickr-input", {
        dateFormat: 'F j, Y',
        mode: 'range'
    });
});

