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

namespace Shopware\Tests\Unit\Components\Model;

use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;

class ModelManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function getSqlTypes()
    {
        return [
            // integer
            ['INT', 'integer'],
            ['integer', 'integer'],
            ['int(11)', 'integer'],
            ['tinyint(1)', 'integer'],
            ['smallint(1)', 'integer'],
            ['mediumint(1)', 'integer'],
            ['bigint(20)', 'integer'],

            // boolean
            ['boolean', 'boolean'],
            ['bool', 'boolean'],

            // float
            ['decimal', 'float'],
            ['dec', 'float'],
            ['numeric', 'float'],
            ['fixed', 'float'],
            ['double(2,2)', 'float'],
            ['double', 'float'],
            ['float', 'float'],
            ['float(4,6)', 'float'],

            // string
            ['varchar(255)', 'string'],
            ['varchar', 'string'],
            ['char(2)', 'string'],
            ['char', 'string'],

            // date / datetime
            ['date', 'date'],
            ['datetimetz', 'date'],
            ['datetime', 'datetime'],
            ['timestamp', 'datetime'],

            // text
            ['text', 'text'],
            ['blob', 'text'],
            ['binary', 'text'],
            ['simple_array', 'text'],
            ['json_array', 'text'],
            ['object', 'text'],
            ['guid', 'text'],
        ];
    }

    /**
     * @dataProvider getSqlTypes
     *
     * @param string $sqlType
     * @param string $expectedAttributeType
     */
    public function testConvertSqlTypeToAttributeType($sqlType, $expectedAttributeType): void
    {
        static::assertEquals($expectedAttributeType, $this->convertColumnType($sqlType));
    }

    /**
     * @param string $type
     */
    private function convertColumnType($type): string
    {
        switch (true) {
            case (bool) preg_match('#\b(char\b|varchar)\b#i', $type):
                $type = TypeMappingInterface::TYPE_STRING;
                break;
            case (bool) preg_match('#\b(text|blob|array|simple_array|json_array|object|binary|guid)\b#i', $type):
                $type = TypeMappingInterface::TYPE_TEXT;
                break;
            case (bool) preg_match('#\b(datetime|timestamp)\b#i', $type):
                $type = TypeMappingInterface::TYPE_DATETIME;
                break;
            case (bool) preg_match('#\b(date|datetimetz)\b#i', $type):
                $type = TypeMappingInterface::TYPE_DATE;
                break;
            case (bool) preg_match('#\b(int|integer|smallint|tinyint|mediumint|bigint)\b#i', $type):
                $type = TypeMappingInterface::TYPE_INTEGER;
                break;
            case (bool) preg_match('#\b(float|double|decimal|dec|fixed|numeric)\b#i', $type):
                $type = TypeMappingInterface::TYPE_FLOAT;
                break;
            case (bool) preg_match('#\b(bool|boolean)\b#i', $type):
                $type = TypeMappingInterface::TYPE_BOOLEAN;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Column type "%s" cannot be converted.', $type));
        }

        return $type;
    }
}
