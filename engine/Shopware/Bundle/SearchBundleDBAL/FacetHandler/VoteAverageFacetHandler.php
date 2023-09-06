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

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use Enlight_Components_Snippet_Namespace;
use PDO;
use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;
use Shopware_Components_Config;
use Shopware_Components_Snippet_Manager;

class VoteAverageFacetHandler implements PartialFacetHandlerInterface
{
    private QueryBuilderFactoryInterface $queryBuilderFactory;

    private Enlight_Components_Snippet_Namespace $snippetNamespace;

    private string $fieldName;

    private Shopware_Components_Config $config;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper,
        Shopware_Components_Config $config
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');
        $this->config = $config;
        $this->fieldName = $queryAliasMapper->getShortAlias('rating') ?? 'rating';
    }

    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof VoteAverageFacet;
    }

    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        return $this->getFacet($facet, $reverted, $criteria, $context);
    }

    private function getFacet(
        VoteAverageFacet $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ): ?RadioFacetResult {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        if (!$query->hasState(VoteAverageCondition::STATE_INCLUDES_VOTE_TABLE)) {
            $this->joinVoteAverage($context, $query);
        }

        $query->groupBy('voteAverage.average');
        $query->select([
            'voteAverage.average',
            'COUNT(voteAverage.average) as count',
        ]);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $activeAverage = null;
        $condition = $criteria->getCondition($facet->getName());
        if ($condition instanceof VoteAverageCondition) {
            $activeAverage = $condition->getAverage();
        }

        $values = $this->buildItems($data, $activeAverage);

        if (!empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetNamespace->get($facet->getName(), 'Shipping free');
        }

        return new RadioFacetResult(
            $facet->getName(),
            $criteria->hasCondition($facet->getName()),
            $label,
            $values,
            $this->fieldName,
            [],
            'frontend/listing/filter/facet-rating.tpl'
        );
    }

    private function joinVoteAverage(ShopContextInterface $context, QueryBuilder $query): void
    {
        $shopCondition = '';
        if ($this->config->get('displayOnlySubShopVotes')) {
            $shopCondition = ' AND (vote.shop_id = :voteAverageShopId OR vote.shop_id IS NULL)';
            $query->setParameter(':voteAverageShopId', $context->getShop()->getId());
        }

        $table = '
    SELECT SUM(vote.points) / COUNT(vote.id) AS average, vote.articleID AS product_id
    FROM s_articles_vote vote
    WHERE vote.active = 1 ' . $shopCondition . '
    GROUP BY vote.articleID';

        $query->innerJoin(
            'product',
            '(' . $table . ')',
            'voteAverage',
            'voteAverage.product_id = product.id'
        );
    }

    /**
     * @param array<array<string, string>> $data
     *
     * @return array<ValueListItem>
     */
    private function buildItems(array $data, ?float $activeAverage): array
    {
        usort($data, static function ($a, $b) {
            return $a['average'] <=> $b['average'];
        });

        $values = [];
        for ($i = 1; $i <= 4; ++$i) {
            $affected = array_filter($data, static function ($value) use ($i) {
                return $value['average'] >= $i;
            });

            $count = array_sum(array_column($affected, 'count'));
            if ($count === 0) {
                continue;
            }

            $values[] = new ValueListItem($i, (string) $count, (int) $activeAverage === $i);
        }

        usort($values, static function (ValueListItem $a, ValueListItem $b) {
            return $b->getId() <=> $a->getId();
        });

        return $values;
    }
}
