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

use Shopware\Bundle\SearchBundle\Condition\ReleaseDateCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ReleaseDateConditionHandler implements ConditionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof ReleaseDateCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $date = new \DateTime();
        /** @var ReleaseDateCondition $condition */
        $intervalSpec = 'P' . $condition->getDays() . 'D';
        $interval = new \DateInterval($intervalSpec);

        $dateNow = new \DateTime();

        $min = ':releaseDateFrom' . md5(json_encode($condition));
        $max = ':releaseDateTo' . md5(json_encode($condition));
        $now = ':dateNow' . md5(json_encode($condition));

        switch ($condition->getDirection()) {
            case ReleaseDateCondition::DIRECTION_FUTURE:
                $date->add($interval);

                $query->andWhere('variant.releasedate <= ' . $min);
                $query->andWhere('variant.releasedate > ' . $now);
                $query->setParameter($min, $date->format('Y-m-d'));
                $query->setParameter($now, $dateNow->format('Y-m-d'));
                break;

            case ReleaseDateCondition::DIRECTION_PAST:
                $date->sub($interval);

                $query->andWhere('variant.releasedate >= ' . $max);
                $query->andWhere('variant.releasedate <= ' . $now);
                $query->setParameter($max, $date->format('Y-m-d'));
                $query->setParameter($now, $dateNow->format('Y-m-d'));
                break;
        }
    }
}
