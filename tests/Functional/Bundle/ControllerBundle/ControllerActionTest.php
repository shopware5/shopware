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

namespace Shopware\Tests\Functional\Bundle\ControllerBundle;

use Enlight_Controller_Request_RequestTestCase as TestRequest;
use Enlight_Controller_Response_ResponseTestCase as TestResponse;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\DependencyInjection\LegacyPhpDumper;
use Shopware\Tests\Functional\Bundle\ControllerBundle\TestController\Test;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;
use TestContainer;

class ControllerActionTest extends TestCase
{
    public function testAutowiringOfActionParameters(): void
    {
        $controller = $this->prepareController();

        $response = new TestResponse();
        $controller->setResponse($response);

        $controller->dispatch('indexAction');

        static::assertSame('Hello World', $response->getBody());
    }

    private function prepareController(): Test
    {
        $container = $this->prepareContainer();

        $controller = $container->get(Test::class);
        static::assertInstanceOf(Test::class, $controller);

        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));
        require_once sys_get_temp_dir() . '/TestContainer.php';
        $controller->setContainer(new TestContainer());

        $controller->setRequest($this->prepareRequest());

        return $controller;
    }

    private function prepareContainer(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new XmlFileLoader($containerBuilder, new FileLocator([__DIR__ . '/Resources/']));
        $loader->load('services.xml');
        $containerBuilder->addCompilerPass(
            new RegisterControllerArgumentLocatorsPass('argument_resolver.service', 'shopware.controller')
        );
        $containerBuilder->compile();

        $dumper = new LegacyPhpDumper($containerBuilder);
        $containerClass = $dumper->dump([
            'class' => 'TestContainer',
            'base_class' => Container::class,
        ]);

        $written = file_put_contents(sys_get_temp_dir() . '/TestContainer.php', $containerClass);
        static::assertNotFalse($written);

        return $containerBuilder;
    }

    private function prepareRequest(): TestRequest
    {
        $request = new TestRequest();
        $request->setModuleName('frontend');
        $request->setControllerName('test');
        $request->setActionName('index');
        $request->setDispatched();
        $request->attributes->set('controllerId', Test::class);

        return $request;
    }
}
