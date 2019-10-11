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

namespace Shopware\tests\Functional\Components\Emotion\Preset;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Components\Emotion\Preset\PresetLoader;

/**
 * @group EmotionPreset
 */
class PresetLoaderTest extends TestCase
{
    /** @var Connection */
    private $connection;

    /** @var PresetLoader */
    private $presetLoader;

    /** @var EmotionPreset */
    private $presetResource;

    protected function setUp(): void
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        $this->connection->executeQuery('DELETE FROM s_emotion_presets');

        $this->presetLoader = Shopware()->Container()->get('shopware.emotion.preset_loader');
        $this->presetResource = Shopware()->Container()->get('shopware.api.emotion_preset');
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
    }

    public function testPresetLoadingShouldFailMissingIdentifier()
    {
        $this->expectException('');
        $this->expectException(ORMException::class);
        $this->presetLoader->load(null);
    }

    public function testPresetLoadingShouldFailNoResult()
    {
        $this->expectException(NoResultException::class);
        $this->presetLoader->load(-1);
    }

    public function testPresetLoadingShouldBeSuccessfulWithoutElements()
    {
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => '[]', 'assetsImported' => true]);

        $presetData = $this->presetLoader->load($preset->getId());

        static::assertIsString($presetData);
        static::assertJson($presetData);
        static::assertEquals('[]', $presetData);
    }

    public function testPresetLoadingShouldBeSuccessfulWithEmptyElements()
    {
        $data = '{"elements":[]}';
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => $data, 'assetsImported' => true]);

        $presetData = $this->presetLoader->load($preset->getId());

        static::assertIsString($presetData);
        static::assertJson($presetData);
        static::assertEquals($data, $presetData);
    }

    public function testPresetLoadingShouldBeSuccessful()
    {
        $data = '{"showListing":false,"templateId":1,"active":false,"position":1,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":0,"seoTitle":"","seoKeywords":"","seoDescription":"","rows":20,"cols":4,"cellSpacing":10,"cellHeight":185,"articleHeight":2,"mode":"fluid","customerStreamId":null,"replacement":null,"elements":[{"componentId":"emotion-components-banner","startRow":1,"startCol":1,"endRow":1,"endCol":1,"cssClass":"","viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true}],"data":[{"componentId":"emotion-components-banner","fieldId":"bannerPosition","value":"center","key":"bannerPosition","valueType":""},{"componentId":"emotion-components-banner","fieldId":"file","value":"media/image/sommerwelten_top_banner.jpg","key":"file","valueType":""},{"componentId":"emotion-components-banner","fieldId":"bannerMapping","value":"null","key":"bannerMapping","valueType":"json"},{"componentId":"emotion-components-banner","fieldId":"link","value":"","key":"link","valueType":""},{"componentId":"emotion-components-banner","fieldId":"banner_link_target","value":"","key":"banner_link_target","valueType":""},{"componentId":"emotion-components-banner","fieldId":"title","value":"","key":"title","valueType":""}],"syncKey":"preset-element-590245eaa16407.36627766"}]}';
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => $data, 'assetsImported' => true]);

        $presetData = $this->presetLoader->load($preset->getId());

        static::assertIsString($presetData);
        static::assertJson($presetData);

        $decodedData = json_decode($presetData, true);

        $componentId = $this->connection->fetchColumn('SELECT id FROM s_library_component WHERE name = "Banner"');
        $fieldId = $this->connection->fetchColumn('SELECT id FROM s_library_component_field WHERE name = "file" AND componentID = :componentId', ['componentId' => $componentId]);

        static::assertEquals($componentId, $decodedData['elements'][0]['componentId']);
        static::assertEquals($componentId, $decodedData['elements'][0]['component']['id']);

        static::assertEquals($fieldId, $decodedData['elements'][0]['component']['fields'][0]['id']);
        static::assertEquals($fieldId, $decodedData['elements'][0]['data'][1]['fieldId']);
        static::assertRegExp('/http/', $decodedData['elements'][0]['data'][1]['value']);
    }

    public function testShouldBeSuccessfulWithJsonEncodedDataValue()
    {
        $data = '{"showListing":false,"templateId":1,"active":false,"position":1,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":0,"seoTitle":"","seoKeywords":"","seoDescription":"","rows":20,"cols":4,"cellSpacing":10,"cellHeight":185,"articleHeight":2,"mode":"fluid","customerStreamId":null,"replacement":null,"elements":[{"componentId":"emotion-components-banner-slider","startRow":1,"startCol":1,"endRow":1,"endCol":1,"cssClass":"","viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":false},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":false},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":false},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":false},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true}],"data":[{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_title","value":"","key":"banner_slider_title","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_arrows","value":"","key":"banner_slider_arrows","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_numbers","value":"","key":"banner_slider_numbers","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_scrollspeed","value":"500","key":"banner_slider_scrollspeed","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_rotation","value":"","key":"banner_slider_rotation","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider_rotatespeed","value":"5000","key":"banner_slider_rotatespeed","valueType":""},{"componentId":"emotion-components-banner-slider","fieldId":"banner_slider","value":"[{\"position\":0,\"path\":\"media\\/image\\/sommerwelten_top_banner.jpg\",\"mediaId\":779,\"link\":\"\",\"altText\":\"\",\"title\":\"\"}]","key":"banner_slider","valueType":"json"}],"syncKey":"preset-element-590246ba120476.51970241"}]}';

        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => $data, 'assetsImported' => true]);

        $presetData = $this->presetLoader->load($preset->getId());

        static::assertIsString($presetData);
        static::assertJson($presetData);

        $decodedData = json_decode($presetData, true);

        $componentId = $this->connection->fetchColumn('SELECT id FROM s_library_component WHERE name = "Banner-Slider"');

        static::assertEquals($componentId, $decodedData['elements'][0]['componentId']);
        static::assertEquals($componentId, $decodedData['elements'][0]['component']['id']);

        static::assertIsArray($decodedData['elements'][0]['data'][6]['value']);
        static::assertRegExp('/http/', $decodedData['elements'][0]['data'][6]['value'][0]['path']);
    }
}
