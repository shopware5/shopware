<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\OrderBundle\Service;

use Shopware\Components\Cart\NetRounding\RoundingInterface;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Order;

class CalculationService implements CalculationServiceInterface
{
    /**
     * @var RoundingInterface
     */
    private $rounding;

    public function __construct(RoundingInterface $rounding)
    {
        $this->rounding = $rounding;
    }

    public function recalculateOrderTotals(Order $order): void
    {
        $invoiceAmount = 0;
        $invoiceAmountNet = 0;

        // Iterate order details to recalculate the amount.
        /** @var Detail $detail */
        foreach ($order->getDetails() as $detail) {
            $price = round($detail->getPrice(), 2);

            $invoiceAmount += $price * $detail->getQuantity();

            $tax = $detail->getTax();

            $taxValue = $detail->getTaxRate();

            // Additional tax checks required for sw-2238, sw-2903 and sw-3164
            if ($tax && $tax->getId() !== 0 && $tax->getId() !== null && $tax->getTax() !== null) {
                $taxValue = (float) $tax->getTax();
            }

            if ($order->getNet()) {
                $invoiceAmountNet += $this->rounding->round($price, $taxValue, $detail->getQuantity());
            } else {
                $invoiceAmountNet += round(($price * $detail->getQuantity()) / (100 + $taxValue) * 100, 2);
            }
        }

        if ($order->getTaxFree()) {
            $order->setInvoiceAmountNet($invoiceAmount + $order->getInvoiceShippingNet());
            $order->setInvoiceAmount($order->getInvoiceAmountNet());
        } elseif ($order->getNet()) {
            $order->setInvoiceAmountNet($invoiceAmount + $order->getInvoiceShippingNet());
            $order->setInvoiceAmount($invoiceAmountNet + $order->getInvoiceShipping());
        } else {
            $order->setInvoiceAmount($invoiceAmount + $order->getInvoiceShipping());
            $order->setInvoiceAmountNet($invoiceAmountNet + $order->getInvoiceShippingNet());
        }
    }
}
