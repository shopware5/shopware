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
use Shopware\Bundle\PluginInstallerBundle\Context\DownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\RangeDownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\MetaRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\MetaStruct;
use ShopwarePlugins\SwagUpdate\Components\Steps\DownloadStep;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\ValidResult;
use ShopwarePlugins\SwagUpdate\Components\Struct\Version;
use Symfony\Component\Filesystem\Filesystem;

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
     * @var array
     */
    private $pluginDirectories;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param string $rootDir
     * @param array $pluginDirectories
     * @param StoreClient $storeClient
     * @param Connection $connection
     */
    public function __construct(
        $rootDir,
        array $pluginDirectories,
        StoreClient $storeClient,
        Connection $connection
    ) {
        $this->pluginDirectories = $pluginDirectories;
        $this->storeClient = $storeClient;
        $this->connection = $connection;
        $this->rootDir = $rootDir;
    }

    /**
     * @param RangeDownloadRequest $request
     * @return FinishResult|ValidResult
     */
    public function downloadRange(RangeDownloadRequest $request)
    {
        // Load SwagUpdate so the DownloadStep can be autoloaded
        Shopware()->Plugins()->Backend()->SwagUpdate();

        $version = new Version([
            'uri'  => $request->getUri(),
            'size' => $request->getSize(),
            'sha1' => $request->getSha1()
        ]);

        $step = new DownloadStep($version, $request->getDestination());
        return $step->run($request->getOffset());
    }

    /**
     * @param string $file
     * @param string $pluginName
     * @throws \Exception
     */
    public function extractPluginZip($file, $pluginName)
    {
        $archive = ZipUtils::openZip($file);
        $pluginZipDetector = new PluginZipDetector();

        if ($pluginZipDetector->isLegacyPlugin($archive)) {
            $source = $this->getPluginSource($pluginName);
            if (!$source) {
                $source = 'Community';
            }
            $destination = $this->pluginDirectories[$source];
            $extractor = new LegacyPluginExtractor();
            $extractor->extract($archive, $destination);
        } elseif ($pluginZipDetector->isPlugin($archive)) {
            $pluginDir = $this->rootDir.'/custom/plugins';
            $extractor = new PluginExtractor($pluginDir, new Filesystem());
            $extractor->extract($archive);
        } else {
            throw new \RuntimeException('No Plugin found in archive.');
        }
    }

    /**
     * @param MetaRequest $request
     * @return MetaStruct
     */
    public function getMetaInformation(MetaRequest $request)
    {
        $params = [
            'domain' => $request->getDomain(),
            'technicalName' => $request->getTechnicalName(),
            'shopwareVersion' => $request->getVersion()
        ];

        if ($request->getToken()) {
            $result = $this->storeClient->doAuthGetRequestRaw(
                $request->getToken(),
                '/pluginFiles/'.$request->getTechnicalName().'/data',
                $params
            );
        } else {
            $result = $this->storeClient->doGetRequestRaw(
                '/pluginFiles/'.$request->getTechnicalName().'/data',
                $params
            );
        }

        $result = json_decode($result, true);

        return new MetaStruct(
            $result['location'],
            $result['size'],
            $result['sha1'],
            $result['binaryVersion'],
            md5($request->getTechnicalName()) . '.zip'
        );
    }

    /**
     * @param DownloadRequest $request
     * @return bool
     */
    public function download(DownloadRequest $request)
    {
        $content = $this->downloadFullZip($request);

        $file = $this->createDownloadZip($content);

        $this->extractPluginZip($file, $request->getTechnicalName());

        return true;
    }

    /**
     * @param DownloadRequest $request
     * @return string
     */
    private function downloadFullZip(DownloadRequest $request)
    {
        if ($request->getToken()) {
            return $this->storeClient->doAuthGetRequestRaw(
                $request->getToken(),
                '/pluginFiles/' . $request->getTechnicalName() . '/file',
                [ 'shopwareVersion' => $request->getShopwareVersion(), 'domain' => $request->getDomain() ]
            );
        }

        return $this->storeClient->doGetRequestRaw(
            '/pluginFiles/'. $request->getTechnicalName() . '/file',
            [ 'shopwareVersion' => $request->getShopwareVersion(), 'domain' => $request->getDomain() ]
        );
    }

    /**
     * @param $content
     * @return string File path to the downloaded file.
     */
    private function createDownloadZip($content)
    {
        $name = 'plugin_' . md5(uniqid()) . '.zip';

        $file = $this->rootDir . '/files/downloads/' . $name;

        file_put_contents($file, $content);

        return $file;
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
