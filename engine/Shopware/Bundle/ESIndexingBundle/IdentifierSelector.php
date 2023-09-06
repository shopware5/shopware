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

namespace Shopware\Bundle\ESIndexingBundle;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class IdentifierSelector
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ShopGatewayInterface
     */
    private $shopGateway;

    public function __construct(
        Connection $connection,
        ShopGatewayInterface $shopGateway
    ) {
        $this->connection = $connection;
        $this->shopGateway = $shopGateway;
    }

    /**
     * @return Shop[]
     */
    public function getShops()
    {
        return $this->shopGateway->getList($this->getShopIds());
    }

    /**
     * @return int[]
     */
    public function getShopIds()
    {
        return $this->connection->createQueryBuilder()
            ->select('id')
            ->from('s_core_shops', 'shop')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return string[]
     */
    public function getCustomerGroupKeys()
    {
        return $this->connection->createQueryBuilder()
            ->select('groupkey')
            ->from('s_core_customergroups', 'customerGroups')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param int $shopId
     *
     * @return int[]
     */
    public function getShopCurrencyIds($shopId)
    {
        $ids = $this->connection->createQueryBuilder()
            ->select('currency_id')
            ->from('s_core_shop_currencies', 'currency')
            ->andWhere('currency.shop_id = :id')
            ->setParameter(':id', $shopId)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        $ids = array_map('intval', $ids);

        return $ids;
    }
}
