<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:/xampp/htdocs/sms/vendor/autoload.php';

header('Content-Type: application/json');

// Initialize response
$response = array('success' => false, 'message' => '');

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientEmail = isset($_POST['recipientEmail']) ? trim($_POST['recipientEmail']) : '';
    $emailSubject = isset($_POST['emailSubject']) ? trim($_POST['emailSubject']) : '';
    $emailBody = isset($_POST['emailBody']) ? trim($_POST['emailBody']) : '';

    // Validate email
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid recipient email address';
        echo json_encode($response);
        exit();
    }

    // Check if PDF is uploaded
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $pdfOutput = file_get_contents($_FILES['pdf']['tmp_name']);
    } else {
        $response['message'] = 'PDF file is missing or could not be read. Error: ' . $_FILES['pdf']['error'];
        echo json_encode($response);
        exit();
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0; // Set to 2 for verbose debugging
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 't8415245@gmail.com'; // Replace with your email
        $mail->Password   = 'ywsgoymidwtmpnkf'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('t8415245@gmail.com', 'Test');
        $mail->addAddress($recipientEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $emailSubject;
        $mail->Body    = nl2br($emailBody);

        // Attach PDF
        $mail->addStringAttachment($pdfOutput, 'Return Order Details.pdf');

        $mail->send();
        $response['success'] = true;
        $response['message'] = 'Email sent successfully';
    } catch (Exception $e) {
        $response['message'] = 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    }

    // Send JSON response
    echo json_encode($response);
} else {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
}
?>
