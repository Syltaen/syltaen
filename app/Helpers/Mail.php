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
     * @param  string   $body
     * @return string
     */
    public static function render($subject, $body)
    {
        // Already an HTML email
        if (strpos($body, "<table")) {
            return $body;
        }

        // Render with WooCommerce template
        $email = WC()->mailer()->emails["WC_Email_New_Order"];
        return $email->style_inline(
            wc_get_template_html("emails/custom-email.php", [
                "email"         => $email,
                "email_heading" => $subject,
                "content"       => $body,
            ])
        );
    }

    /**
     * Render an order's email
     *
     * @param  WC_Mail $email
     * @param  array   $extra_args
     * @return html
     */
    public static function renderOrder($email, $extra_args = [])
    {
        return wc_get_template_html("emails/custom-order-email.php", array_merge([
            "order"              => $email->object,
            "email_heading"      => $email->get_heading(),
            "content"            => $email->format_string($email->get_option("content", "")),
            "additional_content" => $email->get_additional_content(),
            "sent_to_admin"      => false,
            "plain_text"         => false,
            "email"              => $email,
        ], $extra_args));
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