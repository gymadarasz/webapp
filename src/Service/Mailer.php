<?php declare(strict_types = 1);

/**
 * Mailer
 *
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace GyMadarasz\WebApp\Service;

use function strip_tags;
use function file_exists;
use function mkdir;
use function file_put_contents;
use function is_dir;
use function date;
use Exception;
use RuntimeException;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Mailer
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Mailer
{
    protected Config $config;
    protected Logger $logger;

    /**
     * Method __construct
     *
     * @param Config $config config
     * @param Logger $logger logger
     */
    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }
    
    /**
     * Method send
     *
     * @param string $to      to
     * @param string $subject subject
     * @param string $body    body
     *
     * @return bool
     * @throws RuntimeException
     */
    public function send(
        string $to,
        string $subject,
        string $body
    ): bool {
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            
            // Enable verbose debug output
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            
            // Send using SMTP
            $mail->isSMTP();
            
            // Set the SMTP server to send through
            $mail->Host       = $this->config->get('smtpHost');
            
            // Enable SMTP authentication
            $mail->SMTPAuth   = true;
            
            // SMTP username
            $mail->Username   = $this->config->get('smtpUser');
            
            // SMTP password
            $mail->Password   = $this->config->get('smtpSecret');
            
            // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            
            // TCP port to connect to,
            // use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            $mail->Port       = $this->config->get('smtpPort');

            //Recipients
            $mail->setFrom(
                $this->config->get('mailerSystemEmail'),
                $this->config->get('mailerSystemFrom')
            );
            
            // Add a recipient
            $mail->addAddress($to);
            
            $mail->addReplyTo(
                $this->config->get('mailerNoreplyEmail'),
                $this->config->get('mailerNoreplyName')
            );
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            // Attachments
            
            // Add attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');
            //
            // // Optional name
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');

            // Content
            
            // Set email format to HTML
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            if ($this->config->get('mailerSendMails')) {
                $mail->send();
                $this->logger->info(
                    "Email sent to: $to\nSubject: $subject\nBody:\n$body\n\n"
                );
            }
            if ($this->config->get('mailerSaveMails')) {
                if ((!file_exists($this->config->get('mailerSaveMailsPath'))
                    || !is_dir($this->config->get('mailerSaveMailsPath')))
                    && !mkdir(
                        $this->config->get('mailerSaveMailsPath'),
                        $this->config->get('mailerSaveMailsMode'),
                        true
                    )
                ) {
                    throw new RuntimeException(
                        "Folder creation error: " .
                            $this->config->get('mailerSaveMailsPath')
                    );
                }
                $fname = $this->config->get('mailerSaveMailsPath') .
                        '/' . date("Y-m-s H-i-s") . " to $to ($subject).html";
                if (file_put_contents(
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
            $this->logger->error(
                "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n"
                    . "Exception message:" . $e->getMessage() . "\n"
                    . "Trace:\n" . $e->getTraceAsString()
            );
            return false;
        }
    }
}
