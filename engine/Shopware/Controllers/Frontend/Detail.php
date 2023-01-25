<?php

declare(strict_types=1);
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
use Shopware\Components\Validator\EmailValidator;
use Shopware\Models\Category\Category;
use Shopware_Components_Config as Config;

class Shopware_Controllers_Frontend_Detail extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     *
     * Sets the scope
     */
    public function preDispatch()
    {
        $this->View()->setScope(Smarty::SCOPE_PARENT);
    }

    /**
     * Error action method for not found/inactive products
     * Can throw an exception that is handled by the default error controller
     * or show a custom page with related products
     *
     * @return void
     */
    public function errorAction()
    {
        $config = $this->container->get(Config::class);
        if (!$config->get('RelatedArticlesOnArticleNotFound')) {
            throw new Enlight_Controller_Exception('Product not found', 404);
        }

        $this->Response()->setStatusCode($config->get('PageNotFoundCode', 404));
        $this->View()->assign('sRelatedArticles', $this->container->get('modules')->Marketing()->sGetSimilarArticles(
            (int) $this->Request()->getParam('sArticle'),
            (int) $config->get('maxcrosssimilar', 4)
        ));
    }

    /**
     * Index action method
     *
     * Read the product details and base rating form data
     * Loads on demand a custom template
     *
     * @return void
     */
    public function indexAction()
    {
        $id = (int) $this->Request()->getParam('sArticle');
        $tpl = (string) $this->Request()->getParam('template');

        if ($id <= 0) {
            $this->forward('error');

            return;
        }

        $view = $this->View();
        $view->assign('sAction', $view->getAssign('sAction') ?: 'index', true);
        $view->assign('sErrorFlag', $view->getAssign('sErrorFlag') ?: [], true);
        $view->assign('sFormData', $view->getAssign('sFormData') ?: [], true);
        $view->assign('userLoggedIn', (bool) $this->container->get('session')->get('sUserId'));

        if (!empty($this->container->get('session')->get('sUserId')) && empty($this->Request()->get('sVoteName'))
            && $this->Request()->getParam('__cache') !== null) {
            $userData = $this->container->get('modules')->Admin()->sGetUserData();
            if (\is_array($userData)) {
                $view->assign('sFormData', [
                    'sVoteMail' => $userData['additional']['user']['email'],
                    'sVoteName' => $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname'],
                ]);
            }
        }

        $number = $this->Request()->getParam('number');
        $selection = $this->Request()->getParam('group', []);

        $categoryId = (int) $this->Request()->get('sCategory');
        if (!$this->isValidCategory($categoryId)) {
            $categoryId = 0;
        }

        try {
            $product = $this->container->get('modules')->Articles()->sGetArticleById(
                $id,
                $categoryId,
                $number,
                $selection
            );
        } catch (Exception $e) {
            $product = null;
        }

        if (empty($product) || empty($product['articleName'])) {
            $this->forward('error');

            return;
        }

        $this->Request()->setQuery('sCategory', $product['categoryID']);

        $template = trim($product['template']);
        if (!empty($template)) {
            $view->loadTemplate('frontend/detail/' . $product['template']);
        } elseif (!empty($product['mode'])) {
            $view->loadTemplate('frontend/blog/detail.tpl');
        } elseif ($tpl === 'ajax') {
            $view->loadTemplate('frontend/detail/ajax.tpl');
        }

        $product = $this->container->get('modules')->Articles()->sGetConfiguratorImage($product);
        $product['sBundles'] = false;

        if (!empty($this->container->get(Config::class)->get('InquiryID'))) {
            $view->assign('sInquiry', $this->Front()->ensureRouter()->assemble([
                'sViewport' => 'support',
                'sFid' => $this->container->get(Config::class)->get('InquiryID'),
                'sInquiry' => 'detail',
                'sOrdernumber' => $product['ordernumber'],
            ]));
        }

        if (!empty($product['categoryID'])) {
            $breadcrumb = array_reverse($this->container->get('modules')->Categories()->sGetCategoriesByParent($product['categoryID']));
            $categoryInfo = end($breadcrumb);
        } else {
            $breadcrumb = [];
            $categoryInfo = null;
        }

        $view->assign('sBreadcrumb', $breadcrumb);
        $view->assign('sCategoryInfo', $categoryInfo);
        $view->assign('sArticle', $product);
        $view->assign('rand', Random::getAlphanumericString(32));
    }

    /**
     * product quick view method
     *
     * Fetches the correct product corresponding to the given order number.
     * Assigns the product information to the sArticle view variable.
     *
     * @return void
     */
    public function productQuickViewAction()
    {
        $orderNumber = (string) $this->Request()->get('ordernumber');

        if (empty($orderNumber)) {
            throw new InvalidArgumentException('Argument ordernumber missing');
        }

        $productService = $this->get('shopware_storefront.list_product_service');
        $context = $this->get('shopware_storefront.context_service')->getContext();

        $product = $productService->get($orderNumber, $context);
        if (!$product) {
            $productOrderNumber = $this->get('dbal_connection')->fetchOne(
                'SELECT ordernumber FROM s_addon_premiums WHERE ordernumber_export = :ordernumber',
                [':ordernumber' => $orderNumber]
            );
            if ($productOrderNumber) {
                $product = $productService->get($productOrderNumber, $context);
            }
        }
        if ($product) {
            $this->View()->assign('sArticle', $this->get('legacy_struct_converter')->convertListProductStruct($product));
        }
    }

    /**
     * Rating action method
     *
     * Save and review the product rating
     *
     * @return void
     */
    public function ratingAction()
    {
        $id = (int) $this->Request()->getParam('sArticle');
        if (empty($id)) {
            $this->forward('error');

            return;
        }

        $product = $this->container->get('modules')->Articles()->sGetArticleNameByArticleId($id);
        if (empty($product)) {
            $this->forward('error');

            return;
        }

        $voteConfirmed = false;

        $hash = $this->Request()->getParam('sConfirmation');
        if ($hash) {
            $getVote = $this->container->get('db')->fetchRow(
                'SELECT * FROM s_core_optin WHERE hash = ?',
                [$hash]
            );
            if (!empty($getVote['data'])) {
                $this->container->get('front')->ensureRequest()->setPost(unserialize($getVote['data'], ['allowed_classes' => false]));
                $voteConfirmed = true;
                $this->container->get('db')->query('DELETE FROM s_core_optin WHERE hash = ?', [$hash]);
            }
        }

        if (empty($this->container->get('front')->ensureRequest()->getPost('sVoteSummary'))) {
            $sErrorFlag['sVoteSummary'] = true;
        }

        if (!$voteConfirmed) {
            $captchaValidator = $this->container->get('shopware.captcha.validator');

            if (!$captchaValidator->validate($this->Request())) {
                $sErrorFlag['sCaptcha'] = true;
            }
        }

        $validator = $this->container->get(EmailValidator::class);
        if (!empty($this->container->get(Config::class)->get('sOPTINVOTE'))
            && (empty($this->container->get('front')->ensureRequest()->getPost('sVoteMail'))
                || !$validator->isValid($this->container->get('front')->ensureRequest()->getPost('sVoteMail')))
        ) {
            $sErrorFlag['sVoteMail'] = true;
        }

        $view = $this->View();

        if (empty($sErrorFlag)) {
            if (!empty($this->container->get(Config::class)->get('sOPTINVOTE'))
                && !$voteConfirmed && empty($this->container->get('session')->get('sUserId'))
            ) {
                $hash = Random::getAlphanumericString(32);
                $sql = "INSERT INTO s_core_optin (datum, hash, data, type)
                        VALUES (NOW(), ?, ?, 'swProductVote')";
                $this->container->get('db')->query($sql, [
                    $hash, serialize($this->container->get('front')->ensureRequest()->getPost()),
                ]);

                $link = $this->Front()->ensureRouter()->assemble([
                    'sViewport' => 'detail',
                    'action' => 'rating',
                    'sArticle' => $id,
                    'sConfirmation' => $hash,
                ]);

                $context = [
                    'sConfirmLink' => $link,
                    'sArticle' => ['articleName' => $product],
                ];

                $mail = $this->container->get('templatemail')->createMail('sOPTINVOTE', $context);
                $mail->addTo($this->Request()->getParam('sVoteMail'));
                $mail->send();
            } else {
                $this->container->get(Config::class)->offsetUnset('sOPTINVOTE');
                $this->container->get('modules')->Articles()->sSaveComment($id);
            }
        } else {
            $view->assign('sFormData', $this->container->get('front')->ensureRequest()->getPost());
            $view->assign('sErrorFlag', $sErrorFlag);
        }

        $view->assign('sAction', 'ratingAction');

        $this->forward(
            $this->Request()->getParam('sTargetAction', 'index'),
            $this->Request()->getParam('sTarget', 'detail')
        );
    }

    /**
     * Checks if the provided $categoryId is in the current shop's category tree
     */
    private function isValidCategory(int $categoryId): bool
    {
        $category = $this->container->get('shop')->getCategory();
        if (!$category instanceof Category) {
            return false;
        }

        $defaultShopCategoryId = $category->getId();

        $categoryPath = $this->get('models')->getRepository(Category::class)->getPathById($categoryId);

        if (!$categoryPath) {
            return true;
        }

        if (!\array_key_exists($defaultShopCategoryId, $categoryPath)) {
            return false;
        }

        return true;
    }
}
