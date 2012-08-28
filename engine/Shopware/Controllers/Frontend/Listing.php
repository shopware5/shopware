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
 * @subpackage Frontend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Listing controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Frontend_Listing extends Enlight_Controller_Action
{
    /**
     * Index action method
     */
    public function indexAction()
    {
        $categoryId = $this->Request()->getParam('sCategory');
        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);
        $categoryId = $categoryContent['id'];
        Shopware()->System()->_GET['sCategory'] = $categoryId;

        if (!empty($categoryContent['external'])) {
            $location = $categoryContent['external'];
//        } elseif (Shopware()->Config()->categoryDetailLink
//          && !empty($categoryArticles['sArticles']) && count($categoryArticles['sArticles']) == 1) {
//            $categoryArticle = reset($categoryArticles['sArticles']);
//            $location = array(
//                'sViewport' => 'detail',
//                'sArticle' => $categoryArticle['articleID'],
//                'title' => $categoryArticle['articleName']
//            );
        } elseif (!$categoryContent) {
            $location = array('controller' => 'index');
        }
        if (isset($location)) {
            return $this->redirect($location, array('code' => 301));
        }

        $this->View()->assign(array(
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
            'sBreadcrumb' => $this->getBreadcrumb($categoryId),
            'sCategoryContent' => $categoryContent,
            'sCategoryInfo' => $categoryContent
        ));

        if(Shopware()->Shop()->getTemplate()->getVersion() == 2) {
            /**@var $repository \Shopware\Models\Emotion\Repository*/
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion');
            $query = $repository->getCampaignByCategoryQuery($categoryId);
            $campaignsResult = $query->getArrayResult();
            $campaigns = array();
            foreach ($campaignsResult as $campaign) {
                $campaign['categoryId'] = $categoryId;
                $campaigns[$campaign['landingPageBlock']][] = $campaign;
            }
        }

        $hasEmotion = false;
        if (!$this->Request()->getQuery('sSupplier')
            && !$this->Request()->getQuery('sPage')
            && !$this->Request()->getQuery('sFilterProperties')
            && !$this->Request()->getParam('sRss')
            && !$this->Request()->getParam('sAtom')
        ) {
            if(Shopware()->Shop()->getTemplate()->getVersion() == 1) {
                $offers = Shopware()->Modules()->Articles()->sGetPromotions($categoryId);
            } else {
                // Check if is a emotion grid is active for this category
                $hasEmotion = Shopware()->Db()->fetchOne("
                    SELECT ec.id, ec.emotion_id FROM s_emotion_categories ec
                    LEFT JOIN s_emotion e ON (e.id = ec.emotion_id)
                    WHERE ec.category_id = ?
                    AND (e.valid_to >= NOW() OR e.valid_to IS NULL)
                ", array($categoryId));
            }
        }

        $this->View()->hasEmotion = $hasEmotion;
        if (!empty($hasEmotion)) {
            return;
        } elseif (!empty($offers)) {
            $this->View()->sOffers = $offers;
            return;
        }

        $categoryArticles = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId);
        $this->View()->assign($categoryArticles);

        if(empty($categoryContent['noViewSelect'])
            && !empty($categoryArticles['sTemplate'])
            && !empty($categoryContent['layout'])) {
            if ($categoryArticles['sTemplate'] == 'table') {
                if($categoryContent['layout'] == '1col') {
                    $categoryContent['layout'] = '3col';
                    $categoryContent['template'] = 'article_listing_3col.tpl';
                }
            } else {
                $categoryContent['layout'] = '1col';
                $categoryContent['template'] = 'article_listing_1col.tpl';
            }
        }

        if ($this->Request()->getParam('sRss') || $this->Request()->getParam('sAtom')) {
            //Shopware()->Config()->dontAttachSession = true;
            $this->Response()->setHeader('Content-Type', 'text/xml');
            $type = $this->Request()->getParam('sRss') ? 'rss' : 'atom';

            $this->View()->loadTemplate('frontend/listing/' . $type . '.tpl');
        } elseif (!empty($categoryContent['template']) && empty($categoryContent['layout'])) {
            $this->view->loadTemplate('frontend/listing/' . $categoryContent['template']);
        }

        $this->View()->assign(array(
            'sSuppliers' => Shopware()->Modules()->Articles()->sGetAffectedSuppliers($categoryId),
            'sCategoryContent' => $categoryContent
        ));
        if (empty($categoryContent["hideFilter"])) {
            $articleProperties = Shopware()->Modules()->Articles()->sGetCategoryProperties($categoryId);
        }

        if(!empty($articleProperties['filterOptions'])) {
            $this->View()->assign(array(
                'activeFilterGroup' => $this->request->getQuery('sFilterGroup'),
                'sPropertiesOptionsOnly' => $articleProperties['filterOptions']['optionsOnly'] ?: array(),
                'sPropertiesGrouped' => $articleProperties['filterOptions']['grouped'] ?: array()
            ));
        }
    }

    /**
     * Returns listing breadcrumb
     *
     * @param int $categoryId
     * @return array
     */
    public function getBreadcrumb($categoryId)
    {
        return array_reverse(Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId));
    }
}