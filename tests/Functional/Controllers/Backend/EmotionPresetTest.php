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

class EmotionPresetTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var EmotionPreset
     */
    private $resource;

    public function setUp()
    {
        parent::setUp();
        $this->resource = Shopware()->Container()->get('shopware.api.emotionpreset');
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
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

    public function testCreate()
    {
        $this->Request()->setMethod('POST')->setPost(['name' => 'first', 'presetData' => '{}']);

        $this->dispatch('/backend/EmotionPreset/save');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);
        $this->assertCount(1, $this->resource->getList());
    }

    public function testUpdate()
    {
        $preset = $this->resource->create(['name' => 'first', 'presetData' => '{}']);
        $this->Request()->setMethod('POST')->setPost(['id' => $preset->getId(), 'name' => 'updated', 'presetData' => '{}']);
        $this->dispatch('/backend/EmotionPreset/save');

        $data = $this->View()->getAssign();
        $this->assertArrayHasKey('success', $data);

        $list = $this->resource->getList();
        $this->assertCount(1, $list);
        $this->assertSame($list[0]['name'], 'updated');
    }
}
