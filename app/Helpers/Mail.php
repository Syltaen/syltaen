<?php

namespace Syltaen;

abstract class Mail
{
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
     * The content type of all mails
     *
     * @var string
     */
    public static $contType = "text/html";

    /**
     * Send a mail
     *
     * @param string $to Address to send the mail to
     * @param string $subject Subject of the mail
     * @param string $body Content of the mail
     * @param array $custom_headers Custom headers to put in the mail
     * @return boolean True if no error during the sending
     */
    public static function send($to, $subject, $body, $custom_headers = [])
    {
        // Controller::log($to, "to");
        // Controller::log($subject, "subject");
        // Controller::log($body, "body");
        // return true;
        return wp_mail(
            $to,
            $subject,
            $body,
            static::parseHeader($custom_headers)
        );
    }

    /**
     * Send a test mail with generic content
     *
     * @param string $to Address to send the mail to
     * @return string Result of the test
     */
    public static function sendTest($to)
    {
        if (static::send($to, "TEST", "This is a test sent from ".site_url())) {
            return "The e-mail was sent successfully to ".$to;
        } else {
            return "Could not send a mail to ".$to;
        }
    }

    /**
     * Merge the default header and a custom one
     *
     * @param array $custom_headers
     * @return array
     */
    private static function parseHeader($custom_headers = [])
    {
        return array_merge([
            "Content-Type: text/html; charset=UTF-8",
            "From: ".static::$fromName." <".static::$fromAddr.">"
        ], $custom_headers);
    }
}