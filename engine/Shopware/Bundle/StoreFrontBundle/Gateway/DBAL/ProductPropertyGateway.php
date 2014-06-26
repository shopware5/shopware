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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @package Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 */
class ProductPropertyGateway implements Gateway\ProductPropertyGatewayInterface
{
    /**
     * @var Hydrator\PropertyHydrator
     */
    private $propertyHydrator;

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
     * @param Hydrator\PropertyHydrator $propertyHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\PropertyHydrator $propertyHydrator
    ) {
        $this->propertyHydrator = $propertyHydrator;
        $this->entityManager = $entityManager;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $properties = $this->getList(array($product), $context);
        return array_shift($properties);
    }

    /**
     * @inheritdoc
     */
    public function getList($products, Struct\Context $context)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect('products.id as productId')
            ->addSelect($this->fieldHelper->getPropertySetFields())
            ->addSelect($this->fieldHelper->getPropertyGroupFields())
            ->addSelect($this->fieldHelper->getPropertyOptionFields())
        ;

        $query->addSelect('
        (
            CASE
                WHEN propertySet.sortmode = 1 THEN propertyOption.value_numeric
                WHEN propertySet.sortmode = 3 THEN propertyOption.position
                ELSE propertyOption.value
            END
        ) as sortRelevance');

        $query->from('s_filter_articles', 'filterArticles');

        $query->innerJoin(
            'filterArticles',
            's_articles',
            'products',
            'products.id = filterArticles.articleID'
        );

        $query->innerJoin(
            'filterArticles',
            's_filter_values',
            'propertyOption',
            'propertyOption.id = filterArticles.valueID'
        );

        $query->innerJoin(
            'products',
            's_filter',
            'propertySet',
            'propertySet.id = products.filtergroupID'
        );

        $query->leftJoin(
            'propertySet',
            's_filter_attributes',
            'propertySetAttribute',
            'propertySetAttribute.filterID = propertySet.id'
        );

        $query->innerJoin(
            'propertySet',
            's_filter_relations',
            'relations',
            'relations.groupID = propertySet.id'
        );

        $query->innerJoin(
            'propertyOption',
            's_filter_options',
            'propertyGroup',
            'propertyGroup.id = propertyOption.optionID AND relations.optionID = propertyGroup.id'
        );

        $this->fieldHelper->addPropertySetTranslation($query, $context);

        $query->where('products.id IN (:ids)')
            ->setParameter(':language', $context->getShop()->getId())
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $query->orderBy('filterArticles.articleID')
            ->addOrderBy('relations.position')
            ->addOrderBy('propertyGroup.name')
            ->addOrderBy('sortRelevance')
            ->addOrderBy('propertyOption.id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $properties = array();
        foreach ($data as $productId => $values) {
            $properties[$productId] = $this->propertyHydrator->hydrateValues($values);
        }

        $result = array();
        foreach ($products as $product) {
            $sets = $properties[$product->getId()];
            $result[$product->getNumber()] = array_shift($sets);
        }

        return $result;
    }
}
