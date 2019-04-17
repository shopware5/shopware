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

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use Shopware\Bundle\SearchBundle\Condition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class ManufacturerFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var ManufacturerServiceInterface
     */
    private $manufacturerService;

    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $snippetNamespace;

    /**
     * @var string
     */
    private $fieldName;

    public function __construct(
        ManufacturerServiceInterface $manufacturerService,
        QueryBuilderFactoryInterface $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->manufacturerService = $manufacturerService;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');

        if (!$this->fieldName = $queryAliasMapper->getShortAlias('sSupplier')) {
            $this->fieldName = 'sSupplier';
        }
    }

    /**
     * @return FacetResultInterface|null
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->resetQueryPart('groupBy');
        $query->resetQueryPart('orderBy');

        $query->groupBy('product.id');
        $query->select('DISTINCT product.supplierID as id');

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $ids = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return null;
        }

        $manufacturers = $this->manufacturerService->getList($ids, $context);

        $activeManufacturers = $this->getActiveIds($criteria);

        /* @var ManufacturerFacet $facet */
        return $this->createFacetResult($facet, $manufacturers, $activeManufacturers);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof Facet\ManufacturerFacet;
    }

    /**
     * @param Manufacturer[] $manufacturers
     * @param int[]          $activeIds
     *
     * @return ValueListFacetResult
     */
    private function createFacetResult(Facet\ManufacturerFacet $facet, $manufacturers, $activeIds)
    {
        $listItems = [];

        foreach ($manufacturers as $manufacturer) {
            $listItem = new ValueListItem(
                $manufacturer->getId(),
                $manufacturer->getName(),
                in_array($manufacturer->getId(), $activeIds),
                $manufacturer->getAttributes()
            );

            $listItems[] = $listItem;
        }

        usort($listItems, function (ValueListItem $a, ValueListItem $b) {
            return strcasecmp($a->getLabel(), $b->getLabel());
        });

        if (!empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetNamespace->get('manufacturer', 'Manufacturer');
        }

        return new ValueListFacetResult(
            'manufacturer',
            !empty($activeIds),
            $label,
            $listItems,
            $this->fieldName
        );
    }

    /**
     * @param Criteria $criteria
     *
     * @return int[]
     */
    private function getActiveIds($criteria)
    {
        if (!$criteria->hasCondition('manufacturer')) {
            return [];
        }

        /** @var Condition\ManufacturerCondition $condition */
        $condition = $criteria->getCondition('manufacturer');

        return $condition->getManufacturerIds();
    }
}
