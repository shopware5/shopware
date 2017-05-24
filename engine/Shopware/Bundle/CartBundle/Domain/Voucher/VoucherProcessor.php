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

use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\Error\VoucherNotFoundError;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\PercentagePriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Tax\PercentageTaxRuleBuilder;
use Shopware\Bundle\StoreFrontBundle\Common\StructCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class VoucherProcessor implements CartProcessorInterface
{
    const TYPE_VOUCHER = 'voucher';

    /**
     * @var PercentagePriceCalculator
     */
    private $percentagePriceCalculator;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var PercentageTaxRuleBuilder
     */
    private $percentageTaxRuleBuilder;

    public function __construct(
        PercentagePriceCalculator $percentagePriceCalculator,
        PriceCalculator $priceCalculator,
        PercentageTaxRuleBuilder $percentageTaxRuleBuilder
    ) {
        $this->percentagePriceCalculator = $percentagePriceCalculator;
        $this->priceCalculator = $priceCalculator;
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
    }

    public function process(
        CartContainer $cartContainer,
        ProcessorCart $processorCart,
        StructCollection $dataCollection,
        ShopContextInterface $context
    ): void {
        $lineItems = $cartContainer->getLineItems()->filterType(self::TYPE_VOUCHER);

        if (0 === $lineItems->count()) {
            return;
        }

        $prices = $processorCart->getCalculatedLineItems()->filterGoods()->getPrices();
        if (0 === $prices->count()) {
            return;
        }

        /** @var LineItemInterface $lineItem */
        foreach ($lineItems as $lineItem) {
            $code = $lineItem->getExtraData()['code'];

            /** @var VoucherData $voucher */
            if (!$voucher = $dataCollection->get($code)) {
                $cartContainer->getErrors()->add(new VoucherNotFoundError($code));
                $cartContainer->getLineItems()->remove($code);
                continue;
            }

            $this->calculate($processorCart, $context, $voucher, $lineItem);
        }
    }

    private function calculate(
        ProcessorCart $processorCart,
        ShopContextInterface $context,
        VoucherData $voucher,
        LineItemInterface $lineItem
    ): void {
        $prices = $processorCart->getCalculatedLineItems()->filterGoods()->getPrices();

        switch (true) {
            case $voucher instanceof PercentageVoucherData:
                /** @var PercentageVoucherData $voucher */
                $discount = $this->percentagePriceCalculator->calculate(
                    abs($voucher->getPercent()) * -1,
                    $prices,
                    $context
                );

                $processorCart->getCalculatedLineItems()->add(
                    new CalculatedVoucher($lineItem->getIdentifier(), $lineItem, $discount, $voucher->getRule())
                );

                return;

            case $voucher instanceof AbsoluteVoucherData:

                /** @var AbsoluteVoucherData $voucher */
                $discount = $this->priceCalculator->calculate(
                    new PriceDefinition(
                        $voucher->getPrice()->getPrice(),
                        $this->percentageTaxRuleBuilder->buildRules(
                            $prices->getTotalPrice()
                        ),
                        $voucher->getPrice()->getQuantity(),
                        $voucher->getPrice()->isCalculated()
                    ),
                    $context
                );

                $processorCart->getCalculatedLineItems()->add(
                    new CalculatedVoucher($lineItem->getIdentifier(), $lineItem, $discount, $voucher->getRule())
                );

                return;
        }
    }
}
