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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\HrefLangServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\HrefLang;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Shop\Shop as ShopModel;
use Shopware_Components_Config as Config;

class HrefLangService implements HrefLangServiceInterface
{
    private Connection $connection;

    private RouterInterface $rewriteRouter;

    private RouterInterface $defaultRouter;

    private Config $config;

    /**
     * @var array<int, Context>
     */
    private array $contextCache = [];

    private ModelManager $modelManager;

    public function __construct(
        Connection $connection,
        RouterInterface $rewriteRouter,
        RouterInterface $defaultRouter,
        Config $config,
        ModelManager $modelManager
    ) {
        $this->connection = $connection;
        $this->rewriteRouter = $rewriteRouter;
        $this->config = $config;
        $this->modelManager = $modelManager;
        $this->defaultRouter = $defaultRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(array $parameters, ShopContextInterface $contextService)
    {
        $shops = $this->getLanguageShops($contextService->getShop());
        $config = clone $this->config;

        if (\count($shops) === 1) {
            return [];
        }

        $hrefs = [];

        foreach ($shops as $languageShop) {
            $shop = $this->getDetachedShop($languageShop['id']);

            $config->setShop($shop);

            $href = new HrefLang();
            $href->setShopId($languageShop['id']);
            $href->setLocale($languageShop['locale']);
            $routingContext = $this->getContext($shop, $config);
            $href->setLink($this->filterUrl((string) $this->rewriteRouter->assemble($parameters, $routingContext), $parameters));

            if (!$config->get('hrefLangCountry')) {
                $href->setLocale(explode('-', $languageShop['locale'])[0]);
            }

            if ((int) $languageShop['id'] === $config->get('hrefLangDefaultShop')) {
                $href->setLocale('x-default');
            }

            if ($this->config->get('hrefLangJustSeoUrl') && !$this->isSeoUrl($parameters, $href->getLink(), $routingContext)) {
                continue;
            }

            $hrefs[] = $href;
        }

        if (\count($hrefs) === 1) {
            return [];
        }

        return $hrefs;
    }

    /**
     * @param array<string, mixed> $parameters
     * @param string               $url
     *
     * @return bool
     */
    protected function isSeoUrl(array $parameters, $url, Context $context)
    {
        if ($this->isUrlHome($parameters)) {
            return true;
        }

        $defaultUrl = $this->defaultRouter->assemble($parameters, $context);

        if ($defaultUrl === $url) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $parameters
     * @param string               $url
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
     * @param array<string, mixed> $parameters
     *
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
     * @return array<array{id: int, locale: string}>
     */
    private function getLanguageShops(Shop $shop): array
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

    private function getContext(ShopModel $shop, Config $config): Context
    {
        if (!isset($this->contextCache[$shop->getId()])) {
            $this->contextCache[$shop->getId()] = Context::createFromShop($shop, $config);
        }

        return $this->contextCache[$shop->getId()];
    }

    private function getDetachedShop(int $languageShopId): ShopModel
    {
        $shopModel = $this->modelManager->getRepository(ShopModel::class)->getById($languageShopId);
        if ($shopModel === null) {
            throw new ModelNotFoundException(ShopModel::class, $languageShopId);
        }

        return $shopModel;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function isUrlHome(array $parameters): bool
    {
        if (!$parameters) {
            return true;
        }

        if (!isset($parameters['controller'], $parameters['action'])) {
            return false;
        }

        if ($parameters['controller'] === 'index' && $parameters['action'] === 'index') {
            return true;
        }

        return false;
    }
}
