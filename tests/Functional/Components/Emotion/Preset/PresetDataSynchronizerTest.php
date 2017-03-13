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

namespace Shopware\Tests\Functional\Components\Emotion\Preset;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Components\Emotion\Preset\Exception\PresetAssetImportException;
use Shopware\Components\Emotion\Preset\PresetDataSynchronizer;

/**
 * @group EmotionPreset
 */
class PresetDataSynchronizerTest extends TestCase
{
    /** @var PresetDataSynchronizer */
    private $synchronizerService;

    /** @var EmotionPreset */
    private $presetResource;

    /** @var Connection */
    private $connection;

    /** @var string */
    private $imageData;

    protected function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        $this->connection->executeQuery('DELETE FROM s_emotion_presets');
        $this->connection->executeQuery('DELETE FROM s_core_plugins');

        $this->synchronizerService = Shopware()->Container()->get('shopware.emotion.preset_data_synchronizer');
        $this->presetResource = Manager::getResource('EmotionPreset');

        $this->imageData = 'data:image/png;base64,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';
    }

    protected function tearDown()
    {
        $this->connection->rollBack();
    }

    /**
     * @expectedException
     */
    public function testAssetImportWithPresetAlreadyImported()
    {
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => '[]', 'assetsImported' => true]);

        $this->expectException(PresetAssetImportException::class);
        $this->expectExceptionMessage('The assets for this preset are already imported.');
        $this->synchronizerService->importElementAssets($preset, 'key');
    }

    /**
     * @expectedException
     */
    public function testAssetImportWithWrongPresetData()
    {
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => 'wrongData', 'assetsImported' => false]);

        $this->expectException(PresetAssetImportException::class);
        $this->expectExceptionMessage('The preset data of the ' . $preset->getName() . ' preset seems to be invalid.');
        $this->synchronizerService->importElementAssets($preset, 'key');
    }

    /**
     * @expectedException
     */
    public function testAssetImportWithMissingElementKey()
    {
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => '{"elements":[{"componentId":null,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"data":[]}]}', 'assetsImported' => false]);

        $this->expectException(PresetAssetImportException::class);
        $this->expectExceptionMessage('The processed element could not be found in preset data.');
        $this->synchronizerService->importElementAssets($preset, 'key');
    }

    /**
     * @expectedException
     */
    public function testAssetImportWithUnknownElementComponent()
    {
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => '{"elements":[{"syncKey":"key","componentId":null,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"data":[],"component":{"id":7,"pluginId":null,"name":"Unknown component","description":"","xType":"unknown-component","template":"unknown-component","cls":"unknown-component","fieldLabel":"Unknown component","fields":[]}}]}', 'assetsImported' => false]);

        $this->expectException(PresetAssetImportException::class);
        $this->expectExceptionMessage('Element handler not found. Import not possible.');
        $this->synchronizerService->importElementAssets($preset, 'key');
    }

    /**
     * @expectedException
     */
    public function testAssetImportWithMissingElementComponentXtype()
    {
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => '{"elements":[{"syncKey":"key","componentId":null,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"data":[],"component":{"id":7,"pluginId":null,"name":"Unknown component","description":"","template":"unknown-component","cls":"unknown-component","fieldLabel":"Unknown component","fields":[]}}]}', 'assetsImported' => false]);

        $this->expectException(PresetAssetImportException::class);
        $this->expectExceptionMessage('Element handler not found. Import not possible.');
        $this->synchronizerService->importElementAssets($preset, 'key');
    }

    public function testAssetImportForElementWithBannerComponentShouldNotChangeElements()
    {
        $data = '{"elements":[{"componentId":3,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"component":{"id":3,"pluginId":null,"name":"Banner","description":"","xType":"emotion-components-banner","template":"component_banner","cls":"banner-element","fieldLabel":"Banner","fields":[{"id":3,"componentId":3,"name":"file","xType":"mediaselectionfield","valueType":"","fieldLabel":"Bild","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":3},{"id":7,"componentId":3,"name":"bannerMapping","xType":"hidden","valueType":"json","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":7},{"id":47,"componentId":3,"name":"link","xType":"textfield","valueType":"","fieldLabel":"Link","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":47},{"id":65,"componentId":3,"name":"bannerPosition","xType":"hidden","valueType":"","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"center","translatable":0,"position":0},{"id":85,"componentId":3,"name":"title","xType":"textfield","valueType":"","fieldLabel":"Title Text","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":50},{"id":89,"componentId":3,"name":"banner_link_target","xType":"emotion-components-fields-link-target","valueType":"","fieldLabel":"Link-Ziel","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":0,"position":48}]},"syncKey":"key"}]}';
        $preset = $this->presetResource->create(['name' => 'test', 'assetsImported' => false, 'presetData' => $data]);

        $this->synchronizerService->importElementAssets($preset, 'key');
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        $this->assertEquals(1, count($presets));
        $createdPreset = $presets[0];

        $this->assertJson($createdPreset['preset_data']);
        $this->assertEquals($data, $createdPreset['preset_data']);
    }

    public function testAssetImportForElementWithBannerComponent()
    {
        $preset = $this->presetResource->create(['name' => 'test', 'assetsImported' => false, 'presetData' => '{"id":null,"active":false,"articleHeight":2,"cellHeight":185,"cellSpacing":10,"cols":4,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":false,"mode":"fluid","position":1,"rows":20,"showListing":false,"templateId":1,"elements":[{"componentId":3,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"data":[{"id":4275,"fieldId":65,"valueType":"","key":"bannerPosition","value":"center"},{"id":4276,"fieldId":3,"valueType":"","key":"file","value": "' . $this->imageData . '"},{"id":4277,"fieldId":7,"valueType":"json","key":"bannerMapping","value":null},{"id":4278,"fieldId":47,"valueType":"","key":"link","value":""},{"id":4279,"fieldId":89,"valueType":"","key":"banner_link_target","value":""},{"id":4280,"fieldId":85,"valueType":"","key":"title","value":""}],"viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true}],"component":{"id":3,"pluginId":null,"name":"Banner","description":"","xType":"emotion-components-banner","template":"component_banner","cls":"banner-element","fieldLabel":"Banner","fields":[{"id":3,"componentId":3,"name":"file","xType":"mediaselectionfield","valueType":"","fieldLabel":"Bild","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":3},{"id":7,"componentId":3,"name":"bannerMapping","xType":"hidden","valueType":"json","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":7},{"id":47,"componentId":3,"name":"link","xType":"textfield","valueType":"","fieldLabel":"Link","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":47},{"id":65,"componentId":3,"name":"bannerPosition","xType":"hidden","valueType":"","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"center","translatable":0,"position":0},{"id":85,"componentId":3,"name":"title","xType":"textfield","valueType":"","fieldLabel":"Title Text","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":50},{"id":89,"componentId":3,"name":"banner_link_target","xType":"emotion-components-fields-link-target","valueType":"","fieldLabel":"Link-Ziel","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":0,"position":48}]},"syncKey":"key"}]}']);

        $this->synchronizerService->importElementAssets($preset, 'key');
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        $this->assertEquals(1, count($presets));

        $createdPreset = $presets[0];

        $this->assertJson($createdPreset['preset_data']);

        $presetData = json_decode($createdPreset['preset_data'], true);

        $this->assertArrayHasKey('elements', $presetData);
        $this->assertArrayHasKey('data', $presetData['elements'][0]);
        $this->assertRegExp('/media/', $presetData['elements'][0]['data'][1]['value']);
        $this->assertNotEquals($imageData, $presetData['elements'][0]['data'][1]['value']);
    }

    public function testAssetImportForElemementWithBannerSliderComponentShouldNotChangeElements()
    {
        $data = '{"elements":[{"componentId":7,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"component":{"id":7,"pluginId":null,"name":"Banner-Slider","description":"","xType":"emotion-components-banner-slider","template":"component_banner_slider","cls":"banner-slider-element","fieldLabel":"Banner-Slider","fields":[{"id":13,"componentId":7,"name":"banner_slider_title","xType":"textfield","valueType":"","fieldLabel":"Header","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":13},{"id":15,"componentId":7,"name":"banner_slider_arrows","xType":"checkbox","valueType":"","fieldLabel":"Pfeile anzeigen","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":15},{"id":16,"componentId":7,"name":"banner_slider_numbers","xType":"checkbox","valueType":"","fieldLabel":"Nummern ausgeben","supportText":"Bitte beachten Sie, dass diese Einstellung nur Auswirkungen auf das \"Emotion\"-Template hat.","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":16},{"id":17,"componentId":7,"name":"banner_slider_scrollspeed","xType":"numberfield","valueType":"","fieldLabel":"Scroll-Geschwindigkeit","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":17},{"id":18,"componentId":7,"name":"banner_slider_rotation","xType":"checkbox","valueType":"","fieldLabel":"Automatisch rotieren","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":18},{"id":19,"componentId":7,"name":"banner_slider_rotatespeed","xType":"numberfield","valueType":"","fieldLabel":"Rotations Geschwindigkeit","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"5000","translatable":0,"position":19},{"id":20,"componentId":7,"name":"banner_slider","xType":"hidden","valueType":"json","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":20}]},"syncKey":"key"}]}';
        $preset = $this->presetResource->create(['name' => 'test', 'assetsImported' => false, 'presetData' => $data]);

        $this->synchronizerService->importElementAssets($preset, 'key');
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        $this->assertEquals(1, count($presets));

        $createdPreset = $presets[0];

        $this->assertJson($createdPreset['preset_data']);
        $this->assertEquals($data, $createdPreset['preset_data']);
    }

    public function testAssetImportForElemementWithBannerSliderComponent()
    {
        $slider_data = '[{\"position\":0,\"path\":\"' . $this->imageData . '\",\"mediaId\":791,\"link\":\"\",\"altText\":\"\",\"title\":\"\"}]';
        $preset = $this->presetResource->create(['name' => 'test', 'assetsImported' => false, 'presetData' => '{"showListing":false,"templateId":1,"active":false,"position":1,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":0,"seoTitle":"","seoKeywords":"","seoDescription":"","rows":20,"cols":4,"cellSpacing":10,"cellHeight":185,"articleHeight":2,"mode":"fluid","elements":[{"componentId":7,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"cssClass":"","viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true}],"component":{"id":7,"name":"Banner-Slider","convertFunction":"getBannerSlider","description":"","template":"component_banner_slider","cls":"banner-slider-element","xType":"emotion-components-banner-slider","pluginId":null,"fields":[{"id":13,"componentId":7,"name":"banner_slider_title","fieldLabel":"\u00dcberschrift","xType":"textfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":1,"helpTitle":"","helpText":"","translatable":1,"position":13},{"id":15,"componentId":7,"name":"banner_slider_arrows","fieldLabel":"Pfeile anzeigen","xType":"checkbox","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":15},{"id":16,"componentId":7,"name":"banner_slider_numbers","fieldLabel":"Nummern ausgeben","xType":"checkbox","valueType":"","supportText":"Bitte beachten Sie, dass diese Einstellung nur Auswirkungen auf das \"Emotion\"-Template hat.","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":16},{"id":17,"componentId":7,"name":"banner_slider_scrollspeed","fieldLabel":"Scroll-Geschwindigkeit","xType":"numberfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":17},{"id":18,"componentId":7,"name":"banner_slider_rotation","fieldLabel":"Automatisch rotieren","xType":"checkbox","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":18},{"id":19,"componentId":7,"name":"banner_slider_rotatespeed","fieldLabel":"Rotations Geschwindigkeit","xType":"numberfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"5000","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":19},{"id":20,"componentId":7,"name":"banner_slider","fieldLabel":"","xType":"hidden","valueType":"json","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":20}]},"data":[{"componentId":7,"fieldId":13,"value":"","key":"banner_slider_title","valueType":""},{"componentId":7,"fieldId":15,"value":"","key":"banner_slider_arrows","valueType":""},{"componentId":7,"fieldId":16,"value":"","key":"banner_slider_numbers","valueType":""},{"componentId":7,"fieldId":17,"value":"500","key":"banner_slider_scrollspeed","valueType":""},{"componentId":7,"fieldId":18,"value":"","key":"banner_slider_rotation","valueType":""},{"componentId":7,"fieldId":19,"value":"5000","key":"banner_slider_rotatespeed","valueType":""},{"componentId":7,"fieldId":20,"value":"' . $slider_data . '","key":"banner_slider","valueType":"json"}],"syncKey":"key"}]}']);

        $this->synchronizerService->importElementAssets($preset, 'key');
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        $this->assertEquals(1, count($presets));

        $createdPreset = $presets[0];

        $this->assertJson($createdPreset['preset_data']);

        $presetData = json_decode($createdPreset['preset_data'], true);
        $this->assertArrayHasKey('elements', $presetData);

        $this->assertArrayHasKey('data', $presetData['elements'][0]);

        // double encoded value here
        $value = json_decode($presetData['elements'][0]['data'][6]['value'], true);

        $this->assertRegExp('/media/', $value[0]['path']);
        $this->assertNotEmpty($presetData['elements'][0]['data'][6]['value'][0]['mediaId']);
    }
}
