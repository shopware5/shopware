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

use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing;
use Shopware\Components\Routing\Router;
use Shopware\Components\SitemapXMLRepository;

class LegacyUrlProvider implements UrlProviderInterface
{
    /**
     * @var SitemapXMLRepository
     */
    private $sitemapXMLRepository;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var bool
     */
    private $allExported = false;

    /**
     * @param Router               $router
     * @param SitemapXMLRepository $sitemapXMLRepository
     */
    public function __construct(Router $router, SitemapXMLRepository $sitemapXMLRepository)
    {
        $this->router = $router;
        $this->sitemapXMLRepository = $sitemapXMLRepository;
    }

    /**
     * @param Routing\Context      $routingContext
     * @param ShopContextInterface $shopContext
     *
     * @return null|Url[]
     */
    public function getUrls(Routing\Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return null;
        }

        $routingParams = $this->sitemapXMLRepository->getSitemapContent($shopContext);

        $urls = [];
        foreach ($routingParams as $area => $urlParams) {
            if (empty($urlParams)) {
                continue;
            }

            $routes = $this->router->generateList(array_map(function (array $param) {
                return $param['urlParams'];
            }, $urlParams), $routingContext);

            for ($i = 0, $routeCount = count($routes); $i < $routeCount; ++$i) {
                $urls[] = new Url($routes[$i], $urlParams[$i]['changed'], 'weekly');
            }
        }

        $this->allExported = true;

        return $urls;
    }
}
