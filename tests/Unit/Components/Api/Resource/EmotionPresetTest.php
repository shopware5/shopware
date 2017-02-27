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

namespace Shopware\Tests\Unit\Components\Api\Resource;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Slug\SlugInterface;

class EmotionPresetTest extends TestCase
{
    /**
     * @var EmotionPreset
     */
    private $emotionPresetResource;

    protected function setUp()
    {
        $this->emotionPresetResource = new EmotionPreset(
            $this->createMock(Connection::class),
            $this->createMock(ModelManager::class),
            $this->createMock(\Enlight_Template_Manager::class),
            $this->createMock(SlugInterface::class)
        );
    }

    public function testCreateElementSyncKeysWithEmptyStringData()
    {
        $method = new \ReflectionMethod($this->emotionPresetResource, 'generateElementSyncKeys');
        $method->setAccessible(true);

        $presetData = '';

        $this->assertInternalType('string', $presetData);
        $this->assertEquals('', $method->invoke($this->emotionPresetResource, $presetData));
    }

    public function testCreateElementSyncKeysWithEmptyArrayData()
    {
        $method = new \ReflectionMethod($this->emotionPresetResource, 'generateElementSyncKeys');
        $method->setAccessible(true);

        $presetData = '[]';

        $this->assertInternalType('string', $presetData);
        $this->assertEquals('[]', $method->invoke($this->emotionPresetResource, $presetData));
    }

    public function testCreateElementSyncKeysWithEmptyElementsData()
    {
        $method = new \ReflectionMethod($this->emotionPresetResource, 'generateElementSyncKeys');
        $method->setAccessible(true);

        $presetData = '{"elements":[]}';

        $this->assertInternalType('string', $presetData);
        $this->assertEquals($presetData, $method->invoke($this->emotionPresetResource, $presetData));
    }

    public function testCreateElementSyncKeysWithPresetData()
    {
        $method = new \ReflectionMethod($this->emotionPresetResource, 'generateElementSyncKeys');
        $method->setAccessible(true);

        $input = '{"elements":[{"componentId":null,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"data":[]}, {"componentId":null,"startRow":1,"startCol":1,"endRow":1,"endCol":1,"data":[]}]}';

        $output = $method->invoke($this->emotionPresetResource, $input);

        $this->assertInternalType('string', $output);
        $this->assertJson($output);
        $this->assertJsonStringNotEqualsJsonString($output, $input);

        $decodedData = json_decode($output, true);
        $this->assertArrayHasKey('elements', $decodedData);

        $firstElement = $decodedData['elements'][0];
        $secondElement = $decodedData['elements'][1];

        $this->assertArrayHasKey('syncKey', $firstElement);
        $this->assertArrayHasKey('syncKey', $secondElement);

        $this->assertStringStartsWith('preset-element-', $firstElement['syncKey']);
        $this->assertStringStartsWith('preset-element-', $secondElement['syncKey']);

        $this->assertNotEquals($firstElement['syncKey'], $secondElement['syncKey']);
    }
}
