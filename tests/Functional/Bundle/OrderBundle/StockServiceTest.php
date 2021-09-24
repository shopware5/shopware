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

namespace Shopware\Bundle\OrderBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Article\Detail as ProductVariant;
use Shopware\Models\Attribute\Order as OrderAttributes;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\DetailStatus;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Order\Status;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Payment\PaymentInstance;
use Shopware\Models\Shop\Currency;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class StockServiceTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    public function testTryToSaveExternalCreatedOrder(): void
    {
        $order = $this->createOrder();
        $modelManager = Shopware()->Container()->get('models');
        $modelManager->persist($order);
        foreach ($order->getPaymentInstances() as $instance) {
            $modelManager->persist($instance);
        }

        $modelManager->flush($order);

        $sql = 'SELECT * FROM s_order WHERE id = :orderId';
        $result = $modelManager->getConnection()->executeQuery($sql, ['orderId' => $order->getId()])->fetch();

        static::assertSame('1', $result['userID']);
        static::assertSame('131.81', $result['invoice_amount']);
        static::assertSame('110.76', $result['invoice_amount_net']);
        static::assertSame('2.8', $result['invoice_shipping']);
        static::assertSame('2.35', $result['invoice_shipping_net']);
        static::assertSame('19', $result['invoice_shipping_tax_rate']);
        static::assertSame('4', $result['paymentID']);
        static::assertSame('1', $result['language']);
        static::assertSame('9', $result['dispatchID']);
        static::assertSame('EUR', $result['currency']);
        static::assertSame('1', $result['currencyFactor']);
        static::assertSame('1', $result['subshopID']);
        static::assertSame('Backend', $result['deviceType']);
    }

    private function createOrder(): Order
    {
        $modelManager = Shopware()->Container()->get('models');

        $order = new Order();

        $order->setNumber(
            (string) Shopware()->Container()->get('shopware.number_range_incrementer')->increment('invoice')
        );

        $dispatch = $modelManager->find(Dispatch::class, 9);
        static::assertNotNull($dispatch);
        $order->setDispatch($dispatch);

        /** @var Customer $customer */
        $customer = $modelManager->find(Customer::class, 1);
        static::assertNotNull($customer);
        $order->setCustomer($customer);

        $payment = $modelManager->find(Payment::class, 4);
        static::assertNotNull($payment);
        $order->setPayment($payment);

        $orderStatus = $modelManager->getReference(Status::class, 0);
        static::assertNotNull($orderStatus);
        $order->setOrderStatus($orderStatus);

        $paymentStatus = $modelManager->getReference(Status::class, 17);
        static::assertNotNull($paymentStatus);
        $order->setPaymentStatus($paymentStatus);

        $languageSubShop = $modelManager->find(Shop::class, 1);
        static::assertNotNull($languageSubShop);
        $order->setLanguageSubShop($languageSubShop);

        $currency = $modelManager->getReference(Currency::class, 1);
        static::assertNotNull($currency);
        $order->setCurrencyFactor($currency->getFactor());
        $order->setCurrency($currency->getCurrency());

        $billing = new Billing();
        $billingAddress = $customer->getDefaultBillingAddress();
        static::assertNotNull($billingAddress);
        $billing->fromAddress($billingAddress);
        $order->setBilling($billing);

        $shipping = new Shipping();
        $shippingAddress = $customer->getDefaultShippingAddress();
        static::assertNotNull($shippingAddress);
        $shipping->fromAddress($shippingAddress);
        $order->setShipping($shipping);

        $attributes = new OrderAttributes();
        $attributes->setOrder($order);
        $order->setAttribute($attributes);

        $details = [
            $this->createDetailPosition('SW10178', 3),
            $this->createDetailPosition('SW10123.4', 4),
        ];

        $order->setDetails($details);

        $order->setInvoiceShippingNet(2.35);
        $order->setInvoiceShipping(2.8);
        $order->setInvoiceShippingTaxRate(19);
        $order->setInvoiceAmount(131.81);
        $order->setInvoiceAmountNet(110.76);

        $order->setShop($customer->getShop());
        $order->setOrderTime(new \DateTime());
        $order->setDeviceType('Backend');
        $order->setTransactionId('');
        $order->setComment('');
        $order->setCustomerComment('');
        $order->setInternalComment('');
        $order->setTemporaryId('');
        $order->setReferer('');
        $order->setTrackingCode('');
        $order->setRemoteAddress('');
        $order->setNet(0);
        $order->setTaxFree(0);

        $order->setPaymentInstances(new ArrayCollection([$this->createPaymentInstance($order)]));

        return $order;
    }

    private function createDetailPosition(string $ordernumber, int $quantity): Detail
    {
        $modelManager = Shopware()->Container()->get('models');
        $detail = new Detail();

        $articleDetail = $modelManager->getRepository(ProductVariant::class)->findOneBy(['number' => $ordernumber]);
        static::assertInstanceOf(ProductVariant::class, $articleDetail);
        $article = $articleDetail->getArticle();

        $tax = $modelManager->find(Tax::class, 1);
        static::assertInstanceOf(Tax::class, $tax);
        $detail->setTax($tax);

        $taxRate = (float) $tax->getTax();
        $detail->setTaxRate($taxRate);
        $detail->setEsdArticle(0);

        /** @var DetailStatus $detailStatus */
        $detailStatus = $modelManager->find(DetailStatus::class, 0);
        static::assertNotNull($detailStatus);
        $detail->setStatus($detailStatus);

        $detail->setArticleId($article->getId());
        $detail->setArticleDetail($articleDetail);

        $name = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($ordernumber);
        static::assertIsString($name);
        $detail->setArticleName($name);

        $detail->setArticleNumber($ordernumber);
        $detail->setPrice($articleDetail->getPrices()->first()->getPrice());
        $detail->setQuantity($quantity);
        $detail->setShipped(0);
        $detail->setUnit('0');
        $detail->setPackUnit($articleDetail->getPackUnit());
        $detail->setEan($articleDetail->getEan());

        return $detail;
    }

    private function createPaymentInstance(Order $orderModel): PaymentInstance
    {
        $paymentInstance = new PaymentInstance();

        $paymentInstance->setPaymentMean($orderModel->getPayment());
        $paymentInstance->setOrder($orderModel);
        $paymentInstance->setCreatedAt($orderModel->getOrderTime());
        $paymentInstance->setCustomer($orderModel->getCustomer());
        static::assertNotNull($orderModel->getBilling());
        $paymentInstance->setFirstName($orderModel->getBilling()->getFirstName());
        $paymentInstance->setLastName($orderModel->getBilling()->getLastName());
        $paymentInstance->setAddress($orderModel->getBilling()->getStreet());
        $paymentInstance->setZipCode($orderModel->getBilling()->getZipCode());
        $paymentInstance->setCity($orderModel->getBilling()->getCity());
        $paymentInstance->setAmount($orderModel->getInvoiceAmount());

        return $paymentInstance;
    }
}
