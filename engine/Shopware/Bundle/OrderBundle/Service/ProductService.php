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

namespace Shopware\Bundle\OrderBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\PropertyServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VariantCoverServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;

class ProductService implements ProductServiceInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var PropertyServiceInterface
     */
    private $propertyService;

    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * @var VariantCoverServiceInterface
     */
    private $variantCoverService;

    public function __construct(
        ListProductServiceInterface $listProductService,
        PropertyServiceInterface $propertyService,
        LegacyStructConverter $legacyStructConverter,
        VariantCoverServiceInterface $variantCoverService
    ) {
        $this->listProductService = $listProductService;
        $this->propertyService = $propertyService;
        $this->legacyStructConverter = $legacyStructConverter;
        $this->variantCoverService = $variantCoverService;
    }

    public function getList(array $numbers, ShopContextInterface $context): array
    {
        $products = $this->listProductService->getList($numbers, $context);
        $propertySets = $this->propertyService->getList($products, $context);
        $covers = $this->variantCoverService->getList($products, $context);
        $details = [];
        foreach ($products as $product) {
            $arrayProduct = $this->legacyStructConverter->convertListProductStruct($product);

            if ($product->hasConfigurator()) {
                $variantPrice = $product->getVariantPrice();
                $arrayProduct['referenceprice'] = $variantPrice->getCalculatedReferencePrice();
            }

            if (isset($covers[$product->getNumber()])) {
                $arrayProduct['image'] = $this->legacyStructConverter->convertMediaStruct($covers[$product->getNumber()]);
            }

            if ($product->hasProperties() && isset($propertySets[$product->getNumber()])) {
                $propertySet = $propertySets[$product->getNumber()];
                $arrayProduct['sProperties'] = $this->legacyStructConverter->convertPropertySetStruct($propertySet);
                $arrayProduct['filtergroupID'] = $propertySet->getId();
                $arrayProduct['properties'] = array_map(function ($property) {
                    return $property['name'] . ':&nbsp;' . $property['value'];
                }, $arrayProduct['sProperties']);
                $arrayProduct['properties'] = implode(',&nbsp;', $arrayProduct['properties']);
            }

            $details[$product->getNumber()] = $arrayProduct;
        }

        return $details;
    }
}
