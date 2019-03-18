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
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\PrivilegeException;
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
     * @var SlugInterface
     */
    private $slugService;

    public function __construct(
        Connection $connection,
        ModelManager $models,
        SlugInterface $slugService
    ) {
        $this->connection = $connection;
        $this->models = $models;
        $this->slugService = $slugService;

        $this->setManager($models);
    }

    /**
     * @param string $locale
     * @param bool   $fetchAll
     *
     * @return array[]
     */
    public function getList($locale = 'de_DE', $fetchAll = true)
    {
        return $this->hydrate($this->fetch($fetchAll), $locale);
    }

    /**
     * @param int $presetId
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws \Exception
     */
    public function delete($presetId)
    {
        if (!$presetId) {
            throw new ParameterMissingException('id');
        }

        /** @var Preset|null $preset */
        $preset = $this->models->find(Preset::class, $presetId);

        if (!$preset) {
            throw new NotFoundException(sprintf('Emotion preset with id %s not found', $presetId));
        }

        if (!$preset->getCustom()) {
            throw new PrivilegeException(sprintf('Emotion preset %s is not defined as custom preset', $preset->getName()));
        }

        $this->models->remove($preset);
        $this->models->flush($preset);
    }

    /**
     * @param string $locale
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
     * @param int    $id
     * @param string $locale
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return Preset
     */
    public function update($id, array $data, $locale = 'de_DE')
    {
        /** @var Preset|null $preset */
        $preset = $this->models->getRepository(Preset::class)->find($id);
        if (!$preset) {
            throw new NotFoundException(sprintf('Preset with id %s not found', $id));
        }

        $preset->getTranslations()->clear();
        $this->models->flush($preset);

        return $this->save($data, $preset, $locale);
    }

    /**
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
            unset($translation);
        }

        if (!is_array($data['requiredPlugins'])) {
            $data['requiredPlugins'] = [];
        }

        $data['requiredPlugins'] = json_encode($data['requiredPlugins']);

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
     * @throws CustomValidationException
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
            throw new CustomValidationException(sprintf('Preset with name %s already exists', $preset->getName()));
        }
    }

    /**
     * @param bool $fetchAll
     *
     * @return array[]
     */
    private function fetch($fetchAll = true)
    {
        $builder = $this->models->createQueryBuilder();

        if ($fetchAll) {
            $builder->select(['preset, translation']);
        } else {
            $builder->select(['partial preset.{id, name, premium, custom, thumbnail, assetsImported, hidden, requiredPlugins}', 'translation']);
        }

        $builder->from(Preset::class, 'preset', 'preset.id');
        $builder->leftJoin('preset.translations', 'translation');
        $builder->where('preset.hidden = 0');

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * @param string $locale
     *
     * @return array[]
     */
    private function hydrate(array $presets, $locale = 'de_DE')
    {
        $pluginNames = [];
        foreach ($presets as $id => $preset) {
            $plugins = json_decode($preset['requiredPlugins'], true);
            $preset['requiredPlugins'] = array_combine(array_column($plugins, 'name'), $plugins);
            $pluginNames = array_merge($pluginNames, array_keys($preset['requiredPlugins']));
            $presets[$id] = $preset;
        }
        $localPlugins = $this->getPlugins($pluginNames);

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
                'requiredPlugins' => $this->getPluginsForPreset($preset, $localPlugins),
                'assetsImported' => $preset['assetsImported'],
            ];

            $translation = $this->extractTranslation($preset['translations'], $locale);
            $result[] = array_merge($data, $translation);
        }

        return $result;
    }

    /**
     * @param string $locale
     *
     * @return array
     */
    private function extractTranslation(array $translations, $locale)
    {
        /** @var array $translation */
        foreach ($translations as $translation) {
            if ($translation['locale'] === $locale) {
                unset($translation['id']);

                return $translation;
            }
        }

        return [];
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
            '(plugin.id > 0) as plugin_exists',
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
     * @return array
     */
    private function getPluginsForPreset(array $preset, array $localPlugins)
    {
        $required = $preset['requiredPlugins'];
        $merged = array_merge_recursive($required, $localPlugins);
        $plugins = array_values(array_intersect_key($merged, $required));

        return array_map(
            function ($plugin) {
                $plugin = array_merge([
                    'active' => false,
                    'plugin_exists' => false,
                    'installed' => false,
                    'plugin_label' => $plugin['label'],
                    'plugin_name' => $plugin['name'],
                    'current_version' => null,
                    'valid' => false,
                ], $plugin);

                $plugin['updateRequired'] = version_compare($plugin['version'], $plugin['current_version'], '>');
                $plugin['valid'] = ($plugin['active'] && $plugin['installed'] && !$plugin['updateRequired']);

                return $plugin;
            },
            $plugins
        );
    }
}
