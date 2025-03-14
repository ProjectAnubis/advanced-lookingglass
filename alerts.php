<?php
declare(strict_types=1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Alerts {
    /**
     * E-posta uyarısı gönderir.
     */
    public static function sendEmailAlert(string $subject, string $message, string $recipient): bool {
        $mail = new PHPMailer(true);
        try {
            // SMTP ayarları
            $mail->isSMTP();
            $mail->Host       = 'smtp.kernzen.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'example@kernzen.com';
            $mail->Password   = 'your_email_password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('example@kernzen.com', 'Advanced LG Alert');
            $mail->addAddress($recipient);

            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Alert Email Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
