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

namespace Shopware\Bundle\ContentTypeBundle\FieldResolver;

use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;

class ShopResolver extends AbstractResolver
{
    /**
     * @var ShopGatewayInterface
     */
    private $shopGateway;

    public function __construct(ShopGatewayInterface $shopGateway)
    {
        $this->shopGateway = $shopGateway;
    }

    public function resolve(): void
    {
        if (!empty($this->resolveIds)) {
            $shops = $this->shopGateway->getList($this->resolveIds);

            foreach ($shops as $id => $shop) {
                $this->storage[$id] = $shop->jsonSerialize();
            }

            $this->resolveIds = [];
        }
    }
}
