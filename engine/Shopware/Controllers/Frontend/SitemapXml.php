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
 * @author     Heiner Lohaus
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 * Sitemap controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Frontend_SitemapXml extends Enlight_Controller_Action
{
    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $repository;

    /**
     * Init controller method
     */
    public function init()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Front()->setParam('disableOutputBuffering', true);
        $this->Front()->returnResponse(true);

        $this->Response()->setHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->Response()->sendResponse();

        $this->repository = Shopware()->Models()->getRepository(
            'Shopware\Models\Category\Category'
        );

        set_time_limit(0);
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";

        $parentId = Shopware()->Shop()->get('parentID');

        $this->readCategoryUrls($parentId);

        $this->readArticleUrls($parentId);

        echo "</urlset>\r\n";
    }

    /**
     * Print category urls
     *
     * @param int $parentId
     */
    public function readCategoryUrls($parentId)
    {
        $categories = $this->repository
            ->getActiveChildrenByIdQuery($parentId)
            ->getArrayResult();

        foreach ($categories as $category) {
            if(!empty($category['category']['external'])) {
                continue;
            }
            $category['link'] = $this->Front()->Router()->assemble(array(
                'sViewport' => 'cat',
                'sCategory' => $category['category']['id'],
                'title' => $category['category']['name']
            ));
            $this->printCategoryUrl(array(
                'changed' => $category['category']['changed'],
                'link' => $category['link']
            ));
        }
    }

    /**
     * Print category url
     *
     * @param array $url
     */
    public function printCategoryUrl($url)
    {
        $line = '<url>';
        $line .= '<loc>' . $url['link'] . '</loc>';
        if (!empty($url['changed'])) {
            $line .= '<lastmod>' . $url['changed']->format('Y-m-d') . '</lastmod>';
        }
        $line .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
        $line .= '</url>';
        $line .= "\r\n";
        echo $line;
    }

    /**
     * Read article urls
     *
     * @param int $parentId
     */
    public function readArticleUrls($parentId)
    {
        $sql = "
			SELECT
				a.id,
				DATE(a.changetime) as changed
			FROM s_categories c, s_categories c2, s_articles_categories ac, s_articles a
			WHERE c.id=?
	        AND c2.left >= c.left
	        AND c2.right <= c.right
	        AND c2.active = 1
	        AND ac.articleID = a.id
	        AND ac.categoryID = c2.id
	        AND a.active=1
	        GROUP BY a.id
		";
        $result = Shopware()->Db()->query($sql, array($parentId));
        if (!$result->rowCount()) {
            return;
        }
        while ($url = $result->fetch()) {
            $url['link'] = $this->Front()->Router()->assemble(array(
                'sViewport' => 'detail',
                'sArticle' => $url['id']
            ));
            $this->printArticleUrls($url);
        }
    }

    /**
     * Print article url
     *
     * @param array $url
     */
    public function printArticleUrls($url)
    {
        $line = '<url>';
        $line .= '<loc>' . $url['link'] . '</loc>';
        if (!empty($url['changed'])) {
            $line .= '<lastmod>' . $url['changed'] . '</lastmod>';
        }
        $line .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
        $line .= '</url>';
        $line .= "\r\n";
        echo $line;
    }
}