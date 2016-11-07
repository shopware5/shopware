<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Mail
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Basic Enlight mail component.
 *
 * The Enlight_Components_Mail is a component for sending an email. It extends the zend form
 * with php mailer functions.
 *
 * @category   Enlight
 * @package    Enlight_Mail
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @deprecated Use Swift_Message instead
 */
class Enlight_Components_Mail extends Swift_Message implements Zend_Mail
{
    /**
     * Public constructor
     *
     * @param string $charset
     */
    public function __construct($charset = null)
    {
        parent::__construct(null, null, null, $charset);
    }

    /**
     * Get mail mode
     *
     * @return bool
     */
    public function IsHTML()
    {
        return $this->getContentType() == 'text/html';
    }

    /**
     * Add a recipient to mail
     *
     * @param string $email
     * @param string $name
     * @return Enlight_Components_Mail
     */
    public function AddAddress($email, $name = '')
    {
        return $this->addTo($email, $name);
    }

    /**
     * Clears list of recipient email addresses
     *
     * @return Enlight_Components_Mail
     */
    public function ClearAddresses()
    {
        return $this->clearRecipients();
    }

    /**
     * Returns from name
     *
     * @return string
     */
    public function getFromName()
    {
        $addresses = parent::getFrom();
        foreach ($addresses as $address => $name) {
            return $name;
        }
        return null;
    }

    /**
     * Returns from address
     *
     * @return string
     */
    public function getFromAddress()
    {
        $addresses = parent::getFrom();
        foreach ($addresses as $address => $name) {
            return $address;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setBodyText($txt, $charset = null)
    {
        if ($charset === null) {
            $charset = $this->getCharset();
        }
        if ($this->IsHTML()) {
            foreach ($this->getChildren() as $child) {
                if ($child->getContentType() == 'text/plain') {
                    $child->setBody($txt);
                    return $this;
                }
            }
            return $this->addPart($txt, 'text/plain', $charset);
        } else {
            return $this->setBody($txt, 'text/plain', $charset);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setBodyHtml($html, $charset = null)
    {
        if ($charset === null) {
            $charset = $this->getCharset();
        }
        if ($this->getBody() && !$this->IsHTML()) {
            $this->addPart($this->getBody(), $this->getContentType(), $this->getCharset());
        }
        return $this->setBody($html, 'text/html', $charset);
    }

    /**
     * Returns plain body text
     *
     * @return string|null
     */
    public function getPlainBodyText()
    {
        if ($this->getContentType() == 'text/plain') {
            return $this->getBody();
        }
        foreach ($this->getChildren() as $child) {
            if ($child->getContentType() == 'text/plain') {
                return $child->getBody();
            }
        }
        return null;
    }

    /**
     * Returns plain body html
     *
     * @return string|null
     */
    public function getPlainBody()
    {
        if ($this->IsHTML()) {
            return $this->getBody();
        }
        return null;
    }

    /**
     * Returns the plain subject
     * @return string|null
     */
    public function getPlainSubject()
    {
        return $this->getSubject();
    }

    /**
     * {@inheritdoc}
     */
    public function getBodyText()
    {
        return $this->getPlainBodyText();
    }

    /**
     * {@inheritdoc}
     */
    public function getBodyHtml()
    {
        return $this->getPlainBody();
    }

    /**
     * {@inheritdoc}
     */
    public function clearRecipients()
    {
        $this->setTo([]);
        $this->setCc([]);
        $this->setBcc([]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearFrom()
    {
        return parent::setFrom([]);
    }

    /**
     * {@inheritdoc}
     */
    public function clearSubject()
    {
        $this->getHeaders()->remove('Subject');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearReplyTo()
    {
        return $this->setReplyTo([]);
    }

    /**
     * {@inheritdoc}
     */
    public function send($transport = null)
    {
        Shopware()->Container()->get('mailer')->send($this);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createAttachment($body,
                                     $mimeType = Zend_Mime::TYPE_OCTETSTREAM,
                                     $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding = Zend_Mime::ENCODING_BASE64,
                                     $filename = null)
    {
        $attachment = new Swift_Attachment($body, $filename, $mimeType);
        $this->attach($attachment);
    }
}
