<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\DependencyInjection\Bridge;

use Enlight_Components_Mail;
use Shopware\Components\DependencyInjection\Container;
use Shopware_Components_Config;

class Mail
{
    /**
     * @return Enlight_Components_Mail|null
     */
    public function factory(Container $container, Shopware_Components_Config $config, array $options)
    {
        if (!$container->load('mailtransport')) {
            return null;
        }

        if (isset($options['charset'])) {
            $defaultCharSet = $options['charset'];
        } elseif (!empty($config->get('CharSet'))) {
            $defaultCharSet = $config->get('CharSet');
        } else {
            $defaultCharSet = null;
        }

        return new Enlight_Components_Mail($defaultCharSet, $config->get('mailer_hostname') ?: null);
    }
}
