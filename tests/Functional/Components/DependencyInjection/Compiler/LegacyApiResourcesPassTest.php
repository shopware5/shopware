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

namespace Shopware\Tests\Functional\Components\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Api\Resource\Article;
use Shopware\Tests\TestReflectionHelper;

class LegacyApiResourcesPassTest extends TestCase
{
    public function testLegacyServiceGettingAssigned(): void
    {
        $kernel = Shopware()->Container()->get('kernel');
        $container = TestReflectionHelper::getMethod(\get_class($kernel), 'buildContainer')->invoke($kernel);

        $container
            ->register('shopware.api.mynewresource')
            ->setClass(Article::class)
            ->setPublic(true);

        $container
            ->register('shopware.api.mynewresource.hasalreadytag')
            ->setClass(Article::class)
            ->setTags(['shopware.api.mynewresource.hasalreadytag'])
            ->setPublic(true);

        $container->compile();

        static::assertNotEmpty($container->getDefinition('shopware.api.mynewresource')->getTags());
        static::assertNotEmpty($container->getDefinition('shopware.api.mynewresource.hasalreadytag')->getTags());
        static::assertNotEmpty($container->getDefinition('shopware.api.mynewresource')->getTag('shopware.api_resource'));
        static::assertNotEmpty($container->getDefinition('shopware.api.mynewresource.hasalreadytag')->getTag('shopware.api_resource'));
    }
}
