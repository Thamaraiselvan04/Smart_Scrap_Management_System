<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
// Load PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('admin/includes/dbconnection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Save to database
    $sql = "INSERT INTO tbmail (Name, Email, Subject, Message) VALUES (?, ?, ?, ?)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$name, $email, $subject, $message]);

    // Send mail using Gmail SMTP
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
 
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'removed';
        $mail->Password = 'removed';  // <-- Paste App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('removed', 'Trashman Contact');
        $mail->addAddress('removed'); // Admin receives

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Message from $name: $subject";
        $mail->Body = "<strong>From:</strong> $name ($email)<br><strong>Message:</strong><br>$message";

        $mail->send();
        echo "<script>alert('Message sent successfully.'); window.location='contact_us.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Mail sending failed: {$mail->ErrorInfo}'); window.location='contact_us.php';</script>";
    }
}
?>
