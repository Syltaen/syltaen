<?php

namespace Syltaen;

abstract class Mail
{

    /**
     * If set to true, does not send the mail but display it and log all its information
     */
    const DEBUG = false;


    /**
     * Define the sending mode.
     * Either WEB or SMTP
     */
    const MODE  = "WEB";


    /**
     * The sender name used in all mails
     *
     * @var string
     */
    public static $fromName = "";


    /**
     * The address used to send all mails
     *
     * @var string
     */
    public static $fromAddr = "";


    /**
     * Colors used in the mail template
     *
     * @var string Hexadecimal code
     */
    public static $primaryColor   = "#42b38e";
    public static $secondaryColor = "#275aa2";


    /**
     * Send a mail
     *
     * @param string $to Address to send the mail to
     * @param string $subject Subject of the mail
     * @param string $body Content of the mail
     * @param array $mail_hook Callable function to add stuff to the mail
     * @return boolean True if no error during the sending
     */
    public static function send($to, $subject, $body, $attachements = [], $mail_hook = false, $template = "mail-default")
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();

        // ========== FROM ========== //
        $mail->From     = static::$fromAddr;
        $mail->FromName = static::$fromName;

        // ========== TO ========== //
        foreach (static::parseTo($to) as $addr) {
            $mail->AddAddress(trim($addr));
        }

        // ========== SUBJECT ========== //
        $mail->Subject = $subject;


        // ========== BODY ========== //
        $mail->CharSet = get_bloginfo("charset");
        $body = static::parseBody($mail, $body, $template);
        $mail->msgHTML($body);

        // ========== ATTACHEMENTS ========== //


        // ========== SETUPS ========== //
        switch (self::MODE) {
            case "SMTP": static::smtpSetup($mail); break;
            case "WEB":
            default:     static::webSetup($mail); break;
        }
        static::antispamSetup($mail);

        // ========== MAIL MODIFICATION HOOK ========== //
        if (is_callable($mail_hook)) $mail_hook($mail);

        // ========== SEND OR DEBUG ========== //
        if (static::DEBUG) {
            $mail->Subject = "[TEST] " . $mail->Subject;
            return static::log($mail);
        } else {
            static::log($mail);
            return $mail->Send();
        }
    }


    /**
     * Render the mail in a preview box
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return void
     */
    public static function preview($to, $subject, $body, $actions = [])
    {
        echo static::parseBody((object) [
            "Subject"  => $subject,
            "CharSet"  => get_bloginfo("charset"),
            "From"     => static::$fromAddr,
            "FromName" => static::$fromName,
        ], $body, "mail-preview", [
            "to" => $to,
            "actions" => $actions
        ]);
    }

    // ==================================================
    // > PARSERS
    // ==================================================
    /**
     * Parse a string list of recievers into an array
     *
     * @param string|array $to
     * @return array
     */
    private static function parseTo($to)
    {
        if (is_array($to)) return $to;

        if (is_string($to)) {
            $to = explode(",", $to);
        }

        return (array) $to;
    }


    /**
     * Put the text body into a mail template
     *
     * @param string $body
     * @return string
     */
    private static function parseBody($mail, $body, $template, $additional_context = [])
    {
        return (new Controller)->view("mails/".$template, array_merge([
            "mail"    => $mail,
            "body"    => $body,

            "imgpath"   => Files::url("build/img/mails/"),
            "primary"   => static::$primaryColor,
            "secondary" => static::$secondaryColor,

            "url"  => get_bloginfo("url")
        ], $additional_context));
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
                "to"          => $to,
                "subject"     => $mail->Subject,
                "message"     => $mail->Body,
                "headers"     => [],
                "attachments" => []
            ]);
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
        if (static::send($to, "TEST mail", "<p>Hi,<br><br>This is a test sent from <a href='".site_url()."'>".site_url()."</a></p>")) {
            return "The e-mail was sent successfully to ".$to;
        } else {
            return "An error occured";
        }
    }

    /**
     * A hook triggered for each default WordPress mail
     *
     * @param array $args
     * @return void
     */
    public static function hookRelay($args)
    {
        static::send(
            $args["to"],
            $args["subject"],
            $args["message"],
            $args["attachments"]
        );

        // Prevent the default sending
        $args["to"] = "";
        $args["message"] = "";
        return $args;
    }


    // ==================================================
    // > CONFIGS
    // ==================================================
    /**
     * Setup a classic authentification via DKIM
     *
     * @param PHPMailer $mail
     * @return void
     */
    private static function webSetup(&$mail)
    {
        // Use PHP mail()
        $mail->IsMail();

        // DKIM & antispam
        // self::dkimSetup($mail);
    }


    /**
     * Setup a SMTP connection
     *
     * @param PHPMailer $mail
     * @return void
     */
    private static function smtpSetup(&$mail)
    {
        $mail->isSMTP();

        $mail->SMTPDebug = 2;

        $mail->Host       = "";
        $mail->Port       = 587;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = "tls";
        $mail->Username   = "";
        $mail->Password   = "";

        // DKIM & antispam
        // self::dkimSetup($mail);
    }


    /**
     * Configure Dkim
     *
     * @param [type] $mail
     * @return void
     */
    private static function dkimSetup(&$mail)
    {
        $mail->DKIM_domain     = "...";
        $mail->DKIM_selector   = "phpmailer";
        $mail->DKIM_private    = "/var/www/vhosts/.../httpdocs/dkim/dkim.private";
        $mail->DKIM_passphrase = "";
        $mail->DKIM_identity   = $mail->From;
    }


    /**
     * Add custom headers to decrease spam-flag probability
     *
     * @param PHPMailer $mail
     * @return void
     */
    private static function antispamSetup(&$mail)
    {
        // Unsubscribe list
        $mail->addCustomHeader("List-Unsubscribe", "<mailto:".$mail->From."?body=unsubscribe>, <".site_url("contact").">");

        // Remove XMailer
        $mail->XMailer = " ";

        // Organization
        $mail->addCustomHeader("Organization" , get_bloginfo("name"));

        // Sender enveloppe & bounce address
        $mail->Sender = $mail->From;
    }
}