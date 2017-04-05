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

    public function setUp()
    {
        parent::setUp();
        $this->resource = Shopware()->Container()->get('shopware.api.emotionpreset');
        $this->resource->setManager(Shopware()->Models());
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        $this->presetData = ['name' => 'test', 'assetsImported' => false, 'presetData' => '{"id":null,"active":true,"articleHeight":2,"cellHeight":185,"cellSpacing":10,"cols":4,"device":"0,1,2,3,4","fullscreen":0,"isLandingPage":false,"mode":"fluid","position":1,"rows":22,"showListing":false,"templateId":1,"elements":[{"assets":{"assetkey":"data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs="},"componentId":3,"syncKey":"58ac4ec7b38cc" ,"startRow":1,"startCol":4,"endRow":3,"endCol":4,"data":[{"id":null,"fieldId":65,"type":"","key":"bannerPosition","value":"center"},{"id":2810,"fieldId":3,"type":"","key":"file","value":"assetkey"},{"id":2811,"fieldId":7,"type":"json","key":"bannerMapping","value":[{"x":"0","y":"356","width":"251","height":"198","link":"SW10211","resizerIndex":0,"path":""},{"x":"0","y":"184","width":"251","height":"176","link":"SW10170","resizerIndex":1,"path":""},{"x":"0","y":"0","width":"251","height":"188","link":"SW10178","resizerIndex":2,"path":""}]},{"id":2812,"fieldId":47,"type":"","key":"link","value":""},{"id":null,"fieldId":89,"type":"","key":"banner_link_target","value":""},{"id":null,"fieldId":85,"type":"","key":"title","value":""}],"viewports":[{"alias":"xs","startRow":5,"startCol":3,"endRow":6,"endCol":4,"visible":true},{"alias":"s","startRow":5,"startCol":3,"endRow":6,"endCol":4,"visible":true},{"alias":"m","startRow":5,"startCol":3,"endRow":6,"endCol":4,"visible":true},{"alias":"l","startRow":1,"startCol":4,"endRow":3,"endCol":4,"visible":true},{"alias":"xl","startRow":1,"startCol":1,"endRow":1,"endCol":4,"visible":true}],"component":{"id":3,"pluginId":null,"name":"Banner","description":"","xType":"emotion-components-banner","template":"component_banner","cls":"banner-element","fieldLabel":"Banner","fields":[{"id":65,"componentId":3,"name":"bannerPosition","xType":"hidden","valueType":"","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"center","translatable":0,"position":0},{"id":3,"componentId":3,"name":"file","xType":"mediaselectionfield","valueType":"","fieldLabel":"Bild","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":3},{"id":7,"componentId":3,"name":"bannerMapping","xType":"hidden","valueType":"json","fieldLabel":"","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":0,"defaultValue":"","translatable":0,"position":7},{"id":47,"componentId":3,"name":"link","xType":"textfield","valueType":"","fieldLabel":"Link","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":47},{"id":89,"componentId":3,"name":"banner_link_target","xType":"emotion-components-fields-link-target","valueType":"","fieldLabel":"Link-Ziel","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":0,"position":48},{"id":85,"componentId":3,"name":"title","xType":"textfield","valueType":"","fieldLabel":"Title Text","supportText":"","helpTitle":"","helpText":"","store":"","displayField":"","valueField":"","allowBlank":1,"defaultValue":"","translatable":1,"position":50}]}}]}'];
    }

    protected function tearDown()
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
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);

        $this->assertCount(3, $data['data']);
    }

    public function testDeleteAction()
    {
        $first = $this->resource->create(['name' => 'first', 'presetData' => '{}']);
        $this->resource->create(['name' => 'second', 'presetData' => '{}']);
        $this->resource->create(['name' => 'third', 'presetData' => '{}']);

        $this->Request()->setMethod('POST')->setPost(['id' => $first->getId()]);
        $this->dispatch('/backend/EmotionPreset/delete');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);
        $this->assertCount(2, $this->resource->getList());
    }

    public function testLoadPresetActionShouldFail()
    {
        $this->Request()->setMethod('POST')->setPost([
            'id' => null,
        ]);
        $this->dispatch('/backend/EmotionPreset/loadPreset');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayNotHasKey('data', $data);
        $this->assertFalse($data['success']);
    }

    public function testLoadPresetAction()
    {
        $preset = $this->resource->create($this->presetData);

        $this->Request()->setMethod('POST')->setPost([
            'id' => $preset->getId(),
        ]);
        $this->dispatch('/backend/EmotionPreset/loadPreset');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertJson($data['data']);
    }

    public function testCreateShouldFail()
    {
        $this->Request()->setMethod('POST')->setPost(['name' => 'first', 'presetData' => '{}']);

        $this->dispatch('/backend/EmotionPreset/save');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
    }

    public function testCreate()
    {
        $this->Request()->setMethod('POST')->setPost(['name' => 'first', 'presetData' => '{}', 'emotionId' => 1]);

        $this->dispatch('/backend/EmotionPreset/save');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);
        $this->assertCount(1, $this->resource->getList());
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
        $this->assertArrayHasKey('success', $data);

        $list = $this->resource->getList();
        $this->assertCount(1, $list);
        $this->assertSame($list[0]['name'], 'updated');
    }

    public function testImportAssetsShouldFail()
    {
        $this->Request()->setMethod('POST')->setPost([
            'syncKey' => 'key',
        ]);
        $this->dispatch('/backend/EmotionPreset/importAsset');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayNotHasKey('data', $data);
        $this->assertFalse($data['success']);
    }

    public function testImportAssets()
    {
        $preset = $this->resource->create($this->presetData);

        $presetData = json_decode($preset->getPresetData(), true);
        $element = $presetData['elements'][0];

        $this->Request()->setMethod('POST')->setPost([
            'id' => $preset->getId(),
            'syncKey' => $element['syncKey'],
        ]);
        $this->dispatch('/backend/EmotionPreset/importAsset');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);

        $preset = $this->resource->getManager()->find(Preset::class, $preset->getId());
        $presetData = json_decode($preset->getPresetData(), true);
        $element = $presetData['elements'][0];

        $this->assertRegexp('/media/', $element['data'][1]['value']);
    }
}
