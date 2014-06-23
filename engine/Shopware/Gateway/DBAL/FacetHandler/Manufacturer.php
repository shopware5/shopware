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

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Gateway\Search\Condition;
use Shopware\Service;
use Shopware\Struct\Context;
use Shopware\Struct\CoreAttribute;

/**
 * @package Shopware\Gateway\DBAL\FacetHandler
 */
class Manufacturer implements DBAL
{

    /**
     * @var \Shopware\Service\Manufacturer
     */
    private $manufacturerService;

    /**
     * @param Service\Manufacturer $manufacturerService
     */
    function __construct(Service\Manufacturer $manufacturerService)
    {
        $this->manufacturerService = $manufacturerService;
    }

    /**
     * @param Facet $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @param Context $context
     * @return Facet|Facet\Manufacturer
     */
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    ) {
        $query->resetQueryPart('groupBy');
        $query->resetQueryPart('orderBy');

        $query->select(array(
            'products.supplierID as id',
            'COUNT(DISTINCT products.id) as total'
        ));

        $query->groupBy('products.supplierID');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $ids = array_column($data, 'id');

        $manufacturers = $this->manufacturerService->getList($ids, $context);

        /**@var $condition \Shopware\Gateway\Search\Condition\Manufacturer*/
        $condition = $criteria->getCondition('manufacturer');

        foreach ($data as $row) {
            $manufacturer = $manufacturers[$row['id']];

            $attribute = new CoreAttribute();
            $attribute->set('total', $row['total']);
            $attribute->set('active', false);

            if ($condition && $condition->id = $manufacturer->getId()) {
                $attribute->set('active', true);
            }

            $manufacturer->addAttribute('facet', $attribute);
        }

        /**@var $facet Facet\Manufacturer */
        $facet->setManufacturers($manufacturers);

        $facet->setFiltered(($condition instanceof Condition\Manufacturer));

        return $facet;
    }


    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Manufacturer);
    }
}
