<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Doctrine\DBAL\Connection;
use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Exception;
use PDO;
use Shopware\Bundle\StoreFrontBundle;
use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\ConfiguratorGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\ProductConfigurationGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductConfigurationGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductPropertyGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set as ConfiguratorSet;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group as CustomerGroupStruct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set as PropertySet;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax as TaxStruct;
use Shopware\Components\Api\Resource\Article as ProductResource;
use Shopware\Components\Api\Resource\Category as CategoryResource;
use Shopware\Components\Api\Resource\Translation;
use Shopware\Components\Api\Resource\Variant as VariantResource;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Random;
use Shopware\Kernel;
use Shopware\Models;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Price\Group as PriceGroup;
use Shopware\Models\Shop\Currency;
use Shopware\Models\Tax\Tax as TaxModel;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper\ProgressHelper;
use Shopware_Components_Config;

class Helper
{
    protected Converter $converter;

    private Enlight_Components_Db_Adapter_Pdo_Mysql $db;

    private ModelManager $entityManager;

    private ProductResource $articleApi;

    private Translation $translationApi;

    private VariantResource $variantApi;

    private CategoryResource $categoryApi;

    /**
     * @var string[]
     */
    private array $createdProducts = [];

    /**
     * @var int[]
     */
    private array $createdManufacturers = [];

    /**
     * @var int[]
     */
    private array $createdCategories = [];

    /**
     * @var string[]
     */
    private array $createdCustomerGroups = [];

    /**
     * @var string[]
     */
    private array $createdTaxes = [];

    /**
     * @var string[]
     */
    private array $createdCurrencies = [];

    /**
     * @var int[]
     */
    private array $createdConfiguratorGroups = [];

    /**
     * @var string[]
     */
    private array $propertyNames = [];

    public function __construct()
    {
        $this->db = Shopware()->Db();
        $this->entityManager = Shopware()->Models();
        $this->converter = new Converter();

        $api = new ProductResource();
        $api->setManager($this->entityManager);
        $this->articleApi = $api;

        $variantApi = new VariantResource();
        $variantApi->setManager($this->entityManager);
        $this->variantApi = $variantApi;

        $translation = new Translation();
        $translation->setManager($this->entityManager);
        $this->translationApi = $translation;

        $categoryApi = new CategoryResource();
        $categoryApi->setManager($this->entityManager);
        $this->categoryApi = $categoryApi;
    }

    public function getProductConfigurator(
        ListProduct $listProduct,
        StoreFrontBundle\Struct\ShopContext $context,
        array $selection = [],
        ProductConfigurationGateway $productConfigurationGateway = null,
        ConfiguratorGateway $configuratorGateway = null
    ): ConfiguratorSet {
        if ($productConfigurationGateway === null) {
            $productConfigurationGateway = Shopware()->Container()->get(ProductConfigurationGatewayInterface::class);
        }
        if ($configuratorGateway === null) {
            $configuratorGateway = Shopware()->Container()->get(ConfiguratorGatewayInterface::class);
        }

        $service = new StoreFrontBundle\Service\Core\ConfiguratorService(
            $productConfigurationGateway,
            $configuratorGateway
        );

        return $service->getProductConfigurator($listProduct, $context, $selection);
    }

    public function getProductProperties(
        ListProduct $product,
        StoreFrontBundle\Struct\ShopContext $context,
        StoreFrontBundle\Gateway\DBAL\ProductPropertyGateway $productPropertyGateway = null
    ): PropertySet {
        if ($productPropertyGateway === null) {
            $productPropertyGateway = Shopware()->Container()->get(ProductPropertyGatewayInterface::class);
        }
        $service = new StoreFrontBundle\Service\Core\PropertyService($productPropertyGateway);

        return $service->get($product, $context);
    }

    /**
     * @param string[] $numbers
     *
     * @return ListProduct[]
     */
    public function getListProducts(array $numbers, ShopContextInterface $context, array $configs = []): array
    {
        $config = Shopware()->Container()->get(Shopware_Components_Config::class);
        $originals = [];
        foreach ($configs as $key => $value) {
            $originals[$key] = $config->get($key);
            $config->offsetSet($key, $value);
        }

        $result = Shopware()->Container()->get(ListProductServiceInterface::class)->getList($numbers, $context);
        foreach ($originals as $key => $value) {
            $config->offsetSet($key, $value);
        }

        return $result;
    }

    public function getListProduct(string $number, ShopContextInterface $context, array $configs = []): ListProduct
    {
        $listProducts = $this->getListProducts([$number], $context, $configs);

        return array_shift($listProducts);
    }

    /**
     * Creates a simple product which contains all required
     * data for a quick product creation.
     *
     * @param TaxModel|TaxStruct|null $tax
     */
    public function getSimpleProduct(
        string $number,
        $tax = null, // Either Model/Tax or Struct/Tax
        CustomerGroupStruct $customerGroup = null,
        float $priceOffset = 0.00
    ): array {
        if ($tax === null) {
            $tax = $this->entityManager->find(TaxModel::class, 1);
            \assert($tax instanceof TaxModel);
        }
        $key = 'EK';
        if ($customerGroup) {
            $key = $customerGroup->getKey();
        }

        $data = $this->getProductData([
            'taxId' => $tax->getId(),
        ]);

        $data['mainDetail'] = $this->getVariantData([
            'number' => $number,
        ]);

        $data['mainDetail']['prices'] = $this->getGraduatedPrices($key, $priceOffset);

        $data['mainDetail'] += $this->getUnitData();

        return $data;
    }

    public function cleanUp(): void
    {
        foreach ($this->propertyNames as $name) {
            $this->deleteProperties($name);
        }
        $this->removePriceGroup();
        foreach ($this->createdProducts as $number) {
            $this->removeArticle($number);
        }

        foreach ($this->createdCustomerGroups as $key) {
            $this->deleteCustomerGroup($key);
        }

        foreach ($this->createdTaxes as $tax) {
            $this->deleteTax($tax);
        }

        foreach ($this->createdCurrencies as $currency) {
            $this->deleteCurrency($currency);
        }

        foreach ($this->createdCategories as $category) {
            try {
                $this->categoryApi->delete($category);
            } catch (Exception $e) {
            }
        }

        foreach ($this->createdManufacturers as $manufacturerId) {
            try {
                $manufacturer = $this->entityManager->find(Supplier::class, $manufacturerId);
                if (!$manufacturer) {
                    continue;
                }

                $this->entityManager->remove($manufacturer);
                $this->entityManager->flush();
            } catch (Exception $e) {
            }
        }

        foreach ($this->createdConfiguratorGroups as $groupId) {
            $group = $this->entityManager->find(ConfiguratorGroup::class, $groupId);
            if (!$group) {
                continue;
            }
            $this->entityManager->remove($group);
            $this->entityManager->flush();
        }
    }

    public function removeArticle(string $number): void
    {
        $productId = $this->db->fetchOne(
            'SELECT articleID FROM s_articles_details WHERE ordernumber = ?',
            [$number]
        );

        if ($productId === false) {
            return;
        }

        $product = $this->entityManager->find(ProductModel::class, $productId);

        if ($product) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $variantIds = $this->db->fetchCol(
            'SELECT id FROM s_articles_details WHERE articleID = ?',
            [$productId]
        );

        if (empty($variantIds)) {
            return;
        }

        foreach ($variantIds as $id) {
            $detail = $this->entityManager->find(Detail::class, $id);
            if ($detail) {
                $this->entityManager->remove($detail);
                $this->entityManager->flush();
            }
        }
        $this->entityManager->clear();
    }

    public function createArticle(array $data): ProductModel
    {
        $this->removeArticle($data['mainDetail']['number']);
        $this->createdProducts[] = $data['mainDetail']['number'];

        return $this->articleApi->create($data);
    }

    public function createArticleTranslation(int $articleId, int $shopId): void
    {
        $data = [
            'type' => 'article',
            'key' => $articleId,
            'shopId' => $shopId,
            'data' => $this->getArticleTranslation(),
        ];
        $this->translationApi->create($data);
    }

    public function updateArticle(string $orderNumber, array $data): ProductModel
    {
        return $this->articleApi->updateByNumber($orderNumber, $data);
    }

    public function createManufacturerTranslation(int $manufacturerId, int $shopId): void
    {
        $data = [
            'type' => 'supplier',
            'key' => $manufacturerId,
            'shopId' => $shopId,
            'data' => $this->getManufacturerTranslation(),
        ];

        $this->translationApi->create($data);
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function createPropertyTranslation(array $properties, int $shopId): void
    {
        $this->translationApi->create([
            'type' => 'propertygroup',
            'key' => $properties['id'],
            'shopId' => $shopId,
            'data' => ['groupName' => 'Dummy Translation'],
        ]);

        foreach ($properties['groups'] as $group) {
            $this->translationApi->create([
                'type' => 'propertyoption',
                'key' => $group['id'],
                'shopId' => $shopId,
                'data' => ['optionName' => 'Dummy Translation group - ' . $group['id']],
            ]);

            foreach ($group['options'] as $option) {
                $this->translationApi->create([
                    'type' => 'propertyvalue',
                    'key' => $option['id'],
                    'shopId' => $shopId,
                    'data' => ['optionValue' => 'Dummy Translation option - ' . $group['id'] . ' - ' . $option['id']],
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $configuratorSet
     */
    public function createConfiguratorTranslation(array $configuratorSet, int $shopId): void
    {
        foreach ($configuratorSet['groups'] as $group) {
            $this->translationApi->create([
                'type' => 'configuratorgroup',
                'key' => $group['id'],
                'shopId' => $shopId,
                'data' => [
                    'name' => 'Dummy Translation group - ' . $group['id'],
                    'description' => 'Dummy Translation description - ' . $group['id'],
                ],
            ]);

            foreach ($group['options'] as $option) {
                $this->translationApi->create([
                    'type' => 'configuratoroption',
                    'key' => $option['id'],
                    'shopId' => $shopId,
                    'data' => [
                        'name' => 'Dummy Translation option - ' . $group['id'] . ' - ' . $option['id'],
                    ],
                ]);
            }
        }
    }

    public function createUnitTranslations(array $unitIds, int $shopId, array $translation = []): void
    {
        $data = [
            'type' => 'config_units',
            'key' => 1,
            'shopId' => $shopId,
            'data' => [],
        ];

        foreach ($unitIds as $id) {
            if (isset($translation[$id])) {
                $data['data'][$id] = array_merge(
                    $this->getUnitTranslation(),
                    $translation[$id]
                );
            } else {
                $data['data'][$id] = $this->getUnitTranslation();
            }
        }

        $this->translationApi->create($data);
    }

    public function createPriceGroup(array $discounts = []): PriceGroup
    {
        if (empty($discounts)) {
            $discounts = [
                ['key' => 'PHP', 'quantity' => 1,  'discount' => 10],
                ['key' => 'PHP', 'quantity' => 5,  'discount' => 20],
                ['key' => 'PHP', 'quantity' => 10, 'discount' => 30],
            ];
        }

        $this->removePriceGroup();

        $priceGroup = new PriceGroup();
        $priceGroup->setName('TEST-GROUP');

        $repo = $this->entityManager->getRepository(CustomerGroup::class);
        $collection = [];
        foreach ($discounts as $data) {
            $discount = new Models\Price\Discount();
            $discount->setCustomerGroup(
                $repo->findOneBy(['key' => $data['key']])
            );

            $discount->setGroup($priceGroup);
            $discount->setStart($data['quantity']);
            $discount->setDiscount($data['discount']);

            $collection[] = $discount;
        }
        $priceGroup->setDiscounts($collection);

        $this->entityManager->persist($priceGroup);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $priceGroup;
    }

    public function createCustomerGroup(array $data = []): CustomerGroup
    {
        $data = array_merge(
            [
                'key' => 'PHP',
                'name' => 'Unit test',
                'tax' => true,
                'taxInput' => true,
                'mode' => false, //use discounts?
                'discount' => 0, //percentage discount
            ],
            $data
        );

        $this->deleteCustomerGroup($data['key']);

        $customer = new CustomerGroup();
        $customer->fromArray($data);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);
        $this->entityManager->clear();

        $this->createdCustomerGroups[] = $customer->getKey();

        return $customer;
    }

    public function createTax(array $data = []): TaxModel
    {
        $data = array_merge(
            [
                'tax' => 19.00,
                'name' => 'PHP UNIT',
            ],
            $data
        );

        $this->deleteTax($data['name']);

        $tax = new TaxModel();
        $tax->fromArray($data);

        $this->entityManager->persist($tax);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->createdTaxes[] = $data['name'];

        return $tax;
    }

    public function createCurrency(array $data = []): Currency
    {
        $currency = new Currency();

        $data = array_merge(
            [
                'currency' => 'PHP',
                'factor' => 1,
                'name' => 'PHP',
                'default' => false,
                'symbol' => 'PHP',
            ],
            $data
        );

        $this->deleteCurrency($data['name']);

        $currency->fromArray($data);

        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->createdCurrencies[] = $data['name'];

        return $currency;
    }

    public function createCategory(array $data = []): Category
    {
        $data = array_merge($this->getCategoryData(), $data);

        $this->deleteCategory($data['name']);

        $category = $this->categoryApi->create($data);

        $this->createdCategories[] = $category->getId();

        return $category;
    }

    public function createManufacturer(array $data = []): Supplier
    {
        $data = array_merge($this->getManufacturerData(), $data);
        $manufacturer = new Supplier();
        $manufacturer->fromArray($data);
        $this->entityManager->persist($manufacturer);
        $this->entityManager->flush();

        $this->createdManufacturers[] = $manufacturer->getId();

        return $manufacturer;
    }

    public function createVotes(int $articleId, array $points = [], ?int $shopId = null): void
    {
        $data = [
            'id' => null,
            'articleID' => $articleId,
            'name' => 'Bert Bewerter',
            'headline' => 'Super Artikel',
            'comment' => 'Dieser Artikel zeichnet sich durch extreme Stabilität aus und fasst super viele Klamotten. Das Preisleistungsverhältnis ist exorbitant gut.',
            'points' => null,
            'datum' => '2012-08-29 14:02:24',
            'active' => '1',
            'shop_id' => $shopId,
        ];

        foreach ($points as $point) {
            $data['points'] = $point;
            Shopware()->Db()->insert('s_articles_vote', $data);
        }
    }

    /**
     * @param CustomerGroupStruct $customerGroup used for the price definition
     * @param array               $data          contains nested configurator group > option array
     */
    public function getConfigurator(
        CustomerGroupStruct $customerGroup,
        string $number,
        array $data = []
    ): array {
        if (empty($data)) {
            $data = [
                'Farbe' => ['rot', 'gelb', 'blau'],
            ];
        }
        $groups = $this->insertConfiguratorData($data);

        $configurator = $this->createConfiguratorSet($groups);

        $variants = array_merge([
            'prices' => $this->getGraduatedPrices($customerGroup->getKey()),
        ], $this->getUnitData());

        $variants = $this->generateVariants(
            $configurator['groups'],
            $number,
            $variants
        );

        return [
            'configuratorSet' => $configurator,
            'variants' => $variants,
        ];
    }

    public function updateConfiguratorVariants(int $articleId, array $data): void
    {
        foreach ($data as $updateInformation) {
            $options = $updateInformation['options'];
            $variantData = $updateInformation['data'];

            $variants = $this->getVariantsByOptions($articleId, $options);

            if (empty($variants)) {
                continue;
            }

            foreach ($variants as $variantId) {
                $this->variantApi->update($variantId, $variantData);
            }
        }
    }

    /**
     * @param string[] $optionNames
     */
    public function getProductOptionsByName(int $articleId, array $optionNames): array
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(['options.id', 'options.group_id', 'options.name', 'options.position'])
            ->from('s_article_configurator_options', 'options')
            ->innerJoin(
                'options',
                's_article_configurator_option_relations',
                'relation',
                'relation.option_id = options.id'
            )
            ->innerJoin(
                'relation',
                's_articles_details',
                'variant',
                'variant.id = relation.article_id AND variant.articleID = :article'
            )
            ->groupBy('options.id')
            ->where('options.name IN (:names)')
            ->setParameter('article', $articleId)
            ->setParameter(':names', $optionNames, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductData(array $data = []): array
    {
        return array_merge(
            [
                'id' => 277,
                'supplierId' => 1,
                'taxId' => 1,
                'name' => 'Unit test product',
                'description' => 'Lorem ipsum',
                'descriptionLong' => 'Lorem ipsum',
                'active' => true,
                'pseudoSales' => 2,
                'highlight' => true,
                'keywords' => 'Lorem',
                'metaTitle' => 'Lorem',
                'lastStock' => true,
                'crossBundleLook' => 0,
                'notification' => true,
                'template' => '',
                'mode' => 0,
                'availableFrom' => null,
                'availableTo' => null,
            ],
            $data
        );
    }

    public function getCategoryData(): array
    {
        return [
            'parent' => 3,
            'name' => 'Test-Category',
        ];
    }

    public function getManufacturerData(): array
    {
        return [
            'name' => 'Test-Manufacturer',
        ];
    }

    public function getVariantData(array $data = []): array
    {
        return array_merge(
            [
                'number' => 'Variant-' . uniqid((string) Random::getInteger(0, PHP_INT_MAX), true),
                'supplierNumber' => 'kn12lk3nkl213',
                'active' => 1,
                'inStock' => 222,
                'lastStock' => true,
                'stockMin' => 51,
                'weight' => '2.000',
                'width' => '23.000',
                'len' => '32.000',
                'height' => '32.000',
                'ean' => '233',
                'position' => 0,
                'minPurchase' => 1,
                'shippingFree' => true,
                'releaseDate' => '2002-02-02 00:00:00',
                'shippingTime' => '2',
            ],
            $data
        );
    }

    public function getUnitData(array $data = []): array
    {
        return array_merge(
            [
                'minPurchase' => 1,
                'purchaseSteps' => 2,
                'maxPurchase' => 100,
                'purchaseUnit' => 0.5000,
                'referenceUnit' => 1.000,
                'packUnit' => 'Flaschen',
                'unit' => [
                    'name' => 'Liter',
                ],
            ],
            $data
        );
    }

    public function getGraduatedPrices(string $group = 'EK', float $priceOffset = 0.0): array
    {
        return [
            [
                'from' => 1,
                'to' => 10,
                'price' => $priceOffset + 100.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 110,
            ],
            [
                'from' => 11,
                'to' => 20,
                'price' => $priceOffset + 75.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 85,
            ],
            [
                'from' => 21,
                'to' => 'beliebig',
                'price' => $priceOffset + 50.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 60,
            ],
        ];
    }

    public function getImageData(
        string $image = 'test-spachtelmasse.jpg',
        array $data = []
    ): array {
        return array_merge([
            'main' => 2,
            'link' => 'file://' . __DIR__ . '/fixtures/' . $image,
        ], $data);
    }

    public function createContext(
        CustomerGroup $currentCustomerGroup,
        Models\Shop\Shop $shop,
        array $taxes,
        CustomerGroup $fallbackCustomerGroup = null,
        ?Currency $currency = null
    ): TestContext {
        if ($currency === null) {
            \assert($shop->getCurrency() instanceof Currency);
            $currency = $this->converter->convertCurrency($shop->getCurrency());
        } else {
            $currency = $this->converter->convertCurrency($currency);
        }

        if ($fallbackCustomerGroup === null) {
            $fallbackCustomerGroup = $currentCustomerGroup;
        }

        return new TestContext(
            '',
            $this->converter->convertShop($shop),
            $currency,
            $this->converter->convertCustomerGroup($currentCustomerGroup),
            $this->converter->convertCustomerGroup($fallbackCustomerGroup),
            $this->buildTaxRules($taxes),
            []
        );
    }

    public function getProperties(int $groupCount, int $optionCount, string $namePrefix = 'Test'): array
    {
        $properties = $this->createProperties($groupCount, $optionCount, $namePrefix);
        $options = [];
        foreach ($properties['groups'] as $group) {
            $options = array_merge($options, $group['options']);
        }

        return [
            'filterGroupId' => $properties['id'],
            'propertyValues' => $options,
            'all' => $properties,
        ];
    }

    public function getShop(int $shopId = 1): Models\Shop\Shop
    {
        return $this->entityManager->find(
            \Shopware\Models\Shop\Shop::class,
            $shopId
        );
    }

    public function isElasticSearchEnabled(): bool
    {
        /** @var Kernel $kernel */
        $kernel = Shopware()->Container()->get('kernel');

        return $kernel->isElasticSearchEnabled();
    }

    public function refreshSearchIndexes(Shop $shop): void
    {
        if (!$this->isElasticSearchEnabled()) {
            return;
        }

        $this->clearSearchIndex();
        Shopware()->Container()->get('shopware_elastic_search.shop_indexer')->index($shop, new ProgressHelper());
    }

    public function refreshBackendSearchIndex(): void
    {
        if (!$this->isElasticSearchEnabled()) {
            return;
        }

        $this->clearSearchIndex();
        Shopware()->Container()->get('shopware_es_backend.indexer')->index(new ProgressHelper());
    }

    public function clearSearchIndex(): void
    {
        $client = Shopware()->Container()->get('shopware_elastic_search.client');
        $client->indices()->delete(['index' => '_all']);
    }

    /**
     * @param ConfiguratorGroup[] $groups
     */
    public function createConfiguratorSet(array $groups): array
    {
        $data = [];

        foreach ($groups as $group) {
            $options = [];
            /** @var Models\Article\Configurator\Option $option */
            foreach ($group->getOptions() as $option) {
                $options[] = [
                    'id' => $option->getId(),
                    'name' => $option->getName(),
                ];
            }
            $data[] = [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'options' => $options,
            ];
        }

        return [
            'name' => 'Unit test configurator set',
            'groups' => $data,
        ];
    }

    /**
     * @return ConfiguratorGroup[]
     */
    public function insertConfiguratorData(array $groups): array
    {
        $data = [];

        foreach ($groups as $groupName => $options) {
            $group = new ConfiguratorGroup();
            $group->setName($groupName);
            $group->setPosition($groups);
            $this->db->executeQuery('DELETE FROM s_article_configurator_groups WHERE name = ?', [$groupName]);

            $collection = [];
            $optionPos = 1;
            foreach ($options as $optionName) {
                $this->db->executeQuery('DELETE FROM s_article_configurator_options WHERE name = ?', [$optionName]);

                $option = new Models\Article\Configurator\Option();
                $option->setName($optionName);
                $option->setPosition($optionPos);
                $collection[] = $option;
                ++$optionPos;
            }
            $group->setOptions($collection);

            $data[] = $group;

            $this->entityManager->persist($group);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->createdConfiguratorGroups[] = $group->getId();
        }

        return $data;
    }

    /**
     * Helper function which creates all variants for
     * the passed groups with options.
     */
    public function generateVariants(
        array $groups,
        $numberPrefix = null,
        array $data = []
    ): array {
        $options = [];

        foreach ($groups as $group) {
            $groupOptions = [];
            foreach ($group['options'] as $option) {
                $groupOptions[] = [
                    'groupId' => $group['id'],
                    'optionId' => $option['id'],
                    'option' => $option['name'],
                ];
            }
            $options[] = $groupOptions;
        }

        $combinations = $this->combinations($options);
        $combinations = $this->cleanUpCombinations($combinations);

        $variants = [];
        $count = 1;
        if (!$numberPrefix) {
            $numberPrefix = 'Unit-Test-Variant-';
        }

        $this->db->executeQuery(
            'DELETE FROM s_articles_details WHERE ordernumber LIKE ?',
            [$numberPrefix . '%']
        );

        foreach ($combinations as $combination) {
            $variantData = array_merge(
                ['number' => $numberPrefix . $count],
                $data
            );

            $variant = $this->getVariantData($variantData);

            $variant['configuratorOptions'] = $combination;
            $variants[] = $variant;

            ++$count;
        }

        return $variants;
    }

    private function createProperties(int $groupCount, int $optionCount, string $namePrefix = 'Test'): array
    {
        $this->propertyNames[] = $namePrefix;

        $this->deleteProperties($namePrefix);

        $this->db->insert('s_filter', ['name' => $namePrefix . '-Set', 'comparable' => 1]);
        $data = $this->db->fetchRow("SELECT * FROM s_filter WHERE name = '" . $namePrefix . "-Set'");

        for ($i = 0; $i < $groupCount; ++$i) {
            $this->db->insert('s_filter_options', [
                'name' => $namePrefix . '-Gruppe-' . $i,
                'filterable' => 1,
            ]);
            $group = $this->db->fetchRow("SELECT * FROM s_filter_options WHERE name = '" . $namePrefix . '-Gruppe-' . $i . "'");

            for ($i2 = 0; $i2 < $optionCount; ++$i2) {
                $this->db->insert('s_filter_values', [
                    'value' => $namePrefix . '-Option-' . $i . '-' . $i2,
                    'optionID' => $group['id'],
                ]);
            }

            $group['options'] = $this->db->fetchAll('SELECT * FROM s_filter_values WHERE optionID = ?', [$group['id']]);

            $data['groups'][] = $group;

            $this->db->insert('s_filter_relations', [
                'optionID' => $group['id'],
                'groupID' => $data['id'],
            ]);
        }

        return $data;
    }

    private function deleteProperties(string $namePrefix = 'Test'): void
    {
        $this->db->query("DELETE FROM s_filter WHERE name = '" . $namePrefix . "-Set'");

        $ids = $this->db->fetchCol("SELECT id FROM s_filter_options WHERE name LIKE '" . $namePrefix . "-Gruppe%'");
        foreach ($ids as $id) {
            $this->db->query('DELETE FROM s_filter_options WHERE id = ?', [$id]);
            $this->db->query('DELETE FROM s_filter_relations WHERE optionID = ?', [$id]);
        }

        $this->db->query("DELETE FROM s_filter_values WHERE value LIKE '" . $namePrefix . "-Option%'");
    }

    private function deleteCategory(string $name): void
    {
        $ids = Shopware()->Db()->fetchCol('SELECT id FROM s_categories WHERE description = ?', [$name]);

        foreach ($ids as $id) {
            $this->categoryApi->delete($id);
        }
    }

    private function getVariantsByOptions(int $articleId, array $options): array
    {
        $ids = $this->getProductOptionsByName($articleId, $options);
        $ids = array_column($ids, 'id');

        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select([
            'relation.article_id',
            "CONCAT('|', GROUP_CONCAT(relation.option_id SEPARATOR '|'), '|') as options",
        ]);

        $query->from('s_article_configurator_option_relations', 'relation')
            ->innerJoin('relation', 's_articles_details', 'variant', 'variant.id = relation.article_id')
            ->where('variant.articleID = :article')
            ->groupBy('relation.article_id')
            ->setParameter(':article', $articleId);

        foreach ($ids as $id) {
            $query->andHaving("options LIKE '%|" . (int) $id . "|%'");
        }

        $ids = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        return array_column($ids, 'article_id');
    }

    private function deleteCustomerGroup(string $key): void
    {
        $ids = $this->db->fetchCol('SELECT id FROM s_core_customergroups WHERE groupkey = ?', [$key]);
        if (!$ids) {
            return;
        }

        foreach ($ids as $id) {
            $customer = $this->entityManager->find(CustomerGroup::class, $id);
            if (!$customer) {
                continue;
            }
            $this->entityManager->remove($customer);
            $this->entityManager->flush($customer);
        }
        $this->entityManager->clear();
    }

    private function deleteTax(string $name): void
    {
        $ids = $this->db->fetchCol('SELECT id FROM s_core_tax WHERE description = ?', [$name]);
        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            $tax = $this->entityManager->find(TaxModel::class, $id);
            $this->entityManager->remove($tax);
            $this->entityManager->flush();
        }
        $this->entityManager->clear();
    }

    private function deleteCurrency(string $name): void
    {
        $ids = $this->db->fetchCol('SELECT id FROM s_core_currencies WHERE name = ?', [$name]);
        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            $tax = $this->entityManager->find(Currency::class, $id);
            $this->entityManager->remove($tax);
            $this->entityManager->flush();
        }
        $this->entityManager->clear();
    }

    private function removePriceGroup(): void
    {
        $ids = $this->db->fetchCol("SELECT id FROM s_core_pricegroups WHERE description = 'TEST-GROUP'");
        foreach ($ids as $id) {
            $group = $this->entityManager->find(PriceGroup::class, $id);
            $this->entityManager->remove($group);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * Helper function which combines all array elements
     * of the passed arrays.
     */
    private function combinations(array $arrays, int $i = 0): array
    {
        if (!isset($arrays[$i])) {
            return [];
        }
        if ($i == \count($arrays) - 1) {
            return $arrays[$i];
        }

        $tmp = $this->combinations($arrays, $i + 1);
        $result = [];

        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = \is_array($t) ? array_merge([$v], $t) : [$v, $t];
            }
        }

        return $result;
    }

    /**
     * Combinations merge the result of dimensional arrays not perfectly,
     * so we have to clean up the first array level.
     *
     * @param array[] $combinations
     *
     * @return array[]
     */
    private function cleanUpCombinations(array $combinations): array
    {
        foreach ($combinations as &$combination) {
            $combination[] = ['option' => $combination['option'], 'groupId' => $combination['groupId']];
            unset($combination['groupId'], $combination['option']);
        }

        return $combinations;
    }

    /**
     * @param Models\Tax\Tax[] $taxes
     *
     * @return TaxStruct[]
     */
    private function buildTaxRules(array $taxes): array
    {
        $rules = [];
        foreach ($taxes as $model) {
            $key = 'tax_' . $model->getId();
            $rules[$key] = $this->converter->convertTax($model);
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function getUnitTranslation(): array
    {
        return [
            'unit' => 'Dummy Translation',
            'description' => 'Dummy Translation',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getManufacturerTranslation(): array
    {
        return [
            'metaTitle' => 'Dummy Translation',
            'description' => 'Dummy Translation',
            'metaDescription' => 'Dummy Translation',
            'metaKeywords' => 'Dummy Translation',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getArticleTranslation(): array
    {
        return [
            'txtArtikel' => 'Dummy Translation',
            'txtshortdescription' => 'Dummy Translation',
            'txtlangbeschreibung' => 'Dummy Translation',
            'txtshippingtime' => 'Dummy Translation',
            'txtzusatztxt' => 'Dummy Translation',
            'txtkeywords' => 'Dummy Translation',
            'txtpackunit' => 'Dummy Translation',
            'metaTitle' => 'Dummy Translation',
        ];
    }
}
