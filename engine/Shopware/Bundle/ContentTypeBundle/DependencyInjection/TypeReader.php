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

namespace Shopware\Bundle\ContentTypeBundle\DependencyInjection;

use Shopware\Bundle\ContentTypeBundle\Field\TypeField;
use Shopware\Bundle\ContentTypeBundle\Field\TypeGrid;
use Shopware\Bundle\ContentTypeBundle\Services\XmlReader\ContentTypesReader;

class TypeReader
{
    public function getTypes(array $activePlugins, array $pluginDirectories, array $fieldAlias): array
    {
        $result = [];
        $configs = $this->getConfigPaths($activePlugins, $pluginDirectories);

        $reader = new ContentTypesReader();

        foreach ($configs as $config) {
            $result = array_merge($result, $reader->readType($config));
        }

        $result = self::resolveAlias($result, $fieldAlias);

        return $result;
    }

    private function getConfigPaths(array $activePlugins, array $pluginDirectories): array
    {
        $configs = [];

        foreach ($pluginDirectories as $pluginDirectory) {
            foreach (new \DirectoryIterator($pluginDirectory) as $pluginDir) {
                if ($pluginDir->isFile() || strpos($pluginDir->getBasename(), '.') === 0) {
                    continue;
                }

                if (!in_array($pluginDir->getBasename(), $activePlugins, true)) {
                    continue;
                }

                if (file_exists($pluginDir->getRealPath() . '/Resources/contenttypes.xml')) {
                    $configs[] = [
                        'file' => $pluginDir->getRealPath() . '/Resources/contenttypes.xml',
                        'type' => $pluginDir->getBasename(),
                    ];
                }
            }
        }

        return $configs;
    }

    private static function resolveAlias(array $types, array $alias): array
    {
        foreach (array_keys($types) as $type) {
            $alias[$type . '-field'] = TypeField::class;
            $alias[$type . '-grid'] = TypeGrid::class;
        }

        foreach ($types as &$type) {
            foreach ($type['fieldSets'] as &$fieldSet) {
                foreach ($fieldSet['fields'] as &$field) {
                    if (!isset($alias[$field['type']])) {
                        throw new \RuntimeException(sprintf('Type with name "%s" does not exist', $field['type']));
                    }
                }
            }
        }

        return $types;
    }
}
