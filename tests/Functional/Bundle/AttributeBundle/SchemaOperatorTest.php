<?php

namespace Shopware\Tests\Bundle\AttributeBundle;

class SchemaOperatorTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultValues()
    {
        $types = array(
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
        );

        $this->iterateTypeArray($types);
    }

    public function testNullDefaultValues()
    {
        $types = array(
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
            'single_selection' => null
        );

        $this->iterateTypeArray($types);
    }

    public function testNullStringDefaultValues()
    {
        $types = array(
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
            'single_selection' => 'NULL'
        );

        $this->iterateTypeArray($types);
    }

    /**
     * @param $types
     * @throws \Exception
     */
    private function iterateTypeArray($types)
    {
        $service = Shopware()->Container()->get('shopware_attribute.crud_service');
        $tableMapping = Shopware()->Container()->get('shopware_attribute.table_mapping');
        $table = 's_articles_attributes';

        foreach ($types as $type => $default) {
            $name = 'attr_' . $type;

            if ($tableMapping->isTableColumn($table, $name)) {
                $service->delete($table, $name);
            }

            $service->update('s_articles_attributes', $name, $type, [], null, false, $default);

            $this->assertTrue($tableMapping->isTableColumn($table, $name));
            $service->delete($table, $name);
        }
    }
}
