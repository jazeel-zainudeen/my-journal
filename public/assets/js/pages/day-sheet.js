$(function () {
    'use strict';

    if ($("#easyMdeExample").length) {
        var easymde = new EasyMDE({
            element: $("#easyMdeExample")[0]
        });
        $('.daysheet-item').click(function () {
            $('#daysheet-form [name="title"]').val('');
            easymde.value('');

            let id = $(this).data('id');
            $('#daysheet-form [name="id"]').val(id);
            $('.delete-daysheet').hide();
            $.ajax({
                url: 'get-daysheet/' + id,
                method: 'get',
                dataType: 'json',
                success: function (response) {
                    $('#daysheet-form [name="title"]').val(response.title);
                    if (response.data) {
                        easymde.value(response.data);
                    } else {
                        easymde.value('');
                    }
                    $('.delete-daysheet').show();
                    $('.delete-daysheet').attr('data-id', id);
                }
            })
        })

        $('.add-daysheet').on('click', function () {
            $('.delete-daysheet').attr('data-id', 0);
            $('.delete-daysheet').hide();
            $('.chat-content').toggleClass('show');
            $('#daysheet-form [name="id"]').val('');
            $('#daysheet-form').trigger("reset");
            easymde.value('');
        })
    }
});

$('.delete-daysheet').click(function (e) {
    e.preventDefault();
    window.location.href = 'daysheet/delete/' + $(this).attr('data-id');
})
