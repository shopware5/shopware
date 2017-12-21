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
use Shopware\Bundle\SearchBundleDBAL\VariantHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\ConfiguratorOptionsGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\PropertyHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\ListProductGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Service\PriceCalculationServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VoteServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\GroupsByGroup;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductProvider implements ProductProviderInterface
{
    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ListProductGatewayInterface
     */
    private $productGateway;

    /**
     * @var CheapestPriceServiceInterface
     */
    private $cheapestPriceService;

    /**
     * @var VoteServiceInterface
     */
    private $voteService;

    /**
     * @var IdentifierSelector
     */
    private $identifierSelector;

    /**
     * @var PriceCalculationServiceInterface
     */
    private $priceCalculationService;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var PropertyHydrator
     */
    private $propertyHydrator;

    /**
     * @var ConfiguratorServiceInterface
     */
    private $configuratorService;

    /**
     * @var VariantHelper
     */
    private $variantHelper;
    /**
     * @var ConfiguratorOptionsGateway
     */
    private $configuratorOptionsGateway;

    /**
     * @param ListProductGatewayInterface      $productGateway
     * @param CheapestPriceServiceInterface    $cheapestPriceService
     * @param VoteServiceInterface             $voteService
     * @param ContextServiceInterface          $contextService
     * @param Connection                       $connection
     * @param IdentifierSelector               $identifierSelector
     * @param PriceCalculationServiceInterface $priceCalculationService
     * @param FieldHelper                      $fieldHelper
     * @param PropertyHydrator                 $propertyHydrator
     * @param ConfiguratorServiceInterface     $configuratorService
     * @param VariantHelper                    $variantHelper
     * @param ConfiguratorOptionsGateway       $configuratorOptionsGateway
     */
    public function __construct(
        ListProductGatewayInterface $productGateway,
        CheapestPriceServiceInterface $cheapestPriceService,
        VoteServiceInterface $voteService,
        ContextServiceInterface $contextService,
        Connection $connection,
        IdentifierSelector $identifierSelector,
        PriceCalculationServiceInterface $priceCalculationService,
        FieldHelper $fieldHelper,
        PropertyHydrator $propertyHydrator,
        ConfiguratorServiceInterface $configuratorService,
        VariantHelper $variantHelper,
        ConfiguratorOptionsGateway $configuratorOptionsGateway
    ) {
        $this->productGateway = $productGateway;
        $this->cheapestPriceService = $cheapestPriceService;
        $this->voteService = $voteService;
        $this->contextService = $contextService;
        $this->connection = $connection;
        $this->identifierSelector = $identifierSelector;
        $this->priceCalculationService = $priceCalculationService;
        $this->fieldHelper = $fieldHelper;
        $this->propertyHydrator = $propertyHydrator;
        $this->configuratorService = $configuratorService;
        $this->variantHelper = $variantHelper;
        $this->configuratorOptionsGateway = $configuratorOptionsGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Shop $shop, $numbers, $groupByResult)
    {
        $context = $this->contextService->createShopContext(
            $shop->getId(),
            null,
            ContextService::FALLBACK_CUSTOMER_GROUP
        );

        $products = $this->productGateway->getList($numbers, $context);
        $average = $this->voteService->getAverages($products, $context);
        $cheapest = $this->getCheapestPrices($products, $shop->getId());
        $calculated = $this->getCalculatedPrices($shop, $products, $cheapest);
        $categories = $this->getCategories($products);
        $properties = $this->getProperties($products, $context);

        /**
         * @var VariantFacet
         */
        $variantFacet = $this->variantHelper->getVariantFacet();
        if (!empty($variantFacet)) {
            $configurations = $this->configuratorService->getVariantGroups($numbers, $context);
            $productConfigurations = $this->configuratorService->getProductsConfigurations($products, $context);
        }

        $result = [];
        foreach ($products as $listProduct) {
            $product = Product::createFromListProduct($listProduct);
            $number = $product->getNumber();
            $id = $product->getId();

            if (!empty($variantFacet) && !empty($productConfigurations[$product->getNumber()])) {
                $product->setConfiguration($productConfigurations[$product->getNumber()]);

                if (array_key_exists($product->getId(), $configurations)) {
                    $groups = $product->getConfiguration();
                    foreach ($groups as $key => $group) {
                        if (!in_array($group->getId(), $variantFacet->getExpandGroupIds())) {
                            unset($groups[$key]);
                        }
                    }

                    $groups = array_merge($groups, $configurations[$product->getId()]);

                    $product->setConfiguration($groups);
                }

                /**
                 * @var Group[]
                 */
                $productGroups = array_map(function ($group) {
                    /*
                     * @var $group Group
                     */
                    return $group->getId();
                }, $productConfigurations[$product->getNumber()]);
                $variantGroups = $this->configuratorOptionsGateway->getOptionsByGroups($productGroups);
                $combinations = $this->createGroupBy($variantGroups, $variantFacet->getExpandGroupIds());
                $filterInGroups = $this->getFilterGroups($productConfigurations[$product->getNumber()], $combinations);
                $product->setGroupByGroups($filterInGroups);
            }

            if (isset($average[$number])) {
                $product->setVoteAverage($average[$number]);
            }
            if (isset($calculated[$number])) {
                $product->setCalculatedPrices($calculated[$number]);
            }
            if (isset($categories[$id])) {
                $product->setCategoryIds($categories[$id]);
            }
            if (isset($properties[$id])) {
                $product->setProperties($properties[$id]);
            }

            $product->setFormattedCreatedAt(
                $this->formatDate($product->getCreatedAt())
            );
            $product->setFormattedReleaseDate(
                $this->formatDate($product->getReleaseDate())
            );

            $product->setCreatedAt(null);
            $product->setReleaseDate(null);
            $product->setPrices(null);
            $product->setPriceRules(null);
            $product->setCheapestPriceRule(null);
            $product->setCheapestPrice(null);
            $product->setCheapestUnitPrice(null);
            $product->resetStates();

            if (!$this->isValid($shop, $product)) {
                continue;
            }
            $result[$number] = $product;
        }

        return $result;
    }

    /**
     * @param Group[] $groups
     * @param int[]   $expandGroups
     *
     * @return string[]
     */
    public function createGroupBy(array $groups, array $expandGroups)
    {
        $combination = [];
        $baseGroups = $groups;

        foreach ($baseGroups as $baseGroup) {
            $maxDeep = count($groups);
            $currentDeep = 1;
            $iterationBaseGroups = [$baseGroup];

            //Iteration of deep 1
            $group = $groups[0];
            if (in_array($baseGroup->getId(), $expandGroups) && $baseGroup->getId() == $group->getId()) {
                foreach ($group->getOptions() as $option) {
                    $optionString = $option->getId();
                    $combination = $this->recursiveCreateGroupBy($iterationBaseGroups, $groups, $expandGroups, $combination, $optionString);
                }
            } else {
                $option = $group->getOptions()[0];
                $optionString = $option->getId();
                $combination = $this->recursiveCreateGroupBy($iterationBaseGroups, $groups, $expandGroups, $combination, $optionString);
            }

            //Iteration of the other deeps
            $combination = $this->recursiveBaseGroupBy($iterationBaseGroups, $groups, $expandGroups, $currentDeep, $maxDeep, $combination);
        }

        return $combination;
    }

    /**
     * @param Group[] $productConfigurations
     * @param array   $combinations
     *
     * @return array
     */
    private function getFilterGroups($productConfigurations, $combinations)
    {
        $ids = array_map(function ($group) {
            /*
             * @var $group Group
             */
            return $group->getOptions()[0]->getId();
        }, $productConfigurations);
        $optionKey = implode('-', $ids);

        $visibleInGroups = [];
        foreach ($combinations as $key => $options) {
            $visibleInGroups[] = new GroupsByGroup($key, in_array($optionKey, $options));
        }

        return $visibleInGroups;
    }

    /**
     * @param Group[] $iterationBaseGroups
     * @param Group[] $groups
     * @param int[]   $expandGroups
     * @param int     $currentDeep
     * @param int     $maxDeep
     * @param array   $combination
     *
     * @return array
     */
    private function recursiveBaseGroupBy($iterationBaseGroups, $groups, $expandGroups, $currentDeep, $maxDeep, $combination)
    {
        if ($currentDeep > $maxDeep) {
            return $combination;
        }

        $nextIterationGroup = null;
        foreach ($groups as $group) {
            if (!in_array($group, $iterationBaseGroups)) {
                $_iterationBaseGroups = $iterationBaseGroups;
                $_iterationBaseGroups[] = $group;

                $group = $groups[0];
                if (in_array($group->getId(), $expandGroups) && in_array($group, $_iterationBaseGroups)) {
                    foreach ($group->getOptions() as $option) {
                        $optionString = $option->getId();
                        $combination = $this->recursiveCreateGroupBy($_iterationBaseGroups, $groups, $expandGroups, $combination, $optionString);
                    }
                } else {
                    $option = $group->getOptions()[0];
                    $optionString = $option->getId();
                    $combination = $this->recursiveCreateGroupBy($_iterationBaseGroups, $groups, $expandGroups, $combination, $optionString);
                }

                ++$currentDeep;
                $combination = $this->recursiveBaseGroupBy($_iterationBaseGroups, $groups, $expandGroups, $currentDeep, $maxDeep, $combination);
            }
        }

        return $combination;
    }

    /**
     * @param Group[]  $baseGroups
     * @param Group[]  $groups
     * @param int[]    $expandGroups
     * @param string[] $combination
     * @param string   $currentOptionString
     *
     * @return array
     */
    private function recursiveCreateGroupBy($baseGroups, $groups, $expandGroups, $combination, $currentOptionString)
    {
        $_groups = $groups;
        array_shift($_groups);

        if (count($_groups) == 0) {
            if (count($baseGroups) == 1) {
                $combinationKey = $baseGroups[0]->getId();
            } else {
                usort($baseGroups, function ($groupA, $groupB) {
                    /*
                     * @var $groupA Group
                     * @var $groupB Group
                     */
                    return strcmp($groupA->getId(), $groupB->getId());
                });

                $ids = array_map(function ($group) {
                    /*
                     * @var $group Group
                     */
                    return $group->getId();
                }, $baseGroups);
                $combinationKey = implode('-', $ids);
            }

            $combination[$combinationKey][] = $currentOptionString;

            return $combination;
        }

        $group = $_groups[0];
        if (in_array($group->getId(), $expandGroups) && in_array($group, $baseGroups)) {
            foreach ($group->getOptions() as $option) {
                $optionString = $currentOptionString . '-' . $option->getId();
                $combination = $this->recursiveCreateGroupBy($baseGroups, $_groups, $expandGroups, $combination, $optionString);
            }
        } else {
            $option = $group->getOptions()[0];
            $optionString = $currentOptionString . '-' . $option->getId();
            $combination = $this->recursiveCreateGroupBy($baseGroups, $_groups, $expandGroups, $combination, $optionString);
        }

        return $combination;
    }

    /**
     * @param \DateTime|null $date
     *
     * @return null|string
     */
    private function formatDate(\DateTime $date = null)
    {
        return !$date ? null : $date->format('Y-m-d');
    }

    /**
     * @param ListProduct[] $products
     *
     * @return array[]
     */
    private function getCategories($products)
    {
        $ids = array_map(function (BaseProduct $product) {
            return (int) $product->getId();
        }, $products);

        $query = $this->connection->createQueryBuilder();
        $query->select(['mapping.articleID', 'categories.id', 'categories.path'])
            ->from('s_articles_categories', 'mapping')
            ->innerJoin('mapping', 's_categories', 'categories', 'categories.id = mapping.categoryID')
            ->where('mapping.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $articleId = (int) $row['articleID'];
            if (isset($result[$articleId])) {
                $categories = $result[$articleId];
            } else {
                $categories = [];
            }
            $temp = explode('|', $row['path']);
            $temp[] = $row['id'];

            $result[$articleId] = array_merge($categories, $temp);
        }

        return array_map(function ($row) {
            return array_values(array_unique(array_filter($row)));
        }, $result);
    }

    /**
     * @param ListProduct[]        $products
     * @param ShopContextInterface $context
     *
     * @return \array[]
     */
    private function getProperties($products, ShopContextInterface $context)
    {
        $ids = array_map(function (ListProduct $product) {
            return $product->getId();
        }, $products);

        $query = $this->connection->createQueryBuilder();

        $query
            ->addSelect('filterArticles.articleID as productId')
            ->addSelect($this->fieldHelper->getPropertyOptionFields())
            ->addSelect($this->fieldHelper->getMediaFields())
            ->from('s_filter_articles', 'filterArticles')
            ->innerJoin('filterArticles', 's_filter_values', 'propertyOption', 'propertyOption.id = filterArticles.valueID')
            ->leftJoin('propertyOption', 's_media', 'media', 'propertyOption.media_id = media.id')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('propertyOption', 's_filter_values_attributes', 'propertyOptionAttribute', 'propertyOptionAttribute.valueID = propertyOption.id')
            ->where('filterArticles.articleID IN (:ids)')
            ->addOrderBy('filterArticles.articleID')
            ->addOrderBy('propertyOption.value')
            ->addOrderBy('propertyOption.id')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addPropertyOptionTranslation($query, $context);
        $this->fieldHelper->addMediaTranslation($query, $context);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);
        $properties = [];

        $hydrator = $this->propertyHydrator;
        foreach ($data as $productId => $values) {
            $options = array_map(function ($row) use ($hydrator) {
                return $hydrator->hydrateOption($row);
            }, $values);
            $properties[$productId] = $options;
        }

        return $properties;
    }

    /**
     * @param ListProduct[] $products
     * @param int           $shopId
     *
     * @return array[]
     */
    private function getCheapestPrices($products, $shopId)
    {
        $keys = $this->identifierSelector->getCustomerGroupKeys();
        $prices = [];
        foreach ($keys as $key) {
            $context = $this->contextService->createShopContext($shopId, null, $key);
            $customerPrices = $this->cheapestPriceService->getList($products, $context);
            foreach ($customerPrices as $number => $price) {
                $prices[$number][$key] = $price;
            }
        }

        return $prices;
    }

    /**
     * @param Shop          $shop
     * @param ListProduct[] $products
     * @param $priceRules
     *
     * @return array
     */
    private function getCalculatedPrices($shop, $products, $priceRules)
    {
        $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getId());
        if (!$shop->isMain()) {
            $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getParentId());
        }

        $customerGroups = $this->identifierSelector->getCustomerGroupKeys();
        $contexts = $this->getContexts($shop->getId(), $customerGroups, $currencies);

        $prices = [];
        foreach ($products as $product) {
            $number = $product->getNumber();
            if (!isset($priceRules[$number])) {
                continue;
            }
            $rules = $priceRules[$number];

            /** @var $context ProductContextInterface */
            foreach ($contexts as $context) {
                $customerGroup = $context->getCurrentCustomerGroup()->getKey();
                $key = $customerGroup . '_' . $context->getCurrency()->getId();

                $rule = $rules[$context->getFallbackCustomerGroup()->getKey()];
                if (isset($rules[$customerGroup])) {
                    $rule = $rules[$customerGroup];
                }

                /* @var PriceRule $rule */
                $product->setCheapestPriceRule($rule);
                $this->priceCalculationService->calculateProduct($product, $context);

                if ($product->getCheapestPrice()) {
                    $prices[$number][$key] = $product->getCheapestPrice();
                }
            }
        }

        return $prices;
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

    /**
     * @param Shop    $shop
     * @param Product $product
     *
     * @return bool
     */
    private function isValid(Shop $shop, $product)
    {
        $valid = in_array($shop->getCategory()->getId(), $product->getCategoryIds());
        if (!$valid) {
            return false;
        }

        return true;
    }
}
