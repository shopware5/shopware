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
 * @subpackage Ticket
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */
/**
 * Ticket Frontend Controller this controller implements the frontend logic for the ticket system
 */
class Shopware_Controllers_Frontend_Ticket extends Shopware_Controllers_Frontend_Forms
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
     * Show Ticket form
     * @return void
     */
    public function indexAction()
    {

        $customerId = intval($this->Request()->customerId);
        $formId = intval($this->Request()->sFid);
        if (!empty($customerId)) {
            $this->presetForm($formId, $customerId);
        }

        $this->View()->forceMail = intval($this->Request()->forceMail);
        $this->View()->loadTemplate('frontend/forms/index.tpl');
        parent::indexAction();
    }

    /**
     * Save new ticket into database
     * @return void
     */
    public function commitForm()
    {
        if (!empty($this->Request()->forceMail)) {
            return parent::commitForm();
        }
        $id = intval($this->request()->sFid ? $this->request()->sFid : $this->request()->id);
        $formFields = Shopware()->Db()->fetchAll("SELECT * FROM `s_cms_support_fields` WHERE `supportID` = ? ORDER BY `position`", array($id));
        $formData = Shopware()->Db()->fetchRow("SELECT * FROM `s_cms_support` WHERE `id` = ?", array($id));

        $customerEmail = "";
        $userId = 0;

        //create new ticket model
        $ticketModel = new Shopware\CustomModels\Ticket\Support();
        $ticketModel->setAdditional('');
        foreach ($formFields as $field) {
            //check if it is a special field
            if (!empty($field['ticket_task'])) {
                switch ($field['ticket_task']) {
                    case "message":
                        $ticketModel->setMessage($this->_postData[$field['id']]);
                        break;

                    case "email":
                        if (!empty(Shopware()->Session()->sUserMail)) {
                            $this->_postData[$field['id']] = stripcslashes(Shopware()->Session()->sUserMail);
                        }
                        $customerEmail = $this->_postData[$field['id']];
                        $ticketModel->setEmail($customerEmail);
                        break;

                    case "name":
                        if (!empty(Shopware()->Session()->sUserId)) {
                            $name = Shopware()->Modules()->Admin()->sGetUserNameById(Shopware()->Session()->sUserId);
                            $this->_postData[$field['id']] = $name['firstname'] . " " . $name['lastname'];
                        }
                        break;

                    case "subject":
                        $ticketModel->setSubject($this->_postData[$field['id']]);
                        break;
                }
            } else {
                $ticketModel->setAdditional(serialize(array(
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'typ' => $field['typ'],
                    'value' => nl2br(stripcslashes($this->_postData[$field['id']]))
                )));
            }
        }

        if (!empty(Shopware()->Session()->sUserId)) {
            $userId = Shopware()->Session()->sUserId;
        }
        else if(!empty($customerEmail)) {
            $userId = Shopware()->Db()->fetchOne("SELECT id FROM `s_user` WHERE `email` = ?", array($customerEmail));
        }

        $isoCode = !empty($formData["isocode"]) ? $formData["isocode"] : "de";
        $ticketModel->setIsoCode($isoCode);
        $ticketModel->setReceipt(new \DateTime());
        $ticketModel->setLastContact(new \DateTime());
        $ticketModel->setUniqueId(md5(rand(0,1000). time()));

        if (($model = $this->findModelById($userId, 'Shopware\Models\Customer\Customer')) !== null) {
            $ticketModel->setCustomer($model);
        }

        if (($model = $this->findModelById(1, 'Shopware\CustomModels\Ticket\Status')) !== null) {
            $ticketModel->setStatus($model);
        }

        if (($model = $this->findModelById(Shopware()->Shop()->getId(), 'Shopware\Models\Shop\Shop')) !== null) {
            $ticketModel->setShop($model);
        }

        if (($model = $this->findModelById($formData["ticket_typeID"], 'Shopware\CustomModels\Ticket\Type')) !== null) {
            $ticketModel->setType($model);
        }

        Shopware()->Models()->persist($ticketModel);
        Shopware()->Models()->flush();

        $ticketSystem = Shopware()->TicketSystem();
        $ticketSystem->sendNotificationEmails($ticketModel->getId());
    }

    /**
     * Sets the default value to the form
     *
     * @param $formId
     * @param $customerId
     * @return void
     */
    public function presetForm($formId, $customerId) {
        $eMailSupportId = $this->getSupportFieldIdByTicketTask($formId, "email");
        $nameSupportId = $this->getSupportFieldIdByTicketTask($formId, "name");

        $sql = "SELECT email, CONCAT( ub.firstname, ' ', ub.lastname ) AS customerName
                    FROM `s_user` AS u
                    LEFT JOIN s_user_billingaddress AS ub ON ( u.id = ub.userID )
                    WHERE u.id = ?";
        $customerData = Shopware()->Db()->fetchRow($sql, array($customerId));

        if (!empty($customerData)) {
            //to preset the form
            $this->_postData = array($eMailSupportId => $customerData["email"], $nameSupportId => $customerData["customerName"],);
        }
    }

    /**
     * Helper Method to returns the id of the support field
     *
     * @param $formId
     * @param $taskName
     * @return string
     */
    protected function getSupportFieldIdByTicketTask($formId, $taskName) {
        $sql= "SELECT id FROM  s_cms_support_fields WHERE supportID = ? AND ticket_task = ?";
        return Shopware()->Db()->fetchone($sql, array($formId, $taskName));
    }

    /**
     * Helper Method to get the right model
     *
     * @param $id
     * @param $model
     * @return bool|object
     */
    private function findModelById($id, $model) {
        if(!empty($id)) {
            $repository = $this->getManager()->getRepository($model);
            $model = $repository->find($id);

            if(!empty($model) && is_object($model)) {
                return $model;
            }
        }
        return null;
    }

    /**
     * Show ticket history
     * @return void
     */
    public function listingAction(){
        $userID = intval(Shopware()->Session()->sUserId);
        if(!empty($userID)) {
            $result = $this->getTicketRepository()->getFrontendTicketListQuery($userID);
            $ticketStore =  $result->getArrayResult();

            $this->View()->ticketStore = $ticketStore;
        }
        else {
            return $this->forward("index",'account');
        }
    }

    /**
     * Open new ticket mask
     * @return void
     */
    public function requestAction() {
        $config = Shopware()->Plugins()->Core()->SwagTicketSystem()->Config();
        $this->request()->setParam("sFid",$config->ticketAccountFormId);
        $this->View()->loadTemplate('frontend/ticket/request.tpl');
        parent::indexAction();
    }

    /**
     * Show ticket details
     * @return void
     */
    public function detailAction() {
        $ticketId = intval($this->Request()->tid);
        $userId = intval(Shopware()->Session()->sUserId);
        $uniqueId = $this->Request()->sAID;

        if (empty($ticketId) || empty($userId)){
            $checkResult = $this->checkUniqueId($uniqueId);
            if(empty($checkResult)) {
                return $this->forward("index",'account');
            }
            $ticketId = $checkResult;
        }

        $detailQuery = $this->getTicketRepository()->getTicketDetailQuery($ticketId);
        $detailData = $detailQuery->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if(isset($this->Request()->sSubmit) && ($detailData['responsible'] == 1 && $detailData['closed'] != 1))
        {
            $answer = trim(stripslashes($this->request()->sAnswer));
            if(!empty($answer))
            {
                //add answer to ticket system
                $subject = Shopware()->Snippets()->getNamespace('frontend/ticket/detail')->get('TicketDetailInfoAnswerSubject','Answer');
                $ticketSystem = Shopware()->TicketSystem();
                $ticketSystem->addMessageToTicketHistory($ticketId, $subject, nl2br($answer), false, Shopware()->Config()->mail, 'IN');

                //update ticket status status
                $ticketSystem->setTicketStatus($ticketId, 1);

                $this->View()->accept = Shopware()->System()->sCONFIG["sSnippets"]["sTicketSysReplySentSuccessful"];

                //send notification to the customer and the shop host if necessary
                $ticketSystem->sendNotificationEmails($ticketId);
                //set responsible to false so the customer can not answer directly
                $detailData['responsible'] = false;
            }
            else {
                $this->View()->error = Shopware()->System()->sCONFIG["sSnippets"]["sTicketSysFillRequiredFields"];
            }
        }

        $this->View()->ticketDetails = $detailData;
        $dataQuery = $this->getTicketRepository()->getTicketHistoryQuery($ticketId);
        $ticketHistoryData = $dataQuery->getArrayResult();

        $this->View()->ticketHistoryDetails = $ticketHistoryData;
    }

    /**
     * checks the requested uniqueID if it is valid return the corresponding ticketId
     * if not this method will return null
     *
     * @param $uniqueId
     * @return null or the ticketID
     */
    protected function checkUniqueId($uniqueId) {
        $ticketModel = $this->getTicketRepository()->findOneBy(array("uniqueId" => $uniqueId));
        if($ticketModel !== null) {
            $ticketId = $ticketModel->getId();
            if(!empty($ticketId)) {
                return $ticketId;
            }
        }
        return null;
    }


    /**
     * Helper Method to get access to the ticket repository.
     *
     * @return Shopware\CustomModels\Ticket\Repository
     */
    protected function getTicketRepository()
    {
        if ($this->ticketRepository === null) {
            $this->ticketRepository = $this->getManager()->getRepository('Shopware\CustomModels\Ticket\Support');
        }
        return $this->ticketRepository;
    }


    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return null
     */
    protected function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }
        return $this->manager;
    }

}
