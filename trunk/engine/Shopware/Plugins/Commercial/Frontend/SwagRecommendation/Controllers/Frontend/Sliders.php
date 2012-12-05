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
 * @subpackage Recommendation
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

/**
 * todo@all: Documentation
 */
class Shopware_Controllers_Frontend_Sliders extends Enlight_Controller_Action
{
    /**
     * Display products that other customers have clicked before or after the
     * products that current user
     * @throws Enlight_Exception
     */
    public function similaryViewedAction()
    {
        if (empty($this->Request()->category)) {
            throw new Enlight_Exception("Missing category-id");
        }
        $config = Shopware()->Plugins()->Frontend()->SwagRecommendation()->Config();

        $page = empty($this->Request()->pages) ? 1 : (int)$this->Request()->pages;
        $maxArticles = empty($config->max_simlar_articles) ? 20 : (int)$config->max_simlar_articles;
        $perPage = empty($config->page_similar_articles) ? 3 : (int)$config->page_similar_articles;

        $getLastViewed = Shopware()->Modules()->Articles()->sGetLastArticles();
        foreach ($getLastViewed as $v) {
            $lastViewed[] = $v["articleID"];
        }
        if (!count($lastViewed)) {
            $this->View()->setTemplate();
            return;
        }

        $sql = "
               SELECT a.id AS id
               FROM s_articles a, s_articles_categories ac,s_categories c,s_categories c2
               WHERE a.active=1
               AND a.id=ac.articleID
               AND c.id=?
               AND c2.active=1
               AND c2.left >= c.left
               AND c2.right <= c.right
               AND ac.articleID=a.id
               AND ac.categoryID=c2.id
               ORDER BY a.datum DESC LIMIT $maxArticles
               ";
        $sql = "
			SELECT e1.articleID as id, COUNT(e1.articleID) AS hits
			FROM s_emarketing_lastarticles AS e1,
			s_emarketing_lastarticles AS e2,
			s_articles_categories ac,s_categories c,s_categories c2,
			s_articles a
			WHERE
			c.id=?
            AND c2.active=1
            AND c2.left >= c.left
            AND c2.right <= c.right
            AND ac.articleID=a.id
            AND ac.categoryID=c2.id
			AND ac.articleID=e1.articleID
			AND e2.articleID IN (" . implode(",", $lastViewed) . ")
			AND e1.sessionID=e2.sessionID
			AND a.id=e1.articleID
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = a.id AND customergroupID = " . Shopware()->System()->sUSERGROUPDATA["id"] . "
			) IS NULL
			AND a.active=1
			AND a.mode=0
			AND e1.articleID NOT IN (" . implode(",", $lastViewed) . ")
			GROUP BY e1.articleID
			ORDER BY hits DESC
			LIMIT $maxArticles
		";
        $articles = Shopware()->Db()->fetchAll($sql, array($this->Request()->category));
        $articles = array_chunk($articles, $perPage);
        $pages = count($articles);
        $articles = $articles[$page - 1];

        foreach ($articles as $article) {
            $tmpContainer = Shopware()->Modules()->Articles()->sGetPromotionById("fix", 0, (int)$article['id']);
            if (!empty($tmpContainer["articleName"])) {
                $result[] = $tmpContainer;
            }
        }
        $this->View()->loadTemplate("widgets/recommendation/slide_articles.tpl");
        $this->View()->articles = $result;
        $this->View()->pages = $pages;
    }

    /**
     * Show new products in store
     * @throws Enlight_Exception
     */
    public function newAction()
    {
        if (empty($this->Request()->category)) {
            throw new Enlight_Exception("Missing category-id");
        }
        $config = Shopware()->Plugins()->Frontend()->SwagRecommendation()->Config();

        $page = empty($this->Request()->pages) ? 1 : (int)$this->Request()->pages;
        $maxArticles = empty($config->max_new_articles) ? 20 : (int)$config->max_new_articles;
        $perPage = empty($config->page_new_articles) ? 3 : (int)$config->page_new_articles;

        /*$sql = "

			SELECT s_articles.id AS id
			FROM s_articles, s_articles_categories 
			WHERE active=1 AND mode = 0
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = s_articles.id AND customergroupID = " . Shopware()->System()->sUSERGROUPDATA["id"] . "
			) IS NULL
			AND s_articles.id=s_articles_categories.articleID
			AND s_articles_categories.categoryID=?
			ORDER BY datum DESC LIMIT $maxArticles
		";*/

        $sql = "
        SELECT a.id AS id
        FROM s_articles a, s_articles_categories ac,s_categories c,s_categories c2
        WHERE a.active=1
        AND a.id=ac.articleID
        AND c.id=?
        AND c2.active=1
        AND c2.left >= c.left
        AND c2.right <= c.right
        AND ac.articleID=a.id
        AND ac.categoryID=c2.id
        ORDER BY a.datum DESC LIMIT $maxArticles
        ";
        $articles = Shopware()->Db()->fetchAll($sql, array($this->Request()->category));
        $articles = array_chunk($articles, $perPage);
        // Count pages
        $pages = count($articles);
        // Define current scope
        $articles = $articles[$page - 1];

        foreach ($articles as $article) {
            $tmpContainer = Shopware()->Modules()->Articles()->sGetPromotionById("fix", 0, (int)$article['id']);
            if (!empty($tmpContainer["articleName"])) {
                $result[] = $tmpContainer;
            }
        }
        $this->View()->loadTemplate("widgets/recommendation/slide_articles.tpl");
        $this->View()->articles = $result;
        $this->View()->pages = $pages;

    }

    /**
     * Show suppliers in store
     * @throws Enlight_Exception
     */
    public function suppliersAction()
    {
        if (empty($this->Request()->category)) {
            throw new Enlight_Exception("Missing category-id");
        }
        $getSuppliers = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($this->Request()->category);
    }
}