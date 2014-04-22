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
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Detail extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     *
     * Sets the scope
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
    }

    /**
     * Error action method
     *
     * Read similar products
     */
    public function errorAction()
    {
        $this->Response()->setHttpResponseCode(404);
        $this->View()->sRelatedArticles = Shopware()->Modules()->Marketing()->sGetSimilarArticles($this->Request()->sArticle, 4);
    }

    /**
     * Index action method
     *
     * Read the product details and base rating form data
     * Loads on demand a custom template
     */
    public function indexAction()
    {
        $id = (int) $this->Request()->sArticle;
        $tpl = (string) $this->Request()->template;
        if (empty($id)) {
            return $this->forward('error');
        }

        $this->View()->assign('sAction', isset($this->View()->sAction) ? $this->View()->sAction : 'index', true);
        $this->View()->assign('sErrorFlag', isset($this->View()->sErrorFlag) ? $this->View()->sErrorFlag : array(), true);
        $this->View()->assign('sFormData', isset($this->View()->sFormData) ? $this->View()->sFormData : array(), true);

        if (!empty(Shopware()->Session()->sUserId) && empty($this->Request()->sVoteName)
          && $this->Request()->getParam('__cache') !== null) {
            $userData = Shopware()->Modules()->Admin()->sGetUserData();
            $this->View()->sFormData = array(
                'sVoteMail' => $userData['additional']['user']['email'],
                'sVoteName' => $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname']
            );
        }

        $article = Shopware()->Modules()->Articles()->sGetArticleById($id);

        if (empty($article) || empty($article["articleName"])) {
            return $this->forward('error');
        }

        if (!empty($article['template'])) {
            $this->View()->loadTemplate('frontend/detail/' . $article['template']);
        } elseif (!empty($article['mode'])) {
            $this->View()->loadTemplate('frontend/blog/detail.tpl');
        } elseif ($tpl === 'ajax' || $this->Request()->isXmlHttpRequest()) {
            $this->View()->loadTemplate('frontend/detail/ajax.tpl');
        }

        $article = Shopware()->Modules()->Articles()->sGetConfiguratorImage($article);

        // Was:
        // $article['sBundles'] = Shopware()->Modules()->Articles()->sGetArticleBundlesByArticleID($id);
        // But sGetArticleBundlesByArticleID() always returned false.
        $article['sBundles'] = false;

        if (!empty(Shopware()->Config()->InquiryValue)) {
            $this->View()->sInquiry = $this->Front()->Router()->assemble(array(
                'sViewport' => 'support',
                'sFid' => Shopware()->Config()->InquiryID,
                'sInquiry' => 'detail',
                'sOrdernumber' => $article['ordernumber']
            ));
        }

        if (!empty($article["categoryID"])) {
            $breadcrumb = array_reverse(Shopware()->Modules()->sCategories()->sGetCategoriesByParent($article["categoryID"]));
            $categoryInfo = end($breadcrumb);
        } else {
            $breadcrumb = array();
            $categoryInfo = null;
        }

        $breadcrumb[] = array(
            'link' => $article['linkDetails'],
            'name' => $article['articleName']
        );

        // SW-3493 sArticle->getArticleById and sBasket->sGetGetBasket differ in camelcase
        $article['sReleaseDate'] = $article['sReleasedate'];

        $this->View()->sBreadcrumb = $breadcrumb;
        $this->View()->sCategoryInfo = $categoryInfo;
        $this->View()->sArticle = $article;
        $this->View()->rand = md5(uniqid(rand()));
    }

    /**
     * Rating action method
     *
     * Save and review the product rating
     */
    public function ratingAction()
    {
        $id = (int) $this->Request()->sArticle;
        if (empty($id)) {
            return $this->forward('error');
        }

        $article = Shopware()->Modules()->Articles()->sGetArticleNameByArticleId($id);
        if (empty($article)) {
            return $this->forward('error');
        }

        $voteConfirmed = false;

        if ($hash = $this->Request()->sConfirmation) {
            $getVote = Shopware()->Db()->fetchRow('
                SELECT * FROM s_core_optin WHERE hash = ?
            ', array($hash));
            if (!empty($getVote['data'])) {
                Shopware()->System()->_POST = unserialize($getVote['data']);
                $voteConfirmed = true;
                Shopware()->Db()->query('DELETE FROM s_core_optin WHERE hash = ?', array($hash));
            }
        }

        if (empty(Shopware()->System()->_POST['sVoteName'])) {
            $sErrorFlag['sVoteName'] = true;
        }
        if (empty(Shopware()->System()->_POST['sVoteSummary'])) {
            $sErrorFlag['sVoteSummary'] = true;
        }

        if (!empty(Shopware()->Config()->CaptchaColor) && !$voteConfirmed) {
            $captcha = str_replace(' ', '', strtolower($this->Request()->sCaptcha));
            $rand = $this->Request()->getPost('sRand');
            if (empty($rand) || $captcha != substr(md5($rand), 0, 5)) {
                $sErrorFlag['sCaptcha'] = true;
            }
        }
        $validator = new Zend_Validate_EmailAddress();
        $validator->getHostnameValidator()->setValidateTld(false);
        if (!empty(Shopware()->Config()->sOPTINVOTE)
            && (empty(Shopware()->System()->_POST['sVoteMail'])
                || !$validator->isValid(Shopware()->System()->_POST['sVoteMail']))
        ) {
            $sErrorFlag['sVoteMail'] = true;
        }

        if (empty($sErrorFlag)) {
            if (!empty(Shopware()->Config()->sOPTINVOTE)
                && !$voteConfirmed && empty(Shopware()->Session()->sUserId)
            ) {
                $hash = md5(uniqid(rand()));

                $sql = '
                    INSERT INTO s_core_optin (datum, hash, data)
                    VALUES (NOW(), ?, ?)
                ';
                Shopware()->Db()->query($sql, array(
                    $hash, serialize(Shopware()->System()->_POST)
                ));

                $link = $this->Front()->Router()->assemble(array(
                    'sViewport' => 'detail',
                    'action' => 'rating',
                    'sArticle' => $id,
                    'sConfirmation' => $hash
                ));

                $context = array(
                    'sConfirmLink' => $link,
                    'sArticle' => array('articleName' => $article)
                );

                $mail = Shopware()->TemplateMail()->createMail('sOPTINVOTE', $context);
                $mail->addTo($this->Request()->getParam('sVoteMail'));
                $mail->Send();

            } else {
                unset(Shopware()->Config()->sOPTINVOTE);
                Shopware()->Modules()->Articles()->sSaveComment($id);
            }
        } else {
            $this->View()->sFormData = Shopware()->System()->_POST;
            $this->View()->sErrorFlag = $sErrorFlag;
        }

        $this->View()->sAction = 'ratingAction';

        $this->forward('index');
    }
}
