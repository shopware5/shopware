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

/**
 * Shopware Config Controller
 */
class Shopware_Controllers_Backend_Config extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Components\Model\ModelRepository[]
     */
    public static $repositories = null;

    protected function initAcl()
    {
        $this->addAclPermission('getNavigation', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getForm', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getTableList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getValues', 'read', 'Insufficient Permissions');

        $this->addAclPermission('saveForm', 'update', 'Insufficient Permissions');
        $this->addAclPermission('saveValues', 'update', 'Insufficient Permissions');
        $this->addAclPermission('saveTableValues', 'update', 'Insufficient Permissions');

        $this->addAclPermission('deleteValues', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('deleteTableValues', 'delete', 'Insufficient Permissions');
    }

    /**
     * Return the config form navigation
     */
    public function getNavigationAction()
    {
        $node = (int) $this->Request()->getParam('node');
        $filter = $this->Request()->getParam('filter');
        $repository = $this->getRepository('form');

        $user = Shopware()->Container()->get('Auth')->getIdentity();
        /** @var $locale \Shopware\Models\Shop\Locale */
        $locale = $user->locale;

        /** @var $builder \Shopware\Components\Model\QueryBuilder */
        $builder = $repository->createQueryBuilder('form')
            ->leftJoin('form.elements', 'element')
            ->leftJoin('element.translations', 'elementTranslation', \Doctrine\ORM\Query\Expr\Join::WITH, 'elementTranslation.localeId = :localeId')
            ->leftJoin('form.translations', 'translation', \Doctrine\ORM\Query\Expr\Join::WITH, 'translation.localeId = :localeId')
            ->leftJoin('form.children', 'children')
            ->select(array(
                'form.id',
                'IFNULL(translation.label,IFNULL(form.label, form.name)) as label',
                'COUNT(children.id) as childrenCount'
            ))
            ->groupBy('form.id')
            ->setParameter("localeId", $locale->getId());

        // Search forms
        if (isset($filter[0]['property']) && $filter[0]['property'] == 'search') {
            $builder->where('form.name LIKE :search')
                ->orWhere('form.label LIKE :search')
                ->orWhere('translation.label LIKE :search')
                ->orWhere('element.name LIKE :search')
                ->orWhere('element.label LIKE :search')
                ->orWhere('elementTranslation.label LIKE :search')
                ->setParameter('search', $filter[0]['value']);
            $builder->having('childrenCount = 0')
                ->andWhere('form.parentId IS NOT NULL');
        } elseif (!$node) { // Main forms
            $builder->where('form.parentId IS NULL');
            $builder->having('childrenCount > 0');
        } else { // Child forms
            $builder->where('form.parentId = :id')
                ->setParameter('id', $node);
        }

        $data = $builder->getQuery()->getArrayResult();
        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ));
    }

    /**
     * Returns a form with values and elements
     */
    public function getFormAction()
    {
        $repository = $this->getRepository('form');

        $user = Shopware()->Container()->get('Auth')->getIdentity();
        /** @var $locale \Shopware\Models\Shop\Locale */
        $locale = $user->locale;
        $language = $locale->toString();

        /** @var $builder \Shopware\Components\Model\QueryBuilder */
        $builder = $repository->createQueryBuilder('form')
            ->leftJoin('form.elements', 'element')
            ->leftJoin('form.translations', 'formTranslation', \Doctrine\ORM\Query\Expr\Join::WITH, 'formTranslation.localeId = :localeId')
            ->leftJoin('element.translations', 'elementTranslation', \Doctrine\ORM\Query\Expr\Join::WITH, 'elementTranslation.localeId = :localeId')
            ->leftJoin('element.values', 'value')
            ->select(array('form', 'element', 'value', 'elementTranslation', 'formTranslation'))
            ->setParameter("localeId", $locale->getId());

        $builder->addOrderBy((array) $this->Request()->getParam('sort', array()))
            ->addFilter((array) $this->Request()->getParam('filter', array()));

        $data = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        foreach ($data['elements'] as &$values) {
            foreach ($values['translations'] as $array) {
                if ($array['label'] !== null) {
                    $values['label'] = $array['label'];
                }
                if ($array['description'] !== null) {
                    $values['description'] = $array['description'];
                }
            }

            if (!in_array($values['type'], array('select', 'combo'))) {
                continue;
            }

            $values['options']['store'] = $this->translateStore($language, $values['options']['store']);
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ));
    }

    /**
     * Helper function to translate the store of select- and combo-fields
     * Store value will be replaced by the value in the correct language.
     * If there is no matching language in array defined, the first array element will be used.
     * If the store or a value is not an array, it will not be changed.
     *
     * Store should be an array like this:
     *
     * $store = array(
     *              array(1, array('de_DE' => 'Auto', 'en_GB' => 'car')),
     *              array(2, array('de_DE' => 'Hund', 'en_GB' => 'dog')),
     *              array(3, array('de_DE' => 'Katze', 'en_GB' => 'cat'))
     *          );
     *
     * @param string $language
     * @param mixed $store
     * @return mixed
     */
    private function translateStore($language, $store)
    {
        if (!is_array($store)) {
            return $store;
        }

        foreach ($store as &$row) {
            $value = array_pop($row);

            if (!is_array($value)) {
                $row[] = $value;
                continue;
            }

            if (!array_key_exists($language, $value)) {
                $row[] = array_shift($value);
                continue;
            }

            $row[] = $value[$language];
        }

        return $store;
    }

    /**
     * Save values from a config form
     */
    public function saveFormAction()
    {
        $shopRepository = $this->getRepository('shop');
        $elements = $this->Request()->getParam('elements');

        /* @var $defaultShop \Shopware\Models\Shop\Shop */
        $defaultShop = $shopRepository->getDefault();
        if ($defaultShop === null) {
            $this->View()->assign(array('success' => false, 'message' => 'No default shop found. Check your shop configuration'));
            return;
        }

        foreach ($elements as $elementData) {
            /** @var $element Shopware\Models\Config\Element */
            $element = Shopware()->Models()->find(
                'Shopware\Models\Config\Element',
                $elementData['id']
            );

            $removedValues = [];
            foreach ($element->getValues() as $value) {
                Shopware()->Models()->remove($value);
                $removedValues[] = $value;
            }
            Shopware()->Models()->flush($removedValues);

            $values = array();
            foreach ($elementData['values'] as $valueData) {
                /* @var $shop \Shopware\Models\Shop\Shop */
                $shop = $shopRepository->find(
                    $valueData['shopId']
                );
                //  Scope not match
                if (empty($elementData['scope']) && $shop->getId() != $defaultShop->getId()) {
                    continue;
                }

                // Do not save empty checkbox / boolean select values the fallback should be used
                if (($elementData['type'] == "checkbox" || $elementData['type'] == "boolean") && $valueData['value'] === '') {
                    continue;
                }

                // Do not save missing translations
                if ((!isset($valueData['value']) || $valueData['value'] === '') && !empty($elementData['required'])) {
                    continue;
                }

                // Do not save default value
                if ($valueData['value'] === $elementData['value'] && (empty($elementData['scope']) || $shop->getId() == $defaultShop->getId())) {
                    continue;
                }

                // Simple data validation
                if (!$this->validateData($elementData, $valueData['value'])) {
                    continue;
                }

                $valueData['value'] = $this->prepareValue($elementData, $valueData['value']);

                $value = new Shopware\Models\Config\Value();
                $value->setElement($element);
                $value->setShop($shop);
                $value->setValue($valueData['value']);
                $values[$shop->getId()] = $value;
            }

            $this->beforeSaveElement($elementData);

            $values = Shopware()->Events()->filter('Shopware_Controllers_Backend_Config_Before_Save_Config_Element', $values, array(
                'subject' => $this,
                'element' => $element
            ));

            $element->setValues($values);
            Shopware()->Models()->flush($element);

            Shopware()->Events()->notify('Shopware_Controllers_Backend_Config_After_Save_Config_Element', array(
                'subject' => $this,
                'element' => $element
            ));
        }

        $this->View()->assign(array('success' => true));
    }

    /**
     * Return a list of values for extended forms
     */
    public function getListAction()
    {
        /** @var $name string */
        $name = $this->Request()->get('_repositoryClass');
        /** @var $repository Shopware\Components\Model\ModelRepository */
        $repository = $this->getRepository($name);

        if (isset($repository)) {
            $builder = $repository->createQueryBuilder($name);
        }

        switch ($name) {
            case 'shop':
                $builder->leftJoin('shop.main', 'main');
                $builder->select(array(
                    'shop.id as id',
                    'shop.name as name',
                    'shop.host as host',
                    'shop.basePath as basePath',
                    'shop.baseUrl as baseUrl',
                    'shop.default as default',
                    'IFNULL(shop.mainId, shop.id) as orderValue0',
                    'IFNULL(main.default, shop.default) as orderValue1'
                ));
                $builder->addOrderBy('orderValue1', 'DESC');
                $builder->addOrderBy('orderValue0', 'ASC');
                $builder->addOrderBy('shop.host', 'DESC');
                $builder->addOrderBy('name');
                break;
            case 'pageGroup':
                $builder->leftJoin('pageGroup.mapping', 'mapping');
                $builder->addSelect(array(
                    'PARTIAL mapping.{id,name}'
                ));
                $builder->orderBy('pageGroup.mapping');
                break;
            case 'country':
                $builder->leftJoin('country.area', 'area')
                    ->addSelect('area');
                break;
            case 'widgetView':
                $builder->leftJoin('widgetView.auth', 'auth')
                    ->leftJoin('widgetView.widget', 'widget')
                    ->select(array(
                        'widgetView',
                        'PARTIAL auth.{id}',
                        'PARTIAL widget.{id,name,label}'
                    ))
                    ->orderBy('widgetView.column')
                    ->addOrderBy('widgetView.position');
                break;
            default:
                break;
        }

        if ($builder !== null) {
            $builder->addFilter((array) $this->Request()->getParam('filter', array()))
                ->addOrderBy((array) $this->Request()->getParam('sort', array()));
            $builder->setFirstResult($this->Request()->getParam('start'))
                ->setMaxResults($this->Request()->getParam('limit'));

            $query = $builder->getQuery();
            $total = Shopware()->Models()->getQueryCount($query);
            $data = $query->getArrayResult();
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Return a list of values for extended forms
     */
    public function getTableListAction()
    {
        $name = $this->Request()->get('_repositoryClass');
        $limit = intval($this->Request()->get('limit'));
        $start = intval($this->Request()->get('start'));
        $table = $this->getTable($name);
        $filter = $this->Request()->get('filter');
        $data = array();
        if (isset($filter[0]['property']) && $filter[0]['property'] == 'name') {
            $search = $filter[0]['value'];
        }
        switch ($name) {
            case 'cronJob':
                $select = Shopware()->Db()->select();
                $select->from(array('c' => $table));
                if (isset($search)) {
                    $select->where(
                        'c.name LIKE :search OR ' .
                        'c.action LIKE :search'
                    );
                    $select->bind(
                        array(
                            'search' => $search
                        )
                    );
                }
                $select->limit($limit, $start);
                $data = Shopware()->Db()->fetchAll($select);
                foreach ($data as $key => &$row) {
                    $row = array(
                        'id' => (int) $row['id'],
                        'name' => $row['name'],
                        'action' => $row['action'],
                        'active' => !empty($row['active']) && !empty($row['end']),
                        'elementId' => $row['elementID'],
                        'data' => !empty($row['data']) ? unserialize($row['data']) : $row['data'],
                        'next' => isset($row['next']) ? new DateTime($row['next']) : $row['next'],
                        'start' => isset($row['start']) ? new DateTime($row['start']) : $row['start'],
                        'interval' => (int) $row['interval'],
                        'end' => isset($row['end']) ? new DateTime($row['end']) : $row['end'],
                        'informTemplate' => $row['inform_template'],
                        'informMail' => $row['inform_mail'],
                        'pluginId' => isset($row['pluginID']) ? (int) $row['pluginID'] : null
                    );
                    $row['data'] = !is_string($row['data']) ? var_export($row['data'], true) : $row['data'];
                }
                //get the total count
                $select->reset(Zend_Db_Select::FROM);
                $select->reset(Zend_Db_Select::LIMIT_COUNT);
                $select->reset(Zend_Db_Select::LIMIT_OFFSET);
                $select->from(array('c' => $table), array('count(*) as total'));
                $totalCount = Shopware()->Db()->fetchOne($select);

                break;
            case 'searchTable':
                $select = Shopware()->Db()->select();
                $select->from(array('t' => $table), array(
                    '*', 'name' => 'table'
                ));
                if (isset($search)) {
                    $select->where(
                        't.table LIKE :search'
                    );
                    $select->bind(array(
                        'search' => $search
                    ));
                }
                $data = Shopware()->Db()->fetchAll($select);
                break;
            case 'searchField':
                $sqlParams = array();
                $sql = 'SELECT SQL_CALC_FOUND_ROWS f.id, f.name, f.relevance, f.field, f.tableId as tableId, t.table
                        FROM ' . Shopware()->Db()->quoteTableAs($table, 'f') . '
                        LEFT JOIN s_search_tables t on f.tableID = t.id';

                if (isset($search)) {
                    $sql .= ' WHERE f.name LIKE :search OR ' .
                        'f.field LIKE :search OR ' .
                        't.table LIKE :search';
                    $sqlParams = array('search' => $search);
                }

                if (!empty($limit)) {
                    $sql .= ' Limit ' . Shopware()->Db()->quote($start) . ',' . Shopware()->Db()->quote($limit);
                }

                $data = Shopware()->Db()->fetchAll($sql, $sqlParams);

                //get the total count
                $sql = "SELECT FOUND_ROWS()";
                $totalCount = Shopware()->Db()->fetchOne($sql);

                break;
            default:
                break;
        }

        $totalCount = empty($totalCount) ? count($data) : $totalCount;
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $totalCount));
    }

    /**
     * Return values for extended forms
     */
    public function getValuesAction()
    {
        $name = $this->Request()->get('_repositoryClass');
        $repository = $this->getRepository($name);
        if ($repository === null) {
            return;
        }
        /** @var $builder Shopware\Components\Model\QueryBuilder */
        $builder = $repository->createQueryBuilder($name);

        switch ($name) {
            case 'shop':
                $builder->leftJoin('shop.locale', 'locale')
                    ->leftJoin('shop.main', 'main')
                    ->leftJoin('shop.category', 'category')
                    ->leftJoin('shop.currency', 'currency')
                    ->leftJoin('shop.template', 'template')
                    ->leftJoin('shop.documentTemplate', 'documentTemplate')
                    ->leftJoin('shop.customerGroup', 'customerGroup')
                    ->leftJoin('shop.fallback', 'fallback')
                    ->leftJoin('shop.currencies', 'currencies')
                    ->leftJoin('shop.pages', 'pages')
                    ->select(array(
                        'shop',
                        'PARTIAL main.{id,name}',
                        'PARTIAL locale.{id,locale}',
                        'PARTIAL category.{id}',
                        'PARTIAL currency.{id,currency}',
                        'PARTIAL template.{id,template}',
                        'PARTIAL documentTemplate.{id,template}',
                        'PARTIAL customerGroup.{id}',
                        'PARTIAL fallback.{id}',
                        'PARTIAL currencies.{id,currency,name}',
                        'PARTIAL pages.{id,name,key}'
                    ))
                    ->orderBy('shop.default', 'DESC')
                    ->addOrderBy('shop.name');
                break;
            case 'customerGroup':
                $builder->leftJoin('customerGroup.discounts', 'discounts')
                    ->addSelect('discounts');
                break;
            case 'tax':
                $builder->leftJoin('tax.rules', 'rules')
                    ->addSelect('rules');
                break;
            case 'country':
                $builder->leftJoin('country.area', 'area')
                    ->leftJoin('country.states', 'states')
                    ->addSelect('area', 'states');
                break;
            case 'priceGroup':
                $builder->leftJoin('priceGroup.discounts', 'discounts')
                    ->addSelect('discounts')
                    ->leftJoin('discounts.customerGroup', 'customerGroup')
                    ->addSelect('PARTIAL customerGroup.{id,name}');
                break;
            case 'document':
                $builder->leftJoin('document.elements', 'elements')->addSelect('elements');
                break;
            default:
                break;
        }

        $builder->addFilter((array) $this->Request()->getParam('filter', array()));

        $query = $builder->getQuery();
        $data = $query->getArrayResult();
        $total = count($data);

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Save the custom table values
     */
    public function saveValuesAction()
    {
        $manager = Shopware()->Models();
        $name = $this->Request()->get('_repositoryClass');
        $repository = $this->getRepository($name);
        $data = $this->Request()->getPost();

        $data = isset($data[0]) ? array_pop($data) : $data;

        if ($repository === null) {
            $this->View()->assign(array(
                'success' => false,
                'message' => 'Model repository "' . $name . '" not found failure.'
            ));
            return;
        }

        if (!empty($data['id'])) {
            $model = $repository->find($data['id']);
        } else {
            unset($data['id']);
            $model = $repository->getClassName();
            $model = new $model();
        }

        switch ($name) {
            case 'tax':
                $this->saveTaxRules($data, $model);
                return;
            case 'customerGroup':
                if (isset($data['discounts'])) {
                    $model->getDiscounts()->clear();
                    $manager->flush();
                    $discounts = array();
                    foreach ($data['discounts'] as $discountData) {
                        $discount = new Shopware\Models\Customer\Discount();
                        $discount->setDiscount($discountData['discount']);
                        $discount->setValue($discountData['value']);
                        $discount->setGroup($model);
                        $discounts[] = $discount;
                    }

                    $data['discounts'] = $discounts;
                }
                if (empty($data["mode"])) {
                    $data["discount"] = 0;
                }
                break;
            case 'shop':
                if (isset($data['currencies'])) {
                    $mappingRepository = $this->getRepository('currency');
                    $currencies = array();
                    foreach ($data['currencies'] as $currency) {
                        $currencies[] = $mappingRepository->find($currency['id']);
                    }
                    $data['currencies'] = $currencies;
                }
                if (isset($data['pages'])) {
                    $mappingRepository = $this->getRepository('pageGroup');
                    $currencies = array();
                    foreach ($data['pages'] as $currency) {
                        $currencies[] = $mappingRepository->find($currency['id']);
                    }
                    $data['pages'] = $currencies;
                }
                foreach ($data as $key => $value) {
                    if ($value === '' && !in_array($key, array('name', 'hosts'))) {
                        $data[$key] = null;
                    }
                }

                if (!empty($data['id']) && !empty($data['mainId'])) {
                    $sql = 'UPDATE s_core_shops SET main_id = 1 WHERE main_id = ?';
                    Shopware()->Db()->query($sql, [$data['id']]);
                }

                $fields = array(
                    'mainId' => 'main',
                    'templateId' => 'template',
                    'documentTemplateId' => 'documentTemplate',
                    'fallbackId' => 'fallback',
                    'localeId' => 'locale',
                    'currencyId' => 'currency',
                    'categoryId' => 'category',
                    'customerGroupId' => 'customerGroup'
                );
                foreach ($fields as $field => $mapping) {
                    if (isset($data[$field])) {
                        $mappingRepository = $this->getRepository($mapping);
                        $data[$mapping] = $mappingRepository->find($data[$field]);
                    } else {
                        $data[$mapping] = null;
                    }
                    unset($data[$field]);
                }
                break;
            case 'country':
                unset($data['area']);
                if (isset($data['areaId'])) {
                    $mappingRepository = $this->getRepository('countryArea');
                    $data['area'] = $mappingRepository->find($data['areaId']);
                    unset($data['areaId']);
                }
                break;
            case 'widgetView':
                if (isset($data['widgetId'])) {
                    $mappingRepository = $this->getRepository('widget');
                    $data['widget'] = $mappingRepository->find($data['widgetId']);
                    unset($data['widgetId']);
                }
                if (Shopware()->Container()->get('Auth')->hasIdentity()) {
                    $mappingRepository = $this->getRepository('auth');
                    $authId = Shopware()->Container()->get('Auth')->getIdentity()->id;
                    $data['auth'] = $mappingRepository->find($authId);
                }
                break;
            case 'pageGroup':
                if (isset($data['mappingId'])) {
                    $mappingRepository = $this->getRepository('pageGroup');
                    $data['mapping'] = $mappingRepository->find($data['mappingId']);
                    unset($data['mappingId']);
                } else {
                    $data['mapping'] = null;
                }
                break;
            case 'document':
                if ($data['id']) {
                    $elements = new \Doctrine\Common\Collections\ArrayCollection();
                    foreach ($data['elements'] as $element) {
                        /**
                         * @var $elementModel Shopware\Models\Document\Element
                         */
                        $elementRepository = $this->getRepository('documentElement');
                        $elementModel = $elementRepository->find($element['id']);
                        $elementModel->fromArray($element);
                        $elements[] = $elementModel;
                    }
                    $data['elements'] = $elements;
                } else {
                    $data['elements'] = $this->createDocumentElements($model);
                }

                break;
            default:
                break;
        }

        $model->fromArray($data);

        try {
            $manager->persist($model);
            $manager->flush();
        } catch (\Exception $ex) {
            switch ($name) {
                case 'country':
                    if ($ex instanceof \Doctrine\DBAL\DBALException && stripos($ex->getMessage(), "violation: 1451") !== false) {
                        $this->View()->assign(array('success' => false, 'message' => 'A state marked to be deleted is still in use.'));
                        return;
                    }
                    break;
                default:
                    throw $ex;
            }
        }

        $this->View()->assign(array('success' => true));
    }

    /**
     * Save the custom table values
     */
    public function saveTableValuesAction()
    {
        $name = $this->Request()->get('_repositoryClass');
        $data = $this->Request()->getPost();
        $data = isset($data[0]) ? array_pop($data) : $data;
        $id = !empty($data['id']) ? $data['id'] : null;
        unset($data['id']);

        $table = $this->getTable($name);
        if ($table === null) {
            $this->View()->assign(array(
                'success' => false, 'message' => 'Table "' . $name . '" not found failure.'
            ));
            return;
        }

        switch ($name) {
            case 'searchTable':
                $data['table'] = $data['name'];
                unset($data['name']);
                break;
            case 'searchField':
                $data['tableID'] = isset($data['tableId']) ? $data['tableId'] : null;
                unset($data['table'], $data['tableID']);
                break;
            case 'cronJob':
                $data['pluginID'] = isset($data['pluginId']) ? $data['pluginId'] : null;
                $data['inform_mail'] = isset($data['informMail']) ? $data['informMail'] : '';
                $data['inform_template'] = isset($data['informTemplate']) ? $data['informTemplate'] : '';
                if ($data['data'] !== '') {
                    unset($data['data']);
                }
                if (empty($data['next'])) {
                    $data['next'] = new Zend_Date();
                }
                if (!empty($data['active']) && empty($data['end'])) {
                    $data['end'] = $data['next'];
                } else {
                    unset($data['end']);
                }
                unset($data['deletable'], $data['pluginId'], $data['informMail'], $data['informTemplate']);
                break;
            default:
                break;
        }

        if ($id !== null) {
            $result = Shopware()->Db()->update($table, $data, array('id=?' => $id));
        } else {
            $result = Shopware()->Db()->insert($table, $data);
        }

        $this->View()->assign(array(
            'success' => true,
            'result' => $result
        ));
    }

    /**
     * Save form values values with shop reverence
     */
    public function deleteValuesAction()
    {
        $manager = Shopware()->Models();
        $name = $this->Request()->get('_repositoryClass');
        $repository = $this->getRepository($name);
        $data = $this->Request()->getPost();

        if ($repository === null) {
            $this->View()->assign(array('success' => false, 'message' => 'Repository not found.'));
            return;
        }
        if (!empty($data['id'])) {
            $model = $repository->find($data['id']);
        } else {
            $this->View()->assign(array('success' => false, 'message' => 'Entry not found.'));
            return;
        }

        try {
            $manager->remove($model);
            $manager->flush();
        } catch (\Exception $ex) {
            switch ($name) {
                case 'country':
                    $this->View()->assign(array('success' => false, 'message' => 'The country is still being used.'));
                    return;
                default:
                    throw $ex;
            }
        }


        $this->View()->assign(array('success' => true));
    }

    /**
     * Save the custom table values
     */
    public function deleteTableValuesAction()
    {
        $name = $this->Request()->get('_repositoryClass');

        $data = $this->Request()->getPost();
        $data = isset($data[0]) ? array_pop($data) : $data;

        $table = $this->getTable($name);

        if ($table !== null && !empty($data['id'])) {
            Shopware()->Db()->delete($table, array('id=?' => $data['id']));
            $this->View()->assign(array('success' => true));
        } else {
            $this->View()->assign(array('success' => false));
        }
    }

    /**
     * @param $name
     * @return Shopware\Components\Model\ModelRepository
     */
    protected function getRepository($name)
    {
        if (!isset(self::$repositories[$name])) {
            switch ($name) {
                case 'form':
                    $repository = 'Shopware\Models\Config\Form';
                    break;
                case 'documentTemplate':
                case 'template':
                    $repository = 'Shopware\Models\Shop\Template';
                    break;
                case 'main':
                case 'shop':
                case 'fallback':
                    $repository = 'Shopware\Models\Shop\Shop';
                    break;
                case 'locale':
                    $repository = 'Shopware\Models\Shop\Locale';
                    break;
                case 'currency':
                    $repository = 'Shopware\Models\Shop\Currency';
                    break;
                case 'customerGroup':
                    $repository = 'Shopware\Models\Customer\Group';
                    break;
                case 'priceGroup':
                    $repository = 'Shopware\Models\Price\Group';
                    break;
                case 'tax':
                    $repository = 'Shopware\Models\Tax\Tax';
                    break;
                case 'country':
                    $repository = 'Shopware\Models\Country\Country';
                    break;
                case 'countryArea':
                    $repository = 'Shopware\Models\Country\Area';
                    break;
                case 'number':
                    $repository = 'Shopware\Models\Order\Number';
                    break;
                case 'unit':
                    $repository = 'Shopware\Models\Article\Unit';
                    break;
                case 'category':
                    $repository = 'Shopware\Models\Category\Category';
                    break;
                case 'widget':
                    $repository = 'Shopware\Models\Widget\Widget';
                    break;
                case 'widgetView':
                    $repository = 'Shopware\Models\Widget\View';
                    break;
                case 'auth':
                    $repository = 'Shopware\Models\User\User';
                    break;
                case 'plugin':
                    $repository = 'Shopware\Models\Plugin\Plugin';
                    break;
                case 'pageGroup':
                    $repository = 'Shopware\Models\Site\Group';
                    break;
                case 'document':
                    $repository = 'Shopware\Models\Document\Document';
                    break;
                case 'documentElement':
                    $repository = 'Shopware\Models\Document\Element';
                    break;
                default:
                    return null;
            }
            self::$repositories[$name] = Shopware()->Models()->getRepository($repository);
        }
        return self::$repositories[$name];
    }

    /**
     * @var array
     */
    public $tables = array(
        'searchField' => 's_search_fields',
        'searchTable' => 's_search_tables',
        'cronJob' => 's_crontab'
    );

    /**
     * @param   $name
     * @return  string
     */
    protected function getTable($name)
    {
        return isset($this->tables[$name]) ? $this->tables[$name] : null;
    }

    private function createDocumentElements($model)
    {
        $elementCollection = new \Doctrine\Common\Collections\ArrayCollection();

        /**
         * @var $documentModel Shopware\Models\Document\Document
         */
        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Body');
        $elementModel->setValue('');
        $elementModel->setStyle('width:100%; font-family: Verdana, Arial, Helvetica, sans-serif; font-size:11px;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Logo');
        $elementModel->setValue('<p><img src="http://www.shopware.de/logo/logo.png" alt="" /></p>');
        $elementModel->setStyle('height: 20mm; width: 90mm; margin-bottom:5mm;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Header_Recipient');
        $elementModel->setValue('');
        $elementModel->setStyle('');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Header');
        $elementModel->setValue('');
        $elementModel->setStyle('height: 60mm;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Header_Sender');
        $elementModel->setValue('<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>');
        $elementModel->setStyle('');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Header_Box_Left');
        $elementModel->setValue('');
        $elementModel->setStyle('width: 120mm; height:60mm; float:left;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Header_Box_Right');
        $elementModel->setValue('<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 /            56780<br />info@demo.de<br />www.demo.de</p>');
        $elementModel->setStyle('width: 45mm; height: 60mm; float:left; margin-top:-20px; margin-left:5px;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Header_Box_Bottom');
        $elementModel->setValue('');
        $elementModel->setStyle('font-size:14px; height: 10mm;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Content');
        $elementModel->setValue('');
        $elementModel->setStyle('height: 65mm; width: 170mm;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Td');
        $elementModel->setValue('');
        $elementModel->setStyle('white-space:nowrap; padding: 5px 0;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Td_Name');
        $elementModel->setValue('');
        $elementModel->setStyle('white-space:normal;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Td_Line');
        $elementModel->setValue('');
        $elementModel->setStyle('border-bottom: 1px solid #999; height: 0px;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Td_Head');
        $elementModel->setValue('');
        $elementModel->setStyle('border-bottom:1px solid #000;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Footer');
        $elementModel->setValue(
            '<table style="vertical-align: top;" width="100%" border="0">
            <tbody>
            <tr valign="top">
            <td style="width: 25%;">
            <p><span style="font-size: xx-small;">Demo GmbH</span></p>
            <p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>
            </td>
            <td style="width: 25%;">
            <p><span style="font-size: xx-small;">Bankverbindung</span></p>
            <p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>
            <span style="font-size: xx-small;">aaaa<br /></span></td>
            <td style="width: 25%;">
            <p><span style="font-size: xx-small;">AGB<br /></span></p>
            <p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>
            </td>
            <td style="width: 25%;">
            <p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>
            <p><span style="font-size: xx-small;">Max Mustermann</span></p>
            </td>
            </tr>
            </tbody>
            </table>'
        );
        $elementModel->setStyle('width: 170mm; position:fixed; bottom:-20mm; height: 15mm;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Content_Amount');
        $elementModel->setValue('');
        $elementModel->setStyle('margin-left:90mm;');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        $elementModel = new Shopware\Models\Document\Element();
        $elementModel->setName('Content_Info');
        $elementModel->setValue('<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>');
        $elementModel->setStyle('');
        $elementModel->setDocument($model);
        $elementCollection->add($elementModel);

        return $elementCollection;
    }

    /**
     * Simple validation for backend config elements
     *
     * @param $elementData
     * @param $value
     * @return boolean
     */
    private function validateData($elementData, $value)
    {
        switch ($elementData['name']) {
            /**
             * Add rules for a bad case and return false to abort saving
             */
            case 'backendLocales':
                if (!is_array($value) || count($value) === 0) {
                    return false;
                }

                // check existence of each locale
                foreach ($value as $localeId) {
                    $locale = Shopware()->Models()->find('Shopware\Models\Shop\Locale', $localeId);
                    if (null === $locale) {
                        return false;
                    }
                }

                break;
        }

        return true;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Tax\Tax $model
     */
    private function saveTaxRules(array $data, \Shopware\Models\Tax\Tax $model)
    {
        if (isset($data['rules'])) {
            $model->getRules()->clear();
            $rules = array();
            foreach ($data['rules'] as $ruleData) {
                $rule = new Shopware\Models\Tax\Rule();
                $rule->fromArray($ruleData);
                $rule->setGroup($model);
                $rules[] = $rule;
            }
            $data['rules'] = $rules;

            $model->fromArray($data);

            $this->getModelManager()->persist($model);
            $this->getModelManager()->flush();
        }

        $this->View()->assign(array('success' => true));
    }

    /**
     * @param array $elementData
     * @return bool
     */
    private function beforeSaveElement($elementData)
    {
        switch ($elementData['name']) {
            case 'shopsalutations':
                $this->createSalutationSnippets($elementData);
                break;
        }

        return true;
    }

    /**
     * @return int[] indexed by shop id
     */
    private function getShopLocaleMapping()
    {
        $connection = Shopware()->Container()->get('dbal_connection');
        $query = $connection->createQueryBuilder();
        $query->select(['id, IFNULL(main_id, id)']);
        $query->from('s_core_shops');
        return $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param array $elementData
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createSalutationSnippets($elementData)
    {
        $connection = Shopware()->Container()->get('dbal_connection');

        $shops = $this->getShopLocaleMapping();

        $query = $connection->prepare('INSERT IGNORE INTO s_core_snippets (namespace, shopID, localeID, name, created) VALUES (:namespace, :shopId, :localeId, :name, :created)');

        $salutations = [];
        foreach ($elementData['values'] as $value) {
            $salutations = array_merge($salutations, explode(',', $value['value']));
        }
        $salutations = array_unique($salutations);

        $date = new DateTime();
        foreach ($shops as $localeId => $shopId) {
            foreach ($salutations as $salutation) {
                $query->execute([
                    ':created' => $date->format('Y-m-d H:i:s'),
                    ':namespace' => 'frontend/salutation',
                    ':name' => trim($salutation),
                    ':shopId' => $shopId,
                    ':localeId' => $localeId
                ]);
            }
        }
    }

    /**
     * @param array $elementData
     * @param mixed $value
     * @return mixed
     */
    private function prepareValue($elementData, $value)
    {
        switch ($elementData['name']) {
            case 'shopsalutations':
                $values = explode(',', $value);
                $value = implode(',', array_map('trim', $values));
                break;
        }
        return $value;
    }
}
