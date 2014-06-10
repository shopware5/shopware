<?php

namespace Shopware\Tests\Service;

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

    function __construct()
    {
        $this->db = Shopware()->Db();
        $this->entityManager = Shopware()->Models();
        $this->converter = new Converter();

        $api = new Resource\Article();
        $api->setManager($this->entityManager);
        $this->articleApi = $api;


        $translation = new Resource\Translation();
        $translation->setManager($this->entityManager);
        $this->translationApi = $translation;
    }

    public function getProductConfigurator(
        Struct\ListProduct $listProduct,
        Struct\Context $context,
        ProductConfiguration $productConfigurationGateway = null,
        Configurator $configuratorGateway = null
    ) {
        if ($productConfigurationGateway == null) {
            $productConfigurationGateway = Shopware()->Container()->get('product_configuration_gateway');
        }
        if ($configuratorGateway == null) {
            $configuratorGateway = Shopware()->Container()->get('configurator_gateway');
        }

        $service = new Service\Configurator($productConfigurationGateway, $configuratorGateway);

        return $service->getProductConfigurator($listProduct, $context);
    }

    /**
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @param ProductProperty $productPropertyGateway
     * @return Struct\Property\Set
     */
    public function getProductProperties(
        Struct\ListProduct $product,
        Struct\Context $context,
        ProductProperty $productPropertyGateway = null
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
        $detail = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail')
            ->findOneBy(array('number' => $number));

        if (!$detail) {
            return;
        }

        Shopware()->Models()->remove($detail->getArticle());
        Shopware()->Models()->flush();
        Shopware()->Models()->clear();
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
     * Helper function which creates a configurator set in the database
     * and generates the variants and the required configurator set for
     * the rest api.
     *
     * @param \Shopware\Models\Customer\Group $customerGroup
     * @param array $variantData
     * @param int $groupCount
     * @param int $optionCount
     * @return array
     */
    public function getConfigurator(
        Models\Customer\Group $customerGroup,
        $groupCount = 1,
        $optionCount = 3,
        $variantData = array()
    ) {
        $configurator = $this->createConfigurator($groupCount, $optionCount);

        $data = array_merge(array(
            'prices' => $this->getGraduatedPrices($customerGroup->getKey())
        ), $this->getUnitData());

        $data = array_merge(
            $data,
            $variantData
        );

        $variants = $this->generateVariants(
            $configurator['groups'],
            $data
        );

        return array(
            'configuratorSet' => $configurator,
            'variants' => $variants
        );
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
        Shopware()->Db()->query("DELETE FROM s_filter WHERE name = 'Test-Set'");
        $this->db->insert('s_filter', array('name' => 'Test-Set', 'comparable' => 1));
        $data = $this->db->fetchRow("SELECT * FROM s_filter WHERE name = 'Test-Set'");

        Shopware()->Db()->query("DELETE FROM s_filter_options WHERE name LIKE 'Test-Gruppe%'");
        Shopware()->Db()->query("DELETE FROM s_filter_values WHERE value LIKE 'Test-Option%'");

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

            $group['options'] = Shopware()->Db()->fetchAll("SELECT * FROM s_filter_values WHERE optionID = ?", array($group['id']));
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
        return Shopware()->Models()->find(
            'Shopware\Models\Shop\Shop',
            $shopId
        );
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

        Shopware()->Models()->persist($customer);
        Shopware()->Models()->flush($customer);
        Shopware()->Models()->clear();

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

        Shopware()->Models()->persist($tax);
        Shopware()->Models()->flush();
        Shopware()->Models()->clear();

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

        Shopware()->Models()->persist($currency);
        Shopware()->Models()->flush();
        Shopware()->Models()->clear();

        return $currency;
    }


    private function deleteCustomerGroup($key)
    {
        $ids = Shopware()->Db()->fetchCol('SELECT id FROM s_core_customergroups WHERE groupkey = ?', array($key));
        if (!$ids) {
            return;
        }

        foreach ($ids as $id) {
            $customer = Shopware()->Models()->find('Shopware\Models\Customer\Group', $id);
            if (!$customer) continue;
            Shopware()->Models()->remove($customer);
            Shopware()->Models()->flush($customer);
        }
        Shopware()->Models()->clear();
    }

    private function deleteTax($name)
    {
        $ids = Shopware()->Db()->fetchCol("SELECT id FROM s_core_tax WHERE description = ?", array($name));
        if (empty($ids)) return;

        foreach ($ids as $id) {
            $tax = Shopware()->Models()->find('Shopware\Models\Tax\Tax', $id);
            Shopware()->Models()->remove($tax);
            Shopware()->Models()->flush();
        }
        Shopware()->Models()->clear();
    }

    private function deleteCurrency($name)
    {
        $ids = Shopware()->Db()->fetchCol("SELECT id FROM s_core_currencies WHERE name = ?", array($name));
        if (empty($ids)) return;

        foreach ($ids as $id) {
            $tax = Shopware()->Models()->find('Shopware\Models\Shop\Currency', $id);
            Shopware()->Models()->remove($tax);
            Shopware()->Models()->flush();
        }
        Shopware()->Models()->clear();
    }

    private function deleteConfigurator()
    {
        $ids = Shopware()->Db()->fetchCol(
            "SELECT id from s_article_configurator_groups WHERE name LIKE 'Unit-Test%'"
        );

        foreach ($ids as $id) {
            $group = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Group', $id);
            Shopware()->Models()->remove($group);
            Shopware()->Models()->flush();
            Shopware()->Models()->clear();
        }

        $ids = Shopware()->Db()->fetchCol(
            "SELECT id from s_article_configurator_options WHERE name LIKE 'Unit-Test%'"
        );

        foreach ($ids as $id) {
            $group = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Option', $id);
            Shopware()->Models()->remove($group);
            Shopware()->Models()->flush();
            Shopware()->Models()->clear();
        }
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

    private function createConfigurator($groupCount, $optionCount)
    {
        $this->deleteConfigurator();

        $groups = array();

        for ($i = 1; $i <= $groupCount; $i++) {

            $group = new Models\Article\Configurator\Group();
            $group->setName('Unit-Test' . $i);
            $group->setPosition($i);

            $options = array();
            for ($i2 = 1; $i2 <= $optionCount; $i2++) {

                $option = new Models\Article\Configurator\Option();
                $option->setName('Unit-Test' . $i2);
                $option->setPosition($i2);
                $option->setGroup($group);

                $options[] = $option;
            }
            $group->setOptions($options);

            Shopware()->Models()->persist($group);
            Shopware()->Models()->flush();
            Shopware()->Models()->clear();

            $options = array();
            foreach ($group->getOptions() as $option) {
                $options[] = array(
                    'id' => $option->getId(),
                    'name' => $option->getName()
                );
            }
            $groups[] = array(
                'id' => $group->getId(),
                'name' => $group->getName(),
                'options' => $options
            );
        }

        return array(
            'name' => 'Unit test configurator set',
            'groups' => $groups
        );
    }

    /**
     * Helper function which creates all variants for
     * the passed groups with options.
     *
     * @param $groups
     * @param array $data
     * @param array $groupMapping
     * @param array $optionMapping
     * @return array
     */
    private function generateVariants(
        $groups,
        $data = array(),
        $groupMapping = array('key' => 'groupId', 'value' => 'id'),
        $optionMapping = array('key' => 'option', 'value' => 'name')
    ) {
        $options = array();

        $groupArrayKey = $groupMapping['key'];
        $groupValuesKey = $groupMapping['value'];
        $optionArrayKey = $optionMapping['key'];
        $optionValuesKey = $optionMapping['value'];

        foreach ($groups as $group) {
            $groupOptions = array();
            foreach ($group['options'] as $option) {
                $groupOptions[] = array(
                    $groupArrayKey => $group[$groupValuesKey],
                    $optionArrayKey => $option[$optionValuesKey]
                );
            }
            $options[] = $groupOptions;
        }

        $combinations = $this->combinations($options);
        $combinations = $this->cleanUpCombinations($combinations);

        $variants = array();
        foreach ($combinations as $combination) {
            $variantData = array_merge(
                array('number' => 'Unit-Test-Variant-' . uniqid(),),
                $data
            );

            $variant = $this->getVariantData($variantData);

            $variant['configuratorOptions'] = $combination;
            $variants[] = $variant;
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