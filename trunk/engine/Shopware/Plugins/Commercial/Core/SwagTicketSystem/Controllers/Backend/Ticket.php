<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage TicketSystem
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     M.Schmaeing
 */
class Shopware_Controllers_Backend_Ticket extends Shopware_Controllers_Backend_ExtJs
{

    /**
     * Entity Manager
     * @var null
     */
    protected $manager = null;

    /**
     * @var \Shopware\CustomModels\Ticket\Repository
     */
    protected $ticketRepository;

    /**
     * @var \Shopware\CustomModels\Ticket\Status\Repository
     */
    protected $statusRepository;

    /**
     * @var \Shopware\CustomModels\Ticket\Type\Repository
     */
    protected $ticketTypeRepository;

    /**
     * @var \Shopware\CustomModels\Ticket\Mail\Repository
     */
    protected $ticketMailRepository;


    /**
     * The init function calls first the parent init function. After the parent classes initialed,
     * the ticket overview will be refreshed over the "refreshTicketList" function.
     *
     * @public
     * @return void
     */
    public function init() {
        $this->View()->addTemplateDir(dirname(__FILE__) . "/../../Views/");
        parent::init();
    }


    /**
     * Registers the different acl permission for the different controller actions.
     *
     * @return void
     */
    protected function initAcl()
    {
        /**
         * read permissions
         */
        $this->addAclPermission('getCustomerList', 'read','Insufficient Permissions');
        $this->addAclPermission('getForms', 'read','Insufficient Permissions');
        $this->addAclPermission('getList', 'read','Insufficient Permissions');
        $this->addAclPermission('getMailList', 'read','Insufficient Permissions');
        $this->addAclPermission('getShopsWithOutSubmissions', 'read','Insufficient Permissions');
        $this->addAclPermission('getShopsWithSubmissions', 'read','Insufficient Permissions');
        $this->addAclPermission('getStatusList', 'read','Insufficient Permissions');
        $this->addAclPermission('getTicketHistory', 'read','Insufficient Permissions');
        $this->addAclPermission('getTicketTypes', 'read','Insufficient Permissions');

        /**
         * update permissions
         */
        $this->addAclPermission('answerTicket', 'update','Insufficient Permissions');
        $this->addAclPermission('saveMapping', 'update','Insufficient Permissions');
        $this->addAclPermission('updateForm', 'update','Insufficient Permissions');
        $this->addAclPermission('updateMail', 'update','Insufficient Permissions');
        $this->addAclPermission('updateTicket', 'update','Insufficient Permissions');
        $this->addAclPermission('updateTicketType', 'update','Insufficient Permissions');

        /**
         * create permissions
         */
        $this->addAclPermission('createMail', 'create','Insufficient Permissions');
        $this->addAclPermission('createTicketType', 'create','Insufficient Permissions');
        $this->addAclPermission('duplicateMails', 'create','Insufficient Permissions');
        $this->addAclPermission('redirectToForm', 'create','Insufficient Permissions');

        /**
         * delete permissions
         */
        $this->addAclPermission('deleteMailSubmissionByShopId', 'delete','Insufficient Permissions');
        $this->addAclPermission('destroyMail', 'delete','Insufficient Permissions');
        $this->addAclPermission('destroyTicket', 'delete','Insufficient Permissions');
        $this->addAclPermission('destroyTicketType', 'delete','Insufficient Permissions');
    }


    /**
     * returns a JSON string to with all found items for the backend listing
     *
     * @return void
     */
    public function getListAction()
    {
        try {
            $limit = intval($this->Request()->limit);
            $offset = intval($this->Request()->start);
            $userId = intval($this->Request()->userId);
            $statusId = intval($this->Request()->statusId);

            /** @var $filter array */
            $filter = $this->Request()->getParam('filter', array());

            //order data
            $order = (array)$this->Request()->getParam('sort', array());

            /** @var $repository \Shopware\CustomModels\Ticket\Repository */
            $repository = $this->getTicketRepository();
            $dataQuery = $repository->getListQuery($userId, $statusId, $filter, $order, $offset, $limit);

            $totalCount = $this->getManager()->getQueryCount($dataQuery);
            $data = $dataQuery->getArrayResult();

            $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $totalCount));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * returns a JSON string to with all found items for the backend listing
     *
     * @return void
     */
    public function getStatusListAction()
    {
        /** @var $repository \Shopware\CustomModels\Ticket\Repository */
        $repository = $this->getTicketRepository();
        $dataQuery = $repository->getStatusListQuery();
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * returns a JSON string to with all found item for the backend listing
     *
     * @return void
     */
    public function getMailListAction()
    {
        $locale = intval($this->Request()->locale);
        $onlyCustomSubmissions = (bool)$this->Request()->onlyCustomSubmissions;
        $onlyDefaultSubmission = (bool)$this->Request()->onlyDefaultSubmission;

        /** @var $repository \Shopware\CustomModels\Ticket\Repository */
        $repository = $this->getTicketRepository();
        $dataQuery = $repository->getMailListQuery($locale, $onlyCustomSubmissions, $onlyDefaultSubmission);
        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $totalCount));
    }

    /**
     * returns a JSON string to with all found item for the backend listing
     *
     * @return void
     */
    public function getCustomerListAction()
    {
        $limit = intval($this->Request()->limit);
        $offset = intval($this->Request()->start);
        $sort = $this->Request()->getParam('sort', array(array('property' => 'customer.id', 'direction' => 'DESC')));
        $filter = $this->Request()->getParam('query', null);

        /** @var $repository \Shopware\Models\Customer\Repository */
        $repository = $this->getManager()->getRepository('Shopware\Models\Customer\Customer');
        $dataQuery = $repository->getListQuery($filter, null, $sort, $limit, $offset);
        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $totalCount));
    }

    /**
     * Returns the list of ticket types
     */
    public function getTicketTypesAction() {

        $limit = intval($this->Request()->limit);
        $offset = intval($this->Request()->start);

        /** @var $filter array */
        $filter = $this->Request()->getParam('filter', array());

        //order data
        $order = (array)$this->Request()->getParam('sort', array());

        /** @var $repository \Shopware\CustomModels\Ticket\Repository */
        $repository = $this->getTicketRepository();
        $dataQuery = $repository->getTicketTypeListQuery($filter, $order, $offset, $limit);
        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $totalCount));
    }

    /**
     * Shops that have already email submission which can be deleted
     */
    public function getShopsWithSubmissionsAction() {

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('shops.id','shops.name'))
                ->from('Shopware\CustomModels\Ticket\Mail', 'mail')
                ->leftJoin('mail.shop', 'shops')
                ->groupBy('shops');
        $dataQuery = $builder->getQuery();
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data));
    }
    
    /**
     * Shops that haven't  email submission which can be added
     */
    public function getShopsWithOutSubmissionsAction() {

        $sql= "SELECT shops.id as id, shops.name as name
                FROM `s_core_shops` as shops
                WHERE id NOT IN (
                    SELECT DISTINCT shop_id as id
                    FROM `s_ticket_support_mails`
                )";
        $data = Shopware()->Db()->fetchAll($sql, array());
        $this->View()->assign(array('success' => true, 'data' => $data));
    }


    

    /**
     * Returns the form data for the form mapping
     */
    public function getFormsAction() {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('forms', 'fields'))
                ->from('Shopware\Models\Form\Form', 'forms')
                ->leftJoin('forms.fields', 'fields')
                ->orderBy('forms.name','ASC');
        $dataQuery = $builder->getQuery();
        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $builder->getQuery()->getArrayResult();
        $formMapping = array(
            "message" => 0,
            "subject" => 0,
            "author" => 0,
            "email" => 0,
        );

        foreach ($data as &$form) {
            foreach ($form["fields"] as $field) {
                $ticketTask = $field["ticketTask"];
                if(!empty($ticketTask)) {
                    if(in_array($ticketTask, array_keys($formMapping))) {
                        $formMapping[$ticketTask] = $field["id"];
                    }
                }
            }
            $form["mapping"] = $formMapping;
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $totalCount));
    }


    /**
     * save the the mapping form data
     */
    public function updateFormAction()
    {
        try {
            $formId = intval($this->Request()->id);
            $ticketTypeId = intval($this->Request()->ticketTypeid);

            $formRepository = $this->getManager()->getRepository('Shopware\Models\Form\Form');
            $fieldModel = $formRepository->find($formId);
            if(!empty($fieldModel) && is_object($fieldModel)) {
                $fieldModel->setTicketTypeid($ticketTypeId);
                Shopware()->Models()->persist($fieldModel);
                Shopware()->Models()->flush();
            }
            $this->View()->assign(array('success' => true, 'data' => $this->Request()->getParams()));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * save the mapping of the form
     */
    public function saveMappingAction()
    {
        try {
            $mapping = array(
                "message" => intval($this->Request()->message),
                "subject" => intval($this->Request()->subject),
                "author" => intval($this->Request()->author),
                "email" => intval($this->Request()->email)
            );

            $formFieldRepository = $this->getManager()->getRepository('Shopware\Models\Form\Field');

            foreach ($mapping as $key => $value) {
                if (!empty($value)) {
                    $fieldModel = $formFieldRepository->find($value);
                    if(!empty($fieldModel) && is_object($fieldModel)) {
                        $fieldModel->setTicketTask($key);
                        Shopware()->Models()->persist($fieldModel);
                    }
                }
            }
            Shopware()->Models()->flush();

            $this->View()->assign(array('success' => true, 'data' => $this->Request()->getParams()));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }


    /**
     * Deletes a ticket from the database
     *
     * @return void
     */
    public function updateTicketAction()
    {
        $ticketId = intval($this->Request()->id);
        $employeeId = intval($this->Request()->employeeId);
        $statusId = intval($this->Request()->statusId);
        try {
            /** @var ticketModel \Shopware\CustomModels\Ticket\Support */
            $ticketModel = $this->getTicketRepository()->find($ticketId);
            $ticketModel->setEmployeeId($employeeId);
            if(!empty($statusId)) {
                $statusModel = $this->getStatusRepository()->find($statusId);
                if(!empty($statusModel) && is_object($statusModel)) {
                    $ticketModel->setStatus($statusModel);
                }
            }

            Shopware()->Models()->persist($ticketModel);
            Shopware()->Models()->flush();
            $this->View()->assign(array('success' => true, 'data' => $this->Request()->getParams()));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }


    /**
     * Submits the answer to the Customer
     *
     * @return void
     */
    public function answerTicketAction() {

        $ticketId = intval($this->Request()->id);
        $statusId = intval($this->Request()->status);
        $params = $this->Request()->getParams();

        $ticketSystem = Shopware()->TicketSystem();

        //returns the link to the frontend which can be used by the unregistered user to view the ticket
        $ticketUniqueLink = $this->getTicketUniqueLink($ticketId);

        $returning = $ticketSystem->createAnswer($ticketId, $statusId, $params, $this->Request()->onlyEmailAnswer, $ticketUniqueLink);

        if($returning !== true) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $returning));
            return;
        }
        $this->View()->assign(array('success' => true));
    }

    /**
     * Creates a new ticket type
     *
     * @return void
     */
    public function createTicketTypeAction() {
        $this->saveTicketType();
    }

    /**
     * Creates a new ticket type
     *
     * @return void
     */
    public function updateTicketTypeAction() {
        $this->saveTicketType();
    }

    /**
     * Creates or updates a new ticket type
     *
     * @return void
     */
    protected function saveTicketType()
    {
        $params = $this->Request()->getParams();

        $id = $this->Request()->id;

        if(!empty($id)){
            //edit Data
            $typeModel = $this->getTicketTypeRepository()->find($id);
        }
        else {
            //new Data
            $typeModel = new Shopware\CustomModels\Ticket\Type();
        }
        $typeModel->fromArray($params);

        try {
            Shopware()->Models()->persist($typeModel);
            Shopware()->Models()->flush();

            $this->View()->assign(array('success' => true, 'data' => $params));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * create a new mail submission
     */
    public function createMailAction() {
        $this->saveTicketMail();
    }

    /**
     * updates a mail submission
     */
    public function updateMailAction() {
        $this->saveTicketMail();
    }

    /**
     * Creates or updates a mail submission
     *
     * @return void
     */
    protected function saveTicketMail()
    {
        $params = $this->Request()->getParams();

        $id = $this->Request()->id;

        if(!empty($id)) {
            //edit Data
            $ticketMailModel = $this->getTicketMailRepository()->find($id);
        }
        else {
            //new Data
            $ticketMailModel = new Shopware\CustomModels\Ticket\Mail();
        }


        $ticketMailModel->fromArray($params);

        $shopRepository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $shopRepository->find($params["shopId"]);
        if(!empty($shop) && is_object($shop)) {
            $ticketMailModel->setShop($shop);
        }
        else {
            $shop = $shopRepository->find(1);
            $ticketMailModel->setShop($shop);
        }

        try {
            Shopware()->Models()->persist($ticketMailModel);
            Shopware()->Models()->flush();

            $this->View()->assign(array('success' => true, 'data' => $params));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * Deletes a ticket mail from the database
     *
     * @return void
     */
    public function destroyMailAction()
    {
        try {
            /** @var $model \Shopware\CustomModels\Ticket\Support */
            $model = $this->getTicketMailRepository()->find($this->Request()->id);
            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(array('success' => true, 'data' => $this->Request()->getParams()));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * duplicates ticket mail submissions from the database
     *
     * @return void
     */
    public function duplicateMailsAction()
    {
        $baseShopId = intval($this->Request()->baseShopId);
        $newShopId = intval($this->Request()->newShopId);
        $duplicateIndividualSubmissions = intval($this->Request()->duplicateIndividualSubmissions);

        if (!empty($baseShopId) && !empty($newShopId)) {
            $sqlPart = "";
            if (empty($duplicateIndividualSubmissions)) {
                $sqlPart = "AND sys_dependent = 1";
            }
            $sql = " INSERT INTO s_ticket_support_mails (
                    name, description, frommail, fromname, subject, content, contentHTML, ishtml,
                    attachment, sys_dependent, isocode, shop_id
                )
                SELECT name, description, frommail, fromname, subject, content, contentHTML, ishtml,
                    attachment, sys_dependent, isocode, ?
                FROM  s_ticket_support_mails WHERE shop_id = ?" . $sqlPart;
            Shopware()->Db()->query($sql, array($newShopId, $baseShopId));

            $this->View()->assign(array('success' => true, 'data' => $this->Request()->getParams()));
        }
    }

    /**
     * deletes the mail submissions based on the shop id
     *
     * @return void
     */
    public function deleteMailSubmissionByShopIdAction()
    {
        try {
            $shopId = intval($this->Request()->shopId);
            $dql = 'DELETE Shopware\CustomModels\Ticket\Mail mail WHERE mail.shopId = ?1';
            $query = $this->getManager()->createQuery($dql);
            $query->setParameter(1, $shopId);
            $query->execute();
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
        $this->View()->assign(array('success' => true, 'data' => $this->Request()->getParams()));
    }



    /**
     * Deletes a ticket from the database
     *
     * @return void
     */
    public function destroyTicketAction()
    {
        try {
            /** @var $model \Shopware\CustomModels\Ticket\Support */
            $model = $this->getTicketRepository()->find($this->Request()->id);
            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(array('success' => true, 'data' => $this->Request()->getParams()));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Deletes a ticket type  from the database
     *
     * @return void
     */
    public function destroyTicketTypeAction()
    {
        try {
            /** @var $model \Shopware\CustomModels\Ticket\Type */
            $model = $this->getTicketTypeRepository()->find($this->Request()->id);
            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(array('success' => true, 'data' => $this->Request()->getParams()));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    public function getTicketHistoryAction() {
        $ticketId = intval($this->Request()->id);

        try {
            $dataQuery = $this->getTicketRepository()->getTicketHistoryQuery($ticketId);
            $totalCount = $this->getManager()->getQueryCount($dataQuery);
            $data = $dataQuery->getArrayResult();

            $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $totalCount));

        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Redirect to the frontend to the right form for ticket creation
     */
    public function redirectToFormAction() {
        $formId = intval($this->Request()->formId);
        $customerId = intval($this->Request()->customerId);


        if (!empty($formId)) {
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
            $shop = $repository->getActiveDefault();
            $shop->registerResources(Shopware()->Bootstrap());
            Shopware()->Models()->clear();

            $params = array(
                'module' => 'frontend',
                'controller' => 'ticket',
                'sFid' => $formId
            );
            if(!empty($customerId)) {
                $params["customerId"] = $customerId;
            }

            $url = $this->Front()->Router()->assemble($params);
            $this->redirect($url);
        }
    }

    /**
     * Helper Method to get access to the ticket repository.
     *
     * @return Shopware\CustomModels\Ticket\Repository
     */
    public function getTicketRepository()
    {
        if ($this->ticketRepository === null) {
            $this->ticketRepository = $this->getManager()->getRepository('Shopware\CustomModels\Ticket\Support');
        }
        return $this->ticketRepository;
    }

    /**
     * Helper Method to get access to the status repository.
     *
     * @return Shopware\CustomModels\Ticket\Repository
     */
    public function getStatusRepository()
    {
        if ($this->statusRepository === null) {
            $this->statusRepository = $this->getManager()->getRepository('Shopware\CustomModels\Ticket\Status');
        }
        return $this->statusRepository;
    }

    /**
     * Helper Method to get access to the ticket type repository.
     *
     * @return Shopware\CustomModels\Ticket\Repository
     */
    public function getTicketTypeRepository()
    {
        if ($this->ticketTypeRepository === null) {
            $this->ticketTypeRepository = $this->getManager()->getRepository('Shopware\CustomModels\Ticket\Type');
        }
        return $this->ticketTypeRepository;
    }

    /**
     * Helper Method to get access to the ticket mail repository.
     *
     * @return Shopware\CustomModels\Ticket\Repository
     */
    public function getTicketMailRepository()
    {
        if ($this->ticketMailRepository === null) {
            $this->ticketMailRepository = $this->getManager()->getRepository('Shopware\CustomModels\Ticket\Mail');
        }
        return $this->ticketMailRepository;
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return null
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }
        return $this->manager;
    }

    /**
     * Helper method which returns the unique link for the system mail to the frontend
     * This link will be used by the unregistered user to view the ticket answer
     *
     * @param $ticketId
     * @return bool or string | returns false if something goes wrong otherwise the link to the frontend
     */
    public function getTicketUniqueLink($ticketId) {
        /** @var ticketModel \Shopware\CustomModels\Ticket\Support */
        $ticketModel = $this->getTicketRepository()->find($ticketId);

        if (!empty($ticketModel) && is_object($ticketModel)) {
            $uniqueId = $ticketModel->getUniqueId();
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
            $shop = $repository->getActiveDefault();
            $shop->registerResources(Shopware()->Bootstrap());
            Shopware()->Models()->clear();

            $link = $this->Front()->Router()->assemble(array(
                'module' => 'frontend',
                'controller' => 'ticket',
                'action' => 'detail',
                'sAID' => $uniqueId
            ));
            return $link;
        }
        return false;
    }
}