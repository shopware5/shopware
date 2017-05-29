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

namespace Shopware\Bundle\SearchBundleES\FacetHandler;

use ONGR\ElasticsearchDSL\Aggregation\TermsAggregation;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class ManufacturerFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    const AGGREGATION_SIZE = 1000;

    /**
     * @var ManufacturerServiceInterface
     */
    private $manufacturerService;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @param ManufacturerServiceInterface         $manufacturerService
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper                     $queryAliasMapper
     */
    public function __construct(
        ManufacturerServiceInterface $manufacturerService,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->manufacturerService = $manufacturerService;
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof ManufacturerFacet;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $aggregation = new TermsAggregation('manufacturer');
        $aggregation->setField('manufacturer.id');
        $aggregation->addParameter('size', self::AGGREGATION_SIZE);
        $search->addAggregation($aggregation);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(
        array $elasticResult,
        ProductNumberSearchResult $result,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if (!isset($elasticResult['aggregations']['manufacturer'])) {
            return;
        }

        $buckets = $elasticResult['aggregations']['manufacturer']['buckets'];

        if (empty($buckets)) {
            return;
        }

        $manufacturers = $this->getManufacturers($buckets, $context);
        $items = $this->createListItems($criteria, $manufacturers);

        $criteriaPart = $this->createFacet($criteria, $items);
        $result->addFacet($criteriaPart);
    }

    /**
     * @param $buckets
     * @param ShopContextInterface $context
     *
     * @throws \Exception
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer[]
     */
    private function getManufacturers($buckets, ShopContextInterface $context)
    {
        $ids = array_column($buckets, 'key');
        $manufacturers = $this->manufacturerService->getList($ids, $context);

        return $manufacturers;
    }

    /**
     * @param Criteria $criteria
     * @param $manufacturers
     *
     * @return array
     */
    private function createListItems(Criteria $criteria, $manufacturers)
    {
        $actives = [];
        /** @var $condition ManufacturerCondition */
        if ($condition = $criteria->getCondition('manufacturer')) {
            $actives = $condition->getManufacturerIds();
        }

        $items = [];
        /** @var $manufacturer Manufacturer */
        foreach ($manufacturers as $manufacturer) {
            $items[] = new ValueListItem(
                $manufacturer->getId(),
                $manufacturer->getName(),
                in_array($manufacturer->getId(), $actives),
                $manufacturer->getAttributes()
            );
        }

        usort($items, function (ValueListItem $a, ValueListItem $b) {
            return strcasecmp($a->getLabel(), $b->getLabel());
        });

        return $items;
    }

    /**
     * @param Criteria $criteria
     * @param $items
     *
     * @return ValueListFacetResult
     */
    private function createFacet(Criteria $criteria, $items)
    {
        if (!$fieldName = $this->queryAliasMapper->getShortAlias('sSupplier')) {
            $fieldName = 'sSupplier';
        }

        $label = $this->snippetManager->getNamespace('frontend/listing/facet_labels')
            ->get('manufacturer', 'Manufacturer');

        return new ValueListFacetResult(
            'manufacturer',
            $criteria->hasCondition('manufacturer'),
            $label,
            $items,
            $fieldName
        );
    }
}
