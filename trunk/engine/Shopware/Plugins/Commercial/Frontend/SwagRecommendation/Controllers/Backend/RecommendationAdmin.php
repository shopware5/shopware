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
class Shopware_Controllers_Backend_RecommendationAdmin extends Enlight_Controller_Action
{
	public function indexAction(){
		$config = Shopware()->Plugins()->Frontend()->SwagRecommendation()->Config();
		
		$this->View()->block_banner = $config->block_banner;
		$this->View()->block_new = $config->block_new;
		$this->View()->block_personal = $config->block_personal;
		$this->View()->block_supplier = $config->block_supplier;
		
		$this->View()->block_detail = $config->block_detail;
		$this->View()->bought_too = $config->bought_too ? "true" : "false";
		$this->View()->similary_viewed = $config->similary_viewed ? "true" : "false";
		
		$this->View()->loadTemplate(dirname(__FILE__)."/../../Views/backend/recommendation/index.tpl");
	}

	public function skeletonAction(){
        $this->View()->loadTemplate(dirname(__FILE__)."/../../Views/backend/recommendation/skeleton.tpl");
	}
	
	public function getCategoriesAction(){
		$this->View()->setTemplate();
		$node = $this->request()->node;
		if (empty($node)) $node = 1;

		$nodes = array();
		$sql = "
			SELECT c.id, c.description, c.position, c.parent, COUNT(c2.id) as count,
			(
				SELECT categoryID
				FROM s_articles_categories
				WHERE categoryID = c.id
				LIMIT 1
			) as article_count 
			FROM s_categories c
			LEFT JOIN s_categories c2 ON c2.parent=c.id 
			WHERE c.parent=?
			GROUP BY c.id
			ORDER BY c.position, c.description
		";
		$getCategories = Shopware()->Db()->fetchAll($sql,array($node));
		if (!empty($getCategories) && count($getCategories)){
			foreach ($getCategories as $category){
		
				$category["description"] = utf8_encode(html_entity_decode($category["description"]));
				if (!empty($category["count"])){
					$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder');
				}elseif(!empty($_REQUEST["move"])&&empty($category["article_count"])) {
					$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder');
				}else{
					$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder', 'leaf'=>true);
				}
			}
		}
		echo json_encode($nodes);
	}
	
	public function setConfigAction(){
		$this->View()->setTemplate();
		$id = $this->Request()->id;
		
		$banner_active = $this->Request()->banner_active ? $this->Request()->banner_active : "0";
		
		$new_active = $this->Request()->new_active ? $this->Request()->new_active  : "0";
		
		$bought_active = $this->Request()->bought_active ? $this->Request()->bought_active : "0";
		
		$supplier_active = $this->Request()->supplier_active ?  $this->Request()->supplier_active : "0";
		
		
		$checkEdit = Shopware()->Db()->fetchOne("SELECT id FROM s_plugin_recommendations WHERE categoryID = ?",array($id));
		if (!empty($checkEdit)){
			
			// Update
			$update = Shopware()->Db()->query("
			UPDATE s_plugin_recommendations
			SET 
			banner_active = ?,
			new_active = ?,
			bought_active = ?,
			supplier_active = ?
			WHERE categoryID = ?
			",array(
			$banner_active,
			$new_active,
			$bought_active,
			$supplier_active,
			$id
			));
			
		}else {
			// Insert
			$update = Shopware()->Db()->query("
			INSERT INTO s_plugin_recommendations
			(categoryID,banner_active,new_active,bought_active,supplier_active)
			VALUES ( 
			?,
			?,
			?,
			?,
			?
			)
			",array(
			$id,
			$banner_active,
			$new_active,
			$bought_active,
			$supplier_active
			));
		}
		echo json_encode(array("success"=>true));
	}
	
	public function getConfigAction(){
		$this->View()->setTemplate();
		$categoryID = $this->Request()->id;
		$getConfig = Shopware()->Db()->fetchRow("
		SELECT * FROM s_plugin_recommendations WHERE categoryID = ?
		",array($categoryID));
		echo json_encode(array("success"=>true,"data"=>$getConfig));
	}
	

	
	
}