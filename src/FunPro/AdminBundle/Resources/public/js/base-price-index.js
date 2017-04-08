var oTable;
var TableDatatablesResponsive = function () {

    var initTable = function () {
        var table = $('#sample_2');

        oTable = table.DataTable({
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
                {name: "b.id", data: "id", "defaultContent": "", orderable: true, searchable: false},
                {name: "b.entranceFee", data: "entranceFee", "defaultContent": "", orderable: true, searchable: true},
                {name: "b.costPerMeter", data: "costPerMeter", orderable: true, searchable: true},
                {name: "b.discountPercent", data: "discountPercent", "defaultContent": "", orderable: true, searchable: true},
                {name: "b.createdAt", data: "createdAt", "defaultContent": "", orderable: true, searchable: true},
            ],

            order: [4, 'desc'],

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

    $('#new-price').submit(function (event) {
        event.preventDefault();
        $.ajax({
            headers: {
                Accept: 'application/json'
            },
            method: 'post',
            data: $(this).serialize(),
            url: Routing.generate('fun_pro_admin_post_settings'),
            success: function () {
                alert('اطلاعات با موفقیت بروز شد');
                oTable.rows.add({
                    costPerMeter: $('input[name="base_cost[costPerMeter]"]').val(),
                    entranceFee: $('input[name="base_cost[entranceFee]"]').val(),
                    discountPercent: $('input[name="base_cost[discountPercent]"]').val(),
                    createdAt: new Date()
                }).draw();
                $('#new-price').reset();
            }
        });
    });
});