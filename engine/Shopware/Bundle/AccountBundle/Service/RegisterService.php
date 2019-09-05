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
use Shopware\Models\Customer\Group;
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

            $this->addressService->create($billing, $customer);
            $this->addressService->setDefaultBillingAddress($billing);

            if ($shipping !== null) {
                $this->addressService->create($shipping, $customer);
                $this->addressService->setDefaultShippingAddress($shipping);
            } else {
                $this->addressService->setDefaultShippingAddress($billing);
            }

            if (
                ($optinAttribute = $shop->getAttribute('sendOptinMail')) !== null
                && $optinAttribute->get('sendOptinMail') === true
                && $customer->getDoubleOptinRegister()
                && $customer->getDoubleOptinConfirmDate() === null
            ) {
                $hash = Random::getAlphanumericString(32);

                $optinId = $this->doubleOptInSaveHash($customer, $hash);
                $this->saveOptinIdToCustomer($optinId, $customer);
                $this->doubleOptInVerificationMail($shop, $customer, $hash);
            }

            $this->saveReferer($customer);

            $this->modelManager->commit();
        } catch (\Exception $ex) {
            $this->modelManager->rollback();
            throw $ex;
        }
    }

    private function saveReferer(Customer $customer): void
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

    private function saveCustomer(Shop $shop, Customer $customer): void
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
            $customer->setPassword(md5(uniqid((string) rand())));
            $customer->setEncoderName('md5');
        }

        if (!$customer->getPaymentId()) {
            $customer->setPaymentId($this->config->get('defaultPayment'));
        }

        /** @var \Shopware\Models\Shop\Shop $subShop */
        $subShop = $this->modelManager->find(\Shopware\Models\Shop\Shop::class, $shop->getId());
        $customer->setLanguageSubShop($subShop);

        if ($customer->getGroup() === null) {
            /** @var Group $customerGroup */
            $customerGroup = $this->modelManager->find(\Shopware\Models\Customer\Group::class, $subShop->getCustomerGroup()->getId());
            $customer->setGroup($customerGroup);
        }

        if ($customer->getAffiliate()) {
            $customer->setAffiliate((int) $this->getPartnerId($customer));
        } else {
            $customer->setAffiliate(0);
        }

        if (!$customer->getNumber() && $this->config->get('shopwareManagedCustomerNumbers')) {
            $customer->setNumber((string) $this->numberIncrementer->increment('user'));
        }

        $this->validator->validate($customer);
        $this->modelManager->persist($customer);
        $this->modelManager->flush($customer);
        $this->modelManager->refresh($customer);
    }

    private function getPartnerId(Customer $customer): int
    {
        return (int) $this->connection->fetchColumn('SELECT id FROM s_emarketing_partner WHERE idcode = ?', [$customer->getAffiliate()]);
    }

    private function doubleOptInVerificationMail(Shop $shop, Customer $customer, string $hash): void
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

        // Should be compatible with the sREGISTERCONFIRMATION context
        $context = [
            'sConfirmLink' => $link,
            'email' => $customer->getEmail(),
            'sMAIL' => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'salutation' => $customer->getSalutation(),
            'customer_type' => $customer->getCustomerType(),
            'additional' => [
                'customer_type' => $customer->getCustomerType(),
            ],
            'accountmode' => $customer->getAccountMode(),
        ];

        $address = $customer->getDefaultBillingAddress();
        if ($address) {
            $context = array_merge($context, [
                'street' => $address->getStreet(),
                'zipcode' => $address->getZipcode(),
                'city' => $address->getCity(),
                'country' => $address->getCountry() ? $address->getCountry()->getId() : null,
                'state' => $address->getState() ? $address->getState()->getId() : null,
            ]);
        }

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

    private function doubleOptInSaveHash(Customer $customer, string $hash): int
    {
        /** @var Request|null $request */
        $request = Shopware()->Container()->get('front')->Request();
        $fromCheckout = ($request && $request->getParam('sTarget') === 'checkout');

        $sql = "INSERT INTO `s_core_optin` (`type`, `datum`, `hash`, `data`)
                VALUES ('swRegister', ?, ?, ?)";

        // Needs to be compatible with the sREGISTERCONFIRMATION context
        $mailContext = [
            'email' => $customer->getEmail(),
            'sMAIL' => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'salutation' => $customer->getSalutation(),
            'accountmode' => $customer->getAccountMode(),
            'customer_type' => $customer->getCustomerType(),
            'additional' => [
                'customer_type' => $customer->getCustomerType(),
            ],
        ];

        $address = $customer->getDefaultBillingAddress();
        if ($address) {
            $mailContext = array_merge($mailContext, [
                'street' => $address->getStreet(),
                'zipcode' => $address->getZipcode(),
                'city' => $address->getCity(),
                'country' => $address->getCountry() ? $address->getCountry()->getId() : null,
                'state' => $address->getState() ? $address->getState()->getId() : null,
            ]);
        }

        $storedData = [
            'customerId' => $customer->getId(),
            'register' => ['billing' => $mailContext], // This structure being required by \sAdmin::sSaveRegisterSendConfirmation
            'fromCheckout' => $fromCheckout,
        ];

        $this->connection->executeQuery($sql, [$customer->getDoubleOptinEmailSentDate()->format('Y-m-d H:i:s'), $hash, serialize($storedData)]);

        return (int) $this->connection->fetchColumn('SELECT id FROM `s_core_optin` WHERE `hash` = :hash', [':hash' => $hash]);
    }

    private function saveOptinIdToCustomer(int $optinId, Customer $customer): void
    {
        $customer->setRegisterOptInId($optinId);
        $this->modelManager->persist($customer);
        $this->modelManager->flush($customer);
    }
}
