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

/**
 * Newsletter controller
 */
class Shopware_Controllers_Frontend_Newsletter extends Enlight_Controller_Action
{
    /**
     * Transition method
     * Confirm action method
     * @deprecated
     */
    public function confirmAction()
    {
        // todo@all maybe this method can be deleted once all references are removed
        // transition method
        // confirm check is done via helper method isConfirmed()
        return $this->forward("index");
    }

    /**
     * Send mail method
     *
     * @param string $recipient
     * @param string $template
     * @param boolean|string $optin
     */
    protected function sendMail($recipient, $template, $optin = false)
    {
        $context = array();

        if (!empty($optin)) {
            $context['sConfirmLink'] = $optin;
        }

        foreach ($this->Request()->getPost() as $key => $value) {
            $context['sUser.' . $key] = $value;
            $context['sUser'][$key] = $value;
        }

        $mail = Shopware()->TemplateMail()->createMail($template, $context);
        $mail->addTo($recipient);
        $mail->send();
    }

    /**
     * Returns whether or not the current request contains
     * a valid newsletter confirmation
     *
     * @return bool
     */
    protected function isConfirmed()
    {
        if (empty($this->request()->sConfirmation)) {
            return false;
        }

        $getVote = Shopware()->Db()->fetchRow(
            "SELECT * FROM s_core_optin WHERE hash = ?",
            array($this->request()->sConfirmation)
        );

        if (empty($getVote["data"])) {
            return false;
        }

        Shopware()->System()->_POST = unserialize($getVote["data"]);

        Shopware()->Db()->query(
            "DELETE FROM s_core_optin WHERE hash = ?",
            array($this->request()->sConfirmation)
        );

        return true;
    }


    /**
     * Returns customer groups
     *
     * @return array
     */
    protected function getCustomerGroups()
    {
        $customergroups = array('EK');
        if (!empty(Shopware()->System()->sSubShop['defaultcustomergroup'])) {
            $customergroups[] = Shopware()->System()->sSubShop['defaultcustomergroup'];
        }
        if (!empty(Shopware()->System()->sUSERGROUPDATA['groupkey'])) {
            $customergroups[] = Shopware()->System()->sUSERGROUPDATA['groupkey'];
        }
        $customergroups = array_unique($customergroups);
        return $customergroups;
    }

    /**
     * Index action method
     */
    public function indexAction()
    {

        if (isset($this->Request()->sUnsubscribe)) {
            $this->View()->sUnsubscribe = true;
        } else {
            $this->View()->sUnsubscribe = false;
        }

        $voteConfirmed = $this->isConfirmed();
        $email = $this->Request()->getPost('newsletter');
        $groupID = $this->Request()->getPost('groupID');
        $subscribe = $this->Request()->getPost('subscribeToNewsletter');
        $requestData = $this->Request()->getPost();
        $this->View()->assign('_POST', $requestData);
        $this->View()->assign('voteConfirmed', $voteConfirmed);

        if (!isset($email)) {
            return;
        }

        // Unsubscribe user
        if ($subscribe != 1) {
            $sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription($email, true, $groupID);
            $this->View()->assign('sStatus', $sStatus);
            return;
        }

        if (empty(Shopware()->Config()->sOPTINNEWSLETTER) || $voteConfirmed) {
            $sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription($email, false, $groupID);
            if ($sStatus['code'] == 3) {
                // Send mail to subscriber
                $this->sendMail($email, 'sNEWSLETTERCONFIRMATION');
            }
        } else {
            $sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription($email, false, $groupID);
            if ($sStatus["code"] == 3) {

                Shopware()->Modules()->Admin()->sNewsletterSubscription($email, true, $groupID);
                $hash = md5(uniqid(rand()));
                $data = serialize($requestData);

                $link = $this->Front()->Router()->assemble(array('sViewport' => 'newsletter', 'action' => 'confirm', 'sConfirmation' => $hash));

                $this->sendMail($email, 'sOPTINNEWSLETTER', $link);

                // Setting status-code
                $sStatus = array("code" => 3, "message" => Shopware()->Snippets()->getNamespace('frontend')->get('sMailConfirmation'));

                $optin = new \Shopware\Models\CommentConfirm\CommentConfirm();
                $optin->setCreationDate(new \DateTime());
                $optin->setHash($hash);
                $optin->setData($data);

                Shopware()->Models()->persist($optin);
                Shopware()->Models()->flush();
            }
        }

        $this->View()->assign('sStatus', $sStatus);
    }

    /**
     * Listing action method
     */
    public function listingAction()
    {
        $customergroups = $this->getCustomerGroups();
        $customergroups = Shopware()->Db()->quote($customergroups);

        $page = (int)$this->Request()->getQuery('sPage', 1);
        $perPage = (int)Shopware()->Config()->get('contentPerPage', 12);

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS id, IF(datum IS NULL,'',datum) as `date`, subject as description, sendermail, sendername
            FROM `s_campaigns_mailings`
            WHERE `status`!=0
            AND plaintext=0
            AND `publish`!=0
            AND languageID=?
            AND customergroup IN ($customergroups)
            ORDER BY `id` DESC
        ";
        $sql = Shopware()->Db()->limit($sql, $perPage, $perPage * ($page - 1));
        $result = Shopware()->Db()->query($sql, array(Shopware()->System()->sLanguage));

        $content = array();
        while ($row = $result->fetch()) {
            $row['link'] = $this->Front()->Router()->assemble(array('action' => 'detail', 'sID' => $row['id']));
            $content[] = $row;
        }

        $sql = 'SELECT FOUND_ROWS() as count_' . md5($sql);
        $count = Shopware()->Db()->fetchOne($sql);

        $count = ceil($count / $perPage);

        $pages = array();
        for ($i = 1; $i <= $count; $i++) {
            if ($i == $page) {
                $pages['numbers'][$i]['markup'] = true;
            } else {
                $pages['numbers'][$i]['markup'] = false;
            }
            $pages['numbers'][$i]['value'] = $i;
            $pages['numbers'][$i]['link'] = $this->Front()->Router()->assemble(array('sViewport' => 'newsletter', 'action' => 'listing', 'sPage' => $i));
        }

        $this->View()->sPage = $page;
        $this->View()->sNumberPages = $count;
        $this->View()->sPages = $pages;
        $this->View()->sContent = $content;
    }

    /**
     * Detail action method
     */
    public function detailAction()
    {
        $customergroups = $this->getCustomerGroups();
        $customergroups = Shopware()->Db()->quote($customergroups);

        $sql = "
            SELECT id, IF(datum='00-00-0000','',datum) as `date`, subject as description, sendermail, sendername
            FROM `s_campaigns_mailings`
            WHERE `status`!=0
            AND plaintext=0
            AND publish!=0
            AND languageID=?
            AND id=?
            AND customergroup IN ($customergroups)
        ";
        $content = Shopware()->Db()->fetchRow($sql, array(Shopware()->System()->sLanguage, $this->request()->sID));
        if (!empty($content)) {
            // ($license = Shopware()->License()->getLicense('sCORE')) || ($license = Shopware()->License()->getLicense('sCOMMUNITY'));
            // todo@all Hash-Building in rework phase berücksichtigen
            $license = "";
            $content['hash'] = array($content['id'], $license);
            $content['hash'] = md5(implode('|', $content['hash']));
            $content['link'] = $this->Front()->Router()->assemble(array('module' => 'backend', 'controller' => 'newsletter', 'id' => $content['id'], 'hash' => $content['hash'], 'fullPath' => true));
        }

        $this->View()->sContentItem = $content;
        $this->View()->sBackLink = $this->Front()->Router()->assemble(array('action' => 'listing'));
    }
}
