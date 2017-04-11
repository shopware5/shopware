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

namespace Shopware\Bundle\CartBundle\Domain\Validator\Rule;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

abstract class Rule implements \JsonSerializable
{
    use JsonSerializableTrait;

    const OPERATOR_GTE = '=>';

    const OPERATOR_LTE = '<=';

    const OPERATOR_EQ = '=';

    const OPERATOR_NEQ = '!=';

    /**
     * Validate the current rule and return boolean to indicate if the current rule applied (true) or not (false)
     *
     * @param CalculatedCart                                                       $calculatedCart
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface       $context
     * @param \Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection $collection
     *
     * @return bool
     */
    abstract public function match(
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        RuleDataCollection $collection
    ): bool;
}
