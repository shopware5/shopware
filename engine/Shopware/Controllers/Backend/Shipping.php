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

use Shopware\Models\Category\Category;
use Shopware\Models\Country\Country;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Dispatch\Holiday;
use Shopware\Models\Dispatch\ShippingCost;
use Shopware\Models\Payment\Payment;

class Shopware_Controllers_Backend_Shipping extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Wrapper method to create a new dispatch entry
     */
    public function createDispatchAction()
    {
        $this->saveDispatch();
    }

    /**
     * Wrapper method to update a existing dispatch entry
     */
    public function updateDispatchAction()
    {
        $this->saveDispatch();
    }

    /**
     * Returns all Shipping Costs
     */
    public function getShippingCostsAction()
    {
        $this->deleteDispatchWithDeletedShops();
        $dispatchID = $this->Request()->getParam('dispatchID');
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', [['property' => 'dispatch.name', 'direction' => 'ASC']]);

        $filter = $this->Request()->getParam('filter');
        if (is_array($filter) && isset($filter[0]['value'])) {
            $filter = $filter[0]['value'];
        }

        if ($dispatchID === null) {
            $dispatchID = $this->Request()->getParam('id');
        }

        $query = $this->getRepository()->getShippingCostsQuery($dispatchID, $filter, $sort, $limit, $offset);
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = $this->getModelManager()->createPaginator($query);
        // Returns the total count of the query
        $totalResult = $paginator->count();
        $shippingCosts = $paginator->getIterator()->getArrayCopy();
        $shippingCosts = $this->convertShippingCostsDates($shippingCosts);

        // Translate dispatch methods
        // The standard $translationComponent->translateDispatches can not be used here since the
        // name and the description may not be overridden. Both fields are edible and if the translation is
        // shown in the edit field, there is a high chance of a user saving the translation as name.
        $translator = $this->get('translation')->getObjectTranslator('config_dispatch');
        $shippingCosts = array_map(function ($dispatchMethod) use ($translator) {
            $translatedDispatchMethod = $translator->translateObjectProperty($dispatchMethod, 'dispatch_name', 'translatedName', $dispatchMethod['name']);

            return $translator->translateObjectProperty($translatedDispatchMethod, 'dispatch_description', 'translatedDescription', $dispatchMethod['description']);
        }, $shippingCosts);

        $this->View()->assign(['success' => true, 'data' => $shippingCosts, 'total' => $totalResult]);
    }

    /**
     * Returns all Shipping Costs with basic data
     *
     * @return array
     */
    public function getListAction()
    {
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', [['property' => 'dispatch.name', 'direction' => 'ASC']]);

        $filter = $this->Request()->getParam('filter');
        if (is_array($filter) && isset($filter[0]['value'])) {
            $filter = $filter[0]['value'];
        }

        $query = $this->getRepository()->getListQuery($filter, $sort, $limit, $offset);
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = $this->getModelManager()->createPaginator($query);
        // Returns the total count of the query
        $totalResult = $paginator->count();
        $shippingCosts = $paginator->getIterator()->getArrayCopy();
        $shippingCosts = $this->convertShippingCostsDates($shippingCosts);

        $this->View()->assign(['success' => true, 'data' => $shippingCosts, 'total' => $totalResult]);
    }

    /**
     * Returns all entries based on a given dispatch Id.
     */
    public function getCostsMatrixAction()
    {
        // Process the parameters
        $minChange = $this->Request()->getParam('minChange');
        $dispatchId = $this->Request()->getParam('dispatchId');
        $filter = $this->Request()->getParam('filter', []);

        if (is_array($filter) && isset($filter[0]['value'])) {
            $filter = $filter[0]['value'];
        }
        $query = $this->getRepository()->getShippingCostsMatrixQuery($dispatchId, $filter);
        $result = $query->getArrayResult();

        // If minChange was not passed, get it in order to show a proper cost matrix
        if ($minChange === null) {
            $dispatch = $this->getRepository()->getShippingCostsQuery($dispatchId)->getArrayResult();
            if ($dispatch) {
                $config = $this->getCalculationConfig(isset($dispatch[0]['calculation']) ? $dispatch[0]['calculation'] : 0);
                $minChange = $config['minChange'];
            }
        }

        $i = 0;
        $nodes = [];

        foreach ($result as $node) {
            if ($i) {
                $nodes[$i - 1]['to'] = $node['from'] - $minChange;
            }
            if (empty($node['to'])) {
                $node['to'] = '';
            }
            if (empty($node['value'])) {
                $node['value'] = '';
            }
            if (empty($node['factor'])) {
                $node['factor'] = '';
            }
            $nodes[$i] = $node;
            ++$i;
        }

        $totalResult = $this->getManager()->getQueryCount($query);
        $this->View()->assign(['success' => true, 'data' => $nodes, 'total' => $totalResult]);
    }

    /**
     * This method is used to delete one single matrix entry. This data set is addressed through a given
     * id.
     */
    public function deleteCostsMatrixEntryAction()
    {
        $costsId = $this->Request()->getParam('id');
        if ($costsId === null) {
            $this->View()->assign(['success' => false, 'errorMsg' => 'No ID given to delete']);
        }
        try {
            $costsModel = Shopware()->Models()->find(ShippingCost::class, (int) $costsId);
            $this->getManager()->remove($costsModel);
            $this->getManager()->flush();
            $this->View()->assign(['success' => true]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Removes all shipping costs for a given dispatch ID and returns the number of
     * deleted records.
     *
     * @param int $dispatchId
     *
     * @return int
     */
    public function deleteCostsMatrix($dispatchId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $dispatchId = (int) $dispatchId;
        $purge = $this->getRepository()->getPurgeShippingCostsMatrixQuery($dispatchId);

        return $purge->execute();
    }

    /**
     * Deletes a single dispatch or an array of dispatches from the database.
     * Expects a single dispatch id or an array of dispatch ids which placed in the parameter customers
     */
    public function deleteAction()
    {
        try {
            // Get posted dispatch
            $dispatches = $this->Request()->getParam('dispatches', [['id' => $this->Request()->getParam('id')]]);

            // Iterate the customers and add the remove action
            foreach ($dispatches as $dispatch) {
                $entity = $this->getRepository()->find($dispatch['id']);
                $this->getManager()->remove($entity);
                $this->deleteCostsMatrix($entity->getId());
            }
            // Performs all of the collected actions.
            $this->getManager()->flush();
            $this->View()->assign([
                'success' => true,
                'data' => $this->Request()->getParams(),
            ]);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Wrapper around the saveCostsMatrix() to handle ACL
     */
    public function updateCostsMatrixAction()
    {
        $this->saveCostsMatrix();
    }

    /**
     * Wrapper around the saveCostsMatrix() to handle ACL
     */
    public function createCostsMatrixAction()
    {
        $this->saveCostsMatrix();
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Saves one entry of the shipping aka dispatch costs matrix
     */
    public function saveCostsMatrix()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $data = null;
        if (!$this->Request()->isPost()) {
            $this->View()->assign(['success' => false, 'errorMsg' => 'Empty Post Request']);

            return;
        }
        $dispatchId = (int) $this->Request()->getParam('dispatchId');
        $costsMatrix = $this->Request()->getParam('costMatrix');
        $params = $this->Request()->getParams();

        if (!empty($params) && !is_array($costsMatrix)) {
            $costsMatrix = [$params];
        }

        if (!is_array($costsMatrix)) {
            $this->View()->assign(['success' => false, 'errorMsg' => 'Empty data set.']);

            return;
        }
        if ($dispatchId <= 0) {
            $this->View()->assign(['success' => false, 'errorMsg' => 'No dispatch id given.']);

            return;
        }

        $dispatch = Shopware()->Models()->find(Dispatch::class, $dispatchId);
        if (!($dispatch instanceof \Shopware\Models\Dispatch\Dispatch)) {
            $this->View()->assign(['success' => false, 'errorMsg' => 'No valid dispatch ID.']);

            return;
        }

        $manager = $this->getManager();

        // Clear costs
        $this->deleteCostsMatrix($dispatchId);

        $data = [];
        foreach ($costsMatrix as $param) {
            $shippingCostModel = new \Shopware\Models\Dispatch\ShippingCost();
            $param['dispatch'] = $dispatch;
            // Set data to model and overwrite the image field
            $shippingCostModel->fromArray($param);

            try {
                $manager->persist($shippingCostModel);
                $data[] = $this->getManager()->toArray($shippingCostModel);
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
                $this->View()->assign(['success' => false, 'errorMsg' => $errorMsg]);

                return;
            }
        }
        $manager->flush();

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Get all used means of payment for a given dispatch id
     */
    public function getPaymentsAction()
    {
        $limit = (int) $this->Request()->getParam('limit', 20);
        $offset = (int) $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        $query = $this->getRepository()->getPaymentQuery($filter, $sort, $limit, $offset);

        $result = $query->getArrayResult();
        $totalResult = $this->getManager()->getQueryCount($query);
        $this->View()->assign(['success' => true, 'data' => $result, 'total' => $totalResult]);
    }

    /**
     * Get all countries who are selected for this dispatch id
     */
    public function getCountriesAction()
    {
        $limit = (int) $this->Request()->getParam('limit', 999);
        $offset = (int) $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        $query = $this->getRepository()->getCountryQuery($filter, $sort, 999, $offset);

        $result = $query->getArrayResult();
        $totalResult = $this->getManager()->getQueryCount($query);
        $this->View()->assign(['success' => true, 'data' => $result, 'total' => $totalResult]);
    }

    /**
     * Get all countries who are selected for this dispatch id
     */
    public function getHolidaysAction()
    {
        // Process the parameters
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort');
        $filter = $this->Request()->getParam('filter');

        if (is_array($filter) && isset($filter[0]['value'])) {
            $filter = $filter[0]['value'];
        }

        $query = $this->getRepository()->getHolidayQuery($filter, $sort, $limit, $offset);
        $result = $query->getArrayResult();

        $totalResult = $this->getManager()->getQueryCount($query);
        $this->View()->assign(['success' => true, 'data' => $result, 'total' => $totalResult]);
    }

    /**
     * Returns the shopware model manager
     *
     * @return Shopware\Components\Model\ModelManager
     */
    protected function getManager()
    {
        return Shopware()->Models();
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return Shopware\Models\Dispatch\Repository
     */
    protected function getRepository()
    {
        return Shopware()->Models()->getRepository(Dispatch::class);
    }

    /**
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->addAclPermission("name_of_action_with_action_prefix","name_of_assigned_privilege","optionally error message");
     * // $this->addAclPermission("indexAction","read","Ops. You have no permission to view that...");
     * </code>
     */
    protected function initAcl()
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/shipping/controller');
        // Read
        $this->addAclPermission('getCostsMatrixAction', 'read', $namespace->get('no_list_rights', 'Read access denied.'));
        $this->addAclPermission('getCountriesAction', 'read', $namespace->get('no_list_rights', 'Read access denied.'));
        $this->addAclPermission('getHolidaysAction', 'read', $namespace->get('no_list_rights', 'Read access denied.'));
        $this->addAclPermission('getPaymentsAction', 'read', $namespace->get('no_list_rights', 'Read access denied.'));
        $this->addAclPermission('getShippingCostsAction', 'read', $namespace->get('no_list_rights', 'Read access denied.'));
        // Update
        $this->addAclPermission('updateCostsMatrixAction', 'update', $namespace->get('no_update_rights', 'Update access denied.'));
        $this->addAclPermission('updateDispatchAction', 'update', $namespace->get('no_update_rights', 'Update access denied.'));
        // Delete
        $this->addAclPermission('deleteAction', 'delete', $namespace->get('no_delete_rights', 'Delete access denied.'));
        $this->addAclPermission('deleteCostsMatrixEntryAction', 'delete', $namespace->get('no_delete_rights', 'Delete access denied.'));
        // Create
        $this->addAclPermission('createCostsMatrixAction', 'create', $namespace->get('no_create_rights', 'Create access denied.'));
        $this->addAclPermission('createDispatchAction', 'create', $namespace->get('no_create_rights', 'Create access denied.'));
    }

    /**
     * Saves the dispatch to the data base.
     */
    private function saveDispatch()
    {
        $params = $this->Request()->getParams();
        $dispatchModel = null;
        $id = (int) $this->Request()->get('id');
        if ($id > 0) {
            $dispatchModel = $this->getRepository()->find($id);
        } else {
            $dispatchModel = new Shopware\Models\Dispatch\Dispatch();
            $this->getManager()->persist($dispatchModel);
        }

        // Clean up params and init some fields
        $payments = $params['payments'];
        $holidays = $params['holidays'];
        $countries = $params['countries'];
        $categories = $params['categories'];

        if (!isset($params['shippingFree']) || $params['shippingFree'] === '' || $params['shippingFree'] === '0') {
            $params['shippingFree'] = null;
        } else {
            $params['shippingFree'] = (float) str_replace(',', '.', $params['shippingFree']);
        }

        $params['payments'] = new \Doctrine\Common\Collections\ArrayCollection();
        $params['holidays'] = new \Doctrine\Common\Collections\ArrayCollection();
        $params['countries'] = new \Doctrine\Common\Collections\ArrayCollection();
        $params['categories'] = new \Doctrine\Common\Collections\ArrayCollection();

        $params['multiShopId'] = $this->cleanData($params['multiShopId']);
        $params['customerGroupId'] = $this->cleanData($params['customerGroupId']);
        $params['bindTimeFrom'] = $this->cleanData($params['bindTimeFrom']);
        $params['bindTimeTo'] = $this->cleanData($params['bindTimeTo']);
        $params['bindInStock'] = $this->cleanData($params['bindInStock']);
        $params['bindWeekdayFrom'] = $this->cleanData($params['bindWeekdayFrom']);
        $params['bindWeekdayTo'] = $this->cleanData($params['bindWeekdayTo']);
        $params['bindWeightFrom'] = $this->cleanData($params['bindWeightFrom']);
        $params['bindWeightTo'] = $this->cleanData($params['bindWeightTo']);
        $params['bindPriceFrom'] = $this->cleanData($params['bindPriceFrom']);
        $params['bindPriceTo'] = $this->cleanData($params['bindPriceTo']);
        $params['bindSql'] = $this->cleanData($params['bindSql']);
        $params['calculationSql'] = $this->cleanData($params['calculationSql']);

        if (!empty($params['bindTimeFrom'])) {
            $bindTimeFrom = new Zend_Date();
            $bindTimeFrom->set($params['bindTimeFrom'], Zend_Date::TIME_SHORT);
            $bindTimeFrom = (int) $bindTimeFrom->get(Zend_Date::MINUTE) * 60 + (int) $bindTimeFrom->get(Zend_Date::HOUR) * 60 * 60;
            $params['bindTimeFrom'] = $bindTimeFrom;
        } else {
            $params['bindTimeFrom'] = null;
        }

        if (!empty($params['bindTimeTo'])) {
            $bindTimeTo = new Zend_Date();
            $bindTimeTo->set($params['bindTimeTo'], Zend_Date::TIME_SHORT);
            $bindTimeTo = (int) $bindTimeTo->get(Zend_Date::MINUTE) * 60 + (int) $bindTimeTo->get(Zend_Date::HOUR) * 60 * 60;
            $params['bindTimeTo'] = $bindTimeTo;
        } else {
            $params['bindTimeTo'] = null;
        }

        if (!$this->_isAllowed('sql_rule', 'shipping')) {
            unset($params['calculationSql'], $params['bindSql']);
        }

        // Convert params to model
        $dispatchModel->fromArray($params);

        // Convert the payment array to the payment model
        foreach ($payments as $paymentMethod) {
            if (empty($paymentMethod['id'])) {
                continue;
            }
            $paymentModel = $this->getManager()->find(Payment::class, $paymentMethod['id']);
            if ($paymentModel instanceof Shopware\Models\Payment\Payment) {
                $dispatchModel->getPayments()->add($paymentModel);
            }
        }

        // Convert the countries to there country models
        foreach ($countries as $country) {
            if (empty($country['id'])) {
                continue;
            }
            $countryModel = $this->getManager()->find(Country::class, $country['id']);
            if ($countryModel instanceof Shopware\Models\Country\Country) {
                $dispatchModel->getCountries()->add($countryModel);
            }
        }

        foreach ($categories as $category) {
            if (empty($category['id'])) {
                continue;
            }

            $categoryModel = $this->getManager()->find(Category::class, $category['id']);
            if ($categoryModel instanceof Shopware\Models\Category\Category) {
                $dispatchModel->getCategories()->add($categoryModel);
            }
        }

        foreach ($holidays as $holiday) {
            if (empty($holiday['id'])) {
                continue;
            }

            $holidayModel = $this->getManager()->find(Holiday::class, $holiday['id']);
            if ($holidayModel instanceof Shopware\Models\Dispatch\Holiday) {
                $dispatchModel->getHolidays()->add($holidayModel);
            }
        }

        try {
            $this->getManager()->flush();
            $params['id'] = $dispatchModel->getId();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true, 'data' => $params]);
    }

    /**
     * Extends the database data with additional data for easier usage
     *
     * @return array
     */
    private function convertShippingCostsDates(array $shippingCosts)
    {
        foreach ($shippingCosts as $i => $shippingCost) {
            if ($shippingCost['bindTimeFrom'] !== null) {
                $shippingCosts[$i]['bindTimeFrom'] = gmdate('H:i', $shippingCost['bindTimeFrom']);
            }

            if ($shippingCost['bindTimeTo'] !== null) {
                $shippingCosts[$i]['bindTimeTo'] = gmdate('H:i', $shippingCost['bindTimeTo']);
            }
        }

        return $shippingCosts;
    }

    /**
     * Deletes dispatch configurations which are associated to deleted shops.
     */
    private function deleteDispatchWithDeletedShops()
    {
        $builder = $this->getRepository()->getDispatchWithDeletedShopsQuery();
        /** @var Shopware\Models\Dispatch\Dispatch[] $result */
        $result = $builder->getResult();

        $modelManager = Shopware()->Container()->get('models');

        /** @var Shopware\Models\Dispatch\Dispatch $dispatch */
        foreach ($result as $dispatch) {
            $modelManager->remove($dispatch);
        }

        $modelManager->flush();
    }

    /**
     * Helper function to get some settings for the cost matrix
     * todo@all Duplicates getConfig in ExtJS main controller
     *
     * @param int $calculationType
     *
     * @return array
     */
    private function getCalculationConfig($calculationType)
    {
        switch ($calculationType) {
            case 1:
                return [
                    'decimalPrecision' => 2,
                    'minChange' => 0.01,
                    'startValue' => 0,
                ];
                break;

            case 2:
            case 3:
                return [
                    'decimalPrecision' => 0,
                    'minChange' => 1,
                    'startValue' => 1,
                ];
                break;

            case 0:
            default:
                return [
                    'decimalPrecision' => 3,
                    'minChange' => 0.001,
                    'startValue' => 0,
                ];
                break;
        }
    }

    /**
     * @param int|string|null $inputValue
     *
     * @return int|string|null
     */
    private function cleanData($inputValue)
    {
        if (empty($inputValue)) {
            return null;
        }

        if ($inputValue === 0) {
            return null;
        }

        return $inputValue;
    }
}
