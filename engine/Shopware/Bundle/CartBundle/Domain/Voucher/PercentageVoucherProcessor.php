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

namespace Shopware\Bundle\CartBundle\Domain\Voucher;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\PercentagePriceCalculator;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PercentageVoucherProcessor implements CartProcessorInterface
{
    const TYPE_PERCENTAGE_VOUCHER = 'percentage_voucher';

    /**
     * @var PercentagePriceCalculator
     */
    private $percentagePriceCalculator;

    public function __construct(PercentagePriceCalculator $discountCalculator)
    {
        $this->percentagePriceCalculator = $discountCalculator;
    }

    public function process(
        CartContainer $cartContainer,
        CalculatedCart $calculatedCart,
        ProcessorCart $processorCart,
        ShopContextInterface $context
    ): void {
        $vouchers = $cartContainer->getLineItems()->filterType(
            self::TYPE_PERCENTAGE_VOUCHER
        );

        if (0 === count($vouchers)) {
            return;
        }

        $prices = $processorCart->getLineItems()->filterGoods()->getPrices();

        if (0 === count($prices)) {
            return;
        }

        /** @var LineItemInterface $voucher */
        foreach ($vouchers as $voucher) {
            $data = $voucher->getExtraData();

            $percentage = abs($data['percentage']) * -1;

            $discount = $this->percentagePriceCalculator->calculatePrice(
                $percentage,
                $prices,
                $context
            );

            $processorCart->getLineItems()->add(
                new CalculatedVoucher($voucher, $discount)
            );
        }
    }
}
