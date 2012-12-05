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

class Shopware_Controllers_Backend_Wizard extends Enlight_Controller_Action
{
    /**
     * Doing global licence check for this controller
     * @return void
     */
    public function init()
    {
        $licenceCheck = Shopware()->Plugins()->Frontend()->SwagWizard()->checkLicense(false);
        $this->View()->licenceCheck = $licenceCheck;
    }

    /**
     * Pre dispatch method
     *
     * @return void
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), array('index', 'detail', 'skeleton'))) {
            Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        }
    }

    /**
     * Index action method method
     *
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function skeletonAction()
    {
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getProductsAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryID', false);
        $wizardId   = (int) $this->Request()->getParam('wizardID', false);

        $limit = (int) $this->Request()->getParam('limit', 25);
        $start = (int) $this->Request()->getParam('start', 0);

        $dir  = (empty($_REQUEST['dir'])||$_REQUEST['dir']=='ASC') ? 'ASC' : 'DESC';
        $sort = (empty($_REQUEST['sort'])||is_array($_REQUEST['sort'])) ? 'name' : preg_replace('#[^\w]#','',$_REQUEST['sort']);

        $sqlWhere  = '';
        $sqlJoin   = '';
        $sqlSelect = '';

        if (!empty($_REQUEST["search"])) {
            $search = Shopware()->Db()->quote("%".trim($_REQUEST["search"])."%");
            $sqlWhere .= " AND ( d.ordernumber LIKE  $search ";
            $sqlWhere .= "OR a.name LIKE $search ";
            $sqlWhere .= "OR s.name LIKE $search  ) ";
        }

        if ($wizardId) {
            $sqlJoin = 'JOIN `s_plugin_wizard_articles` wa ON a.id = wa.articleID AND wa.wizardID = ' . $wizardId;

            if (empty($_REQUEST['invert'])&&empty($_REQUEST['filterID'])) {
                $sqlJoin = 'LEFT '.$sqlJoin;
                $sqlWhere .= "AND wa.wizardID IS NULL";
            }
        }

        if ($categoryId) {
            $sqlJoin .=  "
                LEFT JOIN s_categories c
                    ON c.id = $categoryId
                LEFT JOIN s_categories c2
                    ON c2.left >= c.left
                    AND c2.right <= c.right
                JOIN s_articles_categories ac
                    ON ac.articleID = a.id AND ac.categoryID = c2.id
            ";
        }

        if (!empty($_REQUEST['filterID'])&&is_numeric($_REQUEST['filterID'])) {
            $filterID = (int) $_REQUEST['filterID'];
            $sql = 'SELECT `typeID` FROM `s_plugin_wizard_filters` WHERE `id`=?';
            $typeID = Shopware()->Db()->fetchOne($sql, array($filterID));

            $sqlJoin .= "
                JOIN `s_plugin_wizard_filters` wf ON wf.id=$filterID";
            $sqlSelect .= ", wf.typeID";

            $sql = 'SELECT `id` FROM `s_plugin_wizard_values` WHERE `filterID`=? ORDER BY `key`';
            $valueIds = Shopware()->Db()->fetchCol($sql, array($filterID));
            foreach ($valueIds as $valueId) {
                $sqlJoin .= "
                    LEFT JOIN `s_plugin_wizard_relations` wr$valueId
                    ON a.id=wr$valueId.articleID AND wr$valueId.valueID=$valueId AND wr$valueId.filterID=$filterID";
                if (in_array($typeID, array(1, 3))) {
                    $sqlSelect .= ", wr$valueId.valueID IS NOT NULL as score_$valueId";
                } else {
                    $sqlSelect .= ", wr$valueId.score as score_$valueId";
                }
            }
        }

        if (!empty($_REQUEST["insert"]) && !empty($_REQUEST['wizardID'])) {
            $wizardID = (int) $_REQUEST['wizardID'];
            $sql = "
                INSERT IGNORE INTO `s_plugin_wizard_articles` (
                    `wizardID`,	`articleID`
                )
                SELECT $wizardID, a.id
                FROM s_articles a
                INNER JOIN s_articles_details d
                ON a.id=d.articleID
                AND d.kind=1
                LEFT JOIN s_articles_supplier s
                ON s.id=a.supplierID
                $sqlJoin
                WHERE a.mode = 0
                $sqlWhere
            ";
            Shopware()->Db()->query($sql);
        }

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                a.id, a.name, d.ordernumber, s.name as supplier
                $sqlSelect
            FROM s_articles a
            INNER JOIN s_articles_details d
            ON a.main_detail_id = d.id
            LEFT JOIN s_articles_supplier s
            ON s.id=a.supplierID
            $sqlJoin
            WHERE a.mode = 0
            $sqlWhere
            ORDER BY $sort $dir
            LIMIT $start, $limit
        ";
        $rows = Shopware()->Db()->fetchAll($sql);

        foreach ($rows as &$row) {
            if (!empty($valueIds)) {
                foreach ($valueIds as $valueId) {
                    if (in_array($typeID, array(1, 3))) {
                        $row['score_'.$valueId] = (bool) $row['score_'.$valueId];
                    } else {
                        $row['score_'.$valueId] = (int) $row['score_'.$valueId];
                    }
                }
            }
        }

        $sql = 'SELECT FOUND_ROWS() as count';
        $count = Shopware()->Db()->fetchOne($sql);

        echo Zend_Json::encode(array('count'=>$count, 'data'=>$rows));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function saveProductsAction()
    {
        $wizardID       = (int) $this->Request()->getParam('wizardID');
        $deleteProducts = Shopware()->Db()->quote($this->Request()->deleteProducts);
        $addProducts    = Shopware()->Db()->quote($this->Request()->addProducts);

        $sql = "
            INSERT IGNORE INTO `s_plugin_wizard_articles` (
                `wizardID`,	`articleID`
            )
            SELECT $wizardID, a.id
            FROM s_articles a
            WHERE a.mode = 0
            AND a.id IN($addProducts)
        ";
        Shopware()->Db()->query($sql);

        $sql = "
            DELETE
            FROM s_plugin_wizard_articles
            WHERE articleID IN($deleteProducts)
            AND `wizardID`=$wizardID
        ";
        Shopware()->Db()->query($sql);

        echo Zend_Json::encode(array('success'=>true));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getCategoriesAction()
    {
        $node     = (int) $this->Request()->getParam('node', 1);
        $wizardID = (int) $this->Request()->getParam('wizardID');

        $nodes = array();
        $sql = "
            SELECT c.id, c.description as text, c.parent as parentId, COUNT(c2.id) as count,
            (
                SELECT COUNT(articleID)
                FROM s_articles_categories
                WHERE categoryID = c.id
                GROUP BY categoryID
            ) as countArticles,
            (
                SELECT 1
                FROM s_plugin_wizard_categories
                WHERE categoryID = c.id
                AND wizardID=?
            ) as checked
            FROM s_categories c
            LEFT JOIN s_categories c2
            ON c2.parent=c.id
            WHERE c.parent=?
            GROUP BY c.id
            ORDER BY c.left, c.position, c.description
        ";
        $getCategories = Shopware()->Db()->fetchAll($sql,array($wizardID, $node));
        if (!empty($getCategories) && count($getCategories)) {
            foreach ($getCategories as $category) {
                $category['leaf'] = empty($category['count']);
                if (!empty($wizardID)) {
                    $category['checked'] = (bool) $category['checked'];
                } else {
                    unset($category['checked']);
                }
                $nodes[] = $category;
            }
        }
        echo Zend_Json::encode($nodes);
    }

    /**
     * Index action method
     *
     * @return string
     */
    public function getCategoryPathsAction()
    {
        $wizardID = (int) $this->Request()->getParam('wizardID');

        $sql = 'SELECT categoryID FROM s_plugin_wizard_categories WHERE wizardID=?';
        $categories = Shopware()->Db()->fetchCol($sql, array($wizardID));

        $rows = array();
        foreach ($categories as $category) {
            $rows[] = $this->getCategoryPath($category);
        }

        echo Zend_Json::encode(array('data'=>$rows, 'success'=>true));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function saveCategoriesAction()
    {
        $wizardID = (int) $this->Request()->getParam('wizardID');
        $categories = Shopware()->Db()->quote($this->Request()->categories);

        $sql = "
            INSERT IGNORE INTO `s_plugin_wizard_categories` (
                `wizardID`,	`categoryID`
            )
            SELECT $wizardID, c.id
            FROM s_categories c
            WHERE c.id IN($categories)
        ";
        Shopware()->Db()->query($sql);

        $sql = "
            DELETE
            FROM `s_plugin_wizard_categories`
            WHERE `categoryID` NOT IN($categories)
            AND `wizardID`=$wizardID
        ";
        Shopware()->Db()->query($sql);

        echo Zend_Json::encode(array('success'=>true));
    }

    /**
     * Index action method
     *
     * @param $start
     * @param  string $separator
     * @return string
     */
    public function getCategoryPath($start, $separator='/')
    {
        $sql = 'SELECT parent FROM s_categories WHERE id=?';
        $parent = Shopware()->Db()->fetchOne($sql, array($start));
        if (empty($parent)||$parent<2||$parent==$start) {
            return $separator.$parent;
        }

        return $this->getCategoryPath($parent, $separator).$separator.$parent;
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function wizardListAction()
    {
        $node = $this->request()->node;
        $nodes = array();
        if ($node=='root') {
            $sql = "
                (
                    SELECT 0 as id, 'Global' as name
                ) UNION (
                    SELECT cm.id, CONCAT(cm.name, ' (', l.locale, ')')
                    FROM s_core_multilanguage cm, s_core_locales l
                    WHERE cm.locale = l.id
                ) ORDER BY id
            ";
            $rows = Shopware()->Db()->fetchAll($sql);
            foreach ($rows as $row) {
                $nodes[] = array('text'=>$row['name'], 'id'=>$row['id'], 'leaf'=>false, 'type'=>'shop');
            }
        } else {
            if (strpos($node, '_')===false) {
                if (!empty($node)) {
                    $sql = "
                        SELECT * FROM s_plugin_wizard WHERE shopID = ? ORDER BY name
                    ";
                    $result = Shopware()->Db()->fetchAll($sql, array($node));
                } else {
                    $sql = "
                        SELECT * FROM s_plugin_wizard WHERE shopID IS NULL ORDER BY name
                    ";
                    $result = Shopware()->Db()->fetchAll($sql);
                }
                foreach ($result as $node) {
                    $nodes[] = array(
                        'text'=>$node['name'],
                        'id'=>'wizard_'.$node['id'],
                        'leaf'=>false,
                        'wizardID'=>$node['id'],
                        'active'=>(bool) $node['active'],
                        'type'=>'wizard'
                    );
                }
            } else {
                list($shopId, $wizardId) = explode('_', $node);
                $sql = "
                    SELECT * FROM s_plugin_wizard_filters WHERE wizardID = ? ORDER BY position, name
                ";
                $result = Shopware()->Db()->fetchAll($sql, array($wizardId));
                foreach ($result as $node) {
                    $nodes[] = array(
                        'text'=>$node['name'],
                        'id'=>'filter_'.$node['id'],
                        'leaf'=>true,
                        'type'=>'filter',
                        'typeID'=>$node['typeID'],
                        'wizardID'=>$node['wizardID'],
                        'filterID'=>$node['id']
                    );
                }
            }
        }
        echo Zend_Json::encode($nodes);
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function shopListAction()
    {
        $sql = "
            (
                SELECT 0 as id, 'Global' as name
            ) UNION (
                SELECT cm.id, CONCAT(cm.name, ' (', l.locale, ')')
                FROM s_core_multilanguage cm, s_core_locales l
                WHERE cm.locale = l.id
            ) ORDER BY id
        ";
        $rows = Shopware()->Db()->fetchAll($sql);

        echo Zend_Json::encode(array('data'=>$rows, 'count'=>count($rows)));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function saveWizardAction()
    {
        $id = $this->Request()->id;
        $name = $this->Request()->name;
        $description = $this->Request()->description;
        $block = $this->Request()->block;
        $shopID = $this->Request()->shopID;
        $uploadDir = Shopware()->DocPath() . 'media/image/';
        $max_quantity = (int) $this->Request()->max_quantity;

        $data = array(
            'name' => $name,
            'description' => empty($description) ? null : $description,
            'shopID' => empty($shopID) ? null : $shopID,
            'image' => empty($image) ? null : $image,
            'active' => $this->Request()->active ? 1 : 0,
            'sidebar' => $this->Request()->sidebar ? 1 : 0,
            'block' => empty($block) ? null : $block,
            'hide_empty' => $this->Request()->hide_empty ? 1 : 0,
            'preview' => $this->Request()->preview ? 1 : 0,
            'listing' => $this->Request()->listing ? 1 : 0,
            'max_quantity' => empty($max_quantity) ? null : $max_quantity,
            'show_other' => $this->Request()->show_other ? 1 : 0,
        );

        try {
            $upload = new Zend_File_Transfer_Adapter_Http();
            if ($upload->isUploaded()) {
                $upload->setDestination($uploadDir);
                $upload->addValidator('Extension', false, array('jpg', 'jpeg', 'png', 'gif'));
                if (!$upload->isValid()) {
                    $message = $upload->getMessages();
                    $message = implode("<br />\n", $message);
                    throw new Exception($message);
                }
                if (!empty($id)) {
                    $oldFiles = glob($uploadDir.'wizard_'.$id.'_*');
                    if (!empty($oldFiles)) {
                        foreach ($oldFiles as $oldFile) {
                            if (file_exists($oldFile)) {
                                @unlink($oldFile);
                            }
                        }
                    }
                }
            }

            if (empty($id)) {
                $sql = "
                    INSERT INTO s_plugin_wizard
                        (name, shopID)
                    VALUES (?, ?)
                ";
                Shopware()->Db()->query($sql,array($name, $shopID));
                $id = Shopware()->Db()->lastInsertId();
            }

            if ($upload->isUploaded()) {
                $image = 'wizard_'.$id.'_'.$upload->getFileName(null, false);
                $upload->addFilter('Rename', $uploadDir.$image);
                if (!$upload->receive()) {
                    $message = $upload->getMessages();
                    $message = implode("<br />\n", $message);
                    throw new Exception($message);
                }
                $data['image'] = $image;
            }

            Shopware()->Db()->update('s_plugin_wizard', $data,  array('id='.(int) $id));

        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        echo htmlspecialchars(Zend_Json::encode(array('success'=>empty($message), 'id'=>$id, 'message'=>isset($message)?$message:'')));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function saveFilterAction()
    {
        $id = $this->Request()->id;
        $wizardID = $this->Request()->wizardID;
        $typeID = $this->Request()->typeID;
        $storeID = $this->Request()->storeID;
        $name = $this->Request()->name;

        $description = $this->Request()->description;
        $active = $this->Request()->active;
        $position = $this->Request()->position;

        if (empty($id)) {
            $sql = "
                INSERT INTO s_plugin_wizard_filters
                    (name, wizardID)
                VALUES (?, ?)
            ";
            Shopware()->Db()->query($sql, array($name, $wizardID));
            $id = Shopware()->Db()->lastInsertId();
        }
        $data = array(
            'description' => empty($description) ? null : utf8_decode($description),
            'name' => $name,
            'active' => $active ? 1 : 0,
            'position' => $position,
            'typeID' => $typeID,
            'storeID' => $storeID
        );
        if (in_array($typeID, array(3, 4, 9))) {
            $data['range_from'] = (float) $this->Request()->range_from;
            $data['range_to'] = (float) $this->Request()->range_to;
            $data['steps'] = (float) $this->Request()->steps;
        }
        $where = array(
            'id='.(int) $id
        );
        Shopware()->Db()->update('s_plugin_wizard_filters', $data, $where);

        $values = $this->Request()->values;

        if (!empty($values)) {
            foreach ($values as $key=>$value) {
                $sql = "
                    INSERT INTO `s_plugin_wizard_values`
                        (`filterID`, `key`, `value`)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)
                ";
                $value = html_entity_decode($value);
                Shopware()->Db()->query($sql,array($id, $key, $value));
            }
            $sql = "
                DELETE FROM `s_plugin_wizard_values`
                WHERE `filterID`=?
                AND `key` NOT IN(".Shopware()->Db()->quote(array_keys($values)).")
            ";
            Shopware()->Db()->query($sql,array($id));
        }

        echo Zend_Json::encode(array('success'=>true, 'id'=>$id));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getWizardAction()
    {
        if (empty($this->Request()->id) && !empty($this->Request()->shopID)) {
            echo Zend_Json::encode(array(
                'success' => true,
                'data'    => array('shopID' => (int) $this->Request()->shopID)
            ));

            return;
        }
        $result = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_plugin_wizard WHERE id=?',
            array($this->Request()->id));

        echo Zend_Json::encode(array('success' => true, 'data' => $result));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getFilterAction()
    {
        $filterID = (int) $this->Request()->id;
        $typeID = (int) $this->Request()->typeID;
        $result = Shopware()->Db()->fetchRow('SELECT * FROM s_plugin_wizard_filters WHERE id=?', array($filterID));
        if (!empty($result)) {

        } else {
            $result = array('typeID' => $typeID);
        }
        echo Zend_Json::encode(array('success' => true, 'data' => $result));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getFilterValuesAction()
    {
        $typeID = (int) $this->Request()->typeID;
        $sql = '
            SELECT `key`, `value`, `id` FROM s_plugin_wizard_values WHERE filterID=? ORDER BY `key` + 0
        ';
        $values = Shopware()->Db()->fetchAssoc($sql, array($this->Request()->filterID));
        if (empty($values)) {
            if ($typeID==1) {
                $rows = array(
                    array('key'=>1, 'value'=>'Ja'),
                    array('key'=>2, 'value'=>'Nein')
                );
            } elseif ($typeID==2 || $typeID==6) {
                $rows = array(
                    array('key'=>1, 'value'=>'Antwort A'),
                    array('key'=>2, 'value'=>'Antwort B'),
                    array('key'=>3, 'value'=>'Antwort C')
                );
            } elseif ($typeID==3) {
                $rows = array(
                    array('key'=>1, 'value'=>'0-10 €'),
                    array('key'=>2, 'value'=>'10-50 €'),
                    array('key'=>3, 'value'=>'100-200 €'),
                    array('key'=>4, 'value'=>'200-500 €'),
                    array('key'=>5, 'value'=>'500-1000 €'),
                );
            } elseif ($typeID==4) {
                $rows = array(
                    array('key'=>1, 'value'=>'1 - unwichtig'),
                    array('key'=>2, 'value'=>'2'),
                    array('key'=>3, 'value'=>'3'),
                    array('key'=>4, 'value'=>'4'),
                    array('key'=>5, 'value'=>'5 - sehr wichtig'),
                );
            }
        } else {
            $rows = array();
            if (in_array($typeID, array(3, 4))) {
                $sql = 'SELECT * FROM s_plugin_wizard_filters WHERE id=?';
                $filter = Shopware()->Db()->fetchRow($sql, array($this->Request()->filterID));
                for ($key=(float) $filter['range_from'];$key<=$filter['range_to'];$key+=$filter['steps']) {
                    $rows[] = array('key'=>$key, 'value'=>isset($values[$key]['value']) ? $values[$key]['value'] : '');
                }
            } else {
                foreach ($values as $key => $value) {
                    $rows[] = array('id'=>$value['id'], 'key'=>$key, 'value'=>$value['value']);
                }
            }
        }
        echo Zend_Json::encode(array('data' => $rows, 'count' => count($rows)));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function saveRelationsAction()
    {
        $filterID = (int) $this->Request()->getParam('filterID');

        $sql = 'SELECT `typeID` FROM `s_plugin_wizard_filters` WHERE `id`=?';
        $typeID = Shopware()->Db()->fetchOne($sql, array($filterID));
        $relations = $this->Request()->relations;
        $sql = 'SELECT `id` FROM `s_plugin_wizard_values` WHERE `filterID`=? ORDER BY `key`';
        $valueIds = Shopware()->Db()->fetchCol($sql, array($filterID));

        if (empty($filterID) || empty($relations) || empty($valueIds)) {
            return;
        }

        foreach ($relations as $relation) {
            foreach ($valueIds as $valueId) {
                if (!empty($relation['score_'.$valueId]) && !in_array($typeID, array(1, 3))) {
                    $sql = "
                        INSERT INTO `s_plugin_wizard_relations`
                            (`filterID`, `articleID`, `valueID`, `score`)
                        VALUES
                            (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            `score`=VALUES(`score`)
                    ";
                    Shopware()->Db()->query($sql, array(
                        $filterID,
                        $relation['id'],
                        $valueId,
                        $relation['score_'.$valueId]
                    ));
                } elseif (!empty($relation['score_'.$valueId]) && $relation['score_'.$valueId] != 'false') {
                    $sql = "
                        INSERT IGNORE INTO `s_plugin_wizard_relations`
                            (`filterID`, `articleID`, `valueID`)
                        VALUES
                            (?, ?, ?)
                    ";
                    Shopware()->Db()->query($sql, array(
                        $filterID,
                        $relation['id'],
                        $valueId
                    ));
                } else {
                    $sql = "
                        DELETE FROM `s_plugin_wizard_relations`
                        WHERE `filterID`=?
                        AND `articleID`=?
                        AND `valueID`=?
                    ";
                    Shopware()->Db()->query($sql, array(
                        $filterID,
                        $relation['id'],
                        $valueId
                    ));
                }
            }
        }
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function copyFilterAction()
    {
        $filterID = (int) $this->Request()->getParam('filterID');
        $wizardID = (int) $this->Request()->getParam('wizardID');

        try {
            $this->copyFilter($filterID, $wizardID);
        } catch (Exception $e) {
            die((string) $e);
        }
    }

    /**
     * @return void
     */
    public function copyWizardAction()
    {
        $wizardID = (int) $this->Request()->getParam('wizardID');
        $shopID   = (int) $this->Request()->getParam('shopID');

        if (empty($shopID)) {
            $shopID = null;
        }

        try {
            $this->copyWizard($wizardID, $shopID);
        } catch (Exception $e) {
            die((string) $e);
        }
    }

    /**
     * @param $wizardID
     * @param $shopID
     */
    public function copyWizard($wizardID, $shopID)
    {
        $sql = '
            INSERT INTO `s_plugin_wizard` (`name`, `description`, `shopID`, `image`, `sidebar`, `active`, `hide_empty`, `preview`, `max_quantity`, `show_other`)
            SELECT `name`, `description`, ? as `shopID`, `image`, `sidebar`, `active`, `hide_empty`, `preview`, `max_quantity`, `show_other`
            FROM `s_plugin_wizard`
            WHERE `id`=?
        ';
        Shopware()->Db()->query($sql, array(
            $shopID,
            $wizardID
        ));
        $newWizardID = Shopware()->Db()->lastInsertId();

        $sql = '
            INSERT INTO `s_plugin_wizard_articles` (`wizardID`, `articleID`)
            SELECT ? as `wizardID`, `articleID`
            FROM `s_plugin_wizard_articles`
            WHERE `wizardID`=?
        ';
        Shopware()->Db()->query($sql, array(
            $newWizardID,
            $wizardID
        ));

        $sql = '
            INSERT INTO `s_plugin_wizard_categories` (`wizardID`, `categoryID`)
            SELECT ? as `wizardID`, `categoryID`
            FROM `s_plugin_wizard_categories`
            WHERE `wizardID`=?
        ';
        Shopware()->Db()->query($sql, array(
            $newWizardID,
            $wizardID
        ));

        $sql = '
            SELECT `id`
            FROM `s_plugin_wizard_filters`
            WHERE `wizardID`=?
        ';
        $filterIDs = Shopware()->Db()->fetchCol($sql, array(
            $wizardID
        ));
        if (!empty($filterIDs)) {
            foreach ($filterIDs as $filterID) {
                $this->copyFilter($filterID, $newWizardID);
            }
        }
    }

    /**
     * @param $filterID
     * @param $wizardID
     */
    public function copyFilter($filterID, $wizardID)
    {
        $sql = '
            INSERT INTO `s_plugin_wizard_filters` (`wizardID`, `typeID`, `name`, `description`, `position`, `active`, `range_from`, `range_to`, `steps`)
            SELECT ? as `wizardID`, `typeID`, `name`, `description`, `position`, `active`, `range_from`, `range_to`, `steps`
            FROM `s_plugin_wizard_filters`
            WHERE `id`=?
        ';
        Shopware()->Db()->query($sql, array(
            $wizardID,
            $filterID
        ));
        $newFilterID = Shopware()->Db()->lastInsertId();

        $sql = '
            INSERT INTO `s_plugin_wizard_values` (`filterID`, `key`, `value`)
            SELECT ? as `filterID`, `key`, `value`
            FROM `s_plugin_wizard_values`
            WHERE `filterID`=?
        ';
        Shopware()->Db()->query($sql, array(
            $newFilterID,
            $filterID
        ));

        $sql = '
            INSERT IGNORE INTO `s_plugin_wizard_relations` (`filterID`, `articleID`, `valueID`, `score`)
            SELECT ? as `filterID`, wr.`articleID`, wv2.`id` as `valueID`, wr.`score`
            FROM `s_plugin_wizard_relations` wr,
            `s_plugin_wizard_articles` wa,
            `s_plugin_wizard_values` wv,
            `s_plugin_wizard_values` wv2
            WHERE wr.`articleID`=wa.`articleID`
            AND wr.`valueID`=wv.`id`
            AND wv.`key`=wv2.`key`
            AND wv2.`filterID`=?
            AND wa.`wizardID`=?
            AND wr.`filterID`=?
        ';
        Shopware()->Db()->query($sql, array(
            $newFilterID,
            $newFilterID,
            $wizardID,
            $filterID
        ));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function deleteFilterAction()
    {
        $filterID = (int) $this->Request()->getParam('filterID');
        try {
            $this->deleteFilter($filterID);
        } catch (Exception $e) {
            die((string) $e);
        }
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function deleteWizardAction()
    {
        $wizardID = (int) $this->Request()->getParam('wizardID');
        try {
            $this->deleteWizard($wizardID);
        } catch (Exception $e) {
            die((string) $e);
        }
    }

    /**
     * Delete wizard method
     *
     * @param int $wizardID
     */
    public function deleteWizard($wizardID)
    {
        $sql = '
            DELETE
            FROM `s_plugin_wizard`
            WHERE `id`=?
        ';
        Shopware()->Db()->query($sql, array(
            $wizardID
        ));

        $sql = '
            DELETE
            FROM `s_plugin_wizard_articles`
            WHERE `wizardID`=?
        ';
        Shopware()->Db()->query($sql, array(
            $wizardID
        ));

        $sql = '
            DELETE
            FROM `s_plugin_wizard_categories`
            WHERE `wizardID`=?
        ';
        Shopware()->Db()->query($sql, array(
            $wizardID
        ));

        $sql = '
            SELECT `id`
            FROM `s_plugin_wizard_filters`
            WHERE `wizardID`=?
        ';
        $filterIDs = Shopware()->Db()->fetchCol($sql, array(
            $wizardID
        ));
        if (!empty($filterIDs)) {
            foreach ($filterIDs as $filterID) {
                $this->deleteFilter($filterID);
            }
        }

        $uploadDir = Shopware()->DocPath('images_cms');
        $oldFiles = glob($uploadDir.'wizard_'.$wizardID.'_*');
        if (!empty($oldFiles)) {
            foreach ($oldFiles as $oldFile) {
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
        }
    }

    /**
     * Delete filter method
     *
     * @param int $filterID
     */
    public function deleteFilter($filterID)
    {
        $sql = '
            DELETE
            FROM `s_plugin_wizard_filters`
            WHERE `id`=?
        ';
        Shopware()->Db()->query($sql, array(
            $filterID
        ));

        $sql = '
            DELETE
            FROM `s_plugin_wizard_values`
            WHERE `filterID`=?
        ';
        Shopware()->Db()->query($sql, array(
            $filterID
        ));

        $sql = '
            DELETE
            FROM `s_plugin_wizard_relations`
            WHERE `filterID`=?
        ';
        Shopware()->Db()->query($sql, array(
            $filterID
        ));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function moveFilterAction()
    {
        $filter = (array) $this->Request()->filter;
        foreach ($filter as $position => $filterID) {
            Shopware()->Db()->update('s_plugin_wizard_filters', array('position'=>$position), array('id='.(int) $filterID));
        }
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getBlocksAction()
    {
        $rows = array(
            array('id'=>'frontend_index_left_campaigns_top', 'name'=>'Oben links'),
            array('id'=>'frontend_index_left_campaigns_middle', 'name'=>'Mitte links'),
            array('id'=>'frontend_index_left_campaigns_bottom', 'name'=>'Unten links'),
            //array('id'=>'frontend_home_right_campaign_top', 'name'=>'Oben rechts auf der Startseite'),
            //array('id'=>'frontend_home_right_campaign_middle', 'name'=>'Mitte rechts auf der Startseite'),
            //array('id'=>'frontend_home_right_campaign_bottom', 'name'=>'Unten rechts auf der Startseite'),
            array('id'=>'frontend_listing_right_campaign_top', 'name'=>'Oben rechts'),
            array('id'=>'frontend_listing_right_campaign_middle', 'name'=>'Mitte rechts'),
            array('id'=>'frontend_listing_right_campaign_bottom', 'name'=>'Unten rechts')
        );

        echo Zend_Json::encode(array('data' => $rows, 'count' => count($rows)));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getWizardStatisticsAction()
    {
        $wizardID = $this->Request()->wizardID;
        $sql = "
            (
                SELECT
                    'open' as `id`,
                    'Berater geöffnet' as `name`,
                    COUNT(*) as `count`
                FROM `s_plugin_wizard_requests` r
                WHERE r.wizardID=?
            ) UNION ALL (
                SELECT
                    'start' as `id`,
                    'Berater nicht gestartet' as `name`,
                    COUNT(*) as `count`
                FROM `s_plugin_wizard_requests` r
                WHERE r.filterID IS NULL
                AND r.wizardID=?
            ) UNION ALL (
                SELECT
                    'abort' as `id`,
                    'Berater abgebrochen' as `name`,
                    COUNT(*) as `count`
                FROM `s_plugin_wizard_requests` r
                WHERE r.filterID IS NOT NULL AND r.filterID!=0
                AND r.wizardID=?
            ) UNION ALL (
                SELECT
                    f.id as `id`,
                    CONCAT('- Filter: ', f.name) as name,
                    COUNT(r.id) as `count`
                FROM `s_plugin_wizard_filters` f
                LEFT JOIN `s_plugin_wizard_requests` r
                ON r.filterID=f.id
                WHERE f.wizardID=?
                GROUP BY f.id
                ORDER BY f.position, f.name
            ) UNION ALL (
                SELECT
                    'finish' as `id`,
                    'Berater abgeschlossen' as `name`,
                    COUNT(DISTINCT r.id) as `count`
                FROM `s_plugin_wizard_requests` r
                -- INNER JOIN `s_plugin_wizard_results` a
                -- ON a.requestID=r.id
                WHERE r.filterID=0
                AND r.wizardID=?
            ) UNION ALL (
                SELECT
                    'basket' as `id`,
                    'Vorschlag in den Warenkorb gelegt' as `name`,
                    COUNT(b.id) as `count`
                FROM `s_plugin_wizard_requests` r
                INNER JOIN `s_plugin_wizard_results` a
                ON a.requestID=r.id
                LEFT JOIN `s_order_basket` b
                ON b.articleID=a.articleID
                AND b.sessionID=r.sessionID
                WHERE r.wizardID=?
            ) UNION ALL (
                SELECT
                    'order' as `id`,
                    'Vorschlag bestellt' as `name`,
                    COUNT(d.id) as `count`
                FROM `s_plugin_wizard_requests` r
                INNER JOIN `s_plugin_wizard_results` a
                ON a.requestID=r.id
                INNER JOIN `s_order` o
                ON o.userID=r.userID
                AND o.ordertime > r.added
                AND o.status > 0
                INNER JOIN s_order_details d
                ON d.articleID=a.articleID
                AND d.orderID=o.id
                AND d.modus=0
                WHERE r.wizardID=?
            )
        ";
        $rows = Shopware()->Db()->fetchAssoc($sql, array(
            $wizardID, $wizardID, $wizardID, $wizardID, $wizardID, $wizardID, $wizardID
        ));

        if (!empty($rows)) {
            foreach ($rows as &$row) {
                $row['percent'] = number_format($row['count']/$rows['open']['count']*100, 2, ',', '').' %';
            }
        }
        echo Zend_Json::encode(array('data' => array_values($rows), 'count' => count($rows)));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getWizardSalesAction()
    {
        $wizardID = $this->Request()->getParam('wizardID');

        $sql = "
            SELECT
                o.id,
                o.ordernumber,
                o.userID as customerID,
                IF(b.id,
                    IF(b.company='', CONCAT(b.firstname, ' ', b.lastname), b.company),
                    IF(u.company='', CONCAT(u.firstname, ' ', u.lastname), u.company)
                ) as customer,
                o.invoice_amount as amount,
                o.ordertime as order_date,
                r.added as request_date,
                o.status as statusID,
                s.description as status
            FROM `s_plugin_wizard_requests` r
            INNER JOIN `s_plugin_wizard_results` a
            ON a.requestID=r.id
            INNER JOIN `s_order` o
            ON o.userID=r.userID
            AND o.ordertime > r.added
            -- AND o.status > 0
            INNER JOIN s_order_details d
            ON d.articleID=a.articleID
            AND d.orderID=o.id
            AND d.modus=0
            INNER JOIN `s_core_states` s
            ON s.id=o.status
            LEFT JOIN `s_order_billingaddress` b
            ON b.orderID=o.id
            LEFT JOIN `s_user_billingaddress` u
            ON u.userID=o.userID
            WHERE r.wizardID=?
        ";
        $rows = Shopware()->Db()->fetchAll($sql, array($wizardID));
        if (!empty($rows)) {
            foreach ($rows as &$row) {
                $row['order_date'] = strtotime($row['order_date']);
                $row['request_date'] = strtotime($row['request_date']);
                $row['amount'] = floatval($row['amount']);
            }
        }
        echo Zend_Json::encode(array('data'=>$rows, 'count'=>count($rows)));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function getClicksStatisticsAction()
    {
        $sql = "
            SELECT
                w.id,
                w.name,
                COUNT(*) as `hits`,
                (
                    SELECT COUNT(*)
                    FROM `s_plugin_wizard_requests` r
                    WHERE r.filterID IS NOT NULL
                    AND r.wizardID=w.id
                ) as `abort`,
                (
                    SELECT COUNT(b.id)
                    FROM `s_plugin_wizard_requests` r
                    INNER JOIN `s_plugin_wizard_results` a
                    ON a.requestID=r.id
                    LEFT JOIN `s_order_basket` b
                    ON b.articleID=a.articleID
                    AND b.sessionID=r.sessionID
                    WHERE r.wizardID=w.id
                ) as `basket`,
                (
                    SELECT COUNT(d.id)
                    FROM `s_plugin_wizard_requests` r
                    INNER JOIN `s_plugin_wizard_results` a
                    ON a.requestID=r.id
                    INNER JOIN `s_order` o
                    ON o.userID=r.userID
                    AND o.ordertime > DATE(r.changed)
                    INNER JOIN s_order_details d
                    ON d.articleID=a.articleID
                    AND d.orderID=o.id
                    AND d.modus=0
                    WHERE r.wizardID=w.id
                ) as `order`

            FROM `s_plugin_wizard` w

            LEFT JOIN `s_plugin_wizard_requests` r
            ON r.wizardID=w.id

            ORDER BY id DESC
            LIMIT 5
        ";
        $rows = Shopware()->Db()->fetchAll($sql);

        foreach ($rows as &$row) {
            $row['finish'] = $row['hits']-$row['basket']-$row['abort']-$row['order'];
        }

        echo Zend_Json::encode(array('data'=>$rows, 'count'=>count($rows)));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function filterTypeListAction()
    {
        $rows = array(
            1 => 'Frage mit Produktausschluss',
            2 => 'Frage mit Scoring',
            3 => 'Slider mit Produktausschluss',
            4 => 'Slider mit Scoring',
            6 => 'Frage mit Mehrfach-Antworten',

            7 => 'Filter nach Eigenschaften',
            8 => 'Filter nach Varianten',
            10 => 'Filter nach Attributen',
            9 => 'Slider nach Preis',
        );
        foreach ($rows as $key => &$row) {
            $row = array('id'=>$key, 'name'=>$row);
        }
        echo Zend_Json::encode(array('data'=>array_values($rows), 'count'=>count($rows)));
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function filterStoreListAction()
    {
        $filterTypeID = (int) $this->Request()->typeID;
        switch ($filterTypeID) {
            case 7:
                $sql = '
                    SELECT o.id , CONCAT(f.name, \': \', o.name) as name
                    FROM `s_filter_relations` r, `s_filter` f, `s_filter_options` o
                    WHERE f.id=r.groupID
                    AND o.id=r.optionID
                    ORDER BY f.position, f.name, r.position, o.name
                ';
                break;
            case 10:
                $sql = '
                    SELECT `name` as id, `name` as name
                    FROM `s_core_engine_elements`
                    WHERE `name` LIKE \'attr%\'
                    ORDER BY `name`
                ';
                break;
            default:
                return;
        }
        $rows = Shopware()->Db()->fetchAll($sql);
        if (!empty($rows)) {
            foreach ($rows as &$row) {
                if (strpos($row['id'], 'attr')===0) {
                    $row['id'] = substr($row['id'], 4);
                }
            }
        }
        echo Zend_Json::encode(array('data'=>$rows, 'count'=>count($rows)));
    }
}
