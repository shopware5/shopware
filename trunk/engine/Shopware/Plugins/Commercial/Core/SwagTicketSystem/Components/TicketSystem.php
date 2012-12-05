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
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     M.Schmaeing
 * @author     $Author$
 */

/**
 * Shopware Ticket System Component
 */
class Shopware_Components_TicketSystem extends Enlight_Class
{

    /**
     * Constant for the direction of an ticket
     */
    const ANSWER_DIRECTION_OUT = 'OUT';

    /**
     * Constant for the direction of an ticket
     */
    const ANSWER_DIRECTION_IN = 'IN';

    /**
     * Constant for the direction of an ticket
     */
    const SUPPORT_TYPE_DIRECT = 'direct';

    /**
     * Constant for the direction of an ticket
     */
    const SUPPORT_TYPE_MANAGE = 'manage';

    /**
     * Constant for the name of the registered user submission
     */
    const REGISTERED_USER_SUBMISSION = 'sSTRAIGHTANSWER';

    /**
     * Constant for the name of the not registered user submission
     */
    const NOT_REGISTERED_USER_SUBMISSION = 'sSTRAIGHTANSWER_UNREG';

    /**
     * Constant for the name of the new ticket submission
     */
    const NOTIFY_NEW_TICKET_SUBMISSION = 'sTICKETNOTIFYMAILNEW';

    /**
     * Constant for the name of the ticket answer submission
     */
    const NOTIFY_TICKET_ANSWER_SUBMISSION = 'sTICKETNOTIFYMAILANS';

    /**
     * Constant for the name of the customer notification submission
     */
    const NOTIFY_CUSTOMER_SUBMISSION = 'sTICKETNOTIFYMAILCOSTUMER';

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
     * @var \Shopware\CustomModels\Ticket\Submission\Repository
     */
    protected $ticketSubmissionRepository;

    /**
     * @var \Shopware\CustomModels\Ticket\Status\Repository
     */
    protected $statusRepository;

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
     * Helper Method to get access to the ticket repository.
     *
     * @return Shopware\CustomModels\Ticket\Repository
     */
    public function getTicketSubmissionRepository()
    {
        if ($this->ticketSubmissionRepository === null) {
            $this->ticketSubmissionRepository = $this->getManager()->getRepository('Shopware\CustomModels\Ticket\Submission');
        }
        return $this->ticketSubmissionRepository;
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
     * creates and sends the answer to the customer
     *
     * @param $ticketId
     * @param $statusId
     * @param $mailData
     * @param $answerOnlyOnEmail
     * @param $ticketUniqueLink
     * @return bool|string
     */
    public function createAnswer($ticketId, $statusId, $mailData, $answerOnlyOnEmail, $ticketUniqueLink = null)
    {
        $mailData["subject"] = $this->renderMailData($ticketId, $mailData["subject"]);
        $mailData["message"] = $this->renderMailData($ticketId, $mailData["message"]);

        if (!empty($answerOnlyOnEmail)) {
            //sends the ticket mail to the customer
            $returning = $this->sendTicketMail(
                $mailData["isHTML"],
                $mailData["email"],
                $mailData["cc"],
                $mailData["senderAddress"],
                $mailData["senderName"],
                $mailData["subject"],
                $mailData["message"]
            );
        } else {
            $submissionName = ($this->isUserRegistered($ticketId)) ? self::REGISTERED_USER_SUBMISSION : self::NOT_REGISTERED_USER_SUBMISSION;
            //send notification to the customer to inform them that a new mail has arrived
            $returning = $this->sendSubmissionMail($ticketId, $mailData, $submissionName, $mailData["shop"], $ticketUniqueLink);
        }

        if ($returning !== true) {
            return $returning;
        }


        //set status of the ticket
        $this->setTicketStatus($ticketId, $statusId);


        $loggedInUser = Shopware()->Auth()->getIdentity();
        $swUser = '';
        if($loggedInUser !== null) {
            $swUser = $loggedInUser->name;
        }

        //saves the message to the history
        $this->addMessageToTicketHistory(
            $ticketId,
            $mailData["subject"],
            $mailData["message"],
            $answerOnlyOnEmail,
            $mailData["email"],
            self::ANSWER_DIRECTION_OUT,
            $swUser
        );

        return true;
    }

    /**
     * Sends the configured notification eMails to the customer and the shop operator
     *
     * @param $ticketId
     * @return bool|string
     */
    public function sendNotificationEmails($ticketId)
    {
        $config = Shopware()->Plugins()->Core()->SwagTicketSystem()->Config();

        if(!empty($config->sendShopOperatorNotification)) {
                $sql= "SELECT id FROM `s_ticket_support_history` WHERE ticketID = ?";
                $historyData = Shopware()->Db()->fetchOne($sql, array($ticketId));
                if(empty($historyData)) {
                    $this->sendSubmissionMail(
                        $ticketId,
                        array('email' => Shopware()->Config()->mail),
                        self::NOTIFY_NEW_TICKET_SUBMISSION,
                        Shopware()->Shop()->getId()
                    );
                }
                else {
                    $this->sendSubmissionMail(
                        $ticketId,
                        array('email' => Shopware()->Config()->mail),
                        self::NOTIFY_TICKET_ANSWER_SUBMISSION,
                        Shopware()->Shop()->getId()
                    );
                }
        }

        if(!empty($config->sendCustomerNotification)) {
            /** @var $ticketModel \Shopware\CustomModels\Ticket\Support */
            $ticketModel = $this->getTicketRepository()->find($ticketId);

            if($ticketModel !== null) {
                $this->sendSubmissionMail(
                    $ticketId,
                    array('email' => $ticketModel->getEmail()),
                    self::NOTIFY_CUSTOMER_SUBMISSION,
                    Shopware()->Shop()->getId()
                );
            }
        }
    }

    /**
     * This method sends a submissino mail depending on the ticketId and the submission name
     * This method loads the submission data and sends a mail with the loaded data
     * Additional mail data can be added
     *
     * @param $ticketId
     * @param $mailData
     * @param $submissionName
     * @param null $shopId
     * @param null $ticketUniqueLink
     * @return bool|string
     */
    public function sendSubmissionMail($ticketId, $mailData, $submissionName, $shopId = null, $ticketUniqueLink = null) {

        /** @var ticketModel \Shopware\CustomModels\Ticket\Support */
        $repository = $this->getTicketRepository();
        if (!empty($shopId)) {
            $dataQuery = $repository->getSystemSubmissionQuery($submissionName, $shopId);
            $submissionModel = $dataQuery->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
            if ($submissionModel === null) {
                //fallback get the default data
                $dataQuery = $repository->getSystemSubmissionQuery($submissionName);
                $submissionModel = $dataQuery->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
            }
        } else {
            $dataQuery = $repository->getSystemSubmissionQuery($submissionName);
            $submissionModel = $dataQuery->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
        }

        $isHTML = $submissionModel->getIsHTML();
        $isHTML = empty($isHTML) ? false : $submissionModel->getIsHTML();
        return $this->sendTicketMail(
            $isHTML,
            $mailData["email"],
            $mailData["cc"],
            $submissionModel->getFromMail(),
            $submissionModel->getFromName(),
            $this->renderMailData($ticketId, $submissionModel->getSubject(), $ticketUniqueLink),
            $this->renderMailData($ticketId, $submissionModel->getContent(), $ticketUniqueLink),
            $this->renderMailData($ticketId, $submissionModel->getContentHTML(), $ticketUniqueLink)
        );
    }


    /**
     * Sets the status of a ticket
     *
     * @param $ticketId
     * @param $statusId
     */
    public function setTicketStatus($ticketId, $statusId)
    {
        if (!empty($statusId) && !empty($ticketId)) {
            /** @var ticketModel \Shopware\CustomModels\Ticket\Support */
            $ticketModel = $this->getTicketRepository()->find($ticketId);
            $statusModel = $this->getStatusRepository()->find($statusId);

            if (!empty($statusModel) && is_object($statusModel) && !empty($ticketModel) && is_object($ticketModel)) {
                $ticketModel->setStatus($statusModel);
            }
            Shopware()->Models()->flush();
        }

    }

    /**
     * add an item to the ticket history
     *
     * @param $ticketId
     * @param $subject
     * @param $message
     * @param $answerOnlyOnEmail
     * @param $receiverAddress
     * @param $direction
     * @param $swUser
     * @return void
     * @internal param $statusId
     */
    public function addMessageToTicketHistory($ticketId, $subject, $message, $answerOnlyOnEmail,$receiverAddress, $direction, $swUser = null)
    {
        if (!empty($ticketId)) {
            /** @var ticketModel \Shopware\CustomModels\Ticket\Support */
            $ticketModel = $this->getTicketRepository()->find($ticketId);
            $history = new \Shopware\CustomModels\Ticket\History();
            $history->setMessage(nl2br($message));
            $history->setSubject($subject);
            $history->setDirection($direction);
            $history->setReceiver($receiverAddress);
            if(!empty($swUser)) {
                $history->setSwUser($swUser);
            }
            $supportType = $answerOnlyOnEmail ? self::SUPPORT_TYPE_DIRECT : self::SUPPORT_TYPE_MANAGE;
            $history->setSupportType($supportType);
            $history->setReceipt(new \DateTime('now'));
            $history->setTicket($ticketModel);
            Shopware()->Models()->persist($history);
            Shopware()->Models()->flush();
        }
    }

    /**
     * sends the ticket mail
     *
     * @param $isHTML
     * @param $eMailAddress
     * @param $cc
     * @param $senderAddress
     * @param $senderName
     * @param $subject
     * @param $plainMessage
     * @param null $htmlMessage
     * @return bool|string
     */
    public function sendTicketMail($isHTML, $eMailAddress, $cc = null, $senderAddress, $senderName, $subject, $plainMessage, $htmlMessage = null)
    {

        if(is_string($isHTML)) {
            $isHTML = $isHTML == "false" ? false : true;
        }

        $mail = new Enlight_Components_Mail('UTF-8');
        $mail->addTo($eMailAddress);
        if(!empty($cc)) {
            $mail->addCc($cc);
        }
        $mail->setFrom($senderAddress, $senderName);
        $mail->setSubject($subject);
        if ($isHTML) {

            if(!empty($htmlMessage)) {
                $mail->setBodyHtml(nl2br($htmlMessage));
            }
            else {
                $mail->setBodyHtml(nl2br($plainMessage));
            }
        } else {
            //replace br to nl
            $plainMessage = preg_replace('#<br\s*/?>#i', "\n", $plainMessage);
            $mail->setBodyText(strip_tags(html_entity_decode($plainMessage,null, 'UTF-8')));
        }

        $mail->IsHTML($isHTML);


        try {
            $mail->send();
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    /**
     * replaces the variables of the eMail Template
     *
     * @param $ticketId
     * @param $mailData
     * @param null $ticketLink
     * @return mixed
     */
    public function renderMailData($ticketId, $mailData, $ticketLink = null)
    {
        if(!empty($ticketLink)) {
            $mailData = str_replace('{sTicketDirectUrl}', $ticketLink, $mailData);
        }
        return str_replace('{sTicketID}', '#' . $ticketId, $mailData);
    }

    /**
     * returns true if the user is a registered user
     * @param $ticketId
     * @return bool
     */
    protected function isUserRegistered($ticketId)
    {
        /** @var ticketModel \Shopware\CustomModels\Ticket\Support */
        $ticketModel = $this->getTicketRepository()->find($ticketId);
        if (!empty($ticketModel) && is_object($ticketModel)) {
            $customerModel = $ticketModel->getCustomer();
            if (!empty($customerModel) && is_object($customerModel)) {
                $customerId = $customerModel->getId();
                return !empty($customerId);
            }
        }
        return false;
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


}
