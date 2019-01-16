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

use Enlight_Components_Mail as MailMessage;
use Shopware_Components_Config as Config;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Mail
{
    /**
     * @param Config $config
     * @param array  $options
     *
     * @return MailMessage
     */
    public function factory(Config $config, array $options)
    {
        $mail = new MailMessage();
        if (!empty($options['charset'])) {
            $mail->setCharset($options['charset']);
        } elseif ($config->get('charset')) {
            $mail->setCharset($config->get('charset'));
        }

        return $mail;
    }
}
