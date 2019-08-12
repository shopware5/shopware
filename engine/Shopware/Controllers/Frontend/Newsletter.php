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

class Shopware_Controllers_Frontend_Newsletter extends Enlight_Controller_Action
{
    /**
     * Index action method
     */
    public function indexAction()
    {
        $this->View()->assign('voteConfirmed', $this->isConfirmed());
        $this->Request()->setParam('voteConfirmed', $this->View()->voteConfirmed);
        $this->View()->assign('sUserLoggedIn', Shopware()->Modules()->Admin()->sCheckUser());

        $this->front->setParam('voteConfirmed', $this->View()->voteConfirmed);
        $this->front->setParam('optinNow', (new \DateTime())->format('Y-m-d H:i:s'));

        if ($this->Request()->get('sUnsubscribe') !== null) {
            $this->View()->assign('sUnsubscribe', true);
        } else {
            $this->View()->assign('sUnsubscribe', false);
        }

        $this->View()->assign('_POST', Shopware()->System()->_POST->toArray());

        if (!isset(Shopware()->System()->_POST['newsletter'])) {
            return;
        }

        if (Shopware()->System()->_POST['subscribeToNewsletter'] != 1) {
            // Unsubscribe user
            $this->View()->assign('sStatus', Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST['newsletter'], true));

            $session = $this->container->get('session');
            if ($session->offsetExists('sNewsletter')) {
                $session->offsetSet('sNewsletter', false);
            }

            return;
        }

        $config = $this->container->get('config');
        $noCaptchaAfterLogin = $config->get('noCaptchaAfterLogin');
        // redirect user if captcha is active and request is sent from the footer
        if ($config->get('newsletterCaptcha') !== 'noCaptcha'
            && $this->Request()->getPost('redirect') !== null
            && !($noCaptchaAfterLogin && Shopware()->Modules()->Admin()->sCheckUser())) {
            return;
        }

        if (empty($config->get('sOPTINNEWSLETTER')) || $this->View()->getAssign('voteConfirmed')) {
            $this->View()->assign('sStatus', Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST['newsletter']));
            if ($this->View()->getAssign('sStatus')['code'] == 3 && $this->View()->getAssign('sStatus')['isNewRegistration']) {
                // Send mail to subscriber
                $this->sendMail(Shopware()->System()->_POST['newsletter'], 'sNEWSLETTERCONFIRMATION');
            }
        } else {
            $this->View()->assign('sStatus', Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST['newsletter']));

            if ($this->View()->getAssign('sStatus')['code'] == 3) {
                if ($this->View()->getAssign('sStatus')['isNewRegistration']) {
                    Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST['newsletter'], true);
                    $hash = \Shopware\Components\Random::getAlphanumericString(32);
                    $data = serialize(Shopware()->System()->_POST->toArray());

                    $link = $this->Front()->Router()->assemble(['sViewport' => 'newsletter', 'action' => 'index', 'sConfirmation' => $hash]);

                    $this->sendMail(Shopware()->System()->_POST['newsletter'], 'sOPTINNEWSLETTER', $link);

                    Shopware()->Db()->query('
                    INSERT INTO s_core_optin (datum,hash,data,type)
                    VALUES (
                    now(),?,?,"swNewsletter"
                    )
                    ', [$hash, $data]);
                }

                $this->View()->assign('sStatus', ['code' => 3, 'message' => Shopware()->Snippets()->getNamespace('frontend')->get('sMailConfirmation')]);
            }
        }
    }

    /**
     * Listing action method
     */
    public function listingAction()
    {
        if (strpos($this->Request()->getPathInfo(), '/newsletterListing') === 0) {
            $this->redirect(['controller' => 'newsletter', 'action' => 'listing', 'module' => 'frontend'], ['code' => 301]);

            return;
        }

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

        // $count has to be set before calling Router::assemble() because it removes the FOUND_ROWS()
        $sql = 'SELECT FOUND_ROWS() as count_' . md5($sql);
        $count = Shopware()->Db()->fetchOne($sql);
        if ($perPage !== 0) {
            $count = ceil($count / $perPage);
        } else {
            $count = 0;
        }

        $content = [];
        while ($row = $result->fetch()) {
            $row['link'] = $this->Front()->Router()->assemble(['action' => 'detail', 'sID' => $row['id'], 'p' => $page]);
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
            $pages['numbers'][$i]['link'] = $this->Front()->Router()->assemble(['sViewport' => 'newsletter', 'action' => 'listing', 'p' => $i]);
        }

        $this->View()->assign('sPage', $page);
        $this->View()->assign('sNumberPages', $count);
        $this->View()->assign('sPages', $pages);
        $this->View()->assign('sContent', $content);
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
        $content = Shopware()->Db()->fetchRow($sql, [$context->getShop()->getId(), $this->Request()->get('sID')]);
        if (!empty($content)) {
            // todo@all Mind hash-building in rework phase
            $license = '';
            $content['hash'] = [$content['id'], $license];
            $content['hash'] = md5(implode('|', $content['hash']));
            $content['link'] = $this->Front()->Router()->assemble(['module' => 'backend', 'controller' => 'newsletter', 'id' => $content['id'], 'hash' => $content['hash'], 'fullPath' => true]);
        }

        $this->View()->assign('sContentItem', $content);
        $this->View()->assign('sBackLink', $this->Front()->Router()->assemble(['action' => 'listing']) . '?p=' . (int) $this->Request()->getParam('p', 1));
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

        $context = Shopware()->Events()->filter('Shopware_Controllers_Frontend_Newsletter_sendMail_FilterVariables', $context, [
            'template' => $template,
            'recipient' => $recipient,
            'optin' => $optin,
        ]);

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
            [$this->Request()->get('sConfirmation')]
        );

        if (empty($getVote['data'])) {
            return false;
        }

        // Needed for 'added' date
        $this->front->setParam('optinDate', $getVote['datum']);

        Shopware()->System()->_POST = unserialize($getVote['data'], ['allowed_classes' => false]);

        Shopware()->Db()->query(
            'DELETE FROM s_core_optin WHERE hash = ?',
            [$this->Request()->get('sConfirmation')]
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
