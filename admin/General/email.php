<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

    <!-- Directly Displayed Email Form -->
    <div class="container mt-5">
        <h2>Bulk Email Service</h2>
        <form id="emailForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="recipientEmail" class="form-label">Recipient Email</label>
                <input type="email" class="form-control" id="recipientEmail" name="recipientEmail" required>

            </div>
            <div class="mb-3">
                <label for="emailSubject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="emailSubject" name="emailSubject"   placeholder="Enter Your Subject" value="" required>
            </div>
            <div class="mb-3">
                <label for="emailBody" class="form-label">Message</label>
                <textarea class="form-control" id="emailBody" name="emailBody"  rows="3" required> </textarea>
            </div>
            <div class="mb-3">
                <label for="attachments" class="form-label">Attachments</label>
                <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
            </div>
            <button type="submit" class="btn btn-primary">Send Email</button>
        </form>
    </div>

    <script>
      $(document).ready(function() {
        $('#emailForm').submit(function(e) {
            e.preventDefault();

            // Create FormData object
            var formData = new FormData(this);

            // Send data via AJAX
            $.ajax({
                url: '/sms/admin/General/bulk_email.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    console.log('Response from server:', response); // Log response for debugging
                    if (response.success) {
                        alert('Email sent successfully');
                        // Clear form values
                        $('#emailForm')[0].reset();
                    } else {
                        alert('Failed to send email: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to send email:', error);
                    alert('Failed to send email. See console for details.');
                }
            });
        });
      });
    </script>

</body>
</html>
