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

namespace Shopware\Components\HttpCache;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware_Components_Config as Config;

/**
 * Shopware Application
 *
 * Warm up the cache with direct http calls using the SEO URLs
 */
class CacheWarmer
{
    const ARTICLE_PATH = 'sViewport=detail&sArticle';
    const CATEGORY_PATH = 'sViewport=cat&sCategory';
    const BlOG_PATH = 'sViewport=blog';
    const SUPPLIER_PATH = 'sViewport=listing&sAction=manufacturer&sSupplier=';
    const CUSTOM_PATH = 'sViewport=custom&sCustom';
    const EMOTION_LANDING_PAGE_PATH = 'sViewport=campaign';

    /**
     * @var Connection connection
     */
    protected $connection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientInterface
     */
    private $guzzleClient;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var ContainerAwareEventManager
     */
    private $eventManager;

    public function __construct(LoggerInterface $logger, GuzzleFactory $guzzleFactory, Config $config, ModelManager $modelManager, ContainerAwareEventManager $eventManager)
    {
        $this->connection = $modelManager->getConnection();
        $this->logger = $logger;
        $this->guzzleClient = $guzzleFactory->createClient();
        $this->config = $config;
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;
    }

    /**
     * Calls every URL given with the specific context
     *
     * @param string[] $urls
     * @param int      $concurrentRequests
     */
    public function warmUpUrls($urls, Context $context, $concurrentRequests = 1)
    {
        $shopId = $context->getShopId();

        $guzzleConfig = [];
        if (!empty($this->getMainShopId($shopId))) {
            $guzzleConfig['Cookie'] = 'shop=' . $shopId;
        }

        $requests = [];
        foreach ($urls as $url) {
            $requests[] = new Request('GET', $url, $guzzleConfig);
        }

        $events = $this->eventManager;

        $pool = new Pool(
            $this->guzzleClient,
            $requests,
            [
                'concurrency' => $concurrentRequests,
                'rejected' => function ($reason) use ($shopId, $events) {
                    $events->notify('Shopware_Components_CacheWarmer_ErrorOccured');
                    $this->logger->warning(
                        'Warm up http-cache error with shopId ' . $shopId . ' ' . $reason
                    );
                },
            ]
        );

        $pool->promise()->wait();
    }

    private function getMainShopId($shopId)
    {
        return $this->connection->fetchColumn(
            'SELECT main_id FROM s_core_shops WHERE active = 1 AND id = :id',
            ['id' => (int) $shopId]
        );
    }
}
