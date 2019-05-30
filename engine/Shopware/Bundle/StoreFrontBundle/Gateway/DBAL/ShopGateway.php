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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ShopHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ShopGateway implements ShopGatewayInterface
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
     * @param int $id
     *
     * @return Shop
     */
    public function get($id)
    {
        $shops = $this->getList([$id]);

        return array_shift($shops);
    }

    /**
     * @param int[] $ids
     *
     * @return Shop[] indexed by id
     */
    public function getList($ids)
    {
        $shops = $this->getShops($ids);

        // Check if parent shops has to be loaded
        $mainIds = array_values(array_unique(array_filter(array_column($shops, '__shop_main_id'))));
        $mainIds = array_diff($mainIds, $ids);

        $parents = [];
        if (!empty($mainIds)) {
            $parents = $this->getShops($mainIds);
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
     * @param int[] $ids
     *
     * @return array[]
     */
    private function getShops($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->addSelect($this->fieldHelper->getShopFields())
            ->addSelect($this->fieldHelper->getCurrencyFields())
            ->addSelect($this->fieldHelper->getTemplateFields())
            ->addSelect($this->fieldHelper->getLocaleFields())
            ->addSelect($this->fieldHelper->getCustomerGroupFields())
            ->addSelect($this->fieldHelper->getCategoryFields())
            ->addSelect($this->fieldHelper->getMediaFields());

        $query->from('s_core_shops', 'shop')
            ->leftJoin('shop', 's_core_shops_attributes', 'shopAttribute', 'shopAttribute.shopID = shop.id')
            ->leftJoin('shop', 's_core_currencies', 'currency', 'currency.id = shop.currency_id')
            ->leftJoin('shop', 's_core_templates', 'template', 'shop.template_id = template.id')
            ->leftJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id')
            ->leftJoin('shop', 's_core_customergroups', 'customerGroup', 'customerGroup.id = shop.customer_group_id')
            ->leftJoin('customerGroup', 's_core_customergroups_attributes', 'customerGroupAttribute', 'customerGroupAttribute.customerGroupID = customerGroup.id')
            ->leftJoin('shop', 's_categories', 'category', 'category.id = shop.category_id')
            ->leftJoin('category', 's_categories_attributes', 'categoryAttribute', 'categoryAttribute.categoryID = category.id')
            ->leftJoin('category', 's_categories_avoid_customergroups', 'customerGroups', 'customerGroups.categoryID = category.id')
            ->leftJoin('category', 's_media', 'media', 'media.id = category.mediaID')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id')
            ->where('shop.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];

        foreach ($data as $row) {
            $result[$row['__shop_id']] = $row;
        }

        return $result;
    }
}
