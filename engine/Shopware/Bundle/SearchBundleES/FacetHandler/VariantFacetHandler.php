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

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListItem;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorOptionsGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class VariantFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    /**
     * @var ConfiguratorOptionsGatewayInterface
     */
    private $gateway;

    /**
     * @var string|null
     */
    private $fieldName;

    public function __construct(
        ConfiguratorOptionsGatewayInterface $gateway,
        QueryAliasMapper $queryAliasMapper
    ) {
        if (!$this->fieldName = $queryAliasMapper->getShortAlias('variants')) {
            $this->fieldName = 'var';
        }

        $this->gateway = $gateway;
    }

    /**
     * Validates if the criteria part can be handled by this handler
     *
     * @return bool
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof VariantFacet;
    }

    /**
     * Handles the criteria part and extends the provided search.
     */
    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $aggregation = new TermsAggregation('variant');
        $aggregation->setField('configuration.options.id');
        $aggregation->setParameters(['size' => 5000]);
        $search->addAggregation($aggregation);
    }

    /**
     * Hydrates the Elasticsearch result to extend the product number search result
     * with facets or attributes.
     */
    public function hydrate(
        array $elasticResult,
        ProductNumberSearchResult $result,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if (!isset($elasticResult['aggregations']['variant'])) {
            return;
        }

        $buckets = $elasticResult['aggregations']['variant']['buckets'];

        if (empty($buckets)) {
            return;
        }

        /**
         * @var VariantFacet
         */
        $facet = $criteria->getFacet('option');
        if (!$facet instanceof VariantFacet) {
            return;
        }

        $ids = array_column($buckets, 'key');
        $groups = $this->gateway->getOptions($ids, $context);
        if (empty($groups)) {
            return;
        }

        $groups = array_filter($groups, function (Group $group) use ($facet) {
            return in_array($group->getId(), $facet->getGroupIds(), true);
        });

        $actives = $this->getFilteredValues($criteria);
        $facet = $this->createCollectionResult($facet, $groups, $actives);
        $result->addFacet($facet);
    }

    /**
     * @param Group[] $groups
     * @param int[]   $actives
     *
     * @return FacetResultGroup|FacetResultInterface
     */
    private function createCollectionResult(
        VariantFacet $facet,
        array $groups,
        array $actives
    ) {
        $results = [];

        foreach ($groups as $group) {
            $items = [];
            $useMedia = false;
            $isActive = false;

            foreach ($group->getOptions() as $option) {
                $listItem = new MediaListItem(
                    $option->getId(),
                    $option->getName(),
                    in_array($option->getId(), $actives, true),
                    $option->getMedia(),
                    $option->getAttributes()
                );

                $isActive = ($isActive || $listItem->isActive());
                $useMedia = ($useMedia || $listItem->getMedia() !== null);

                $items[] = $listItem;
            }

            if ($useMedia) {
                $results[] = new MediaListFacetResult(
                    $facet->getName(),
                    $isActive,
                    $group->getName(),
                    $items,
                    $this->fieldName,
                    $group->getAttributes()
                );
            } else {
                $results[] = new ValueListFacetResult(
                    $facet->getName(),
                    $isActive,
                    $group->getName(),
                    $items,
                    $this->fieldName,
                    $group->getAttributes()
                );
            }
        }

        return new FacetResultGroup($results, null, $facet->getName());
    }

    /**
     * @return array
     */
    private function getFilteredValues(Criteria $criteria)
    {
        $values = [];
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        /** @var VariantCondition $condition */
        foreach ($conditions as $condition) {
            foreach ($condition->getOptionIds() as $id) {
                $values[] = $id;
            }
        }

        return $values;
    }
}
