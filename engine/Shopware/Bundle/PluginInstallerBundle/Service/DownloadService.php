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
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LicenceStruct;

/**
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
class DownloadService
{
    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param $rootDir
     * @param StoreClient $storeClient
     * @param Connection $connection
     */
    public function __construct(
        $rootDir,
        StoreClient $storeClient,
        Connection $connection
    ) {
        $this->rootDir = $rootDir;
        $this->storeClient = $storeClient;
        $this->connection = $connection;
    }

    /**
     * @param UpdateRequest $request
     * @return bool
     * @throws \Exception
     */
    public function downloadUpdate(UpdateRequest $request)
    {
        $content = $this->executeDownloadRequest($request);

        $file = $this->download($content);

        $this->extractPluginZip($file, $request->getPluginName());

        return true;
    }

    /**
     * @param UpdateRequest $request
     * @return string
     */
    private function executeDownloadRequest(UpdateRequest $request)
    {
        if ($request->getToken()) {
            return $this->storeClient->doAuthGetRequestRaw(
                $request->getToken(),
                '/pluginFiles/' . $request->getPluginName() . '/file',
                [
                    'shopwareVersion' => $request->getShopwareVersion(),
                    'domain' => $request->getDomain()
                ]
            );
        }

        return $this->storeClient->doGetRequestRaw(
            '/pluginFiles/'. $request->getPluginName() . '/file',
            [
                'shopwareVersion' => $request->getShopwareVersion(),
                'domain' => $request->getDomain()
            ]
        );
    }

    /**
     * @param AccessTokenStruct $token
     * @param LicenceStruct $licence
     * @return bool
     */
    public function downloadPlugin(AccessTokenStruct $token, LicenceStruct $licence)
    {
        $content = $this->storeClient->doGetRequestRaw(
            $licence->getBinaryLink(),
            [ 'token' => $token->getToken(), 'domain' => $licence->getShop() ]
        );

        $file = $this->download($content);

        $this->extractPluginZip($file, $licence->getTechnicalName());

        return true;
    }

    /**
     * @param $pluginName
     * @param $version
     * @return bool
     */
    public function downloadDummy($pluginName, $version)
    {
        $content = $this->storeClient->doGetRequestRaw(
            '/pluginFiles/' . $pluginName . '/file',
            ['shopwareVersion' => $version]
        );

        $file = $this->download($content);

        $this->extractPluginZip($file, $pluginName);

        return true;
    }

    /**
     * @param $content
     * @return string File path to the downloaded file.
     */
    private function download($content)
    {
        $name = 'plugin_' . md5(uniqid()) . '.zip';

        $file = $this->rootDir . '/files/downloads/' . $name;

        file_put_contents($file, $content);

        return $file;
    }

    /**
     * @param $file
     * @param $pluginName
     * @throws \Exception
     */
    private function extractPluginZip($file, $pluginName)
    {
        $source = $this->getPluginSource($pluginName);
        if (!$source) {
            $source = 'Community';
        }
        $destination = $this->rootDir . '/engine/Shopware/Plugins/' . $source;

        $extractor = new PluginExtractor();
        $extractor->extract($file, $destination);
    }

    /**
     * @param $name
     * @return string
     */
    private function getPluginSource($name)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['plugin.source'])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.name = :name')
            ->setParameter(':name', $name)
            ->setMaxResults(1);

        /**@var $statement \PDOStatement*/
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }
}
