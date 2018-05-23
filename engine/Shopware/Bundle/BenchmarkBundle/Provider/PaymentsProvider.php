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

class PaymentsProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'payments';
    }

    /**
     * @return array
     */
    public function getBenchmarkData()
    {
        return [
            'activePayments' => $this->getTotalActivePayments(),
            'paymentsWithSurcharge' => $this->getPaymentsWithSurcharges(),
            'paymentsWithReduction' => $this->getPaymentsWithReduction(),
            'paymentsWithPercentagePrice' => $this->getPaymentsWithPercentagePrice(),
            'paymentsWithAbsolutePrice' => $this->getPaymentsWithAbsolutePrice(),
            'paymentUsages' => $this->getPaymentUsages(),
        ];
    }

    /**
     * @return int
     */
    private function getTotalActivePayments()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(payments.id)')
            ->from('s_core_paymentmeans', 'payments')
            ->where('payments.active = 1')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getPaymentsWithSurcharges()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $payments = $queryBuilder->select('payments.debit_percent, payments.surcharge, payments.surchargestring')
            ->from('s_core_paymentmeans', 'payments')
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
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $payments = $queryBuilder->select('payments.debit_percent, payments.surcharge, payments.surchargestring')
            ->from('s_core_paymentmeans', 'payments')
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
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(payments.id)')
            ->from('s_core_paymentmeans', 'payments')
            ->where('payments.debit_percent != 0')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getPaymentsWithAbsolutePrice()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(payments.id)')
            ->from('s_core_paymentmeans', 'payments')
            ->where("payments.surcharge != 0 OR payments.surchargestring != ''")
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return array
     */
    private function getPaymentUsages()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('orders.paymentID, payments.name, COUNT(orders.id) as usages')
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_core_paymentmeans', 'payments', 'payments.id = orders.paymentID')
            ->groupBy('orders.paymentID')
            ->orderBy('usages', 'DESC')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
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
}
