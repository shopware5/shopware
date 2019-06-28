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

use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class VoteAverageFacetHandler implements PartialFacetHandlerInterface
{
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

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper,
        \Shopware_Components_Config $config
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');
        $this->config = $config;

        if (!$this->fieldName = $queryAliasMapper->getShortAlias('rating')) {
            $this->fieldName = 'rating';
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

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $activeAverage = null;
        if ($criteria->hasCondition($facet->getName())) {
            /** @var VoteAverageCondition $condition */
            $condition = $criteria->getCondition($facet->getName());
            $activeAverage = $condition->getAverage();
        }

        $values = $this->buildItems($data, $activeAverage);

        /** @var VoteAverageFacet $facet */
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

    /**
     * Checks if the passed facet can be handled by this class.
     *
     * @return bool
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof VoteAverageFacet;
    }

    private function joinVoteAverage(ShopContextInterface $context, QueryBuilder $query)
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
     * @param array $data
     * @param float $activeAverage
     *
     * @return array
     */
    private function buildItems($data, $activeAverage)
    {
        usort($data, function ($a, $b) {
            return $a['average'] > $b['average'];
        });

        $values = [];
        for ($i = 1; $i <= 4; ++$i) {
            $affected = array_filter($data, function ($value) use ($i) {
                return $value['average'] >= $i;
            });

            $count = array_sum(array_column($affected, 'count'));
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
