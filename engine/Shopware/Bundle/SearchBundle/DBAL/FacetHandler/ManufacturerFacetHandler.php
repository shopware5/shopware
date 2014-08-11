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

namespace Shopware\Bundle\SearchBundle\DBAL\FacetHandler;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\DBAL\QueryBuilder;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\Condition;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\SearchBundle\DBAL\FacetHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\DBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ManufacturerFacetHandler implements FacetHandlerInterface
{
    /**
     * @var ManufacturerServiceInterface
     */
    private $manufacturerService;

    /**
     * @param ManufacturerServiceInterface $manufacturerService
     */
    public function __construct(ManufacturerServiceInterface $manufacturerService)
    {
        $this->manufacturerService = $manufacturerService;
    }

    /**
     * @param FacetInterface $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @param Context $context
     * @return FacetInterface|Facet\ManufacturerFacet
     */
    public function generateFacet(
        FacetInterface $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    ) {
        $query->resetQueryPart('groupBy');
        $query->resetQueryPart('orderBy');

        $query->select(array(
            'product.supplierID as id',
            'COUNT(DISTINCT product.id) as total'
        ));

        $query->groupBy('product.supplierID');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $ids = array_column($data, 'id');
        $ids = array_filter($ids);

        if (empty($ids)) {
            return $facet;
        }

        $manufacturers = $this->manufacturerService->getList($ids, $context);

        /**@var $condition \Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition*/
        $condition = $criteria->getCondition('manufacturer');

        foreach ($data as $row) {
            if (!$row['id']) {
                continue;
            }

            $manufacturer = $manufacturers[$row['id']];

            $attribute = new Attribute();
            $attribute->set('total', $row['total']);
            $attribute->set('active', false);

            if ($condition && in_array($manufacturer->getId(), $condition->getManufacturerIds())) {
                $attribute->set('active', true);
            }

            $manufacturer->addAttribute('facet', $attribute);
        }

        /**@var $facet Facet\ManufacturerFacet */
        $facet->setManufacturers($manufacturers);

        $facet->setFiltered(($condition instanceof Condition\ManufacturerCondition));

        return $facet;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\ManufacturerFacet);
    }
}
