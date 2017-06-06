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
 * Newsletter controller
 */
class Shopware_Controllers_Frontend_Newsletter extends Enlight_Controller_Action
{
    /**
     * Transition method
     * Confirm action method
     *
     * @deprecated
     */
    public function confirmAction()
    {
        // todo@all maybe this method can be deleted once all references are removed
        // transition method
        // confirm check is done via helper method isConfirmed()
        return $this->forward('index');
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        $this->View()->voteConfirmed = $this->isConfirmed();
        $this->View()->assign('sUserLoggedIn', Shopware()->Modules()->Admin()->sCheckUser());

        if (isset($this->Request()->sUnsubscribe)) {
            $this->View()->sUnsubscribe = true;
        } else {
            $this->View()->sUnsubscribe = false;
        }

        $this->View()->_POST = Shopware()->System()->_POST->toArray();

        if (!isset(Shopware()->System()->_POST['newsletter'])) {
            return;
        }

        if (Shopware()->System()->_POST['subscribeToNewsletter'] != 1) {
            // Unsubscribe user
            $this->View()->sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST['newsletter'], true);

            $session = $this->container->get('session');
            if ($session->offsetExists('sNewsletter')) {
                $session->offsetSet('sNewsletter', false);
            }

            return;
        }

        $config = $this->container->get('config');
        $noCaptchaAfterLogin = $config->get('noCaptchaAfterLogin');
        // redirect user if captcha is active and request is sent from the footer
        if ($config->get('newsletterCaptcha') !== 'noCaptcha' &&
            $this->Request()->getPost('redirect') !== null &&
            !($noCaptchaAfterLogin && Shopware()->Modules()->Admin()->sCheckUser())) {
            return;
        }

        if (empty($config->get('sOPTINNEWSLETTER')) || $this->View()->voteConfirmed) {
            $this->View()->sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST['newsletter'], false);
            if ($this->View()->sStatus['code'] == 3) {
                // Send mail to subscriber
                $this->sendMail(Shopware()->System()->_POST['newsletter'], 'sNEWSLETTERCONFIRMATION');
            }
        } else {
            $this->View()->sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST['newsletter'], false);
            if ($this->View()->sStatus['code'] == 3) {
                Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST['newsletter'], true);
                $hash = \Shopware\Components\Random::getAlphanumericString(32);
                $data = serialize(Shopware()->System()->_POST->toArray());

                $link = $this->Front()->Router()->assemble(['sViewport' => 'newsletter', 'action' => 'confirm', 'sConfirmation' => $hash]);

                $this->sendMail(Shopware()->System()->_POST['newsletter'], 'sOPTINNEWSLETTER', $link);

                // Setting status-code
                $this->View()->sStatus = ['code' => 3, 'message' => Shopware()->Snippets()->getNamespace('frontend')->get('sMailConfirmation')];

                Shopware()->Db()->query('
                INSERT INTO s_core_optin (datum,hash,data)
                VALUES (
                now(),?,?
                )
                ', [$hash, $data]);
            }
        }
    }

    /**
     * Listing action method
     */
    public function listingAction()
    {
        $customergroups = $this->getCustomerGroups();
        $customergroups = Shopware()->Db()->quote($customergroups);
        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();

        $page = (int) $this->Request()->getQuery('sPage', 1);
        $perPage = (int) Shopware()->Config()->get('contentPerPage', 12);

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
        $result = Shopware()->Db()->query($sql, [$context->getShop()->getId()]);

        //$count has to be set before calling Router::assemble() because it removes the FOUND_ROWS()
        $sql = 'SELECT FOUND_ROWS() as count_' . md5($sql);
        $count = Shopware()->Db()->fetchOne($sql);
        if ($perPage != 0) {
            $count = ceil($count / $perPage);
        } else {
            $count = 0;
        }

        $content = [];
        while ($row = $result->fetch()) {
            $row['link'] = $this->Front()->Router()->assemble(['action' => 'detail', 'sID' => $row['id']]);
            $content[] = $row;
        }

        $pages = [];
        for ($i = 1; $i <= $count; ++$i) {
            if ($i == $page) {
                $pages['numbers'][$i]['markup'] = true;
            } else {
                $pages['numbers'][$i]['markup'] = false;
            }
            $pages['numbers'][$i]['value'] = $i;
            $pages['numbers'][$i]['link'] = $this->Front()->Router()->assemble(['sViewport' => 'newsletter', 'action' => 'listing', 'sPage' => $i]);
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
        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();

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
        $content = Shopware()->Db()->fetchRow($sql, [$context->getShop()->getId(), $this->Request()->sID]);
        if (!empty($content)) {
            // todo@all Hash-Building in rework phase berücksichtigen
            $license = '';
            $content['hash'] = [$content['id'], $license];
            $content['hash'] = md5(implode('|', $content['hash']));
            $content['link'] = $this->Front()->Router()->assemble(['module' => 'backend', 'controller' => 'newsletter', 'id' => $content['id'], 'hash' => $content['hash'], 'fullPath' => true]);
        }

        $this->View()->sContentItem = $content;
        $this->View()->sBackLink = $this->Front()->Router()->assemble(['action' => 'listing']);
    }

    /**
     * Send mail method
     *
     * @param string      $recipient
     * @param string      $template
     * @param bool|string $optin
     */
    protected function sendMail($recipient, $template, $optin = false)
    {
        $context = [];

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
        if (empty($this->Request()->sConfirmation)) {
            return false;
        }

        $getVote = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_optin WHERE hash = ?',
            [$this->Request()->sConfirmation]
        );

        if (empty($getVote['data'])) {
            return false;
        }

        Shopware()->System()->_POST = unserialize($getVote['data']);

        Shopware()->Db()->query(
            'DELETE FROM s_core_optin WHERE hash = ?',
            [$this->Request()->sConfirmation]
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
        $customergroups = ['EK'];

        $defaultCustomerGroupKey = Shopware()->Shop()->getCustomerGroup()->getKey();
        if (!empty($defaultCustomerGroupKey)) {
            $customergroups[] = $defaultCustomerGroupKey;
        }
        if (!empty(Shopware()->System()->sUSERGROUPDATA['groupkey'])) {
            $customergroups[] = Shopware()->System()->sUSERGROUPDATA['groupkey'];
        }
        $customergroups = array_unique($customergroups);

        return $customergroups;
    }
}
