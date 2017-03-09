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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopPageGateway
{
    /**
     * @var Hydrator\ShopPageHydrator
     */
    private $shopPageHydrator;

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
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection                $connection
     * @param FieldHelper               $fieldHelper
     * @param Hydrator\ShopPageHydrator $shopPageHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\ShopPageHydrator $shopPageHydrator
    ) {
        $this->connection = $connection;
        $this->shopPageHydrator = $shopPageHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\ShopPage requires the following data:
     * - shop page data
     * - Core attribute of the shop page
     *
     * Required translation in the provided context language:
     * - Shop page
     *
     * @param int[]                     $ids
     * @param Struct\TranslationContext $context
     *
     * @return Struct\ShopPage[] Indexed by the shop page id
     */
    public function getList(array $ids, Struct\TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('page.id as arrayKey');
        $query->addSelect($this->fieldHelper->getShopPageFields());

        $query->from('s_cms_static', 'page')
            ->leftJoin('page', 's_cms_static_attributes', 'pageAttribute', 'pageAttribute.cmsStaticID = page.id')
            ->where('page.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

        return array_map([$this->shopPageHydrator, 'hydrate'], $data);
    }
}
