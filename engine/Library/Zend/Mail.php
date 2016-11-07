<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Class for sending an email.
 *
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @deprecated Use Swift_Message instead
 */
interface Zend_Mail
{
    /**
     * Sets the text body for the message.
     *
     * @param  string $txt
     * @param  string $charset
     * @return Zend_Mail Provides fluent interface
     */
    public function setBodyText($txt, $charset = null);

    /**
     * Return text body
     *
     * @return null|string
     */
    public function getBodyText();

    /**
     * Sets the HTML body for the message
     *
     * @param  string $html
     * @param  string $charset
     * @return Zend_Mail Provides fluent interface
     */
    public function setBodyHtml($html, $charset = null);


    /**
     * Return body HTML
     *
     * @return null|string
     */
    public function getBodyHtml();

    /**
     * Creates a Zend_Mime_Part attachment
     *
     * Attachment is automatically added to the mail object after creation. The
     * attachment object is returned to allow for further manipulation.
     *
     * @param  string $body
     * @param  string $mimeType
     * @param  string $disposition
     * @param  string $encoding
     * @param  string $filename OPTIONAL A filename for the attachment
     * @return Zend_Mime_Part Newly created Zend_Mime_Part object (to allow
     * advanced settings)
     */
    public function createAttachment($body,
                                     $mimeType = Zend_Mime::TYPE_OCTETSTREAM,
                                     $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding = Zend_Mime::ENCODING_BASE64,
                                     $filename = null);

    /**
     * Adds To-header and recipient, $email can be an array, or a single string address
     *
     * @param  string|array $email
     * @param  string $name
     * @return Zend_Mail Provides fluent interface
     */
    public function addTo($email, $name = '');

    /**
     * Adds Cc-header and recipient, $email can be an array, or a single string address
     *
     * @param  string|array $email
     * @param  string $name
     * @return Zend_Mail Provides fluent interface
     */
    public function addCc($email, $name = '');

    /**
     * Adds Bcc recipient, $email can be an array, or a single string address
     *
     * @param  string|array $email
     * @return Zend_Mail Provides fluent interface
     */
    public function addBcc($email);

    /**
     * Clears list of recipient email addresses
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearRecipients();

    /**
     * Sets From-header and sender of the message
     *
     * @param  string $email
     * @param  string $name
     * @return Zend_Mail Provides fluent interface
     */
    public function setFrom($email, $name = null);

    /**
     * Set Reply-To Header
     *
     * @param  string $email
     * @param  string $name
     * @return Zend_Mail
     */
    public function setReplyTo($email, $name = null);

    /**
     * Returns the sender of the mail
     *
     * @return string
     */
    public function getFrom();

    /**
     * Returns the current Reply-To address of the message
     *
     * @return string|null Reply-To address, null when not set
     */
    public function getReplyTo();

    /**
     * Clears the sender from the mail
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearFrom();

    /**
     * Clears the current Reply-To address from the message
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearReplyTo();

    /**
     * Sets the subject of the message
     *
     * @param  string $subject
     * @return Zend_Mail Provides fluent interface
     */
    public function setSubject($subject);

    /**
     * Returns the encoded subject of the message
     *
     * @return string
     */
    public function getSubject();

    /**
     * Clears the encoded subject from the message
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearSubject();

    /**
     * Sends this email using the given transport or a previously
     * set DefaultTransport or the internal mail function if no
     * default transport had been set.
     *
     * @param  null $transport
     * @return Zend_Mail Provides fluent interface
     */
    public function send($transport = null);
}
