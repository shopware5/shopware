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
use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;
use Shopware_Components_Snippet_Manager;

class VoteAverageFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof VoteAverageFacet;
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
        $search->addAggregation(
            new TermsAggregation('vote_average', 'voteAverage.average')
        );
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
        if (!isset($elasticResult['aggregations'])) {
            return;
        }
        if (!isset($elasticResult['aggregations']['vote_average'])) {
            return;
        }

        $data = $elasticResult['aggregations']['vote_average'];
        if (empty($data['buckets'])) {
            return;
        }

        $criteriaPart = $this->createFacet($criteria, $data['buckets']);
        $result->addFacet($criteriaPart);
    }

    /**
     * @return RadioFacetResult
     */
    private function createFacet(Criteria $criteria, array $buckets)
    {
        $activeAverage = null;
        if ($criteria->hasCondition('vote_average')) {
            /** @var VoteAverageCondition $condition */
            $condition = $criteria->getCondition('vote_average');
            $activeAverage = $condition->getAverage();
        }

        $values = $this->buildItems($buckets, $activeAverage);

        /** @var VoteAverageFacet|null $facet */
        $facet = $criteria->getFacet('vote_average');
        if ($facet && !empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetManager
                ->getNamespace('frontend/listing/facet_labels')
                ->get('vote_average', 'Ranking');
        }

        if (!$fieldName = $this->queryAliasMapper->getShortAlias('rating')) {
            $fieldName = 'rating';
        }

        return new RadioFacetResult(
            'vote_average',
            $criteria->hasCondition('vote_average'),
            $label,
            $values,
            $fieldName,
            [],
            'frontend/listing/filter/facet-rating.tpl'
        );
    }

    /**
     * @param array $data
     * @param float $activeAverage
     *
     * @return array
     */
    private function buildItems($data, $activeAverage)
    {
        usort($data, function ($a, $b) {
            return $a['key'] > $b['key'];
        });

        $values = [];
        for ($i = 1; $i <= 4; ++$i) {
            $affected = array_filter($data, function ($value) use ($i) {
                return $i <= ($value['key'] / 2);
            });

            $count = array_sum(array_column($affected, 'doc_count'));
            if ($count === 0) {
                continue;
            }

            $values[] = new ValueListItem($i, (string) $count, $activeAverage == $i);
        }

        usort($values, function (ValueListItem $a, ValueListItem $b) {
            return $a->getId() < $b->getId();
        });

        return $values;
    }
}
