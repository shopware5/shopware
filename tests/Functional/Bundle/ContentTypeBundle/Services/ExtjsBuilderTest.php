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

namespace Shopware\Tests\Functional\Bundle\ContentTypeBundle\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Field\IntegerField;
use Shopware\Bundle\ContentTypeBundle\Field\TextField;
use Shopware\Bundle\ContentTypeBundle\Services\ExtjsBuilder;
use Shopware\Bundle\ContentTypeBundle\Services\ExtjsBuilderInterface;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Bundle\ContentTypeBundle\Structs\Fieldset;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;

class ExtjsBuilderTest extends TestCase
{
    /**
     * @var ExtjsBuilder
     */
    private $service;

    public function setUp(): void
    {
        $this->service = Shopware()->Container()->get(ExtjsBuilderInterface::class);
    }

    /**
     * @dataProvider dataProviderModelFields
     */
    public function testModelFields(Type $type, array $expectedResult): void
    {
        static::assertSame($expectedResult, $this->service->buildModelFields($type));
    }

    /**
     * @dataProvider dataProviderColumnFields
     */
    public function testColumns(Type $type, array $expectedResult): void
    {
        static::assertSame($expectedResult, $this->service->buildColumns($type));
    }

    /**
     * @dataProvider dataProviderFieldSets
     */
    public function testFieldSets(Type $type, array $expectedResult): void
    {
        static::assertSame($expectedResult, $this->service->buildFieldSets($type));
    }

    public function dataProviderModelFields(): array
    {
        return [
            [
                (new Type())
                    ->setFields([
                        (new Field())
                            ->setName('test')
                            ->setType(new TextField()),
                    ]),
                [
                    [
                        'name' => 'id',
                        'type' => 'int',
                    ],
                    [
                        'name' => 'test',
                        'type' => 'string',
                        'useNull' => true,
                    ],
                ],
            ],
            [
                (new Type())
                    ->setFields([
                        (new Field())
                            ->setName('test')
                            ->setType(new TextField())
                            ->setRequired(true),
                    ]),
                [
                    [
                        'name' => 'id',
                        'type' => 'int',
                    ],
                    [
                        'name' => 'test',
                        'type' => 'string',
                        'useNull' => false,
                    ],
                ],
            ],
            [
                (new Type())
                    ->setFields([
                        (new Field())
                            ->setName('test')
                            ->setType(new IntegerField())
                            ->setRequired(true),
                    ]),
                [
                    [
                        'name' => 'id',
                        'type' => 'int',
                    ],
                    [
                        'name' => 'test',
                        'type' => 'int',
                        'useNull' => false,
                    ],
                ],
            ],
        ];
    }

    public function dataProviderColumnFields(): array
    {
        return [
            [
                (new Type())
                    ->setFields([
                        (new Field())
                            ->setName('test')
                            ->setLabel('Test')
                            ->setShowListing(true)
                            ->setType(new TextField()),
                    ]),
                [
                    'test' => [
                        'header' => 'Test',
                    ],
                ],
            ],
            [
                (new Type())
                    ->setFields([
                        (new Field())
                            ->setName('test')
                            ->setLabel('Test')
                            ->setShowListing(false)
                            ->setType(new TextField()),
                    ]),
                [
                ],
            ],
        ];
    }

    public function dataProviderFieldSets(): array
    {
        return [
            [
                (new Type())
                    ->setFieldSets([
                        (new Fieldset())
                            ->setLabel('Test')
                            ->setFields([
                                (new Field())
                                    ->setName('test')
                                    ->setLabel('Test')
                                    ->setType(new TextField()),
                            ]),
                    ]),
                [
                    [
                        'title' => 'Test',
                        'autoScroll' => true,
                        'fields' => [
                            'test' => [
                                'fieldLabel' => 'Test',
                                'xtype' => 'textfield',
                                'anchor' => '100%',
                                'translatable' => false,
                                'supportText' => null,
                                'helpText' => null,
                                'allowBlank' => true,
                            ],
                        ],
                        'anchor' => '100%',
                    ],
                ],
            ],
            [
                (new Type())
                    ->setFieldSets([
                        (new Fieldset())
                            ->setLabel('Test')
                            ->setFields([
                                (new Field())
                                    ->setName('test')
                                    ->setLabel('Test')
                                    ->setType(new TextField())
                                    ->setRequired(true),
                            ]),
                    ]),
                [
                    [
                        'title' => 'Test',
                        'autoScroll' => true,
                        'fields' => [
                            'test' => [
                                'fieldLabel' => 'Test',
                                'xtype' => 'textfield',
                                'anchor' => '100%',
                                'translatable' => false,
                                'supportText' => null,
                                'helpText' => null,
                                'allowBlank' => false,
                            ],
                        ],
                        'anchor' => '100%',
                    ],
                ],
            ],
        ];
    }
}
