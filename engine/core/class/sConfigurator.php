<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

class sConfigurator
{
    const TYPE_STANDARD = 0;
    const TYPE_SELECTION = 1;
    const TYPE_TABLE = 2;

    /**
     * The shopware system object.
     *
     * @var sSystem
     */
    public $sSYSTEM;

    /**
     * @var sArticles
     */
    public $module;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->module = Shopware()->Modules()->Articles();
    }

    /**
     * Helper function to prevent a lazy loading of all inversed sides one to one
     * associations of an article.
     *
     * @param $articleId
     *
     * @return mixed
     */
    protected function getSingleArticle($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('article'))
                ->from('Shopware\Models\Article\Article', 'article')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Returns all configurator options for the passed variant id.
     * @param $variantId
     *
     * @return array
     */
    protected function getConfiguratorOptionsForVariantId($variantId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('options'))
                ->from('Shopware\Models\Article\Configurator\Option', 'options')
                ->innerJoin('options.articles', 'details', 'WITH', 'details.id = :variantId')
                ->addOrderBy('options.position')
                ->setParameter('variantId', $variantId);
        return $builder->getQuery()->getArrayResult();
    }

    /**
     * @param      $id
     * @param      $articleData
     * @param bool $recursiveCall
     * @return array
     */
    public function getArticleConfigurator($id, $articleData, $recursiveCall = false)
    {
        $id = intval($id);

        //get posted groups and options
        $selectedItems = $this->sSYSTEM->_POST["group"];

        if (empty($selectedItems)) {
            $selectedItems = array();
        }

        /**@var $repository \Shopware\Models\Article\Repository*/
        $repository = Shopware()->Models()->Article();

        /**@var $article \Shopware\Models\Article\Article*/
        $article = $this->getSingleArticle($id);

        //the data property contains now the configurator set. Set configurator set has the array keys "options" and "groups"
        //where the assigned configurator options and groups are.
        $data = $repository->getArticleConfiguratorSetByArticleIdIndexedByIdsQuery($id)
                ->getArrayResult();

        $data = $data[0]['configuratorSet'];

        $customerGroupKey = $this->sSYSTEM->sUSERGROUP;
        if (empty($customerGroupKey)) {
            $customerGroupKey = 'EK';
        }
        if (empty($data)) {
            return $articleData;
        }

        //first we convert the configurator set settings from the new structure to the old structure.
        $settings = $this->getConfiguratorSettings($data, $article);
        $optionsIds = array();

        $mainDetailOptions = $this->getConfiguratorOptionsForVariantId($article['mainDetailId']);
        //now we iterate all activated options and assign them to the corresponding group.
        foreach ($data['options'] as $option) {
            //the convert functions changes the property names, so we save the ids in internal helper properties.
            $groupId = $option['groupId'];
            $optionId = $option['id'];

            //the groups in the data property indexed by their ids, so we can use "array_key_exists" to check if the group id of the current options exists in our group array.
            if (array_key_exists($groupId, $data['groups'])) {
                //if the group exist, we save the option id into in helper array. This helper array is only used for "configurator - tables".
                $optionsIds[] = $optionId;

                $selected = 0;
                if (empty($selectedItems)) {

                    /**@var $mainDetailOption \Shopware\Models\Article\Configurator\Option*/
                    foreach ($mainDetailOptions as $mainDetailOption) {
                        if ($mainDetailOption['id'] === $optionId) {
                            $selected = 1;
                        }
                    }
                } else {
                    $selected = (int) (array_key_exists($groupId, $selectedItems) && $selectedItems[$groupId] == $optionId);
                }

                //now we convert the configurator option data from the old property structure to new one.
                $option = $this->getConvertedOptionData($option);
                $option = $this->module->sGetTranslation($option, $option['optionID'], 'configuratoroption');
                $option['user_selected'] = $selected;
                $option['selected'] = $selected;

                //now we assign the option into the options array element to corresponding group.
                $data['groups'][$groupId]['options'][$optionId] = $option;
            }
        }

        //now we iterate all groups to convert them from the old property structure to new one.
        $sConfigurator = array();
        foreach ($data['groups'] as $group) {
            $data = $this->getConvertGroupData($group);
            $isSelected = (int) array_key_exists($group['id'], $selectedItems) && !empty($selectedItems[$group['id']]);
            //if the current group id exists in the post data, the group was selected already.
            $data['user_selected'] = $isSelected;
            $data['selected'] = $isSelected;
            $data = $this->module->sGetTranslation($data, $group['id'], 'configuratorgroup');
            $sConfigurator[] = $data;
        }

        /**
         * If the configurator set is configured as a table configurator, we have to create the "table structure array"
         * this array looks like this:
         * ['SIZE XXL']
         *     ['COLOR YELLOW']
         *     ['COLOR GREEN']
         *     ['COLOR RED']
         * ['SIZE L']
         *     ['COLOR YELLOW']
         *     ['COLOR GREEN']
         *     ['COLOR RED']
         * ...
         */
        $sConfiguratorValues = array();
        if ($settings['type'] == self::TYPE_TABLE) {
            $sConfiguratorValues = $this->getTableConfiguratorData($id, $optionsIds, $articleData, $article, $customerGroupKey);
        }

        //now we check if the sQuantity property is set in the post.
        $quantity = 1;
        if (!empty($this->sSYSTEM->_POST["sQuantity"])&&is_numeric($this->sSYSTEM->_POST["sQuantity"])) {
            $quantity = (int) $this->sSYSTEM->_POST["sQuantity"];
        }
        $articleData["quantity"] = $quantity;

        //if the posted quantity is lesser then the min purchase we have to set the min purchase as quantity
        if(empty($articleData["quantity"])||$articleData["quantity"]<$articleData["minpurchase"])
            $articleData["quantity"] = $articleData["minpurchase"];

        $selected = null;
        //if some items was selected from the user, we have to select the first available variant
        if (!empty($selectedItems)) {
            $builder = $this->getSelectionQueryBuilder($selectedItems);
            $builder->setParameter('articleId', $id);
            $builder->setParameter('customerGroup', $customerGroupKey);
            $selected = $builder->getQuery()->getArrayResult();

            if (empty($selected)) {
                $builder = $this->getSelectionQueryBuilder($selectedItems);
                $builder->setParameter('articleId', $id);
                $builder->setParameter('customerGroup', 'EK');
                $selected = $builder->getQuery()->getArrayResult();
            }

            //we can only set one variant as select, so we select the first one
            $detailData = $selected[0];
            if (!empty($detailData)) {
                if ($article['lastStock'] && $detailData['inStock'] < 1) {
                    $detailData['active'] = 0;
                }
                if (empty($detailData['prices'])) {
                    $detailData['prices'] = $this->getDefaultPrices($detailData['id']);
                }
                $detailData['prices'] = $this->getConvertedPrices($detailData['prices'], $articleData["tax"], $articleData["taxID"]);

                $selected = $this->getConvertedDetail($detailData);
                $attributeIndex = 1;
                //at least we creates the old "attr1-X" attributes with the option id as value.
                foreach ($detailData['configuratorOptions'] as $option) {
                    $attribute = 'attr' . $attributeIndex;
                    $selected[$attribute] = $option['id'];
                    $attributeIndex++;
                }
            }
        }

        if (!empty($selectedItems) && empty($selected)) {
            if ($settings['type'] == self::TYPE_STANDARD) {
                unset($this->sSYSTEM->_POST["group"]);
            } elseif ($settings['type'] == self::TYPE_SELECTION) {
                array_pop($this->sSYSTEM->_POST["group"]);
            }
            if (count($this->sSYSTEM->_POST["group"])) {
                return $this->getArticleConfigurator($id, $articleData, true);
            }
        }

        if (empty($selected)) {
            // Limiting the results with setMaxResults(1) will result in only one price being selected SW-4465
            $query = $repository->getConfiguratorTablePreSelectionItemQuery($id, $customerGroupKey, ($article['lastStock'] === 1));
            $query->setFirstResult(0)->setMaxResults(1);
            $detail = $this->getOneOrNullResult($query);

            if ($article['lastStock'] && $detail['inStock'] < 1) {
                $detail['active'] = 0;
            }
            $preSelectedOptions = $detail['configuratorOptions'];

            foreach ($sConfigurator as &$group) {
                $preSelectedOption = $preSelectedOptions[$group['groupID']];
                $id = $preSelectedOption['id'];
                if (array_key_exists($id, $group['values'])) {
                    $group['values'][$preSelectedOption['id']]['user_selected'] = 1;
                    $group['values'][$preSelectedOption['id']]['selected'] = 1;
                }
            }
            if (!empty($detail)) {
                if (empty($detail['prices'])) {
                    $detail['prices'] = $this->getDefaultPrices($detail['id']);
                }

                $detail['prices'] = $this->getConvertedPrices($detail['prices'], $articleData["tax"], $articleData["taxID"]);
                $selected = $this->getConvertedDetail($detail);
            }
        }

        //if one variant are selected we have to calculate the prices and get the price
        if (!empty($selected)) {
            $selectedPrice = $selected['price'][0];
            foreach ($selected['price'] as $price) {
                if (!is_numeric($price['to'])) {
                    $selectedPrice = $price;
                    break;
                } elseif ($quantity < $price['to']) {
                    $selectedPrice = $price;
                    break;
                }
            }

            if (!empty($articleData['pricegroupActive'])) {
                $articleData['sBlockPrices'] = $this->module->sGetPricegroupDiscount(
                    $this->sSYSTEM->sUSERGROUP, $articleData["pricegroupID"],
                    $selected['price'][0]['priceNet'], 1, true, $articleData
                );
            } elseif (count($selected['price']) > 1) {
                $articleData['sBlockPrices'] = $selected['price'];
            } else {
                $articleData['sBlockPrices'] = array();
            }
            if ($selected['kind'] > 1) {
                $articleData = $this->mergeSelectedAndArticleData($articleData, $selected, $selectedPrice);
                $articleData = $this->module->sGetTranslation($articleData, $selected['valueID'], 'variant');
            } else {
                $articleData["active"] = $selected["active"];
            }
            $articleData["sConfiguratorSelection"] = $selected;
        }

        $articleData['sConfiguratorValues'] = $sConfiguratorValues;
        $articleData['sConfigurator'] = $sConfigurator;
        $articleData['sConfiguratorSettings'] = $settings;
        if ($recursiveCall) {
            $articleData['sError']['variantNotAvailable'] = true;
        }
        return $articleData;
    }

    /**
     * Helper function to get one or null result for the passed query object.
     * @param $query
     *
     * @return mixed
     */
    private function getOneOrNullResult($query)
    {
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = Shopware()->Models()->createPaginator($query);

        return $paginator->getIterator()->current();
    }

    /**
     * @param array $selectedItems
     * @return Shopware\Components\Model\QueryBuilder
     */
    public function getSelectionQueryBuilder($selectedItems = array())
    {
        //first we create a small query builder with the article details and the prices
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('detail', 'prices'))
                ->from('Shopware\Models\Article\Detail', 'detail')
                ->leftJoin('detail.prices', 'prices')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->where('detail.articleId = :articleId')
                ->andWhere('customerGroup.key = :customerGroup')
                ->orderBy('detail.kind', 'ASC')
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC');

        //now we iterate all selected groups with their options to filter the available variant
        foreach ($selectedItems as $key => $optionId) {
            $optionId = (int)$optionId;
            if (empty($optionId)) {
                continue;
            }
            $alias = 'option' . (int)$key;
            $builder->addSelect($alias);
            $builder->innerJoin('detail.configuratorOptions', $alias);
            $builder->andWhere($alias . '.id = :' . $alias);
            $builder->addOrderBy($alias . '.position', 'ASC');
            $builder->setParameter($alias, $optionId);
        }
        return $builder;
    }

    protected function getDefaultPrices($detailId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('prices'))
                ->from('Shopware\Models\Article\Price', 'prices')
                ->where('prices.articleDetailsId = :detailId')
                ->andWhere('prices.customerGroupKey = :key')
                ->setParameter('detailId', $detailId)
                ->setParameter('key', 'EK')
                ->orderBy('prices.from', 'ASC')
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * Internal helper function to set the variant data of the selected item into the article data array.
     * @param $articleData
     * @param $selected
     * @param $selectedPrice
     * @return mixed
     */
    private function mergeSelectedAndArticleData($articleData, $selected, $selectedPrice)
    {
        $articleData['minpurchase'] = empty($selected['minpurchase']) ? $articleData['minpurchase'] : $selected['minpurchase'];
        $articleData['maxpurchase'] = empty($selected['maxpurchase']) ? $articleData['maxpurchase'] : $selected['maxpurchase'];
        $articleData['purchasesteps'] = empty($selected['purchasesteps']) ? $articleData['purchasesteps'] : $selected['purchasesteps'];
        $articleData['packunit'] = empty($selected['packunit']) ? $articleData['packunit'] : $selected['packunit'];

        // Calculating price for reference-unit
        if ($selected["purchaseunit"] > 0 && $selected["referenceunit"]) {
            $selected["purchaseunit"] = (float) $selected["purchaseunit"];
            $selected["referenceunit"] = (float) $selected["referenceunit"];
            $articleData['referenceprice'] = Shopware()->Modules()->Articles()->calculateReferencePrice(
                $selected["price"][0]["price"],
                $selected["purchaseunit"],
                $selected["referenceunit"]
            );
        }
        //these information should always been overwritten even if they're empty
        $articleData['purchaseunit'] = $selected['purchaseunit'];
        $articleData['referenceunit'] = $selected['referenceunit'];

        if ($selected["unitID"]) {
            $articleData["sUnit"] = $this->sSYSTEM->sMODULES['sArticles']->sGetUnit($selected["unitID"]);
        } else {
            $articleData["sUnit"] = null;
        }

        $articleData['pricegroup'] = $selectedPrice['customerGroupKey'];
        $articleData["pricenumeric"] = $selectedPrice['pricenumeric'];
        $articleData["price"] = $selectedPrice['price'];
        $articleData["pseudoprice"] = $selectedPrice['pseudoPrice'];

        $articleData["articleDetailsID"] = $selected["id"];
        $articleData["ordernumber"] = $selected["ordernumber"];
        $articleData["additionaltext"] = $selected["additionaltext"];
        $articleData["instock"] = $selected["instock"];
        $articleData["active"] = $selected["active"];
        $articleData["suppliernumber"] = empty($selected['suppliernumber']) ? $articleData['suppliernumber'] : $selected['suppliernumber'];
        $articleData["stockmin"] = empty($selected['stockmin']) ? $articleData['stockmin'] : $selected['stockmin'];
        $articleData["weight"] = empty($selected['weight']) ? $articleData['weight'] : $selected['weight'];
        $articleData["width"] = empty($selected['width']) ? $articleData['width'] : $selected['width'];
        $articleData["length"] = empty($selected['length']) ? $articleData['length'] : $selected['length'];
        $articleData["height"] = empty($selected['height']) ? $articleData['height'] : $selected['height'];
        $articleData["ean"] = empty($selected['ean']) ? $articleData['ean'] : $selected['ean'];
        $articleData["releasedate"] = empty($selected['releasedate']) ? $articleData['releasedate'] : $selected['releasedate'];
        $articleData["shippingtime"] = empty($selected['shippingtime']) ? $articleData['shippingtime'] : $selected['shippingtime'];
        $articleData["shippingfree"] = isset($selected['shippingfree']) ? $selected['shippingfree'] : $articleData['shippingfree'];

        $articleData["sReleasedate"] = $articleData["releasedate"];

        if (!empty($selected['attributes'])) {
            foreach ($selected['attributes'] as $key => $value) {
                $articleData[$key] = $value;
            }
        }

        return $articleData;
    }

    /**
     * @param $data
     * @param $tax
     * @param $taxId
     * @return array
     */
    private function getConvertedPrices($data, $tax, $taxId)
    {
        $prices = array();
        //iterate price to calculate the gross price.
        foreach ($data as $price) {
            $price['priceNet'] = $price["price"];
            $price['price'] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPrice($price["price"],$tax,$taxId);
            $price['pricenumeric'] =  $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($price["price"],$tax,false,false,$taxId,false);
            $prices[] = $price;
        }

        return $prices;
    }

    /**
     * Returns the "sConfiguratorValues" array element if the passed configurator set is and table configurator set.
     * Example return value:
     * <code>
     * ARRAY (
     *    ['SIZE XXL']
     *        ['COLOR YELLOW']
     *        ['COLOR GREEN']
     *        ['COLOR RED']
     *    ['SIZE L']
     *        ['COLOR YELLOW']
     *        ['COLOR GREEN']
     *        ['COLOR RED']
     * )
     * </code>
     * @param $id
     * @param $optionsIds
     * @param $articleData
     * @param $article \Shopware\Models\Article\Article
     * @param $customerGroupKey
     * @return array
     */
    private function getTableConfiguratorData($id, $optionsIds, $articleData, $article, $customerGroupKey)
    {
        //get posted groups and options
        $selectedItems = $this->sSYSTEM->_POST["group"];
        if (empty($selectedItems)) {
            $selectedItems = array();
        }

        /**@var $repository \Shopware\Models\Article\Repository*/
        $repository = Shopware()->Models()->Article();
        $groups = $repository->getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQuery($optionsIds)
                ->getArrayResult();

        //now we check if the sQuantity property is set in the post.
        $quantity = 1;
        if (!empty($this->sSYSTEM->_POST["sQuantity"])&&is_numeric($this->sSYSTEM->_POST["sQuantity"])) {
            $quantity = (int) $this->sSYSTEM->_POST["sQuantity"];
        }

        $firstGroup = $groups[0];

        $secondGroup = $groups[1];
        $resultGroups = array();
        foreach ($firstGroup['options'] as $firstKey => $firstGroupOption) {
            $mergedGroups = array();
            foreach ($secondGroup['options'] as $secondKey => $secondGroupOption) {
                $detail = $repository->getArticleDetailForTableConfiguratorOptionCombinationQuery($id, $firstKey, $secondKey, $article, $customerGroupKey)
                                     ->getArrayResult();
                $detail = $detail[0];

                if (empty($detail['prices'])) {
                    $detail['prices'] = $this->getDefaultPrices($detail['id']);
                }
                $selectedPrice = null;
                //iterate price to calculate the gross price.
                foreach ($detail['prices'] as $key => $price) {
                    $price['price'] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPrice($price["price"],$articleData["tax"],$articleData["taxID"]);
                    if (!is_numeric($price['to'])) {
                        $selectedPrice = $price;
                    } elseif ($quantity < $price['to']) {
                        $selectedPrice = $price;
                    }
                    $detail['prices'][$key] = $price;
                }
                if ($selectedPrice === null) {
                    $selectedPrice = $detail['prices'][0];
                }

                $selected = false;
                if (array_key_exists($firstGroup['id'], $selectedItems) &&
                        array_key_exists($secondGroup['id'], $selectedItems)) {
                    $selectedOptionIds = array($selectedItems[$secondGroup['id']], $selectedItems[$firstGroup['id']]);
                    $selected = (int) (in_array($firstKey, $selectedOptionIds) && in_array($secondKey, $selectedOptionIds));
                }
                if (empty($selectedItems)) {
                    $selected = (int) ($detail['kind'] === 1);
                }
                $standard = 0;
                if ($detail['kind'] === 1) {
                    $standard = 1;
                }

                $mergedGroup = array(
                    'value1' => $firstKey,
                    'value2' => $secondKey,
                    'valueID' => $detail['id'],
                    'standard' => $standard,
                    'active' => $detail['active'],
                    'ordernumber' => $detail['number'],
                    'price' => $selectedPrice['price'],
                    'user_selected' => $selected,
                    'selected' => $selected,
                    'linkBasket' => $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sAdd=".$detail["number"]
                );
                if (count($detail['prices']) > 0) {
                    $mergedGroup['sBlockPrices'] = $detail['prices'];
                }
                $mergedGroups[$secondKey] = $mergedGroup;
            }
            $resultGroups[$firstKey] = $mergedGroups;
        }

        return $resultGroups;
    }

    /**
     * Shopware 3.5 <=> Shopware 4.0 Mapping function. Converts the new article detail properties to the old property names.
     * @param $data
     * @return array
     */
    private function getConvertedDetail($data)
    {
        $detail = array(
            'valueID' => $data['id'],
            'active' => $data['active'],
            'kind' => $data['kind'],
            'ordernumber' => $data['number'],
            'instock' => $data['inStock'],
            'price' => $data['prices'],
            //additional information which was not given by the old configurator structure.
            'id' => $data['id'],
            'articleID' => $data['articleId'],
            'unitID' => $data['unitId'],
            'suppliernumber' => $data['supplierNumber'],
            'additionaltext' => $data['additionalText'],
            'stockmin' => $data['stockMin'],
            'weight' => $data['weight'],
            'width' => $data['width'],
            'length' => $data['len'],
            'height' => $data['height'],
            'ean' => $data['ean'],
            'position' => $data['position'],
            'minpurchase' => $data['minPurchase'],
            'purchasesteps' => $data['purchaseSteps'],
            'maxpurchase' => $data['maxPurchase'],
            'purchaseunit' => $data['purchaseUnit'],
            'referenceunit' => $data['referenceUnit'],
            'packunit' => $data['packUnit'],
            'shippingfree' => $data['shippingFree'],
            'releasedate' => $data['releaseDate'],
            'shippingtime' => $data['shippingTime']
        );
        $detail['attributes'] = $this->getDetailAttributes($data['id']);

        if (count($data['prices']) > 1) {
            $detail['sBlockPrices'] = $data['prices'];
        }

        return $detail;
    }

    /**
     * Helper function to get the detail attributes for the passed detail id.
     * @param $detailId
     *
     * @return mixed
     */
    protected function getDetailAttributes($detailId)
    {
        if (empty($detailId)) {
            return array();
        }
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('attribute'))
                ->from('Shopware\Models\Attribute\Article', 'attribute')
                ->where('attribute.articleDetailId = :articleDetailId')
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->setParameters(array('articleDetailId' => $detailId));

        return $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Shopware 3.5 <=> Shopware 4.0 Mapping function. Converts the new configurator group properties to the old
     * property names.
     * @param $data
     * @param $article \Shopware\Models\Article\Article
     * @return array
     */
    private function getConvertedSettings($data, $article)
    {
        $settings = array(
            'articleID' => $article['id'],
            'type' => $data['type'],
            'template' => $data['template'],
            'instock' => $article['lastStock'],
            'upprice' => 0
        );
        return $settings;
    }

    /**
     * Shopware 3.5 <=> Shopware 4.0 Mapping function. Converts the new configurator group properties to the old
     * property names.
     * @param $data
     * @return array
     */
    private function getConvertGroupData($data)
    {
        return array(
            'groupID' => $data['id'],
            'groupname' => $data['name'],
            'groupnameOrig' => $data['name'],
            'groupdescription' => $data['description'],
            'groupdescriptionOrig' => $data['description'],
            'groupimage' => '',
            'postion' => $data['position'],
            'selected_value' => $data['selected_value'],
            'selected' => $data['selected'],
            'values' => $data['options']
        );
    }

    /**
     * Shopware 3.5 <=> Shopware 4.0 Mapping function. Converts the new configurator option properties to the old
     * property names.
     * @param $data
     * @return array
     */
    protected function getConvertedOptionData($data)
    {
        return array(
            'optionID' => $data['id'],
            'groupID' => $data['groupId'],
            'optionnameOrig' => $data['name'],
            'optionname' => $data['name'],
            'optionposition' => $data['position'],
            'optionactive' => 1,
        );
    }

    /**
     * @param $data array
     * @param $article \Shopware\Models\Article\Article
     * @return array
     */
    protected function getConfiguratorSettings($data, $article)
    {
        $settings = $this->getConvertedSettings($data, $article);
        //if no template configured use default templates.
        if (empty($settings['template'])) {

            //switch the template for the different configurator types.
            if ($settings["type"]== self::TYPE_SELECTION) {
                //Selection configurator
                $settings["template"] = "article_config_step.tpl";
            } elseif ($settings["type"]== self::TYPE_TABLE) {
                //Table configurator
                $settings["template"] = "article_config_table.tpl";
            } else {
                //Other configurator types
                $settings["template"] = "article_config_upprice.tpl";
            }
        }
        return $settings;
    }

    /**
     * Returns the group options for the product configurator.
     *
     * @param int $id
     * @param array $article
     * @return array
     */
    public function sGetArticleConfig($id, $article)
    {
        return $this->getArticleConfigurator($id, $article);
    }


    /**
     * Returns the translation of an single configurator option.
     *
     * @deprecated
     * @param int $optionId
     * @param string $fallback
     * @return mixed
     */
    public function getOptionTranslation($optionId, $fallback)
    {
        $sql= "SELECT objectdata
               FROM s_core_translations
               WHERE objecttype = ?
               AND objectkey = ?
               AND objectlanguage = ?";

        $data = Shopware()->Db()->fetchOne($sql, array('configuratoroption', $optionId, Shopware()->Shop()->getId()));
        if ($data) {
            return unserialize($data);
        } else {
            return $fallback;
        }
    }

    /**
     * Returns the translation of an single configurator group.
     *
     * @deprecated
     *
     * @param int $groupId
     * @param string $fallback
     *
     * @return mixed
     */
    public function getGroupTranslation($groupId, $fallback)
    {
        $sql= "SELECT objectdata
               FROM s_core_translations
               WHERE objecttype = ?
               AND objectkey = ?
               AND objectlanguage = ?";
        $data = Shopware()->Db()->fetchOne($sql, array('configuratorgroup', $groupId, Shopware()->Shop()->getId()));

        if ($data) {
            return unserialize($data);
        } else {
            return $fallback;
        }
    }

}
