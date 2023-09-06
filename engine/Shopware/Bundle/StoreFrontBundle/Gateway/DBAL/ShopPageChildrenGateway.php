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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopPageChildrenGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopPageGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ShopPageChildrenGateway implements ShopPageChildrenGatewayInterface
{
    private Connection $connection;

    private ShopPageGatewayInterface $shopPageGateway;

    public function __construct(
        Connection $connection,
        ShopPageGatewayInterface $shopPageGateway
    ) {
        $this->connection = $connection;
        $this->shopPageGateway = $shopPageGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('page.id');

        $parentIds = $query->from('s_cms_static', 'page')
            ->where('page.parentID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        if (empty($parentIds)) {
            return [];
        }

        return $this->shopPageGateway->getList($parentIds, $context);
    }
}
