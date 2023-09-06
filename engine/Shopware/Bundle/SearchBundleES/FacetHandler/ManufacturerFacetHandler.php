<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\SearchBundleES\FacetHandler;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
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
use Shopware_Components_Snippet_Manager;

class ManufacturerFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    public const AGGREGATION_SIZE = 1000;

    private ManufacturerServiceInterface $manufacturerService;

    private Shopware_Components_Snippet_Manager $snippetManager;

    private QueryAliasMapper $queryAliasMapper;

    public function __construct(
        ManufacturerServiceInterface $manufacturerService,
        Shopware_Components_Snippet_Manager $snippetManager,
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

        $ids = array_column($buckets, 'key');
        $manufacturers = $this->manufacturerService->getList($ids, $context);

        $items = $this->createListItems($criteria, $manufacturers);

        $criteriaPart = $this->createFacet($criteria, $items);
        $result->addFacet($criteriaPart);
    }

    /**
     * @param Manufacturer[] $manufacturers
     *
     * @return array<ValueListItem>
     */
    private function createListItems(Criteria $criteria, array $manufacturers): array
    {
        $actives = [];
        $condition = $criteria->getCondition('manufacturer');
        if ($condition instanceof ManufacturerCondition) {
            $actives = $condition->getManufacturerIds();
        }

        $items = [];
        foreach ($manufacturers as $manufacturer) {
            $items[] = new ValueListItem(
                $manufacturer->getId(),
                $manufacturer->getName(),
                \in_array($manufacturer->getId(), $actives),
                $manufacturer->getAttributes()
            );
        }

        usort($items, function (ValueListItem $a, ValueListItem $b) {
            return strcasecmp($a->getLabel(), $b->getLabel());
        });

        return $items;
    }

    /**
     * @param ValueListItem[] $items
     */
    private function createFacet(Criteria $criteria, array $items): ValueListFacetResult
    {
        $fieldName = $this->queryAliasMapper->getShortAlias('sSupplier') ?? 'sSupplier';

        $facet = $criteria->getFacet('manufacturer');
        if ($facet instanceof ManufacturerFacet && !empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetManager->getNamespace('frontend/listing/facet_labels')
                ->get('manufacturer', 'Manufacturer');
        }

        return new ValueListFacetResult(
            'manufacturer',
            $criteria->hasCondition('manufacturer'),
            $label,
            $items,
            $fieldName
        );
    }
}
