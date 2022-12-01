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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_View_Default;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\CustomerLoginTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Frontend_Account;
use Symfony\Component\HttpFoundation\Request;

class AccountTest extends ControllerTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;
    use CustomerLoginTrait;

    private const NEW_MAIL_ADDRESS = 'test1@example.com';

    private Connection $connection;

    public function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->Template()->clearAllAssign();
        parent::tearDown();
    }

    public function testPasswordWillBeChanged(): void
    {
        $hash = $this->getNextResetHash('test@example.com');
        $before = $this->getPasswordForEmail('test@example.com');

        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $front = $this->getContainer()->get('front');
        $front->setRequest($request);
        $front->setResponse($response);

        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'hash' => $hash,
                'password' => [
                    'password' => 'shopware1',
                    'passwordConfirmation' => 'shopware1',
                ],
        ]);

        $controller->setFront($front);
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->init();
        $controller->resetPasswordAction();

        $changed = $this->getPasswordForEmail('test@example.com');

        static::assertNotSame($before, $changed);
    }

    public function testPasswordWillNotChangeOnErrorDifferentPasswords(): void
    {
        $hash = $this->getNextResetHash('test@example.com');
        $before = $this->getPasswordForEmail('test@example.com');

        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'hash' => $hash,
            'password' => [
                'password' => 'shopware1',
                'passwordConfirmation' => 'shopware12',
            ],
        ]);

        $controller->setRequest($request);
        $controller->resetPasswordAction();

        $after = $this->getPasswordForEmail('test@example.com');

        static::assertFalse($this->Response()->isRedirect());
        static::assertSame($before, $after);
    }

    public function testPasswordWillNotChangeOnErrorToShort(): void
    {
        $hash = $this->getNextResetHash('test@example.com');
        $before = $this->getPasswordForEmail('test@example.com');

        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'hash' => $hash,
            'password' => [
                'password' => 'test',
                'passwordConfirmation' => 'test',
            ],
        ]);

        $controller->setRequest($request);
        $controller->resetPasswordAction();

        $after = $this->getPasswordForEmail('test@example.com');

        static::assertSame($before, $after);
    }

    public function testPasswordWillBeChangedOnInvalidCustomers(): void
    {
        $hash = $this->getNextResetHash('test@example.com');
        $before = $this->getPasswordForEmail('test@example.com');

        // Make user invalid
        $this->connection->executeStatement('UPDATE `s_user` SET `salutation` = null WHERE `email` = :email', ['email' => 'test@example.com']);

        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'hash' => $hash,
            'password' => [
                'password' => 'shopware12',
                'passwordConfirmation' => 'shopware12',
            ],
        ]);

        $controller->setRequest($request);
        $controller->resetPasswordAction();

        $changed = $this->getPasswordForEmail('test@example.com');
        static::assertSame($before, $changed);
    }

    public function testNoServerErrorOnMissingHashParameter(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $request->setMethod(Request::METHOD_GET);

        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->resetPasswordAction();

        static::assertNotNull($response);
    }

    /**
     * Asserts, that no password change takes place, when an old hash is used.
     */
    public function testExistingHashIsInvalidated(): void
    {
        $hash = $this->getNextResetHash('test@example.com');
        $secondHash = $this->getNextResetHash('test@example.com');
        $before = $this->getPasswordForEmail('test@example.com');

        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'hash' => $hash,
            'password' => [
                'password' => 'fd12179f-d454-42e4-ae90-9271f806088d',
                'passwordConfirmation' => 'fd12179f-d454-42e4-ae90-9271f806088d',
            ],
        ]);

        $controller->setRequest($request);
        $controller->resetPasswordAction();

        $changed = $this->getPasswordForEmail('test@example.com');

        static::assertSame($before, $changed);
    }

    public function testHashIsNotValidAfterUserChangesMailAddress(): void
    {
        $hash = $this->getNextResetHash('test@example.com');

        $this->loginCustomer();

        $customer = $this->getContainer()->get(ModelManager::class)->find(Customer::class, $this->getContainer()->get('session')->get('sUserId'));
        static::assertInstanceOf(Customer::class, $customer);

        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'email' => [
                'email' => 'test1@example.com',
                'emailConfirmation' => 'test1@example.com',
                'currentPassword' => 'shopware',
            ],
        ]);

        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->init();
        $controller->saveEmailAction();

        static::assertSame(self::NEW_MAIL_ADDRESS, $customer->getEmail(), 'Newly set email was not taken over.');

        $this->logOutCustomer();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setMethod(Request::METHOD_GET)->setParam('hash', $hash);
        $controller->setRequest($request);
        $controller->resetPasswordAction();

        $view = $controller->View()->getAssign();

        static::assertTrue($view['invalidToken'], 'Password-reset-link is still valid');
        static::assertArrayHasKey('sErrorMessages', $view);
    }

    private function getNextResetHash(string $mail): string
    {
        // Request a variant that is not the default one
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setMethod(Request::METHOD_POST)->setParam('email', $mail);
        $controller->setRequest($request);
        $controller->passwordAction();

        static::assertTrue($controller->View()->getAssign('sSuccess'));

        $this->reset();

        return $this->connection->fetchOne(
            'SELECT `hash` FROM `s_core_optin` WHERE `type` IN (:types) ORDER BY `datum` DESC LIMIT 1',
            [':types' => ['password', 'swPassword']],
            [':types' => Connection::PARAM_STR_ARRAY]
        );
    }

    private function getController(): Shopware_Controllers_Frontend_Account
    {
        $controller = new Shopware_Controllers_Frontend_Account(
            $this->getContainer()->get('config'),
            $this->getContainer()->get('shopware.captcha.validator')
        );

        $controller->setView(new Enlight_View_Default($this->getContainer()->get('template')));
        $controller->setContainer($this->getContainer());

        return $controller;
    }

    private function getPasswordForEmail(string $email): ?string
    {
        return $this->connection->fetchOne('SELECT `password` FROM `s_user` WHERE `email` = :email', ['email' => $email]) ?: null;
    }
}
