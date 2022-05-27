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

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\SitePageMenu;
use Shopware\Models\Shop\Locale as ShopLocale;

/**
 * Shopware ControllerBase Plugin
 */
class Shopware_Plugins_Core_ControllerBase_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch',
            100
        );

        return true;
    }

    /**
     * Event listener method
     *
     * Read base controller data
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();

        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        if (!$request->isDispatched() || $response->isException()
            || $request->getModuleName() !== 'frontend'
            || !$view->hasTemplate()
        ) {
            return;
        }

        $view->assign('baseUrl', $request->getBaseUrl() . $request->getPathInfo());

        $shop = Shopware()->Shop();
        $view->assign('Controller', $args->getSubject()->Request()->getControllerName());

        $view->assign('sBasketQuantity', $view->getAssign('sBasketQuantity') ?: 0);
        $view->assign('sBasketAmount', $view->getAssign('sBasketAmount') ?: 0);
        $view->assign('sNotesQuantity', $view->getAssign('sNotesQuantity') ?: 0);
        $view->assign('sUserLoggedIn', $view->getAssign('sUserLoggedIn') ?: false);

        $view->assign('Shop', $shop);
        if (!$shop->getLocale() instanceof ShopLocale) {
            throw new RuntimeException('Shop does not have a locale set');
        }
        $view->assign('Locale', $shop->getLocale()->getLocale());

        $view->assign('sCategoryStart', $shop->getCategory()->getId());
        $view->assign('sCategoryCurrent', $this->getCategoryCurrent($view->getAssign('sCategoryStart')));
        $view->assign('sCategories', $this->getCategories($view->getAssign('sCategoryCurrent')));
        $view->assign('sMainCategories', $view->getAssign('sCategories'));
        $view->assign('sOutputNet', Shopware()->Session()->get('sOutputNet'));

        $activePage = $view->getAssign('sCustomPage')['id'] ?? null;
        $view->assign('sMenu', $this->getMenu($shop->getId(), $activePage));

        $view->assign('sShopname', Shopware()->Config()->get('shopName'));
    }

    /**
     * Returns basket amount
     *
     * @return float
     */
    public function getBasketAmount()
    {
        $amount = Shopware()->Modules()->Basket()->sGetAmount();

        return empty($amount) ? 0 : array_shift($amount);
    }

    /**
     * Returns current category id
     *
     * @param int $default
     *
     * @return int
     */
    public function getCategoryCurrent($default)
    {
        if (!empty(Shopware()->System()->_GET['sCategory'])) {
            return (int) Shopware()->System()->_GET['sCategory'];
        }

        if (Shopware()->Front()->Request()->get('sCategory')) {
            return (int) Shopware()->Front()->Request()->get('sCategory');
        }

        return (int) $default;
    }

    /**
     * Return current categories
     *
     * @param int $parentId
     *
     * @return array
     */
    public function getCategories($parentId)
    {
        return Shopware()->Modules()->Categories()->sGetCategories($parentId);
    }

    /**
     * Return cms menu items
     *
     * @param int|null $shopId
     * @param int|null $activePageId
     *
     * @return array
     */
    public function getMenu($shopId = null, $activePageId = null)
    {
        if ($shopId === null) {
            $context = Shopware()->Container()->get(ContextServiceInterface::class)->getShopContext();
            $shopId = $context->getShop()->getId();
        }

        $data = Shopware()->Container()->get(SitePageMenu::class)
            ->getTree($shopId, $activePageId);

        return $data;
    }

    /**
     * Return box campaigns items
     *
     * @deprecated Will be removed in 5.8 without replacement
     *
     * @param int $parentId
     *
     * @return array
     */
    public function getCampaigns($parentId)
    {
        $campaigns = [
            'leftTop' => [],
            'leftMiddle' => [],
            'leftBottom' => [],
            'rightMiddle' => [],
            'rightBottom' => [],
        ];

        foreach ($campaigns as $position => $content) {
            $campaigns[$position] = Shopware()->Modules()->Marketing()->sCampaignsGetList(
                $parentId,
                $position
            );
        }

        return $campaigns;
    }

    public function getCapabilities()
    {
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }
}
