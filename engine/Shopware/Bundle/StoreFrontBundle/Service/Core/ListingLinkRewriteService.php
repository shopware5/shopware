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

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListingLinkRewriteServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\RouterInterface;

class ListingLinkRewriteService implements ListingLinkRewriteServiceInterface
{
    private ConfiguratorServiceInterface $configuratorService;

    private RouterInterface $router;

    public function __construct(ConfiguratorServiceInterface $configuratorService, RouterInterface $router)
    {
        $this->configuratorService = $configuratorService;
        $this->router = $router;
    }

    /**
     * @param array<string, mixed> $articles
     * @param int|null             $categoryId
     *
     * @return array<string, mixed>
     */
    public function rewriteLinks(
        Criteria $criteria,
        array $articles,
        ShopContextInterface $context,
        $categoryId = null
    ) {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);
        $conditions = array_filter($conditions, function (VariantCondition $condition) {
            return $condition->expandVariants();
        });

        $products = array_map(function (array $product) {
            return new BaseProduct($product['articleID'], $product['articleDetailsID'], $product['ordernumber']);
        }, $articles);

        $configurations = [];
        if (!empty($conditions)) {
            $configurations = $this->configuratorService->getProductsConfigurations($products, $context);
        }

        $urls = array_map(function ($product) use ($categoryId) {
            if ($categoryId !== null) {
                return $product['linkDetails'] . '&sCategory=' . (int) $categoryId;
            }

            return $product['linkDetails'];
        }, $articles);

        $rewrite = $this->router->generateList($urls);

        foreach ($articles as $key => &$product) {
            if (!\array_key_exists($key, $rewrite)) {
                continue;
            }
            $product['linkDetails'] = $rewrite[$key];
        }
        unset($product);

        foreach ($articles as &$product) {
            $number = $product['ordernumber'];

            $config = $configurations[$number] ?? [];

            if (!empty($config)) {
                $variantLink = $this->buildListingVariantLink($number, $config, $conditions);

                if (str_contains($product['linkDetails'], '?')) {
                    $product['linkDetails'] .= '&' . $variantLink;
                } else {
                    $product['linkDetails'] .= '?' . $variantLink;
                }
            }
        }

        return $articles;
    }

    /**
     * @param array<Group>            $config
     * @param array<VariantCondition> $conditions
     */
    private function buildListingVariantLink(string $number, array $config, array $conditions): string
    {
        $groupIds = array_map(function (VariantCondition $condition) {
            return $condition->getGroupId();
        }, $conditions);

        $filtered = array_filter($config, function (Group $group) use ($groupIds) {
            return \in_array($group->getId(), $groupIds, true);
        });

        if (\count($config) === \count($filtered)) {
            return 'number=' . $number;
        }

        $keys = [];
        foreach ($filtered as $group) {
            $keys['group'][$group->getId()] = $group->getOptions()[0]->getId();
        }

        return http_build_query($keys);
    }
}
