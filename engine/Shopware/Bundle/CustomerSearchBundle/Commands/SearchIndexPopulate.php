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

use Shopware\Bundle\CustomerSearchBundle\CustomerStream\AnalyzedCustomerStruct;
use Shopware\Bundle\CustomerSearchBundle\Gateway\InterestsStruct;
use Shopware\Bundle\ESIndexingBundle\Console\ConsoleProgressHelper;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
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

        $provider = $this->container->get('shopware_bundle_customer_search.customer_stream.customer_provider');

        $helper = new ConsoleProgressHelper($output);
        $helper->start($query->fetchCount(), 'Start indexing stream search data');

        $this->container->get('dbal_connection')->beginTransaction();

        $this->container->get('dbal_connection')->executeUpdate('DELETE FROM s_customer_search_index');

        $indexer = $this->container->get('shopware_bundle_customer_search.customer_stream.search_indexer');

        while ($ids = $query->fetch()) {
            $indexer->populate($ids);
            $helper->advance(count($ids));
        }

        $this->container->get('dbal_connection')->commit();

        $helper->finish();
    }

    /**
     * @param $customer
     *
     * @return array
     */
    protected function buildData(AnalyzedCustomerStruct $customer)
    {
        $data = [
            'id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'active' => $customer->getActive(),
            'accountmode' => $customer->getAccountMode(),
            'firstlogin' => $this->formatDate($customer->getFirstLogin()),
            'newsletter' => $customer->isNewsletter(),
            'shopId' => $customer->getShopId(),
            'default_billing_address_id' => $customer->getDefaultBillingAddressId(),
            'title' => $customer->getTitle(),
            'salutation' => $customer->getSalutation(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'birthday' => $this->formatDate($customer->getBirthday()),
            'customernumber' => $customer->getNumber(),
            'customerGroupId' => $customer->getCustomerGroup()->getId(),
            'customerGroup' => $customer->getCustomerGroup()->getName(),
            'paymentId' => $customer->getPaymentId(),
            'payment' => $customer->getPayment() ? $customer->getPayment()->getName() : null,
            'shop' => $customer->getShopId(),
            'company' => $customer->getBillingAddress()->getCompany(),
            'department' => $customer->getBillingAddress()->getDepartment(),
            'street' => $customer->getBillingAddress()->getStreet(),
            'zipcode' => $customer->getBillingAddress()->getZipcode(),
            'city' => $customer->getBillingAddress()->getCity(),
            'phone' => $customer->getBillingAddress()->getPhone(),
            'additional_address_line1' => $customer->getBillingAddress()->getAdditionalAddressLine1(),
            'additional_address_line2' => $customer->getBillingAddress()->getAdditionalAddressLine2(),
            'countryId' => $customer->getBillingAddress()->getCountryId(),
            'country' => $customer->getBillingAddress()->getCountry()->getName(),
            'stateId' => $customer->getBillingAddress()->getStateId(),
            'state' => $customer->getBillingAddress()->getState() ? $customer->getBillingAddress()->getState()->getName() : null,
            'age' => $customer->getAge(),
            'count_orders' => $customer->getOrderInformation()->getOrderCount(),
            'product_avg' => $customer->getOrderInformation()->getAvgProductPrice(),
            'invoice_amount_sum' => $customer->getOrderInformation()->getTotalAmount(),
            'invoice_amount_avg' => $customer->getOrderInformation()->getAvgAmount(),
            'invoice_amount_min' => $customer->getOrderInformation()->getMinAmount(),
            'invoice_amount_max' => $customer->getOrderInformation()->getMaxAmount(),
            'first_order_time' => $this->formatDate($customer->getOrderInformation()->getFirstOrderTime()),
            'last_order_time' => $this->formatDate($customer->getOrderInformation()->getLastOrderTime()),
            'products' => $this->implodeUnique(
                array_map(function (InterestsStruct $interest) {
                    return $interest->getProductNumber();
                }, $customer->getInterests())
            ),
            'categories' => $this->implodeUnique(
                array_map(function (InterestsStruct $interest) {
                    return $interest->getCategoryId();
                }, $customer->getInterests())
            ),
            'manufacturers' => $this->implodeUnique(
                array_map(function (InterestsStruct $interest) {
                    return $interest->getManufacturerId();
                }, $customer->getInterests())
            ),
            'interests' => json_encode(array_slice($customer->getInterests(), 0, 5)),
            'newest_interests' => json_encode(array_slice($customer->getNewestInterests(), 0, 5)),
        ];

        return $data;
    }

    private function createQuery()
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select(['id', 'id']);
        $query->from('s_user', 'u');
        $query->where('u.id > :lastId');
        $query->setParameter(':lastId', 0);
        $query->orderBy('u.id', 'ASC');
        $query->setMaxResults(50);

        return new LastIdQuery($query);
    }

    private function createInsertQuery()
    {
        return $this->container->get('dbal_connection')->prepare(
            'INSERT INTO s_customer_search_index (
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
                interests,
                newest_interests
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
                :interests,
                :newest_interests
            )
      ');
    }

    private function implodeUnique($array)
    {
        if (empty($array)) {
            return null;
        }

        return '||' . implode('||', array_keys(array_flip($array))) . '||';
    }

    /**
     * @param \DateTime|null $date
     * @param string         $format
     *
     * @return null|string
     */
    private function formatDate(\DateTime $date = null, $format = 'Y-m-d H:i:s')
    {
        if ($date === null) {
            return null;
        }

        return $date->format($format);
    }
}
