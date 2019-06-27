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

class Shopware_Controllers_Backend_Supplier extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Models\Article\Repository
     */
    private $repository;

    /**
     * Deletes a Supplier from the database
     * Feeds the view with an json encoded array containing
     * - success : boolean Set to true if everything went well otherwise it is set to false
     * - data    : int  Id of the deleted supplier
     */
    public function deleteSupplierAction()
    {
        if (!$this->Request()->isPost()) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $id = (int) $this->Request()->get('id');
        $supplierModel = Shopware()->Models()->find('Shopware\Models\Article\Supplier', $id);

        Shopware()->Models()->remove($supplierModel);
        Shopware()->Models()->flush();

        $this->View()->assign(['success' => true, 'data' => $id]);
    }

    /**
     * Returns a JSON string containing all Suppliers
     * Json Structure
     * - success : boolean Set to true if everything went well otherwise it is set to false;
     *             Will return false if no suppliers are found
     * - data : array of suppliers containing the following keys
     *          - name : String
     *          - id : Int
     *          - link : String
     *          - articleCounter : Int
     *          - description : String
     */
    public function getSuppliersAction()
    {
        // if id is provided return a single form instead of a collection
        if ($id = $this->Request()->getParam('id')) {
            $this->getSingleSupplier($id);

            return;
        }

        $filter = $this->Request()->getParam('filter');
        $sort = $this->Request()->getParam('sort', [['property' => 'name']]);
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);

        $query = $this->getRepository()->getSupplierListQuery($filter, $sort, $limit, $offset);
        $total = Shopware()->Models()->getQueryCount($query);

        $suppliers = $query->getArrayResult();
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($suppliers as &$supplier) {
            $supplier['description'] = strip_tags($supplier['description']);
            $supplier['image'] = $mediaService->getUrl($supplier['image']);
        }

        $this->View()->assign([
            'success' => !empty($suppliers),
            'data' => $suppliers,
            'total' => $total,
        ]);
    }

    /**
     * This method is called if a new supplier should be written to the database.
     * It works as a wrapper around the saveSupplier method to use ACL
     * ACL configuration is done in initAcl()
     */
    public function createSupplierAction()
    {
        $this->saveSuppliers();
    }

    /**
     * This method is called if a supplier should be updated.
     * It works as a wrapper around the saveSupplier method to use ACL
     * ACL configuration is done in initAcl()
     */
    public function updateSupplierAction()
    {
        $this->saveSuppliers();
    }

    /**
     * Creates a new supplier
     * on the passed values
     *
     * Json Structure
     * - success : boolean Set to true if everything went well otherwise it is set to false
     * - data : Data for the new saved supplier containing
     *          - name : String
     *          - id : Int
     *          - link : String
     *          - articleCounter : Int
     *          - description : String
     * [-errorMsg] : String containing the error message
     */
    public function saveSuppliers()
    {
        if (!$this->Request()->isPost()) {
            $this->View()->assign(['success' => false]);

            return;
        }
        /** @var \Shopware\Models\Article\Supplier $supplierModel */
        $supplierModel = null;
        $id = (int) $this->Request()->get('id');
        if ($id > 0) {
            $supplierModel = Shopware()->Models()->find('Shopware\Models\Article\Supplier', $id);
        } else {
            $supplierModel = new \Shopware\Models\Article\Supplier();
        }

        $params = $this->Request()->getParams();

        // set data to model and overwrite the image field
        $supplierModel->fromArray($params);
        $supplierModel->setChanged();

        $mediaData = $this->Request()->get('media-manager-selection');
        if (!empty($mediaData) && $mediaData !== null) {
            $supplierModel->setImage($this->Request()->get('media-manager-selection'));
        }

        // strip full qualified url
        $mediaService = $this->get('shopware_media.media_service');
        $supplierModel->setImage($mediaService->normalize($supplierModel->getImage()));

        // backend checks
        $name = $supplierModel->getName();
        if (empty($name)) {
            $this->View()->assign([
                'success' => false,
                'errorMsg' => 'No supplier name given',
            ]);

            return;
        }

        $manager = Shopware()->Models();
        $manager->persist($supplierModel);
        $manager->flush();
        $params['id'] = $supplierModel->getId();
        $this->View()->assign(['success' => true, 'data' => $params]);
    }

    /**
     * @deprecated in 5.6, will removed in 5.7 without any replacement
     *
     * Returns all known Suppliers from the database. there are ordered by there name
     *
     * @return array
     */
    public function getAllSupplier()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $filter = $this->Request()->getParam('filter');
        $sort = $this->Request()->getParam('sort', [['property' => 'name']]);
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);

        $query = $this->getRepository()->getSupplierListQuery($filter, $sort, $limit, $offset);
        $count = Shopware()->Models()->getQueryCount($query);

        return [
            'result' => $query->getArrayResult(),
            'total' => $count,
        ];
    }

    /**
     * Gets a single supplier
     *
     * @param int $id
     */
    protected function getSingleSupplier($id)
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $data = $this->getRepository()->getSupplierQuery($id)->getArrayResult();
        $data[0]['image'] = $data[0]['image'] ? $mediaService->getUrl($data[0]['image']) : null;

        if (empty($data)) {
            $this->View()->assign(['success' => false, 'message' => 'Supplier not found']);

            return;
        }

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => 1]);
    }

    /**
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->addAclPermission("name_of_action_with_action_prefix","name_of_assigned_privilege","optionally error message");
     * </code>
     */
    protected function initAcl()
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/supplier');

        $this->addAclPermission('getSuppliersAction', 'read', $namespace->get('no_list_rights', 'Read access denied.'));
        $this->addAclPermission('deleteSupplierAction', 'delete', $namespace->get('no_list_rights', 'Delete access denied.'));
        $this->addAclPermission('updateSupplierAction', 'update', $namespace->get('no_update_rights', 'Update access denied.'));
        $this->addAclPermission('createSupplierAction', 'create', $namespace->get('no_create_rights', 'Create access denied.'));
    }

    /**
     * Internal helper function to get access to the form repository.
     *
     * @return \Shopware\Models\Article\Repository
     */
    private function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        }

        return $this->repository;
    }
}
