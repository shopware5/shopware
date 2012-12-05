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
 * @author     shopware AG
 */

class Shopware_Controllers_Frontend_Wizard extends Enlight_Controller_Action
{
    /**
     * Init controller method
     */
    public function init()
    {
        Shopware()->Loader()->registerNamespace('Shopware_Models', dirname(dirname(dirname(__FILE__))).'/Models/');
    }

    /**
     * Index action method method
     */
    public function indexAction()
    {
        $wizardId = (int) $this->Request()->wizardID;
        $wizard = Shopware_Models_WizardManager::getActiveWizardById($wizardId);
        if (!empty($wizard->listing)) {
            $this->forward('listing');
        } else {
            $this->View()->Wizard = $wizard;
        }
    }

    /**
     * Listing action method method
     */
    public function listingAction()
    {
        $wizardId = (int) $this->Request()->wizardID;
        $filter = (array) $this->Request()->filter;
        if (empty($wizardId)) {
            return;
        }

        $wizard = Shopware_Models_WizardManager::getActiveWizardById($wizardId);
        if (empty($wizard)) {
            return;
        }

        $perPage = empty($this->Request()->perPage) ? 12 : (int) $this->Request()->perPage;
        $page = empty($this->Request()->page) ? 0 : (int) $this->Request()->page;
        $maxQuantity = (int) $this->Request()->getQuery('max_quantity', $wizard['max_quantity']);
        if (!empty($maxQuantity)) {
            $page = 0;
            $perPage = $maxQuantity;
        }

        $select = $this->getWizardArticlesSelect($wizard, $filter);
        $articles = Shopware()->Db()->fetchAssoc($select);

        $articleIds = array_keys($articles);
        $count = count($articleIds);

        $articles = array_slice($articles, $page*$perPage, $perPage);
        $numberPages = ceil($count/$perPage);

        $pages = array();
        for ($i=0,$p=0;$i<$count;$i+=$perPage,$p++) {
            if ($page-3<$p&&$page+3>$p) {
                $pages['pages'][] = $p;
            }
        }
        $pages['count'] = $p;
        if ($page>0) {
            $pages['before'] = $page-1;
        }
        if ($page<$pages['count']-1) {
            $pages['next'] = $page+1;
        }

        foreach ($articles as $key => $row) {
            $article = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $row['articleID']);
            if (!empty($article)) {
                $articles[$key] = $article+$row;
            }
        }

        $this->View()->WizardSelection = $filter;
        $this->View()->WizardArticles = $articles;
        $this->View()->WizardArticleIds = $articleIds;
        $this->View()->WizardCount = $count;
        $this->View()->Wizard = $wizard;
        $this->View()->WizardTemplate = $this->Request()->template;
        $this->View()->WizardPage = $page;
        $this->View()->WizardPerPage = $perPage;

        $this->View()->sPages = $pages;
        $this->View()->sPerPage = $perPage;
        $this->View()->sNumberPages = $numberPages;
        $this->View()->sPage = $page+1;
        $this->View()->sPerPages = explode('|', Shopware()->Config()->NumberArticlesToShow);
    }

    /**
     * Filter action method method
     */
    public function filterAction()
    {
        $wizardId     = (int) $this->Request()->getParam('wizardID');
        $wizardPage   = (int) $this->Request()->getParam('page');
        $filterValues = (array) $this->Request()->getParam('filter');

        if (empty($wizardId) || empty($wizardPage)) {
            return $this->forward('index');
        }

        $this->View()->WizardPage = $wizardPage;

        $wizard = Shopware_Models_WizardManager::getActiveWizardById($wizardId);
        if (empty($wizard)) {
            return $this->forward('index');
        }

        $this->View()->Wizard = $wizard;

        $wizardFilter = $wizard->getNextFilterByPage($wizardPage);

        if (empty($wizardFilter)) {
            return $this->forward('result');
        }

        $this->View()->WizardFilter = $wizardFilter;
        unset($filterValues[$wizardFilter['id']]);
        $this->View()->WizardSelection = $filterValues;

        $select = $this->getWizardArticlesSelect($wizard, $filterValues);

        $articleIds = Shopware()->Db()->fetchCol($select);
        $this->View()->WizardCount = count($articleIds);

        if (!empty($wizard['max_quantity']) && $this->View()->WizardCount > $wizard['max_quantity']) {
            $this->View()->WizardCount = (int) $wizard['max_quantity'];
        }

        if (empty($this->View()->WizardCount)) {
            return $this->forward('result');
        }

        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() > 1;
        $this->View()->isEmotion    = $isEmotion;
        $this->View()->WizardValues = $wizardFilter->getValuesByArticleIds($articleIds);

        if ($this->View()->WizardValues!==null && !count($this->View()->WizardValues)) {
            return $this->forward('result');
        }
    }

    /**
     * Result action method
     */
    public function resultAction()
    {
        $wizardId = (int) $this->Request()->getParam('wizardID');
        $filter   = (array) $this->Request()->getParam('filter');

        if (empty($wizardId)) {
            return;
        }

        $wizard = Shopware_Models_WizardManager::getActiveWizardById($wizardId);
        if (empty($wizard)) {
            return;
        }

        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() > 1;
        if ($this->Request()->isXmlHttpRequest()) {
            $this->view->loadTemplate('frontend/wizard/ajax.tpl');
            if ($isEmotion) {
                $perPage = (int) $this->Request()->getParam('perPage', 4);
            } else {
                $perPage = (int) $this->Request()->getParam('perPage', 3);
            }

            $page = (int) $this->Request()->getParam('pages', 1);
        } else {
            $perPage = (int) $this->Request()->getParam('perPage', 24);
            $page = 1;
        }

        $offset      = (int) $this->Request()->getParam('offset', 0);
        $hideEmpty   = $this->Request()->getQuery('hide_empty', $wizard['hide_empty']) ? 1 : 0;
        $maxQuantity = (int) $this->Request()->getQuery('max_quantity', $wizard['max_quantity']);

        if (!empty($maxQuantity)) {
            $page    = 1;
            $perPage = $maxQuantity;
        }

        if (!empty($filter)) {
            $select = $this->getWizardArticlesSelect($wizard, $filter);
            $select->limit($perPage, ($perPage*($page-1)+$offset));

            $rows = Shopware()->Db()->fetchAll($select);

            $sql = 'SELECT FOUND_ROWS() as count';
            $realCount = $count = Shopware()->Db()->fetchOne($sql);

            $count = max(0, $realCount-$offset);
            if (!empty($maxQuantity) && $count>$maxQuantity) {
                $count = $maxQuantity;
            }
            $pages = ceil($count/$perPage);

            $articles = array();
            foreach ($rows as $row) {
                $article = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $row['articleID']);
                if (!empty($article)) {
                    $articles[$row['articleID']] = $article+$row;
                }
            }

            $this->View()->Wizard          = $wizard;
            $this->View()->WizardSelection = $filter;
            $this->View()->WizardArticles  = $articles;
            $this->View()->WizardCount     = $count;
            $this->View()->WizardRealCount = $realCount;
            $this->View()->WizardPages     = $pages;
        }

        $this->View()->isEmotion      = $isEmotion;
        $this->View()->Wizard         = $wizard;
        $this->View()->WizardPage     = (int) $this->Request()->page;
        $this->View()->WizardTemplate = $this->Request()->template;
    }

    /**
     * Returns wizard articles select
     *
     * @param  Shopware_Models_Wizard $wizard
     * @param  array                  $filterValues
     * @return Zend_Db_Select
     */
    public function getWizardArticlesSelect($wizard, $filterValues)
    {
        $select = Shopware()->Db()
            ->select()
            ->from(
                array('wa' => 's_plugin_wizard_articles'),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS a.id as articleID'))
            )

            ->joinInner(
                array('a' => 's_articles'),
                'a.id = wa.articleID AND a.mode=0 AND a.active=1',
                null
            )

            ->joinInner(
                array('d' => 's_articles_details'),
                'a.id = d.articleID',
                null
            )

            ->joinLeft(
                array('c' => 's_categories'),
                'c.id = ' . Shopware()->Db()->quote(Shopware()->Shop()->get('parentID')),
                null
             )
            ->joinLeft(
                array('c2' => 's_categories'),
                'c2.left >= c.left AND c2.right <= c.right',
                null
             )
             ->joinInner(
                array('ac' => 's_articles_categories'),
                'ac.articleID = a.id AND ac.categoryID = c2.id',
                null
             )

            ->where('wa.wizardID = ?', $wizard['id'])
            ->group('a.id');

        $filters = $wizard->getActiveFilters();
        if (empty($filters)) {
            return $select;
        }

        $score = array();
        foreach ($filters as $filter) {
            if (empty($filterValues[$filter->id])) {
                continue;
            }
            $filterValue = $filterValues[$filter->id];

            switch ($filter['typeID']) {
                case 1:
                case 3:
                    $select->joinInner(array(
                        'wr'.$filter->id => 's_plugin_wizard_relations'
                    ),
                        'wr'.$filter->id.'.articleID=wa.articleID '.
                        'AND wr'.$filter->id.'.valueID IN ('.Shopware()->Db()->quote($filterValue).')'
                    , null);
                    break;
                case 2:
                case 4:
                case 6:
                    $select->joinInner(array(
                        'wr'.$filter->id => 's_plugin_wizard_relations'
                    ),
                        'wr'.$filter->id.'.articleID=wa.articleID '.
                        'AND wr'.$filter->id.'.valueID IN ('.Shopware()->Db()->quote($filterValue).')'
                    , null);
                    $score[] = 'wr'.$filter->id.'.score';
                    break;
                case 7:
                    $select->joinInner(
                        array('fa' . $filter->id => 's_filter_articles'),
                        'fa' . $filter->id . '.articleID=wa.articleID',
                        null
                    );

                    $select->joinInner(array(
                        'fv'.$filter->id => 's_filter_values'
                    ),
                        'fv'.$filter->id.'.id=fa'.$filter->id . '.valueID'.
                        ' AND fv'.$filter->id.'.value IN ('.
                            'SELECT value FROM s_filter_values WHERE id IN ('.Shopware()->Db()->quote($filterValue).')'.
                        ')'
                    , null);
                    break;
                case 8:
                    $select->joinInner(array(
                        'd2' => 's_articles_details'
                    ),
                        'd2.articleID=wa.articleID '.
                        'AND d2.additionaltext IN ('.
                            'SELECT additionaltext FROM s_articles_details WHERE id IN ('.Shopware()->Db()->quote($filterValue).')'.
                        ')'
                    , null);
                    break;
                case 9:
                    if (empty($filterValue[2]) || (empty($filterValue[0]) && empty($filterValue[1]))) {
                        break;
                    }
                    $select->joinLeft(array(
                        't' => 's_core_tax'
                    ), 'a.taxID = t.id'
                    , null);
                    $select->joinLeft(array(
                        'p' => 's_articles_prices'
                    ),
                        'p.articledetailsID=d.id '.
                        'AND p.from=1 '.
                        'AND p.pricegroup=\'EK\''
                    , null);

                    if(!empty(Shopware()->System()->sUSERGROUP)
                      && empty(Shopware()->System()->sUSERGROUPDATA['mode'])
                      && Shopware()->System()->sUSERGROUP!='EK') {
                        $select->joinLeft(array(
                            'p2' => 's_articles_prices'
                        ),
                            'p.articledetailsID=d.id '.
                            'AND p2.from=1 AND p.price!=0 '.
                            'AND p.pricegroup='.Shopware()->Db()->quote(Shopware()->System()->sUSERGROUP)
                        , null);
                        $priceField = "IF(p2.price, p2.price, p.price)";
                    } else {
                        $priceField = "p.price";
                    }

                    if (!empty(Shopware()->System()->sUSERGROUPDATA['mode'])
                      && !empty(Shopware()->System()->sUSERGROUPDATA['discount'])){
                        $priceField = $priceField.'*(100-'.Shopware()->System()->sUSERGROUPDATA['discount'].')/100';
                    }
                    if (!empty(Shopware()->System()->sCurrency['factor'])
                      && Shopware()->System()->sCurrency['factor']!=1) {
                        $priceField = $priceField.'*'.Shopware()->System()->sCurrency['factor'];
                    }
                    if (!empty(Shopware()->System()->sUSERGROUPDATA['tax'])) {
                        $priceField = "ROUND($priceField*(100+t.tax)/100,2)";
                    }
                    if (!empty($filterValue[0])) {
                        $select->where($priceField.'>=?', $filterValue[0]);
                    }
                    if (!empty($filterValue[1])) {
                        $select->where($priceField.'<=?', $filterValue[1]);
                    }
                    break;
                case 10:
                    $select->joinInner(array(
                        'at'.$filter->id => 's_articles_attributes'
                    ),
                        'at'.$filter->id.'.articledetailsID=d.id '.
                        'AND at'.$filter->id.'.attr'.$filter['storeID'].' IN ('.
                            'SELECT attr'.$filter['storeID'].' FROM s_articles_attributes WHERE id IN ('.Shopware()->Db()->quote($filterValue).')'.
                        ')'
                    , null);
                    break;
                default:
                    break;
            }
        }

        if (!empty($score)) {
            $select->columns(new Zend_Db_Expr('IFNULL(SUM('.implode('), 0)+IFNULL(SUM(', $score).'), 0) as score'));
            $select->having('score>0 OR ?=0', $wizard['hide_empty']);
            $select->order(array('score DESC', 'd.sales'));
        }

        return $select;
    }

    /**
     * Post dispatch method
     */
    public function postDispatch()
    {
        if (empty($this->View()->Wizard['id'])
         || $this->Request()->isXmlHttpRequest()
         || $this->Request()->getActionName() == 'result') {
            return;
        }

        $this->saveRequests();

        if($this->Request()->getActionName()=='result'
            && !empty($this->View()->WizardArticles)) {
                $this->saveResults();
        }
    }

    /**
     * Save requests method
     */
    public function saveRequests()
    {
        $sql = '
            UPDATE `s_plugin_wizard_requests`
            SET `userID`=0
            WHERE `changed` < DATE_SUB(NOW(), INTERVAL 1 DAY)
            AND `userID` IS NULL
        ';
        Shopware()->Db()->exec($sql);

        if (!empty($this->View()->WizardFilter['id'])) {
            $filterId = (int) $this->View()->WizardFilter['id'];
        } elseif (!empty($this->View()->WizardSelection)) {
            $filterId = 0;
        } else {
            $filterId = null;
        }

        $sql = '
            INSERT INTO `s_plugin_wizard_requests` (
                `wizardID`,
                `filterID`,
                `sessionID`,
                `userID`,
                `added`,
                `changed`
            ) VALUES (
                ?, ?, ?, ?, NOW(), NOW()
            ) ON DUPLICATE KEY UPDATE
                `filterID`=VALUES(`filterID`),
                `userID`=IFNULL(VALUES(`userID`), `userID`),
                `changed`=NOW()
        ';
        Shopware()->Db()->query($sql, array(
            $this->View()->Wizard['id'],
            $filterId,
            Shopware()->SessionID(),
            Shopware()->Session()->sUserId
        ));
    }

    /**
     * Save results method
     */
    public function saveResults()
    {
        $sql = 'SELECT `id` FROM `s_plugin_wizard_requests` WHERE `wizardID`=? AND `sessionID`=?';
        $requestId = Shopware()->Db()->fetchOne($sql, array(
            $this->View()->Wizard['id'],
            Shopware()->SessionID()
        ));

        foreach ($this->View()->WizardArticles as $articleId => $article) {
            $sql = '
                INSERT IGNORE INTO `s_plugin_wizard_results` (
                    `wizardID`, `articleID`, `requestID`
                ) VALUES (
                    ?, ?, ?
                );
            ';
            Shopware()->Db()->query($sql, array(
                $this->View()->Wizard['id'],
                $articleId,
                $requestId
            ));
        }
        $articleIDs = array_keys($this->View()->WizardArticles);
        $sql = '
            DELETE FROM `s_plugin_wizard_results`
            WHERE `requestID`=?
            AND `articleID` NOT IN ('.Shopware()->Db()->quote($articleIDs).')
        ';
        Shopware()->Db()->query($sql, array(
            $requestId
        ));
    }
}
