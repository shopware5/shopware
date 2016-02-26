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
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateLicencesRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;

class PluginLicenceService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var InstallerService
     */
    private $installer;

    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @param Connection $connection
     * @param InstallerService $installer
     * @param StoreClient $storeClient
     */
    public function __construct(
        Connection $connection,
        InstallerService $installer,
        StoreClient $storeClient
    ) {
        $this->connection = $connection;
        $this->installer = $installer;
        $this->storeClient = $storeClient;
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

    /**
     * @param UpdateLicencesRequest $request
     * @return array
     */
    public function updateLicences(UpdateLicencesRequest $request)
    {
        $response = $this->storeClient->doAuthPostRequestRaw(
            $request->getToken(),
            '/licenseupgrades/simple',
            [
                'domain' => $request->getDomain(),
                'shopwareVersion' => $request->getShopwareVersion(),
                'locale' => $request->getLocale()
            ]
        );

        return $response;
    }
}
