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

    /**
     * @param string $locale
     *
     * @return array[]
     */
    public function getList($locale = 'de_DE')
    {
        return $this->hydrate($this->fetch(), $locale);
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
            $data['requiredPlugins'] = $this->formatRequiredPlugins($data['requiredPlugins']);
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
     * @return array[]
     */
    private function fetch()
    {
        $builder = $this->models->createQueryBuilder();
        $builder->select(['preset', 'translation', 'requiredPlugin']);
        $builder->from(Preset::class, 'preset', 'preset.id');
        $builder->leftJoin('preset.translations', 'translation');
        $builder->leftJoin('preset.requiredPlugins', 'requiredPlugin', null, null, 'requiredPlugin.technicalName');

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * @param array  $presets
     * @param string $locale
     *
     * @return array[]
     */
    private function hydrate(array $presets, $locale = 'de_DE')
    {
        $localPlugins = $this->getPlugins(
            $this->extractTechnicalNames($presets)
        );

        $result = [];
        foreach ($presets as $preset) {
            $data = [
                'id' => $preset['id'],
                'name' => $preset['name'],
                'label' => $preset['name'],
                'description' => $preset['name'],
                'premium' => $preset['premium'],
                'custom' => $preset['custom'],
                'thumbnail' => $preset['thumbnail'],
                'preview' => $preset['preview'],
                'presetData' => $preset['presetData'],
                'thumbnailUrl' => $this->getPresetImageUrl($preset['thumbnail']),
                'previewUrl' => $this->getPresetImageUrl($preset['preview']),
                'requiredPlugins' => $this->getPluginsForPreset($preset, $localPlugins),
            ];

            $result[] = array_merge($data, $this->extractTranslation($preset['translations'], $locale));
        }

        return $result;
    }

    /**
     * @param array  $translations
     * @param string $locale
     *
     * @return array
     */
    private function extractTranslation(array $translations, $locale)
    {
        /** @var array $translation */
        foreach ($translations as $translation) {
            if ($translation['locale'] === $locale) {
                return $translation;
            }
        }

        return [];
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
    private function formatRequiredPlugins(array $pluginIds)
    {
        return $this->models->getDBALQueryBuilder()
            ->select('name AS technicalName, label', 'version')
            ->from('s_core_plugins', 's')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $pluginIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string[] $technicalNames
     *
     * @return array
     */
    private function getPlugins($technicalNames)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'plugin.name as array_key',
            'plugin.name as plugin_name',
            'plugin.label as plugin_label',
            'plugin.active',
            'plugin.installation_date IS NOT NULL as installed',
            'plugin.version as current_version',
        ]);
        $query->from('s_core_plugins', 'plugin');
        $query->where('plugin.name IN (:names)');
        $query->setParameter(':names', $technicalNames, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }

    /**
     * @param array $preset
     * @param array $localPlugins
     *
     * @return array
     */
    private function getPluginsForPreset(array $preset, array $localPlugins)
    {
        $required = $preset['requiredPlugins'];
        $merged = array_merge_recursive($required, $localPlugins);
        $requiredPlugins = array_values(array_intersect_key($merged, $required));

        return $requiredPlugins;
    }

    /**
     * @param array $presets
     *
     * @return array
     */
    private function extractTechnicalNames(array $presets)
    {
        $technicalNames = [];
        foreach ($presets as $preset) {
            $technicalNames = array_merge($technicalNames, array_keys($preset['requiredPlugins']));
        }

        return $technicalNames;
    }
}
