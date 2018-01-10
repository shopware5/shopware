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

namespace Shopware\Bundle\ESIndexingBundle\Product;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\Struct\Product;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\SearchBundleDBAL\ListingPriceHelper;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\PriceCalculatorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Option;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductListingVariationLoader
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PriceCalculatorInterface
     */
    private $calculator;

    /**
     * @var IdentifierSelector
     */
    private $identifierSelector;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ListingPriceHelper
     */
    private $listingPriceHelper;

    public function __construct(
        Connection $connection,
        PriceCalculatorInterface $calculationService,
        IdentifierSelector $identifierSelector,
        ContextServiceInterface $contextService,
        ListingPriceHelper $listingPriceHelper
    ) {
        $this->connection = $connection;
        $this->calculator = $calculationService;
        $this->identifierSelector = $identifierSelector;
        $this->contextService = $contextService;
        $this->listingPriceHelper = $listingPriceHelper;
    }

    /**
     * @param Shop          $shop
     * @param ListProduct[] $products
     * @param array         $configurations
     *
     * @return array
     */
    public function getListingPrices(
        Shop $shop,
        array $products,
        array $configurations
    ) {
        $combinationPrices = [];

        $contexts = $this->getCustomerGroupContexts($shop);

        /** @var ShopContextInterface $context */
        foreach ($contexts as $context) {
            $prices = $this->fetchPrices($products, $context);
            $key = $context->getCurrentCustomerGroup()->getKey();

            foreach ($products as $product) {
                if (!isset($configurations[$product->getNumber()])) {
                    continue;
                }

                $configuration = $configurations[$product->getNumber()];
                $groups = array_map(function (Group $group) {
                    return $group->getId();
                }, $configuration);

                $combinations = $this->arrayCombinations($groups);

                $combinationPrices[$key][$product->getNumber()] = $this->getCombinationPrices(
                    $configuration,
                    $prices[$product->getId()],
                    $combinations
                );
            }
        }

        $contexts = $this->getPriceContexts($shop);

        $calculated = [];
        foreach ($contexts as $context) {
            if (!array_key_exists($context->getCurrentCustomerGroup()->getKey(), $combinationPrices)) {
                continue;
            }
            $customerPrices = $combinationPrices[$context->getCurrentCustomerGroup()->getKey()];

            $key = $context->getCurrentCustomerGroup()->getKey() . '_' . $context->getCurrency()->getId();

            /** @var array[] $customerPrices */
            foreach ($customerPrices as $number => $productPrices) {
                $product = $products[$number];
                foreach ($productPrices as &$price) {
                    $price = $this->calculator->calculatePrice($price, $product->getTax(), $context);
                }

                $calculated[$number][$key] = $productPrices;
            }
        }

        return $calculated;
    }

    /**
     * Builds the visibility for the variant listings
     *
     * @param Product      $product
     * @param VariantFacet $facet
     *
     * @return array
     */
    public function getVisibility(Product $product, VariantFacet $facet)
    {
        $groups = $product->getFullConfiguration();

        $splitting = $this->createSplitting($groups, $product->getAvailableCombinations(), $facet);

        $configuration = $product->getConfiguration();

        return $this->buildListingVisibility($splitting, $configuration);
    }

    /**
     * Combines all array elements with all array elements
     *
     * @param array $array
     *
     * @return array
     */
    private function arrayCombinations(array $array)
    {
        $results = [[]];

        foreach ($array as $element) {
            foreach ($results as $combination) {
                array_push($results, array_merge([$element], $combination));
            }
        }

        return array_filter($results);
    }

    private function createSplitting(array $groups, array $availability, VariantFacet $facet)
    {
        $consider = array_filter($groups, function (Group $group) use ($facet) {
            return in_array($group->getId(), $facet->getExpandGroupIds(), true);
        });

        $c = $this->arrayCombinations(array_keys($consider));

        //flip keys for later intersection
        $keys = array_flip(array_keys($consider));

        $result = [];
        foreach ($c as $combination) {
            //flip combination to use key intersect
            $combination = array_flip($combination);

            //all options of groups will be combined together
            $full = array_intersect_key($groups, $combination);

            $first = array_intersect_key($groups, array_diff_key($keys, $combination));

            usort($full, function (Group $a, Group $b) {
                return $a->getId() > $b->getId();
            });

            //create unique group key
            $groupKey = array_map(function (Group $group) {
                return $group->getId();
            }, $full);
            $groupKey = 'g' . implode('-', $groupKey);

            $all = array_filter(array_merge($full, $first));

            $firstIds = array_map(function (Group $group) {
                return $group->getId();
            }, $first);

            $fullIds = array_map(function (Group $group) {
                return $group->getId();
            }, $full);

            foreach ($groups as $group) {
                if (in_array($group->getId(), $fullIds, true)) {
                    continue;
                }
                if (in_array($group->getId(), $firstIds, true)) {
                    continue;
                }
                $firstIds[] = $group->getId();
                $all[] = $group;
            }

            $result[$groupKey] = $this->nestedArrayCombinations($all, $firstIds, $availability);
        }

        return $result;
    }

    /**
     * Builds all possible combinations of an nested array
     *
     * @param array   $groups
     * @param Group[] $onlyFirst
     * @param array   $availability
     *
     * @return array
     */
    private function nestedArrayCombinations(array $groups, array $onlyFirst, array $availability)
    {
        $result = [[]];

        $groups = array_values($groups);

        /** @var Group $group */
        foreach ($groups as $index => $group) {
            //check if options of this group only be combined with the first element
            $isFirst = in_array($group->getId(), $onlyFirst, true);
            $new = [];

            foreach ($result as $item) {
                $options = array_values($group->getOptions());

                //sort by ids ascending - forces always same order
                usort($options, function (Option $a, Option $b) {
                    return $a->getId() > $b->getId();
                });

                /** @var Option $option */
                foreach ($options as $option) {
                    $tmp = array_merge($item, [$index => (int) $option->getId()]);
                    sort($tmp, SORT_NUMERIC);

                    //check if this combination is a available combination (out of stock, not active)
                    $isAvailable = false;
                    foreach ($availability as $available) {
                        $available = '-' . $available . '-';

                        $allMatch = true;
                        foreach ($tmp as $key) {
                            if (strpos($available, '-' . $key . '-') === false) {
                                $allMatch = false;
                            }
                        }
                        //all options matched? combination is available, break availability check
                        if ($allMatch) {
                            $isAvailable = true;
                            break;
                        }
                    }

                    if (!$isAvailable) {
                        continue;
                    }

                    $new[] = $tmp;

                    //in case that options of this group should only combined with the first element, break combination loop
                    if ($isFirst) {
                        break;
                    }
                }
            }

            if (empty($new)) {
                continue;
            }

            $result = $new;
        }

        foreach ($result as &$toImplode) {
            $toImplode = implode('-', $toImplode);
        }

        return $result;
    }

    private function fetchPrices(array $products, ShopContextInterface $context)
    {
        $ids = array_map(function (ListProduct $product) {
            return $product->getId();
        }, $products);

        $priceTable = $this->listingPriceHelper->getPriceTable($context);

        $query = $this->connection->createQueryBuilder();
        $query->addSelect([
            'prices.articleID',
            'relations.article_id as variant_id',
            'prices.price',
            'GROUP_CONCAT(DISTINCT relations.option_id ORDER BY relations.option_id) as `options`',
            'GROUP_CONCAT(DISTINCT options.name ORDER BY options.id) as `optionNames`',
            'GROUP_CONCAT(DISTINCT options.group_id ORDER BY options.group_id) as `groups`',
        ]);

        $query->from('s_articles_details', 'variant');
        $query->innerJoin('variant', '(' . $priceTable . ')', 'prices', 'variant.id = prices.articledetailsID');
        $query->innerJoin('prices', 's_article_configurator_option_relations', 'relations', 'relations.article_id = prices.articledetailsID');
        $query->innerJoin('relations', 's_article_configurator_options', 'options', 'relations.option_id = options.id');

        $query->andWhere('variant.laststock * variant.instock >= variant.laststock * variant.minpurchase');
        $query->andWhere('variant.active = 1');
        $query->andWhere('prices.to = :to');
        $query->andWhere('prices.articleID IN (:products)');
        $query->groupBy('prices.articledetailsID');

        $query->setParameter('to', 'beliebig');
        $query->setParameter('products', $ids, Connection::PARAM_INT_ARRAY);

        $query->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey());
        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        if ($context->getCurrentCustomerGroup()->getId() !== $context->getFallbackCustomerGroup()->getId()) {
            $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey());
        }

        $prices = $query->execute()->fetchAll(\PDO::FETCH_GROUP);

        /** @var array[] $prices */
        foreach ($prices as &$productPrices) {
            foreach ($productPrices as &$price) {
                $price['options'] = array_filter(explode(',', $price['options']));
                $price['options'] = array_map('intval', $price['options']);
                $price['groups'] = array_filter(explode(',', $price['groups']));
                $price['groups'] = array_map('intval', $price['groups']);
            }
        }

        return $prices;
    }

    private function buildListingVisibility(array $splitting, array $configuration)
    {
        $key = [];

        usort($configuration, function (Group $a, Group $b) {
            return $a->getId() > $b->getId();
        });

        /** @var Group $group */
        foreach ($configuration as $group) {
            foreach ($group->getOptions() as $option) {
                $key[] = $option->getId();
            }
        }
        sort($key, SORT_NUMERIC);
        $key = implode('-', $key);

        $visibility = [];

        foreach ($splitting as $combination => $variants) {
            $visibility[$combination] = in_array($key, $variants);
        }

        return $visibility;
    }

    private function getCombinationPrices(array $configuration, array $prices, array $combinations)
    {
        $cheapestPrices = [];

        $options = [];
        foreach ($configuration as $group) {
            $options[$group->getId()] = $group->getOptions()[0]->getId();
        }

        //combinations contains all group combinations ('size', 'color', 'size+color')
        foreach ($combinations as $combination) {
            sort($combination, SORT_NUMERIC);

            //now check which option ids are affected by the current combination
            // size combination => only consider prices with same size
            // size + color combination => only consider prices with same size and color like the current product

            $tmp = array_values(array_keys(array_intersect(array_flip($options), $combination)));
            sort($tmp, SORT_NUMERIC);

            //filter prices which has configuration matches the current variant configuration
            $affected = array_filter($prices, function (array $price) use ($tmp) {
                $diff = array_intersect($price['options'], $tmp);
                $diff = array_values($diff);

                return $diff === $tmp;
            });

            $price = array_column($affected, 'price');

            //build combination key by group ids
            //store front filters to filtered group "sort by price for `color`"
            $key = 'g' . implode('-', $combination);

            $cheapestPrices[$key] = min($price);
        }

        return $cheapestPrices;
    }

    /**
     * @param Shop $shop
     *
     * @return array
     */
    private function getPriceContexts(Shop $shop)
    {
        $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getId());
        if (!$shop->isMain()) {
            $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getParentId());
        }

        $customerGroups = $this->identifierSelector->getCustomerGroupKeys();

        return $this->getContexts($shop->getId(), $customerGroups, $currencies);
    }

    private function getCustomerGroupContexts(Shop $shop)
    {
        $customerGroups = $this->identifierSelector->getCustomerGroupKeys();

        return $this->getContexts($shop->getId(), $customerGroups, [$shop->getCurrency()->getId()]);
    }

    /**
     * @param int      $shopId
     * @param string[] $customerGroups
     * @param int[]    $currencies
     *
     * @return array
     */
    private function getContexts($shopId, $customerGroups, $currencies)
    {
        $contexts = [];
        foreach ($customerGroups as $customerGroup) {
            foreach ($currencies as $currency) {
                $contexts[] = $this->contextService->createShopContext($shopId, $currency, $customerGroup);
            }
        }

        return $contexts;
    }
}
