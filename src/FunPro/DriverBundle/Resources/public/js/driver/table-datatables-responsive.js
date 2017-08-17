var TableDatatablesResponsive = function () {

    var initTable = function () {
        var table = $('#sample_2');

        var oTable = table.dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "",

            // Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "No data available in table",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered1 from _MAX_ total entries)",
                "lengthMenu": "_MENU_ entries",
                "search": "Search:",
                "zeroRecords": "No matching records found"
            },

            // Or you can use remote translation file
            //"language": {
            //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
            //},

            // setup buttons extentension: http://datatables.net/extensions/buttons/
            buttons: [
                {extend: 'print', className: 'btn dark btn-outline'},
                {extend: 'pdf', className: 'btn green btn-outline'},
                {extend: 'csv', className: 'btn purple btn-outline '}
            ],

            // setup responsive extension: http://datatables.net/extensions/responsive/
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            columns: [
                {"defaultContent": "", name: "d.id", data: "id", orderable: true, searchable: false, className: "driverId"},
                {name: "d.contractNumber", data: "contractNumber", orderable: true, searchable: true},
                {name: "d.name", data: "name", orderable: true, searchable: true, className: 'driverName'},
                {name: "d.nationalCode", data: "nationalCode", orderable: true, searchable: true},
                {name: "d.mobile", data: "mobile", "defaultContent": "", orderable: true, searchable: true},
                {name: "d.age", data: "age", "defaultContent": "", orderable: true, searchable: false},
                {name: "d.sex", data: "sex", "defaultContent": "", orderable: true, searchable: false},
                {name: "d.credit", data: "credit", "defaultContent": 0, orderable: true, searchable: false, className: "credit"},
                {name: "d.description", data: "description", "defaultContent": "", orderable: false, searchable: false},
                {name: "d.avatar", data: "avatar", "defaultContent": "", orderable: false, searchable: false, className: "avatar"},
                {name: "delete", "defaultContent": "<i class='btn btn-danger'>حذف</i>", orderable: false, searchable: false, className: "delete text-center"},
                {name: "actions", "defaultContent": "", orderable: false, searchable: false, className: "actions"}
            ],

            order: [1, 'asc'],

            // pagination control
            "lengthMenu": [
                [5, 10, 15, 20, 50, 100],
                [5, 10, 15, 20, 50, 100] // change per page values here
            ],
            // set the initial value
            "pageLength": 10,
            "pagingType": 'bootstrap_extended', // pagination type

            "dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

            // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
            // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
            // So when dropdowns used the scrollable div should be removed.
            //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
        });
    };

    return {

        //main function to initiate the module
        init: function () {

            if (!jQuery().dataTable) {
                return;
            }

            initTable();
        }

    };

}();

jQuery(document).ready(function() {
    TableDatatablesResponsive.init();
    $('#sample_2').on( 'draw.dt', function () {
        $('.delete').on('click', function(event) {
            var driverName = $(this).parent().find('.driverName').text();
            if (!confirm(' نسبت به حذف راننده' + driverName + ' مطمین هستید؟')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            var that = this;
            $.ajax({
                type: 'delete',
                headers: {'content-type': 'application/json', accept: 'application/json'},
                url: Routing.generate('fun_pro_admin_delete_driver', {id: $(this).parent().find('.driverId').text()}),
                success: function (result) {
                    $(that).parent().slideUp();

                },
                error: function (xhr) {
                    if (xhr.status == 404) {
                        toastr.error('راننده وجود ندارد');
                    } else if (xhr.status == 401) {
                        document.location.href = Routing.generate('fun_pro_admin_login');
                    }
                }
            });
        });

        $(document).on('click', '.recover', function(event) {
            event.preventDefault();
            event.stopPropagation();
            var that = this;
            $.ajax({
                type: 'patch',
                headers: {'content-type': 'application/json', accept: 'application/json'},
                url: Routing.generate('fun_pro_admin_recover_driver', {id: $(this).parents('tr').find('.driverId').text()}),
                success: function (result) {
                    toastr.success('رمز جدید تنظیم شد');
                },
                statusCode: {
                    401: function () {
                        document.location.href = Routing.generate('fun_pro_admin_login');
                    },
                    404: function () {
                        toastr.error('راننده وجود ندارد');
                    }
                }
            });
        });

        $('td.avatar').each(function(index, item) {
            var avatar = $(item).text() ? $(item).text() : 'default_avatar.jpg';
            $(item).html('<img src="' + Routing.generate('liip_imagine_filter', {filter: 'panel_avatar_thumb', path: avatar}) + '" />');
        });

        $('td.actions').each(function(index, item) {
            var template = $('#actions').html();
            Mustache.parse(template);
            var rendered = Mustache.render(template);
            $(item).html(rendered);
        });

        $('a.carList').each(function(index, item) {
            $(this).attr('href', Routing.generate('fun_pro_admin_cget_driver_car', {driverId: $(this).parents('tr').find('.driverId').text()}));
        });

        $('a.edit').on('click', function() {
            $(this).attr('href', Routing.generate('fun_pro_admin_edit_driver', {id: $(this).parents('tr').find('.driverId').text()}));
        });

        $('a.withdraw').on('click', function() {
            event.preventDefault();
            event.stopPropagation();
            var that = this;
            $.ajax({
                type: 'post',
                headers: {'content-type': 'application/json', accept: 'application/json'},
                url: Routing.generate('fun_pro_admin_post_driver_withdraw', {id: $(this).parents('tr').find('.driverId').text()}),
                success: function (result) {
                    toastr.success('تراکنش با موفقیت ثبت شد');
                    $(that).parents('tr').find('.credit').text(result.value);
                },
                error: function (xhr) {
                    if (xhr.status == 400) {
                        toastr.error('اعتبار صفر است');
                    } else if (xhr.status == 404) {
                        toastr.error('راننده وجود ندارد');
                    } else if (xhr.status == 400) {
                        document.location.href = Routing.generate('fun_pro_admin_login');
                    }
                }
            });
        });
    });
});