<?php
/**
 * Shopware Backend Functions
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2012, shopware AG
 * @author Stefan Hamann
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Backend
 */
class sFunctions
{
	/**
	 * Config array
	 *
	 * @var array
	 */
	var $sCONFIG;
	
	/**
	 * License data array
	 *
	 * @var array
	 */
	var $sLicenseData;
	
	/**
	 * Subshop array
	 *
	 * @var array
	 */
	var $sLanguages;
	
	/**
	 * Translations array
	 *
	 * @var array
	 */
	var $sTranslations;

	/**
	 * Hook point array
	 *
	 * @var array
	 */
	var $sHookPoints;
	
	/**
	 * Init functions method
	 */
	public function __construct ()
	{
		if(!function_exists('Shopware')) {
            $base = dirname(dirname(dirname(dirname(__FILE__))));

            set_include_path(
                get_include_path() . PATH_SEPARATOR .
                $base . '/engine/Library/' . PATH_SEPARATOR .
                $base . '/engine/' . PATH_SEPARATOR .
                $base
            );

            include_once 'Enlight/Application.php';
            include_once 'Shopware/Application.php';

            $s = new Shopware('production');
		} else {
            $s = Shopware();
        }
		
        $s->Bootstrap()->loadResource('Zend');
        $s->Bootstrap()->loadResource('Db');
        $s->Bootstrap()->loadResource('Plugins');
        
        $s->Plugins()->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);
	}

    public function sInitDb()
    {
        $config = Shopware()->getOption('Db');
        $host = isset($config['host']) ? $config['host'] : 'localhost';
        if(isset($config['socket'])) {
            $host .= ':' . $config['socket'] ;
        } elseif(isset($config['port'])) {
            $host .= ':' . $config['port'] ;
        }
        mysql_pconnect($host, $config['username'], $config['password']);
        mysql_select_db($config['dbname']);
        mysql_query("SET NAMES 'latin1'");
    }

    public function sInitTranslation()
    {
        global $sLang, $mode;
        include 'language_de.php';
        if (file_exists('language_de_custom.php')){
        	include 'language_de_custom.php';
        }
    }
	
	/**
	 * Init translations
	 *
	 * @param unknown_type $key
	 * @param unknown_type $object
	 * @param unknown_type $addAdditionalKey
	 */
	function sInitTranslations($key,$object,$addAdditionalKey=""){
		
		unset($this->sLanguages);
		
		$queryLanguages = mysql_query("
		SELECT * FROM s_core_multilanguage 
		WHERE 
		skipbackend != 1
		ORDER BY id ASC		
		");
			
		while ($language = mysql_fetch_array($queryLanguages)){
			
			$this->sLanguages[] = $language;
			
			$queryTranslation = mysql_query("
			SELECT * FROM s_core_translations
			WHERE
				objecttype = '$object'
			AND
				objectkey = '$key'
			AND
				objectlanguage = '{$language["isocode"]}'
			");
			
			if ($addAdditionalKey){
				$this->sTranslations[$object][$language["isocode"]] = unserialize(@mysql_result($queryTranslation,0,"objectdata"));
			}else {
				$this->sTranslations[$language["isocode"]] = unserialize(@mysql_result($queryTranslation,0,"objectdata"));
			}
		}
	}
	
	/**
	 * Dump licence info
	 *
	 * @param unknown_type $path
	 * @param unknown_type $name
	 * @param unknown_type $info
	 * @param unknown_type $link
	 * @param unknown_type $key
	 * @param unknown_type $title
	 * @return unknown
	 */
	function sDumpLicenceInfo($path = "../../",$name,$info,$link,$key,$title="NICHT LIZENZIERT"){
		$template = "<fieldset class=\"col2_cat2\">
<legend><a class=\"ico exclamation\"></a><span style=\"color:#F00\">$title</span></legend>
<div style=\"padding-left:150px;background: url(".$path."backend/img/default/account/module.jpg) no-repeat 0px 0px;height:150px;\">

<h1 style=\"color:#000; font-size:16px;font-weight:bold;margin:0;padding:0;margin-bottom:5px\">$name</h1>
<p style=\"font-size:11px; color:#6e6e6e;\">
$info
<a href=\"$link\" target=\"_blank\">[mehr]</a>
</p>
<a onclick=\"parent.parent.myExt.sShowBuy('trial','$key');\" style=\"cursor:pointer;background: url(".$path."backend/img/default/account/bt_testen.gif) no-repeat 0px 0px;height:39px;width:238px;display:block;float:left;margin-right:10px;\"></a>
<a onclick=\"parent.parent.myExt.sShowBuy('','$key');\" style=\"cursor:pointer;background: url(".$path."backend/img/default/account/bt_mieten.gif) no-repeat 0px 0px;height:39px;width:189px;display:block; float:left;\"></a>
<div style=\"clear:both;\"></div>
</div>
</fieldset>";
		return $template;
	}
	
	/**
	 * Returns unadjust s_core_config / s_core_config_text data
	 *
	 * @return array of unadjust data || false
	 * @author Dennis Scharfenberg
	 * @version 1.0 || 2009-04-27
	 */
	function getUnadjustedSettings(){
		
		$aCoreConfig = array();
		
		//check s_core_config entries
		$sql = "SELECT 
					def.*,
					cnf.description
				FROM `s_core_unadjusted` AS def
				LEFT JOIN `s_core_config` AS cnf
					ON def.name = cnf.name
				WHERE
					TRIM(def.value) = TRIM(cnf.value)
					AND def.table = 's_core_config'";
		$qu = mysql_query($sql);
		
		if(mysql_num_rows($qu) != 0){
			while ($fetch = mysql_fetch_assoc($qu)) {
				$aCoreConfig[] = $fetch;
			}
		}else{
			return false;
		}
		return array("s_core_config" => $aCoreConfig);
		
	}
	
	/**
	 * Build translation
	 *
	 * @param unknown_type $field
	 * @param unknown_type $key
	 * @param unknown_type $id
	 * @param unknown_type $object
	 * @param unknown_type $secondkey
	 * @return unknown
	 */
	function sBuildTranslation($field,$key,$id,$object,$secondkey=0){
		if (!$this->sLanguages || !@count($this->sLanguages)) return;
        $element = '';
		foreach ($this->sLanguages as $language){
			if ($secondkey){
				
				if ($this->sTranslations[$object][$language["isocode"]][$secondkey][$field]){
					$opacity = "opacity:1";
				}else {
					$opacity = "opacity:0.5";
				}
			}else {
				if ($this->sTranslations[$language["isocode"]][$field]){
					$opacity = "opacity:1";
				}else {
					$opacity = "opacity:0.5";
				}
			}
			
			$style = "style=\"margin-left:10px;$opacity;cursor:pointer\"";
			$onclick = "onclick=\"sTranslations('$field','$key','$id','$object','{$language["isocode"]}','$secondkey')\"";
			$element .= "<img src=\"".sConfigPath."engine/backend/img/default/icons/flags/{$language["flagbackend"]}\" $style $onclick>";	
		}
		return $element;	
	}
	
	/**
	 * Check license method
	 *
	 * @param unknown_type $host
	 * @param unknown_type $module
	 * @param unknown_type $key
	 * @return unknown
	 */
	function sCheckLicense($host=null, $module=null, $key)
	{
		return true;
	}

	/**
	 * Init license data
	 */
	function sGetLicenseData(){
		$fetchConfiguration = mysql_query("SELECT * FROM s_core_licences");
		while ($configRow = mysql_fetch_array($fetchConfiguration)){
			$this->sLicenseData[$configRow["module"]] = $configRow["hash"];
		}
	}
	
	/**
	 * Calculating price
	 *
	 * @param unknown_type $price
	 * @param unknown_type $tax
	 * @return unknown
	 */
	function sCalculatingPrice($price,$tax){

		if ($this->sSYSTEM->sUSERGROUP=="H"){
			$price = $this->sFormatPrice($price);
		}else {
			$price = $this->sFormatPrice(round($price*(100+$tax)/100,2));
		}
		return $price;

	}
	
	/**
	 * Format price method
	 *
	 * @param unknown_type $price
	 * @return unknown
	 */
	function sFormatPrice ($price){
			$price = str_replace(".",",",$price);	// Replaces points with commas
			$commaPos = strpos($price,",");
			if ($commaPos){
			
				$part = substr($price,$commaPos+1,strlen($price)-$commaPos);
				switch (strlen($part)){
					case 1:
					$price .= "0";
					break;
					case 2:
					break;
				}
			}
			else {
				if (!$price){
					$price = "0";
				}else {
					# Wir haben keins... :-(
					$price .= ",00";
				}
			}
	
			return $price;
		}
	
	/**
	 * Init config method
	 */
	function sInitConfig(){
		$this->sCONFIG = Shopware()->Config();
	}
	
	/**
	 * Call hock point metod
	 *
	 * @param unknown_type $hook
	 * @return unknown
	 */
	function sCallHookPoint($hook){
		
		if (!empty($this->sHookPoints[$hook])){
			$compile = "";
			foreach ($this->sHookPoints[$hook] as $hookCode){
				$compile .= $hookCode["code"];
			}
			return $compile;
		}
	}
	
	/**
	 * Load hook points
	 *
	 */
	function sLoadHookPoints(){
		/*
		Table s_core_hookpoints
		1. id
		2. class / function - sArticles_sGetAllArticles
		3. name
		4. modified (date,time)
		5. code (Code to execute)
		*/
		$loadHookPoints = mysql_query("
		SELECT * FROM s_core_hookpoints ORDER BY module ASC,position ASC
		");
		if (!empty($loadHookPoints)){
			while ($hookValue = mysql_fetch_assoc($loadHookPoints)){
				$this->sHookPoints[$hookValue["name"]][] = $hookValue;
			}
		}
		
	}
	
	/**
	 * Returns currency char
	 *
	 * @return unknown
	 */
	function sGetCurrencyChar(){
		$getCurrency = mysql_query("SELECT templatechar FROM s_core_currencies WHERE standard=1");
		return mysql_result($getCurrency,0,"templatechar");
	}
	
	/**
	 * Delete article by id
	 *
	 * @param unknown_type $id
	 */
	function sDeleteArticle($id){
			
			//Liveshopping l�schen
			$getLiveshoppings = mysql_query("
				SELECT *
				FROM `s_articles_live`
				WHERE `articleID` = {$_GET["delete"]}
			");
			if(mysql_num_rows($getLiveshoppings) != 0){
				while ($liveArt = mysql_fetch_assoc($getLiveshoppings)) {
					$liveID = $liveArt['id'];
					
					$deleteLive = mysql_query("DELETE FROM `s_articles_live_prices` WHERE `liveshoppingID` = {$liveID}");
					if (!$deleteLive){
						echo mysql_error()."<br />Delete article live#1 error";
					}
					
					$deleteLive = mysql_query("DELETE FROM `s_articles_live_shoprelations` WHERE `liveshoppingID` = {$liveID}");
					if (!$deleteLive){
						echo mysql_error()."<br />Delete article live#2 error";
					}
					
					$deleteLive = mysql_query("DELETE FROM `s_articles_live_stint` WHERE `liveshoppingID` = {$liveID}");
					if (!$deleteLive){
						echo mysql_error()."<br />Delete article live#3 error";
					}
					
					$deleteLive = mysql_query("DELETE FROM `s_articles_live` WHERE `id` = {$liveID}");
					if (!$deleteLive){
						echo mysql_error()."<br />Delete article live#4 error";
					}
				}
			}
			
			//Bundles l�schen
			$getBundles = mysql_query("
				SELECT *
				FROM `s_articles_bundles`
				WHERE `articleID` = {$_GET["delete"]}
			");
			if(mysql_num_rows($getBundles) != 0){
				while ($bundle = mysql_fetch_assoc($getBundles)) {
					$bundleID = $bundle['id'];
					
					$deleteBundle = mysql_query("DELETE FROM `s_articles_bundles_articles` WHERE `bundleID` = {$bundleID}");
					if (!$deleteBundle){
						echo mysql_error()."<br />Delete article bundle#1 error";
					}
					
					$deleteBundle = mysql_query("DELETE FROM `s_articles_bundles_prices` WHERE `bundleID` = {$bundleID}");
					if (!$deleteBundle){
						echo mysql_error()."<br />Delete article bundle#2 error";
					}
					
					$deleteBundle = mysql_query("DELETE FROM `s_articles_bundles_stint` WHERE `bundleID` = {$bundleID}");
					if (!$deleteBundle){
						echo mysql_error()."<br />Delete article bundle#3 error";
					}
					
					$deleteBundle = mysql_query("DELETE FROM `s_articles_bundles` WHERE `id` = {$bundleID}");
					if (!$deleteBundle){
						echo mysql_error()."<br />Delete article bundle#4 error";
					}
				}
			}
		
			// Alle Bestellnummern auslesen
			$getOrdernumbers = mysql_query("
			SELECT ordernumber FROM s_articles_details 
				WHERE articleID = {$_GET["delete"]}
			");
			while ($ordernumber = mysql_fetch_assoc($getOrdernumbers)){
				// Delete promotions / Einkaufswelten
				$deletePromotions = mysql_query("
				DELETE FROM s_emarketing_promotions 
					WHERE ordernumber = '{$ordernumber["ordernumber"]}'
				");
				// Delete promotion / Aktionen
				$deletePromotions = mysql_query("
				DELETE FROM s_emarketing_promotion_articles 
					WHERE articleordernumber = '{$ordernumber["ordernumber"]}'
				");
				
				// Last-Articles
				$deletePromotions = mysql_query("
				DELETE FROM s_emarketing_lastarticles 
					WHERE articleID = {$_GET["delete"]}
				");
				// Delete relationsships
				$deleteArticle = mysql_query("
				DELETE FROM s_articles_relationships WHERE relatedarticle = '{$ordernumber["ordernumber"]}'
				");
				
				$deleteArticle = mysql_query("
				DELETE FROM s_articles_similar WHERE relatedarticle = '{$ordernumber["ordernumber"]}'
				");
			}
			// Maindata
			$deleteArticle = mysql_query("
			DELETE FROM s_articles WHERE id={$_GET["delete"]}
			");
			
			if (!$deleteArticle){
				echo mysql_error()."<br />Delete article#1 error";
			}
			// --
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_attributes WHERE articleID={$_GET["delete"]}
			");
			
			if (!$deleteArticle){
				echo mysql_error()."<br />Delete article#2 error";
			}
			// --
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_categories WHERE articleID={$_GET["delete"]}
			");
			
			if (!$deleteArticle){
				echo mysql_error()."<br />Delete article#3 error";
			}
			// --
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_details WHERE articleID={$_GET["delete"]}
			");
			
			if (!$deleteArticle){
				echo mysql_error()."<br />Delete article#4 error";
			}
			// --
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_esd WHERE articleID={$_GET["delete"]}
			");
			
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_prices WHERE articleID={$_GET["delete"]}
			");
			
			if (!$deleteArticle){
				echo mysql_error()."<br />Delete article#5 error";
			}
			
			// Article-Images
			// delete all related pictures physical
			$deleteArticle = mysql_query("
			SELECT img FROM s_articles_img WHERE articleID={$_GET["delete"]}
			");
			
			while ($img=mysql_fetch_array($deleteArticle)){
				$this->rfr("../../../images/articles/",$img["img"]."*");
			}
			
			
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_img WHERE articleID={$_GET["delete"]}
			");
			
			if (!$deleteArticle){
				echo mysql_error()."<br />Delete article#6 error";
			}
			// --
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_information WHERE articleID={$_GET["delete"]}
			");
			
			if (!$deleteArticle){
				echo mysql_error()."<br />Delete article#7 error";
			}
			// --
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_relationships WHERE articleID={$_GET["delete"]}
			");
			
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_similar WHERE articleID={$_GET["delete"]}
			");
			
			$deleteArticle = mysql_query("
			DELETE FROM s_articles_vote WHERE articleID={$_GET["delete"]}
			");
			
			if (!$deleteArticle){
				echo mysql_error()."<br />Delete article#8 error";
			}
			// --
			
			// Delete Configurator Articles
			$delete = mysql_query("
			DELETE FROM s_articles_groups WHERE articleID = {$_GET["delete"]}
			");
			
			$delete = mysql_query("
			DELETE FROM s_articles_groups_accessories WHERE articleID = {$_GET["delete"]}
			");
			
			$delete = mysql_query("
			DELETE FROM s_articles_groups_accessories_option WHERE articleID = {$_GET["delete"]}
			");
			
			$delete = mysql_query("
			DELETE FROM s_articles_groups_prices WHERE articleID = {$_GET["delete"]}
			");
				
			$delete = mysql_query("
			DELETE FROM s_articles_groups_value WHERE articleID = {$_GET["delete"]}
			");
			
			$delete = mysql_query("
			DELETE FROM s_order_comparisons WHERE articleID = {$_GET["delete"]}
			");
			
			$delete = mysql_query("
			DELETE FROM s_filter_values WHERE articleID = {$_GET["delete"]}
			");
			
			$delete = mysql_query("
			DELETE FROM s_core_translations WHERE objectkey = {$_GET["delete"]} AND ( objecttype='article' OR objecttype='configuratorgroup' OR objecttype='configuratoroption' OR objecttype='variant')
			");
			
			
	}

	/**
	 * Delete partial cache
	 *
	 * @param unknown_type $typ
	 * @param unknown_type $id
	 */
	function sDeletePartialCache($typ,$id)
	{
		//Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($typ.'_'.$id));
	}
	
	/**
	 * Delete files method
	 *
	 * @param unknown_type $path
	 * @param unknown_type $match
	 * @return unknown
	 */
	function rfr($path,$match){
	  
	   $files = glob($path.$match);

	   foreach($files as $file){
	         unlink($file);
	   }
	   return "";
	}
	
	/**
	 * Check referer method
	 *
	 */
	function sCheckReferer()
	{
		$config = Shopware()->getOption('backend');
		if(empty($config['refererCheck'])) {
			return;
		}
		if(!empty($_SERVER['HTTP_REFERER'])
		  && !preg_match('#^https?://'.preg_quote($_SERVER['HTTP_HOST']).'/[^?&]*(?:engine|backend)#', $_SERVER['HTTP_REFERER'])) {
			session_start();
			session_regenerate_id(true);
			session_write_close();
		}
	}
}
