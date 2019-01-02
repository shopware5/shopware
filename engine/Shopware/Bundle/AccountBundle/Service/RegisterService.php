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
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\AccountBundle\Service\Validator\CustomerValidatorInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\NumberRangeIncrementerInterface;
use Shopware\Components\Password\Manager;
use Shopware\Components\Random;
use Shopware\Components\Routing\Context;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config;

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
     *
     * @param ModelManager                    $modelManager
     * @param CustomerValidatorInterface      $validator
     * @param Shopware_Components_Config      $config
     * @param Manager                         $passwordManager
     * @param NumberRangeIncrementerInterface $numberIncrementer
     * @param Connection                      $connection
     * @param AddressServiceInterface         $addressService
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
     * @param Shop         $shop
     * @param Customer     $customer
     * @param Address      $billing
     * @param Address|null $shipping
     *
     * @throws \Exception
     */
    public function register(
        Shop $shop,
        Customer $customer,
        Address $billing,
        Address $shipping = null
    ) {
        $this->modelManager->beginTransaction();
        try {
            $this->saveCustomer($shop, $customer);
            if (
                ($optinAttribute = $shop->getAttribute('sendOptinMail')) !== null &&
                $optinAttribute->get('sendOptinMail') === true &&
                $customer->getDoubleOptinRegister() &&
                $customer->getDoubleOptinConfirmDate() === null
            ) {
                $hash = Random::getAlphanumericString(32);

                $this->doubleOptInSaveHash($customer, $hash);
                $this->doubleOptInVerificationMail($shop, $customer, $hash);
            }

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
            'date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Shop     $shop
     * @param Customer $customer
     */
    private function saveCustomer(Shop $shop, Customer $customer)
    {
        if ($customer->getValidation() !== ContextService::FALLBACK_CUSTOMER_GROUP) {
            $customer->setCustomerType(Customer::CUSTOMER_TYPE_BUSINESS);
        }

        $customerConfirmed = !$customer->getDoubleOptinRegister() || $customer->getDoubleOptinConfirmDate() !== null;
        $customer->setActive($customerConfirmed);

        if (!$customerConfirmed) {
            // Reset login information if Double-Opt-In is active
            $customer->setFirstLogin('0000-00-00');
            $customer->setLastLogin('0000-00-00');
            $customer->setDoubleOptinEmailSentDate(new \DateTime());
        }

        // Password validation
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

        // Account mode validation
        if ($customer->getAccountMode() == Customer::ACCOUNT_MODE_FAST_LOGIN) {
            $customer->setPassword(md5(uniqid(rand())));
            $customer->setEncoderName('md5');
        }

        if (!$customer->getPaymentId()) {
            $customer->setPaymentId($this->config->get('defaultPayment'));
        }

        $customer->setShop(
            $this->modelManager->find(\Shopware\Models\Shop\Shop::class, $shop->getParentId())
        );

        $customer->setLanguageSubShop(
            $this->modelManager->find(\Shopware\Models\Shop\Shop::class, $shop->getId())
        );

        if ($customer->getGroup() === null) {
            $customer->setGroup(
                $this->modelManager->find(\Shopware\Models\Customer\Group::class, $shop->getCustomerGroup()->getId())
            );
        }

        if ($customer->getAffiliate()) {
            $customer->setAffiliate((int) $this->getPartnerId($customer));
        } else {
            $customer->setAffiliate(0);
        }

        if (!$customer->getNumber() && $this->config->get('shopwareManagedCustomerNumbers')) {
            $customer->setNumber($this->numberIncrementer->increment('user'));
        }

        $this->validator->validate($customer);
        $this->modelManager->persist($customer);
        $this->modelManager->flush($customer);
        $this->modelManager->refresh($customer);
    }

    /**
     * @param Customer $customer
     *
     * @return int
     */
    private function getPartnerId(Customer $customer)
    {
        return (int) $this->connection->fetchColumn('SELECT id FROM s_emarketing_partner WHERE idcode = ?', [$customer->getAffiliate()]);
    }

    /**
     * @param Shop     $shop
     * @param Customer $customer
     * @param string   $hash
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function doubleOptInVerificationMail(Shop $shop, Customer $customer, $hash)
    {
        $container = Shopware()->Container();
        $router = Shopware()->Front()->Router();

        $router->setContext(
            Context::createFromShop(
                $this->modelManager->getRepository(\Shopware\Models\Shop\Shop::class)->getById($shop->getId()),
                $this->config
            )
        );
        $link = $router->assemble([
            'sViewport' => 'register',
            'action' => 'confirmValidation',
            'sConfirmation' => $hash,
        ]);

        $context = [
            'sConfirmLink' => $link,
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'salutation' => $customer->getSalutation(),
        ];

        $context = $container->get('events')->filter(
            'Shopware_Controllers_Frontend_RegisterService_DoubleOptIn_ConfirmationMail',
            $context,
            [
                'customer' => $customer,
            ]
        );

        if (((int) $customer->getAccountMode()) === 1) {
            $mail = $container->get('templatemail')->createMail('sOPTINREGISTERACCOUNTLESS', $context);
        } else {
            $mail = $container->get('templatemail')->createMail('sOPTINREGISTER', $context);
        }
        $mail->addTo($customer->getEmail());
        $mail->send();
    }

    /**
     * @param Customer $customer
     * @param string   $hash
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function doubleOptInSaveHash(Customer $customer, $hash)
    {
        /** @var Request $request */
        $request = Shopware()->Container()->get('front')->Request();
        $fromCheckout = ($request && $request->getParam('sTarget') === 'checkout');

        $sql = "INSERT INTO `s_core_optin` (`type`, `datum`, `hash`, `data`)
                VALUES ('swRegister', ?, ?, ?)";

        // Minimal billing data for Mailtemplates
        $storedData = [
            'customerId' => $customer->getId(),
            'register' => [
                'billing' => [
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastname(),
                    'salutation' => $customer->getSalutation(),
                    ],
                ],
            'fromCheckout' => $fromCheckout,
        ];

        $this->connection->executeQuery($sql, [$customer->getDoubleOptinEmailSentDate()->format('Y-m-d H:i:s'), $hash, serialize($storedData)]);
    }
}
