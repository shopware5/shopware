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
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\Rule;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\ProductOfCategoriesRuleData;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductOfCategoriesRule extends Rule
{
    /**
     * @var int[]
     */
    protected $categoryIds = [];

    /**
     * @param int[] $categoryIds
     */
    public function __construct(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    public function match(
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        RuleDataCollection $collection
    ): bool {
        /** @var ProductOfCategoriesRuleData $data */
        $data = $collection->get(ProductOfCategoriesRuleData::class);

        //no products found for categories? invalid
        if (!$data) {
            return false;
        }

        return $data->hasOneCategory($this->categoryIds);
    }

    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }
}
