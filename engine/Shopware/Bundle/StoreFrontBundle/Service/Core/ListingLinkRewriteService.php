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

class ListingLinkRewriteService implements ListingLinkRewriteServiceInterface
{
    /**
     * @var ConfiguratorServiceInterface
     */
    private $configuratorService;

    public function __construct(ConfiguratorServiceInterface $configuratorService)
    {
        $this->configuratorService = $configuratorService;
    }

    public function rewriteLinks(Criteria $criteria, array $articles, ShopContextInterface $context)
    {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);
        $conditions = array_filter($conditions, function (VariantCondition $condition) {
            if (!$condition->expandVariants() && count($condition->getOptionIds()) == 1) {
                return true;
            }

            return $condition->expandVariants();
        });

        $products = array_map(function (array $article) {
            return new BaseProduct($article['articleID'], $article['articleDetailsID'], $article['ordernumber']);
        }, $articles);

        $configurations = [];
        if (!empty($conditions)) {
            $configurations = $this->configuratorService->getProductsConfigurations($products, $context);
        }

        foreach ($articles as &$article) {
            $number = $article['ordernumber'];

            $config = [];
            if (isset($configurations[$number])) {
                $config = $configurations[$number];
            }

            if (!empty($config)) {
                $variantLink = $this->buildListingVariantLink($number, $config, $conditions);
                $article['linkDetails'] .= '&' . $variantLink;

                if (strpos($variantLink, 'number=') !== false) {
                    $article['allowBuyInListing'] = true;
                }
            }
        }

        return $articles;
    }

    private function buildListingVariantLink($number, array $config, array $conditions)
    {
        $notExpandGroupIds = array_map(function (VariantCondition $condition) {
            if (!$condition->expandVariants()) {
                return $condition->getGroupId();
            }
        }, $conditions);

        $groupIds = array_map(function (VariantCondition $condition) {
            return $condition->getGroupId();
        }, $conditions);

        $groupIds = array_diff($groupIds, $notExpandGroupIds);

        $filtered = array_filter($config, function (Group $group) use ($groupIds) {
            return in_array($group->getId(), $groupIds, true);
        });

        $complete = count($config) === count($conditions);
        if (empty($notExpandGroupIds) && $complete) {
            return 'number=' . $number;
        }

        $keys = [];
        /** @var Group $group */
        foreach ($filtered as $group) {
            $keys[] = 'group[' . $group->getId() . ']' . '=' . $group->getOptions()[0]->getId();
        }

        if (!empty($notExpandGroupIds)) {
            /** @var VariantCondition[] $conditions */
            foreach ($conditions as $condition) {
                if (in_array($condition->getGroupId(), $notExpandGroupIds)) {
                    $keys[] = 'group[' . $condition->getGroupId() . ']' . '=' . $condition->getOptionIds()[0];
                }
            }
        }

        return implode('&', $keys);
    }
}
