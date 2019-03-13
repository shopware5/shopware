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
use Shopware\Bundle\PluginInstallerBundle\Context\MetaRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\RangeDownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\MetaStruct;
use ShopwarePlugins\SwagUpdate\Components\Steps\DownloadStep;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\ValidResult;
use ShopwarePlugins\SwagUpdate\Components\Struct\Version;

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
     * @var PluginExtractor
     */
    private $pluginExtractor;

    public function __construct(
        array $pluginDirectories,
        StoreClient $storeClient,
        Connection $connection,
        PluginExtractor $pluginExtractor
    ) {
        $this->pluginDirectories = $pluginDirectories;
        $this->storeClient = $storeClient;
        $this->connection = $connection;
        $this->pluginExtractor = $pluginExtractor;
    }

    /**
     * @return FinishResult|ValidResult
     */
    public function downloadRange(RangeDownloadRequest $request)
    {
        // Load SwagUpdate so the DownloadStep can be auto-loaded
        Shopware()->Plugins()->Backend()->SwagUpdate();

        $version = new Version([
            'uri' => $request->getUri(),
            'size' => $request->getSize(),
            'sha1' => $request->getSha1(),
        ]);

        $step = new DownloadStep($version, $request->getDestination());

        return $step->run($request->getOffset());
    }

    /**
     * @param string $file
     * @param string $pluginName
     *
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
            $this->pluginExtractor->extract($archive);
        } else {
            throw new \RuntimeException('No Plugin found in archive.');
        }
    }

    /**
     * @throws \Exception
     *
     * @return MetaStruct
     */
    public function getMetaInformation(MetaRequest $request)
    {
        $params = [
            'domain' => $request->getDomain(),
            'technicalName' => $request->getTechnicalName(),
            'shopwareVersion' => $request->getVersion(),
        ];

        if ($request->getToken()) {
            $result = $this->storeClient->doAuthGetRequestRaw(
                $request->getToken(),
                '/pluginFiles/' . $request->getTechnicalName() . '/data',
                $params
            );
        } else {
            $result = $this->storeClient->doGetRequestRaw(
                '/pluginFiles/' . $request->getTechnicalName() . '/data',
                $params
            );
        }

        $result = json_decode($result, true);

        return new MetaStruct(
            $result['location'],
            $result['size'],
            $result['sha1'],
            $result['binaryVersion'],
            md5($request->getTechnicalName()) . '.zip',
            $request->getTechnicalName()
        );
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function download(DownloadRequest $request)
    {
        $request = new MetaRequest(
            $request->getTechnicalName(),
            $request->getShopwareVersion(),
            $request->getDomain(),
            $request->getToken()
        );

        $result = $this->getMetaInformation($request);

        /** @var \Shopware\Components\HttpClient\HttpClientInterface $client */
        $client = Shopware()->Container()->get('http_client');

        $response = $client->get($result->getUri());
        $file = $this->createDownloadZip($response->getBody());

        $this->extractPluginZip($file, $request->getTechnicalName());
        unlink($file);

        return true;
    }

    /**
     * @param string $content
     *
     * @return string file path to the downloaded file
     */
    private function createDownloadZip($content)
    {
        $file = tempnam(sys_get_temp_dir(), 'plugin_') . '.zip';
        file_put_contents($file, $content);

        return $file;
    }

    /**
     * @param string $name
     *
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

        /** @var \PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }
}
