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
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Pool;
use Psr\Log\LoggerInterface;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;
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
            $guzzleConfig['cookies'] = ['shop' => $shopId];
        }

        $requests = [];
        foreach ($urls as $url) {
            $requests[] = $this->guzzleClient->createRequest('GET', $url, $guzzleConfig);
        }

        $events = $this->eventManager;

        $pool = new Pool(
            $this->guzzleClient,
            $requests,
            [
                'pool_size' => $concurrentRequests,
                'error' => function (ErrorEvent $e) use ($shopId, $events) {
                    $events->notify('Shopware_Components_CacheWarmer_ErrorOccured');
                    $this->logger->warning(
                        'Warm up http-cache error with shopId ' . $shopId . ' ' . $e->getException()->getMessage()
                    );
                },
            ]);

        $pool->wait();
    }

    /**
     * @deprecated since version 5.5, to be removed in 5.7 - Use warmUpUrls instead
     *
     * Calls every URL given with the specific shop cookie
     *
     * @param string[] $urls
     * @param array    $shop
     * @param int      $concurrentRequests
     */
    public function callUrls($urls, $shop, $concurrentRequests = 1)
    {
        /** @var Repository $shopRepository */
        $shopRepository = $this->modelManager->getRepository(Shop::class);

        $context = Context::createFromShop(
            $shopRepository->getById($shop['id']),
            $this->config
        );

        $this->warmUpUrls($urls, $context, $concurrentRequests);
    }

    /**
     * @deprecated since version 5.5, to be removed in 5.7 - Use the UrlProviders' getCount() of HttpCache instead
     *
     * Calculates the amount of available URLs based on a specific viewport and shop
     *
     * @param string $viewPort
     * @param int    $shopId
     *
     * @return int $urlCount | the number of the seo urls
     */
    public function getSEOURLByViewPortCount($viewPort, $shopId)
    {
        $urlCount = $this->connection->fetchColumn(
            'SELECT count(path)
            FROM s_core_rewrite_urls
            WHERE org_path LIKE :path AND main=1 AND subshopID = :shopId
        ',
            ['shopId' => $shopId, 'path' => $viewPort . '%']
        );

        return (int) $urlCount;
    }

    /**
     * @deprecated since version 5.5, to be removed in 5.7 - Use the UrlProviders' getCount() of HttpCache instead
     *
     * Returns the amount of all available SEO URLs
     *
     * @param int $shopId
     *
     * @return int $urlCount | the number of all seo urls
     */
    public function getAllSEOUrlCount($shopId)
    {
        $urlCount = $this->connection->fetchColumn(
            'SELECT COUNT(path)
            FROM s_core_rewrite_urls
            WHERE main=1 AND subshopID = :shopId
            ',
            ['shopId' => $shopId]
        );

        return (int) $urlCount;
    }

    /**
     * @deprecated since version 5.5, to be removed in 5.7 - The cache warmer doesn't rely on SEO URLs anymore, so its
     * highly recommended to use the UrlProviders of HttpCache instead.
     *
     * Returns all available seo urls
     *
     * @param int      $shopId
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return string[]
     */
    public function getAllSEOUrls($shopId, $limit = null, $offset = null)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(['urls.path'])
            ->from('s_core_rewrite_urls', 'urls')
            ->where('main = 1')
            ->andWhere('subshopID = :shopId')
            ->setParameter(':shopId', $shopId);

        if ($limit !== null && $offset !== null) {
            $qb->setFirstResult($offset);
            $qb->setMaxResults($limit);
        }

        $statement = $qb->execute();
        $urls = $statement->fetchAll(\PDO::FETCH_COLUMN);

        $urls = $this->prepareUrl($shopId, $urls);

        return $urls;
    }

    /**
     * @deprecated since version 5.5, to be removed in 5.7 - The cache warmer doesn't rely on SEO URLs anymore, so its
     * highly recommended to use the UrlProviders of HttpCache instead.
     *
     * Returns the URLs from the SEO URL table by the given view ports
     *
     * @param string[] $viewPorts
     * @param int      $shopId
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return string[]
     */
    public function getSEOUrlByViewPort($viewPorts, $shopId, $limit = null, $offset = null)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(['path'])
            ->from('s_core_rewrite_urls', 'urls')
            ->where('main = 1')
            ->andWhere('subshopID = :shopId')
            ->setParameter(':shopId', $shopId);

        if (count($viewPorts) > 1) {
            $orExpr = $qb->expr()->orX();
            foreach ($viewPorts as $viewPort) {
                $orExpr->add(
                    $qb->expr()->like(
                        'org_path',
                        $qb->createNamedParameter($viewPort . '%')
                    )
                );
            }
            $qb->andWhere($orExpr);
        } else {
            $qb->andWhere('org_path Like ' . $qb->createNamedParameter($viewPorts[0] . '%'));
        }

        if ($limit !== null && $offset !== null) {
            $qb->setFirstResult($offset);
            $qb->setMaxResults($limit);
        }

        $statement = $qb->execute();
        $urls = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $urls = $this->prepareUrl($shopId, $urls);

        return $urls;
    }

    private function getMainShopId($shopId)
    {
        return $this->connection->fetchColumn(
            'SELECT main_id FROM s_core_shops WHERE active = 1 AND id = :id',
            ['id' => (int) $shopId]
        );
    }

    /**
     * @deprecated since version 5.5, to be removed in 5.7 - The cache warmer doesn't rely on SEO URLs anymore, so its
     * highly recommended to use the UrlProviders' getUrls() of HttpCache instead.
     *
     * Helper to add the host and the basepath as a prefix to the url
     *
     * @param int      $shopId
     * @param string[] $urls
     *
     * @return string[]
     */
    private function prepareUrl($shopId, $urls)
    {
        $shop = $this->getShopDataById($shopId);

        //if not already the main shop get it
        $mainShop = !empty($shop['main_id']) ? $this->getShopDataById($shop['main_id']) : $shop;
        $httpHost = $mainShop['secure'] ? 'https://' : 'http://';
        if ($shop['base_url']) {
            $baseUrl = $shop['base_url'];
        } else {
            // If no virtual url of the language shop is give us the one from the main shop. Otherwise use simply the base_path
            $baseUrl = $mainShop['base_url'] ?: $mainShop['base_path'];
        }
        // Use the main host if no language host ist available
        $shopHost = empty($shop['host']) ? $mainShop['host'] : $shop['host'];

        foreach ($urls as &$url) {
            $url = $httpHost . $shopHost . $baseUrl . '/' . strtolower($url);
        }

        return $urls;
    }

    /**
     * @deprecated since version 5.5, to be removed in 5.7 - Only used by `prepareUrl` which is deprecated
     *
     * Returns the shop object by id
     *
     * @param int $shopId
     *
     * @return array
     */
    private function getShopDataById($shopId)
    {
        $shopData = $this->connection->fetchAssoc(
            'SELECT * FROM s_core_shops WHERE active = 1 AND id = :id',
            ['id' => (int) $shopId]
        );

        return $shopData;
    }
}
