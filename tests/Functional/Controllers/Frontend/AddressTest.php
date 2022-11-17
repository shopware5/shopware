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

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Bridge\Config;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\CustomerLoginTrait;
use Shopware_Components_Config;
use Symfony\Component\HttpFoundation\Request;

class AddressTest extends TestCase
{
    use ContainerTrait;
    use CustomerLoginTrait;

    public function testCreateActionWithMissingFirstNameHasError(): void
    {
        $this->loginCustomer();

        $controller = $this->getContainer()->get('shopware_controllers_frontend_address');
        $controller->setContainer($this->getContainer());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'address' => [
                'additional' => [
                    'customer_type' => 'private',
                ],
                'salutation' => 'mr',
                'firstname' => '',
                'lastname' => 'Test',
                'street' => 'Test Street 123',
                'zipcode' => 12345,
                'city' => 'Test Town',
                'country' => 2,
            ],
        ]);
        $controller->setRequest($request);
        $this->getContainer()->get('front')->setRequest($request);

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $controller->setView($view);

        $controller->preDispatch();
        $controller->createAction();

        static::assertTrue($view->getAssign('error_flags')['firstname']);
    }

    public function testCreateActionWithoutSalutationWorksWithNotRequiredSalutation(): void
    {
        $this->loginCustomer();

        /** @var Shopware_Components_Config $config */
        $config = $this->getContainer()->get('config');
        $config->offsetSet('shopSalutationRequired', false);

        $controller = $this->getContainer()->get('shopware_controllers_frontend_address');
        $controller->setContainer($this->getContainer());

        $response = new Enlight_Controller_Response_ResponseTestCase();
        $controller->setResponse($response);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'address' => [
                'additional' => [
                    'customer_type' => 'private',
                ],
                'firstname' => 'Test',
                'lastname' => 'Test',
                'street' => 'Test Street 123',
                'zipcode' => 12345,
                'city' => 'Test Town',
                'country' => 2,
            ],
        ]);
        $controller->setRequest($request);
        $this->getContainer()->get('front')->setRequest($request);

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $controller->setView($view);

        $controller->preDispatch();
        $controller->createAction();

        static::assertEmpty($view->getAssign('error_flags'));

        $config->offsetSet('shopSalutationRequired', true);
    }

    public function testCreateActionWithoutSalutationFails(): void
    {
        $this->loginCustomer();

        $controller = $this->getContainer()->get('shopware_controllers_frontend_address');
        $controller->setContainer($this->getContainer());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'address' => [
                'additional' => [
                    'customer_type' => 'private',
                ],
                'firstname' => 'Test',
                'lastname' => 'Test',
                'street' => 'Test Street 123',
                'zipcode' => 12345,
                'city' => 'Test Town',
                'country' => 2,
            ],
        ]);
        $controller->setRequest($request);
        $this->getContainer()->get('front')->setRequest($request);

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $controller->setView($view);

        $controller->preDispatch();
        $controller->createAction();

        static::assertTrue($view->getAssign('error_flags')['salutation']);
    }
}
