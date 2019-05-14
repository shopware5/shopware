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

namespace Shopware\Bundle\SitemapBundle\Provider;

use DateTime;
use Shopware\Bundle\SitemapBundle\Repository\StaticUrlRepositoryInterface;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing;

class StaticUrlProvider implements UrlProviderInterface
{
    /**
     * @var Routing\RouterInterface
     */
    private $router;

    /**
     * @var StaticUrlRepositoryInterface
     */
    private $repository;

    /**
     * @var bool
     */
    private $allExported;

    public function __construct(Routing\RouterInterface $router, StaticUrlRepositoryInterface $repository)
    {
        $this->router = $router;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Routing\Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return null;
        }

        $shopId = $shopContext->getShop()->getId();

        $sites = $this->repository->getSitesByShopId($shopId);
        foreach ($sites as $key => &$site) {
            $site['urlParams'] = [
                'sViewport' => 'custom',
                'sCustom' => $site['id'],
            ];

            if (!$this->filterLink($site['link'], $site['urlParams'])) {
                unset($sites[$key]);
                continue;
            }

            $site['changed'] = new DateTime($site['changed']);
        }
        unset($site);

        $sites = array_values($sites);

        $this->allExported = true;

        if (count($sites) === 0) {
            return null;
        }

        $routes = $this->router->generateList(array_column($sites, 'urlParams'), $routingContext);

        $urls = [];

        for ($i = 0, $routeCount = count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $sites[$i]['changed'], 'weekly', \Shopware\Models\Site\Site::class, $sites[$i]['id']);
        }

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->allExported = false;
    }

    /**
     * Helper function to filter predefined links, which should not be in the sitemap (external links, sitemap links itself)
     * Returns false, if the link is not allowed
     *
     * @param string $link
     * @param array  $userParams
     *
     * @return bool
     */
    private function filterLink($link, &$userParams)
    {
        if (empty($link)) {
            return true;
        }
        $userParams = parse_url($link, PHP_URL_QUERY);
        parse_str($userParams, $userParams);
        $blacklist = ['', 'sitemap', 'sitemapXml'];
        if (in_array($userParams['sViewport'], $blacklist)) {
            return false;
        }

        return true;
    }
}
