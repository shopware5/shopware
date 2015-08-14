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

namespace Shopware\Bundle\MediaBundle;

use League\Flysystem\Filesystem;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Local;

class MediaBackendFactory
{
    /**
     * @var array
     */
    private $cdnConfig;

    /**
     * @param array $cdnConfig
     */
    public function __construct($cdnConfig)
    {
        $this->cdnConfig = $cdnConfig;
    }

    /**
     * Return a new FilesystemFactory instance based on the configured storage type
     *
     * @param string $backendName
     * @return MediaBackendInterface
     * @throws \Exception
     */
    public function factory($backendName)
    {
        if (!isset($this->cdnConfig['adapters'][$backendName])) {
            throw new \Exception("Configarution not found");
        }

        $config = $this->cdnConfig['adapters'][$backendName];

        return $this->createAdapterFromConfig($config);
    }

    /**
     * Looks for a filesystem adapter and initialize it with
     * a MediaBackend
     *
     * @param array $config
     * @return MediaBackendInterface
     * @throws \Exception
     */
    private function createAdapterFromConfig(array $config)
    {
        switch ($config['type']) {
            case 'local':
                $adapter = new Local($config['path']);
                break;
            default:
                throw new \Exception(sprintf("Unsupported backend type'%s'.", $config['type']));
        }

        $filesystem = new Filesystem($adapter, ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);

        return new MediaBackendFlysystemAdapter($filesystem, $config);
    }
}
