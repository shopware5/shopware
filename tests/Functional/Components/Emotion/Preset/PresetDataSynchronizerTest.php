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

        $this->imageData = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
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
        $preset = $this->presetResource->create(['name' => 'test', 'assetsImported' => false, 'presetData' => '{"id":null,"active":false,"articleHeight":2,"cellHeight":185,"cellSpacing":10,"cols":4,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":false,"mode":"fluid","position":1,"rows":20,"showListing":false,"templateId":1,"elements":[{"assets":{"assetkey":"' . $this->imageData . '"},"componentId":3,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"data":[{"id":4275,"fieldId":65,"valueType":"","key":"bannerPosition","value":"center"},{"id":4276,"fieldId":3,"valueType":"","key":"file","value": "assetkey"},{"id":4277,"fieldId":7,"valueType":"json","key":"bannerMapping","value":null},{"id":4278,"fieldId":47,"valueType":"","key":"link","value":""},{"id":4279,"fieldId":89,"valueType":"","key":"banner_link_target","value":""},{"id":4280,"fieldId":85,"valueType":"","key":"title","value":""}],"viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true}],"component":{"id":3,"pluginId":null,"name":"Banner","description":"","xType":"emotion-components-banner","template":"component_banner","cls":"banner-element","fieldLabel":"Banner","fields":[{"id":3,"componentId":3,"name":"file","xType":"mediaselectionfield","valueType":"","fieldLabel":"Bild","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":3},{"id":7,"componentId":3,"name":"bannerMapping","xType":"hidden","valueType":"json","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":7},{"id":47,"componentId":3,"name":"link","xType":"textfield","valueType":"","fieldLabel":"Link","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":47},{"id":65,"componentId":3,"name":"bannerPosition","xType":"hidden","valueType":"","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"center","translatable":0,"position":0},{"id":85,"componentId":3,"name":"title","xType":"textfield","valueType":"","fieldLabel":"Title Text","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":50},{"id":89,"componentId":3,"name":"banner_link_target","xType":"emotion-components-fields-link-target","valueType":"","fieldLabel":"Link-Ziel","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":0,"position":48}]},"syncKey":"key"}]}']);

        $this->synchronizerService->importElementAssets($preset, 'key');
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        $this->assertEquals(1, count($presets));

        $createdPreset = $presets[0];

        $this->assertJson($createdPreset['preset_data']);

        $presetData = json_decode($createdPreset['preset_data'], true);

        $this->assertArrayHasKey('elements', $presetData);
        $this->assertArrayHasKey('data', $presetData['elements'][0]);
        $this->assertRegExp('/media/', $presetData['elements'][0]['data'][1]['value']);
        $this->assertNotEquals($this->imageData, $presetData['elements'][0]['data'][1]['value']);
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
        $preset = $this->presetResource->create(['name' => 'test', 'assetsImported' => false, 'presetData' => '{"showListing":false,"templateId":1,"active":false,"position":1,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":0,"seoTitle":"","seoKeywords":"","seoDescription":"","rows":20,"cols":4,"cellSpacing":10,"cellHeight":185,"articleHeight":2,"mode":"fluid","elements":[{"assets":{"assetkey":"' . $this->imageData . '"},"componentId":7,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"cssClass":"","viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":3,"visible":true}],"component":{"id":7,"name":"Banner-Slider","convertFunction":"getBannerSlider","description":"","template":"component_banner_slider","cls":"banner-slider-element","xType":"emotion-components-banner-slider","pluginId":null,"fields":[{"id":13,"componentId":7,"name":"banner_slider_title","fieldLabel":"\u00dcberschrift","xType":"textfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":1,"helpTitle":"","helpText":"","translatable":1,"position":13},{"id":15,"componentId":7,"name":"banner_slider_arrows","fieldLabel":"Pfeile anzeigen","xType":"checkbox","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":15},{"id":16,"componentId":7,"name":"banner_slider_numbers","fieldLabel":"Nummern ausgeben","xType":"checkbox","valueType":"","supportText":"Bitte beachten Sie, dass diese Einstellung nur Auswirkungen auf das \"Emotion\"-Template hat.","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":16},{"id":17,"componentId":7,"name":"banner_slider_scrollspeed","fieldLabel":"Scroll-Geschwindigkeit","xType":"numberfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":17},{"id":18,"componentId":7,"name":"banner_slider_rotation","fieldLabel":"Automatisch rotieren","xType":"checkbox","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":18},{"id":19,"componentId":7,"name":"banner_slider_rotatespeed","fieldLabel":"Rotations Geschwindigkeit","xType":"numberfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"5000","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":19},{"id":20,"componentId":7,"name":"banner_slider","fieldLabel":"","xType":"hidden","valueType":"json","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":20}]},"data":[{"componentId":7,"fieldId":13,"value":"","key":"banner_slider_title","valueType":""},{"componentId":7,"fieldId":15,"value":"","key":"banner_slider_arrows","valueType":""},{"componentId":7,"fieldId":16,"value":"","key":"banner_slider_numbers","valueType":""},{"componentId":7,"fieldId":17,"value":"500","key":"banner_slider_scrollspeed","valueType":""},{"componentId":7,"fieldId":18,"value":"","key":"banner_slider_rotation","valueType":""},{"componentId":7,"fieldId":19,"value":"5000","key":"banner_slider_rotatespeed","valueType":""},{"componentId":7,"fieldId":20,"value":"[{\"position\":0,\"path\":\"assetkey\",\"mediaId\":791,\"link\":\"\",\"altText\":\"\",\"title\":\"\"}]","key":"banner_slider","valueType":"json"}],"syncKey":"key"}]}']);

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
