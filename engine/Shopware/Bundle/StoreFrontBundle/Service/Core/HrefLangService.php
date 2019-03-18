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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\HrefLangServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\HrefLang;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\PreFilterInterface;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Shop\Shop as ShopModel;
use Shopware_Components_Config as Config;

class HrefLangService implements HrefLangServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $contextCache = [];

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var PreFilterInterface[]
     */
    private $preFilters;

    public function __construct(Connection $connection, RouterInterface $router, Config $config, ModelManager $modelManager, \IteratorAggregate $preFilters)
    {
        $this->connection = $connection;
        $this->router = $router;
        $this->config = $config;
        $this->modelManager = $modelManager;
        $this->preFilters = iterator_to_array($preFilters, false);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(array $parameters, ShopContextInterface $contextService)
    {
        $shops = $this->getLanguageShops($contextService->getShop());

        if (count($shops) === 1) {
            return [];
        }

        $hrefs = [];

        foreach ($shops as $key => $languageShop) {
            $href = new HrefLang();
            $href->setShopId($languageShop['id']);
            $href->setLocale($languageShop['locale']);
            $routingContext = $this->getContext($languageShop['id']);
            $href->setLink($this->filterUrl($this->router->assemble($parameters, $routingContext), $parameters));

            if (!$this->config->get('hrefLangCountry')) {
                $href->setLocale(explode('-', $languageShop['locale'])[0]);
            }

            if ((int) $languageShop['id'] === $this->config->get('hrefLangDefaultShop')) {
                $href->setLocale('x-default');
            }

            if (!$this->isSeoUrl($parameters, $href->getLink(), $routingContext)) {
                continue;
            }

            $hrefs[] = $href;
        }

        if (count($hrefs) === 1) {
            return [];
        }

        return $hrefs;
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    protected function isSeoUrl(array $parameters, $url, Context $context)
    {
        foreach ($this->preFilters as $preFilter) {
            $parameters = $preFilter->preFilter($parameters, $context);
        }

        if (strpos($url, $parameters['controller']) !== false && strpos($url, $parameters['action']) !== false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function filterUrl($url, array $parameters)
    {
        // We don't filter category href links
        if ($this->isCategoryLink($parameters)) {
            return $url;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        if ($query === null) {
            return $url;
        }

        return str_replace('?' . $query, '', $url);
    }

    /**
     * @return bool
     */
    protected function isCategoryLink(array $parameters)
    {
        return (isset($parameters['controller']) && $parameters['controller'] === 'listing')
            || (isset($parameters['controller']) && $parameters['controller'] === 'cat')
            || (isset($parameters['sViewport']) && $parameters['sViewport'] === 'cat')
            || (isset($parameters['sViewport']) && $parameters['sViewport'] === 'listing');
    }

    /**
     * @return array
     */
    private function getLanguageShops(Shop $shop)
    {
        $parentId = $shop->getParentId() ?: $shop->getId();

        return $this->connection->createQueryBuilder()
            ->addSelect('shop.id')
            ->addSelect('REPLACE(locale.locale, "_", "-") as locale')
            ->from('s_core_shops', 'shop')
            ->innerJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id')
            ->where('shop.id = :shopId')
            ->orWhere('shop.main_id = :shopId')
            ->andWhere('active=1')
            ->setParameter('shopId', $parentId)
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $shopId
     *
     * @return Context
     */
    private function getContext($shopId)
    {
        if (!isset($this->contextCache[$shopId])) {
            $shop = $this->modelManager->getRepository(ShopModel::class)->getById($shopId);
            $config = clone $this->config;
            $config->setShop($shop);
            $this->contextCache[$shopId] = Context::createFromShop($shop, $config);
        }

        return $this->contextCache[$shopId];
    }
}
