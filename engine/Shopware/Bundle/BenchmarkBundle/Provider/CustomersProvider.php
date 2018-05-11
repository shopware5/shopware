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

class CustomersProvider implements BenchmarkProviderInterface
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
        return 'customers';
    }

    public function getBenchmarkData()
    {
        return [
            'total' => $this->getTotalCustomers(),
            'turnOverPerAge' => $this->getTurnOverPerAge(),
            'turnOverPerGender' => $this->getTurnOverPerGender(),
            'sex' => $this->getCustomersBySex(),
            'countries' => $this->getCustomersByCountries(),
            'ageBySex' => $this->getAverageAgeBySex(),
        ];
    }

    /**
     * @return int
     */
    private function getTotalCustomers()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(customers.id)')
            ->from('s_user', 'customers')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return array
     */
    private function getTurnOverPerAge()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $turnOverPerAge = $queryBuilder->select('IFNULL(YEAR(customers.birthday), "unknown") birthYear, SUM(orders.invoice_amount)')
            ->from('s_order', 'orders')
            ->innerJoin('orders', 's_user', 'customers', 'customers.id = orders.userID')
            ->groupBy('YEAR(customers.birthday)')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $turnOverPerAge;
    }

    /**
     * @return array
     */
    private function getTurnOverPerGender()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $result = $queryBuilder->select('IFNULL(customers.salutation, "unknown"), SUM(orders.invoice_amount)')
            ->from('s_order', 'orders')
            ->innerJoin('orders', 's_user', 'customers', 'customers.id = orders.userID')
            ->groupBy('customers.salutation')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $turnOverPerAge['male'] = 0;
        $turnOverPerAge['female'] = 0;
        if ($result['mr']) {
            $turnOverPerAge['male'] = $result['mr'];
        }

        if ($result['mrs']) {
            $turnOverPerAge['female'] += $result['mrs'];
        }

        if ($result['ms']) {
            $turnOverPerAge['female'] += $result['ms'];
        }

        $turnOverPerAge['others'] = $result['unknown'];

        return $turnOverPerAge;
    }

    /**
     * @return int[]
     */
    private function getCustomersBySex()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $countsForSalutations = $queryBuilder->select('customers.salutation, COUNT(customers.id) as customerCount')
            ->from('s_user', 'customers')
            ->groupBy('customers.salutation')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $salutationCounts = [
            'other' => 0,
        ];
        foreach ($countsForSalutations as $key => $countsForSalutation) {
            if ($key === 'mr') {
                $salutationCounts['male'] = $countsForSalutation;
                continue;
            }

            if (in_array($key, ['ms', 'mrs'])) {
                $salutationCounts['female'] += $countsForSalutation;
                continue;
            }

            $salutationCounts['other'] += $countsForSalutation;
        }

        return $salutationCounts;
    }

    /**
     * @return array
     */
    private function getAverageAgeBySex()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $birthYears = $queryBuilder->select('customers.salutation, YEAR(customers.birthday) as birthYear')
            ->from('s_user', 'customers')
            ->execute()
            ->fetchAll();

        $totalAgeWomen = 0;
        $totalAgeMen = 0;
        $totalWomen = 0;
        $totalMen = 0;
        foreach ($birthYears as $birthYearData) {
            $salutation = $birthYearData['salutation'];
            $birthYear = $birthYearData['birthYear'];
            $dateNow = new \DateTime('now');
            $then = \DateTime::createFromFormat('Y', $birthYear);
            $age = $dateNow->diff($then)->y;

            if (in_array($salutation, ['ms', 'mrs'], true)) {
                ++$totalWomen;
                $totalAgeWomen += $age;
                continue;
            }

            if ($salutation !== 'mr') {
                continue;
            }

            ++$totalMen;
            $totalAgeMen += $age;
        }

        $averageMen = $totalAgeMen / $totalMen;
        $averageWomen = $totalAgeWomen / $totalWomen;

        return [
            'averageAgeMen' => is_nan($averageMen) ? 0 : $averageMen,
            'averageAgeWomen' => is_nan($averageWomen) ? 0 : $averageWomen,
        ];
    }

    /**
     * @return array
     */
    private function getCustomersByCountries()
    {
        $billingAddressQueryBuilder = $this->dbalConnection->createQueryBuilder();
        $shippingAddressQueryBuilder = $this->dbalConnection->createQueryBuilder();

        $billingAddressCountries = $billingAddressQueryBuilder->select('billingAddress.country_id as countryId, COUNT(customers.id) as customerCount')
            ->from('s_user', 'customers')
            ->innerJoin('customers', 's_user_addresses', 'billingAddress', 'billingAddress.id = customers.default_billing_address_id')
            ->groupBy('billingAddress.country_id')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $shippingAddressCountries = $shippingAddressQueryBuilder->select('shippingAddress.country_id as countryId, COUNT(customers.id) as customerCount')
            ->from('s_user', 'customers')
            ->innerJoin('customers', 's_user_addresses', 'shippingAddress', 'shippingAddress.id = customers.default_shipping_address_id')
            ->groupBy('shippingAddress.country_id')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return [
            'billing' => $this->addCountryNameKeys($billingAddressCountries),
            'shipping' => $this->addCountryNameKeys($shippingAddressCountries),
        ];
    }

    /**
     * @param array $addressCountries
     *
     * @return array
     */
    private function addCountryNameKeys(array $addressCountries)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $countryNames = $queryBuilder->select('country.id as countryId, country.countryname as countryName')
            ->from('s_core_countries', 'country')
            ->where('country.id IN (:countryIds)')
            ->setParameter(':countryIds', array_keys($addressCountries), Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($addressCountries as $key => $addressCountry) {
            $addressCountries[$countryNames[$key]] = $addressCountry;
            unset($addressCountries[$key]);
        }

        return $addressCountries;
    }
}
