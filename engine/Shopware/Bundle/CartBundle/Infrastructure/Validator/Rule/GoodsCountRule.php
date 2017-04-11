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

namespace Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\LineItem\Goods;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Exception\UnsupportedOperatorException;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\Rule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class GoodsCountRule extends Rule
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var string
     */
    private $operator;

    public function __construct(int $count, string $operator)
    {
        $this->count = $count;
        $this->operator = $operator;
    }

    public function match(
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        RuleDataCollection $collection
    ): bool {
        $goods = $calculatedCart->getCalculatedLineItems()->filterInstance(Goods::class);

        switch ($this->operator) {
            case self::OPERATOR_GTE:

                return $goods->count() >= $this->count;

            case self::OPERATOR_LTE:

                return $goods->count() <= $this->count;

            default:
                throw new UnsupportedOperatorException($this->operator, __CLASS__);
        }
    }
}
