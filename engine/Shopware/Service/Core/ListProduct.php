<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

/**
 * @package Shopware\Service\Core
 */
class ListProduct implements Service\ListProduct
{
    /**
     * @var Gateway\ListProduct
     */
    private $productGateway;

    /**
     * @var Service\Media
     */
    private $mediaService;

    /**
     * @var Service\CheapestPrice
     */
    private $cheapestPriceService;

    /**
     * @var Service\GraduatedPrices
     */
    private $graduatedPricesService;

    /**
     * @var Service\PriceCalculation
     */
    private $priceCalculationService;

    /**
     * @var Service\Marketing
     */
    private $marketingService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param Gateway\ListProduct $productGateway
     * @param Service\GraduatedPrices $graduatedPricesService
     * @param Service\CheapestPrice $cheapestPriceService
     * @param Service\PriceCalculation $priceCalculationService
     * @param Service\Media $mediaService
     * @param Service\Marketing $marketingService
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(
        Gateway\ListProduct $productGateway,
        Service\GraduatedPrices $graduatedPricesService,
        Service\CheapestPrice $cheapestPriceService,
        Service\PriceCalculation $priceCalculationService,
        Service\Media $mediaService,
        Service\Marketing $marketingService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->productGateway = $productGateway;
        $this->graduatedPricesService = $graduatedPricesService;
        $this->cheapestPriceService = $cheapestPriceService;
        $this->priceCalculationService = $priceCalculationService;
        $this->mediaService = $mediaService;
        $this->eventManager = $eventManager;
        $this->marketingService = $marketingService;
    }

    /**
     * @inheritdoc
     */
    public function get($number, Struct\Context $context)
    {
        $products = $this->getList(array($number), $context);

        return array_shift($products);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $numbers, Struct\Context $context)
    {
        $products = $this->productGateway->getList($numbers, $context);

        $covers = $this->mediaService->getCovers($products, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrices = $this->cheapestPriceService->getList($products, $context);

        $result = array();
        foreach ($numbers as $number) {
            if (!array_key_exists($number, $products)) {
                continue;
            }
            $product = $products[$number];

            $product->setCover($covers[$number]);

            $product->setPriceRules($graduatedPrices[$number]);

            $product->setCheapestPriceRule($cheapestPrices[$number]);

            $product->addAttribute(
                'marketing',
                $this->marketingService->getProductAttribute($product)
            );

            $this->priceCalculationService->calculateProduct($product, $context);

            $result[$number] = $product;
        }

        return $result;
    }
}
