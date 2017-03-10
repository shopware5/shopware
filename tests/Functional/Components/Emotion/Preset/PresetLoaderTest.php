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
use Shopware\Components\Api\Manager;
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

    protected function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        $this->connection->executeQuery('DELETE FROM s_emotion_presets');

        $this->presetLoader = Shopware()->Container()->get('shopware.emotion.preset_loader');
        $this->presetResource = Manager::getResource('EmotionPreset');
    }

    protected function tearDown()
    {
        $this->connection->rollBack();
    }

    /**
     * @expectedException
     */
    public function testPresetLoadingShouldFailMissingIdentifier()
    {
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

        $this->assertInternalType('string', $presetData);
        $this->assertJson($presetData);
        $this->assertEquals('[]', $presetData);
    }

    public function testPresetLoadingShouldBeSuccessfulWithEmptyElements()
    {
        $data = '{"elements":[]}';
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => $data, 'assetsImported' => true]);

        $presetData = $this->presetLoader->load($preset->getId());

        $this->assertInternalType('string', $presetData);
        $this->assertJson($presetData);
        $this->assertEquals($data, $presetData);
    }

    public function testPresetLoadingShouldBeSuccessfulWithEmptyComponents()
    {
        $data = '{"elements":[{"componentId":3,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"cssClass":"","data":[{"componentId":3,"fieldId":65,"value":"center"},{"componentId":3,"fieldId":3,"value":"media/image/Dortmund-658c01c2f0783d.jpg"},{"componentId":3,"fieldId":7,"value":"null"},{"componentId":3,"fieldId":47,"value":""},{"componentId":3,"fieldId":89,"value":""},{"componentId":3,"fieldId":85,"value":""}]}]}';
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => $data, 'assetsImported' => true]);

        $presetData = $this->presetLoader->load($preset->getId());

        $this->assertInternalType('string', $presetData);
        $this->assertJson($presetData);

        $decodedData = json_decode($presetData, true);

        $this->assertArrayHasKey('componentId', $decodedData['elements'][0]);
        $this->assertNull($decodedData['elements'][0]['componentId']);
        $this->assertNull($decodedData['elements'][0]['data'][0]['componentId']);
        $this->assertNull($decodedData['elements'][0]['data'][0]['fieldId']);
        $this->assertArrayHasKey('key', $decodedData['elements'][0]['data'][0]);
        $this->assertArrayHasKey('valueType', $decodedData['elements'][0]['data'][0]);
    }

    public function testPresetLoadingShouldBeSuccessful()
    {
        $data = '{"elements":[{"componentId":59,"component":{"id":59,"name":"Banner","convertFunction":"getBannerMappingLinks","description":"","template":"component_banner","cls":"banner-element","xType":"emotion-components-banner","pluginId":null,"fields":[{"id":401,"componentId":59,"name":"file","fieldLabel":"Bild","xType":"mediaselectionfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":3},{"id":402,"componentId":59,"name":"bannerMapping","fieldLabel":"","xType":"hidden","valueType":"json","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":7},{"id":403,"componentId":59,"name":"link","fieldLabel":"Link","xType":"textfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":1,"helpTitle":"","helpText":"","translatable":1,"position":47},{"id":400,"componentId":59,"name":"bannerPosition","fieldLabel":"","xType":"hidden","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"center","allowBlank":0,"helpTitle":"","helpText":"","translatable":0,"position":null},{"id":405,"componentId":59,"name":"title","fieldLabel":"Title Text","xType":"textfield","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":1,"helpTitle":"","helpText":"","translatable":1,"position":50},{"id":404,"componentId":3,"name":"banner_link_target","fieldLabel":"Link-Ziel","xType":"emotion-components-fields-link-target","valueType":"","supportText":"","store":"","displayField":"","valueField":"","defaultValue":"","allowBlank":1,"helpTitle":"","helpText":"","translatable":0,"position":48}]},"data":[{"componentId":59,"fieldId":400,"value":"center"},{"componentId":59,"fieldId":401,"value":"media/image/test.jpg"},{"componentId":59,"fieldId":402,"value":"null"},{"componentId":59,"fieldId":403,"value":""},{"componentId":59,"fieldId":404,"value":""},{"componentId":59,"fieldId":405,"value":""}]}]}';
        $preset = $this->presetResource->create(['name' => 'test', 'presetData' => $data, 'assetsImported' => true]);

        $presetData = $this->presetLoader->load($preset->getId());

        $this->assertInternalType('string', $presetData);
        $this->assertJson($presetData);

        $decodedData = json_decode($presetData, true);

        $componentId = $this->connection->fetchColumn('SELECT id FROM s_library_component WHERE name = "Banner"');
        $fieldId = $this->connection->fetchColumn('SELECT id FROM s_library_component_field WHERE name = "file"');

        $this->assertEquals($componentId, $decodedData['elements'][0]['componentId']);
        $this->assertEquals($componentId, $decodedData['elements'][0]['component']['id']);

        $this->assertEquals($fieldId, $decodedData['elements'][0]['component']['fields'][0]['id']);
        $this->assertEquals($fieldId, $decodedData['elements'][0]['data'][1]['fieldId']);
        $this->assertRegExp('/http/', $decodedData['elements'][0]['data'][1]['value']);
    }
}
