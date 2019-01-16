<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Mail;

use Swift_Events_SendEvent as SendEvent;
use Swift_Events_SendListener as SendListenerInterface;

/**
 * Class ConfigListener
 */
class ConfigListener implements SendListenerInterface
{
    private $options;

    private $config;

    public function __construct(array $options, \Shopware_Components_Config $config)
    {
        $this->options = $options;
        $this->config = $config;
    }

    public function beforeSendPerformed(SendEvent $event)
    {
        $mail = $event->getMessage();

        if (empty($mail->getFrom())) {
            if (!empty($this->options['from']['email'])) {
                $mail->setFrom(
                    $this->options['from']['email'],
                    !empty($this->options['from']['name']) ? $this->options['from']['name'] : null
                );
            } elseif ($this->config->get('mailer_from_address')) {
                $mail->setFrom(
                    $this->config->get('mailer_from_address'),
                    $this->config->get('mailer_from_name')
                );
            } elseif ($this->config->get('mail')) {
                $mail->setFrom(
                    $this->config->get('mail'),
                    $this->config->get('shopName')
                );
            }
        }

        if (empty($mail->getReplyTo())) {
            if (!empty($this->options['replyTo']['email'])) {
                $mail->setReplyTo(
                    $this->options['replyTo']['email'],
                    !empty($this->options['replyTo']['name']) ? $this->options['replyTo']['name'] : null
                );
            } elseif ($this->config->get('mailer_reply_to')) {
                $mail->setReplyTo(
                    $this->config->get('mailer_reply_to_address') ?: $mail->getFrom()
                );
            }
        }

        if (empty($mail->getReturnPath())) {
            if (!empty($this->options['return_path'])) {
                $mail->setReturnPath($this->options['return_path']);
            } elseif ($this->config->get('mailer_return_path')) {
                $mail->setReturnPath($this->config->get('mailer_return_path'));
            }
        }

        if ($this->config->get('mailer_bcc_address')) {
            $addresses = $mail->getBcc();
            $newAddresses = array_map('trim', explode(';', $this->config->get('mailer_bcc_address')));
            $addresses = array_merge($addresses, array_combine($newAddresses, array_fill(0, count($newAddresses), null)));
            $mail->setBcc($addresses);
        }
    }

    public function sendPerformed(SendEvent $event)
    {
    }
}
