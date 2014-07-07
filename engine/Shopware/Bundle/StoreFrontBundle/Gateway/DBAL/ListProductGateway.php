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
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ListProductGateway implements Gateway\ListProductGatewayInterface
{
    /**
     * @var Hydrator\ProductHydrator
     */
    protected $hydrator;

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
    protected $fieldHelper;

    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\ProductHydrator $hydrator
     */
    public function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\ProductHydrator $hydrator
    ) {
        $this->hydrator = $hydrator;
        $this->fieldHelper = $fieldHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public function get($number, Struct\Context $context)
    {
        $products = $this->getList(array($number), $context);

        return array_shift($products);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $numbers, Struct\Context $context)
    {
        $query = $this->getQuery($numbers, $context);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = array();
        foreach ($data as $product) {
            $key = $product['__variant_ordernumber'];
            $products[$key] = $this->hydrator->hydrateListProduct($product);
        }

        return $products;
    }

    /**
     * @param array $numbers
     * @param Struct\Context $context
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function getQuery(array $numbers, Struct\Context $context)
    {
        $esdQuery = $this->getEsdQuery();

        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->fieldHelper->getArticleFields())
            ->addSelect($this->fieldHelper->getTopSellerFields())
            ->addSelect($this->fieldHelper->getVariantFields())
            ->addSelect($this->fieldHelper->getUnitFields())
            ->addSelect($this->fieldHelper->getTaxFields())
            ->addSelect($this->fieldHelper->getPriceGroupFields())
            ->addSelect($this->fieldHelper->getManufacturerFields())
            ->addSelect($this->fieldHelper->getEsdFields())
            ->addSelect('(' . $esdQuery->getSQL() . ') as __product_has_esd');

        $query->from('s_articles_details', 'variant')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('product', 's_articles_supplier', 'manufacturer', 'manufacturer.id = product.supplierID')
            ->leftJoin('product', 's_core_pricegroups', 'priceGroup', 'priceGroup.id = product.pricegroupID')
            ->leftJoin('variant', 's_articles_attributes', 'productAttribute', 'productAttribute.articledetailsID = variant.id')
            ->leftJoin('product', 's_articles_supplier_attributes', 'manufacturerAttribute', 'manufacturerAttribute.id = product.supplierID')
            ->leftJoin('product', 's_articles_top_seller_ro', 'topSeller', 'topSeller.article_id = product.id')
            ->leftJoin('variant', 's_articles_esd', 'esd', 'esd.articledetailsID = variant.id')
            ->leftJoin('esd', 's_articles_esd_attributes', 'esdAttribute', 'esdAttribute.esdID = esd.id')
            ->where('variant.ordernumber IN (:numbers)')
            ->andWhere('variant.active = 1')
            ->andWhere('product.active = 1')
            ->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        $this->fieldHelper->addProductTranslation($query);
        $this->fieldHelper->addVariantTranslation($query);
        $this->fieldHelper->addManufacturerTranslation($query);
        $this->fieldHelper->addUnitTranslation($query);

        $query->setParameter(':language', $context->getShop()->getId());

        return $query;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getEsdQuery()
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select('1')
            ->from('s_articles_esd', 'esd')
            ->where('esd.articleID = product.id')
            ->setMaxResults(1);

        return $query;
    }
}
