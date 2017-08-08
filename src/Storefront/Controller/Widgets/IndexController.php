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

namespace Shopware\Storefront\Controller\Widgets;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopware\Storefront\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller
{
    /**
     * @Route("/widgets/index/shopMenu", name="widgets/shopMenu")
     * @Method({"GET"})
     */
    public function shopMenuAction(Request $request)
    {
        $shop = $request->attributes->get('_shop');
        //        $shop['children'] = $this->getChildrenByShopId($shop['id']);
        $shop['children'] = $this->getChildrenByShopId(1);
        $shop['currencies'] = [];
        $currencies = [];

        if (!$request->get('hideCurrency', false)) {
            //            $currencies = $this->getCurrenciesByShopId($shop['id']);
            $currencies = $this->getCurrenciesByShopId(1);
        }

        $languages = array_filter(
            $shop['children'],
            function ($language) {
                return (bool) $language['active'];
            }
        );

        array_unshift($languages, $shop);

        return $this->render('@Shopware/widgets/index/shop_menu.html.twig', [
            'shop' => $shop,
            'currencies' => $currencies,
            'languages' => $languages,
        ]);
    }

    private function getCurrenciesByShopId(int $id)
    {
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();

        return $builder->select(['currency.id', 'currency.currency', 'currency.templatechar as symbol'])
            ->from('s_core_shop_currencies', 'shop_currency')
            ->innerJoin('shop_currency', 's_core_currencies', 'currency', 'currency.id = shop_currency.currency_id')
            ->where('shop_currency.shop_id = :shopId')
            ->setParameter('shopId', $id)
            ->execute()
            ->fetchAll();
    }

    private function getChildrenByShopId($id)
    {
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();

        return $builder->select(['shop.*'])
            ->from('s_core_shops', 'shop')
            ->where('shop.main_id = :shopId')
            ->setParameter('shopId', $id)
            ->execute()
            ->fetchAll();
    }
}
