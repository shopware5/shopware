<?php
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

namespace Shopware\Recovery\Update;

use Gaufrette\Adapter\Ftp as FtpAdapter;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Filesystem;

class FilesystemFactory
{
    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var array
     */
    private $remoteConfig;

    /**
     * @param string $baseDir
     * @param array  $remoteConfig
     */
    public function __construct($baseDir, $remoteConfig)
    {
        $this->baseDir = $baseDir;
        $this->remoteConfig = $remoteConfig;
    }

    /**
     * @return Filesystem
     */
    public function createLocalFilesystem()
    {
        return $this->getLocalFilesystem();
    }

    /**
     * @return Filesystem
     */
    public function createRemoteFilesystem()
    {
        if (!empty($this->remoteConfig)) {
            return $this->getRemoteFilesystem();
        }

        return $this->getLocalFilesystem();
    }

    /**
     * @return Filesystem
     */
    private function getLocalFilesystem()
    {
        $adapter = new LocalAdapter($this->baseDir);

        return new Filesystem($adapter);
    }

    /**
     * @return Filesystem
     */
    private function getRemoteFilesystem()
    {
        $adapter = new FtpAdapter(
            $this->remoteConfig['path'],
            $this->remoteConfig['server'],
            [
                'username' => $this->remoteConfig['user'],
                'password' => $this->remoteConfig['password'],
            ]
        );

        return new Filesystem($adapter);
    }
}
