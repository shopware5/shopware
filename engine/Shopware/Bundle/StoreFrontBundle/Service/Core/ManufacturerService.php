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

use Shopware\Bundle\StoreFrontBundle\Gateway\ManufacturerGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\RouterInterface;

class ManufacturerService implements ManufacturerServiceInterface
{
    private ManufacturerGatewayInterface $manufacturerGateway;

    private RouterInterface $router;

    public function __construct(
        ManufacturerGatewayInterface $manufacturerGateway,
        RouterInterface $router
    ) {
        $this->manufacturerGateway = $manufacturerGateway;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, ShopContextInterface $context)
    {
        $manufacturers = $this->getList([$id], $context);

        return array_shift($manufacturers);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $manufacturers = $this->manufacturerGateway->getList($ids, $context);

        // fetch all manufacturer links instead of calling {url ...} smarty function which executes a query for each link
        $links = $this->collectLinks($manufacturers);
        $urls = $this->router->generateList($links);
        foreach ($manufacturers as $manufacturer) {
            if (\array_key_exists($manufacturer->getId(), $urls)) {
                $manufacturer->setLink((string) $urls[$manufacturer->getId()]);
            }
        }

        return $manufacturers;
    }

    /**
     * @param Manufacturer[] $manufacturers
     *
     * @return array<int, array{controller: string, action: string, sSupplier: int}>
     */
    private function collectLinks(array $manufacturers): array
    {
        $links = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerId = $manufacturer->getId();
            $links[$manufacturerId] = [
                'controller' => 'listing',
                'action' => 'manufacturer',
                'sSupplier' => $manufacturerId,
            ];
        }

        return $links;
    }
}
