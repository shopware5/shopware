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

namespace Shopware\Bundle\SearchBundle;

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VariantListingPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Option;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VariantSearch implements ProductSearchInterface
{
    /**
     * @var ProductSearchInterface
     */
    private $decorated;

    /**
     * @var VariantListingPriceServiceInterface
     */
    private $listingPriceService;

    /**
     * @var ConfiguratorServiceInterface
     */
    private $configuratorService;

    public function __construct(
        ProductSearchInterface $decorated,
        VariantListingPriceServiceInterface $listingPriceService,
        ConfiguratorServiceInterface $configuratorService
    ) {
        $this->decorated = $decorated;
        $this->listingPriceService = $listingPriceService;
        $this->configuratorService = $configuratorService;
    }

    /**
     * {@inheritdoc}
     */
    public function search(Criteria $criteria, ShopContextInterface $context)
    {
        $result = $this->decorated->search($criteria, $context);
        if (!$criteria->hasConditionOfClass(VariantCondition::class)) {
            return $result;
        }

        $this->listingPriceService->updatePrices($criteria, $result, $context);

        $products = $result->getProducts();
        $configurations = $this->configuratorService->getProductsConfigurations($products, $context);

        $filterGroupIds = array_map(function ($variantCondition) {
            if ($variantCondition->expandVariants()) {
                return $variantCondition->getGroupId();
            }

            return null;
        }, $criteria->getConditionsByClass(VariantCondition::class));

        if (!empty($filterGroupIds)) {
            foreach ($products as $product) {
                if (!\array_key_exists($product->getNumber(), $configurations)) {
                    continue;
                }

                $groups = [];
                foreach ($configurations[$product->getNumber()] as $group) {
                    if (\in_array($group->getId(), $filterGroupIds)) {
                        $tmpGroup = ['groupName' => $group->getName()];
                        $firstOption = $group->getOptions()[0];
                        if ($firstOption instanceof Option) {
                            $tmpGroup['optionName'] = $firstOption->getName();
                        }
                        $groups[] = $tmpGroup;
                    }
                }

                if (!empty($groups)) {
                    $product->addAttribute('swagVariantConfiguration', new Attribute(['value' => $groups]));
                }
            }
        }

        return $result;
    }
}
