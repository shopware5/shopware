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

namespace Shopware\Bundle\StoreFrontBundle\Shop;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Common\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

class ShopGateway
{
    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var ShopHydrator
     */
    private $hydrator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ShopHydrator $hydrator
     * @param FieldHelper  $fieldHelper
     * @param Connection   $connection
     */
    public function __construct(
        ShopHydrator $hydrator,
        FieldHelper $fieldHelper,
        Connection $connection
    ) {
        $this->hydrator = $hydrator;
        $this->fieldHelper = $fieldHelper;
        $this->connection = $connection;
    }

    /**
     * @param int[]                                                        $ids
     * @param \Shopware\Bundle\StoreFrontBundle\Context\TranslationContext $context
     *
     * @return Shop[] indexed by id
     */
    public function getList(array $ids, TranslationContext $context)
    {
        $shops = $this->getShops($ids, $context);

        //check if parent shops has to be loaded
        $mainIds = array_values(array_unique(array_filter(array_column($shops, '__shop_main_id'))));
        $mainIds = array_diff($mainIds, $ids);

        $parents = [];
        if (!empty($mainIds)) {
            $parents = $this->getShops($mainIds, $context);
        }

        $result = [];
        foreach ($shops as $row) {
            $id = $row['__shop_id'];
            $mainId = $row['__shop_main_id'];

            if ($mainId && isset($parents[$mainId])) {
                $row['parent'] = $parents[$mainId];
            } elseif ($mainId && isset($shops[$mainId])) {
                $row['parent'] = $shops[$mainId];
            } else {
                $row['parent'] = null;
            }

            $result[$id] = $this->hydrator->hydrate($row);
        }

        return $result;
    }

    /**
     * @param int[]                                                        $ids
     * @param \Shopware\Bundle\StoreFrontBundle\Context\TranslationContext $context
     *
     * @return array[]
     */
    private function getShops(array $ids, TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->addSelect($this->fieldHelper->getShopFields())
            ->addSelect($this->fieldHelper->getCountryFields())
            ->addSelect($this->fieldHelper->getAreaFields())
            ->addSelect($this->fieldHelper->getPaymentMethodFields())
            ->addSelect($this->fieldHelper->getShippingMethodFields())
            ->addSelect($this->fieldHelper->getCurrencyFields())
            ->addSelect($this->fieldHelper->getTemplateFields())
            ->addSelect($this->fieldHelper->getLocaleFields())
            ->addSelect($this->fieldHelper->getCustomerGroupFields())
            ->addSelect($this->fieldHelper->getCategoryFields())
            ->addSelect($this->fieldHelper->getMediaFields());

        $query->from('s_core_shops', 'shop')
            ->innerJoin('shop', 's_core_currencies', 'currency', 'currency.id = shop.currency_id')
            ->innerJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id')
            ->innerJoin('shop', 's_core_customergroups', 'customerGroup', 'customerGroup.id = shop.customer_group_id')
            ->innerJoin('shop', 's_categories', 'category', 'category.id = shop.category_id')
            ->innerJoin('shop', 's_core_countries', 'country', 'country.id = shop.country_id')
            ->innerJoin('shop', 's_core_paymentmeans', 'paymentMethod', 'paymentMethod.id = shop.payment_id')
            ->innerJoin('shop', 's_premium_dispatch', 'shippingMethod', 'shippingMethod.id = shop.dispatch_id')

            ->leftJoin('country', 's_core_countries_areas', 'countryArea', 'countryArea.id = country.areaID')
            ->leftJoin('shop', 's_core_templates', 'template', 'shop.template_id = template.id')
            ->leftJoin('shippingMethod', 's_premium_dispatch_attributes', 'shippingMethodAttribute', 'shippingMethodAttribute.dispatchID = shippingMethod.id')
            ->leftJoin('paymentMethod', 's_core_paymentmeans_attributes', 'paymentMethodAttribute', 'paymentMethodAttribute.paymentmeanID = paymentMethod.id')
            ->leftJoin('country', 's_core_countries_attributes', 'countryAttribute', 'countryAttribute.countryID = country.id')
            ->leftJoin('customerGroup', 's_core_customergroups_attributes', 'customerGroupAttribute', 'customerGroupAttribute.customerGroupID = customerGroup.id')
            ->leftJoin('category', 's_categories_attributes', 'categoryAttribute', 'categoryAttribute.categoryID = category.id')
            ->leftJoin('category', 's_categories_avoid_customergroups', 'customerGroups', 'customerGroups.categoryID = category.id')
            ->leftJoin('category', 's_media', 'media', 'media.id = category.mediaID')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id')
            ->where('shop.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addCountryTranslation($query, $context);
        $this->fieldHelper->addPaymentTranslation($query, $context);
        $this->fieldHelper->addDeliveryTranslation($query, $context);
        $this->fieldHelper->addMediaTranslation($query, $context);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];

        foreach ($data as $row) {
            $result[$row['__shop_id']] = $row;
        }

        return $result;
    }
}
