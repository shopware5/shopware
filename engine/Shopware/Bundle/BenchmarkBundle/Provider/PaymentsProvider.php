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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\BenchmarkBundle\Service\MatcherService;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PaymentsProvider implements BenchmarkProviderInterface
{
    private const NAME = 'payments';

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @var array
     */
    private $paymentIds = [];

    /**
     * @var MatcherService
     */
    private $matcherService;

    public function __construct(Connection $dbalConnection, MatcherService $matcherService)
    {
        $this->dbalConnection = $dbalConnection;
        $this->matcherService = $matcherService;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        $this->shopId = $shopContext->getShop()->getId();
        $this->paymentIds = [];

        return [
            'activePayments' => $this->getTotalActivePayments(),
            'paymentsWithSurcharge' => $this->getPaymentsWithSurcharges(),
            'paymentsWithReduction' => $this->getPaymentsWithReduction(),
            'paymentsWithPercentagePrice' => $this->getPaymentsWithPercentagePrice(),
            'paymentsWithAbsolutePrice' => $this->getPaymentsWithAbsolutePrice(),
            'paymentUsages' => $this->getMatchedPaymentUsages(),
        ];
    }

    private function getMatchedPaymentUsages()
    {
        $paymentUsages = $this->getPaymentUsages();

        $matches = [];
        foreach ($paymentUsages as $paymentName => $usages) {
            $match = $this->matcherService->matchString($paymentName);

            if (!isset($matches[$match])) {
                $matches[$match] = [
                    'name' => $match,
                    'usages' => 0,
                ];
            }

            $matches[$match]['usages'] += $usages;
        }

        return array_values($matches);
    }

    /**
     * @return array
     */
    private function getPaymentUsages()
    {
        $paymentIds = $this->getPossiblePaymentIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('payments.name, COUNT(orders.id) as usages')
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_core_paymentmeans', 'payments', 'payments.id = orders.paymentID')
            ->where('payments.id IN (:paymentIds)')
            ->setParameter(':paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY)
            ->groupBy('orders.paymentID')
            ->orderBy('usages', 'DESC')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @return int
     */
    private function getTotalActivePayments()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $paymentIds = $this->getPossiblePaymentIds();

        return (int) $queryBuilder->select('COUNT(payments.id)')
            ->from('s_core_paymentmeans', 'payments')
            ->where('payments.active = 1')
            ->andWhere('payments.id IN (:paymentIds)')
            ->setParameter(':paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getPaymentsWithSurcharges()
    {
        $paymentIds = $this->getPossiblePaymentIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $payments = $queryBuilder->select('payments.debit_percent, payments.surcharge, payments.surchargestring')
            ->from('s_core_paymentmeans', 'payments')
            ->where('payments.id IN (:paymentIds)')
            ->setParameter(':paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll();

        foreach ($payments as $key => $payment) {
            if ($payment['surcharge'] > 0 || $payment['debit_percent'] > 0 || $this->hasSurchargeInCountries($payment['surchargestring'])) {
                continue;
            }

            unset($payments[$key]);
        }

        return count($payments);
    }

    /**
     * @return int
     */
    private function getPaymentsWithReduction()
    {
        $paymentIds = $this->getPossiblePaymentIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $payments = $queryBuilder->select('payments.debit_percent, payments.surcharge, payments.surchargestring')
            ->from('s_core_paymentmeans', 'payments')
            ->where('payments.id IN (:paymentIds)')
            ->setParameter(':paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll();

        foreach ($payments as $key => $payment) {
            if ($payment['surcharge'] < 0 || $payment['debit_percent'] < 0 || $this->hasReductionInCountries($payment['surchargestring'])) {
                continue;
            }

            unset($payments[$key]);
        }

        return count($payments);
    }

    /**
     * @return int
     */
    private function getPaymentsWithPercentagePrice()
    {
        $paymentIds = $this->getPossiblePaymentIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(payments.id)')
            ->from('s_core_paymentmeans', 'payments')
            ->where('payments.debit_percent != 0')
            ->andWhere('payments.id IN (:paymentIds)')
            ->setParameter(':paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getPaymentsWithAbsolutePrice()
    {
        $paymentIds = $this->getPossiblePaymentIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(payments.id)')
            ->from('s_core_paymentmeans', 'payments')
            ->where("payments.surcharge != 0 OR payments.surchargestring != ''")
            ->andWhere('payments.id IN (:paymentIds)')
            ->setParameter(':paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param string $surchargeString
     *
     * @return bool
     */
    private function hasSurchargeInCountries($surchargeString)
    {
        if (!$surchargeString) {
            return false;
        }

        return $this->getSurcharge($surchargeString)[0];
    }

    /**
     * @param string $surchargeString
     *
     * @return bool
     */
    private function hasReductionInCountries($surchargeString)
    {
        if (!$surchargeString) {
            return false;
        }

        return $this->getSurcharge($surchargeString)[1];
    }

    /**
     * @param string $surchargeString
     *
     * @return bool[]
     */
    private function getSurcharge($surchargeString)
    {
        $pricePerCountry = explode(';', $surchargeString);

        $hasReduction = false;
        $hasSurcharge = false;
        foreach ($pricePerCountry as $countryPrice) {
            $countryPrice = explode(':', $countryPrice)[1];
            if ($countryPrice < 0) {
                $hasReduction = true;
            }

            if ($countryPrice > 0) {
                $hasSurcharge = true;
            }
        }

        return [$hasSurcharge, $hasReduction];
    }

    /**
     * @return array
     */
    private function getPossiblePaymentIds()
    {
        if (array_key_exists($this->shopId, $this->paymentIds)) {
            return $this->paymentIds[$this->shopId];
        }

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $this->paymentIds[$this->shopId] = $queryBuilder->select('DISTINCT payment.id')
            ->from('s_core_paymentmeans', 'payment')
            ->leftJoin('payment', 's_core_paymentmeans_subshops', 'paymentShop', 'paymentShop.paymentID = payment.id')
            ->where('paymentShop.subshopID IS NULL or paymentShop.subshopID = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        return $this->paymentIds[$this->shopId];
    }
}
