<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\PropertyHydrator;

class PropertyHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PropertyHydrator
     */
    protected $hydrator;

    public function setUp(): void
    {
        parent::setUp();
        $this->hydrator = Shopware()->Container()->get(PropertyHydrator::class);
    }

    /**
     * @param array<string, string> $input
     * @param array<string, string> $group
     * @dataProvider hydrateDataProvider
     */
    public function testHydrate(array $input, array $group): void
    {
        $set = json_decode(json_encode($this->hydrator->hydrateValues([$input])[1], JSON_THROW_ON_ERROR), true);

        static::assertEquals($group, $set);
    }

    /**
     * @return array<int, array>
     */
    public function hydrateDataProvider(): iterable
    {
        // With English Translation
        yield [
            [
                '__relations_position' => '4',
                '__propertySet_id' => '1',
                '__propertySet_name' => 'Edelbrände',
                '__propertySet_position' => '0',
                '__propertySet_comparable' => '1',
                '__propertySet_sortmode' => '2',
                '__propertySetAttribute_id' => null,
                '__propertySetAttribute_filterID' => null,
                '__propertyGroup_id' => '1',
                '__propertyGroup_name' => 'Alkoholgehalt',
                '__propertyGroup_filterable' => '1',
                '__propertyGroupAttribute_id' => '1',
                '__propertyGroupAttribute_optionID' => '1',
                '__propertyGroupAttribute_kek' => 'de',
                '__propertyOption_id' => '39',
                '__propertyOption_optionID' => '1',
                '__propertyOption_value' => '< 20%',
                '__propertyOption_position' => '7',
                '__propertyOptionAttribute_id' => null,
                '__propertyOptionAttribute_valueID' => null,
                '__media_id' => null,
                '__media_albumID' => null,
                '__media_name' => null,
                '__media_description' => null,
                '__media_path' => null,
                '__media_type' => null,
                '__media_extension' => null,
                '__media_file_size' => null,
                '__media_width' => null,
                '__media_height' => null,
                '__media_userID' => null,
                '__media_created' => null,
                '__mediaSettings_id' => null,
                '__mediaSettings_create_thumbnails' => null,
                '__mediaSettings_thumbnail_size' => null,
                '__mediaSettings_icon' => null,
                '__mediaSettings_thumbnail_high_dpi' => null,
                '__mediaAttribute_id' => null,
                '__mediaAttribute_mediaID' => null,
                '__mediaAttribute_translation' => null,
                '__propertySet_translation' => 'a:1:{s:9:"groupName";s:6:"Brandy";}',
                '__propertyGroup_translation' => 'a:2:{s:10:"optionName";s:12:"Alcohol in %";s:15:"__attribute_kek";s:2:"en";}',
                '__propertyOption_translation' => null,
            ],
            [
                'id' => 1,
                'name' => 'Brandy',
                'comparable' => true,
                'groups' => [1 => [
                    'id' => 1,
                    'name' => 'Alcohol in %',
                    'filterable' => true,
                    'options' => [[
                        'id' => 39,
                        'name' => '< 20%',
                        'media' => null,
                        'position' => 7,
                        'attributes' => [],
                    ],
                    ],
                    'attributes' => [
                        'core' => [
                            'id' => '1',
                            'optionID' => '1',
                            'kek' => 'en',
                        ],
                    ],
                ],
                ],
                'sortMode' => 2,
                'attributes' => [],
            ],
        ];

        // Without Translation
        yield [
            [
                '__relations_position' => '4',
                '__propertySet_id' => '1',
                '__propertySet_name' => 'Edelbrände',
                '__propertySet_position' => '0',
                '__propertySet_comparable' => '1',
                '__propertySet_sortmode' => '2',
                '__propertySetAttribute_id' => null,
                '__propertySetAttribute_filterID' => null,
                '__propertyGroup_id' => '1',
                '__propertyGroup_name' => 'Alkoholgehalt',
                '__propertyGroup_filterable' => '1',
                '__propertyGroupAttribute_id' => '1',
                '__propertyGroupAttribute_optionID' => '1',
                '__propertyGroupAttribute_kek' => 'de',
                '__propertyOption_id' => '39',
                '__propertyOption_optionID' => '1',
                '__propertyOption_value' => '< 20%',
                '__propertyOption_position' => '7',
                '__propertyOptionAttribute_id' => null,
                '__propertyOptionAttribute_valueID' => null,
                '__media_id' => null,
                '__media_albumID' => null,
                '__media_name' => null,
                '__media_description' => null,
                '__media_path' => null,
                '__media_type' => null,
                '__media_extension' => null,
                '__media_file_size' => null,
                '__media_width' => null,
                '__media_height' => null,
                '__media_userID' => null,
                '__media_created' => null,
                '__mediaSettings_id' => null,
                '__mediaSettings_create_thumbnails' => null,
                '__mediaSettings_thumbnail_size' => null,
                '__mediaSettings_icon' => null,
                '__mediaSettings_thumbnail_high_dpi' => null,
                '__mediaAttribute_id' => null,
                '__mediaAttribute_mediaID' => null,
                '__mediaAttribute_translation' => null,
                '__propertySet_translation' => 'a:1:{s:9:"groupName";s:6:"Brandy";}',
                '__propertyGroup_translation' => null,
                '__propertyOption_translation' => null,
            ],
            [
                'id' => 1,
                'name' => 'Brandy',
                'comparable' => true,
                'groups' => [1 => [
                    'id' => 1,
                    'name' => 'Alkoholgehalt',
                    'filterable' => true,
                    'options' => [[
                        'id' => 39,
                        'name' => '< 20%',
                        'media' => null,
                        'position' => 7,
                        'attributes' => [],
                    ],
                    ],
                    'attributes' => [
                        'core' => [
                            'id' => '1',
                            'optionID' => '1',
                            'kek' => 'de',
                        ],
                    ],
                ],
                ],
                'sortMode' => 2,
                'attributes' => [],
            ],
        ];
    }
}
