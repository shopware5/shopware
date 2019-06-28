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

namespace Shopware\Components\DependencyInjection\Bridge;

class MailTransport
{
    /**
     * @return \Enlight_Class|\Zend_Mail_Transport_Abstract
     */
    public function factory(\Enlight_Loader $loader, \Shopware_Components_Config $config, array $options)
    {
        if (!isset($options['type']) && !empty($config->MailerMailer) && $config->MailerMailer != 'mail') {
            $options['type'] = $config->MailerMailer;
        }
        if (empty($options['type'])) {
            $options['type'] = 'sendmail';
        }

        if ($options['type'] == 'smtp') {
            if (!isset($options['username']) && !empty($config->MailerUsername)) {
                if (!empty($config->MailerAuth)) {
                    $options['auth'] = $config->MailerAuth;
                } elseif (empty($options['auth'])) {
                    $options['auth'] = 'login';
                }
                $options['username'] = $config->MailerUsername;
                $options['password'] = $config->MailerPassword;
            }
            if (!isset($options['ssl']) && !empty($config->MailerSMTPSecure)) {
                $options['ssl'] = $config->MailerSMTPSecure;
            }
            if (!isset($options['port']) && !empty($config->MailerPort)) {
                $options['port'] = $config->MailerPort;
            }
            if (!isset($options['name']) && !empty($config->MailerHostname)) {
                $options['name'] = $config->MailerHostname;
            }
            if (!isset($options['host']) && !empty($config->MailerHost)) {
                $options['host'] = $config->MailerHost;
            }
        }

        if (!$loader->loadClass($options['type'])) {
            $transportName = ucfirst(strtolower($options['type']));
            $transportName = 'Zend_Mail_Transport_' . $transportName;
        } else {
            $transportName = $options['type'];
        }
        unset($options['type'], $options['charset']);

        if ($transportName === 'Zend_Mail_Transport_Smtp') {
            /** @var \Zend_Mail_Transport_Smtp $transport */
            $transport = \Enlight_Class::Instance($transportName, [$options['host'], $options]);
        } elseif (!empty($options)) {
            /** @var \Zend_Mail_Transport_Abstract $transport */
            $transport = \Enlight_Class::Instance($transportName, [$options]);
        } else {
            /** @var \Zend_Mail_Transport_Abstract $transport */
            $transport = \Enlight_Class::Instance($transportName);
        }
        \Enlight_Components_Mail::setDefaultTransport($transport);

        if (!isset($options['from']) && !empty($config->Mail)) {
            $options['from'] = ['email' => $config->Mail, 'name' => $config->Shopname];
        }

        if (!empty($options['from']['email'])) {
            \Enlight_Components_Mail::setDefaultFrom(
                $options['from']['email'],
                !empty($options['from']['name']) ? $options['from']['name'] : null
            );
        }

        if (!empty($options['replyTo']['email'])) {
            \Enlight_Components_Mail::setDefaultReplyTo(
                $options['replyTo']['email'],
                !empty($options['replyTo']['name']) ? $options['replyTo']['name'] : null
            );
        }

        return $transport;
    }
}
