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

use Google\Cloud\Storage\StorageClient;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoogleStorageFactory implements AdapterFactoryInterface
{
    /**
     * @return GoogleStorageAdapter
     */
    public function create(array $config)
    {
        $options = $this->resolveStorageConfig($config);

        $storageClient = new StorageClient([
            'projectId' => $options['projectId'],
            'keyFilePath' => $options['keyFilePath'],
        ]);

        $bucket = $storageClient->bucket($options['bucket']);

        return new GoogleStorageAdapter($storageClient, $bucket, $options['root']);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'gcp';
    }

    /**
     * @return array
     */
    private function resolveStorageConfig(array $definition)
    {
        $options = new OptionsResolver();

        $options->setRequired(['projectId', 'keyFilePath', 'bucket']);
        $options->setDefined(['root', 'mediaUrl', 'type', 'url']);

        $options->setAllowedTypes('projectId', 'string');
        $options->setAllowedTypes('keyFilePath', 'string');
        $options->setAllowedTypes('bucket', 'string');
        $options->setAllowedTypes('root', 'string');

        $options->setDefault('root', '');

        return $options->resolve($definition);
    }
}
