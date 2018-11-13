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
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
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
     * @var VariantHelperInterface
     */
    private $variantHelper;

    /**
     * @var ProductListingVariationLoader
     */
    private $listingVariationLoader;

    /**
     * @var ProductConfigurationLoader
     */
    private $configurationLoader;

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
        VariantHelperInterface $variantHelper,
        ProductConfigurationLoader $configurationLoader,
        ProductListingVariationLoader $visibilityLoader
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
        $this->configurationLoader = $configurationLoader;
        $this->listingVariationLoader = $visibilityLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Shop $shop, $numbers)
    {
        $context = $this->contextService->createShopContext(
            $shop->getId(),
            null,
            ContextService::FALLBACK_CUSTOMER_GROUP
        );

        $availability = null;
        $listingPrices = null;
        $combinations = null;
        $configurations = null;
        $variantConfiguration = null;
        $products = $this->productGateway->getList($numbers, $context);
        $average = $this->voteService->getAverages($products, $context);
        $cheapest = $this->getCheapestPrices($products, $shop->getId());
        $calculated = $this->getCalculatedPrices($shop, $products, $cheapest);
        $categories = $this->getCategories($products);
        $properties = $this->getProperties($products, $context);

        $variantFacet = $this->variantHelper->getVariantFacet();

        if ($variantFacet) {
            $variantConfiguration = $this->configuratorService->getProductsConfigurations($products, $context);

            $articleIds = array_map(
                function (ListProduct $product) {
                    return $product->getId();
                }, $products);

            $configurations = $this->configurationLoader->getConfigurations($articleIds, $context);

            $combinations = $this->configurationLoader->getCombinations($articleIds);

            $listingPrices = $this->listingVariationLoader->getListingPrices($shop, $products, $variantConfiguration, $variantFacet);

            $availability = $this->listingVariationLoader->getAvailability($products, $variantConfiguration, $variantFacet);
        }

        $result = [];
        foreach ($products as $listProduct) {
            $product = Product::createFromListProduct($listProduct);
            $number = $product->getNumber();
            $id = $product->getId();

            if ($variantFacet) {
                if (array_key_exists($number, $variantConfiguration)) {
                    $product->setConfiguration($variantConfiguration[$number]);
                }
                if (array_key_exists($id, $configurations)) {
                    $product->setFullConfiguration($configurations[$id]);
                }
                if (array_key_exists($id, $combinations)) {
                    $product->setAvailableCombinations($combinations[$id]);
                }

                if ($product->getConfiguration()) {
                    $product->setVisibility(
                        $this->listingVariationLoader->getVisibility($product, $variantFacet)
                    );

                    $product->setFilterConfiguration(
                        $this->buildFilterConfiguration(
                            $variantFacet->getExpandGroupIds(),
                            $product->getConfiguration(),
                            $product->getFullConfiguration()
                        )
                    );

                    if (array_key_exists($product->getNumber(), $listingPrices)) {
                        $product->setListingVariationPrices(
                            $listingPrices[$product->getNumber()]
                        );
                    }

                    if (array_key_exists($number, $availability)) {
                        $product->setAvailability($availability[$number]);
                    }
                }
            } else {
                if (!$product->isMainVariant()) {
                    continue;
                }
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
            $product->setFormattedUpdatedAt(
                $this->formatDate($product->getUpdatedAt())
            );
            $product->setFormattedReleaseDate(
                $this->formatDate($product->getReleaseDate())
            );

            $product->setCreatedAt(null);
            $product->setUpdatedAt(null);
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
            $categories = [];
            if (isset($result[$articleId])) {
                $categories = $result[$articleId];
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
     * @return array[]
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

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
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
     * @param array         $priceRules
     *
     * @return array
     */
    private function getCalculatedPrices($shop, $products, $priceRules)
    {
        $contexts = $this->getPriceContexts($shop);

        $prices = [];
        foreach ($products as $product) {
            $number = $product->getNumber();
            if (!isset($priceRules[$number])) {
                continue;
            }
            $rules = $priceRules[$number];

            /** @var ProductContextInterface $context */
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

    /**
     * @param int[]   $expandGroupIds
     * @param Group[] $configurations
     * @param Group[] $fullConfiguration
     *
     * @return array
     */
    private function buildFilterConfiguration(array $expandGroupIds, array $configurations, array $fullConfiguration)
    {
        $merged = [];
        foreach ($configurations as $config) {
            if (in_array($config->getId(), $expandGroupIds, true)) {
                $merged[] = $config;
                continue;
            }
            $merged[] = $this->getFullConfigurationGroup($config->getId(), $fullConfiguration);
        }

        return $merged;
    }

    /**
     * @param int     $id
     * @param Group[] $groups
     *
     * @return null|Group
     */
    private function getFullConfigurationGroup($id, $groups)
    {
        foreach ($groups as $group) {
            if ($group->getId() === $id) {
                return $group;
            }
        }

        return null;
    }
}
