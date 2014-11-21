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
use GuzzleHttp\Client;
use Shopware\Components\Logger;

/**
 * Shopware Application
 *
 * Warm up the cache with direct http calls using the SEO URLs
 *
 * @category  Shopware
 * @package   Shopware\Components\HttpCacheWarmer
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
     * @var Logger $logger
     */
    protected $logger;

    /**
     * standard constructor
     *
     * @param Connection $connection
     * @param Logger $logger
     */
    public function __construct(Connection $connection, Logger $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * calculates the amount of available urls based on a specific viewport and shop
     *
     * @param string $viewPort
     * @param integer $shopId
     * @return integer $urlCount | the number of the seo urls
     */
    public function getSEOURLByViewPortCount($viewPort, $shopId)
    {
        $urlCount = $this->connection->fetchColumn(
            'SELECT count(path)
            FROM s_core_rewrite_urls
            WHERE org_path LIKE :path AND main=1 AND subshopID = :shopId
        ',
            array('shopId' => $shopId, 'path' => $viewPort . '%')
        );

        return (int)$urlCount;
    }

    /**
     * returns the amount of all available seo urls
     *
     * @param integer $shopId
     * @return integer $urlCount | the number of all seo urls
     */
    public function getAllSEOUrlCount($shopId)
    {
        $urlCount = $this->connection->fetchColumn(
            'SELECT count(path)
            FROM s_core_rewrite_urls
            WHERE main=1 AND subshopID = :shopId
        ',
            array('shopId' => $shopId)
        );

        return (int)$urlCount;
    }

    /**
     * returns all available seo urls
     *
     * @param integer $shopId
     * @param null $limit
     * @param null $offset
     * @return string[]
     */
    public function getAllSEOUrls($shopId, $limit = null, $offset = null)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(array('urls.path'))
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
     * returns the urls from the seo url table by the given view ports
     *
     * @param string[] $viewPorts
     * @param integer $shopId
     * @param null $limit
     * @param null $offset
     * @return string[]
     */
    public function getSEOUrlByViewPort($viewPorts, $shopId, $limit = null, $offset = null)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(array('path'))
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

        if ($limit != null && $offset != null) {
            $qb->setFirstResult($offset);
            $qb->setMaxResults($limit);
        }

        $statement = $qb->execute();
        $urls = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $urls = $this->prepareUrl($shopId, $urls);

        return $urls;
    }

    /**
     * calls every given url with the specific shop cookie
     *
     * @param string[] $urls
     * @param integer $shopId
     */
    public function callUrls($urls, $shopId)
    {
        $httpClient = new Client();
        $shop = $this->getShopDataById($shopId);

        foreach ($urls as $url) {
            if (empty($shop["main_id"])) {
                //is main, shop call url without shop cookie encoded in it
                $request = $httpClient->createRequest('GET', $url);
            } else {
                $request = $httpClient->createRequest('GET', $url, ['cookies' => ['shop' => $shopId]]);
            }

            try {
                $httpClient->send($request);
            } catch (\Exception $e) {
                $this->logger->error(
                    "Warm up http-cache error with shopId " . $shopId . " " . $e->getMessage()
                );
            }
        }
    }

    /**
     * helper to add the host and the basepath as a prefix to the url
     *
     * @param integer $shopId
     * @param string[] $urls
     * @return string[]
     */
    private function prepareUrl($shopId, $urls)
    {
        $shop = $this->getShopDataById($shopId);

        if (!empty($shop['main_id'])) {
            // not the main shop get it
            $shop = $this->getShopDataById($shop['main_id']);
        }
        foreach ($urls as &$url) {
            $url = strtolower($url);
            $httpHost = $shop['always_secure'] ? 'https://' : 'http://';
            $baseUrl = $shop['base_url'] ? $shop['base_url'] : $shop['base_path'];
            $url = $httpHost . $shop['host'] . $baseUrl . "/" . $url;
        }

        return $urls;
    }

    /**
     * returns the shop object by id
     *
     * @param integer $shopId
     * @return array
     */
    private function getShopDataById($shopId)
    {
        $shopData = $this->connection->fetchAssoc(
            'SELECT * FROM s_core_shops WHERE active = 1 AND id = :id',
            array('id' => $shopId)
        );

        return $shopData;
    }
}
