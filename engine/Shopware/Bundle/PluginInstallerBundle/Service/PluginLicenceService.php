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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LicenceStruct;

class PluginLicenceService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DownloadService
     */
    private $downloadService;

    /**
     * @var InstallerService
     */
    private $installer;

    /**
     * @param Connection $connection
     * @param DownloadService $downloadService
     * @param InstallerService $installer
     */
    public function __construct(
        Connection $connection,
        DownloadService $downloadService,
        InstallerService $installer
    ) {
        $this->connection = $connection;
        $this->downloadService = $downloadService;
        $this->installer = $installer;
    }

    /**
     * @param string $licenceKey
     * @return int
     */
    public function importLicence($licenceKey)
    {
        $persister = new \Shopware_Components_LicensePersister(
            $this->connection
        );

        $info = \Shopware_Components_License::readLicenseInfo($licenceKey);

        if ($info == false) {
            throw new \RuntimeException();
        }

        return $persister->saveLicense($info, true);
    }
}