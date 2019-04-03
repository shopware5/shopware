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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BenchmarkCollector;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\License\Service\LicenseUnpackServiceInterface;
use Shopware\Components\ShopwareReleaseStruct;

class ShopwareProvider implements BenchmarkProviderInterface
{
    private const NAME = 'shopware';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ShopwareReleaseStruct
     */
    private $releaseStruct;

    /**
     * @var LicenseUnpackServiceInterface
     */
    private $licenseUnpackService;

    public function __construct(
        Connection $connection,
        ShopwareReleaseStruct $releaseStruct,
        LicenseUnpackServiceInterface $licenseUnpackService
    ) {
        $this->connection = $connection;
        $this->releaseStruct = $releaseStruct;
        $this->licenseUnpackService = $licenseUnpackService;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        return [
            'api' => $this->getApi(),
            'os' => $this->getOs(),
            'arch' => $this->getArch(),
            'dist' => $this->getDist(),
            'serverSoftware' => $this->getServerSoftware(),
            'phpVersion' => $this->getPhpVersion(),
            'phpVersionId' => $this->getPhpVersionId(),
            'maxExecutionTime' => $this->getMaxExecutionTime(),
            'memoryLimit' => $this->getMemoryLimit(),
            'sApi' => $this->getSApi(),
            'extensions' => $this->getExtensions(),
            'mysqlVersion' => $this->getMysqlVersion(),
            'version' => $this->getVersion(),
            'revision' => $this->getRevision(),
            'licence' => $this->getLicence(),
            'shops' => $this->getShopCount(),
        ];
    }

    /**
     * @return string
     */
    private function getApi()
    {
        return BenchmarkCollector::getVersion();
    }

    /**
     * @return string
     */
    private function getOs()
    {
        return PHP_OS ?: '';
    }

    /**
     * @return string
     */
    private function getArch()
    {
        return php_uname('m') ?: '';
    }

    /**
     * @return string
     */
    private function getDist()
    {
        return php_uname('r') ?: '';
    }

    /**
     * @return string
     */
    private function getServerSoftware()
    {
        return isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
    }

    /**
     * @return string
     */
    private function getPhpVersion()
    {
        return PHP_VERSION;
    }

    /**
     * @return int
     */
    private function getPhpVersionId()
    {
        return PHP_VERSION_ID;
    }

    /**
     * @return int
     */
    private function getMaxExecutionTime()
    {
        return (int) ini_get('max_execution_time');
    }

    /**
     * @return int
     */
    private function getMemoryLimit()
    {
        return (int) ini_get('memory_limit');
    }

    /**
     * @return string
     */
    private function getSApi()
    {
        return PHP_SAPI;
    }

    /**
     * @return array
     */
    private function getExtensions()
    {
        return array_values(get_loaded_extensions());
    }

    /**
     * @return string
     */
    private function getMysqlVersion()
    {
        return (string) $this->connection->fetchColumn('SELECT @@version');
    }

    /**
     * @return string
     */
    private function getVersion()
    {
        return $this->releaseStruct->getVersion() . '-' . $this->releaseStruct->getVersionText();
    }

    /**
     * @return string
     */
    private function getRevision()
    {
        return $this->releaseStruct->getRevision();
    }

    /**
     * @return string
     */
    private function getLicence()
    {
        $license = $this->connection->fetchColumn('SELECT license FROM s_core_licenses WHERE active=1 AND module = "SwagCommercial"');

        if (!$license) {
            return 'ce';
        }

        try {
            $licenseInfo = $this->licenseUnpackService->readLicenseInfo($license);

            if (isset($licenseInfo['product'])) {
                return strtolower($licenseInfo['product']);
            }
        } catch (\Exception $e) {
        }

        return 'ce';
    }

    /**
     * @return int
     */
    private function getShopCount()
    {
        return (int) $this->connection->fetchColumn('SELECT COUNT(id) FROM s_core_shops');
    }
}
