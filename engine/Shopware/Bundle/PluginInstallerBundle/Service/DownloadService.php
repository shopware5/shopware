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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Doctrine\DBAL\Connection;
use Exception;
use RuntimeException;
use Shopware\Bundle\PluginInstallerBundle\Context\DownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\MetaRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\RangeDownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\MetaStruct;
use Shopware\Components\HttpClient\HttpClientInterface;
use ShopwarePlugins\SwagUpdate\Components\Steps\DownloadStep;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\ValidResult;
use ShopwarePlugins\SwagUpdate\Components\Struct\Version;

class DownloadService
{
    private array $pluginDirectories;

    private StoreClient $storeClient;

    private Connection $connection;

    private PluginExtractor $pluginExtractor;

    private HttpClientInterface $httpClient;

    public function __construct(
        array $pluginDirectories,
        StoreClient $storeClient,
        Connection $connection,
        PluginExtractor $pluginExtractor,
        HttpClientInterface $httpClient
    ) {
        $this->pluginDirectories = $pluginDirectories;
        $this->storeClient = $storeClient;
        $this->connection = $connection;
        $this->pluginExtractor = $pluginExtractor;
        $this->httpClient = $httpClient;
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

        return (new DownloadStep($version, $request->getDestination()))->run($request->getOffset());
    }

    /**
     * @param string $file
     * @param string $pluginName
     *
     * @throws Exception
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
            throw new RuntimeException('No Plugin found in archive.');
        }
    }

    /**
     * @throws Exception
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
     * @throws Exception
     *
     * @return bool
     */
    public function download(DownloadRequest $request)
    {
        $metaRequest = new MetaRequest(
            $request->getTechnicalName(),
            $request->getShopwareVersion(),
            $request->getDomain(),
            $request->getToken()
        );

        $result = $this->getMetaInformation($metaRequest);

        $response = $this->httpClient->get($result->getUri());
        $file = $this->createDownloadZip($response->getBody());

        $this->extractPluginZip($file, $metaRequest->getTechnicalName());
        unlink($file);

        return true;
    }

    /**
     * @return string file path to the downloaded file
     */
    private function createDownloadZip(string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), 'plugin_') . '.zip';
        file_put_contents($file, $content);

        return $file;
    }

    private function getPluginSource(string $name): string
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['plugin.source'])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.name = :name')
            ->setParameter(':name', $name)
            ->setMaxResults(1);

        return (string) $query->execute()->fetchOne();
    }
}
