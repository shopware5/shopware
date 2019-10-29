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

namespace Shopware\Tests\Unit\Bundle\ContentTypeBundle\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Services\FrontendTypeTranslator;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;

class FrontendTypeTranslatorTest extends TestCase
{
    /**
     * @var FrontendTypeTranslator
     */
    private $service;

    public function setUp(): void
    {
        $namespace = $this->getMockBuilder(\Enlight_Components_Snippet_Namespace::class)
            ->disableOriginalConstructor()
            ->getMock();

        $namespace->method('get')
            ->willReturnMap([
                ['name', 'foo', false, 'Fancy Translation'],
                ['field_label', 'field', false, 'Fancy Translation'],
            ]);

        $manager = $this->getMockBuilder(\Shopware_Components_Snippet_Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->method('getNamespace')->willReturn($namespace);

        $this->service = new FrontendTypeTranslator($manager);
    }

    public function testTypeTranslation(): void
    {
        $type = new Type();
        $type->setName('foo');
        $type->setFields([]);
        $this->service->translate($type);

        static::assertEquals('Fancy Translation', $type->getName());
    }

    public function testTypeWithFieldsTranslation(): void
    {
        $type = new Type();
        $type->setName('foo');
        $type->setFields([
            (new Field())
                ->setName('field')
                ->setLabel('field'),
        ]);
        $this->service->translate($type);

        static::assertEquals('Fancy Translation', $type->getName());

        foreach ($type->getFields() as $field) {
            static::assertEquals('Fancy Translation', $field->getLabel());
        }
    }
}
