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

use Shopware\Bundle\SearchBundle\Condition\LengthCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class LengthConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var VariantHelperInterface
     */
    private $variantHelper;

    public function __construct(VariantHelperInterface $variantHelper)
    {
        $this->variantHelper = $variantHelper;
    }

    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof LengthCondition;
    }

    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        /* @var LengthCondition $condition */
        $this->variantHelper->joinVariants($query);

        $min = ':minLength' . md5(json_encode($condition));
        $max = ':maxLength' . md5(json_encode($condition));

        if ($condition->getMinLength() > 0) {
            $query->andWhere('allVariants.length >= ' . $min);
            $query->setParameter($min, $condition->getMinLength());
        }

        if ($condition->getMaxLength() > 0) {
            $query->andWhere('allVariants.length <= ' . $max);
            $query->setParameter($max, $condition->getMaxLength());
        }
    }
}
