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

namespace Shopware\Bundle\MediaBundle\Adapters;

use League\Flysystem\Adapter\Local;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocalAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @return Local
     */
    public function create(array $config)
    {
        if (isset($config['path'])) {
            $config['root'] = $config['path'];
            unset($config['path']);
        }

        $options = $this->resolveOptions($config);

        return new Local(
            $options['root'],
            LOCK_EX,
            Local::DISALLOW_LINKS,
            $options
        );
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'local';
    }

    /**
     * @return array
     */
    private function resolveOptions(array $config)
    {
        $options = new OptionsResolver();

        $options->setRequired(['root']);
        $options->setDefined(['file', 'dir', 'mediaUrl', 'type', 'permissions', 'url']);

        $options->setAllowedTypes('root', 'string');
        $options->setAllowedTypes('file', 'array');
        $options->setAllowedTypes('dir', 'array');
        $options->setAllowedTypes('mediaUrl', 'string');
        $options->setAllowedTypes('type', 'string');
        $options->setAllowedTypes('permissions', 'array');

        $options->setDefault('file', []);
        $options->setDefault('dir', []);

        $config = $options->resolve($config);
        $config['file'] = $this->resolveFilePermissions($config['file']);
        $config['dir'] = $this->resolveDirectoryPermissions($config['dir']);

        return $config;
    }

    /**
     * @return array
     */
    private function resolveFilePermissions(array $permissions)
    {
        $options = new OptionsResolver();

        $options->setDefined(['public', 'private']);

        $options->setAllowedTypes('public', 'int');
        $options->setAllowedTypes('private', 'int');

        $options->setDefault('public', 0666 & ~umask());
        $options->setDefault('private', 0600 & ~umask());

        return $options->resolve($permissions);
    }

    /**
     * @return array
     */
    private function resolveDirectoryPermissions(array $permissions)
    {
        $options = new OptionsResolver();

        $options->setDefined(['public', 'private']);

        $options->setAllowedTypes('public', 'int');
        $options->setAllowedTypes('private', 'int');

        $options->setDefault('public', 0777 & ~umask());
        $options->setDefault('private', 0700 & ~umask());

        return $options->resolve($permissions);
    }
}
