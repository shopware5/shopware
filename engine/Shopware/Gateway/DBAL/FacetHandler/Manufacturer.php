<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Gateway\Search\Condition;
use Shopware\Struct\Attribute;
use Shopware\Service;
use Shopware\Struct\Context;

class Manufacturer extends DBAL
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

        foreach($data as $row) {
            $manufacturer = $manufacturers[$row['id']];

            $attribute = new Attribute();
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