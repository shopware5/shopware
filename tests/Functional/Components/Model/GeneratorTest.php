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

namespace Shopware\Tests\Functional\Components;

use Shopware\Components\Model\Generator;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    const TEST_TABLE_NAME = 's_articles_attributes';
    const TEST_ATTRIBUTE_FIELD_PREFIX = 'test';
    const TEST_ATTRIBUTE_FIELD_NAME = 'not_null_default_value_field';
    const TEST_ATTRIBUTE_PROPERTY_NAME = 'testNotNullDefaultValueField';

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    public $em;

    /**
     * @var \Shopware\Components\Model\Generator
     */
    public $generator;

    public function setUp()
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->generator = new Generator(
            $this->em->getConnection()->getSchemaManager(),
            $this->em->getConfiguration()->getAttributeDir(),
            Shopware()->AppPath('Models')
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->removeAttribute(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX,
            self::TEST_ATTRIBUTE_FIELD_NAME
        );
    }

    public function testDefaultInitializationString()
    {
        $default = 'test 123';
        $this->addAndEvaluateInitialization(
            'VARCHAR(255)',
            $default,
            '"' . $default . '"'
        );
    }

    public function testDefaultInitializationEmptyString()
    {
        $this->addAndEvaluateInitialization(
            'VARCHAR(255)',
            '',
            '""'
        );
    }

    public function testDefaultInitializationInteger()
    {
        $default = 123;
        $this->addAndEvaluateInitialization(
            'INT(11)',
            $default,
            $default
        );
    }

    public function testDefaultInitializationBooleanTrue()
    {
        $this->addAndEvaluateInitialization(
            'TINYINT(1)',
            true,
            'true'
        );
    }

    public function testDefaultInitializationBooleanFalse()
    {
        $this->addAndEvaluateInitialization(
            'TINYINT(1)',
            false,
            'false'
        );
    }

    public function testDefaultInitializationBooleanTrueAsInt()
    {
        $this->addAndEvaluateInitialization(
            'TINYINT(1)',
            1,
            'true'
        );
    }

    public function testDefaultInitializationBooleanFalseAsInt()
    {
        $this->addAndEvaluateInitialization(
            'TINYINT(1)',
            0,
            'false'
        );
    }

    public function testDefaultInitializationFloat()
    {
        $default = 123.45;
        $this->addAndEvaluateInitialization(
            'DECIMAL(10,2)',
            $default,
            $default
        );
    }

    public function testDefaultInitializationDate()
    {
        $default = '2016-01-02';
        $this->addAndEvaluateInitialization(
            'DATE',
            $default,
            'new \DateTime("' . $default . '")'
        );
    }

    public function testDefaultInitializationDateTime()
    {
        $default = '2016-01-02 12:13:14';
        $this->addAndEvaluateInitialization(
            'DATETIME',
            $default,
            'new \DateTime("' . $default . '")'
        );
    }

    public function testDefaultInitializationNotNullConstraint()
    {
        $default = 'test 123 with not NULL constraint';
        $this->addAndEvaluateInitialization(
            'VARCHAR(255)',
            $default,
            '"' . $default . '"',
            false
        );
    }

    public function testDefaultInitializationTwoProperties()
    {
        // Add two attribute fields
        $firstDefault = 'test 123';
        $this->em->addAttribute(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX,
            self::TEST_ATTRIBUTE_FIELD_NAME,
            'VARCHAR(255)',
            false,
            $firstDefault
        );
        $secondDefault = 123;
        $this->em->addAttribute(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX,
            self::TEST_ATTRIBUTE_FIELD_NAME . '_two',
            'INT(11)',
            false,
            $secondDefault
        );

        // Generate updated attribute source code
        $modelSourceCode = $this->generator->getSourceCodeForTable(self::TEST_TABLE_NAME);

        $initialization = '
    public function __construct()
    {
        $this->' . self::TEST_ATTRIBUTE_PROPERTY_NAME . ' = "' . $firstDefault . '";
        $this->' . self::TEST_ATTRIBUTE_PROPERTY_NAME . 'Two = ' . $secondDefault . ';
    }';
        $this->assertTrue(strpos($modelSourceCode, $initialization) !== false);

        // Clean up second field
        $this->em->removeAttribute(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX,
            self::TEST_ATTRIBUTE_FIELD_NAME . '_two'
        );
    }

    /**
     * @param string                $type
     * @param string|int|float|bool $default
     * @param string                $initializedValue
     * @param bool                  $allowNull        (optional)
     */
    private function addAndEvaluateInitialization($type, $default, $initializedValue, $allowNull = true)
    {
        // Add attribute field
        $this->em->addAttribute(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX,
            self::TEST_ATTRIBUTE_FIELD_NAME,
            $type,
            $allowNull,
            $default
        );

        // Generate updated attribute source code
        $modelSourceCode = $this->generator->getSourceCodeForTable(self::TEST_TABLE_NAME);

        $initialization = '
    public function __construct()
    {
        $this->' . self::TEST_ATTRIBUTE_PROPERTY_NAME . ' = ' . $initializedValue . ';
    }';
        $this->assertTrue(strpos($modelSourceCode, $initialization) !== false);
    }
}
