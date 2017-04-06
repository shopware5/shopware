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
    private $ruleDataCollectorRegistry;

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
        $this->ruleDataCollectorRegistry = $voucherDataCollectorRegistry;
        $this->priceCalculator = $priceCalculator;
    }

    public function process(
        CartContainer $cartContainer,
        ProcessorCart $processorCart,
        ShopContextInterface $context
    ): void {
        $lineItems = $cartContainer->getLineItems()->filterType(self::TYPE_VOUCHER);

        if (0 === $lineItems->count()) {
            return;
        }

        $calculatedCart = $this->calculatedCartGenerator->create($cartContainer, $context, $processorCart);

        $codes = array_column($lineItems->getExtraData(), 'code');
        $vouchers = $this->voucherGateway->get($codes, $calculatedCart, $context);

        $rules = array_filter($vouchers->map(function (Voucher $voucher) {
            return $voucher->getRule();
        }));

        $dataCollection = $this->ruleDataCollectorRegistry->collect(
            $calculatedCart,
            $context,
            new RuleCollection($rules)
        );

        /** @var LineItemInterface $lineItem */
        foreach ($lineItems as $lineItem) {
            $code = $lineItem->getExtraData()['code'];

            if (!$voucher = $vouchers->get($code)) {
                $processorCart->getErrors()->add(new VoucherNotFoundError($code));
                $cartContainer->getLineItems()->remove($code);
                continue;
            }

            if ($voucher->getRule() && !$voucher->getRule()->match($calculatedCart, $context, $dataCollection)) {
                $cartContainer->getLineItems()->remove(self::TYPE_VOUCHER);
                $processorCart->getErrors()->add(new VoucherRuleError($code, $voucher->getRule()));
                continue;
            }

            $this->calculate($processorCart, $context, $voucher, $lineItem);
        }
    }

    private function calculate(
        ProcessorCart $processorCart,
        ShopContextInterface $context,
        Voucher $voucher,
        LineItemInterface $lineItem
    ): void {
        $prices = $processorCart->getCalculatedLineItems()->filterGoods()->getPrices();

        if (0 === $prices->count()) {
            return;
        }

        switch ($voucher->getMode()) {
            case self::TYPE_PERCENTAGE:
                $percentage = abs($voucher->getPercentageDiscount()) * -1;

                $discount = $this->percentagePriceCalculator->calculatePrice($percentage, $prices, $context);

                $processorCart->getCalculatedLineItems()->add(
                    new CalculatedVoucher($lineItem->getIdentifier(), $lineItem, $discount)
                );

                return;

            case self::TYPE_ABSOLUTE:
                $discount = $this->priceCalculator->calculate($voucher->getPrice(), $context);

                $processorCart->getCalculatedLineItems()->add(
                    new CalculatedVoucher($lineItem->getIdentifier(), $lineItem, $discount)
                );

                return;
            default:
                $processorCart->getErrors()->add(
                    new VoucherModeNotFoundError($voucher->getCode(), $voucher->getMode())
                );
        }
    }
}
