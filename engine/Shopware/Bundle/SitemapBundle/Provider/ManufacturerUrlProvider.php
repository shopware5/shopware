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

use Shopware\Bundle\SitemapBundle\Repository\ManufacturerRepositoryInterface;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing;
use Shopware\Models\Article\Supplier as Manufacturer;

class ManufacturerUrlProvider implements UrlProviderInterface
{
    /**
     * @var Routing\RouterInterface
     */
    private $router;

    /**
     * @var ManufacturerRepositoryInterface
     */
    private $repository;

    /**
     * @var bool
     */
    private $allExported;

    public function __construct(ManufacturerRepositoryInterface $repository, Routing\RouterInterface $router)
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
            return [];
        }

        $manufacturers = $this->repository->getManufacturers($shopContext);

        foreach ($manufacturers as &$manufacturer) {
            $manufacturer['changed'] = new \DateTime($manufacturer['changed']);
            $manufacturer['urlParams'] = [
                'sViewport' => 'listing',
                'sAction' => 'manufacturer',
                'sSupplier' => $manufacturer['id'],
            ];
        }

        unset($manufacturer);

        $routes = $this->router->generateList(array_column($manufacturers, 'urlParams'), $routingContext);
        $urls = [];

        for ($i = 0, $routeCount = count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $manufacturers[$i]['changed'], 'weekly', Manufacturer::class, $manufacturers[$i]['id']);
        }

        $this->allExported = true;

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->allExported = false;
    }
}
