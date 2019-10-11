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
use Shopware\Components\Emotion\Preset\PresetInstaller;
use Shopware\Components\Emotion\Preset\PresetMetaDataInterface;

/**
 * @group EmotionPreset
 */
class PresetInstallerTest extends TestCase
{
    /** @var PresetInstaller */
    private $presetInstaller;

    /** @var Connection */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        $this->connection->executeQuery('DELETE FROM s_emotion_presets');
        $this->connection->executeQuery('DELETE FROM s_core_plugins');

        $this->presetInstaller = Shopware()->Container()->get('shopware.emotion.preset_installer');
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
    }

    public function testPresetInstallationShouldSucceedWithEmptyPresetData()
    {
        $presetMetaData = $this->buildMetaDataMock('foo');

        static::assertInstanceOf(PresetMetaDataInterface::class, $presetMetaData);
        $this->presetInstaller->installOrUpdate([$presetMetaData]);
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        static::assertCount(1, $presets);
        // check slugified name
        static::assertEquals('foo', $presets[0]['name']);
    }

    public function testPresetUpdateShouldSucceedWithEmptyPresetData()
    {
        $presetMetaData = $this->buildMetaDataMock('foo');
        $presetMetaDataUpdate = $this->buildMetaDataMock('foo', true);

        static::assertInstanceOf(PresetMetaDataInterface::class, $presetMetaData);
        $this->presetInstaller->installOrUpdate([$presetMetaData]);
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        static::assertCount(1, $presets);
        // check slugified name
        static::assertEquals('foo', $presets[0]['name']);

        $this->presetInstaller->installOrUpdate([$presetMetaDataUpdate]);
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');

        static::assertCount(1, $presets);
        static::assertEquals('foo', $presets[0]['name']);
        static::assertEquals(1, $presets[0]['custom']);
    }

    public function testPresetUninstallationShouldSucceed()
    {
        $firstPreset = $this->buildMetaDataMock('foo', true);
        $secondPreset = $this->buildMetaDataMock('bar');

        $this->presetInstaller->installOrUpdate([$firstPreset, $secondPreset]);
        static::assertCount(2, $this->connection->fetchAll('SELECT * FROM s_emotion_presets'));

        $this->presetInstaller->uninstall(['foo']);
        $presets = $this->connection->fetchAll('SELECT * FROM s_emotion_presets');
        static::assertCount(1, $presets);
        static::assertEquals('bar', $presets[0]['name']);
    }

    /**
     * @param string $name
     * @param bool   $custom
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function buildMetaDataMock($name, $custom = false)
    {
        $presetMetaData = $this->createMock(PresetMetaDataInterface::class);
        $presetMetaData->method('getName')->willReturn($name);
        $presetMetaData->method('getPresetData')->willReturn([]);
        $presetMetaData->method('getTranslations')->willReturn([]);
        $presetMetaData->method('getPremium')->willReturn(false);
        $presetMetaData->method('getCustom')->willReturn($custom);
        $presetMetaData->method('getAssetsImported')->willReturn(false);

        return $presetMetaData;
    }
}
