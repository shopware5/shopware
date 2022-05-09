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

namespace Shopware\Tests\Functional\Components;

use Enlight_Controller_Action;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Event_EventArgs;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\CSRFGetProtectionAware;
use Shopware\Components\CSRFTokenValidationException;
use Shopware\Components\CSRFTokenValidator;
use Shopware\Components\Random;
use Shopware\Tests\Functional\Helper\Utils;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\ShopContextTrait;

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

        $token = Random::getAlphanumericString(30);

        $controller = new MockController();
        $incomingRequest = new Enlight_Controller_Request_RequestTestCase();
        $incomingResponse = new Enlight_Controller_Response_ResponseTestCase();
        $incomingRequest->setActionName(self::EXISTING_ACTION_NAME);
        $incomingRequest->setParam(CSRFTokenValidator::CSRF_TOKEN_ARGUMENT, $token);
        $incomingRequest->cookies->set(self::CSRF_TOKEN_FOR_SHOP_ONE, $token);
        $controller->setRequest($incomingRequest);
        $controller->setResponse($incomingResponse);
        $enlightEventArgs = new Enlight_Event_EventArgs([
            'subject' => $controller,
        ]);

        $tokenValidator->checkFrontendTokenValidation($enlightEventArgs);

        static::assertTrue($incomingRequest->getAttribute('isValidated'));
    }

    public function testFrontendTokenValidationThrowsErrorWhenCookieIsNotSet(): void
    {
        $tokenValidator = $this->getContainer()->get(CSRFTokenValidator::class);
        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);
        $token = Random::getAlphanumericString(30);

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

        $this->expectException(CSRFTokenValidationException::class);
        $tokenValidator->checkFrontendTokenValidation($enlightEventArgs);
    }

    public function testFrontendTokenValidationThrowsErrorWhenParamIsNotSet(): void
    {
        $tokenValidator = $this->getContainer()->get(CSRFTokenValidator::class);
        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);

        $token = Random::getAlphanumericString(30);

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
