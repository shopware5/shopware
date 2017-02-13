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

namespace Shopware\Bundle\CustomerSearchBundle\Commands;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\Console\ConsoleProgressHelper;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Commands
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SearchIndexPopulate extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:customer:stream:index:populate')
            ->setDescription('Reindex all customer streams.')
            ->addOption('streamId', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $this->createQuery();

        $gateway = $this->container->get('shopware_customer_search.customer_list_gateway');

        $helper = new ConsoleProgressHelper($output);
        $helper->start($query->fetchCount(), 'Start indexing stream search data');

        $this->container->get('dbal_connection')->beginTransaction();

        $this->container->get('dbal_connection')->executeUpdate(
            "DELETE FROM s_customer_search_index"
        );

        $insert = $this->createInsertQuery();

        while ($ids = $query->fetch()) {
            $customers = $gateway->getList($ids);

            foreach ($customers as $customer) {
                $insert->execute($this->buildData($customer));
            }

            $helper->advance(count($ids));
        }

        $this->container->get('dbal_connection')->commit();

        $helper->finish();
    }

    private function createQuery()
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select(['id', 'id']);
        $query->from('s_user', 'u');
        $query->where('u.id > :lastId');
        $query->setParameter(':lastId', 0);
        $query->orderBy('u.id', 'ASC');
        $query->setMaxResults(100);
        return new LastIdQuery($query);
    }

    private function createInsertQuery()
    {
        return $this->container->get('dbal_connection')->prepare(
            "INSERT INTO s_customer_search_index (
                id,
                email,
                active,
                accountmode,
                firstlogin,
                newsletter,
                shopId,
                default_billing_address_id,
                title,
                salutation,
                firstname,
                lastname,
                birthday,
                customernumber,
                customerGroupId,
                customerGroup,
                paymentId,
                payment,
                shop,
                company,
                department,
                street,
                zipcode,
                city,
                phone,
                additional_address_line1,
                additional_address_line2,
                countryId,
                country,
                stateId,
                state,
                age,
                count_orders ,
                invoice_amount_sum,
                invoice_amount_avg,
                invoice_amount_min,
                invoice_amount_max,
                first_order_time,
                last_order_time,
                product_avg,
                products,
                categories,
                manufacturers,
                interests
            ) VALUES (
                :id,
                :email,
                :active,
                :accountmode,
                :firstlogin,
                :newsletter,
                :shopId,
                :default_billing_address_id,
                :title,
                :salutation,
                :firstname,
                :lastname,
                :birthday,
                :customernumber,
                :customerGroupId,
                :customerGroup,
                :paymentId,
                :payment,
                :shop,
                :company,
                :department,
                :street,
                :zipcode,
                :city,
                :phone,
                :additional_address_line1,
                :additional_address_line2,
                :countryId,
                :country,
                :stateId,
                :state,
                :age,
                :count_orders,
                :invoice_amount_sum,
                :invoice_amount_avg,
                :invoice_amount_min,
                :invoice_amount_max,
                :first_order_time,
                :last_order_time,
                :product_avg,
                :products,
                :categories,
                :manufacturers,
                :interests)
      ");
    }


    private function implodeUnique($array)
    {
        if (empty($array)) {
            return null;
        }

        return '||' . implode('||', array_keys(array_flip($array))) . '||';
    }

    /**
     * @param $customer
     * @return array
     */
    protected function buildData($customer)
    {
        $data = [
            'id' => $customer['id'],
            'email' => $customer['email'],
            'active' => $customer['active'],
            'accountmode' => $customer['accountmode'],
            'firstlogin' => $customer['firstlogin'],
            'newsletter' => $customer['newsletter'],
            'shopId' => $customer['shopId'],
            'default_billing_address_id' => $customer['default_billing_address_id'],
            'title' => $customer['title'],
            'salutation' => $customer['salutation'],
            'firstname' => $customer['firstname'],
            'lastname' => $customer['lastname'],
            'birthday' => $customer['birthday'],
            'customernumber' => $customer['customernumber'],
            'customerGroupId' => $customer['customerGroupId'],
            'customerGroup' => $customer['customerGroup'],
            'paymentId' => $customer['paymentId'],
            'payment' => $customer['payment'],
            'shop' => $customer['shop'],
            'company' => $customer['company'],
            'department' => $customer['department'],
            'street' => $customer['street'],
            'zipcode' => $customer['zipcode'],
            'city' => $customer['city'],
            'phone' => $customer['phone'],
            'additional_address_line1' => $customer['additional_address_line1'],
            'additional_address_line2' => $customer['additional_address_line2'],
            'countryId' => $customer['countryId'],
            'country' => $customer['country'],
            'stateId' => $customer['stateId'],
            'state' => $customer['state'],
            'age' => $customer['age'],
            'count_orders' => $customer['aggregation']['count_orders'],
            'product_avg' => $customer['aggregation']['product_avg'],
            'invoice_amount_sum' => $customer['aggregation']['invoice_amount_sum'],
            'invoice_amount_avg' => $customer['aggregation']['invoice_amount_avg'],
            'invoice_amount_min' => $customer['aggregation']['invoice_amount_min'],
            'invoice_amount_max' => $customer['aggregation']['invoice_amount_max'],
            'first_order_time' => $customer['aggregation']['first_order_time'],
            'last_order_time' => $customer['aggregation']['last_order_time'],
            'products' => $this->implodeUnique(array_column($customer['interests'], 'articleordernumber')),
            'categories' => $this->implodeUnique(array_column($customer['interests'], 'categoryId')),
            'manufacturers' => $this->implodeUnique(array_column($customer['interests'], 'manufacturerId')),
            'interests' => json_encode(array_slice($customer['interests'], 0, 10))
        ];

        return $data;
    }
}
