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

namespace Shopware\Components\Api\Resource;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Slug\SlugInterface;
use Shopware\Models\Emotion\Preset;

class EmotionPreset extends Resource
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $models;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var \Enlight_Template_Manager
     */
    private $template;

    /**
     * @var SlugInterface
     */
    private $slugService;

    /**
     * @param Connection                $connection
     * @param ModelManager              $models
     * @param MediaService              $mediaService
     * @param \Enlight_Template_Manager $template
     * @param SlugInterface             $slugService
     */
    public function __construct(
        Connection $connection,
        ModelManager $models,
        MediaService $mediaService,
        \Enlight_Template_Manager $template,
        SlugInterface $slugService
    ) {
        $this->connection = $connection;
        $this->models = $models;
        $this->mediaService = $mediaService;
        $this->template = $template;
        $this->slugService = $slugService;
    }

    public function getList($locale = 'de_DE')
    {
        $query = $this->createQuery();
        $presets = $query->getQuery()->getArrayResult();
        $data = $this->preparePresetData($presets, $locale);

        return $data;
    }

    public function delete($presetId)
    {
        if (!$presetId) {
            throw new ParameterMissingException('id');
        }

        /** @var Preset $preset */
        $preset = $this->models->find(Preset::class, $presetId);

        if (!$preset) {
            throw new NotFoundException(sprintf('Emotion preset with id %s not found', $presetId));
        }

        if (!$preset || !$preset->getCustom()) {
            throw new \Exception(sprintf('Emotion preset %s is not defined as custom preset', $preset->getName()));
        }

        $this->models->remove($preset);
        $this->models->flush($preset);
    }

    /**
     * @param array $data
     *
     * @throws ParameterMissingException
     *
     * @return Preset
     */
    public function create(array $data, $locale = 'de_DE')
    {
        if (!array_key_exists('name', $data)) {
            throw new ParameterMissingException('name');
        }
        if (!array_key_exists('presetData', $data)) {
            throw new ParameterMissingException('presetData');
        }

        return $this->save($data, new Preset(), $locale);
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @throws \Exception
     *
     * @return Preset
     */
    public function update($id, array $data, $locale = 'de_DE')
    {
        /** @var $preset Preset */
        $preset = $this->models->getRepository(Preset::class)->find($id);
        if (!$preset) {
            throw new \Exception(sprintf('Preset with id %s not found', $id));
        }

        $preset->getTranslations()->clear();
        $preset->getRequiredPlugins()->clear();
        $this->models->flush($preset);

        return $this->save($data, $preset, $locale);
    }

    /**
     * @param array  $data
     * @param Preset $preset
     * @param string $locale
     *
     * @throws ValidationException
     *
     * @return Preset
     */
    private function save(array $data, Preset $preset, $locale = 'de_DE')
    {
        // fill translation locale when not yet set
        if (!empty($data['translations'])) {
            foreach ($data['translations'] as &$translation) {
                if (!isset($translation['locale']) && $locale) {
                    $translation['locale'] = $locale;
                }
            }
        }

        // get technical names and label of required plugins
        if (!empty($data['requiredPlugins'])) {
            $requiredPlugins = $this->getRequiredPlugins($data['requiredPlugins']);
            $data['requiredPlugins'] = [];
            if ($requiredPlugins) {
                $data['requiredPlugins'] = $requiredPlugins;
            }
        }

        // slugify technical name of preset
        $data['name'] = $this->slugService->slugify($data['name']);
        $preset->fromArray($data);
        $this->validateName($preset);

        $violations = $this->models->validate($preset);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->models->persist($preset);
        $this->models->flush();

        return $preset;
    }

    /**
     * @param Preset $preset
     *
     * @throws \Exception
     */
    private function validateName(Preset $preset)
    {
        $qb = $this->models->createQueryBuilder()
            ->select('COUNT(preset)')
            ->from(Preset::class, 'preset')
            ->where('preset.name = :name');

        if ($preset->getId()) {
            $qb->andWhere('preset.id != :id')
                ->setParameter('id', $preset->getId());
        }

        $result = $qb->setParameter('name', $preset->getName())
            ->getQuery()
            ->getSingleScalarResult();

        if ($result > 0) {
            throw new \Exception(sprintf('Preset with name %s already exists', $preset->getName()));
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createQuery()
    {
        $builder = $this->models->createQueryBuilder();

        $builder->select([
            'preset',
            'translation',
            'requiredPlugin',
        ]);

        $builder->from(Preset::class, 'preset');
        $builder->leftJoin('preset.translations', 'translation');
        $builder->leftJoin('preset.requiredPlugins', 'requiredPlugin');

        return $builder;
    }

    /**
     * @param array  $presets
     * @param string $locale
     *
     * @return array
     */
    private function preparePresetData(array $presets, $locale = 'de_DE')
    {
        $preparedPresets = [];

        foreach ($presets as $preset) {
            $label = $description = $preset['name'];
            $translation = $this->findTranslationByUserLocale($preset['translations'], $locale);
            $requiredPlugins = $this->getExtendedPluginInformation($preset['requiredPlugins']);

            if (!empty($translation)) {
                $label = $translation['label'];
                $description = $translation['description'];
            }

            $preparedPresets[] = [
                'id' => $preset['id'],
                'name' => $preset['name'],
                'premium' => $preset['premium'],
                'custom' => $preset['custom'],
                'thumbnail' => $preset['thumbnail'],
                'thumbnailUrl' => $this->getPresetImageUrl($preset['thumbnail']),
                'preview' => $preset['preview'],
                'previewUrl' => $this->getPresetImageUrl($preset['preview']),
                'presetData' => $preset['presetData'],
                'label' => $label,
                'description' => $description,
                'requiredPlugins' => $requiredPlugins,
            ];
        }

        return $preparedPresets;
    }

    /**
     * @param array $translations
     * @param $locale
     *
     * @return array
     */
    private function findTranslationByUserLocale(array $translations, $locale)
    {
        if (empty($translations) || !in_array($locale, array_column($translations, 'locale'))) {
            return [];
        }

        $requiredTranslation = [];
        /** @var array $translation */
        foreach ($translations as $translation) {
            if ($translation['locale'] === $locale) {
                $requiredTranslation = $translation;
                break;
            }
        }

        return $requiredTranslation;
    }

    /**
     * @param array $requiredPlugins
     *
     * @return array
     */
    private function getExtendedPluginInformation(array $requiredPlugins)
    {
        if (empty($requiredPlugins)) {
            return [];
        }
        $pluginData = [];

        foreach ($requiredPlugins as $requiredPlugin) {
            $pluginData[$requiredPlugin['technicalName']] = $requiredPlugin;
        }

        $query = $this->models->getConnection()->createQueryBuilder();
        $plugins = $query->select(['plugin.id, plugin.name, plugin.active'])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.name IN (:names)')
            ->setParameter(':names', array_keys($pluginData), Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($plugins as $plugin) {
            if (!array_key_exists($plugin['name'], $pluginData)) {
                $pluginData[$plugin['name']]['installationRequired'] = true;
                $pluginData[$plugin['name']]['activationRequired'] = true;

                continue;
            }
            $pluginData[$plugin['name']]['installationRequired'] = false;
            $pluginData[$plugin['name']]['activationRequired'] = !(bool) $plugin['active'];
        }

        return array_values($pluginData);
    }

    /**
     * @param string $path
     *
     * @return null|string
     */
    private function getPresetImageUrl($path)
    {
        if (strpos($path, 'media') === 0) {
            return $this->mediaService->getUrl($path);
        }

        return $this->template->fetch(sprintf('string:{url file="%s"}', $path));
    }

    /**
     * Get detailed plugin information by plugin ids
     *
     * @param array $pluginIds
     *
     * @return array
     */
    private function getRequiredPlugins(array $pluginIds)
    {
        return $this->models->getDBALQueryBuilder()
            ->select('name AS technicalName, label')
            ->from('s_core_plugins', 's')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $pluginIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }
}
