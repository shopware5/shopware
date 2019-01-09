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

use Swift_Events_SendEvent;
use Swift_Plugins_RedirectingPlugin;

/**
 * Class RedirectingListener
 */
class RedirectingListener extends Swift_Plugins_RedirectingPlugin
{
    /**
     * The recipient who will receive all messages.
     *
     * @var bool
     */
    private $hasRecipient;

    public function __construct(\Shopware_Components_Config $config)
    {
        $recipient = $config->get('mailer_delivery_address');
        if (!empty($recipient)) {
            $this->hasRecipient = true;
            $recipient = explode(';', $recipient);
        } else {
            $this->hasRecipient = false;
            $recipient = [];
        }
        $whitelist = $config->get('mailer_delivery_whitelist');
        if (!empty($whitelist)) {
            $whitelist = (array)$whitelist;
        } else {
            $whitelist = [];
        }
        parent::__construct($recipient, $whitelist);
    }

    /**
     * Invoked immediately before the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        if ($this->hasRecipient) {
            parent::beforeSendPerformed($evt);
        }
    }

    /**
     * Invoked immediately after the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        if ($this->hasRecipient) {
            parent::sendPerformed($evt);
        }
    }
}
