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
 * @package    Shopware_Controllers_Widgets
 * @subpackage Widgets
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

/**
 * Shopware Application
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Widgets_Emotion extends Enlight_Controller_Action
{

    /**
     * Get emotion by category
     * @param $repository \Shopware\Models\Emotion\Repository
     * @return array
     */
    public function getEmotion($repository)
    {
        $categoryId = (int)$this->Request()->getParam('categoryId');
        $query = $repository->getCategoryEmotionsQuery($categoryId);
        $emotions = $query->getArrayResult();
        return $emotions;
    }

    /**
     * Action that will be triggered by product slider type topseller
     */
    public function emotionTopSellerAction()
    {
        $category = (int)$this->Request()->getParam("category");
        $start = (int)$this->Request()->getParam("start");
        $limit = (int)$this->Request()->getParam("limit");

        $elementHeight = $this->Request()->getParam("elementHeight");
        $elementWidth = $this->Request()->getParam("elementWidth");

        $pages = $this->Request()->getParam("pages");
        $offset = $limit * $pages - $limit;

        $this->View()->loadTemplate("widgets/emotion/slide_articles.tpl");

        $max = $this->Request()->getParam("max");
        $maxPages = round($max / $limit);

        $values = $this->getProductTopSeller($category, $offset, $limit);

        $this->View()->assign('articles', $values["values"]);
        $this->View()->assign('pages', $values["pages"] > $maxPages ? $maxPages : $values["pages"]);
        $this->View()->assign('sPerPage', $limit);
        $this->View()->assign('sElementWidth', $elementWidth);
        $this->View()->assign('sElementHeight', $elementHeight);
    }

    /**
     * Action that will be triggered by product slider type newcomer
     */
    public function emotionNewcomerAction()
    {
        $this->View()->loadTemplate("widgets/emotion/slide_articles.tpl");
        $category = (int)$this->Request()->getParam("category");
        $start = (int)$this->Request()->getParam("start");
        $limit = (int)$this->Request()->getParam("limit");
        $elementHeight = $this->Request()->getParam("elementHeight");
        $elementWidth = $this->Request()->getParam("elementWidth");

        $pages = $this->Request()->getParam("pages");
        $offset = $limit * $pages - $limit;

        $max = $this->Request()->getParam("max");
        $maxPages = round($max / $limit);

        $values = $this->getProductNewcomer($category, $offset, $limit);

        $this->View()->assign('articles', $values["values"]);
        $this->View()->assign('pages', $values["pages"] > $maxPages ? $maxPages : $values["pages"]);
        $this->View()->assign('sPerPage', $limit);
        $this->View()->assign('sElementWidth', $elementWidth);
        $this->View()->assign('sElementHeight', $elementHeight);
    }

    /**
     * The getEmotions function selects all emotions for the passed category id
     * and sets the result into the view variable "sEmotions".
     */
    public function indexAction()
    {
        /**@var $repository \Shopware\Models\Emotion\Repository*/
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion');
        $emotions = $this->getEmotion($repository);
        //iterate all emotions to select the element data.

        foreach ($emotions as &$emotion) {
            //for each emotion we have to iterate the elements to get the element data.
            foreach ($emotion['elements'] as &$element) {
                $component = $element['component'];
                $elementQuery = $repository->getElementDataQuery($element['id'], $element['componentId']);
                $componentData = $elementQuery->getArrayResult();
                $data = array();
                $data["objectId"] = md5($element["id"]);
                //we have to iterate the component data to decode the values.
                foreach ($componentData as $entry) {
                    $value = '';
                    switch (strtolower($entry['valueType'])) {
                        case "json":
                            $value = Zend_Json::decode($entry['value']);
                            break;
                        case "string":
                        default:
                            $value = $entry['value'];
                            break;
                    }
                    $data[$entry['name']] = $value;
                }

                $data = Enlight()->Events()->filter('Shopware_Controllers_Widgets_Emotion_AddElement', $data, array('subject' => $this, 'element' => $element));

                if (!empty($component['convertFunction'])) {
                    $data = $this->$component['convertFunction']($data, $this->Request()->getParam('categoryId'), $element);
                }

                $element['data'] = $data;

            }
        }

        $this->View()->assign('categoryId', (int)$this->Request()->getParam('categoryId'));
        $this->View()->assign('sEmotions', $emotions, true);
        $this->View()->assign('Controller', (string)$this->Request()->getParam('controllerName'));
    }

    private function getArticle($data, $categoryId, $element)
    {
        if ($data["article_type"] == "newcomer") {
            // new product
            $data = array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('new', $categoryId, 0, false));
        } elseif ($data["article_type"] == "topseller") {
            // top product
            $temp = Shopware()->Modules()->Articles()->sGetPromotionById('top', $categoryId, 0, false);
            if (empty($temp["articleID"])) {
                $data = array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('random', $categoryId, 0, false));
            } else {
                $data = array_merge($data, $temp);
            }
        } elseif ($data["article_type"] == "random_article") {
            // random product
            $data = array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('random', $categoryId, 0, false));

        } else {
            // Fix product
            $data = array_merge($data, $this->articleByNumber($data["article"]));
        }

        return $data;
    }

    /**
     * Gets the specific blog entry from the database.
     *
     * @param $data
     * @param $category
     * @param $element
     * @internal param $ {Array} $data
     * @return array {Array} $data
     */
    private function getBlogEntry($data, $category, $element)
    {
        $entryAmount = (int)$data['entry_amount'];

        // Get the category model for the given category ID
        /** @var $category \Shopware\Models\Category\Category */
        $category = Shopware()->Models()->find('Shopware\Models\Category\Category', $category);

        if(!$category) {
            return $data;
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('blog', 'media', 'mappingMedia'))
            ->from('Shopware\Models\Blog\Blog', 'blog')
            ->leftJoin('blog.media', 'mappingMedia', \Doctrine\ORM\Query\Expr\Join::WITH, 'mappingMedia.preview = 1')
            ->leftJoin('mappingMedia.media', 'media')
            ->leftJoin('blog.category', 'category')
            ->where('blog.active = 1')
            ->andWhere('blog.displayDate <= ?1')
            ->andWhere('category.left >= ?2')
            ->andWhere('category.right <= ?3')
            ->orderBy('blog.displayDate', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($entryAmount)
            ->setParameter(1, date('Y-m-d H:i:s'))
            ->setParameter(2, $category->getLeft())
            ->setParameter(3, $category->getRight());


        $result = $builder->getQuery()->getArrayResult();

        foreach ($result as &$entry) {
            foreach ($entry['media'] as $media) {
                if (!empty($media['mediaId'])) {
                    $mediaModel = Shopware()->Models()->find('Shopware\Models\Media\Media', $media['mediaId']);
                    if ($mediaModel != null) {
                        $entry['media']['thumbnails'] = array_values($mediaModel->getThumbnails());
                        $entry['media'] = array('path' => $mediaModel->getPath(), 'thumbnails' => array_values($mediaModel->getThumbnails()));
                    }
                }
            }
        }

        if (!empty($result)) {
            $data['totalCount'] = count($result);
            $data['entries'] = $result;
        }
        return $data;
    }

    private function getArticleByNumber($data, $category, $element)
    {
        return $data;
    }

    private function getCategoryTeaser($data, $category, $element)
    {

        // First get category name
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('category.name')
            ->from('Shopware\Models\Category\Category', 'category')
            ->where('category.id = ?1')
            ->setParameter(1, $data["category_selection"]);

        $categoryName = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $data["categoryName"] = $categoryName["name"];

        // Second get category image per random, if configured
        if ($data["image_type"] != "selected_image") {
            // Get random article from selected $category
            $temp = Shopware()->Modules()->Articles()->sGetPromotionById('random', $data["category_selection"], 0, true);

            $data["image"] = $temp["image"]["src"][2];
        }
        return $data;
    }

    private function articleByNumber($number)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('article.id')
            ->from('Shopware\Models\Article\Article', 'article')
            ->join('article.details', 'details')
            ->where('details.number = ?1')
            ->setParameter(1, $number)
            ->setFirstResult(0)
            ->setMaxResults(1);

        $articleId = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $article = array();
        if (!empty($articleId['id'])) {
            $article = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $articleId['id']);
        }
        return $article;
    }

    private function getBannerMappingLinks($data, $category, $element)
    {

        if(!empty($data['link'])) {
            preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $data['link'], $matches);

            if(empty($matches) && substr($data['link'], 0, 1) === '/') {
                $data['link'] = $this->Request()->getBaseUrl() . $data['link'];
            }
        }

        $mappings = $data['bannerMapping'];
        if (!empty($mappings)) {
            foreach ($mappings as $key => $mapping) {
                $number = $mapping['link'];

                if(!empty($number)) {
                    preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $number, $matches);

                    if(empty($matches)) {
                        if(substr($number, 0, 1) === '/') {
                            $mapping['link'] = $this->Request()->getBaseUrl() . $number;
                        } else {
                            $mapping['link'] = $this->articleByNumber($number);
                            $mapping['link'] = $mapping['link']['linkDetails'];
                        }
                    }
                }

                $mappings[$key] = $mapping;
            }
        }
        $data['bannerMapping'] = $mappings;

        return $data;
    }

    private function getManufacturerSlider($data, $category, $element)
    {

        if (empty($data["manufacturer_type"])) {
            return $data;
        }

        // Get all manufacturers
        if ($data["manufacturer_type"] == "manufacturers_by_cat") {
            $data["values"] = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($data["manufacturer_category"], 12);
        } else {
            $selectedManufacturers = $data["selected_manufacturers"];
            $manufacturers = array();

            foreach ($selectedManufacturers as $k => $manufacturer) {
                $manufacturers[] = $manufacturer["supplierId"];
            }

            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->select('supplier.id', 'supplier.name', 'supplier.image', 'supplier.link', 'supplier.description')
                ->from('Shopware\Models\Article\Supplier', 'supplier')
                ->where('supplier.id IN (?1)')
                ->setParameter(1, $manufacturers);

            $data["values"] = $builder->getQuery()->getArrayResult();

            $temporaryValues = array();
            foreach ($manufacturers as $manufacturer){
                foreach ($data["values"] as $value){
                    if ($value["id"] == $manufacturer){
                        $temporaryValues[] = $value;
                    }
                }
            }

            $data["values"] = $temporaryValues;

            foreach ($data["values"] as &$value) {
                $query = array('sViewport' => 'cat', 'sCategory' => $category, 'sPage' => 1, 'sSupplier' => $value["id"]);
                $value["link"] = Shopware()->Router()->assemble($query);
            }
        }

        return $data;
    }

    private function getBannerSlider($data, $category, $element)
    {
        $data["values"] = $data["banner_slider"];

        foreach($data["values"] as &$value) {
            if(!empty($value['link'])) {
                preg_match('/^(http|https):\/\//', $value['link'], $matches);

                if(empty($matches)) {
                    $value['link'] = $this->Request()->getBaseUrl() . $value['link'];
                }
            }
        }

        return $data;
    }

    private function getArticleSlider($data, $category, $element)
    {
        if (!isset($data["article_slider_select"])) {
            $data["article_slider_select"] = "horizontal";
        }

        // Determinate how many products showing on initial request
        if ($data["article_slider_select"] == "horizontal") {
            $perPage = $element['endCol'] - $element['startCol'] + 1;
        } else {
            $perPage = $element['endRow'] - $element['startRow'] + 1;
        }

        $values = array();

        $max = $data["article_slider_max_number"];
        $maxPages = round($max / $perPage);

        switch ($data["article_slider_type"]) {
            case "selected_article":
                foreach ($data["selected_articles"] as &$article) {
                    $articleId = $article["articleId"];
                    $entry = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $articleId, false);
	                if (!empty($entry["articleID"])) $values[] = $entry;
                }
                break;
            case "topseller":
                $temp = $this->getProductTopSeller($category, 0, $perPage);
                $values = $temp["values"];
                $data["pages"] = $temp["pages"] > $maxPages ? $maxPages : $temp["pages"];

                $query = array('controller' => 'emotion', 'module' => 'widgets', 'action' => 'emotionTopSeller');
                $data["ajaxFeed"] = Shopware()->Router()->assemble($query);
                break;
            case "newcomer":
                $temp = $this->getProductNewcomer($category, 0, $perPage);
                $values = $temp["values"];
                $data["pages"] = $temp["pages"] > $maxPages ? $maxPages : $temp["pages"];

                $query = array('controller' => 'emotion', 'module' => 'widgets', 'action' => 'emotionNewcomer');
                $data["ajaxFeed"] = Shopware()->Router()->assemble($query);
                break;
            default;
                // Prevent the slider form endless loading
                $data['article_slider_type'] = 'selected_article';
                return $data;
        }

        $data["values"] = $values;
        $data['categoryId'] = $category;
        return $data;
    }

    private function getProductNewcomer($category, $offset = 0, $limit)
    {
        $perPage = "$offset,$limit";
        $sql = "
            SELECT DISTINCT SQL_CALC_FOUND_ROWS a.id AS id
            FROM s_articles a, s_articles_categories ac,s_categories c,s_categories c2
            WHERE a.active=1
            AND a.id=ac.articleID
            AND c.id=?
            AND c2.active=1
            AND c2.left >= c.left
            AND c2.right <= c.right
            AND ac.articleID=a.id
            AND ac.categoryID=c2.id
            ORDER BY a.datum DESC
            LIMIT {$perPage}
        ";

        $articles = Shopware()->Db()->fetchAll($sql, array($category));

        $count = Shopware()->Db()->fetchOne("SELECT FOUND_ROWS()");
        $pages = round($count / $limit);

        $values = array();
        foreach ($articles as &$article) {
            $articleId = $article["id"];

            $value = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $articleId, false);
            if (!$value) {
                continue;
            }

            $values[] = $value;
        }

        return array("values" => $values, "pages" => $pages);

    }

    private function getProductTopSeller($category, $offset = 0, $limit)
    {
        $perPage = "$offset,$limit";

        $sql = "
        SELECT SQL_CALC_FOUND_ROWS a.id AS articleID, SUM(IF(o.id, IFNULL(od.quantity, 0), 0))+pseudosales AS quantity
        FROM s_articles_categories ac, s_categories c, s_categories c2, s_articles a

        LEFT JOIN s_order_details od
        ON a.id = od.articleID
        AND od.modus = 0

        LEFT JOIN s_order o
        ON o.ordertime>=DATE_SUB(NOW(),INTERVAL 30 DAY)
        AND o.status >= 0
        AND o.id = od.orderID

        WHERE a.active = 1
        AND c.id=?
        AND c2.active=1
        AND c2.left >= c.left
        AND c2.right <= c.right
        AND ac.articleID=a.id
        AND ac.categoryID=c2.id

        GROUP BY a.id
        ORDER BY quantity DESC, topseller DESC
        LIMIT {$perPage}
        ";

        $articles = Shopware()->Db()->fetchAll($sql, array($category));

        $count = Shopware()->Db()->fetchOne("SELECT FOUND_ROWS()");
        $pages = round($count / $limit);

        $values = array();
        foreach ($articles as &$article) {
            $articleId = $article["articleID"];

            $value = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $articleId, false);
            if (!$value) {
                continue;
            }
            $values[] = $value;
        }

        return array("values" => $values, "pages" => $pages);
    }
}
