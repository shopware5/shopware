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

namespace Shopware\Tests\Functional\Bundle\AttributeBundle;

use Doctrine\DBAL\DBALException;
use Exception;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TableMapping;
use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;

class SchemaOperatorTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $types = [
            TypeMappingInterface::TYPE_STRING => 'test123',
            TypeMappingInterface::TYPE_INTEGER => 123,
            TypeMappingInterface::TYPE_FLOAT => 123,
            TypeMappingInterface::TYPE_BOOLEAN => 1,
            TypeMappingInterface::TYPE_DATE => '2010-01-01',
            TypeMappingInterface::TYPE_DATETIME => '2010-01-01 10:00:00',
            TypeMappingInterface::TYPE_TEXT => 'test123',
            TypeMappingInterface::TYPE_HTML => 'test123',
            TypeMappingInterface::TYPE_COMBOBOX => '1',
            TypeMappingInterface::TYPE_MULTI_SELECTION => '1',
            TypeMappingInterface::TYPE_SINGLE_SELECTION => 'SW10003',
        ];

        $this->iterateTypeArray($types);
    }

    public function testNullDefaultValues(): void
    {
        $types = [
            TypeMappingInterface::TYPE_STRING => null,
            TypeMappingInterface::TYPE_INTEGER => null,
            TypeMappingInterface::TYPE_FLOAT => null,
            TypeMappingInterface::TYPE_BOOLEAN => null,
            TypeMappingInterface::TYPE_DATE => null,
            TypeMappingInterface::TYPE_DATETIME => null,
            TypeMappingInterface::TYPE_TEXT => null,
            TypeMappingInterface::TYPE_HTML => null,
            TypeMappingInterface::TYPE_COMBOBOX => null,
            TypeMappingInterface::TYPE_MULTI_SELECTION => null,
            TypeMappingInterface::TYPE_SINGLE_SELECTION => null,
        ];

        $this->iterateTypeArray($types);
    }

    public function testNullStringDefaultValues(): void
    {
        $types = [
            TypeMappingInterface::TYPE_STRING => 'NULL',
            TypeMappingInterface::TYPE_INTEGER => 'NULL',
            TypeMappingInterface::TYPE_FLOAT => 'NULL',
            TypeMappingInterface::TYPE_BOOLEAN => 'NULL',
            TypeMappingInterface::TYPE_DATE => 'NULL',
            TypeMappingInterface::TYPE_DATETIME => 'NULL',
            TypeMappingInterface::TYPE_TEXT => 'NULL',
            TypeMappingInterface::TYPE_HTML => 'NULL',
            TypeMappingInterface::TYPE_COMBOBOX => 'NULL',
            TypeMappingInterface::TYPE_MULTI_SELECTION => 'NULL',
            TypeMappingInterface::TYPE_SINGLE_SELECTION => 'NULL',
        ];

        $this->iterateTypeArray($types);
    }

    /**
     * @throws Exception
     */
    public function testDefaultValuesBoolean(): void
    {
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => 1]);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => 0]);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => true]);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => false]);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => null]);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => '1']);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => '0']);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => 'true']);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => 'false']);
        $this->iterateTypeArray([TypeMappingInterface::TYPE_BOOLEAN => 'null']);
    }

    public function testUpdateConfiguration(): void
    {
        $service = Shopware()->Container()->get('shopware_attribute.crud_service');
        $tableMapping = Shopware()->Container()->get('shopware_attribute.table_mapping');
        $table = 's_articles_attributes';
        $columnName = 'attr_' . uniqid((string) mt_rand());

        $service->update($table, $columnName, TypeMappingInterface::TYPE_BOOLEAN);
        static::assertTrue($tableMapping->isTableColumn($table, $columnName));
        $service->update($table, $columnName, TypeMappingInterface::TYPE_DATE);
        static::assertTrue($tableMapping->isTableColumn($table, $columnName));

        $column = $service->get($table, $columnName);
        static::assertInstanceOf(ConfigurationStruct::class, $column);
        static::assertEquals(TypeMappingInterface::TYPE_DATE, $column->getColumnType());
    }

    public function testReinsertColumnConfigurationShouldFail(): void
    {
        $this->expectException(DBALException::class);
        $connection = Shopware()->Container()->get('dbal_connection');
        $attributeData = [
            'table_name' => 's_articles_attributes',
            'column_name' => 'attr_' . uniqid((string) mt_rand(), false),
            'column_type' => 'bool',
        ];
        $connection->insert('s_attribute_configuration', $attributeData);
        $connection->insert('s_attribute_configuration', $attributeData);
    }

    /**
     * @param array<string, mixed> $types
     *
     * @throws Exception
     */
    private function iterateTypeArray(array $types): void
    {
        $service = Shopware()->Container()->get(CrudService::class);
        $tableMapping = Shopware()->Container()->get(TableMapping::class);
        $table = 's_articles_attributes';

        foreach ($types as $type => $default) {
            $name = 'attr_' . $type;

            if ($tableMapping->isTableColumn($table, $name)) {
                $service->delete($table, $name);
            }

            $service->update($table, $name, $type, [], null, false, $default);

            static::assertTrue($tableMapping->isTableColumn($table, $name));
            $service->delete($table, $name);
        }
    }
}
