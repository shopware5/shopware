<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Unit\Bundle\ContentTypeBundle;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Field\DummyField;
use Shopware\Bundle\ContentTypeBundle\Field\TextField;
use Shopware\Bundle\ContentTypeBundle\Services\TypeBuilder;
use Shopware\Bundle\ContentTypeBundle\Services\XmlReader\ContentTypesReader;

class XmlReaderTest extends TestCase
{
    private ContentTypesReader $reader;

    public function setUp(): void
    {
        parent::setUp();

        $this->reader = new ContentTypesReader();
    }

    public function testReadValidXml(): void
    {
        $data = $this->reader->read(__DIR__ . '/fixtures/valid.xml');

        static::assertArrayHasKey('store', $data);
        $store = $data['store'];
        static::assertIsArray($store);

        // Minimum required fields
        static::assertArrayHasKey('name', $store);
        static::assertArrayHasKey('menuParent', $store);
        static::assertArrayHasKey('fieldSets', $store);

        static::assertCount(1, $store['fieldSets']);

        static::assertArrayHasKey('fields', $store['fieldSets'][0]);
        static::assertCount(3, $store['fieldSets'][0]['fields']);

        $type = $this->getTypeBuilder()->createType('store', $store);

        static::assertEquals('store', $type->getInternalName());
        static::assertEquals('stores', $type->getName());
        static::assertCount(3, $type->getFields());
    }

    public function testReadWithFallbackToDummy(): void
    {
        $data = $this->reader->read(__DIR__ . '/fixtures/valid_with_invalid_field.xml');

        static::assertArrayHasKey('store', $data);
        $store = $data['store'];

        $type = $this->getTypeBuilder()->createType('store', $store);

        static::assertCount(2, $type->getFields());

        static::assertInstanceOf(DummyField::class, $type->getFields()[1]->getType());
    }

    public function testReadInvalidFile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->reader->read(__DIR__ . '/fixtures/invalid.xml');
    }

    public function testReadInvalidTypeNameFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->reader->read(__DIR__ . '/fixtures/valid_with_invalid_typeName.xml');
    }

    public function testReadValidTypeNameWithNumber(): void
    {
        $contentTypes = $this->reader->read(__DIR__ . '/fixtures/valid_with_number_in_typeName.xml');
        static::assertArrayHasKey('a123Test', $contentTypes);
        $testContentType = $contentTypes['a123Test'];
        static::assertSame('TEST 123', $testContentType['name']);
    }

    public function testReadInvalidTypeNameStartingWithNumberFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->reader->read(__DIR__ . '/fixtures/valid_with_invalid_starting_number_in_typeName.xml');
    }

    public function testReadingInvalidFrontendConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Content-Type with enabled showInFrontend requires a viewTitleFieldName, viewDescriptionFieldName, viewImageFieldName');

        $this->reader->read(__DIR__ . '/fixtures/invalid_frontend.xml');
    }

    private function getTypeBuilder(): TypeBuilder
    {
        return new TypeBuilder([
            'text' => TextField::class,
        ], []);
    }
}
