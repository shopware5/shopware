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

use Shopware\Components\Routing\Context;
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Frontend_RobotsTxt extends Enlight_Controller_Action
{
    public function indexAction()
    {
        $shop = $this->get('shop');
        $routerContexts = $this->getRouterContext($shop->getMain() ?: $shop);

        $baseUrls = array_map(static function (Context $context) {
            return $context->getBaseUrl();
        }, $routerContexts);

        $robotsTxtGenerator = $this->get(\Shopware\Components\RobotsTxtGeneratorInterface::class);
        $baseUrls = array_unique($baseUrls);

        $robotsTxtGenerator->setRouterContext($routerContexts);
        $robotsTxtGenerator->setBaseUrls($baseUrls);
        $robotsTxtGenerator->setHost($shop->getHost());
        $robotsTxtGenerator->setSecure($shop->getSecure());

        $this->View()->assign('robotsTxt', $robotsTxtGenerator);
        $this->Response()->headers->set('content-type', 'text/plain; charset=utf-8');
    }

    /**
     * @return array<int, Context>
     */
    private function getRouterContext(Shop $mainShop): array
    {
        $config = $this->container->get('config');

        /** @var \Shopware\Models\Shop\Repository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository(Shop::class);
        $context = [];
        $allShops = $mainShop->getChildren();
        $allShops[] = $mainShop;

        foreach ($allShops as $shop) {
            $shop = $shopRepository->getById($shop->getId());

            $newConfig = clone $config;
            $newConfig->setShop($shop);

            $context[$shop->getId()] = Context::createFromShop($shop, $newConfig);
        }

        return $context;
    }
}
