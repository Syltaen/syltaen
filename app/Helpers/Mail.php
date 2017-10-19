<?php

namespace Syltaen;

abstract class Mail
{

    const DEBUG = false;

    /**
     * The sender name used in all mails
     *
     * @var string
     */
    public static $fromName = "Hungry Minds";

    /**
     * The address used to send all mails
     *
     * @var string
     */
    public static $fromAddr = "info@hungryminds.be";


    /**
     * Send a mail
     *
     * @param string $to Address to send the mail to
     * @param string $subject Subject of the mail
     * @param string $body Content of the mail
     * @param array $custom_headers Custom headers to put in the mail
     * @return boolean True if no error during the sending
     */
    public static function send($to, $subject, $body, $custom_headers = [], $attachements = [])
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();

        $mail->CharSet = "UTF-8";

        // From
        $mail->From     = static::$fromAddr;
        $mail->FromName = static::$fromName;

        // To
        foreach (static::parseTo($to) as $addr) {
            $mail->AddAddress(trim($addr));
        }

        // Subject
        $mail->Subject = $subject;

        // Body
        $mail->msgHTML($body);

        // Use PHP mail()
        $mail->IsMail();

        // Send or debug
        if (static::DEBUG) {
            return Controller::log($mail, "MAIL");
        } else {
            static::log($mail);

            // Send
            return $mail->Send();
        }
    }

    /**
     * Send a test mail with generic content
     *
     * @param string $to Address to send the mail to
     * @return string Result of the test
     */
    public static function sendTest($to)
    {
        if (static::send($to, "TEST mail", "This is a test sent from <a href='".site_url()."'>".site_url()."</a>")) {
            return "The e-mail was sent successfully to ".$to;
        } else {
            return "An error occured";
        }
    }

    // ==================================================
    // > PARSERS
    // ==================================================
    private static function parseTo($to)
    {
        if (is_array($to)) return $to;

        if (is_string($to)) {
            $to = explode(",", $to);
        }

        return (array) $to;
    }

    // ==================================================
    // > TOOLS
    // ==================================================
    private static function log($mail)
    {
        if (class_exists("No3x\WPML\WPML_Plugin")) {
            $to = [];
            foreach ($mail->getToAddresses() as $reciever) {
                $to[] = $reciever[0];
            }

            (new \No3x\WPML\WPML_Plugin)->log_email([
                "to" => $to,
                "subject" => $mail->Subject,
                "message" => $mail->Body,
                "headers" => [],
                "attachments" => []
            ]);
        }
    }
}