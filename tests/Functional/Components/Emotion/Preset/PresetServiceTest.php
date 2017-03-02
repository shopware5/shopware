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
use PHPUnit\Framework\TestCase;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Emotion\Preset\PresetMetaData;
use Shopware\Components\Emotion\Preset\PresetMetaDataInterface;
use Shopware\Components\Emotion\Preset\PresetService;

/**
 * @group EmotionPreset
 */
class PresetServiceTest extends TestCase
{
    /** @var PresetService */
    private $presetService;

    /** @var Connection */
    private $connection;

    protected function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        $this->connection->executeQuery('DELETE FROM s_emotion_presets');
        $this->connection->executeQuery('DELETE FROM s_core_plugins');

        $this->presetService = Shopware()->Container()->get('shopware.emotion.preset_service');
    }

    protected function tearDown()
    {
        $this->connection->rollBack();
    }

    /**
     * @expectedException
     */
    public function testPresetInstallationShouldFail()
    {
        $presetMetaData = new PresetMetaData();

        $this->assertInstanceOf(PresetMetaDataInterface::class, $presetMetaData);
        $this->expectException(ValidationException::class);
        $this->presetService->installOrUpdatePresets([$presetMetaData]);
    }

    public function testPresetInstallationShouldSucceedWithEmptyPresetData()
    {
        $presetMetaData = new PresetMetaData();
        $presetMetaData->fromArray([
            'name' => 'test_foo_preset',
            'presetData' => [],
        ]);

        $this->assertInstanceOf(PresetMetaDataInterface::class, $presetMetaData);
        $this->presetService->installOrUpdatePresets([$presetMetaData]);
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        $this->assertCount(1, $presets);
        // check slugified name
        $this->assertEquals('test-foo-preset', $presets[0]['name']);
    }

    public function testPresetUninstallationShouldSucceed()
    {
        $firstPreset = new PresetMetaData();
        $this->assertInstanceOf(PresetMetaDataInterface::class, $firstPreset);
        $firstPreset->fromArray([
            'name' => 'foo',
            'custom' => true,
            'presetData' => [],
        ]);

        $secondPreset = new PresetMetaData();
        $this->assertInstanceOf(PresetMetaDataInterface::class, $secondPreset);

        $secondPreset->fromArray([
            'name' => 'bar',
            'custom' => true,
            'presetData' => [],
        ]);

        $this->presetService->installOrUpdatePresets([$firstPreset, $secondPreset]);
        $this->assertCount(2, $this->connection->fetchAll('SELECT * FROM s_emotion_presets'));

        $this->presetService->uninstall(['foo']);
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');
        $this->assertCount(1, $presets);
        $this->assertEquals('bar', $presets[0]['name']);
    }
}
