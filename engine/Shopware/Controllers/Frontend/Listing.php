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
 * Listing controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Listing extends Enlight_Controller_Action
{
    /**
     * Translation handler.
     * @var Shopware_Components_Translation
     */
    private $translator;

    /**
     * Index action method
     */
    public function indexAction()
    {
        $supplierId = $this->Request()->getParam('sSupplier');

        $categoryId = $this->Request()->getParam('sCategory');
        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);
        $categoryId = $categoryContent['id'];

        Shopware()->System()->_GET['sCategory'] = $categoryId;

        if (!empty($categoryContent['external'])) {
            $location = $categoryContent['external'];
        } elseif (empty($categoryContent)) {
            $location = array('controller' => 'index');
        } elseif (Shopware()->Config()->categoryDetailLink && $categoryContent['articleCount'] == 1) {
            /**@var $repository \Shopware\Models\Category\Repository*/
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
            $articleId = $repository->getActiveArticleIdByCategoryId($categoryContent['id']);
            if (!empty($articleId)) {
                $location = array(
                    'sViewport' => 'detail',
                    'sArticle' => $articleId
                );
            }
        }
        if (isset($location)) {
            return $this->redirect($location, array('code' => 301));
        }

        if (Shopware()->Config()->get('seoSupplier') === true && $categoryContent['parentId'] == 1 && $this->Request()->getParam('sSupplier', false)) {
            $supplier = Shopware()->Models()->getRepository('Shopware\Models\Article\Supplier')->find($this->Request()->getParam('sSupplier'));

            $supplierName = $supplier->getName();
            $supplierTitle = $supplier->getMetaTitle();
            $categoryContent['metadescription'] = $supplier->getMetaDescription();
            $categoryContent['metakeywords'] = $supplier->getMetaKeywords();
            if (!Shopware()->Shop()->getDefault()) {
                $translation = $this->getTranslator()->read(Shopware()->Shop()->getId(), 'supplier', $supplier->getId());
                if (array_key_exists('metaTitle', $translation))
                    $supplierTitle = $translation['metaTitle'];
                if (array_key_exists('metaDescription', $translation))
                    $categoryContent['metadescription'] = $translation['metaDescription'];
                if (array_key_exists('metaKeywords', $translation))
                    $categoryContent['metakeywords'] = $translation['metaKeywords'];
            }
            $path = $this->Front()->Router()->assemble(array(
                'sViewport' => 'supplier',
                'sSupplier' => $supplier->getId(),
            ));
            if ($path) {
                $categoryContent['sSelfCanonical'] = $path;
            }
            if (!empty($supplierTitle)) {
                $categoryContent['title'] = $supplierTitle.' | '.Shopware()->Shop()->getName();
            } elseif (!empty($supplierName)) {
                $categoryContent['title'] = $supplierName;
            }
            $categoryContent['canonicalTitle'] = $supplierName;
        }

        /**@var $repository \Shopware\Models\Emotion\Repository*/
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion');
        $query = $repository->getCampaignByCategoryQuery($categoryId);
        $campaignsResult = $query->getArrayResult();
        $campaigns = array();
        foreach ($campaignsResult as $campaign) {
            $campaign['categoryId'] = $categoryId;
            $campaigns[$campaign['landingPageBlock']][] = $campaign;
        }

        $showListing = true;
        $hasEmotion = false;
        $viewAssignments = array(
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
            'sBreadcrumb' => $this->getBreadcrumb($categoryId),
            'sCategoryContent' => $categoryContent,
            'campaigns' => $campaigns,
            'sCategoryInfo' => $categoryContent
        );

        if (!$this->Request()->getQuery('sSupplier')
            && !$this->Request()->getQuery('sPage')
            && !$this->Request()->getQuery('sFilterProperties')
            && !$this->Request()->getParam('sRss')
            && !$this->Request()->getParam('sAtom')
        ) {
            // Check if is a emotion grid is active for this category
            $emotion = Shopware()->Db()->fetchRow("
                SELECT e.id, e.show_listing
                FROM s_emotion_categories ec, s_emotion e
                WHERE ec.category_id = ?
                AND e.id = ec.emotion_id
                AND e.is_landingpage = 0
                AND e.active = 1
                AND (e.valid_to >= NOW() OR e.valid_to IS NULL)
            ", array($categoryId));
            $hasEmotion = !empty($emotion['id']);
            $showListing = !$hasEmotion || !empty($emotion['show_listing']);

            /**
             * @deprecated
             */
            if (empty($hasEmotion) && Shopware()->Shop()->getTemplate()->getVersion() == 1) {
                $offers = Shopware()->Modules()->Articles()->sGetPromotions($categoryId);
                $viewAssignments['sOffers'] = $offers;
                if (!empty($offers)) {
                    $showListing = false;
                }
            }
        }

        $viewAssignments['showListing'] = $showListing;
        $viewAssignments['hasEmotion'] = $hasEmotion;
        //assign the variables here for the emotion view
        $this->View()->assign($viewAssignments);
        if (!$showListing) {
            return;
        }

        $categoryArticles = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId);

        if(empty($categoryContent['noViewSelect'])
            && !empty($categoryArticles['sTemplate'])
            && !empty($categoryContent['layout'])) {
            if ($categoryArticles['sTemplate'] == 'table') {
                if ($categoryContent['layout'] == '1col') {
                    $categoryContent['layout'] = '3col';
                    $categoryContent['template'] = 'article_listing_3col.tpl';
                }
            } else {
                $categoryContent['layout'] = '1col';
                $categoryContent['template'] = 'article_listing_1col.tpl';
            }
        }

        $newTemplateLoaded = false;
        if ($this->Request()->getParam('sRss') || $this->Request()->getParam('sAtom')) {
            $this->Response()->setHeader('Content-Type', 'text/xml');
            $type = $this->Request()->getParam('sRss') ? 'rss' : 'atom';

            $this->View()->loadTemplate('frontend/listing/' . $type . '.tpl');
            $newTemplateLoaded = true;

        } elseif (!empty($categoryContent['template']) && empty($categoryContent['layout'])) {
            $this->view->loadTemplate('frontend/listing/' . $categoryContent['template']);
            $newTemplateLoaded = true;
        }

        if ($newTemplateLoaded) {
            //assign it again because load template was called
            $this->View()->assign($viewAssignments);
        }

        $this->View()->assign($categoryArticles);

        $this->View()->assign(array(
            'sSuppliers' => Shopware()->Modules()->Articles()->sGetAffectedSuppliers($categoryId),
            'sCategoryContent' => $categoryContent
        ));

        if (empty($categoryContent["hideFilter"]) && $this->displayFiltersInListing()) {
            $articleProperties = Shopware()->Modules()->Articles()->sGetCategoryProperties($categoryId, $supplierId, null);
        }

        if (!empty($articleProperties['filterOptions'])) {
            $this->View()->assign(array(
                'activeFilterGroup' => $this->request->getQuery('sFilterGroup'),
                'sPropertiesOptionsOnly' => $articleProperties['filterOptions']['optionsOnly'] ?: array(),
                'sPropertiesGrouped' => $articleProperties['filterOptions']['grouped'] ?: array()
            ));
        }
    }

    /**
     * Helper function which checks the configuration for listing filters.
     * @return boolean
     */
    protected function displayFiltersInListing()
    {
        return Shopware()->Config()->get('displayFiltersInListings', true);
    }

    /**
     * Returns listing breadcrumb
     *
     * @param int $categoryId
     * @return array
     */
    public function getBreadcrumb($categoryId)
    {
        $breadcrumb = Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId);
        return array_reverse($breadcrumb);
    }

    /**
     * @return \Shopware_Components_Translation
     */
    private function getTranslator()
    {
        if (null === $this->translator) {
            $this->translator = new Shopware_Components_Translation();
        }

        return $this->translator;
    }
}
