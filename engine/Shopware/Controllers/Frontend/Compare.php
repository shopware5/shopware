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
 * Compare controller
 */
class Shopware_Controllers_Frontend_Compare extends Enlight_Controller_Action
{
    protected $articles;

    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
        $this->articles = Shopware()->Modules()->Articles();
        $this->Front()->Plugins()->Json()->setPadding();
    }

    public function indexAction()
    {
        $this->View()->sComparisons = $this->articles->sGetComparisons();
    }

    public function addArticleAction()
    {
        if (($articleId = $this->Request()->getParam('articleID')) !== null) {
            $this->View()->sCompareAddResult = $this->articles->sAddComparison($articleId);
        }
        $this->View()->sComparisons = $this->articles->sGetComparisons();
    }

    public function deleteArticleAction()
    {
        if (($articleId = $this->Request()->getParam('articleID')) !== null) {
            $this->articles->sDeleteComparison($articleId);
        }
        $this->forward('index');
    }

    public function deleteAllAction()
    {
        $this->articles->sDeleteComparisons();
        $this->forward('index');
    }

    public function getListAction()
    {
        $this->forward('index');
    }

    public function overlayAction()
    {
        $this->View()->sComparisonsList = $this->articles->sGetComparisonList();
    }
}
