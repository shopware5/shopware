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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Shopware\Bundle\PluginInstallerBundle\Context\OrderRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\BasketStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;

class StoreOrderService
{
    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var StructHydrator
     */
    private $hydrator;

    public function __construct(
        StoreClient $storeClient,
        StructHydrator $hydrator
    ) {
        $this->storeClient = $storeClient;
        $this->hydrator = $hydrator;
    }

    /**
     * @return BasketStruct
     */
    public function getCheckout(
        AccessTokenStruct $token,
        OrderRequest $context
    ) {
        $data = [
            'origin' => ['name' => 'Shopware Backend'],
            'shopwareId' => $token->getShopwareId(),
            'positions' => [
                [
                    'licenseShopDomain' => $context->getLicenceShop(),
                    'bookingShopDomain' => $context->getBookingShop(),
                    'orderNumber' => $context->getOrderNumber(),
                    'isArticle' => true,
                    'priceModel' => [
                        'price' => $context->getPrice(),
                        'type' => $context->getPriceType(),
                    ],
                ],
            ],
        ];

        $response = $this->storeClient->doAuthPostRequest(
            $token,
            '/basket',
            $data
        );
        $basket = $this->hydrator->hydrateBasket($response);

        $basket->setLicenceDomain($context->getLicenceShop());

        foreach ($basket->getDomains() as $domain) {
            if ($domain->getDomain() !== $context->getLicenceShop()) {
                continue;
            }

            $basket->setLicenceShopId($domain->getId());
        }

        return $basket;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function orderPlugin(
        AccessTokenStruct $accessToken,
        OrderRequest $context
    ) {
        $data = [
            'origin' => ['name' => 'Shopware Backend'],
            'shopwareId' => $accessToken->getShopwareId(),
            'positions' => [
                [
                    'licenseShopDomain' => $context->getLicenceShop(),
                    'bookingShopDomain' => $context->getBookingShop(),
                    'orderNumber' => $context->getOrderNumber(),
                    'isArticle' => true,
                    'priceModel' => [
                        'type' => $context->getPriceType(),
                        'price' => $context->getPrice(),
                    ],
                ],
            ],
        ];

        $response = $this->storeClient->doAuthPostRequestRaw(
            $accessToken,
            '/orders',
            $data
        );

        return $response->getStatusCode() == 204;
    }
}
