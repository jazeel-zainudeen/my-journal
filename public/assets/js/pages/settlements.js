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

                Swal.fire({
                    title: 'Settle Balance<br><p class="fs-6 mt-2">(Pending: ' + pending_balance.toLocaleString('en-SA', { style: 'currency', currency: 'SAR' }) + ')</p>',
                    input: 'text',
                    inputAttributes: {
                        'data-inputmask': "'alias': 'currency', 'prefix':'SAR '",
                        autocapitalize: 'off'
                    },
                    didOpen: () => {
                        $(".swal2-input").inputmask();
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Settle Amount',
                    showLoaderOnConfirm: true,
                    preConfirm: (value) => {
                        value = value.replace(/[^0-9.-]+/g, "");

                        if (parseFloat(value) > pending_balance) {
                            Swal.showValidationMessage('The entered value exceeds the maximum balance.');
                            return false;
                        }

                        if (value == 0) {
                            Swal.showValidationMessage('The entered value is invalid.');
                            return false;
                        }

                        return fetch(`/settle_balance/${supplier_id}?settlement_amount=${value}`)
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
                        { data: 'amount' },
                        { data: 'created_at' }
                    ],
                    columnDefs: [{
                        "targets": 1,
                        "width": "1%",
                        "searchable": false
                    }],
                    order: [
                        [1, 'desc']
                    ]
                });
                $('#view-history-modal').modal('show');
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
});

