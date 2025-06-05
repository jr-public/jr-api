<?php
namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;

    public function __construct() {
        $this->host = getenv('MAIL_HOST');
        $this->port = (int) getenv('MAIL_PORT');
        $this->username = getenv('MAIL_USERNAME');
        $this->password = getenv('MAIL_PASSWORD');
        $this->fromEmail = getenv('MAIL_FROM_EMAIL');
        $this->fromName = getenv('MAIL_FROM_NAME');
    }

	public function send(string $to, string $subject, string $body, string $toName = ''): bool {
		$mail = new PHPMailer(true);

		try {
			$mail->isSMTP();
			$mail->Host = $this->host;
			$mail->SMTPAuth = true;
			$mail->Username = $this->username;
			$mail->Password = $this->password;
			$mail->Port = $this->port;

			// Conditionally disable encryption for MailHog on port 1025
			if ($this->port === 1025) {
				$mail->SMTPAutoTLS = false;  // disable automatic STARTTLS
				$mail->SMTPSecure = false;    // no encryption
			} else {
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // enable STARTTLS
			}

			// Recipients
			$mail->setFrom($this->fromEmail, $this->fromName);
			$mail->addAddress($to, $toName);

			// Content
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $body;

			$mail->send();
			return true;
		} catch (Exception $e) {
			throw new \RuntimeException("Email sending failed: {$mail->ErrorInfo}");
		}
	}


    public function sendActivationEmail(string $email, string $username, string $token): bool {
        $activationUrl = "http://localhost/activation.php?token=" . urlencode($token);
        
        $subject = "Activate your account";
        $body = "
            <h2>Welcome {$username}!</h2>
            <p>Please click the link below to activate your account:</p>
            <p><a href='{$activationUrl}'>Activate Account</a></p>
            <p>Or copy this link: {$activationUrl}</p>
        ";

        return $this->send($email, $subject, $body, $username);
    }

    public function sendPasswordResetEmail(string $email, string $username, string $token): bool {
        $resetUrl = "http://localhost/password_reset.php?token=" . urlencode($token);
        
        $subject = "Reset your password";
        $body = "
            <h2>Password Reset Request</h2>
            <p>Hi {$username},</p>
            <p>Click the link below to reset your password:</p>
            <p><a href='{$resetUrl}'>Reset Password</a></p>
            <p>Or copy this link: {$resetUrl}</p>
            <p>This link will expire in 1 hour.</p>
        ";

        return $this->send($email, $subject, $body, $username);
    }
}