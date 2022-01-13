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
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ShopPageHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopPageGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ShopPageGateway implements ShopPageGatewayInterface
{
    private ShopPageHydrator $shopPageHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     */
    private FieldHelper $fieldHelper;

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ShopPageHydrator $shopPageHydrator
    ) {
        $this->connection = $connection;
        $this->shopPageHydrator = $shopPageHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('page.id as arrayKey');
        $query->addSelect($this->fieldHelper->getShopPageFields());

        $query->from('s_cms_static', 'page')
            ->leftJoin('page', 's_cms_static_attributes', 'pageAttribute', 'pageAttribute.cmsStaticID = page.id')
            ->where('page.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addShopPageTranslation($query, $context);

        $data = $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        return array_map([$this->shopPageHydrator, 'hydrate'], $data);
    }
}
