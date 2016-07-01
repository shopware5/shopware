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

namespace Shopware\Bundle\AccountBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AccountBundle\Service\Validator\CustomerValidatorInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\NumberRangeIncrementerInterface;
use Shopware\Components\Password\Manager;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config;
use Symfony\Component\Validator\Constraints\Collection;

class RegisterService implements RegisterServiceInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var CustomerValidatorInterface
     */
    private $validator;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Manager
     */
    private $passwordManager;

    /**
     * @var NumberRangeIncrementerInterface
     */
    private $numberIncrementer;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    /**
     * RegisterService constructor.
     * @param ModelManager $modelManager
     * @param CustomerValidatorInterface $validator
     * @param Shopware_Components_Config $config
     * @param Manager $passwordManager
     * @param NumberRangeIncrementerInterface $numberIncrementer
     * @param Connection $connection
     * @param AddressServiceInterface $addressService
     */
    public function __construct(
        ModelManager $modelManager,
        CustomerValidatorInterface $validator,
        Shopware_Components_Config $config,
        Manager $passwordManager,
        NumberRangeIncrementerInterface $numberIncrementer,
        Connection $connection,
        AddressServiceInterface $addressService
    ) {
        $this->modelManager = $modelManager;
        $this->validator = $validator;
        $this->config = $config;
        $this->passwordManager = $passwordManager;
        $this->numberIncrementer = $numberIncrementer;
        $this->connection = $connection;
        $this->addressService = $addressService;
    }

    /**
     * @param Shop $shop
     * @param Customer $customer
     * @param Address $billing
     * @param Address|null $shipping
     * @throws \Exception
     */
    public function register(
        Shop $shop,
        Customer $customer,
        Address $billing,
        Address $shipping = null
    ) {
        // Check whether a customer number must be created. This must be done before a transaction
        // is started, because the numberIncrementer uses a transaction itself.
        if (!$customer->getNumber() && $this->config->get('shopwareManagedCustomerNumbers')) {
            $customerNumber = $this->numberIncrementer->increment('user');
        }

        $this->modelManager->beginTransaction();

        try {
            $this->saveCustomer($shop, $customer, $customerNumber);

            $this->addressService->create($billing, $customer);
            $this->addressService->setDefaultBillingAddress($billing);

            if ($shipping !== null) {
                $this->addressService->create($shipping, $customer);
                $this->addressService->setDefaultShippingAddress($shipping);
            } else {
                $this->addressService->setDefaultShippingAddress($billing);
            }

            $this->saveReferer($customer);

            $this->modelManager->commit();
        } catch (\Exception $ex) {
            $this->modelManager->rollback();
            throw $ex;
        }
    }

    /**
     * @param Customer $customer
     */
    private function saveReferer(Customer $customer)
    {
        if (!$customer->getReferer()) {
            return;
        }

        $this->connection->insert('s_emarketing_referer', [
            'userID' => $customer->getId(),
            'referer' => $customer->getReferer(),
            'date' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @param Shop $shop
     * @param Customer $customer
     * @param string $customerNumber
     */
    private function saveCustomer(Shop $shop, Customer $customer, $customerNumber = null)
    {
        if ($customer->getValidation() !== ContextService::FALLBACK_CUSTOMER_GROUP) {
            $customer->setCustomerType(Customer::CUSTOMER_TYPE_BUSINESS);
        }

        $customer->setActive(true);

        //password validation
        if ($customer->getPassword()) {
            $customer->setEncoderName(
                $this->passwordManager->getDefaultPasswordEncoderName()
            );
            $customer->setPassword(
                $this->passwordManager->encodePassword(
                    $customer->getPassword(),
                    $customer->getEncoderName()
                )
            );
        }

        //account mode validation
        if ($customer->getAccountMode() == Customer::ACCOUNT_MODE_FAST_LOGIN) {
            $customer->setPassword(md5(uniqid(rand())));
            $customer->setEncoderName('md5');
        }

        if (!$customer->getPaymentId()) {
            $customer->setPaymentId($this->config->get('defaultPayment'));
        }

        $customer->setShop(
            $this->modelManager->find('Shopware\Models\Shop\Shop', $shop->getParentId())
        );

        $customer->setLanguageSubShop(
            $this->modelManager->find('Shopware\Models\Shop\Shop', $shop->getId())
        );

        $customer->setGroup(
            $this->modelManager->find('Shopware\Models\Customer\Group', $shop->getCustomerGroup()->getId())
        );

        if ($customer->getAffiliate()) {
            $customer->setAffiliate((int) $this->getPartnerId($customer));
        } else {
            $customer->setAffiliate(0);
        }

        if (!$customer->getNumber() && $customerNumber !== null) {
            $customer->setNumber($customerNumber);
        }

        $this->validator->validate($customer);
        $this->modelManager->persist($customer);
        $this->modelManager->flush($customer);
        $this->modelManager->refresh($customer);
    }

    /**
     * @param Customer $customer
     * @return int
     */
    private function getPartnerId(Customer $customer)
    {
        return (int) $this->connection->fetchColumn('SELECT id FROM s_emarketing_partner WHERE idcode = ?', [$customer->getAffiliate()]);
    }
}
