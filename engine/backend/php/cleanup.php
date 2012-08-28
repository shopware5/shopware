<?php
class sCleaner{
	var $reporting;
	var $sRealDelete = false;
	var $sRelatedTables = array("s_articles_attributes","s_articles_details","s_articles_prices");
	
	var $sFullTableScan = array(
	"s_articles_attributes"=>array("key"=>"articleID"),
	"s_articles_categories"=>array("key"=>"articleID"),
	"s_articles_details"=>array("key"=>"articleID"),
	"s_articles_downloads"=>array("key"=>"articleID"),
	"s_articles_esd"=>array("key"=>"articleID"),
	"s_articles_groups"=>array("key"=>"articleID"),
	"s_articles_groups_accessories"=>array("key"=>"articleID"),
	"s_articles_groups_accessories_option"=>array("key"=>"articleID"),
	"s_articles_groups_option"=>array("key"=>"articleID"),
	"s_articles_groups_prices"=>array("key"=>"articleID"),
	"s_articles_groups_settings"=>array("key"=>"articleID"),
	"s_articles_groups_value"=>array("key"=>"articleID"),
	"s_articles_img"=>array("key"=>"articleID"),
	"s_articles_information"=>array("key"=>"articleID"),
	"s_articles_prices"=>array("key"=>"articleID"),
	"s_articles_relationships"=>array("key"=>"articleID"),
	"s_articles_similar"=>array("key"=>"articleID"),
	"s_articles_translations"=>array("key"=>"articleID"),
	"s_articles_vote"=>array("key"=>"articleID"),
	"s_emarketing_lastarticles"=>array("key"=>"articleID"),
	"s_emarketing_promotion_articles"=>array("key"=>"articleordernumber"),
	"s_export_articles"=>array("key"=>"articleID"),
	"s_filter_values"=>array("key"=>"articleID"),
	"s_order_comparisons"=>array("key"=>"articleID")
	);
	
	var $articeList;
	var $articleListReverse;
	
	var $sCore;
	
	function sCleaner($core){
		$this->sCore = $core;
	}
	
	function sCheckRelations(){
		$getActiveArticles = mysql_query("
		SELECT a.id, IF(sas.id,supplierID,0) AS supplierAvailable,a.mode, supplierID, a.name, ordernumber 
		FROM s_articles a 
		LEFT JOIN s_articles_supplier AS sas ON a.supplierID = sas.id
		LEFT JOIN s_articles_details ad ON a.id = ad.articleID AND ad.kind=1
		ORDER BY id ASC
		");
		
		while ($article=mysql_fetch_assoc($getActiveArticles)){
			// Check if supplier exists 
			if (empty($article["supplierAvailable"])){
				$this->reporting[] =  "Article: {$article["name"]} # Supplier {$article["supplierID"]} is missing";
				// Reset supplier
				$this->sResetSupplier($article["id"]);
			}
			
			// Add article to whitelist
			if (!empty($article["ordernumber"])) $this->articeList[$article["id"]] = $article["ordernumber"];
			
			foreach ($this->sRelatedTables as $relatedTable){
				$sql = "
				SELECT id FROM $relatedTable WHERE articleID = {$article["id"]} LIMIT 1
				";
				$count = mysql_num_rows(mysql_query($sql));
				if (empty($count)){
					if (empty($article["mode"])){
					$this->reporting[] =  "Article: {$article["name"]} #{$article["ordernumber"]}#ID:{$article["id"]}#2 Entry in table $relatedTable is missing";
					// Delete 
					}
					if ($this->sRealDelete && empty($article["mode"])){
						$this->sDeleteArticle($article["id"]);
						break;
					}
				}
			}
		}
		
		$this->articleListReverse = array_flip($this->articeList);
		
		// Full scan related tables
		foreach ($this->sFullTableScan as $table => $value){
			
			$articles = $this->sGetArticles($table,$value);
			$this->reporting[] = "Scanning $table (".count($articles)." rows)####";
			
			// Remove empty rows
			if (count($articles)){
				foreach ($articles as $key => $article){
					
					$this->reporting[] = " - DELETE FROM $table WHERE {$value["key"]}='$key' ####";
					if ($this->sRealDelete){
						mysql_query("DELETE FROM $table WHERE {$value["key"]}='$key'");
					}
				}
			}
		}
		
	}
	
	function sGetArticles($table,$value){
		if ($value["key"]!="articleID"){
			$sql = "
			SELECT DISTINCT {$value["key"]} AS ordernumber FROM $table
			";
			$result = mysql_query($sql);
			if (!$result){
				die(mysql_error().$sql);
			}else {
				while ($article = mysql_fetch_assoc($result)){
					if (empty($this->articleListReverse[$article["ordernumber"]]) && !empty($article["ordernumber"])){
						$articles[$article["ordernumber"]] = "";
					}
				}
			}
			
			return $articles;
		}else {
			
			$sql = "
			SELECT DISTINCT articleID FROM $table
			";
		
			$result = mysql_query($sql);
			if (!$result){
				die(mysql_error().$sql);
			}else {
				while ($article = mysql_fetch_assoc($result)){
					if (empty($this->articeList[$article["articleID"]])){
						$articles[$article["articleID"]] = "";
					}
				}
			}
			return $articles;
		}
	}
	
	function sDeleteArticle($id){
		if (is_object($this->sCore) && $this->sRealDelete){
			$_GET["delete"] = $id;
			$this->sCore->sDeleteArticle($id);
			$this->reporting[] = "## Article $id was removed##";
		}
	}
	
	function sCheckImages(){
		$getImages = mysql_query("
		SELECT img FROM s_articles_img
		");
		while ($image = mysql_fetch_assoc($getImages)){
			$imagesArray[$image["img"]] = "x";
		}
		
		$this->reporting[] = count($imagesArray)." Images found in database";
		
		$path = "../../../images/articles";
		$handle = opendir($path);
	     while (false !== ($file = readdir($handle))) {
	      	//162_908d8997a34a0d7e5d434c89232aa7ff_3.jpg
	     	preg_match("/(.*?)(_[0-9])?\.(jpg|png|gif)/",$file,$result);
			$filename = $result[1];
			//$filename = preg_replace("/(.*)_(.*)/","\\1",$filename);
			if (empty($filename)) continue;
			if (!empty($imagesArray[$filename])){
				//echo "$filename is valid\n";
			}else {
				$this->reporting[] = "$filename not need\n";
				// Deleting image
				if (empty($this->sRealDelete)) continue;
				$filesToDelete = glob($path."/$filename*");
			    foreach($filesToDelete as $deleteFile){
					$this->reporting[] = "Delete $deleteFile\n";
					unlink($deleteFile);
			    }
			}
	     }
	}
	
	function sResetSupplier($id){
		if (!empty($id)){
			$querySupplier = mysql_query("
			SELECT id FROM s_articles_supplier WHERE name='Keine Angabe'
			");
			$result = @mysql_result($querySupplier,0,"id");
			if (empty($result)){
				// Insert temporary supplier
				$insertSupplier = mysql_query("
				INSERT INTO s_articles_supplier (name)
				VALUES ('Keine Angabe')
				");
				$result = mysql_insert_id();
			}
			$updateArticle = mysql_query("
			UPDATE s_articles SET supplierID = $result WHERE id=$id
			");
		}
	}
	
	function sClear(){
		$this->reporting[] = "Clean Tables####";
		$this->reporting[] = "Clean 's_emarketing_lastarticles'...";
		mysql_query("DELETE FROM s_emarketing_lastarticles WHERE UNIX_TIMESTAMP(time)<=(UNIX_TIMESTAMP(now())-1209600)");
		$this->reporting[] = "Clean 's_core_log'...";
		mysql_query("DELETE FROM s_core_log WHERE UNIX_TIMESTAMP(datum)<=(UNIX_TIMESTAMP(now())-1209600)");
		$this->reporting[] = "Clean 'adodb_logsql'...";
		mysql_query("DELETE FROM adodb_logsql WHERE UNIX_TIMESTAMP(created)<=(UNIX_TIMESTAMP(now())-1209600)");
	}
	
	function sOptimize(){
		$this->reporting[] = "OPTIMIZE Tables####";
		$query = mysql_query("
		SHOW tables
		");
		while ($table = mysql_fetch_row($query)){
			$table = $table[0];
			if (!empty($table)){
				$this->reporting[] = "OPTIMIZE / REPAIR $table...";
				mysql_query("REPAIR TABLE `$table`");
				mysql_query("OPTIMIZE TABLE `$table`");
			}
		}
	}
	
	function sCheckCategories(){
		$this->reporting[] = "Check Categories####";
		$getCategories = mysql_query("
		SELECT s_categories.id,s_categories.parent,s_categories.description,sc.id AS parentCheck FROM s_categories
		LEFT JOIN s_categories AS sc ON sc.id = s_categories.parent
		");
		while ($category = mysql_fetch_assoc($getCategories)){
			if (empty($category["parentCheck"]) && $category["parent"]!=1){
				$this->reporting[] = "Category {$category["id"]} ({$category["description"]}) damaged, missing {$category["parent"]}...";
				if ($this->sRealDelete){
					mysql_query("
					DELETE FROM s_categories WHERE id = {$category["id"]}
					");
					mysql_query("
					DELETE FROM s_articles_categories WHERE categoryID = {$category["id"]}
					");
					mysql_query("
					DELETE FROM s_articles_categories WHERE categoryparentID = {$category["id"]}
					");	
				}
			}
		}
	}
	
	function sCheckPrices(){
		$this->reporting[] = "Check Prices####";
		$getPrices = mysql_query("
		SELECT ap.id,ad.id AS detailsCheck  FROM s_articles_prices AS ap
		LEFT JOIN s_articles_details AS ad ON ad.id = ap.articledetailsID
		LEFT JOIN s_articles AS a ON a.id = ad.articleID AND a.mode = 0
		");
		while ($price = mysql_fetch_assoc($getPrices)){
			if (empty($price["detailsCheck"])){
				$this->reporting[] = "Price-Row {$price["id"]} missing relations...";
				if ($this->sRealDelete){
				mysql_query("
				DELETE FROM s_articles_prices WHERE id = {$price["id"]}
				");	
				}
			}
		}
		
		// Check Configurator Prices
		$getPrices = mysql_query("
		SELECT ap.id,av.valueID FROM s_articles_groups_prices AS ap
		LEFT JOIN s_articles_groups_value AS av ON av.articleID = ap.articleID AND av.valueID = ap.valueID
		WHERE ap.valueID != 0
		");
		while ($price = mysql_fetch_assoc($getPrices)){
			if (empty($price["valueID"])){
				$this->reporting[] = "Configurator-Row {$price["id"]} missing relations...";
				if ($this->sRealDelete){
					mysql_query("
					DELETE FROM s_articles_groups_prices WHERE id = {$price["id"]}
					");	
				}
			}
		}
	}
	
	function sCheckTranslations(){
		$this->reporting[] = "Check Translations####";
		$getPrices = mysql_query("
		SELECT ap.id,a.id AS articleCheck  FROM s_articles_translations AS ap
		LEFT JOIN s_articles AS a ON a.id = ap.articleID AND a.mode = 0
		");
		
		while ($translation = mysql_fetch_assoc($getPrices)){
			if (empty($translation["articleCheck"])){
				$this->reporting[] = "Translation-Row {$translation["id"]} missing relations...";
				if ($this->sRealDelete){
					mysql_query("
					DELETE FROM s_articles_translations WHERE id = {$translation["id"]}
					");	
				}
			}
		}
	}
}
?>