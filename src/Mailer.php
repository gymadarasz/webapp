<?php

namespace Madsoft\App;

use function strip_tags;
use function file_exists;
use function mkdir;
use function file_put_contents;
use function is_dir;
use function date;
use Exception;
use RuntimeException;
use Madsoft\App\Config;
use Madsoft\App\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

final class Mailer
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function send(
        string $to,
        string $subject,
        string $body
    ): bool {
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                              // Send using SMTP
            $mail->Host       = Config::get('smtpHost');                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = Config::get('smtpUser');                     // SMTP username
            $mail->Password   = Config::get('smtpSecret');                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = Config::get('smtpPort');                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom(Config::get('mailerSystemEmail'), Config::get('mailerSystemFrom'));
            $mail->addAddress($to);     // Add a recipient
            $mail->addReplyTo(Config::get('mailerNoreplyEmail'), Config::get('mailerNoreplyName'));
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            // Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            if (Config::get('mailerSendMails')) {
                $mail->send();
                $this->logger->info("Email sent to: $to\nSubject: $subject\nBody:\n$body\n\n");
            }
            if (Config::get('mailerSaveMails')) {
                if (!file_exists(Config::get('mailerSaveMailsPath')) || !is_dir(Config::get('mailerSaveMailsPath'))) {
                    if (!mkdir(Config::get('mailerSaveMailsPath'), 777)) {
                        throw new RuntimeException("Folder creation error: " . Config::get('mailerSaveMailsPath'));
                    }
                }
                $fname = Config::get('mailerSaveMailsPath') . '/' . date("Y-m-s H-i-s") . " to $to ($subject).html";
                if (
                    file_put_contents(
                        $fname,
                        $body
                    )
                ) {
                    $this->logger->info("Email saved to: $fname");
                } else {
                    $this->logger->error("Message could not be saved to: $fname");
                }
            }
            return true;
        } catch (Exception $e) {
            $this->logger->error("Message could not be sent. Mailer Error: {$mail->ErrorInfo}\nException message:" . $e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
            return false;
        }
    }
}
