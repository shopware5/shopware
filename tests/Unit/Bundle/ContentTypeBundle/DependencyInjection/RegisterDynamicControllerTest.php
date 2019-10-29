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

namespace Shopware\Tests\Unit\Bundle\ContentTypeBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\RegisterDynamicController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterDynamicControllerTest extends TestCase
{
    public function testRegistrationWithoutFrontend(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('shopware.bundle.content_type.types', [
            'foo' => [],
        ]);
        (new RegisterDynamicController())->process($container);

        static::assertInstanceOf(Definition::class, $container->getDefinition('shopware_bundle.content_type.controllers.api.foo'));
        static::assertInstanceOf(Definition::class, $container->getDefinition('shopware_bundle.content_type.controllers.backend.foo'));

        static::assertSame(['shopware.controller' => [['controller' => 'CustomFoo', 'module' => 'api']]], $container->getDefinition('shopware_bundle.content_type.controllers.api.foo')->getTags());
        static::assertSame(['shopware.controller' => [['controller' => 'CustomFoo', 'module' => 'backend']]], $container->getDefinition('shopware_bundle.content_type.controllers.backend.foo')->getTags());

        static::assertFalse($container->hasDefinition('shopware_bundle.content_type.controllers.frontend.foo'));
    }

    public function testRegistrationWithFrontend(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('shopware.bundle.content_type.types', [
            'foo' => [
                'showInFrontend' => true,
                'viewTitleFieldName' => true,
                'viewDescriptionFieldName' => true,
                'viewImageFieldName' => true,
            ],
        ]);
        (new RegisterDynamicController())->process($container);

        static::assertInstanceOf(Definition::class, $container->getDefinition('shopware_bundle.content_type.controllers.api.foo'));
        static::assertInstanceOf(Definition::class, $container->getDefinition('shopware_bundle.content_type.controllers.backend.foo'));
        static::assertInstanceOf(Definition::class, $container->getDefinition('shopware_bundle.content_type.controllers.frontend.foo'));

        static::assertSame(['shopware.controller' => [['controller' => 'CustomFoo', 'module' => 'api']]], $container->getDefinition('shopware_bundle.content_type.controllers.api.foo')->getTags());
        static::assertSame(['shopware.controller' => [['controller' => 'CustomFoo', 'module' => 'backend']]], $container->getDefinition('shopware_bundle.content_type.controllers.backend.foo')->getTags());
        static::assertSame(['shopware.controller' => [['controller' => 'CustomFoo', 'module' => 'frontend']]], $container->getDefinition('shopware_bundle.content_type.controllers.frontend.foo')->getTags());
    }
}
