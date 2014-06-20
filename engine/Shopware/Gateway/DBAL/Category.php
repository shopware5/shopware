<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct\Context;
use Shopware\Gateway\DBAL\Hydrator;

/**
 * @package Shopware\Gateway\DBAL
 */
class Category implements \Shopware\Gateway\Category
{
    /**
     * @var Hydrator\Category
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
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\Category $categoryHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Category $categoryHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->categoryHydrator = $categoryHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function get($id, Context $context)
    {
        $categories = $this->getList($id, $context);
        return array_shift($categories);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $ids, Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->fieldHelper->getCategoryFields())
            ->addSelect($this->fieldHelper->getMediaFields())
            ->addSelect($this->fieldHelper->getMediaSettingFields())
        ;

        $query->from('s_categories', 'category');

        $query->leftJoin('category', 's_categories_attributes', 'categoryAttribute', 'categoryAttribute.categoryID = category.id')
            ->leftJoin('category', 's_media', 'media', 'media.id = category.mediaID')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id');

        $query->addOrderBy('category.position');

        $query->where('category.id IN (:categories)');

        $query->setParameter(':categories', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $categories = array();
        foreach ($data as $row) {
            $id = $row['__category_id'];

            $categories[$id] = $this->categoryHydrator->hydrate($row);
        }

        return $categories;
    }

}
