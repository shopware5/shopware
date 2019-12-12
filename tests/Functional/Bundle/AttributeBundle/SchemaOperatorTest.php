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

use Exception;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class SchemaOperatorTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    public function testDefaultValues(): void
    {
        $types = [
            'string' => 'test123',
            'integer' => 123,
            'float' => 123,
            'boolean' => 1,
            'date' => '2010-01-01',
            'datetime' => '2010-01-01 10:00:00',
            'text' => 'test123',
            'html' => 'test123',
            'combobox' => '1',
            'multi_selection' => '1',
            'single_selection' => 'SW10003',
        ];

        $this->iterateTypeArray($types);
    }

    public function testNullDefaultValues(): void
    {
        $types = [
            'string' => null,
            'integer' => null,
            'float' => null,
            'boolean' => null,
            'date' => null,
            'datetime' => null,
            'text' => null,
            'html' => null,
            'combobox' => null,
            'multi_selection' => null,
            'single_selection' => null,
        ];

        $this->iterateTypeArray($types);
    }

    public function testNullStringDefaultValues(): void
    {
        $types = [
            'string' => 'NULL',
            'integer' => 'NULL',
            'float' => 'NULL',
            'boolean' => 'NULL',
            'date' => 'NULL',
            'datetime' => 'NULL',
            'text' => 'NULL',
            'html' => 'NULL',
            'combobox' => 'NULL',
            'multi_selection' => 'NULL',
            'single_selection' => 'NULL',
        ];

        $this->iterateTypeArray($types);
    }

    /**
     * @throws Exception
     */
    public function testDefaultValuesBoolean(): void
    {
        $this->iterateTypeArray(['boolean' => 1]);
        $this->iterateTypeArray(['boolean' => 0]);
        $this->iterateTypeArray(['boolean' => true]);
        $this->iterateTypeArray(['boolean' => false]);
        $this->iterateTypeArray(['boolean' => null]);
        $this->iterateTypeArray(['boolean' => '1']);
        $this->iterateTypeArray(['boolean' => '0']);
        $this->iterateTypeArray(['boolean' => 'true']);
        $this->iterateTypeArray(['boolean' => 'false']);
        $this->iterateTypeArray(['boolean' => 'null']);
    }

    public function testUpdateConfiguration(): void
    {
        $service = Shopware()->Container()->get('shopware_attribute.crud_service');
        $tableMapping = Shopware()->Container()->get('shopware_attribute.table_mapping');
        $table = 's_articles_attributes';
        $columnName = 'attr_' . uniqid(mt_rand(), false);

        $service->update($table, $columnName, 'bool');
        static::assertTrue($tableMapping->isTableColumn($table, $columnName));
        $service->update($table, $columnName, 'date');
        static::assertTrue($tableMapping->isTableColumn($table, $columnName));

        /** @var ConfigurationStruct|null $column */
        $column = $service->get($table, $columnName);
        static::assertInstanceOf(ConfigurationStruct::class, $column);
        static::assertEquals('date', $column->getColumnType());
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testReinsertColumnConfigurationShouldFail(): void
    {
        $connection = Shopware()->Container()->get('dbal_connection');
        $attributeData = [
            'table_name' => 's_articles_attributes',
            'column_name' => 'attr_' . uniqid(mt_rand(), false),
            'column_type' => 'bool',
        ];
        $connection->insert('s_attribute_configuration', $attributeData);
        $connection->insert('s_attribute_configuration', $attributeData);
    }

    /**
     * @param array $types
     *
     * @throws Exception
     */
    private function iterateTypeArray($types): void
    {
        $service = Shopware()->Container()->get('shopware_attribute.crud_service');
        $tableMapping = Shopware()->Container()->get('shopware_attribute.table_mapping');
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
