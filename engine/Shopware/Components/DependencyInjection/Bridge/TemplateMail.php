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

use Enlight_Template_Manager;
use RuntimeException;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Shopware_Components_StringCompiler;
use Shopware_Components_TemplateMail;

class TemplateMail
{
    /**
     * @return Shopware_Components_TemplateMail
     */
    public function factory(Container $container)
    {
        $container->load('mailtransport');

        $stringCompiler = new Shopware_Components_StringCompiler(
            $container->get(Enlight_Template_Manager::class)
        );
        $mailer = new Shopware_Components_TemplateMail();
        if ($container->initialized('shop')) {
            $shop = $container->get('shop');
            if (!$shop instanceof Shop) {
                throw new RuntimeException('Shop object not found in DI container');
            }
            $mailer->setShop($shop);
        }
        $mailer->setModelManager($container->get(ModelManager::class));
        $mailer->setStringCompiler($stringCompiler);

        return $mailer;
    }
}
