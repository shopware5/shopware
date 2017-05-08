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
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => '{"syncData":{"assets":[{"abcdefg":"media\/this-is-no-link"}]},"elements":[{"componentId":null,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"data":[]}]}', 'assetsImported' => false]);

        $this->expectException(PresetAssetImportException::class);
        $this->expectExceptionMessage('The processed element could not be found in preset data.');
        $this->synchronizerService->importElementAssets($preset, 'key');
    }

    public function testAssetImportForElementWithBannerComponentShouldNotChangeElements()
    {
        $data = '{"syncData":{"assets":[{"abcdefg":"media\/this-is-no-link"}],"importedAssets":[]},"elements":[{"componentId":"emotion-components-banner","startRow":1,"startCol":1,"endRow":1,"endCol":1,"syncKey":"key"}]}';
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
        $preset = $this->presetResource->create(['name' => 'test', 'assetsImported' => false, 'presetData' => '{"showListing":false,"templateId":1,"active":false,"name":"testemotion","position":1,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":0,"seoTitle":"","seoKeywords":"","seoDescription":"","rows":20,"cols":4,"cellSpacing":10,"cellHeight":185,"articleHeight":2,"mode":"fluid","customerStreamId":null,"replacement":null,"elements":[{"componentId":"emotion-components-banner","startRow":1,"startCol":1,"endRow":1,"endCol":1,"cssClass":"","viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true}],"data":[{"componentId":"emotion-components-banner","fieldId":"bannerPosition","value":"center","key":"bannerPosition","valueType":""},{"componentId":"emotion-components-banner","fieldId":"file","value":"7143d7fbadfa4693b9eec507d9d37443","key":"file","valueType":""},{"componentId":"emotion-components-banner","fieldId":"bannerMapping","value":"null","key":"bannerMapping","valueType":"json"},{"componentId":"emotion-components-banner","fieldId":"link","value":"","key":"link","valueType":""},{"componentId":"emotion-components-banner","fieldId":"banner_link_target","value":"","key":"banner_link_target","valueType":""},{"componentId":"emotion-components-banner","fieldId":"title","value":"","key":"title","valueType":""}],"syncKey":"key"}],"syncData":{"assets":{"7143d7fbadfa4693b9eec507d9d37443":"data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs="}}}']);

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
        $data = '{"elements":[{"componentId":"emotion-components-banner-slider","startRow":1,"startCol":1,"endRow":1,"endCol":1,"syncKey":"key"}]}';
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
        $preset = $this->presetResource->create(['name' => 'test', 'assetsImported' => false, 'presetData' => '{"showListing":false,"templateId":1,"active":false,"name":"testemotion","position":1,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":0,"seoTitle":"","seoKeywords":"","seoDescription":"","rows":20,"cols":4,"cellSpacing":10,"cellHeight":185,"articleHeight":2,"mode":"fluid","customerStreamId":null,"replacement":null,"elements":[{"componentId":"emotion-components-banner-slider","startRow":1,"startCol":1,"endRow":1,"endCol":1,"cssClass":"","viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true}],"data":[{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_title","value":"Banner Slider","key":"banner_slider_title","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_arrows","value":"","key":"banner_slider_arrows","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_numbers","value":"","key":"banner_slider_numbers","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_scrollspeed","value":"500","key":"banner_slider_scrollspeed","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_rotation","value":"","key":"banner_slider_rotation","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_rotatespeed","value":"5000","key":"banner_slider_rotatespeed","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider","value":"[{\"position\":0,\"path\":\"6e0721b2c6977135b916ef286bcb49ec\",\"mediaId\":783,\"link\":\"\",\"altText\":\"\",\"title\":\"\"},{\"position\":1,\"path\":\"fc8001f834f6a5f0561080d134d53d29\",\"mediaId\":784,\"link\":\"\",\"altText\":\"\",\"title\":\"\"}]","key":"banner_slider","valueType":"json"}],"syncKey":"key"}],"syncData":{"assets":{"6e0721b2c6977135b916ef286bcb49ec":"data:image\/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=","fc8001f834f6a5f0561080d134d53d29":"data:image\/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs="}}}']);

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
