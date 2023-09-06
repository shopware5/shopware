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
use Doctrine\ORM\NoResultException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Shopware\Components\Emotion\Preset\EmotionToPresetDataTransformer;
use Shopware\Components\Emotion\Preset\EmotionToPresetDataTransformerInterface;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group EmotionPreset
 */
class EmotionToPresetDataTransformerTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * @var EmotionToPresetDataTransformer
     */
    private $transformer;

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);

        $this->connection->executeQuery('DELETE FROM s_emotion_presets');
        $this->connection->executeQuery('DELETE FROM s_core_plugins');

        $this->transformer = $this->getContainer()->get(EmotionToPresetDataTransformerInterface::class);
    }

    public function testShouldFailBecauseOfMissingEmotion(): void
    {
        $this->expectException(NoResultException::class);
        $this->transformer->transform(0);
    }

    public function testTransformShouldSucceed(): void
    {
        $emotionId = $this->connection->executeQuery('SELECT id FROM s_emotion LIMIT 1')->fetchOne();

        $data = $this->transformer->transform($emotionId);

        static::assertArrayHasKey('presetData', $data);
        static::assertJson($data['presetData']);

        $presetData = json_decode($data['presetData'], true);

        static::assertArrayNotHasKey('id', $presetData);

        static::assertArrayNotHasKey('id', $presetData['elements'][0]);
        static::assertIsString($presetData['elements'][0]['componentId']);
        static::assertArrayHasKey('syncKey', $presetData['elements'][0]);

        static::assertArrayHasKey('data', $presetData['elements'][0]);

        static::assertIsArray($data['requiredPlugins']);
    }

    public function testTransformWithTranslationsShouldSucceed(): void
    {
        $emotionId = $this->connection->executeQuery('SELECT id FROM s_emotion LIMIT 1')->fetchOne();
        $this->connection->insert('s_core_translations', ['objecttype' => 'emotion', 'objectdata' => 'a:1:{s:4:"name";s:11:"My homepage";}', 'objectkey' => $emotionId, 'objectlanguage' => 2, 'dirty' => 1]);

        $data = $this->transformer->transform($emotionId);

        static::assertArrayHasKey('emotionTranslations', $data);
        static::assertJson($data['emotionTranslations']);

        $translationData = json_decode($data['emotionTranslations'], true);

        static::assertSame('en_GB', $translationData[0]['locale']);
        static::assertSame('emotion', $translationData[0]['objecttype']);

        static::assertArrayHasKey('presetData', $data);
        static::assertJson($data['presetData']);
        static::assertArrayHasKey('requiredPlugins', $data);
        static::assertEmpty($data['requiredPlugins']);
    }

    public function testGettingRequiredPluginsByIdShouldSucceed(): void
    {
        $this->connection->insert('s_core_plugins', ['name' => 'SwagLiveShopping', 'label' => 'Live shopping', 'version' => '1.0.0']);
        $pluginId = $this->connection->fetchOne('SELECT id FROM s_core_plugins');

        $method = new ReflectionMethod($this->transformer, 'getRequiredPluginsById');
        $method->setAccessible(true);

        $ids = [$pluginId];

        $result = $method->invoke($this->transformer, $ids);

        static::assertIsArray($result);
        static::assertEquals('SwagLiveShopping', $result[0]['name']);
    }
}
