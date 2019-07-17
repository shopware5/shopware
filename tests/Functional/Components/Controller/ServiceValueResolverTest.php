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

namespace Shopware\Tests\Functional\Components\Controller;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Controller\ServiceValueResolver;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ServiceValueResolverTest extends TestCase
{
    public function testDoNotSupportWhenControllerDoNotExists()
    {
        $resolver = new ServiceValueResolver(new ServiceLocator([]));
        $argument = new ArgumentMetadata('dummy', DummyService::class, false, false, null);
        $request = $this->requestWithAttributes(['_controller' => 'my_controller']);

        static::assertFalse($resolver->supports($request, $argument));
    }

    public function testExistingController()
    {
        $resolver = new ServiceValueResolver(new ServiceLocator([
            'App\\Controller\\Mine::method' => function () {
                return new ServiceLocator([
                    'dummy' => function () {
                        return new DummyService();
                    },
                ]);
            },
        ]));

        $request = $this->requestWithAttributes(['_controller' => 'App\\Controller\\Mine::method']);
        $argument = new ArgumentMetadata('dummy', DummyService::class, false, false, null);

        static::assertTrue($resolver->supports($request, $argument));
        $this->assertYieldEquals([new DummyService()], $resolver->resolve($request, $argument));
    }

    public function testExistingControllerWithATrailingBackSlash()
    {
        $resolver = new ServiceValueResolver(new ServiceLocator([
            'App\\Controller\\Mine::method' => function () {
                return new ServiceLocator([
                    'dummy' => function () {
                        return new DummyService();
                    },
                ]);
            },
        ]));

        $request = $this->requestWithAttributes(['_controller' => '\\App\\Controller\\Mine::method']);
        $argument = new ArgumentMetadata('dummy', DummyService::class, false, false, null);

        static::assertTrue($resolver->supports($request, $argument));
        $this->assertYieldEquals([new DummyService()], $resolver->resolve($request, $argument));
    }

    public function testExistingControllerWithMethodNameStartUppercase()
    {
        $resolver = new ServiceValueResolver(new ServiceLocator([
            'App\\Controller\\Mine::method' => function () {
                return new ServiceLocator([
                    'dummy' => function () {
                        return new DummyService();
                    },
                ]);
            },
        ]));
        $request = $this->requestWithAttributes(['_controller' => 'App\\Controller\\Mine::Method']);
        $argument = new ArgumentMetadata('dummy', DummyService::class, false, false, null);

        static::assertTrue($resolver->supports($request, $argument));
        $this->assertYieldEquals([new DummyService()], $resolver->resolve($request, $argument));
    }

    public function testControllerNameIsAnArray()
    {
        $resolver = new ServiceValueResolver(new ServiceLocator([
            'App\\Controller\\Mine::method' => function () {
                return new ServiceLocator([
                    'dummy' => function () {
                        return new DummyService();
                    },
                ]);
            },
        ]));

        $request = $this->requestWithAttributes(['_controller' => ['App\\Controller\\Mine', 'method']]);
        $argument = new ArgumentMetadata('dummy', DummyService::class, false, false, null);

        static::assertTrue($resolver->supports($request, $argument));
        $this->assertYieldEquals([new DummyService()], $resolver->resolve($request, $argument));
    }

    public function testServiceNotation()
    {
        $resolver = new ServiceValueResolver(new ServiceLocator([
            'App\\Controller\\Mine:method' => function () {
                return new ServiceLocator([
                    'dummy' => function () {
                        return new DummyService();
                    },
                ]);
            },
        ]));

        $request = $this->requestWithAttributes(['_controller' => ['App\\Controller\\Mine', 'method']]);
        $argument = new ArgumentMetadata('dummy', DummyService::class, false, false, null);

        static::assertTrue($resolver->supports($request, $argument));
        $this->assertYieldEquals([new DummyService()], $resolver->resolve($request, $argument));
    }

    public function testServiceNotationAndInvoke()
    {
        $resolver = new ServiceValueResolver(new ServiceLocator([
            'App\\Controller\\Mine:__invoke' => function () {
                return new ServiceLocator([
                    'dummy' => function () {
                        return new DummyService();
                    },
                ]);
            },
        ]));

        $request = $this->requestWithAttributes(['_controller' => 'App\\Controller\\Mine']);
        $argument = new ArgumentMetadata('dummy', DummyService::class, false, false, null);

        static::assertTrue($resolver->supports($request, $argument));
        $this->assertYieldEquals([new DummyService()], $resolver->resolve($request, $argument));
    }

    private function requestWithAttributes(array $attributes)
    {
        $request = Request::create('/');

        foreach ($attributes as $name => $value) {
            $request->attributes->set($name, $value);
        }

        return $request;
    }

    private function assertYieldEquals(array $expected, \Generator $generator)
    {
        $args = [];
        foreach ($generator as $arg) {
            $args[] = $arg;
        }

        static::assertEquals($expected, $args);
    }
}

class DummyService
{
}
