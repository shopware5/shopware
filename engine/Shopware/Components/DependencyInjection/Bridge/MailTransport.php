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

use Shopware_Components_Config;
use Swift_FileSpool;
use Swift_MailTransport;
use Swift_NullTransport;
use Swift_SpoolTransport;
use Swift_Transport;
use Swift_SmtpTransport;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MailTransport
{
    /**
     * @param Shopware_Components_Config $config
     * @param array $options
     * @return Swift_Transport
     */
    public function factory(Shopware_Components_Config $config, array $options)
    {
        if (!isset($options['type']) && !empty($config->MailerMailer) && $config->MailerMailer != 'mail') {
            $options['type'] = $config->MailerMailer;
        }

        if (empty($options['type'])) {
            $options['type'] = 'mail';
        } elseif ($options['type'] == 'gmail') {
            $options['type'] = 'smtp';
            $options['ssl'] = 'ssl';
            $options['auth'] = 'login';
            $options['host'] = 'smtp.gmail.com';
            $options['port'] = 465;
        }

        if ($options['type'] == 'smtp') {
            $transport = new Swift_SmtpTransport();
            $this->setSmtpOptions(
                $transport,
                $this->getSmtpOptions($options, $config)
            );
            return $transport;
        } elseif ($options['type'] == 'mail') {
            return new Swift_MailTransport();
        } elseif ($options['type'] == 'file') {
            return new Swift_SpoolTransport(new Swift_FileSpool($options['path']));
        } else {
            return new Swift_NullTransport();
        }
    }

    private function getSmtpOptions(array $options, Shopware_Components_Config $config)
    {
        if (!isset($options['username']) && !empty($config->MailerUsername)) {
            if (!empty($config->MailerAuth)) {
                $options['auth'] = $config->MailerAuth;
            } elseif (empty($options['auth'])) {
                $options['auth'] = 'login';
            }
            if ($options['auth'] == 'crammd5') {
                $options['auth'] = 'cram-md5';
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
        return $options;
    }

    private function setSmtpOptions(Swift_SmtpTransport $transport, $options)
    {
        if (isset($options['host'])) {
            $transport->setHost($options['host']);
        }
        if (isset($options['port'])) {
            $transport->setPort($options['port']);
        }
        if (!empty($options['ssl'])) {
            $transport->setEncryption($options['ssl']);
        }
        if (isset($options['username'])) {
            $transport->setUsername($options['username']);
        }
        if (isset($options['password'])) {
            $transport->setPassword($options['password']);
        }
        if (isset($options['auth'])) {
            $transport->setAuthMode($options['auth']);
        }
        if (isset($options['name'])) {
            $transport->setLocalDomain($options['name']);
        }
    }
}
