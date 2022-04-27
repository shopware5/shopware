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

    /**
     * @before
     */
    public function enableCsrfInFrontend(): void
    {
        Utils::hijackProperty($this->getContainer()->get(CSRFTokenValidator::class), 'isEnabledFrontend', true);
    }

    /**
     * @after
     */
    public function disableCsrfInFrontend(): void
    {
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

        static::assertNotNull($this->getContainer()->get('session')->get('__csrf_token-1'));
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

        static::assertNotNull($this->getContainer()->get('session')->get('__csrf_token-1'));
        static::assertNotEquals($token, $this->getContainer()->get('session')->get('__csrf_token-1'));
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

        static::assertNotNull($this->getContainer()->get('session')->get('__csrf_token-1'));
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

        static::assertNotNull($this->getContainer()->get('session')->get('__csrf_token-1'));
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
