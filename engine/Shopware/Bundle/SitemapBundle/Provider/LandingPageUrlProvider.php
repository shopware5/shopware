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

use Shopware\Bundle\SitemapBundle\Repository\LandingPageRepositoryInterface;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing;
use Shopware\Models\Emotion\Emotion;

class LandingPageUrlProvider implements UrlProviderInterface
{
    /**
     * @var LandingPageRepositoryInterface
     */
    protected $repository;

    /**
     * @var Routing\RouterInterface
     */
    protected $router;

    /**
     * @var bool
     */
    protected $allExported = false;

    public function __construct(LandingPageRepositoryInterface $repository, Routing\RouterInterface $router)
    {
        $this->repository = $repository;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->allExported = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Routing\Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return [];
        }

        $campaigns = $this->repository->getLandingPages($shopContext);

        if (count($campaigns) === 0) {
            return [];
        }

        foreach ($campaigns as $key => &$campaign) {
            $campaign['changed'] = $campaign['modified'];
            $campaign['urlParams'] = [
                'sViewport' => 'campaign',
                'emotionId' => $campaign['id'],
            ];
        }
        unset($campaign);

        $campaigns = array_values($campaigns);

        $routes = $this->router->generateList(array_column($campaigns, 'urlParams'), $routingContext);
        $urls = [];

        for ($i = 0, $routeCount = count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $campaigns[$i]['changed'], 'weekly', Emotion::class, $campaigns[$i]['id']);
        }

        $this->allExported = true;

        return $urls;
    }
}
