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

use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class AdditionalTextService implements AdditionalTextServiceInterface
{
    /**
     * @var ConfiguratorServiceInterface
     */
    private $configuratorService;

    public function __construct(ConfiguratorServiceInterface $configuratorService)
    {
        $this->configuratorService = $configuratorService;
    }

    /**
     * {@inheritdoc}
     */
    public function buildAdditionalText(ListProduct $product, ShopContextInterface $context)
    {
        $products = $this->buildAdditionalTextLists([$product], $context);

        return array_shift($products);
    }

    /**
     * {@inheritdoc}
     */
    public function buildAdditionalTextLists($products, ShopContextInterface $context)
    {
        $required = [];
        foreach ($products as &$product) {
            if (!$product->getAdditional()) {
                $required[] = $product;
            }
        }

        if (empty($required)) {
            return $products;
        }

        $configurations = $this->configuratorService->getProductsConfigurations(
            $required,
            $context
        );

        /** @var ListProduct[] $required */
        foreach ($required as &$product) {
            if (!array_key_exists($product->getNumber(), $configurations)) {
                continue;
            }

            $config = $configurations[$product->getNumber()];

            $product->setAdditional($this->buildTextDynamic($config));
        }

        return $products;
    }

    /**
     * @param Group[] $configurations
     *
     * @return string
     */
    private function buildTextDynamic($configurations)
    {
        $text = [];
        foreach ($configurations as $group) {
            foreach ($group->getOptions() as $option) {
                $text[] = $option->getName();
            }
        }

        return implode(' ', $text);
    }
}
