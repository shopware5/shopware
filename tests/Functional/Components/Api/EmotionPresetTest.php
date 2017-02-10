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

namespace Shopware\Tests\Functional\Components\Api;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Models\Emotion\Preset;

class EmotionPresetTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EmotionPreset
     */
    private $resource;

    protected function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();
        $this->resource = Shopware()->Container()->get('shopware.api.emotionpreset');
        parent::setUp();
    }

    protected function tearDown()
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function testCreate()
    {
        $this->resource->create(['name' => 'test', 'presetData' => 'data']);
        $this->assertCount(1, $this->connection->fetchAll('SELECT * FROM s_emotion_presets'));
    }

    public function testCreateReturnsPersistedEntity()
    {
        $preset = $this->resource->create(['name' => 'test', 'presetData' => 'data']);
        $this->assertInstanceOf(Preset::class, $preset);
        $this->assertNotSame(null, $preset->getId());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testPresetDataIsRequiredOnCreate()
    {
        $this->resource->create(['name' => 'Test']);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testPresetNameIsRequiredOnCreate()
    {
        $this->resource->create(['presetData' => 'test']);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testPresetNameCannotBeEmpty()
    {
        $this->resource->create(['name' => '', 'presetData' => 'test']);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testPresetDataCanNotBeEmpty()
    {
        $this->resource->create(['name' => 'test', 'presetData' => '']);
    }

    public function testUpdate()
    {
        $preset = $this->resource->create(['name' => 'test', 'presetData' => 'data']);
        $updated = $this->resource->update($preset->getId(), ['name' => 'updated']);
        $this->assertSame('updated', $updated->getName());
        $this->assertCount(1, $this->connection->fetchAll("SELECT * FROM s_emotion_presets WHERE name = 'updated'"));
    }
}
