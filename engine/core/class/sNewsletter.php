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
 * Deprecated Shopware Class that handle newsletters
 */
class sNewsletter
{
	var $sSYSTEM;

		public function sCampaignsGetSuggestions($id,$userid=0){

			$id = intval($id);

			$sql = "
			SELECT value, description FROM s_campaigns_containers WHERE type='ctSuggest'
			AND promotionID=$id
			";
			unset($this->sSYSTEM->sMODULES['sArticles']->sCachePromotions);
			unset($this->sSYSTEM->sMODULES['sMarketing']->sBlacklist);

			$getSuggestInfo = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($sql);
			if ($getSuggestInfo["value"] && $getSuggestInfo["description"]){
				// Main-Information
				$sSuggestion["description"] = $getSuggestInfo["description"];
				$sSuggestion["value"] = $getSuggestInfo["value"];
				// Get personalized articles

				$limit = intval($sSuggestion["value"] / 2);
				if ($userid){

					// 1.) Get last viewed articles
					$sql = "
					SELECT DISTINCT articleID FROM s_emarketing_lastarticles WHERE userID=$userid
					ORDER BY time DESC LIMIT $limit
					";

					$selectLast = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
					$countLimit = $limit - count($selectLast);
					$this->sSYSTEM->sCONFIG['sMAXCROSSSIMILAR'] = 1;
					foreach ($selectLast as $lastArticle) $this->sSYSTEM->sMODULES['sMarketing']->sBlacklist[] = $lastArticle["articleID"];
					foreach ($selectLast as $lastArticle){
						$temp = $this->sSYSTEM->sMODULES['sMarketing']->sGetSimilaryShownArticles($lastArticle["articleID"]);
						if ($temp[0]["id"]){
							$selectLastAlsoView[]["articleID"] = $temp[0]["id"];
						}
					}
					if (count($selectLastAlsoView)) $selectLast = array_merge($selectLast,$selectLastAlsoView);
					// 2.) Get last bought articles
					$sql = "
					SELECT DISTINCT articleID FROM s_order_details, s_order WHERE
					s_order.userID=$userid
					AND s_order_details.orderID = s_order.id
					ORDER BY ordertime DESC LIMIT $limit
					";

					$selectLastOrders = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
					foreach ($selectLastOrders as $lastArticle) $this->sSYSTEM->sMODULES['sMarketing']->sBlacklist[] = $lastArticle["articleID"];
					foreach ($selectLastOrders as $lastArticle){
						$temp = $this->sSYSTEM->sMODULES['sMarketing']->sGetAlsoBoughtArticles($lastArticle["articleID"]);
						if ($temp[0]["id"]){
							$selectLastAlsoBought[]["articleID"] = $temp[0]["id"];
						}
					}
					if (count($selectLastAlsoBought)) $selectLast = array_merge($selectLast,$selectLastAlsoBought);

					$blacklist = array();

					$countRecommendations = count($selectLast);
					if ($countRecommendations){
						foreach ($selectLast as $lastArticle){
							$category = $this->sSYSTEM->_GET["sCategory"] ? $this->sSYSTEM->_GET["sCategory"] : 0;
							$temp = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix",$category,$lastArticle["articleID"]);
							if ($temp["articleID"] && empty($blacklist[$temp["articleID"]])){
								//$this->sSYSTEM->sMODULES['sArticles']->sCachePromotions[] = $temp["articleID"];	// Refresh Blacklist
								$finalRecommendations[] = $temp;
								$blacklist[$temp["articleID"]] = $temp["articleID"];
							}
						}
					}
				}

				$leftRecommendations = $sSuggestion["value"] - count($finalRecommendations);

				$randomize = array('new', 'top');
				$category = $this->sSYSTEM->_GET['sCategory'] ? $this->sSYSTEM->_GET['sCategory'] : 0;

				while ($leftRecommendations>0) {
					$article = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById($randomize[array_rand($randomize)], $category, '');
					if(!empty($article)) {
						$leftRecommendations--;
						$this->sSYSTEM->sMODULES['sArticles']->sCachePromotions[] = $article['articleID'];
						$finalRecommendations[] = $article;
					}
				}

				$sSuggestion["data"] = $finalRecommendations;

				return $sSuggestion;

		}
	}

}
