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

namespace Shopware\Components\Cart;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Shopware\Bundle\CartBundle\CartKey;
use Shopware\Bundle\CartBundle\CartPositionsMode;
use Shopware\Bundle\CartBundle\CheckoutKey;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;
use Shopware\Components\Cart\Struct\Price;

class TaxAggregator implements TaxAggregatorInterface
{
    public const VOUCHER_TAX_MODE_DEFAULT = 'default';
    public const VOUCHER_TAX_MODE_AUTO = 'auto';
    public const VOUCHER_TAX_MODE_NONE = 'none';

    private Connection $connection;

    private ContextServiceInterface $contextService;

    private ?string $voucherTaxPreset;

    private ?string $discountTaxPreset;

    private ?string $automaticTaxMode;

    public function __construct(
        Connection $connection,
        ContextServiceInterface $contextService,
        ?string $voucherTaxPreset = null,
        ?string $discountTaxPreset = null,
        ?string $automaticTaxMode = null
    ) {
        $this->connection = $connection;
        $this->contextService = $contextService;
        $this->voucherTaxPreset = $voucherTaxPreset;
        $this->discountTaxPreset = $discountTaxPreset;
        $this->automaticTaxMode = $automaticTaxMode;
    }

    /**
     * {@inheritDoc}
     */
    public function positionsTaxSum(array $cart, float $maximumTaxRate): ?array
    {
        if (!\array_key_exists(CartKey::POSITIONS, $cart)) {
            return null;
        }

        /** @var array<numeric-string, float> $result */
        $result = [];

        foreach ($cart[CartKey::POSITIONS] as $cartPosition) {
            [$taxRate, $tax] = $this->resolveTaxRateAndTax($cartPosition, $maximumTaxRate);

            if (empty($taxRate) || empty($tax)) {
                continue;
            }

            /** @var numeric-string $taxRateString */
            $taxRateString = number_format($taxRate, 2);

            if (!\array_key_exists($taxRateString, $result)) {
                $result[$taxRateString] = 0.0;
            }

            $result[$taxRateString] += $tax;
        }

        if (empty($result)) {
            return null;
        }

        ksort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function shippingCostsTaxSum(array $cart): ?array
    {
        if (empty($cart[CheckoutKey::SHIPPING_COSTS_TAX])) {
            return null;
        }

        /** @var array<numeric-string, float> $result */
        $result = [];

        if (!empty($cart[CheckoutKey::SHIPPING_COSTS_TAX_PROPORTIONAL])) {
            /** @var callable(array<numeric-string, float> $carry, Price $shippingTax): array<numeric-string, float> */
            $callback = static function (array $carry, Price $shippingTax) {
                /** @var numeric-string $taxRate */
                $taxRate = number_format($shippingTax->getTaxRate(), 2);

                if (!\array_key_exists($taxRate, $carry)) {
                    $carry[$taxRate] = 0.0;
                }

                $carry[$taxRate] += $shippingTax->getTax();

                return $carry;
            };

            $result = array_reduce($cart[CheckoutKey::SHIPPING_COSTS_TAX_PROPORTIONAL], $callback, []);
        } else {
            /** @var numeric-string $taxRate */
            $taxRate = number_format((float) $cart[CheckoutKey::SHIPPING_COSTS_TAX], 2);

            $result[$taxRate] = $cart[CheckoutKey::SHIPPING_COSTS_WITH_TAX] - $cart[CheckoutKey::SHIPPING_COSTS_NET];

            if (empty($result[$taxRate])) {
                unset($result[$taxRate]);
            }
        }

        if (empty($result)) {
            return null;
        }

        ksort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * @param array<int|numeric-string, float> $positionTaxSum
     * @param array<int|numeric-string, float> $shippingCostsTaxSum
     *
     * @return array<int|numeric-string, float>
     */
    private function aggregateTaxes(array $positionTaxSum, array $shippingCostsTaxSum): array
    {
        $same = array_keys(
            array_intersect_key($positionTaxSum, $shippingCostsTaxSum)
        );

        $different = array_merge(
            array_diff_key($positionTaxSum, $shippingCostsTaxSum),
            array_diff_key($shippingCostsTaxSum, $positionTaxSum)
        );

        /** @var callable(array<numeric-string, float> $carry, numeric-string $taxRate): array<numeric-string, float> $callback */
        $callback = static function (array $carry, string $taxRate) use ($positionTaxSum, $shippingCostsTaxSum): array {
            $carry[$taxRate] = $positionTaxSum[$taxRate] + $shippingCostsTaxSum[$taxRate];

            return $carry;
        };

        return array_reduce($same, $callback, $different);
    }

    /**
     * @param array{tax_rate?: string|float, taxRate?: string|float, tax: string|float|null, modus: string, ordernumber: string} $cartPosition
     *
     * @throws DBALException
     *
     * @return array<float>
     */
    private function resolveTaxRateAndTax(array $cartPosition, float $maximumTaxRate): array
    {
        $taxRate = 0.0;
        $tax = 0.0;

        if (\array_key_exists('tax_rate', $cartPosition)) {
            $taxRate = (float) $cartPosition['tax_rate'];
        }

        if (\array_key_exists('tax', $cartPosition)) {
            if (\is_string($cartPosition['tax'])) {
                $tax = (float) str_replace(',', '.', (string) $cartPosition['tax']);
            } else {
                $tax = (float) $cartPosition['tax'];
            }
        }

        if (!empty($taxRate)) {
            return [
                $taxRate,
                $tax,
            ];
        }

        if (!empty($cartPosition['taxPercent'])) {
            return [
                $cartPosition['taxPercent'],
                $tax,
            ];
        }

        if ($cartPosition['modus'] == CartPositionsMode::VOUCHER) {
            return [
                $this->getVoucherTaxRate($cartPosition['ordernumber'], $maximumTaxRate) ?: 0.0,
                $tax,
            ];
        }

        if (!empty($this->automaticTaxMode)) {
            return [
                $maximumTaxRate,
                $tax,
            ];
        }

        return [
            (float) $this->discountTaxPreset,
            $tax,
        ];
    }

    /**
     * @param string $ordernumber    The voucher's ordernumber
     * @param float  $maximumTaxRate The highest tax rate from the order/basket we're looking at
     *
     * @throws DBALException
     */
    private function getVoucherTaxRate(string $ordernumber, float $maximumTaxRate): ?float
    {
        $voucherTaxMode = $this->connection->fetchOne(
            'SELECT `taxconfig` FROM `s_emarketing_vouchers` WHERE `ordercode` = :ordernumber',
            [
                'ordernumber' => $ordernumber,
            ]
        );

        if (empty($voucherTaxMode) || $voucherTaxMode === self::VOUCHER_TAX_MODE_DEFAULT) {
            // Old behaviour
            return (float) $this->voucherTaxPreset;
        }

        if ($voucherTaxMode === self::VOUCHER_TAX_MODE_AUTO) {
            // Determine tax rate automatically
            return $maximumTaxRate;
        }

        if ($voucherTaxMode === self::VOUCHER_TAX_MODE_NONE) {
            // No tax
            return 0.0;
        }

        /*
         * This case is a fallback, in case the tax mode associated with the
         * voucher is none of the known ones. The value will then be
         * interpreted as a tax ID and the corresponding tax rate is
         * selected based on it.
         */
        if ((int) $voucherTaxMode) {
            // Fix defined tax
            $taxRule = $this->contextService->getContext()->getTaxRule((int) $voucherTaxMode);

            if ($taxRule instanceof Tax) {
                return $taxRule->getTax();
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function taxSum(array $cart, float $maximumTaxRate): array
    {
        $result = $this->aggregateTaxes(
            $this->positionsTaxSum($cart, $maximumTaxRate) ?? [],
            $this->shippingCostsTaxSum($cart) ?? []
        );

        ksort($result, SORT_NUMERIC);

        return $result;
    }
}
