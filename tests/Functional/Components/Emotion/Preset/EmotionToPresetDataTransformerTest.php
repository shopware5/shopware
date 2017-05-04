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
use PHPUnit\Framework\TestCase;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Components\Emotion\Preset\EmotionToPresetDataTransformer;

/**
 * @group EmotionPreset
 */
class EmotionToPresetDataTransformerTest extends TestCase
{
    /** @var EmotionToPresetDataTransformer */
    private $transformer;

    /** @var EmotionPreset */
    private $presetResource;

    /** @var Connection */
    private $connection;

    protected function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        $this->connection->executeQuery('DELETE FROM s_emotion_presets');
        $this->connection->executeQuery('DELETE FROM s_core_plugins');

        $this->transformer = Shopware()->Container()->get('shopware.emotion.emotion_presetdata_transformer');
        $this->presetResource = Manager::getResource('EmotionPreset');
    }

    protected function tearDown()
    {
        $this->connection->rollBack();
    }

    /**
     * @expectedException
     */
    public function testShouldFailBecauseOfMissingEmotion()
    {
        $this->expectException(NoResultException::class);
        $this->transformer->transform(null);
    }

    public function testTransformShouldSucceed()
    {
        $emotionId = $this->connection->executeQuery('SELECT id FROM s_emotion LIMIT 1')->fetchColumn();

        $data = $this->transformer->transform($emotionId);

        $this->assertArrayHasKey('presetData', $data);
        $this->assertJson($data['presetData']);
        $this->assertInternalType('array', $data['requiredPlugins']);
    }
}
