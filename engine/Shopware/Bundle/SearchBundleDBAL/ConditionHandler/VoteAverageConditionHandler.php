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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VoteAverageConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(\Shopware_Components_Config $config)
    {
        $this->config = $config;
    }

    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @return bool
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof VoteAverageCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
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
            'voteAverage.product_id = product.id
             AND voteAverage.average >= :average'
        );

        /* @var VoteAverageCondition $condition */
        $query->setParameter(':average', (float) $condition->getAverage());
        $query->addState(VoteAverageCondition::STATE_INCLUDES_VOTE_TABLE);
    }
}
