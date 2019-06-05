<?php

namespace Syltaen;

abstract class Mail
{
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

        // Force "from" with config values
        $mail->From     = App::config("mail_from_addr");
        $mail->FromName = App::config("mail_from_name");

        // Parse "to"
        foreach (static::parseTo($to) as $addr) $mail->AddAddress(trim($addr));

        // Add subject
        $mail->Subject = $subject;

        // Parse body into the template
        $mail->CharSet = get_bloginfo("charset");
        $body = static::parseBody($mail, $body, $template);
        $mail->msgHTML($body);

        // Attachements : TODO


        // Setup SMTP if the config is provided
        if (App::config("mail_smtp")["host"]) {
            static::smtpSetup($mail);
        } else {
            $mail->IsMail();
        }

        // Dkim, unsubscribe, ...
        static::antispamSetup($mail);

        // Hook to update the mail before sending it
        if (is_callable($mail_hook)) $mail_hook($mail);

        // Log the mail and send it
        if (App::config("mail_debug")) {
            $mail->Subject = "[TEST] " . $mail->Subject;
            static::log($mail);
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
            "From"     => App::config("mail_from_addr"),
            "FromName" => App::config("mail_from_name"),
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
            "primary"   => App::config("color_primary"),
            "secondary" => App::config("color_secondary"),

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

            (new \No3x\WPML\WPML_Plugin(["raw", "html", "json"], []))->log_email([
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
     * Setup a SMTP connection
     *
     * @param PHPMailer $mail
     * @return void
     */
    private static function smtpSetup(&$mail)
    {
        $mail->isSMTP();

        if (App::config("mail_smtp")["debug"]) $mail->SMTPDebug = 3;

        $mail->Host       = App::config("mail_smtp")["host"];
        $mail->Port       = 587;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = "tls";

        $mail->Username   = $mail->From;
        $mail->Password   = App::config("mail_smtp")["password"];
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
        $mail->addCustomHeader("Organization" , App::config("client"));

        // Sender enveloppe & bounce address
        $mail->Sender = $mail->From;

        // DKIM
        if (App::config("mail_dkim")["domain"]) {
            $mail->DKIM_domain     = App::config("mail_dkim")["domain"];
            $mail->DKIM_selector   = App::config("mail_dkim")["selector"];
            $mail->DKIM_private    = App::config("mail_dkim")["private"];
            $mail->DKIM_passphrase = App::config("mail_dkim")["passphrase"];
            $mail->DKIM_identity   = $mail->From;
        }
    }
}