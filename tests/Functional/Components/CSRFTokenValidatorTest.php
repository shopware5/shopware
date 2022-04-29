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

use Enlight_Controller_Action;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Event_EventArgs;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\CSRFGetProtectionAware;
use Shopware\Components\CSRFTokenValidationException;
use Shopware\Components\CSRFTokenValidator;
use Shopware\Tests\Functional\Helper\Utils;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\ShopContextTrait;
use Throwable;

class CSRFTokenValidatorTest extends TestCase
{
    use ContainerTrait;
    use ShopContextTrait;

    public const EXISTING_ACTION_NAME = 'foo';

    private const CSRF_TOKEN_FOR_SHOP_ONE = '__csrf_token-1';

    /**
     * @before
     */
    public function enableCsrfInFrontend(): void
    {
        $this->getContainer()->get('session')->offsetUnset(self::CSRF_TOKEN_FOR_SHOP_ONE);
        Utils::hijackProperty($this->getContainer()->get(CSRFTokenValidator::class), 'isEnabledFrontend', true);
    }

    /**
     * @after
     */
    public function disableCsrfInFrontend(): void
    {
        $this->getContainer()->get('session')->offsetUnset(self::CSRF_TOKEN_FOR_SHOP_ONE);
        Utils::hijackProperty($this->getContainer()->get(CSRFTokenValidator::class), 'isEnabledFrontend', false);
    }

    public function testFrontendTokenIsValid(): void
    {
        $tokenValidator = $this->getContainer()->get(CSRFTokenValidator::class);
        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);
        $createRequest = new Enlight_Controller_Request_RequestTestCase();
        $createResponse = new Enlight_Controller_Response_ResponseTestCase();

        $token = $tokenValidator->regenerateToken($createRequest, $createResponse);

        $controller = new MockController();
        $incomingRequest = new Enlight_Controller_Request_RequestTestCase();
        $incomingResponse = new Enlight_Controller_Response_ResponseTestCase();
        $incomingRequest->setActionName(self::EXISTING_ACTION_NAME);
        $incomingRequest->setParam(CSRFTokenValidator::CSRF_TOKEN_ARGUMENT, $token);
        $controller->setRequest($incomingRequest);
        $controller->setResponse($incomingResponse);
        $enlightEventArgs = new Enlight_Event_EventArgs([
            'subject' => $controller,
        ]);

        $tokenValidator->checkFrontendTokenValidation($enlightEventArgs);

        static::assertIsString($this->getContainer()->get('session')->get(self::CSRF_TOKEN_FOR_SHOP_ONE));
        static::assertTrue($incomingRequest->getAttribute('isValidated'));
    }

    public function testFrontendTokenValidationThrowsError(): void
    {
        $tokenValidator = $this->getContainer()->get(CSRFTokenValidator::class);
        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);
        $createRequest = new Enlight_Controller_Request_RequestTestCase();
        $createResponse = new Enlight_Controller_Response_ResponseTestCase();

        $token = $tokenValidator->regenerateToken($createRequest, $createResponse);

        $controller = new MockController();
        $incomingRequest = new Enlight_Controller_Request_RequestTestCase();
        $incomingResponse = new Enlight_Controller_Response_ResponseTestCase();
        $incomingRequest->setActionName(self::EXISTING_ACTION_NAME);
        $incomingRequest->setParam(CSRFTokenValidator::CSRF_TOKEN_ARGUMENT, 'NOT_FITTING');
        $controller->setRequest($incomingRequest);
        $controller->setResponse($incomingResponse);
        $enlightEventArgs = new Enlight_Event_EventArgs([
            'subject' => $controller,
        ]);

        try {
            $tokenValidator->checkFrontendTokenValidation($enlightEventArgs);
        } catch (Throwable $e) {
            static::assertInstanceOf(CSRFTokenValidationException::class, $e);
        }

        static::assertIsString($this->getContainer()->get('session')->get(self::CSRF_TOKEN_FOR_SHOP_ONE));
        static::assertNotEquals($token, $this->getContainer()->get('session')->get(self::CSRF_TOKEN_FOR_SHOP_ONE));
    }

    public function testCsrfExceptionIsThrownWhenNoSession(): void
    {
        $tokenValidator = $this->getContainer()->get(CSRFTokenValidator::class);
        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);

        $controller = new MockController();
        $incomingRequest = new Enlight_Controller_Request_RequestTestCase();
        $incomingResponse = new Enlight_Controller_Response_ResponseTestCase();
        $incomingRequest->setActionName(self::EXISTING_ACTION_NAME);
        $incomingRequest->setParam(CSRFTokenValidator::CSRF_TOKEN_ARGUMENT, 'NOT_FITTING');
        $controller->setRequest($incomingRequest);
        $controller->setResponse($incomingResponse);
        $enlightEventArgs = new Enlight_Event_EventArgs([
            'subject' => $controller,
        ]);

        try {
            $tokenValidator->checkFrontendTokenValidation($enlightEventArgs);
        } catch (Throwable $e) {
            static::assertInstanceOf(CSRFTokenValidationException::class, $e);
        }

        static::assertIsString($this->getContainer()->get('session')->get(self::CSRF_TOKEN_FOR_SHOP_ONE));
    }

    public function testCsrfExceptionIsThrownWhenNoRequestCsrfIsSet(): void
    {
        $tokenValidator = $this->getContainer()->get(CSRFTokenValidator::class);
        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);
        $createRequest = new Enlight_Controller_Request_RequestTestCase();
        $createResponse = new Enlight_Controller_Response_ResponseTestCase();
        $tokenValidator->regenerateToken($createRequest, $createResponse);

        $controller = new MockController();
        $incomingRequest = new Enlight_Controller_Request_RequestTestCase();
        $incomingResponse = new Enlight_Controller_Response_ResponseTestCase();
        $controller->setRequest($incomingRequest);
        $controller->setResponse($incomingResponse);
        $enlightEventArgs = new Enlight_Event_EventArgs([
            'subject' => $controller,
        ]);

        try {
            $tokenValidator->checkFrontendTokenValidation($enlightEventArgs);
        } catch (Throwable $e) {
            static::assertInstanceOf(CSRFTokenValidationException::class, $e);
        }

        static::assertIsString($this->getContainer()->get('session')->get(self::CSRF_TOKEN_FOR_SHOP_ONE));
    }

    public function testCsrfTokenIsUpdatedIfItIsNotAvailableInTheSessionAndIsGetRequest(): void
    {
        $tokenValidator = $this->getContainer()->get(CSRFTokenValidator::class);
        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);

        static::assertNull($this->getContainer()->get('session')->get(self::CSRF_TOKEN_FOR_SHOP_ONE));

        $controller = new NotProtectionAwareController();
        $incomingRequest = new Enlight_Controller_Request_RequestTestCase();
        $incomingRequest->setMethod('GET');
        $createResponse = new Enlight_Controller_Response_ResponseTestCase();
        $controller->setRequest($incomingRequest);
        $controller->setResponse($createResponse);
        $enlightEventArgs = new Enlight_Event_EventArgs([
            'subject' => $controller,
        ]);

        $tokenValidator->checkFrontendTokenValidation($enlightEventArgs);

        static::assertIsString($this->getContainer()->get('session')->get(self::CSRF_TOKEN_FOR_SHOP_ONE));
    }

    public function testCsrfTokenIsNotUpdatedIfItIsNotAvailableInTheSession(): void
    {
        $tokenValidator = $this->getContainer()->get(CSRFTokenValidator::class);
        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);

        static::assertNull($this->getContainer()->get('session')->get(self::CSRF_TOKEN_FOR_SHOP_ONE));

        $controller = new MockController();
        $incomingRequest = new Enlight_Controller_Request_RequestTestCase();
        $incomingRequest->setMethod('GET');
        $incomingRequest->setActionName(self::EXISTING_ACTION_NAME);
        $createResponse = new Enlight_Controller_Response_ResponseTestCase();
        $controller->setRequest($incomingRequest);
        $controller->setResponse($createResponse);
        $enlightEventArgs = new Enlight_Event_EventArgs([
            'subject' => $controller,
        ]);

        $this->expectException(CSRFTokenValidationException::class);
        $tokenValidator->checkFrontendTokenValidation($enlightEventArgs);
    }
}

class MockController extends Enlight_Controller_Action implements CSRFGetProtectionAware
{
    public function getCSRFProtectedActions()
    {
        return [
            'foo',
        ];
    }
}

class NotProtectionAwareController extends Enlight_Controller_Action
{
}
