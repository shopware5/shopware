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

namespace ProductBundle\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Enlight_Event_EventManager as EventManager;
use EventBundle\EventDispatcher;
use ProductBundle\Event\ManufacturersLoadedEvent;
use ProductBundle\Event\ProductsLoadedEvent;
use ProductBundle\Event\TaxesLoadedEvent;
use ProductBundle\Event\UnitsLoadedEvent;
use ProductBundle\Hydrator\ProductHydrator;
use Shopware\Product\Struct\ProductCollection;
use Shopware\Framework\Struct\FieldHelper;
use Shopware\Context\TranslationContext;

class ProductReader
{
    /**
     * @var ProductHydrator
     */
    protected $hydrator;

    /**
     * @var \Shopware\Framework\Struct\FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ProductHydrator $hydrator,
        EventManager $eventManager
    ) {
        $this->hydrator = $hydrator;
        $this->fieldHelper = $fieldHelper;
        $this->connection = $connection;
        $this->eventDispatcher = $eventManager;
    }

    public function read(array $numbers, TranslationContext $context): ProductCollection
    {
//        $rows = $this->getQuery($numbers, $context)->execute()->fetchAll(\PDO::FETCH_ASSOC);
//
//        $collection = new ProductCollection();
//        foreach ($rows as $row) {
//            $collection->add($this->hydrator->hydrate($row));
//        }
//
//        $this->eventDispatcher->dispatch(
//            new ProductsLoadedEvent($collection, $context)
//        );
//
//        $this->eventDispatcher->dispatch(
//            new ManufacturersLoadedEvent($collection->getManufacturers(), $context)
//        );
//
//        $this->eventDispatcher->dispatch(
//            new UnitsLoadedEvent($collection->getUnits(), $context)
//        );
//
//        $this->eventDispatcher->dispatch(
//            new TaxesLoadedEvent($collection->getTaxes(), $context)
//        );
//
//        return $collection;
    }

    private function getQuery(array $numbers, TranslationContext $context): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query
            ->addSelect($this->fieldHelper->getArticleFields())
            ->addSelect($this->fieldHelper->getVariantFields())
            ->addSelect($this->fieldHelper->getUnitFields())
            ->addSelect($this->fieldHelper->getManufacturerFields())
        ;

        $query
            ->from('s_articles_details', 'variant')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->leftJoin('variant', 's_articles_attributes', 'productAttribute', 'productAttribute.articledetailsID = variant.id')
            ->leftJoin('product', 's_articles_supplier', 'manufacturer', 'manufacturer.id = product.supplierID')
            ->leftJoin('product', 's_articles_supplier_attributes', 'manufacturerAttribute', 'manufacturerAttribute.supplierID = product.supplierID')
            ->leftJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID')
            ->andWhere('variant.ordernumber IN (:numbers)')
            ->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        $this->fieldHelper->addProductTranslation($query, $context);
        $this->fieldHelper->addVariantTranslation($query, $context);
        $this->fieldHelper->addManufacturerTranslation($query, $context);
        $this->fieldHelper->addUnitTranslation($query, $context);

        return $query;
    }
}
