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

namespace Shopware\Bundle\StoreFrontBundle\AdditionalText;

use Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorGroup;
use Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Product\ListProduct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class AdditionalTextService implements AdditionalTextServiceInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorServiceInterface
     */
    private $configuratorService;

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorServiceInterface $configuratorService
     */
    public function __construct(ConfiguratorServiceInterface $configuratorService)
    {
        $this->configuratorService = $configuratorService;
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

        /** @var $required ListProduct[] */
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
     * @param ConfiguratorGroup[] $configurations
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
