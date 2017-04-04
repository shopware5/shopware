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

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCartGenerator;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\Error\VoucherModeNotFoundError;
use Shopware\Bundle\CartBundle\Domain\Error\VoucherNotFoundError;
use Shopware\Bundle\CartBundle\Domain\Error\VoucherRuleError;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\PercentagePriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VoucherProcessor implements CartProcessorInterface
{
    const TYPE_VOUCHER = 'voucher';

    const TYPE_PERCENTAGE = 'percentage';

    const TYPE_ABSOLUTE = 'absolute';

    /**
     * @var PercentagePriceCalculator
     */
    private $percentagePriceCalculator;

    /**
     * @var CalculatedCartGenerator
     */
    private $calculatedCartGenerator;

    /**
     * @var VoucherGatewayInterface
     */
    private $voucherGateway;

    /**
     * @var RuleDataCollectorRegistry
     */
    private $voucherDataCollectorRegistry;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    public function __construct(
        PercentagePriceCalculator $percentagePriceCalculator,
        CalculatedCartGenerator $calculatedCartGenerator,
        VoucherGatewayInterface $voucherGateway,
        RuleDataCollectorRegistry $voucherDataCollectorRegistry,
        PriceCalculator $priceCalculator
    ) {
        $this->percentagePriceCalculator = $percentagePriceCalculator;
        $this->calculatedCartGenerator = $calculatedCartGenerator;
        $this->voucherGateway = $voucherGateway;
        $this->voucherDataCollectorRegistry = $voucherDataCollectorRegistry;
        $this->priceCalculator = $priceCalculator;
    }

    public function process(
        CartContainer $cartContainer,
        ProcessorCart $processorCart,
        ShopContextInterface $context
    ): void {
        if (!$cartContainer->getLineItems()->has(self::TYPE_VOUCHER)) {
            return;
        }

        $lineItem = $cartContainer->getLineItems()->get(self::TYPE_VOUCHER);

        $code = $lineItem->getExtraData()['code'];

        $calculatedCart = $this->calculatedCartGenerator->create($cartContainer, $context, $processorCart);

        $vouchers = $this->voucherGateway->get([$code], $calculatedCart, $context);

        if (!$vouchers->has($code)) {
            $processorCart->getErrors()->add(new VoucherNotFoundError($code));
            $cartContainer->getLineItems()->remove($code);

            return;
        }

        $voucher = $vouchers->get($code);

        if (!$voucher->getRule()) {
            $this->calculate($processorCart, $context, $voucher, $lineItem);

            return;
        }

        $dataCollection = $this->voucherDataCollectorRegistry->collect(
            $calculatedCart,
            $context,
            new RuleCollection([$voucher->getRule()])
        );

        if ($voucher->getRule()->match($calculatedCart, $context, $dataCollection)) {
            $cartContainer->getLineItems()->remove(self::TYPE_VOUCHER);
            $processorCart->getErrors()->add(
                new VoucherRuleError($code, $voucher->getRule())
            );

            return;
        }

        $this->calculate($processorCart, $context, $voucher, $lineItem);
    }

    private function calculate(
        ProcessorCart $processorCart,
        ShopContextInterface $context,
        Voucher $voucher,
        LineItemInterface $lineItem
    ): void {
        $prices = $processorCart->getLineItems()->filterGoods()->getPrices();

        if (0 === $prices->count()) {
            return;
        }

        switch ($voucher->getMode()) {
            case self::TYPE_PERCENTAGE:
                $percentage = abs($voucher->getPercentageDiscount()) * -1;

                $discount = $this->percentagePriceCalculator->calculatePrice($percentage, $prices, $context);

                $processorCart->getLineItems()->add(
                    new CalculatedVoucher($voucher->getCode(), $lineItem, $discount)
                );

                return;

            case self::TYPE_ABSOLUTE:
                $discount = $this->priceCalculator->calculate($voucher->getPrice(), $context);

                $processorCart->getLineItems()->add(
                    new CalculatedVoucher($voucher->getCode(), $lineItem, $discount)
                );

                return;
            default:
                $processorCart->getErrors()->add(
                    new VoucherModeNotFoundError($voucher->getCode(), $voucher->getMode())
                );
        }
    }
}
