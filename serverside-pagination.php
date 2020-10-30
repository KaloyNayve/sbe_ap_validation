<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Jquery Datatable Example</title>
  <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
<link rel='stylesheet prefetch' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.0/bootstrap-table.min.css'>
<link rel='stylesheet prefetch' href='https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css'>
      <!-- <link rel="stylesheet" href="css/style.css"> -->
</head>
<body>
    <table id="example" class="display" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Company</th>
                <th>Document Type</th>
                <th>Supplier</th>
                <th>Invoice Date</th>
                <th>notes/comments</th>
                <th>Subtotal</th> 
                <th>GST/HST</th>
                <th>Grandtotal</th>                      
                <th>Attached Docs</th> 
            </tr>
        </thead>
    </table>

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js'></script>
<script src='https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js'></script>
<script>
$(document).ready(function () {

    var url = 'https://www.json-generator.com/api/json/get/cbEfqLwFaq?indent=2';

    var table = $('#example').DataTable({
        dom: "Bfrtip",
        paging: true,
        pageLength: 15,
        ajax: function ( data, callback, settings ) {
            console.log(data);
            $.ajax({
                url: 'actions-dev.php',
                // dataType: 'text',
                type: 'post',
                contentType: 'application/x-www-form-urlencoded',
                data: {
                    "get_documents_in_flow" : "1",
                    RecordsStart: data.start,
                    PageSize: data.length
                },
                success: function( data, textStatus, jQxhr ){
                    console.log(data);
                    // callback({
                    //     // draw: data.draw,
                    //     data: data.Data,
                    //     recordsTotal:  data.TotalRecords,
                    //     recordsFiltered:  data.RecordsFiltered
                    // });
                },
                error: function( jqXhr, textStatus, errorThrown ){
                }
            });
        },
        serverSide: true,
        columns: [
            { data: "Invoice Number" },
            { data: "Document Type" },
            { data: "Supplier" },
            { data: "Invoice Date" },
            { data: "notes/comments" },
            { data: "Subtotal"},
            { data: "GST/HST"},
            { data: "Grandtotal"},
            { data: "Attached Docs"}
        ]

    });

});


</script>
</body>

</html>