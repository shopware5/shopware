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

namespace Shopware\Components\Emotion\Preset;

use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Components\Slug\SlugInterface;
use Shopware\Models\Emotion\Preset;

class PresetInstaller implements PresetInstallerInterface
{
    /**
     * @var EmotionPreset
     */
    private $presetResource;

    /**
     * @var SlugInterface
     */
    private $slugService;

    public function __construct(EmotionPreset $presetResource, SlugInterface $slugService)
    {
        $this->presetResource = $presetResource;
        $this->slugService = $slugService;
    }

    /**
     * @param PresetMetaDataInterface[] $presetMetaData
     */
    public function installOrUpdate(array $presetMetaData)
    {
        $modelManager = $this->presetResource->getManager();

        /** @var PresetMetaDataInterface $metaData */
        foreach ($presetMetaData as $metaData) {
            $presetData = [
                'name' => $metaData->getName(),
                'premium' => $metaData->getPremium(),
                'custom' => $metaData->getCustom(),
                'thumbnail' => $metaData->getThumbnail(),
                'preview' => $metaData->getPreview(),
                'translations' => $metaData->getTranslations(),
                'presetData' => json_encode($metaData->getPresetData()),
                'requiredPlugins' => $metaData->getRequiredPlugins(),
                'assetsImported' => $metaData->getAssetsImported(),
            ];

            $slugifiedName = $this->slugService->slugify($metaData->getName());
            $preset = $modelManager->getRepository(Preset::class)->findOneBy(['name' => $slugifiedName]);

            if (!$preset) {
                $this->presetResource->create($presetData);

                continue;
            }
            $this->presetResource->update($preset->getId(), $presetData);
        }
    }

    /**
     * @param string[] $presetNames
     */
    public function uninstall(array $presetNames)
    {
        $modelManager = $this->presetResource->getManager();
        $slugifiedNames = [];
        foreach ($presetNames as $presetName) {
            $slugifiedNames[] = $this->slugService->slugify($presetName);
        }

        // Make default presets deletable when plugin is removed
        $modelManager->getConnection()->createQueryBuilder()
            ->update('s_emotion_presets', 'preset')
            ->set('preset.custom', '1')
            ->where('preset.name IN (:names) AND preset.custom = 0')
            ->setParameter('names', $slugifiedNames, Connection::PARAM_STR_ARRAY)
            ->execute();

        $presets = $modelManager->getRepository(Preset::class)->findBy(['name' => $slugifiedNames]);

        foreach ($presets as $preset) {
            $this->presetResource->delete($preset->getId());
        }
    }
}
