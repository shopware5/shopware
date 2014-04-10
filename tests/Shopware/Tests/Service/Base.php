<?php

class Shopware_Tests_Service_Base extends Enlight_Components_Test_TestCase
{
    /**
     * @return \Shopware\Models\Article\Repository
     */
    protected function getDetailRepo()
    {
        return Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');
    }

    /**
     * @return \Shopware\Models\Shop\Shop
     */
    protected function getDefaultShop()
    {
        $id = Shopware()->Db()->fetchOne(
            'SELECT id FROM s_core_shops WHERE `default` = 1'
        );

        return Shopware()->Models()->find(
            'Shopware\Models\Shop\Shop',
            $id
        );
    }

    /**
     * @return \Shopware\Components\Api\Resource\Article
     */
    protected function getApi()
    {
        $api = new \Shopware\Components\Api\Resource\Article();
        $api->setManager(Shopware()->Models());
        return $api;
    }

    protected function removeArticle($number)
    {
        $article = $this->getDetailRepo()->findOneBy(array('number' => $number));
        if ($article) {
            Shopware()->Models()->remove($article);
            Shopware()->Models()->flush($article);
            Shopware()->Models()->clear();
        }
    }

    protected function createArticle($data)
    {
        $this->removeArticle($data['mainDetail']['number']);
        return $this->getApi()->create($data);
    }

    protected function getProduct($number, $state)
    {
        return Shopware()->Container()->get('product_service')
            ->getMini($number, $state);
    }

    protected function getHighTax()
    {
        return Shopware()->Models()->find(
            'Shopware\Models\Tax\Tax',
            1
        );
    }

    protected function getLowTax()
    {
        return Shopware()->Models()->find(
            'Shopware\Models\Tax\Tax',
            4
        );
    }


    protected function createGlobalState(
        \Shopware\Models\Customer\Group $group,
        \Shopware\Models\Shop\Shop $shop,
        \Shopware\Models\Tax\Tax $tax
    ) {
        $state = new \Shopware\Struct\GlobalState();

        $customerGroup = new \Shopware\Struct\CustomerGroup();
        $customerGroup->setKey($group->getKey());
        $customerGroup->setUseDiscount(true);
        $customerGroup->setId($group->getId());
        $customerGroup->setPercentageDiscount($group->getDiscount());
        $customerGroup->setDisplayGross($group->getTax());


        $state->setCurrentCustomerGroup($customerGroup);
        $state->setFallbackCustomerGroup($customerGroup);

        $state->setCurrency(new \Shopware\Struct\Currency());
        $state->getCurrency()->setFactor(1);


        $state->setShop(new \Shopware\Struct\Shop());
        $state->getShop()->setId($shop->getId());

        $state->setTax(new \Shopware\Struct\Tax());
        $state->getTax()->setId($tax->getId());
        $state->getTax()->setTax($tax->getTax());

        return $state;
    }

    protected function getBaseData()
    {
        return array(
            'name' => 'Refactor test',
            'supplierId' => 1,
            'taxId' => 1,
            'active' => true
        );
    }

    protected function getSimpleDetail($number)
    {
        return array(
            'number' => $number,
            'inStock' => 20,
            'active' => true
        );
    }

    protected function getUnitData($purchaseunit = 500, $referenceunit = 100, $minpurchase = 1)
    {
        return array(
            'packunit' => 'Beutel',
            'purchaseunit' => $purchaseunit,
            'referenceunit' => $referenceunit,
            'minpurchase' => $minpurchase,
            'maxpurchase' => 20,
            'purchasesteps' => 2,
            'unit' => array(
                'name' => 'Gramm'
            )
        );
    }

    protected function getScaledPrices($group = 'EK', $priceOffset = 0)
    {
        return array(
            array(
                'from' => 1,
                'to' => 10,
                'price' => $priceOffset + 1000.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 1200
            ),
            array(
                'from' => 11,
                'to' => 20,
                'price' => $priceOffset + 750.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 950
            ),
            array(
                'from' => 21,
                'to' => 'beliebig',
                'price' => $priceOffset + 500.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 700
            )
        );
    }


    /**
     * @param $key
     * @param int $discount
     * @param bool $displayGross
     * @return \Shopware\Models\Customer\Group
     */
    protected function createCustomerGroup(
        $key,
        $discount = 10,
        $displayGross = true
    ) {
        $this->removeCustomerGroup($key);

        $customer = new \Shopware\Models\Customer\Group();

        $customer->fromArray(array(
            'key' => $key,
            'name' => 'Test group',
            'tax' => $displayGross,
            'taxInput' => true,
            'mode' => true,
            'discount' => $discount
        ));

        Shopware()->Models()->persist($customer);
        Shopware()->Models()->flush($customer);
        Shopware()->Models()->clear();

        return $customer;
    }

    protected function removeCustomerGroup($key)
    {
        $ids = Shopware()->Db()->fetchCol('SELECT id FROM s_core_customergroups WHERE groupkey = ?', array($key));
        if (!$ids) return;

        foreach ($ids as $id) {
            $customer = Shopware()->Models()->find('Shopware\Models\Customer\Group', $id);
            if (!$customer) continue;
            Shopware()->Models()->remove($customer);
            Shopware()->Models()->flush($customer);
        }
        Shopware()->Models()->clear();
    }


    protected function removePriceGroup()
    {
        $ids = Shopware()->Db()->fetchCol("SELECT id FROM s_core_pricegroups WHERE description = 'TEST'");
        foreach($ids as $id) {
            $group = Shopware()->Models()->find('Shopware\Models\Price\Group', $id);
            Shopware()->Models()->remove($group);
            Shopware()->Models()->flush();
            Shopware()->Models()->clear();
        }
    }

    protected function createPriceGroup($scaledDiscounts = array())
    {
        $this->removePriceGroup();

        $priceGroup = new Shopware\Models\Price\Group();
        $priceGroup->setName('TEST');

        $repo = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group');

        $discounts = array();
        foreach($scaledDiscounts as $data) {
            $discount = new \Shopware\Models\Price\Discount();

            $discount->setCustomerGroup(
                $repo->findOneBy(array('key' => $data['customerGroup']))
            );

            $discount->setGroup($priceGroup);

            $discount->setStart($data['quantity']);

            $discount->setDiscount($data['discount']);

            $discounts[] = $discount;
        }

        $priceGroup->setDiscounts($discounts);

        Shopware()->Models()->persist($priceGroup);
        Shopware()->Models()->flush();
        Shopware()->Models()->clear();

        return $priceGroup;
    }

    private function removeConfigurator() {
        $ids = Shopware()->Db()->fetchCol(
            "SELECT id from s_article_configurator_groups WHERE name LIKE 'TEST%'"
        );

        foreach($ids as $id) {
            $group = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Group', $id);
            Shopware()->Models()->remove($group);
            Shopware()->Models()->flush();
            Shopware()->Models()->clear();
        }

        $ids = Shopware()->Db()->fetchCol(
            "SELECT id from s_article_configurator_options WHERE name LIKE 'TEST%'"
        );

        foreach($ids as $id) {
            $group = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Option', $id);
            Shopware()->Models()->remove($group);
            Shopware()->Models()->flush();
            Shopware()->Models()->clear();
        }
    }

    protected function getSimpleConfigurator($groupCount, $optionCount)
    {
        $this->removeConfigurator();

        $groups = array();

        for($i=1; $i<=$groupCount; $i++) {

            $group = new \Shopware\Models\Article\Configurator\Group();
            $group->setName('TEST' . $i);
            $group->setPosition($i);

            $options = array();
            for($i2=1; $i2<=$optionCount; $i2++) {

                $option = new \Shopware\Models\Article\Configurator\Option();
                $option->setName('TEST' . $i2);
                $option->setPosition($i2);
                $option->setGroup($group);

                $options[] = $option;
            }
            $group->setOptions($options);

            Shopware()->Models()->persist($group);
            Shopware()->Models()->flush();
            Shopware()->Models()->clear();

            $options = array();
            foreach($group->getOptions() as $option) {
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
            'name' => 'Configurator set',
            'groups' => $groups
        );
    }


    /**
     * Helper function which creates all variants for
     * the passed groups with options.
     * @param $groups
     * @param array $data
     * @param array $groupMapping
     * @param array $optionMapping
     * @return array
     */
    protected function generateVariants(
        $groups,
        $data = array(),
        $groupMapping = array('key' => 'groupId', 'value' => 'id'),
        $optionMapping = array('key' => 'option', 'value' => 'name')
    )
    {
        $options = array();

        $groupArrayKey = $groupMapping['key'];
        $groupValuesKey = $groupMapping['value'];
        $optionArrayKey = $optionMapping['key'];
        $optionValuesKey = $optionMapping['value'];

        foreach($groups as $group) {
            $groupOptions = array();
            foreach($group['options'] as $option) {
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
        foreach($combinations as $combination) {
            $variant = $this->getSimpleDetail('CONFIGURATOR-' . uniqid());
            $variant = array_merge($variant, $data);

            $variant['configuratorOptions'] = $combination;
            $variants[] = $variant;
        }
        return $variants;
    }


    /**
     * Helper function which combines all array elements
     * of the passed arrays.
     *
     * @param $arrays
     * @param int $i
     * @return array
     */
    protected function combinations($arrays, $i = 0)
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
     * Combinations merge the result of dimensional arrays not perfectly
     * so we have to clean up the first array level.
     * @param $combinations
     * @return mixed
     */
    protected function cleanUpCombinations($combinations)
    {
        foreach($combinations as &$combination) {
            $combination[] = array('option' => $combination['option'], 'groupId' => $combination['groupId']);
            unset($combination['groupId']);
            unset($combination['option']);
        }
        return $combinations;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

}