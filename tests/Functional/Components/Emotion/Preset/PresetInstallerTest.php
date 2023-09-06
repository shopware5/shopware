<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Components\Emotion\Preset;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Emotion\Preset\PresetInstaller;
use Shopware\Components\Emotion\Preset\PresetMetaDataInterface;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group EmotionPreset
 */
class PresetInstallerTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private PresetInstaller $presetInstaller;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);

        $this->connection->executeQuery('DELETE FROM s_emotion_presets');

        $this->presetInstaller = $this->getContainer()->get('shopware.emotion.preset_installer');
    }

    public function testPresetInstallationShouldSucceedWithEmptyPresetData(): void
    {
        $presetMetaData = $this->buildMetaDataMock('foo');

        $this->presetInstaller->installOrUpdate([$presetMetaData]);
        $presets = $this->connection->fetchAllAssociative('SELECT * FROM s_emotion_presets');

        static::assertCount(1, $presets);
        // check slugified name
        static::assertEquals('foo', $presets[0]['name']);
    }

    public function testPresetUpdateShouldSucceedWithEmptyPresetData(): void
    {
        $presetMetaData = $this->buildMetaDataMock('foo');
        $presetMetaDataUpdate = $this->buildMetaDataMock('foo', true);

        $this->presetInstaller->installOrUpdate([$presetMetaData]);
        $presets = $this->connection->fetchAllAssociative('SELECT * FROM s_emotion_presets');

        static::assertCount(1, $presets);
        // check slugified name
        static::assertEquals('foo', $presets[0]['name']);

        $this->presetInstaller->installOrUpdate([$presetMetaDataUpdate]);
        $presets = $this->connection->fetchAllAssociative('SELECT * FROM s_emotion_presets');

        static::assertCount(1, $presets);
        static::assertEquals('foo', $presets[0]['name']);
        static::assertEquals(1, $presets[0]['custom']);
    }

    public function testPresetUninstallationShouldSucceed(): void
    {
        $firstPreset = $this->buildMetaDataMock('foo', true);
        $secondPreset = $this->buildMetaDataMock('bar');

        $this->presetInstaller->installOrUpdate([$firstPreset, $secondPreset]);
        static::assertCount(2, $this->connection->fetchAllAssociative('SELECT * FROM s_emotion_presets'));

        $this->presetInstaller->uninstall(['foo']);
        $presets = $this->connection->fetchAllAssociative('SELECT * FROM s_emotion_presets');
        static::assertCount(1, $presets);
        static::assertEquals('bar', $presets[0]['name']);
    }

    private function buildMetaDataMock(string $name, bool $custom = false): PresetMetaDataInterface
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
