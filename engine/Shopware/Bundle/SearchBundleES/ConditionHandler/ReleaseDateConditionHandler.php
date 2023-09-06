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

namespace Shopware\Bundle\SearchBundleES\ConditionHandler;

use DateInterval;
use DateTime;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Search;
use RuntimeException;
use Shopware\Bundle\SearchBundle\Condition\ReleaseDateCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ReleaseDateConditionHandler implements PartialConditionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof ReleaseDateCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function handleFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $search->addQuery($this->getQuery($criteriaPart), BoolQuery::FILTER);
    }

    /**
     * {@inheritdoc}
     */
    public function handlePostFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $search->addPostFilter($this->getQuery($criteriaPart));
    }

    private function getQuery(ReleaseDateCondition $criteriaPart): RangeQuery
    {
        $date = new DateTime();
        $intervalSpec = 'P' . $criteriaPart->getDays() . 'D';
        $interval = new DateInterval($intervalSpec);
        $dateNow = new DateTime();

        switch ($criteriaPart->getDirection()) {
            case ReleaseDateCondition::DIRECTION_FUTURE:
                $date->add($interval);

                return new RangeQuery('formattedReleaseDate', [
                    'lte' => $date->format('Y-m-d'),
                    'gt' => $dateNow->format('Y-m-d'),
                ]);

            case ReleaseDateCondition::DIRECTION_PAST:
                $date->sub($interval);

                return new RangeQuery('formattedReleaseDate', [
                    'gte' => $date->format('Y-m-d'),
                    'lte' => $dateNow->format('Y-m-d'),
                ]);

            default:
                throw new RuntimeException(sprintf('Provided release date direction %s not supported', $criteriaPart->getDirection()));
        }
    }
}
