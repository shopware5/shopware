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
use Shopware\Bundle\BenchmarkBundle\BatchableProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CustomersProvider implements BatchableProviderInterface
{
    private const NAME = 'customers';

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var int
     */
    private $shopId;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext, $batchSize = null)
    {
        $this->shopId = $shopContext->getShop()->getId();

        return [
            'list' => $this->getCustomersList($batchSize),
        ];
    }

    /**
     * @param int $batchSize
     *
     * @return array
     */
    private function getCustomersList($batchSize = null)
    {
        $config = $this->getConfig();
        $batch = (int) $config['batch_size'];
        $lastCustomerId = $config['last_customer_id'];

        if ($batchSize !== null) {
            $batch = $batchSize;
        }

        $customers = $this->getCustomersBasicList($batch, $lastCustomerId);

        $customerIds = array_keys($customers);

        foreach ($this->getTurnOverPerCustomer($customerIds) as $customerId => $turnOver) {
            $customers[$customerId]['turnOver'] = $turnOver;
        }

        $customers = array_map([$this, 'matchGenders'], array_values($customers));

        $customers = array_map(function ($item) {
            $item['hasNewsletter'] = (bool) $item['hasNewsletter'];
            $item['registered'] = (bool) $item['registered'];
            $item['turnOver'] = (float) $item['turnOver'];

            if ($item['birthMonth']) {
                $item['birthMonth'] = (int) $item['birthMonth'];
                $item['birthYear'] = (int) $item['birthYear'];
            } else {
                $item['birthMonth'] = 0;
                $item['birthYear'] = 0;
            }

            return $item;
        }, $customers);

        return $customers;
    }

    /**
     * @param int $batch
     * @param int $lastCustomerId
     *
     * @return array
     */
    private function getCustomersBasicList($batch, $lastCustomerId)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select([
                'customer.id',
                'customer.id as customerId',
                'customer.accountmode = 0 as registered',
                'YEAR(customer.birthday) as birthYear',
                'MONTH(customer.birthday) as birthMonth',
                'customer.salutation as gender',
                'customer.firstlogin as registerDate',
                'newsletter.id IS NOT NULL as hasNewsletter',
                '0 as turnOver',
            ])
            ->from('s_user', 'customer')
            ->leftJoin('customer', 's_campaigns_mailaddresses', 'newsletter', 'newsletter.email = customer.email AND newsletter.customer = 1')
            ->where('customer.id > :lastCustomerId')
            ->andWhere('customer.subshopID = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->setParameter(':lastCustomerId', $lastCustomerId)
            ->orderBy('customer.id')
            ->setMaxResults($batch)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /**
     * @param int[] $customerIds
     *
     * @return array
     */
    private function getTurnOverPerCustomer(array $customerIds)
    {
        $turnOverQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $turnOverQueryBuilder->select([
                'orders.userID',
                'SUM(orders.invoice_amount)',
            ])
            ->from('s_order', 'orders')
            ->where('orders.userID IN (:customerIds)')
            ->groupBy('orders.userID')
            ->setParameter(':customerIds', $customerIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @return array
     */
    private function matchGenders(array $customer)
    {
        if ($customer['gender'] === 'mr') {
            $customer['gender'] = 'male';

            return $customer;
        }

        if (in_array($customer['gender'], ['mrs', 'ms'])) {
            $customer['gender'] = 'female';

            return $customer;
        }

        $customer['gender'] = 'unknown';

        return $customer;
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        $configsQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $configsQueryBuilder->select('configs.*')
            ->from('s_benchmark_config', 'configs')
            ->where('configs.shop_id = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->execute()
            ->fetch();
    }
}
