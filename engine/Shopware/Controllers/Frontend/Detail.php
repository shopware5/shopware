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

use Shopware\Components\Random;

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
     * Error action method for not found/inactive products
     * Can throw an exception that is handled by the default error controller
     * or show a custom page with related products
     */
    public function errorAction()
    {
        $config = $this->container->get('config');
        if (!$config->get('RelatedArticlesOnArticleNotFound')) {
            throw new Enlight_Controller_Exception('Product not found', 404);
        }

        $this->Response()->setStatusCode(
            $config->get('PageNotFoundCode', 404)
        );
        $this->View()->assign('sRelatedArticles', Shopware()->Modules()->Marketing()->sGetSimilarArticles(
            (int) $this->Request()->sArticle,
            4
        ));
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

        if ($id <= 0) {
            return $this->forward('error');
        }

        $this->View()->assign('sAction', isset($this->View()->sAction) ? $this->View()->sAction : 'index', true);
        $this->View()->assign('sErrorFlag', isset($this->View()->sErrorFlag) ? $this->View()->sErrorFlag : [], true);
        $this->View()->assign('sFormData', isset($this->View()->sFormData) ? $this->View()->sFormData : [], true);
        $this->View()->assign('userLoggedIn', Shopware()->Modules()->Admin()->sCheckUser());

        if (!empty(Shopware()->Session()->sUserId) && empty($this->Request()->sVoteName)
          && $this->Request()->getParam('__cache') !== null) {
            $userData = Shopware()->Modules()->Admin()->sGetUserData();
            $this->View()->assign('sFormData', [
                'sVoteMail' => $userData['additional']['user']['email'],
                'sVoteName' => $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname'],
            ]);
        }

        $number = $this->Request()->getParam('number');
        $selection = $this->Request()->getParam('group', []);

        $categoryId = $this->Request()->get('sCategory');
        if (!$this->isValidCategory($categoryId)) {
            $categoryId = 0;
        }

        try {
            $product = Shopware()->Modules()->Articles()->sGetArticleById(
                $id,
                $categoryId,
                $number,
                $selection
            );
        } catch (\Exception $e) {
            $product = null;
        }

        if (empty($product) || empty($product['articleName'])) {
            return $this->forward('error');
        }

        $this->Request()->setQuery('sCategory', $product['categoryID']);

        $template = trim($product['template']);
        if (!empty($template)) {
            $this->View()->loadTemplate('frontend/detail/' . $product['template']);
        } elseif (!empty($product['mode'])) {
            $this->View()->loadTemplate('frontend/blog/detail.tpl');
        } elseif ($tpl === 'ajax') {
            $this->View()->loadTemplate('frontend/detail/ajax.tpl');
        }

        $product = Shopware()->Modules()->Articles()->sGetConfiguratorImage($product);
        $product['sBundles'] = false;

        if (!empty(Shopware()->Config()->InquiryID)) {
            $this->View()->assign('sInquiry', $this->Front()->Router()->assemble([
                'sViewport' => 'support',
                'sFid' => Shopware()->Config()->InquiryID,
                'sInquiry' => 'detail',
                'sOrdernumber' => $product['ordernumber'],
            ]));
        }

        if (!empty($product['categoryID'])) {
            $breadcrumb = array_reverse(Shopware()->Modules()->sCategories()->sGetCategoriesByParent($product['categoryID']));
            $categoryInfo = end($breadcrumb);
        } else {
            $breadcrumb = [];
            $categoryInfo = null;
        }

        // SW-3493 sArticle->getArticleById and sBasket->sGetGetBasket differ in camelcase
        $product['sReleaseDate'] = $product['sReleasedate'];

        $this->View()->assign('sBreadcrumb', $breadcrumb);
        $this->View()->assign('sCategoryInfo', $categoryInfo);
        $this->View()->assign('sArticle', $product);
        $this->View()->assign('rand', Random::getAlphanumericString(32));
    }

    /**
     * product quick view method
     *
     * Fetches the correct product corresponding to the given order number.
     * Assigns the product information to the sArticle view variable.
     */
    public function productQuickViewAction()
    {
        $orderNumber = (string) $this->Request()->get('ordernumber');

        if (empty($orderNumber)) {
            throw new \InvalidArgumentException('Argument ordernumber missing');
        }

        /** @var sArticles $articleModule */
        $articleModule = Shopware()->Modules()->Articles();
        $this->View()->assign('sArticle', $articleModule->sGetProductByOrdernumber($orderNumber));
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

        $product = Shopware()->Modules()->Articles()->sGetArticleNameByArticleId($id);
        if (empty($product)) {
            return $this->forward('error');
        }

        $voteConfirmed = false;

        if ($hash = $this->Request()->sConfirmation) {
            $getVote = Shopware()->Db()->fetchRow('
                SELECT * FROM s_core_optin WHERE hash = ?
            ', [$hash]);
            if (!empty($getVote['data'])) {
                Shopware()->System()->_POST = unserialize($getVote['data'], ['allowed_classes' => false]);
                $voteConfirmed = true;
                Shopware()->Db()->query('DELETE FROM s_core_optin WHERE hash = ?', [$hash]);
            }
        }

        if (empty(Shopware()->System()->_POST['sVoteSummary'])) {
            $sErrorFlag['sVoteSummary'] = true;
        }

        if (!$voteConfirmed) {
            /** @var \Shopware\Components\Captcha\CaptchaValidator $captchaValidator */
            $captchaValidator = $this->container->get('shopware.captcha.validator');

            if (!$captchaValidator->validate($this->Request())) {
                $sErrorFlag['sCaptcha'] = true;
            }
        }

        $validator = $this->container->get('validator.email');
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
                $hash = \Shopware\Components\Random::getAlphanumericString(32);
                $sql = '
                    INSERT INTO s_core_optin (datum, hash, data, type)
                    VALUES (NOW(), ?, ?, "swProductVote")
                ';
                Shopware()->Db()->query($sql, [
                    $hash, serialize(Shopware()->System()->_POST->toArray()),
                ]);

                $link = $this->Front()->Router()->assemble([
                    'sViewport' => 'detail',
                    'action' => 'rating',
                    'sArticle' => $id,
                    'sConfirmation' => $hash,
                ]);

                $context = [
                    'sConfirmLink' => $link,
                    'sArticle' => ['articleName' => $product],
                ];

                $mail = Shopware()->TemplateMail()->createMail('sOPTINVOTE', $context);
                $mail->addTo($this->Request()->getParam('sVoteMail'));
                $mail->send();
            } else {
                unset(Shopware()->Config()->sOPTINVOTE);
                Shopware()->Modules()->Articles()->sSaveComment($id);
            }
        } else {
            $this->View()->assign('sFormData', Shopware()->System()->_POST->toArray());
            $this->View()->assign('sErrorFlag', $sErrorFlag);
        }

        $this->View()->assign('sAction', 'ratingAction');

        $this->forward(
            $this->Request()->getParam('sTargetAction', 'index'),
            $this->Request()->getParam('sTarget', 'detail')
        );
    }

    /**
     * Checks if the provided $categoryId is in the current shop's category tree
     *
     * @param int $categoryId
     *
     * @return bool
     */
    private function isValidCategory($categoryId)
    {
        $defaultShopCategoryId = Shopware()->Shop()->getCategory()->getId();

        /** @var \Shopware\Models\Category\Repository $repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
        $categoryPath = $repository->getPathById($categoryId);

        if (!$categoryPath) {
            return true;
        }

        if (!array_key_exists($defaultShopCategoryId, $categoryPath)) {
            return false;
        }

        return true;
    }
}
