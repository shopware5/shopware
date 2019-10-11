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

namespace Shopware\Tests\Unit\Controller\Backend;

use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Models\Emotion\Preset;

/**
 * @group EmotionPreset
 */
class EmotionPresetTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var EmotionPreset
     */
    private $resource;

    /**
     * @var array
     */
    private $presetData;

    public function setUp(): void
    {
        parent::setUp();
        $this->resource = Shopware()->Container()->get('shopware.api.emotionpreset');
        $this->resource->setManager(Shopware()->Models());
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        $this->presetData = ['name' => 'test', 'assetsImported' => false, 'presetData' => '{"id":null,"active":true,"articleHeight":2,"cellHeight":185,"cellSpacing":10,"cols":4,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":false,"mode":"fluid","position":1,"rows":22,"showListing":false,"templateId":1,"elements":[{"assets":{"assetkey":"data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs="},"componentId":"emotion-components-banner","syncKey":"58ac4ec7b38cc","startRow":1,"startCol":4,"endRow":3,"endCol":4,"data":[{"id":null,"fieldId":65,"type":"","key":"bannerPosition","value":"center"},{"id":2810,"fieldId":3,"type":"","key":"file","value":"assetkey"},{"id":2811,"fieldId":7,"type":"json","key":"bannerMapping","value":[{"x":"0","y":"356","width":"251","height":"198","link":"SW10211","resizerIndex":0,"path":""},{"x":"0","y":"184","width":"251","height":"176","link":"SW10170","resizerIndex":1,"path":""},{"x":"0","y":"0","width":"251","height":"188","link":"SW10178","resizerIndex":2,"path":""}]},{"id":2812,"fieldId":47,"type":"","key":"link","value":""},{"id":null,"fieldId":89,"type":"","key":"banner_link_target","value":""},{"id":null,"fieldId":85,"type":"","key":"title","value":""}],"viewports":[{"alias":"xs","startRow":5,"startCol":3,"endRow":6,"endCol":4,"visible":true},{"alias":"s","startRow":5,"startCol":3,"endRow":6,"endCol":4,"visible":true},{"alias":"m","startRow":5,"startCol":3,"endRow":6,"endCol":4,"visible":true},{"alias":"l","startRow":1,"startCol":4,"endRow":3,"endCol":4,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":4,"visible":true}]}]}'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Container()->get('dbal_connection')->rollback();
    }

    public function testListAction()
    {
        $this->resource->create(['name' => 'first', 'presetData' => '{}']);
        $this->resource->create(['name' => 'second', 'presetData' => '{}']);
        $this->resource->create(['name' => 'third', 'presetData' => '{}']);

        $this->Request()->setMethod('GET');
        $this->dispatch('/backend/EmotionPreset/list');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);
        static::assertArrayHasKey('data', $data);

        static::assertCount(3, $data['data']);
    }

    public function testDeleteAction()
    {
        $first = $this->resource->create(['name' => 'first', 'presetData' => '{}']);
        $this->resource->create(['name' => 'second', 'presetData' => '{}']);
        $this->resource->create(['name' => 'third', 'presetData' => '{}']);

        $this->Request()->setMethod('POST')->setPost(['id' => $first->getId()]);
        $this->dispatch('/backend/EmotionPreset/delete');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);
        static::assertCount(2, $this->resource->getList());
    }

    public function testLoadPresetActionShouldFail()
    {
        $this->Request()->setMethod('POST')->setPost([
            'id' => null,
        ]);
        $this->dispatch('/backend/EmotionPreset/loadPreset');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);
        static::assertArrayNotHasKey('data', $data);
        static::assertFalse($data['success']);
    }

    public function testLoadPresetAction()
    {
        $preset = $this->resource->create($this->presetData);

        $this->Request()->setMethod('POST')->setPost([
            'id' => $preset->getId(),
        ]);
        $this->dispatch('/backend/EmotionPreset/loadPreset');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);
        static::assertArrayHasKey('data', $data);
        static::assertJson($data['data']);
    }

    public function testCreateShouldFail()
    {
        $this->Request()->setMethod('POST')->setPost(['name' => 'first', 'presetData' => '{}']);

        $this->dispatch('/backend/EmotionPreset/save');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);
        static::assertFalse($data['success']);
    }

    public function testCreate()
    {
        $this->Request()->setMethod('POST')->setPost(['name' => 'first', 'presetData' => '{}', 'emotionId' => 1]);

        $this->dispatch('/backend/EmotionPreset/save');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);
        static::assertCount(1, $this->resource->getList());
    }

    public function testUpdate()
    {
        $preset = $this->resource->create(['name' => 'first', 'presetData' => '{}', 'emotionId' => 1]);
        $this->Request()->setMethod('POST')->setPost([
            'id' => $preset->getId(),
            'name' => 'updated',
            'presetData' => '{}',
            'emotionId' => 1,
        ]);
        $this->dispatch('/backend/EmotionPreset/save');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);

        $list = $this->resource->getList();
        static::assertCount(1, $list);
        static::assertSame($list[0]['name'], 'updated');
    }

    public function testImportAssetsShouldFail()
    {
        $this->Request()->setMethod('POST')->setPost([
            'syncKey' => 'key',
        ]);
        $this->dispatch('/backend/EmotionPreset/importAsset');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);
        static::assertArrayNotHasKey('data', $data);
        static::assertFalse($data['success']);
    }

    public function testImportAssets()
    {
        $presetData = '{"showListing":false,"templateId":1,"active":false,"name":"testemotion","position":1,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":0,"seoTitle":"","seoKeywords":"","seoDescription":"","rows":20,"cols":4,"cellSpacing":10,"cellHeight":185,"articleHeight":2,"mode":"fluid","customerStreamId":null,"replacement":null,"elements":[{"componentId":"emotion-components-banner","startRow":1,"startCol":1,"endRow":1,"endCol":1,"cssClass":"","viewports":[{"alias":"xs","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"s","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"m","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"l","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":1,"visible":true}],"data":[{"componentId":"emotion-components-banner","fieldId":"bannerPosition","value":"center","key":"bannerPosition","valueType":""},{"componentId":"emotion-components-banner","fieldId":"file","value":"7143d7fbadfa4693b9eec507d9d37443","key":"file","valueType":""},{"componentId":"emotion-components-banner","fieldId":"bannerMapping","value":"null","key":"bannerMapping","valueType":"json"},{"componentId":"emotion-components-banner","fieldId":"link","value":"","key":"link","valueType":""},{"componentId":"emotion-components-banner","fieldId":"banner_link_target","value":"","key":"banner_link_target","valueType":""},{"componentId":"emotion-components-banner","fieldId":"title","value":"","key":"title","valueType":""}],"syncKey":"preset-element-590ed04d726936.19755837"}],"syncData":{"assets":{"7143d7fbadfa4693b9eec507d9d37443":"data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs="}}}';
        $this->presetData['presetData'] = $presetData;

        $preset = $this->resource->create($this->presetData);

        $presetData = json_decode($preset->getPresetData(), true);
        $element = $presetData['elements'][0];

        $this->Request()->setMethod('POST')->setPost([
            'id' => $preset->getId(),
            'syncKey' => $element['syncKey'],
        ]);
        $this->dispatch('/backend/EmotionPreset/importAsset');

        $data = $this->View()->getAssign();
        static::assertArrayHasKey('success', $data);

        $preset = $this->resource->getManager()->find(Preset::class, $preset->getId());
        $presetData = json_decode($preset->getPresetData(), true);
        $element = $presetData['elements'][0];

        $this->assertRegexp('/media/', $element['data'][1]['value']);
    }
}
