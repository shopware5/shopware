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

namespace Shopware\Tests\Unit\Components\Emotion\Preset;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Emotion\Preset\EmotionToPresetDataTransformer;
use Shopware\Components\Model\ModelManager;

/**
 * @group EmotionPreset
 */
class EmotionToPresetDataTransformerTest extends TestCase
{
    /**
     * @var EmotionToPresetDataTransformer
     */
    private $transformer;

    protected function setUp()
    {
        $this->transformer = new EmotionToPresetDataTransformer($this->createMock(ModelManager::class));
    }

    public function testCleanupEmotionDataShouldBeSuccessful()
    {
        $method = new \ReflectionMethod($this->transformer, 'cleanupEmotionData');
        $method->setAccessible(true);

        $transformedData = $method->invoke($this->transformer, $this->getEmotionData());

        $this->assertInternalType('array', $transformedData);
        $this->assertJson($transformedData['presetData']);
        $this->assertInternalType('array', $transformedData['requiredPlugins']);

        $decodedData = json_decode($transformedData['presetData'], true);

        $this->assertInternalType('array', $decodedData);
        $this->assertArrayNotHasKey('id', $decodedData);
        $this->assertArrayNotHasKey('parentId', $decodedData);
        $this->assertArrayNotHasKey('name', $decodedData);
        $this->assertArrayNotHasKey('userId', $decodedData);
        $this->assertArrayNotHasKey('validFrom', $decodedData);
        $this->assertArrayNotHasKey('validTo', $decodedData);
        $this->assertArrayNotHasKey('createDate', $decodedData);
        $this->assertArrayNotHasKey('modified', $decodedData);
        $this->assertArrayNotHasKey('previewId', $decodedData);
        $this->assertArrayNotHasKey('previewSecret', $decodedData);

        $this->assertEquals(false, $decodedData['active']);

        $elements = $decodedData['elements'];

        foreach ($elements as $element) {
            $this->assertArrayNotHasKey('id', $element);
            $this->assertArrayNotHasKey('emotionId', $element);
            $this->assertArrayHasKey('syncKey', $element);
            $this->assertContains('preset-element-', $element['syncKey']);
            $this->assertContains('emotion-components-banner', $element['componentId']);

            foreach ($element['data'] as $data) {
                $this->assertArrayNotHasKey('id', $data);
                $this->assertArrayNotHasKey('emotionId', $data);
                $this->assertArrayNotHasKey('elementId', $data);
                $this->assertArrayHasKey('key', $data);
                $this->assertArrayHasKey('valueType', $data);
                $this->assertContains('emotion-components-banner', $data['componentId']);
            }

            foreach ($element['viewports'] as $viewport) {
                $this->assertArrayNotHasKey('id', $viewport);
                $this->assertArrayNotHasKey('emotionId', $viewport);
                $this->assertArrayNotHasKey('elementId', $viewport);
            }
        }
    }

    /**
     * @return array
     */
    private function getEmotionData()
    {
        return [
            'showListing' => false,
            'templateId' => 1,
            'id' => 1,
            'parentId' => null,
            'active' => 1,
            'name' => 'Meine Startseite',
            'userId' => 1,
            'position' => 1,
            'device' => '0,1,2,3,4',
            'fullscreen' => 0,
            'validFrom' => null,
            'isLandingPage' => 0,
            'seoTitle' => '',
            'seoKeywords' => '',
            'seoDescription' => '',
            'validTo' => null,
            'rows' => 22,
            'cols' => 4,
            'cellSpacing' => 10,
            'cellHeight' => 185,
            'articleHeight' => 2,
            'mode' => 'fluid',
            'previewId' => null,
            'previewSecret' => null,
            'elements' => [
                    0 => [
                            'id' => 1018,
                            'emotionId' => 1,
                            'componentId' => 3,
                            'startRow' => 1,
                            'startCol' => 4,
                            'endRow' => 3,
                            'endCol' => 4,
                            'cssClass' => null,
                            'viewports' => [
                                    0 => [
                                            'id' => 6,
                                            'elementId' => 1018,
                                            'emotionId' => 1,
                                            'alias' => 'xs',
                                            'startRow' => 5,
                                            'startCol' => 3,
                                            'endRow' => 6,
                                            'endCol' => 4,
                                            'visible' => true,
                                        ],
                                    1 => [
                                            'id' => 7,
                                            'elementId' => 1018,
                                            'emotionId' => 1,
                                            'alias' => 's',
                                            'startRow' => 5,
                                            'startCol' => 3,
                                            'endRow' => 6,
                                            'endCol' => 4,
                                            'visible' => true,
                                        ],
                                    2 => [
                                            'id' => 8,
                                            'elementId' => 1018,
                                            'emotionId' => 1,
                                            'alias' => 'm',
                                            'startRow' => 5,
                                            'startCol' => 3,
                                            'endRow' => 6,
                                            'endCol' => 4,
                                            'visible' => true,
                                        ],
                                    3 => [
                                            'id' => 9,
                                            'elementId' => 1018,
                                            'emotionId' => 1,
                                            'alias' => 'l',
                                            'startRow' => 1,
                                            'startCol' => 4,
                                            'endRow' => 3,
                                            'endCol' => 4,
                                            'visible' => true,
                                        ],
                                    4 => [
                                            'id' => 10,
                                            'elementId' => 1018,
                                            'emotionId' => 1,
                                            'alias' => 'xl',
                                            'startRow' => 1,
                                            'startCol' => 4,
                                            'endRow' => 3,
                                            'endCol' => 4,
                                            'visible' => true,
                                        ],
                                ],
                            'component' => [
                                    'id' => 3,
                                    'name' => 'Banner',
                                    'convertFunction' => 'getBannerMappingLinks',
                                    'description' => '',
                                    'template' => 'component_banner',
                                    'cls' => 'banner-element',
                                    'xType' => 'emotion-components-banner',
                                    'pluginId' => null,
                                    'fields' => [
                                            0 => [
                                                    'id' => 3,
                                                    'componentId' => 3,
                                                    'name' => 'file',
                                                    'fieldLabel' => 'Bild',
                                                    'xType' => 'mediaselectionfield',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 0,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 0,
                                                    'position' => 3,
                                                ],
                                            1 => [
                                                    'id' => 7,
                                                    'componentId' => 3,
                                                    'name' => 'bannerMapping',
                                                    'fieldLabel' => '',
                                                    'xType' => 'hidden',
                                                    'valueType' => 'json',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 0,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 0,
                                                    'position' => 7,
                                                ],
                                            2 => [
                                                    'id' => 47,
                                                    'componentId' => 3,
                                                    'name' => 'link',
                                                    'fieldLabel' => 'Link',
                                                    'xType' => 'textfield',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 1,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 1,
                                                    'position' => 47,
                                                ],
                                            3 => [
                                                    'id' => 65,
                                                    'componentId' => 3,
                                                    'name' => 'bannerPosition',
                                                    'fieldLabel' => '',
                                                    'xType' => 'hidden',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => 'center',
                                                    'allowBlank' => 0,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 0,
                                                    'position' => null,
                                                ],
                                            4 => [
                                                    'id' => 85,
                                                    'componentId' => 3,
                                                    'name' => 'title',
                                                    'fieldLabel' => 'Title Text',
                                                    'xType' => 'textfield',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 1,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 1,
                                                    'position' => 50,
                                                ],
                                            5 => [
                                                    'id' => 89,
                                                    'componentId' => 3,
                                                    'name' => 'banner_link_target',
                                                    'fieldLabel' => 'Link-Ziel',
                                                    'xType' => 'emotion-components-fields-link-target',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 1,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 0,
                                                    'position' => 48,
                                                ],
                                        ],
                                ],
                            'data' => [
                                    0 => [
                                            'id' => 2810,
                                            'emotionId' => 1,
                                            'elementId' => 1018,
                                            'componentId' => 3,
                                            'fieldId' => 3,
                                            'value' => 'media/image/beach_teaser5038874e87338.jpg',
                                        ],
                                    1 => [
                                            'id' => 2811,
                                            'emotionId' => 1,
                                            'elementId' => 1018,
                                            'componentId' => 3,
                                            'fieldId' => 7,
                                            'value' => '[{"x":"0","y":"356","width":"251","height":"198","link":"SW10211","resizerIndex":0,"path":""},{"x":"0","y":"184","width":"251","height":"176","link":"SW10170","resizerIndex":1,"path":""},{"x":"0","y":"0","width":"251","height":"188","link":"SW10178","resizerIndex":2,"path":""}]',
                                        ],
                                    2 => [
                                            'id' => 2812,
                                            'emotionId' => 1,
                                            'elementId' => 1018,
                                            'componentId' => 3,
                                            'fieldId' => 47,
                                            'value' => '',
                                        ],
                                ],
                        ],
                    1 => [
                            'id' => 1020,
                            'emotionId' => 1,
                            'componentId' => 3,
                            'startRow' => 4,
                            'startCol' => 1,
                            'endRow' => 4,
                            'endCol' => 2,
                            'cssClass' => null,
                            'viewports' => [
                                    0 => [
                                            'id' => 11,
                                            'elementId' => 1020,
                                            'emotionId' => 1,
                                            'alias' => 'xs',
                                            'startRow' => 4,
                                            'startCol' => 1,
                                            'endRow' => 4,
                                            'endCol' => 4,
                                            'visible' => true,
                                        ],
                                    1 => [
                                            'id' => 12,
                                            'elementId' => 1020,
                                            'emotionId' => 1,
                                            'alias' => 's',
                                            'startRow' => 4,
                                            'startCol' => 1,
                                            'endRow' => 4,
                                            'endCol' => 4,
                                            'visible' => true,
                                        ],
                                    2 => [
                                            'id' => 13,
                                            'elementId' => 1020,
                                            'emotionId' => 1,
                                            'alias' => 'm',
                                            'startRow' => 4,
                                            'startCol' => 1,
                                            'endRow' => 4,
                                            'endCol' => 4,
                                            'visible' => true,
                                        ],
                                    3 => [
                                            'id' => 14,
                                            'elementId' => 1020,
                                            'emotionId' => 1,
                                            'alias' => 'l',
                                            'startRow' => 4,
                                            'startCol' => 1,
                                            'endRow' => 4,
                                            'endCol' => 2,
                                            'visible' => true,
                                        ],
                                    4 => [
                                            'id' => 15,
                                            'elementId' => 1020,
                                            'emotionId' => 1,
                                            'alias' => 'xl',
                                            'startRow' => 4,
                                            'startCol' => 1,
                                            'endRow' => 4,
                                            'endCol' => 2,
                                            'visible' => true,
                                        ],
                                ],
                            'component' => [
                                    'id' => 3,
                                    'name' => 'Banner',
                                    'convertFunction' => 'getBannerMappingLinks',
                                    'description' => '',
                                    'template' => 'component_banner',
                                    'cls' => 'banner-element',
                                    'xType' => 'emotion-components-banner',
                                    'pluginId' => null,
                                    'fields' => [
                                            0 => [
                                                    'id' => 3,
                                                    'componentId' => 3,
                                                    'name' => 'file',
                                                    'fieldLabel' => 'Bild',
                                                    'xType' => 'mediaselectionfield',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 0,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 0,
                                                    'position' => 3,
                                                ],
                                            1 => [
                                                    'id' => 7,
                                                    'componentId' => 3,
                                                    'name' => 'bannerMapping',
                                                    'fieldLabel' => '',
                                                    'xType' => 'hidden',
                                                    'valueType' => 'json',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 0,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 0,
                                                    'position' => 7,
                                                ],
                                            2 => [
                                                    'id' => 47,
                                                    'componentId' => 3,
                                                    'name' => 'link',
                                                    'fieldLabel' => 'Link',
                                                    'xType' => 'textfield',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 1,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 1,
                                                    'position' => 47,
                                                ],
                                            3 => [
                                                    'id' => 65,
                                                    'componentId' => 3,
                                                    'name' => 'bannerPosition',
                                                    'fieldLabel' => '',
                                                    'xType' => 'hidden',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => 'center',
                                                    'allowBlank' => 0,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 0,
                                                    'position' => null,
                                                ],
                                            4 => [
                                                    'id' => 85,
                                                    'componentId' => 3,
                                                    'name' => 'title',
                                                    'fieldLabel' => 'Title Text',
                                                    'xType' => 'textfield',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 1,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 1,
                                                    'position' => 50,
                                                ],
                                            5 => [
                                                    'id' => 89,
                                                    'componentId' => 3,
                                                    'name' => 'banner_link_target',
                                                    'fieldLabel' => 'Link-Ziel',
                                                    'xType' => 'emotion-components-fields-link-target',
                                                    'valueType' => '',
                                                    'supportText' => '',
                                                    'store' => '',
                                                    'displayField' => '',
                                                    'valueField' => '',
                                                    'defaultValue' => '',
                                                    'allowBlank' => 1,
                                                    'helpTitle' => '',
                                                    'helpText' => '',
                                                    'translatable' => 0,
                                                    'position' => 48,
                                                ],
                                        ],
                                ],
                            'data' => [
                                    0 => [
                                            'id' => 2816,
                                            'emotionId' => 1,
                                            'elementId' => 1020,
                                            'componentId' => 3,
                                            'fieldId' => 3,
                                            'value' => 'media/image/deli_teaser503886c2336e3.jpg',
                                        ],
                                    1 => [
                                            'id' => 2817,
                                            'emotionId' => 1,
                                            'elementId' => 1020,
                                            'componentId' => 3,
                                            'fieldId' => 7,
                                            'value' => 'null',
                                        ],
                                    2 => [
                                            'id' => 2818,
                                            'emotionId' => 1,
                                            'elementId' => 1020,
                                            'componentId' => 3,
                                            'fieldId' => 47,
                                            'value' => '/Campaign/index/emotionId/6',
                                        ],
                                ],
                        ],
                ],
        ];
    }
}
