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
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CategoryGateway implements Gateway\CategoryGatewayInterface
{
    /**
     * @var Hydrator\CategoryHydrator
     */
    private $categoryHydrator;

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
     * @param Connection $connection
     * @param FieldHelper $fieldHelper
     * @param Hydrator\CategoryHydrator $categoryHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\CategoryHydrator $categoryHydrator
    ) {
        $this->connection = $connection;
        $this->categoryHydrator = $categoryHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function get($id, Struct\ShopContextInterface $context)
    {
        $categories = $this->getList($id, $context);

        return array_shift($categories);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $ids, Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getCategoryFields())
            ->addSelect($this->fieldHelper->getMediaFields())
            ->addSelect("GROUP_CONCAT(customerGroups.customergroupID) as __category_customer_groups")
        ;

        $query->from('s_categories', 'category');

        $query->leftJoin('category', 's_categories_attributes', 'categoryAttribute', 'categoryAttribute.categoryID = category.id')
            ->leftJoin('category', 's_categories_avoid_customergroups', 'customerGroups', 'customerGroups.categoryID = category.id')
            ->leftJoin('category', 's_media', 'media', 'media.id = category.mediaID')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id');

        $query->addOrderBy('category.position')
            ->addOrderBy('category.id')
            ->addGroupBy('category.id');

        $query->where('category.id IN (:categories)')
            ->andWhere('category.active = 1');

        $query->setParameter(':categories', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $categories = [];
        foreach ($data as $row) {
            $id = $row['__category_id'];

            $categories[$id] = $this->categoryHydrator->hydrate($row);
        }

        return $categories;
    }
}
