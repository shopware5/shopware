<?php
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

namespace Shopware\Tests\Functional\Components\Model;

use Shopware\Components\Model\Generator;

class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    public const TEST_TABLE_NAME = 's_articles_attributes';
    public const TEST_ATTRIBUTE_FIELD_PREFIX = 'test_';
    public const TEST_ATTRIBUTE_FIELD_NAME = 'not_null_default_value_field';
    public const TEST_ATTRIBUTE_PROPERTY_NAME = 'testNotNullDefaultValueField';

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    public $em;

    /**
     * @var \Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface
     */
    public $cs;

    /**
     * @var \Shopware\Components\Model\Generator
     */
    public $generator;

    public function setUp(): void
    {
        parent::setUp();
        $this->cs = Shopware()->Container()->get(\Shopware\Bundle\AttributeBundle\Service\CrudService::class);
        $this->em = Shopware()->Models();
        $this->generator = new Generator(
            $this->em->getConnection()->getSchemaManager(),
            $this->em->getConfiguration()->getAttributeDir(),
            Shopware()->AppPath('Models')
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->cs->delete(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX . self::TEST_ATTRIBUTE_FIELD_NAME
        );
    }

    public function testDefaultInitializationEmptyString()
    {
        $this->addAndEvaluateInitialization(
            'string',
            'string',
            'text'
        );
    }

    public function testDefaultInitializationInteger()
    {
        $default = 123;
        $this->addAndEvaluateInitialization(
            'integer',
            'integer',
            'integer',
            $default
        );
    }

    public function testDefaultInitializationBooleanTrue()
    {
        $this->addAndEvaluateInitialization(
            'boolean',
            'integer',
            'integer',
            1
        );
    }

    public function testDefaultInitializationBooleanFalse()
    {
        $this->addAndEvaluateInitialization(
            'boolean',
            'integer',
            'integer',
            0
        );
    }

    public function testDefaultInitializationFloat()
    {
        $default = 123.45;
        $this->addAndEvaluateInitialization(
            'float',
            'float',
            'float',
            $default
        );
    }

    public function testDefaultInitializationDate()
    {
        $default = '2016-01-02';
        $this->addAndEvaluateInitialization(
            'date',
            'date',
            'date',
            $default
        );
    }

    public function testDefaultInitializationDateTime()
    {
        $default = '2016-01-02 12:13:14';
        $this->addAndEvaluateInitialization(
            'datetime',
            'datetime',
            'datetime',
            $default
        );
    }

    public function testDefaultInitializationTwoProperties()
    {
        // Add two attribute fields
        $this->cs->update(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX . self::TEST_ATTRIBUTE_FIELD_NAME,
            'string'
        );

        $this->cs->update(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX . self::TEST_ATTRIBUTE_FIELD_NAME . '_two',
            'integer',
            [],
            null,
            false,
            123456
        );

        // Generate updated attribute source code
        $modelSourceCode = $this->generator->getSourceCodeForTable(self::TEST_TABLE_NAME);

        $definitionInt = '/**
     * @var integer $' . self::TEST_ATTRIBUTE_PROPERTY_NAME . 'Two
     *
     * @ORM\Column(name="' . self::TEST_ATTRIBUTE_FIELD_PREFIX . self::TEST_ATTRIBUTE_FIELD_NAME . '_two", type="integer", nullable=true)
     */
     protected $' . self::TEST_ATTRIBUTE_PROPERTY_NAME . 'Two;';

        $definitionString = '/**
     * @var string $' . self::TEST_ATTRIBUTE_PROPERTY_NAME . '
     *
     * @ORM\Column(name="' . self::TEST_ATTRIBUTE_FIELD_PREFIX . self::TEST_ATTRIBUTE_FIELD_NAME . '", type="text", nullable=true)
     */
     protected $' . self::TEST_ATTRIBUTE_PROPERTY_NAME . ';';

        $initialization = 'public function __construct()
    {
        $this->' . self::TEST_ATTRIBUTE_PROPERTY_NAME . 'Two = 123456;
    }';

        static::assertTrue(strpos($modelSourceCode, $definitionInt) !== false);
        static::assertTrue(strpos($modelSourceCode, $definitionString) !== false);
        static::assertTrue(strpos($modelSourceCode, $initialization) !== false);

        // Clean up second field
        $this->cs->delete(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX . self::TEST_ATTRIBUTE_FIELD_NAME . '_two'
        );
    }

    /**
     * @param string         $type
     * @param string         $phpType
     * @param string         $ormType
     * @param float|int|null $default
     */
    private function addAndEvaluateInitialization($type, $phpType, $ormType, $default = null)
    {
        // Add attribute field
        $this->cs->update(
            self::TEST_TABLE_NAME,
            self::TEST_ATTRIBUTE_FIELD_PREFIX . self::TEST_ATTRIBUTE_FIELD_NAME,
            $type,
            [],
            null,
            false,
            $default
        );

        // Generate updated attribute source code
        $modelSourceCode = $this->generator->getSourceCodeForTable(self::TEST_TABLE_NAME);

        $definition = '/**
     * @var ' . $phpType . ' $' . self::TEST_ATTRIBUTE_PROPERTY_NAME . '
     *
     * @ORM\Column(name="' . self::TEST_ATTRIBUTE_FIELD_PREFIX . self::TEST_ATTRIBUTE_FIELD_NAME . '", type="' . $ormType . '", nullable=true)
     */
     protected $' . self::TEST_ATTRIBUTE_PROPERTY_NAME . ';';

        static::assertTrue(strpos($modelSourceCode, $definition) !== false);
        if ($default == !null) {
            switch ($type) {
                case 'date':
                case 'datetime':
                    $default = 'new \DateTime("' . $default . '")';
                    break;
            }
            $initialization = '$this->' . self::TEST_ATTRIBUTE_PROPERTY_NAME . ' = ' . $default . ';';
            static::assertTrue(strpos($modelSourceCode, $initialization) !== false);
        }
    }
}
