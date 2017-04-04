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
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\LastOrderRuleData;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class LastOrderRule extends Rule
{
    /**
     * @var int
     */
    private $days;

    public function __construct(int $days)
    {
        $this->days = $days;
    }

    public function match(
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        RuleDataCollection $collection
    ): bool {
        if (!$collection->has(LastOrderRuleData::class)) {
            return false;
        }

        /** @var LastOrderRuleData $data */
        $data = $collection->get(LastOrderRuleData::class);

        $min = (new \DateTime())->sub(
            new \DateInterval('P' . (int) $this->days . 'D')
        );

        return $min >= $data->getLastOrderTime();
    }
}
