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
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\RegisterFieldsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterFieldsCompilerPassTest extends TestCase
{
    public function testMissingFieldName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Service with id "foo" need the tag attribute fieldName to identify the short name');
        $container = new ContainerBuilder();
        $container
            ->register('foo')
            ->setTags(['shopware.bundle.content_type.field' => ['attr' => 'baz']]);
        (new RegisterFieldsCompilerPass())->process($container);
    }
}
