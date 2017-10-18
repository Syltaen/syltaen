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
     * The content type of all mails
     *
     * @var string
     */
    public static $contType = "multipart/alternative";



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
        $boundary = static::generateBoundary();

        if (static::DEBUG) {
            Controller::log([
                "to"      => $to,
                "subject" => static::parseSubject($subject),
                "body"    => static::parseBody($body, $boundary),
                "headers" => static::parseHeader($custom_headers, $boundary)
            ], "MAIL");
            return true;

        } else {
            if (empty($attachements)) {
                return wp_mail(
                    $to,
                    static::parseSubject($subject),
                    static::parseBody($body, $boundary),
                    static::parseHeader($custom_headers, $boundary),
                    $attachements
                );
            } else {
                return wp_mail(
                    $to,
                    static::parseSubject($subject),
                    $body,
                    static::parseHeader($custom_headers),
                    $attachements
                );
            }
        }
    }

    /**
     * Generate a multipart boundary
     *
     * @return string
     */
    public static function generateBoundary()
    {
        return "---boudary-" . sha1(microtime(true).mt_rand(10000,90000));
    }

    /**
     * Send a test mail with generic content
     *
     * @param string $to Address to send the mail to
     * @return string Result of the test
     */
    public static function sendTest($to)
    {
        if (static::send($to, "TEST", "This is a test sent from <a href='".site_url()."'>".site_url()."</a>")) {
            return "The e-mail was sent successfully to ".$to;
        } else {
            return "Could not send a mail to ".$to;
        }
    }


    // ==================================================
    // > PARSERS
    // ==================================================
    /**
     * Get an UTF-8 version of the subject
     *
     * @param string $subject
     * @return string
     */
    private static function parseSubject($subject)
    {
        return "=?utf-8?Q?".imap_8bit(substr($subject, 0, 60))."?=";
    }
    /**
     * Merge the default header and a custom one
     *
     * @param array $custom_headers
     * @return array
     */
    private static function parseHeader($custom_headers = [], $boundary = false)
    {
        $g = array_merge([

            "From: ".static::$fromName." <".static::$fromAddr.">",
            "Return-Path: ".static::$fromAddr,

            "List-Id: ".static::$fromName,
            "List-Unsubscribe: <mailto:".static::$fromAddr.">"

        ], $custom_headers);


        if ($boundary) {
            $g = array_merge([
                "Content-Type: ".static::$contType."; boundary=$boundary",
                "MIME-Version: 1.0",
            ], $g);
        }

        return implode("\r\n", $g);

    }

    /**
     * Get a multipart version of the body
     *
     * @param string $html_body
     * @param string $boundary
     * @return string
     */
    private static function parseBody($html_body, $boundary)
    {
        $body = [
            "--$boundary",
            "Content-Type: text/plain; charset=\"UTF-8\"",
            "Content-Transfer-Encoding: 8bit",
            "",
            \Html2Text\Html2Text::convert($html_body),

            "",
            "--$boundary",
            "Content-Type: text/html; charset=\"UTF-8\"",
            "Content-Transfer-Encoding: 8bit",
            "",
            $html_body,

            "--$boundary--",
        ];

        return implode("\r\n", $body);
    }
}