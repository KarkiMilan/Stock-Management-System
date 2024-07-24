<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:/xampp/htdocs/sms/vendor/autoload.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data from POST request
    $recipientEmail = isset($_POST['recipientEmail']) ? trim($_POST['recipientEmail']) : '';
    $emailSubject = isset($_POST['emailSubject']) ? trim($_POST['emailSubject']) : '';
    $emailBody = isset($_POST['emailBody']) ? trim($_POST['emailBody']) : '';

    // Validate and sanitize input data
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid recipient email address';
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

        $mail->send();
        $response['success'] = true;
        $response['message'] = 'Email sent successfully';
    } catch (Exception $e) {
        $response['message'] = 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    }

    echo json_encode($response);
} else {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
}
?>
