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
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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

        switch ($condition->getDirection()) {
            case ReleaseDateCondition::DIRECTION_FUTURE:
                $date->add($interval);

                $query->andWhere('variant.releasedate <= :releaseDateFrom');
                $query->andWhere('variant.releasedate > :dateNow');
                $query->setParameter(':releaseDateFrom', $date->format('Y-m-d'));
                $query->setParameter(':dateNow', $dateNow->format('Y-m-d'));
                break;

            case ReleaseDateCondition::DIRECTION_PAST:
                $date->sub($interval);

                $query->andWhere('variant.releasedate >= :releaseDateTo');
                $query->andWhere('variant.releasedate <= :dateNow');
                $query->setParameter(':releaseDateTo', $date->format('Y-m-d'));
                $query->setParameter(':dateNow', $dateNow->format('Y-m-d'));
                break;
        }
    }
}
