<?php

namespace Shopware\Tests\Service;

use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Resource;
use Shopware\Gateway\DBAL\Configurator;
use Shopware\Gateway\DBAL\ProductConfiguration;
use Shopware\Gateway\DBAL\ProductProperty;
use Shopware\Service;
use Shopware\Models;
use Shopware\Struct;

class Helper
{
    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @var \Shopware\Components\Api\Resource\Article
     */
    private $articleApi;

    /**
     * @var \Shopware\Service\Converter
     */
    private $converter;

    /**
     * @var \Shopware\Components\Api\Resource\Translation
     */
    private $translationApi;

    /**
     * @var Resource\Variant
     */
    private $variantApi;

    function __construct()
    {
        $this->db = Shopware()->Db();
        $this->entityManager = Shopware()->Models();
        $this->converter = new Converter();

        $api = new Resource\Article();
        $api->setManager($this->entityManager);
        $this->articleApi = $api;

        $variantApi = new Resource\Variant();
        $variantApi->setManager($this->entityManager);
        $this->variantApi = $variantApi;

        $translation = new Resource\Translation();
        $translation->setManager($this->entityManager);
        $this->translationApi = $translation;
    }

    public function getProductConfigurator(
        Struct\ListProduct $listProduct,
        Struct\Context $context,
        array $selection = array(),
        \Shopware\Gateway\ProductConfiguration $productConfigurationGateway = null,
        \Shopware\Gateway\Configurator $configuratorGateway = null
    ) {
        if ($productConfigurationGateway == null) {
            $productConfigurationGateway = Shopware()->Container()->get('product_configuration_gateway');
        }
        if ($configuratorGateway == null) {
            $configuratorGateway = Shopware()->Container()->get('configurator_gateway');
        }

        $service = new Service\Configurator($productConfigurationGateway, $configuratorGateway);

        return $service->getProductConfigurator($listProduct, $context, $selection);
    }

    /**
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @param \Shopware\Gateway\ProductProperty $productPropertyGateway
     * @return Struct\Property\Set
     */
    public function getProductProperties(
        Struct\ListProduct $product,
        Struct\Context $context,
        \Shopware\Gateway\ProductProperty $productPropertyGateway = null
    ) {

        if ($productPropertyGateway === null) {
            $productPropertyGateway = Shopware()->Container()->get('product_property_gateway');
        }
        $service = new Service\Property($productPropertyGateway);

        return $service->get($product, $context);
    }

    /**
     * @param $number
     * @param Context $context
     * @param null $productGateway
     * @param null $graduatedPricesService
     * @param null $cheapestPriceService
     * @param null $priceCalculationService
     * @param null $mediaService
     * @param null $eventManager
     * @return Struct\ListProduct
     */
    public function getListProduct(
        $number,
        Context $context,
        $productGateway = null,
        $graduatedPricesService = null,
        $cheapestPriceService = null,
        $priceCalculationService = null,
        $mediaService = null,
        $eventManager = null
    ) {
        $products = $this->getListProducts(
            array($number),
            $context,
            $productGateway,
            $graduatedPricesService,
            $cheapestPriceService,
            $priceCalculationService,
            $mediaService,
            $eventManager
        );

        return array_shift($products);
    }

    /**
     * @param $numbers
     * @param $context
     * @param null $productGateway
     * @param null $graduatedPricesService
     * @param null $cheapestPriceService
     * @param null $priceCalculationService
     * @param null $mediaService
     * @param null $eventManager
     * @return Struct\ListProduct[]
     */
    public function getListProducts(
        $numbers,
        $context,
        $productGateway = null,
        $graduatedPricesService = null,
        $cheapestPriceService = null,
        $priceCalculationService = null,
        $mediaService = null,
        $eventManager = null
    ) {

        if ($productGateway === null)           $productGateway = Shopware()->Container()->get('list_product_gateway');
        if ($graduatedPricesService === null)   $graduatedPricesService = Shopware()->Container()->get('graduated_prices_service');
        if ($cheapestPriceService === null)     $cheapestPriceService = Shopware()->Container()->get('cheapest_price_service');
        if ($priceCalculationService === null)  $priceCalculationService = Shopware()->Container()->get('price_calculation_service');
        if ($mediaService === null)             $mediaService = Shopware()->Container()->get('media_service');
        if ($eventManager === null)             $eventManager = Shopware()->Container()->get('events');

        $service = new Service\ListProduct(
            $productGateway,
            $graduatedPricesService,
            $cheapestPriceService,
            $priceCalculationService,
            $mediaService,
            $eventManager
        );

        return $service->getList($numbers, $context);
    }


    /**
     * @param $number
     */
    public function removeArticle($number)
    {
        $detail = $this->entityManager->getRepository('Shopware\Models\Article\Detail')
            ->findOneBy(array('number' => $number));

        if (!$detail) {
            return;
        }

        $this->entityManager->remove($detail->getArticle());
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param array $data
     * @return Models\Article\Article
     */
    public function createArticle(array $data)
    {
        $this->removeArticle($data['mainDetail']['number']);
        return $this->articleApi->create($data);
    }

    public function createArticleTranslation($articleId, $shopId)
    {
        $data = array(
            'type' => 'article',
            'key' => $articleId,
            'localeId' => $shopId,
            'data' => $this->getArticleTranslation()
        );
        $this->translationApi->create($data);
    }

    public function createManufacturerTranslation($manufacturerId, $shopId)
    {
        $data = array(
            'type' => 'supplier',
            'key' => 1,
            'localeId' => $shopId,
            'data' => array(
                $manufacturerId => $this->getManufacturerTranslation()
            )
        );

        $this->translationApi->create($data);
    }

    public function createPropertyTranslation($properties, $shopId)
    {
        $this->translationApi->create(array(
            'type' => 'propertygroup',
            'key' => $properties['id'],
            'localeId' => $shopId,
            'data' => array('groupName' => 'Dummy Translation')
        ));

        foreach($properties['groups'] as $group) {
            $this->translationApi->create(array(
                'type' => 'propertyoption',
                'key' => $group['id'],
                'localeId' => $shopId,
                'data' => array('optionName' => 'Dummy Translation group - ' . $group['id'])
            ));

            foreach($group['options'] as $option) {
                $this->translationApi->create(array(
                    'type' => 'propertyvalue',
                    'key' => $option['id'],
                    'localeId' => $shopId,
                    'data' => array('optionValue' => 'Dummy Translation option - ' . $group['id'] . ' - ' . $option['id'])
                ));
            }
        }
    }

    public function createConfiguratorTranslation($configuratorSet, $shopId)
    {
        foreach($configuratorSet['groups'] as $group) {
            $this->translationApi->create(array(
                'type' => 'configuratorgroup',
                'key' => $group['id'],
                'localeId' => $shopId,
                'data' => array(
                    'name' => 'Dummy Translation group - ' . $group['id'],
                    'description' => 'Dummy Translation description - ' . $group['id']
                )
            ));

            foreach($group['options'] as $option) {
                $this->translationApi->create(array(
                    'type' => 'configuratoroption',
                    'key' => $option['id'],
                    'localeId' => $shopId,
                    'data' => array(
                        'name' => 'Dummy Translation option - ' . $group['id'] . ' - ' . $option['id']
                    )
                ));
            }
        }
    }

    public function createUnitTranslations(array $unitIds, $shopId, array $translation = array())
    {
        $data = array(
            'type' => 'config_units',
            'key' => 1,
            'localeId' => $shopId,
            'data' => array()
        );

        foreach($unitIds as $id) {
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

    /**
     * Creates a simple product which contains all required
     * data for an quick product creation.
     *
     * @param $number
     * @param Models\Tax\Tax $tax
     * @param Models\Customer\Group $customerGroup
     * @param float $priceOffset
     * @return array
     */
    public function getSimpleProduct(
        $number,
        Models\Tax\Tax $tax,
        Models\Customer\Group $customerGroup,
        $priceOffset = 0.00
    ) {
        $data = $this->getProductData(array(
            'taxId' => $tax->getId()
        ));

        $data['mainDetail'] = $this->getVariantData(array(
            'number' => $number
        ));

        $data['mainDetail']['prices'] = $this->getGraduatedPrices(
            $customerGroup->getKey(),
            $priceOffset
        );

        $data['mainDetail'] += $this->getUnitData();

        return $data;
    }

    /**
     * @param Models\Customer\Group $customerGroup used for the price definition
     * @param string $number
     * @param array $data Contains nested configurator group > option array.
     * @return array
     */
    public function getConfigurator(
        Models\Customer\Group $customerGroup,
        $number,
        array $data = array()
    ) {
        if (empty($data)) {
            $data = array(
                'Farbe' => array('rot', 'gelb', 'blau')
            );
        }
        $groups = $this->insertConfiguratorData($data);

        $configurator = $this->createConfiguratorSet($groups);

        $variants = array_merge(array(
            'prices' => $this->getGraduatedPrices($customerGroup->getKey())
        ), $this->getUnitData());

        $variants = $this->generateVariants(
            $configurator['groups'],
            $number,
            $variants
        );

        return array(
            'configuratorSet' => $configurator,
            'variants' => $variants
        );
    }

    public function updateConfiguratorVariants($articleId, $data)
    {
        foreach($data as $updateInformation) {
            $options = $updateInformation['options'];
            $variantData = $updateInformation['data'];

            $variants = $this->getVariantsByOptions($articleId, $options);

            if (empty($variants)) {
                continue;
            }

            foreach($variants as $variantId) {
                $this->variantApi->update($variantId, $variantData);
            }
        }
    }

    private function getVariantsByOptions($articleId, $options)
    {
        $ids = $this->getProductOptionsByName($articleId, $options);
        $ids = array_column($ids, 'id');

        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array(
            'relation.article_id',
            "CONCAT('|', GROUP_CONCAT(relation.option_id SEPARATOR '|'), '|') as options"
        ));

        $query->from('s_article_configurator_option_relations', 'relation')
            ->innerJoin('relation', 's_articles_details', 'variant', 'variant.id = relation.article_id')
            ->where('variant.articleID = :article')
            ->groupBy('relation.article_id')
            ->setParameter(':article', $articleId);


        foreach($ids as $id) {
            $query->andHaving("options LIKE '%|". (int) $id."|%'");
        }

        $statement = $query->execute();
        $ids = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return array_column($ids, 'article_id');
    }

    public function getProductOptionsByName($articleId, $optionNames) {

        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('options.id', 'options.group_id', 'options.name', 'options.position'))
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

        $statement = $query->execute();
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param Models\Article\Configurator\Group[] $groups
     * @return array
     */
    private function createConfiguratorSet(array $groups)
    {
        $data = array();

        foreach($groups as $group) {
            $options = array();
            foreach($group->getOptions() as $option) {
                $options[] =  array(
                    'id' => $option->getId(),
                    'name' => $option->getName()
                );
            }
            $data[] = array(
                'id' => $group->getId(),
                'name' => $group->getName(),
                'options' => $options
            );
        }
        return array(
            'name' => 'Unit test configurator set',
            'groups' => $data
        );
    }

    private function insertConfiguratorData($groups)
    {
        $pos = 1;
        $data = array();

        foreach($groups as $groupName => $options) {
            $group = new Models\Article\Configurator\Group();
            $group->setName($groupName);
            $group->setPosition($groups);
            $this->db->executeQuery("DELETE FROM s_article_configurator_groups WHERE name = ?", array($groupName));

            $collection = array();
            $optionPos = 1;
            foreach($options as $optionName) {
                $this->db->executeQuery("DELETE FROM s_article_configurator_options WHERE name = ?", array($optionName));

                $option = new Models\Article\Configurator\Option();
                $option->setName($optionName);
                $option->setPosition($optionPos);
                $collection[] = $option;
                $optionPos++;
            }
            $group->setOptions($collection);
            $pos++;

            $data[] = $group;

            $this->entityManager->persist($group);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getProductData(array $data = array())
    {
        return array_merge(
            array(
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

            ),
            $data
        );
    }

    public function getVariantData(array $data = array())
    {
        return array_merge(
            array(
                'number' => 'Variant-' . uniqid(),
                'supplierNumber' => 'kn12lk3nkl213',
                'active' => 1,
                'inStock' => 222,
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
            ),
            $data
        );
    }

    public function getUnitData(array $data = array())
    {
        return array_merge(
            array(
                'minPurchase' => 1,
                'purchaseSteps' => 2,
                'maxPurchase' => 100,
                'purchaseUnit' => 0.5000,
                'referenceUnit' => 1.000,
                'packUnit' => 'Flaschen',
                'unit' => array(
                    'name' => 'Liter'
                )
            ),
            $data
        );
    }

    public function getGraduatedPrices($group = 'EK', $priceOffset = 0)
    {
        return array(
            array(
                'from' => 1,
                'to' => 10,
                'price' => $priceOffset + 100.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 110
            ),
            array(
                'from' => 11,
                'to' => 20,
                'price' => $priceOffset + 75.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 85
            ),
            array(
                'from' => 21,
                'to' => 'beliebig',
                'price' => $priceOffset + 50.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 60
            )
        );
    }

    /**
     * @param string $image
     * @param array $data
     * @return array
     */
    public function getImageData(
        $image = 'test-spachtelmasse.jpg',
        array $data = array()
    ) {
        return array_merge(array(
            'main' => 2,
            'link' => 'file://' . __DIR__ . '/fixtures/' . $image
        ), $data);
    }

    /**
     * @param Models\Customer\Group $currentCustomerGroup
     * @param Models\Customer\Group $fallbackCustomerGroup
     * @param Models\Shop\Shop $shop
     * @param array $taxes
     * @param Models\Shop\Currency $currency
     *
     * @return \Shopware\Struct\Context
     */
    public function createContext(
        Models\Customer\Group $currentCustomerGroup,
        Models\Shop\Shop $shop,
        array $taxes,
        Models\Customer\Group $fallbackCustomerGroup = null,
        Models\Shop\Currency $currency = null
    ) {
        $context = new Struct\Context();

        $context->setTaxRules($this->buildTaxRules($taxes));

        $context->setShop($this->converter->convertShop($shop));

        if ($currency == null && $shop->getCurrency()) {
            $currency = $this->converter->convertCurrency(
                $shop->getCurrency()
            );
        } else {
            $currency = $this->converter->convertCurrency($currency);
        }

        $context->setCurrency($currency);

        $context->setCurrentCustomerGroup(
            $this->converter->convertCustomerGroup($currentCustomerGroup)
        );

        if (!$fallbackCustomerGroup) {
            $fallbackCustomerGroup = $currentCustomerGroup;
        }

        $context->setFallbackCustomerGroup(
            $this->converter->convertCustomerGroup($fallbackCustomerGroup)
        );

        return $context;
    }


    public function getProperties($groupCount, $optionCount)
    {
        $properties = $this->createProperties($groupCount, $optionCount);
        $options = array();
        foreach($properties['groups'] as $group) {
            $options = array_merge($options, $group['options']);
        }

        return array(
            'filterGroupId' => $properties['id'],
            'propertyValues' => $options,
            'all' => $properties
        );
    }

    private function createProperties($groupCount, $optionCount)
    {
        $this->db->query("DELETE FROM s_filter WHERE name = 'Test-Set'");
        $this->db->insert('s_filter', array('name' => 'Test-Set', 'comparable' => 1));
        $data = $this->db->fetchRow("SELECT * FROM s_filter WHERE name = 'Test-Set'");

        $this->db->query("DELETE FROM s_filter_options WHERE name LIKE 'Test-Gruppe%'");
        $this->db->query("DELETE FROM s_filter_values WHERE value LIKE 'Test-Option%'");

        for($i=0; $i<$groupCount; $i++) {
            $this->db->insert('s_filter_options', array(
                'name' => 'Test-Gruppe-' . $i,
                'filterable' => 1
            ));
            $group = $this->db->fetchRow("SELECT * FROM s_filter_options WHERE name = 'Test-Gruppe-" . $i . "'");

            for($i2=0; $i2 < $optionCount; $i2++) {
                $this->db->insert('s_filter_values', array(
                    'value' => 'Test-Option-' . $i . '-' .$i2,
                    'optionID' => $group['id']
                ));
            }

            $group['options'] = $this->db->fetchAll("SELECT * FROM s_filter_values WHERE optionID = ?", array($group['id']));
            $data['groups'][] = $group;

            $this->db->insert('s_filter_relations', array(
                'optionID' => $group['id'],
                'groupID' => $data['id']
            ));
        }
        return $data;
    }

    /**
     * @param int $shopId
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop($shopId = 1)
    {
        return $this->entityManager->find(
            'Shopware\Models\Shop\Shop',
            $shopId
        );
    }

    public function createPriceGroup($discounts = array())
    {
        if (empty($discounts)) {
            $discounts = array(
                array('key' => 'PHP', 'quantity' => 1,  'discount' => 10),
                array('key' => 'PHP', 'quantity' => 5,  'discount' => 20),
                array('key' => 'PHP', 'quantity' => 10, 'discount' => 30),
            );
        }

        $this->removePriceGroup();

        $priceGroup = new \Shopware\Models\Price\Group();
        $priceGroup->setName('TEST-GROUP');

        $repo = $this->entityManager->getRepository('Shopware\Models\Customer\Group');
        $collection = array();
        foreach($discounts as $data) {
            $discount = new \Shopware\Models\Price\Discount();
            $discount->setCustomerGroup(
                $repo->findOneBy(array('key' => $data['key']))
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

    private function removePriceGroup()
    {
        $ids = $this->db->fetchCol("SELECT id FROM s_core_pricegroups WHERE description = 'TEST-GROUP'");
        foreach($ids as $id) {
            $group = $this->entityManager->find('Shopware\Models\Price\Group', $id);
            $this->entityManager->remove($group);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * @param array $data
     * @return Models\Customer\Group
     */
    public function createCustomerGroup($data = array())
    {
        $data = array_merge(
            array(
                'key' => 'PHP',
                'name' => 'Unit test',
                'tax' => true,
                'taxInput' => true,
                'mode' => false, //use discounts?
                'discount' => 0 //percentage discount
            ),
            $data
        );

        $this->deleteCustomerGroup($data['key']);

        $customer = new Models\Customer\Group();
        $customer->fromArray($data);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);
        $this->entityManager->clear();

        return $customer;
    }

    public function createTax($data = array())
    {
        $data = array_merge(
            array(
                'tax' => 19.00,
                'name' => 'PHP UNIT'
            ),
            $data
        );

        $this->deleteTax($data['name']);

        $tax = new Models\Tax\Tax();
        $tax->fromArray($data);

        $this->entityManager->persist($tax);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $tax;
    }

    public function createCurrency(array $data = array())
    {
        $currency = new Models\Shop\Currency();

        $data = array_merge(
            array(
                'currency' => 'PHP',
                'factor' => 1,
                'name' => 'PHP',
                'default' => false,
                'symbol' => 'PHP',
            ),
            $data
        );

        $this->deleteCurrency($data['name']);

        $currency->fromArray($data);

        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $currency;
    }

    private function deleteCustomerGroup($key)
    {
        $ids = $this->db->fetchCol('SELECT id FROM s_core_customergroups WHERE groupkey = ?', array($key));
        if (!$ids) {
            return;
        }

        foreach ($ids as $id) {
            $customer = $this->entityManager->find('Shopware\Models\Customer\Group', $id);
            if (!$customer) continue;
            $this->entityManager->remove($customer);
            $this->entityManager->flush($customer);
        }
        $this->entityManager->clear();
    }

    private function deleteTax($name)
    {
        $ids = $this->db->fetchCol("SELECT id FROM s_core_tax WHERE description = ?", array($name));
        if (empty($ids)) return;

        foreach ($ids as $id) {
            $tax = $this->entityManager->find('Shopware\Models\Tax\Tax', $id);
            $this->entityManager->remove($tax);
            $this->entityManager->flush();
        }
        $this->entityManager->clear();
    }

    private function deleteCurrency($name)
    {
        $ids = $this->db->fetchCol("SELECT id FROM s_core_currencies WHERE name = ?", array($name));
        if (empty($ids)) return;

        foreach ($ids as $id) {
            $tax = $this->entityManager->find('Shopware\Models\Shop\Currency', $id);
            $this->entityManager->remove($tax);
            $this->entityManager->flush();
        }
        $this->entityManager->clear();
    }

    /**
     * Helper function which combines all array elements
     * of the passed arrays.
     *
     * @param $arrays
     * @param int $i
     * @return array
     */
    private function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        $tmp = $this->combinations($arrays, $i + 1);
        $result = array();

        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ? array_merge(array($v), $t) : array($v, $t);
            }
        }

        return $result;
    }

    /**
     * Helper function which creates all variants for
     * the passed groups with options.
     *
     * @param $groups
     * @param null $numberPrefix
     * @param array $data
     * @return array
     */
    private function generateVariants(
        $groups,
        $numberPrefix = null,
        $data = array()
    ) {
        $options = array();

        foreach ($groups as $group) {
            $groupOptions = array();
            foreach ($group['options'] as $option) {
                $groupOptions[] = array(
                    'groupId' => $group['id'],
                    'option' => $option['name']
                );
            }
            $options[] = $groupOptions;
        }

        $combinations = $this->combinations($options);
        $combinations = $this->cleanUpCombinations($combinations);

        $variants = array();
        $count = 1;
        if (!$numberPrefix) {
            $numberPrefix = 'Unit-Test-Variant-';
        }

        $this->db->executeQuery(
            "DELETE FROM s_articles_details WHERE ordernumber LIKE ?",
            array($numberPrefix . '%')
        );

        foreach ($combinations as $combination) {
            $variantData = array_merge(
                array('number' => $numberPrefix . $count),
                $data
            );

            $variant = $this->getVariantData($variantData);

            $variant['configuratorOptions'] = $combination;
            $variants[] = $variant;

            $count++;
        }
        return $variants;
    }

    /**
     * Combinations merge the result of dimensional arrays not perfectly
     * so we have to clean up the first array level.
     * @param $combinations
     * @return mixed
     */
    private function cleanUpCombinations($combinations)
    {
        foreach ($combinations as &$combination) {
            $combination[] = array('option' => $combination['option'], 'groupId' => $combination['groupId']);
            unset($combination['groupId']);
            unset($combination['option']);
        }
        return $combinations;
    }

    /**
     * @param Models\Tax\Tax[] $taxes
     * @return Struct\Tax[]
     */
    private function buildTaxRules(array $taxes)
    {
        $rules = array();
        foreach ($taxes as $model) {
            $key = 'tax_' . $model->getId();
            $struct = $this->converter->convertTax($model);
            $rules[$key] = $struct;
        }
        return $rules;
    }


    private function getUnitTranslation()
    {
        return array(
            'unit' => 'Dummy Translation',
            'description' => 'Dummy Translation'
        );
    }


    private function getManufacturerTranslation()
    {
        return array(
            'metaTitle' => 'Dummy Translation',
            'description' => 'Dummy Translation',
            'metaDescription' => 'Dummy Translation',
            'metaKeywords' => 'Dummy Translation',
        );
    }

    private function getArticleTranslation()
    {
        return array(
            'txtArtikel' => 'Dummy Translation',
            'txtshortdescription' => 'Dummy Translation',
            'txtlangbeschreibung' => 'Dummy Translation',
            'txtzusatztxt' => 'Dummy Translation',
            'txtkeywords' => 'Dummy Translation',
            'txtpackunit' => 'Dummy Translation',
            'metaTitle' => 'Dummy Translation'
        );
    }
}