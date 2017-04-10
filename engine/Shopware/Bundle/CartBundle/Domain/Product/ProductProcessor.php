<?php
declare(strict_types=1);
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

namespace Shopware\Bundle\CartBundle\Domain\Product;

use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ProductProcessor implements CartProcessorInterface
{
    const TYPE_PRODUCT = 'product';

    /**
     * @var ProductCalculator
     */
    private $calculator;

    /**
     * @param ProductCalculator $calculator
     */
    public function __construct(ProductCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function process(
        CartContainer $cartContainer,
        ProcessorCart $processorCart,
        ShopContextInterface $context
    ): void {
        $collection = $cartContainer->getLineItems()->filterType(self::TYPE_PRODUCT);
        if ($collection->count() === 0) {
            return;
        }

        $products = $this->calculator->calculate($collection, $context);

        $processorCart->getErrors()->fill($products->getErrors());

        $processorCart->getCalculatedLineItems()->fill(
            $products->getIterator()->getArrayCopy()
        );
    }
}
