<?php

namespace Syltaen;

abstract class Mail
{
    /**
     * Send a mail
     *
     * @param  string  $to        Address to send the mail to
     * @param  string  $subject   Subject of the mail
     * @param  string  $body      Content of the mail
     * @param  array   $mail_hook Callable function to add stuff to the mail
     * @return boolean True if no error during the sending
     */
    public static function send($to, $subject, $body, $attachments = [], $hook = false)
    {
        // If there is a hook, add it
        if ($hook) {
            add_action("phpmailer_init", $hook);
        }

        // Send the mail
        $result = wp_mail($to, $subject, $body, $attachments);

        // Remove the hook
        if ($hook) {
            add_action("phpmailer_init", $hook);
        }

        return $result;
    }

    /**
     * Put the text body into a mail template
     *
     * @param  string   $subject
     * @param  string   $body
     * @param  string   $template  to use
     * @return string
     */
    public static function render($subject, $body, $template = "mail-default", $additional_context = [])
    {
        return View::render("mails/" . $template, array_merge([
            "subject"   => $subject,
            "body"      => $body,
            "from"      => config("mail.from.name"),

            "imgpath"   => Files::url("build/img/mails/"),
            "primary"   => config("color.primary"),
            "secondary" => config("color.secondary"),
            "logo"      => Data::option("mail_logo"),

            "url"       => get_bloginfo("url"),
        ], $additional_context));
    }

    // ==================================================
    // > TOOLS
    // ==================================================
    /**
     * Send a test mail with generic content
     *
     * @param  string $to    Address to send the mail to
     * @return string Result of the test
     */
    public static function sendTest($to)
    {
        if (static::send($to, "TEST mail", "<p>Hi,<br><br>This is a test sent from <a href='" . site_url() . "'>" . site_url() . "</a></p>")) {
            return "The e-mail was sent successfully to " . $to;
        } else {
            return "An error occured";
        }
    }

    /**
     * Send a mail using a template defined in the options
     *
     * @param  string  $option_key The option key
     * @param  array   $tags       A set of dynamic tags to use. Provide "to" if it's not define in the options.
     * @return boolean True if no error during the sending
     */
    public static function sendTemplate($option_key, $tags = [])
    {
        $mail = Data::option($option_key);

        // Mail not found or Option specify that the mail should not be sent
        if (empty($mail) || (isset($mail["send"]) && !$mail["send"])) {
            return false;
        }

        // Some info is lacking
        $mail = array_merge($mail, $tags);
        if (empty($mail["to"]) || empty($mail["subject"]) || empty($mail["body"])) {
            return false;
        }

        $tags_keys = array_map(function ($tag) {return "[$tag]";}, array_keys($mail));
        $tags_values = array_values($mail);

        Mail::send(
            str_replace($tags_keys, $tags_values, $mail["to"]),
            str_replace($tags_keys, $tags_values, $mail["subject"]),
            str_replace($tags_keys, $tags_values, $mail["body"])
        );
    }

    // ==================================================
    // > CONFIGS
    // ==================================================
    /**
     * Update the WordPress phpmailer config
     *
     * @param  object $mail
     * @return void
     */
    public static function init(&$mail)
    {
        // Render the body
        $mail->msgHTML(static::render($mail->Subject, $mail->Body));

        // Send via SMTP if is set in config
        if (config("mail.smtp.host")) {
            static::smtpSetup($mail);
        }

        // Dkim, unsubscribe, ...
        static::antispamSetup($mail);

        // If debug : prevent sending the mail
        if (config("mail.debug")) {
            $mail->clearAllRecipients();
        }
    }

    /**
     * Setup a SMTP connection
     *
     * @param  PHPMailer $mail
     * @return void
     */
    public static function smtpSetup(&$mail)
    {
        $mail->isSMTP();

        if (config("mail.smtp.debug")) {
            $mail->SMTPDebug = 3;
        }

        $mail->Host       = config("mail.smtp.host");
        $mail->Port       = 587;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = "tls";

        $mail->Username = config("mail.smtp.username");
        $mail->Password = config("mail.smtp.password");
    }

    /**
     * Add custom headers to decrease spam-flag probability
     *
     * @param  PHPMailer $mail
     * @return void
     */
    public static function antispamSetup(&$mail)
    {
        // Unsubscribe list
        $mail->addCustomHeader("List-Unsubscribe", "<mailto:" . $mail->From . "?body=unsubscribe>, <" . site_url("contact") . ">");

        // Remove XMailer
        $mail->XMailer = " ";

        // Organization
        $mail->addCustomHeader("Organization", config("client"));

        // Sender enveloppe & bounce address
        $mail->Sender = $mail->From;

        // DKIM
        if (config("mail.dkim.domain")) {
            $mail->DKIM_domain     = config("mail.dkim.domain");
            $mail->DKIM_selector   = config("mail.dkim.selector");
            $mail->DKIM_private    = config("mail.dkim.private");
            $mail->DKIM_passphrase = config("mail.dkim.passphrase");
            $mail->DKIM_identity   = $mail->From;
        }
    }
}
