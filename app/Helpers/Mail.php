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
    public static function send($to, $subject, $body, $hook = false)
    {
        // If there is a hook, add it
        if ($hook) add_action("phpmailer_init", $hook);

        // Send the mail
        $result = wp_mail($to, $subject, $body);

        // Remove the hook
        if ($hook) add_action("phpmailer_init", $hook);

        return $result;
    }


    /**
     * Put the text body into a mail template
     *
     * @param string $subject
     * @param string $body
     * @param string $template to use
     * @return string
     */
    public static function render($subject, $body, $template = "mail-default", $additional_context = [])
    {
        return View::render("mails/" . $template, array_merge([
            "subject"   => $subject,
            "body"      => $body,

            "imgpath"   => Files::url("build/img/mails/"),
            "primary"   => App::config("color_primary"),
            "secondary" => App::config("color_secondary"),
            "fromName"  => App::config("mail_from_name"),

            "url"  => get_bloginfo("url")
        ], $additional_context));
    }


    // ==================================================
    // > TOOLS
    // ==================================================
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


    // ==================================================
    // > CONFIGS
    // ==================================================
    /**
     * Update the WordPress phpmailer config
     *
     * @param object $mail
     * @return void
     */
    public static function init(&$mail)
    {
        // If not already an HTML email, render the content
        if (!strpos($mail->Body, "<html")) {
            $mail->msgHTML(static::render($mail->Subject, $mail->Body));
        }

        // Send via SMTP if is set in config
        if (App::config("mail_smtp")["host"]) {
            static::smtpSetup($mail);
        }

        // Dkim, unsubscribe, ...
        static::antispamSetup($mail);

        // If debug : prevent sending the mail
        if (App::config("mail_debug")) {
            $mail->clearAllRecipients();
        }
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