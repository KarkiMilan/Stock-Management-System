<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php 
$qry = $conn->query("SELECT p.*,s.name as supplier FROM purchase_order_list p inner join supplier_list s on p.supplier_id = s.id  where p.id = '{$_GET['id']}'");
if($qry->num_rows >0){
    foreach($qry->fetch_array() as $k => $v){
        $$k = $v;
    }
}
?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">Purchase Order Details - <?php echo $po_code ?></h4>
    </div>
    <div class="card-body" id="print_out">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label text-info">P.O. Code</label>
                    <div><?php echo isset($po_code) ? $po_code : '' ?></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="supplier_id" class="control-label text-info">Supplier</label>
                        <div><?php echo isset($supplier) ? $supplier : '' ?></div>
                    </div>
                </div>
            </div>
            <h4 class="text-info">Orders</h4>
            <table class="table table-striped table-bordered" id="list">
                <colgroup>
                    <col width="10%">
                    <col width="10%">
                    <col width="30%">
                    <col width="25%">
                    <col width="25%">
                </colgroup>
                <thead>
                    <tr class="text-light bg-navy">
                        <th class="text-center py-1 px-2">Qty</th>
                        <th class="text-center py-1 px-2">Unit</th>
                        <th class="text-center py-1 px-2">Item</th>
                        <th class="text-center py-1 px-2">Cost</th>
                        <th class="text-center py-1 px-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    $qry = $conn->query("SELECT p.*,i.name,i.description FROM `po_items` p inner join item_list i on p.item_id = i.id where p.po_id = '{$id}'");
                    while($row = $qry->fetch_assoc()):
                        $total += $row['total']
                    ?>
                    <tr>
                        <td class="py-1 px-2 text-center"><?php echo number_format($row['quantity'],2) ?></td>
                        <td class="py-1 px-2 text-center"><?php echo ($row['unit']) ?></td>
                        <td class="py-1 px-2">
                            <?php echo $row['name'] ?> <br>
                            <?php echo $row['description'] ?>
                        </td>
                        <td class="py-1 px-2 text-right"><?php echo number_format($row['price']) ?></td>
                        <td class="py-1 px-2 text-right"><?php echo number_format($row['total']) ?></td>
                    </tr>

                    <?php endwhile; ?>
                    
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="4">Sub Total</th>
                        <th class="text-right py-1 px-2 sub-total"><?php echo number_format($total,2)  ?></th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="4">Discount <?php echo isset($discount_perc) ? $discount_perc : 0 ?>%</th>
                        <th class="text-right py-1 px-2 discount"><?php echo isset($discount) ? number_format($discount,2) : 0 ?></th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="4">Tax <?php echo isset($tax_perc) ? $tax_perc : 0 ?>%</th>
                        <th class="text-right py-1 px-2 tax"><?php echo isset($tax) ? number_format($tax,2) : 0 ?></th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="4">Total</th>
                        <th class="text-right py-1 px-2 grand-total"><?php echo isset($amount) ? number_format($amount,2) : 0 ?></th>
                    </tr>
                </tfoot>
            </table>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="remarks" class="text-info control-label">Remarks</label>
                        <p><?php echo isset($remarks) ? $remarks : '' ?></p>
                    </div>
                </div>
                <?php if($status > 0): ?>
                <div class="col-md-6">
                    <span class="text-info"><?php echo ($status == 2)? "RECEIVED" : "PARTIALLY RECEIVED" ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-footer py-1 text-center">
    
    
    <button class="btn btn-flat btn-secondary" type="button" id="pdf">    <i class="fas fa-file-pdf"></i> 
    PDF</button>
<button class="btn btn-flat btn-success" type="button" id="excel"> <i class="fas fa-file-excel"></i> Excel</button>
<!-- Button to trigger the modal -->
<button class="btn btn-primary" type="button" id="emailButton" data-bs-toggle="modal" data-bs-target="#emailModal"> <i class="fas fa-envelope"></i>
        Send Email
    </button>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Send Test Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form id="emailForm" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="recipientEmail" class="form-label">Recipient Email</label>
        <input type="email" class="form-control" id="recipientEmail" name="recipientEmail" required>
    </div>
    <div class="mb-3">
        <label for="emailSubject" class="form-label">Subject</label>
        <input type="text" class="form-control" id="emailSubject" name="emailSubject" value="Purchase Order Report" required>
    </div>
    <div class="mb-3">
        <label for="emailBody" class="form-label">Message</label>
        <textarea class="form-control" id="emailBody" name="emailBody" rows="3" required>Please Find The Attachment For The Purchase Order Report</textarea>
    </div>
    <button type="submit" class="btn btn-white">
    <i class="fas fa-envelope"></i> Send Email
</button>
</form>

                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-flat btn-light" type="button" id="print">
    <i class="fas fa-print"></i> Print
</button>
<a class="btn btn-flat btn-info"  href="<?php echo base_url.'/admin?page=purchase_order/manage_po&id='.(isset($id) ? $id : '') ?>">
    <i class="fas fa-edit"></i> Edit
</a>       
 <a class="btn btn-flat btn-dark" href="<?php echo base_url.'/admin?page=purchase_order' ?>">
 <i class="fas fa-list"></i>
 Back To List</a>
    </div>
</div>
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 text-center qty">
            <span class="visible"></span>
            <input type="hidden" name="item_id[]">
            <input type="hidden" name="unit[]">
            <input type="hidden" name="qty[]">
            <input type="hidden" name="price[]">
            <input type="hidden" name="total[]">
        </td>
        <td class="py-1 px-2 text-center unit">
        </td>
        <td class="py-1 px-2 item">
        </td>
        <td class="py-1 px-2 text-right cost">
        </td>
        <td class="py-1 px-2 text-right total">
        </td>
    </tr>
</table>
<script>
    
    $(function(){
        $('#print').click(function(){
            start_loader()
            var _el = $('<div>')
            var _head = $('head').clone()
                _head.find('title').text("Purchase Order Details - Print View")
            var p = $('#print_out').clone()
            p.find('tr.text-light').removeClass("text-light bg-navy")
            _el.append(_head)
            _el.append('<div class="d-flex justify-content-center">'+
                      '<div class="col-1 text-right">'+
                      '<img src="<?php echo validate_image($_settings->info('logo')) ?>" width="65px" height="65px" />'+
                      '</div>'+
                      '<div class="col-10">'+
                      '<h4 class="text-center"><?php echo $_settings->info('name') ?></h4>'+
                      '<h4 class="text-center">Purchase Order</h4>'+
                      '</div>'+
                      '<div class="col-1 text-right">'+
                      '</div>'+
                      '</div><hr/>')
            _el.append(p.html())
            var nw = window.open("","","width=1200,height=900,left=250,location=no,titlebar=yes")
                     nw.document.write(_el.html())
                     nw.document.close()
                     setTimeout(() => {
                         nw.print()
                         setTimeout(() => {
                            nw.close()
                            end_loader()
                         }, 200);
                     }, 500);
        })
    })

    $(function(){
    $('#pdf').click(function(){
        start_loader()
        var _el = $('<div>')
        var p = $('#print_out').clone()
        p.find('tr.text-light').removeClass("text-light bg-navy")
        _el.append('<div class="d-flex justify-content-center">'+
                  '<div class="col-1 text-right">'+
                  '<img src="<?php echo validate_image($_settings->info('logo')) ?>" width="65px" height="65px" />'+
                  '</div>'+
                  '<div class="col-10">'+
                  '<h2 class="text-center"><?php echo $_settings->info('name') ?></h2>'+
                  '<h3 class="text-center">Purchase Order</h3>'+
                  '</div>'+
                  '<div class="col-1 text-right">'+
                  '</div>'+
                  '</div><hr/>')
                  
        _el.append(p.html())
        var opt = {
            margin: [0.5, 0.5, 0.5, 0.5], // set margins to 0.5 inches
            filename: 'purchase_order.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        // adjust font sizes and spacing
        _el.find('h2').css('font-size', '28px');
        _el.find('h3').css('font-size', '24px');
        _el.find('table').css('font-size', '16px');
        _el.find('table').css('line-height', '1.5');
        _el.find('table').css('margin-bottom', '20px');

        // remove white lines
        _el.find('.table-bordered td').css('border', 'none');
        _el.find('.table-bordered th').css('border', 'none');

        // adjust table width and height
        _el.find('table').css('width', '100%');
        _el.find('table').css('height', 'auto');

        // increase display size to bottom of the page
        _el.css('height', '100%');
        _el.css('display', 'flex');
        _el.css('flex-direction', 'column');
        _el.css('justify-content', 'flex-end');

        html2pdf().set(opt).from(_el.html()).save()
        end_loader()
    })
})
$(document).ready(function() {
    $('#emailButton').click(function() {
        $('#emailModal').modal('show');
    });

    $('#emailForm').submit(function(e) {
        e.preventDefault();

        // Start loader (for visual feedback)
        start_loader();

        // Generate the PDF content
        var _el = $('<div>');
        var p = $('#print_out').clone();
        p.find('tr.text-light').removeClass("text-light bg-navy");
        _el.append('<div class="d-flex justify-content-center">'+
                  '<div class="col-1 text-right">'+
                  '<img src="<?php echo validate_image($_settings->info('logo')) ?>" width="65px" height="65px" />'+
                  '</div>'+
                  '<div class="col-10">'+
                  '<h2 class="text-center"><?php echo $_settings->info('name') ?></h2>'+
                  '<h3 class="text-center">Purchase Order</h3>'+
                  '</div>'+
                  '<div class="col-1 text-right">'+
                  '</div>'+
                  '</div><hr/>');

        _el.append(p.html());

        // Ensure the HTML content is correctly available
        var contentHtml = _el.html();
        if (!contentHtml) {
            console.error('No content to generate PDF.');
            end_loader();
            return;
        }

        var opt = {
            margin: [0.5, 0.5, 0.5, 0.5],
            filename: 'purchase_order.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(contentHtml).toPdf().get('pdf').then(function(pdf) {
            var pdfOutput = pdf.output('blob');
            var formData = new FormData($('#emailForm')[0]);
            formData.append('pdf', pdfOutput, 'purchase_order.pdf');

            // Debugging
            console.log('FormData content:', ...formData.entries()); // Log FormData content

            // Send data via AJAX
            $.ajax({
                url: '/sms/admin/purchase_order/send_mail.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log('Response from server:', response);
                    if (response.success) {
                        alert('Email sent successfully');
                        $('#emailModal').modal('hide');
                    } else {
                        alert('Failed to send email: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to send email:', error);
                    console.log('Response text:', xhr.responseText); // Log response text
                    alert('Failed to send email. See console for details.');
                }
            }).always(function() {
                end_loader(); // Stop loader regardless of success or failure
            });
        }).catch(function(err) {
            console.error('Error generating PDF:', err);
            end_loader(); // Ensure loader stops if there's an error
        });
    });
});
$(function(){
    $('#excel').click(function(){
        start_loader()
        var table = $('#print_out').clone()
        table.find('tr.text-light').removeClass("text-light bg-navy")
        var html = table.html()
        
        // add header with logo and company name
        var _head = $('head').clone()
        _head.find('title').text("Purchase Order Details - Excel Export")
        var header = $('<div>')
        header.append(_head)
        header.append('<div class="d-flex justify-content-center">'+
                      '<div class="col-1 text-right">'+
                      '</div>'+
                      '<div class="col-10">'+
                      '<h4 class="text-center"><?php echo $_settings->info('name') ?></h4>'+
                      '<h4 class="text-center">Purchase Order</h4>'+
                      '</div>'+
                      '<div class="col-1 text-right">'+
                      '</div>'+
                      '</div><hr/>')
        html = header.html() + html
        
        var uri = 'data:application/vnd.ms-excel;base64,'
        var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"><title>Excel Export</title></head><body><table>{table}</table></body></html>'
        var base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
        var format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
        var ctx = {worksheet: 'Purchase Order', table: html}
        var link = document.createElement("a")
        link.download = "purchase_order.xls"
        link.href = uri + base64(format(template, ctx))
        link.click()
        end_loader()
    })
})
    </script>
