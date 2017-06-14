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

namespace Shopware\Bundle\CartBundle\Domain\Product;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\ValidatorInterface;
use Shopware\Bundle\CartBundle\Domain\Error\Error;
use Shopware\Bundle\CartBundle\Domain\Error\ProductNotFoundError;
use Shopware\Bundle\CartBundle\Domain\Error\ValidationError;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\StoreFrontBundle\Common\StructCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ProductValidator implements ValidatorInterface
{
    public function validate(
        CalculatedCart $cart,
        ShopContextInterface $context,
        StructCollection $dataCollection
    ): bool {
        $products = $cart->getCartContainer()->getLineItems()->filterType(ProductProcessor::TYPE_PRODUCT);

        if ($products->count() === 0) {
            return true;
        }

        $valid = true;

        /** @var LineItemInterface $product */
        foreach ($products as $product) {
            if (!$cart->getCalculatedLineItems()->has($product->getIdentifier())) {
                $valid = false;
                $cart->getErrors()->add(new ProductNotFoundError($product->getIdentifier()));
                $cart->getCartContainer()->getLineItems()->remove($product->getIdentifier());
                continue;
            }

            /** @var CalculatedProduct $calculated */
            $calculated = $cart->getCalculatedLineItems()->get($product->getIdentifier());
            if (!$rule = $calculated->getRule()) {
                continue;
            }

            $reason = $rule->match($cart, $context, $dataCollection);
            if (!$reason->matches()) {
                continue;
            }

            $cart->getCartContainer()->getLineItems()->remove($product->getIdentifier());
            foreach ($reason->getMessages() as $message) {
                $cart->getErrors()->add(new ValidationError(Error::LEVEL_ERROR, $message, $product->getIdentifier()));
            }
            $valid = false;
        }

        return $valid;
    }
}
