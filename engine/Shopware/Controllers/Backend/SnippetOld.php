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
 * @subpackage SnippetOld
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     Marcel Schmäing
 * @author     Heiner Lohaus
 */

/**
 * Shopware Snippet Controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_SnippetOld extends Enlight_Controller_Action
{
	/**
	 * Pre dispatch action method
	 */
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('index', 'skeleton'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}

	/**
	 * Skeleton action method
	 */
	public function skeletonAction ()
	{

	}

	/**
	 * Index action method
	 */
	public function indexAction()
	{
		$sql = "DELETE FROM `s_core_snippets` WHERE `namespace` LIKE '/%' OR `namespace` LIKE 'templates/%';";
		Shopware()->Db()->exec($sql);

		$sql = "UPDATE `s_core_snippets` SET `shopID` = 1 WHERE `shopID` = 0;";
		Shopware()->Db()->exec($sql);

		$result = $this->getAllLocalesAndShopIds();
		$locales = array();
		foreach ($result as $row) {
			$tempArray = array();
			$tempArray["name"] = $row["locale"]."-".$row["id"];
			$tempArray["label"] = $row["name"]." (".$tempArray["name"].")";
			$locales[] = $tempArray;
		}
		$this->View()->translations = $locales;
	}

	/**
	 * View action method
	 */
	public function viewAction()
	{
		$this->forward('index');
	}

	/**
	 * Get snippet action
	 */
	public function getSnippetAction()
	{
		$limit = (int)$this->Request()->limit ? $this->Request()->limit : 25;
		$order = $this->Request()->sort ? $this->Request()->sort : "namespace,name,locale,shopID";
		$dir = $this->Request()->dir ? $this->Request()->dir : "ASC";
		$start = (int)$this->Request()->start ? $this->Request()->start : 0;
		$nameSpace = $this->Request()->nameSpace;
		$nameSpace = ($nameSpace == "_") ? "" :$nameSpace;

		if(!empty($this->Request()->locale)) {
			$tempLocale = $this->Request()->locale;
			$localeAndShopID = explode("-",$tempLocale);
			$localQuery = "AND l.locale = ".Shopware()->Db()->quote($localeAndShopID[1])."AND sn.shopID = ".Shopware()->Db()->quote($localeAndShopID[0]);
		}
		$showEmpty = ($this->Request()->showEmpty) ? " AND value = ''" : "";
		//make regex groups out of the namespace
		preg_match("/(.*)_([0-9]*)/", $nameSpace, $result);
		if (!empty($nameSpace) && $nameSpace !="_") {
			$nameSpace = "AND namespace LIKE ".Shopware()->Db()->quote($result[1]."%");
		}
		if ($this->Request()->search) {
			$search = $this->Request()->search;
			$search = $this->getFormatSnippetForSave($search);
			if (strlen($search)>1){
				$search = "%".$search."%";
			}else {
				$search = $search."%";
			}
			$htmlSearch = htmlentities($search);
			$searchSQL = "
			AND
			(
				namespace LIKE '$search'
			OR
				locale LIKE '$search'
			OR
				name LIKE '$search'
			OR
				shopID LIKE '$search'
			OR
				value LIKE '$search'
			OR
				namespace LIKE '$htmlSearch'
			OR
				localeID LIKE '$htmlSearch'
			OR
				name LIKE '$htmlSearch'
			OR
				shopID LIKE '$htmlSearch'
			OR
				value LIKE '$htmlSearch'
			)
			";
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS * ,sn.id as id, l.locale AS locale
			FROM s_core_snippets AS sn
			LEFT JOIN s_core_locales AS l ON ( sn.localeID = l.id )
			WHERE 1
			$nameSpace
			$showEmpty
			$localQuery
			$searchSQL
			ORDER BY $order $dir
			LIMIT $start, $limit";
		$getSnippets = Shopware()->Db()->fetchAll($sql);
		foreach ($getSnippets as &$snippet) {
			$snippet["value"] = $this->getFormatSnippetForGrid($snippet["value"]);
			$snippet["name"] = $this->getFormatSnippetForGrid($snippet["name"]);
		}

		$count = Shopware()->Db()->fetchOne("SELECT FOUND_ROWS()");

		echo json_encode(array("count"=>$count, "data"=>$getSnippets));
	}

	/**
	 * Delete snippet action
	 */
	public function deleteSnippetsAction()
	{
		$snippetIds = explode(',', $this->Request()->snippetIds);
		$snippetIds = Shopware()->Db()->quote($snippetIds);
		$sql = "DELETE FROM s_core_snippets WHERE id IN ($snippetIds)";
		$stuff = Shopware()->Db()->exec($sql);
	}

	/**
	 * Get language action
	 */
	public function getLanguageShopAction()
	{
		$locales = $this->getAllLocalesAndShopIds();
		$data[] = array("id"=>0,"locale"=>"Alle anzeigen"); //Default val todo Snippet
		foreach ($locales as $locale) {
			$locale["id"] = $locale["id"].'-'.$locale["locale"];
			$locale["locale"] = $locale["name"]." (".$locale["id"].")";
			$locale["locale"] = utf8_encode($locale["locale"]);
			$data[] = $locale;
		}
		echo Zend_Json::encode(array("locales"=>$data));
	}

	/**
	 * Get locales action
	 */
	public function getLocalesAction()
	{
		$locales = Shopware()->Db()->fetchAll("
			SELECT DISTINCT l.locale as locale
			FROM s_core_multilanguage AS ml, s_core_locales AS l
			WHERE ml.locale = l.id
		");
		echo Zend_Json::encode(array("locales"=>$locales));
	}

	/**
	 * Get shop ids action
	 */
	public function getShopIdsAction()
	{
		$ids = Shopware()->Db()->fetchAll("
			SELECT id as shopID FROM s_core_multilanguage
		");
		echo Zend_Json::encode(array("shopIDs"=>$ids));
	}

	/**
	 * Shange snippets action
	 */
	public function changeSnippetsAction()
	{
		$snippetIds = explode(',', $this->Request()->snippetIds);
		$snippetIds = Shopware()->Db()->quote($snippetIds);
		$nameSpace = Shopware()->Db()->quote($this->Request()->nameSpace);
		$sql = "UPDATE s_core_snippets SET namespace = $nameSpace WHERE id in ($snippetIds)";
		Shopware()->Db()->exec($sql);
	}

	/**
	 * Get whole snippet data for form action
	 */
	public function loadSnippetFormAction()
	{
		$snippetIds = $this->Request()->snippetIds;
		$sql = "SELECT sn.namespace, sn.name, sn.value AS value, l.locale AS locale, ml.id AS id
			FROM s_core_snippets AS sn
			LEFT JOIN s_core_locales AS l ON ( sn.localeID = l.id )
			LEFT JOIN s_core_multilanguage AS ml ON ( sn.shopID = ml.id )
			WHERE sn.namespace = ?
			AND sn.name = ?";
		$getSnippets = Shopware()->Db()->fetchAll($sql,array($this->Request()->nameSpace, $this->Request()->name));

		$namespace = $getSnippets[0]["namespace"];
		$name = $getSnippets[0]["name"];
		$data["name"] = $name;
		$data["namespace"] = $namespace;
		$data["oldName"] = $name;
		$data["oldNamespace"] = $namespace;
		foreach ($getSnippets as $snippet) {
			$data[$snippet["locale"]."-".$snippet["id"]] = $this->getFormatSnippetForGrid(($snippet["value"]));
		}
		echo json_encode(array("snippet"=>array($data)));
	}

	/**
	 * Submit snippet action
	 */
	public function submitSnippetAction()
	{
		$req = (array)$this->Request()->getParams();
		$oldNamespace = $req["oldNamespace"];
		$oldName = $req["oldName"];
		$localesAndShopIds = $this->getAllLocalesAndShopIds();
		foreach ($localesAndShopIds as $row) {
			$tempArray["both"] = $row["locale"]."-".$row["id"];
			$tempArray["locale"] = $row["locale"];
			$tempArray["shopID"] = $row["id"];
			$localesShopIds[] = $tempArray;
		}
		foreach ($localesShopIds as $localeAndShopID) {
			//check if locale was send
			if(!empty($req[$localeAndShopID["both"]])) {
				//getLocationID = $locale
				$localeID = $this->getLocaleId($localeAndShopID["locale"]);

				$sql = "SELECT id FROM s_core_snippets WHERE namespace = ? and name = ? and localeID = ? AND shopID = ?";
				$getSnippetID = Shopware()->Db()->fetchOne($sql,array($oldNamespace, $oldName, $localeID, $localeAndShopID["shopID"]));

				if(!empty($getSnippetID)) {
					//update
					$sql = "UPDATE s_core_snippets SET namespace = ?, name = ?, value = ?, updated = now() WHERE id = ?";
					Shopware()->Db()->query($sql,
						array(
							$this->getFormatSnippetForSave($this->Request()->namespace),
							$this->getFormatSnippetForSave($this->Request()->name),
							$this->getFormatSnippetForSave($req[$localeAndShopID["both"]]),
							$getSnippetID
						));
				}
				else {

					//if no translation is available insert one
					//Add a new Snippet
					$sql ="INSERT INTO `s_core_snippets` (
						`namespace` ,
						`name` ,
						`localeID`,
						`shopID`,
						`value` ,
						`created` ,
						`updated`
					)
					VALUES (
						?,?,?,?,?,NOW(),NOW()
					)
					";
					Shopware()->Db()->query($sql,
					array(
						$this->getFormatSnippetForSave($this->Request()->namespace),
						$this->getFormatSnippetForSave($this->Request()->name),
						$localeID,
						$localeAndShopID["shopID"],
						$this->getFormatSnippetForSave($req[$localeAndShopID["both"]])
					));
				}
			}
		}
	}

	/**
	 * Change snippet action
	 */
	public function changeSnippetAction()
	{
		//getLocationID = $locale
		$localeID = $this->getLocaleId($this->Request()->locale);

		if(!$this->Request()->id) {
			//Add new Snippet
			$sql ="INSERT INTO `s_core_snippets` (
			`namespace` ,
			`name` ,
			`localeID` ,
			`shopID` ,
			`value` ,
			`created` ,
			`updated`
			)
			VALUES (
			?,?,?,?,?,now(),now()
			)";
			Shopware()->Db()->query($sql,
				array(
					$this->getFormatSnippetForSave($this->Request()->namespace),
					$this->getFormatSnippetForSave($this->Request()->name),
					$localeID,
					$this->Request()->shopID,
					$this->getFormatSnippetForSave($this->Request()->value)
				));
			$data[] = Shopware()->Db()->lastInsertId();
			$data[] = Shopware()->Db()->fetchOne("SELECT created FROM s_core_snippets WHERE id =?",array(Shopware()->Db()->lastInsertId()));
			echo json_encode($data);
		}
		else {
			//update snippet
			$sql = "UPDATE s_core_snippets SET namespace = ?, name = ?, localeID = ?, shopID = ?, value = ?, updated = now() WHERE id = ?";
			Shopware()->Db()->query($sql,
				array(
					$this->getFormatSnippetForSave($this->Request()->namespace),
					$this->getFormatSnippetForSave($this->Request()->name),
					$localeID,
					$this->getFormatSnippetForSave($this->Request()->shopID),
					$this->getFormatSnippetForSave($this->Request()->value),
					$this->Request()->id
				));
		}
	}

	/**
	 * Dublicate the existing snippets with new namespaces action
	 */
	public function dublicateSnippetsAction()
	{
		$snippetIds = explode(',', $this->Request()->snippetIds);
		$snippetIds = Shopware()->Db()->quote($snippetIds);
		$nameSpace = $this->Request()->nameSpace;
		$sql = "SELECT * FROM s_core_snippets WHERE id in ($snippetIds)";
		$stuff = Shopware()->Db()->fetchAll($sql);
		foreach ($stuff as $snippet) {
			$sql ="
				INSERT INTO `s_core_snippets` (
					`namespace` ,
					`name` ,
					`localeID` ,
					`shopID` ,
					`value` ,
					`created` ,
					`updated`
				)
				VALUES (
					?,?,?,?,?,?,?
				)
			";
			Shopware()->Db()->query($sql,array(
				$nameSpace,
				$snippet["name"],
				$snippet["localeID"],
				$snippet["shopID"],
				$snippet["value"],
				$snippet["created"],
				$snippet["updated"]
			));
		}
	}

	/**
	 * Get namespace action
	 */
	public function getNSAction()
	{
		$node = $this->Request()->node;

		if ($node!="_"){
			$result = array();
			preg_match("/(.*)_([0-9]*)/",$node,$result);
			$node = $result[1];
			$layer = $result[2];
			$node = Shopware()->Db()->quote($node);
			$where = "WHERE SUBSTRING_INDEX(namespace, '/', $layer) = $node";
			$layer += 1;
		}else {
			$where = "";
			$layer = "1";
		}

		$sql = "
			SELECT SUBSTRING_INDEX(s_core_snippets.namespace, '/', $layer) AS namespaceExploded,s_core_snippets.namespace AS namespaceOriginal FROM s_core_snippets
			$where
			GROUP BY namespaceExploded
		";
		$getNamespaceTree = Shopware()->Db()->fetchAll($sql);
		foreach ($getNamespaceTree as &$namespace){
			$ns = explode("/",$namespace["namespaceExploded"]);
			$nsOrig = $namespace["namespaceOriginal"];
			if (!is_array($ns)) $ns = $namespace["namespaceExploded"];
			$ns = $ns[count($ns)-1];
			//echo $nsOrig."<br />"."/".$ns."\//"."<br />";
			if (!preg_match("/".$ns."$/",$nsOrig) || $layer == 1){
				$leaf = false;
			}else {
				$leaf = true;
			}
			$data[] = array("text"=>$ns,"id"=>$namespace["namespaceExploded"]."_".$layer,"leaf"=>$leaf,"layer"=>$layer);
		}
		echo Zend_Json::encode($data);
	}

	/**
	 * Export snippet action
	 */
	public function exportSnippetAction()
	{
		$format = $this->Request()->formatExport;
		if($format=="CSV") {
			$sql = "
				SELECT namespace, name, GROUP_CONCAT( value
					ORDER BY shopID, localeId
					SEPARATOR '~' ) AS localeVals
				FROM s_core_snippets
				GROUP BY name, namespace
				ORDER BY namespace";
			$result = Shopware()->Db()->query($sql);

			$header = array_keys($result->fields);
			$this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
			$this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.csv"');
			$locales = Shopware()->Db()->fetchCol("SELECT concat(l.locale,'-',ml.id) as locale
				FROM s_core_multilanguage as ml
				LEFT JOIN s_core_locales as l ON (ml.locale = l.id) ORDER BY ml.id, l.id");
			$tempHeader = array();
			$tempHeader[] ="namespace";
			$tempHeader[] ="name";
			$countLocales = count($locales);
			foreach ($locales as $key => $locale) {
				$tempHeader[] = "value-".$locale;
				$headerLocaleFields = "value-".$locale;
			}
			$header = $tempHeader;

			//echo "\xEF\xBB\xBF";
			echo implode($header,";");
			echo "\r\n";

			while ($row = $result->fetch()) {
				if(strpos($row["localeVals"],"~") !== false) {
					//Contains foreign Value
					$tempArr = explode("~",$row["localeVals"]);
					$countAvailableLocales = count($tempArr);
					foreach ($tempArr as $key => $value) {
					    $row["value-".$locales[$key]] = $this->getFormatSnippetForExport($value);
					    $values[] = $value;
					}
					//fill missing locale data
					for ($i = $countAvailableLocales; $i < $countLocales; $i++) {
						$row["value-".$locales[$i]] = "";
					}
				}
				else {
					$row["value-".$locales[0]] = $this->getFormatSnippetForExport($row["localeVals"]);
					for ($i = 1; $i < $countLocales; $i++) {
					    $row["value-".$locales[$i]] = "";
					}
				}
				$row['namespace'] = $this->getFormatSnippetForExport($row['namespace']);
				$row['name'] = $this->getFormatSnippetForExport($row['name']);
				unset($row["localeVals"]);
				echo $this->encodeLine($row, array_keys($row));
			}
		} else {
			$this->Response()->setHeader('Content-type: text/plain','');
			$this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.sql"');

			$sql = "SELECT * FROM `s_core_snippets`";
			$result = Shopware()->Db()->query($sql);
			$countRows = (int) $result->rowCount();

			echo "\xEF\xBB\xBF";
			echo  "REPLACE INTO `s_core_snippets` (`namespace`, `name`, `localeID`, `shopID`, `value`, `created`, `updated`) VALUES"."\r\n";

			for ($i=1; $row = $result->fetch(); $i++) {
				$row['namespace'] = mysql_escape_string(utf8_encode($row['namespace']));
				$row['name'] = mysql_escape_string(utf8_encode($row['name']));
				$row['localeID'] = mysql_escape_string(utf8_encode($row['localeID']));
				$row['value'] = mysql_escape_string(utf8_encode($row['value']));
				if($countRows != $key+1){
					echo "('{$row['namespace']}', '{$row['name']}', '{$row['localeID']}', '{$row['shopID']}','{$row['value']}', '{$row['created']}', NOW()),"."\r\n";
				} else {
					echo "('{$row['namespace']}', '{$row['name']}', '{$row['localeID']}', '{$row['shopID']}','{$row['value']}', '{$row['created']}', NOW());";
				}
			}
		}
	}

	/**
	 * Read xml row action
	 *
	 * @param unknown_type $xml
	 * @param array $keys
	 * @return array
	 */
	public function readXmlRow($xml, $keys=null)
	{
		$data = array();
    	foreach ($xml as $cell) {
    		$data[] = (string) $cell->Data;
    	}
    	if($keys!==null) {
    		$key_data = array();
	    	foreach ($keys as $key=>$name) {
	    		$key_data[$name] = isset($data[$key]) ? $data[$key] : '';
	    	}
	    	return $key_data;
    	}
    	return $data;
	}

	/**
	 * Import snippet action
	 */
	public function importSnippetAction()
	{
		$file_name = basename($_FILES['snippet_file']['name']);
		$extension = pathinfo($file_name, PATHINFO_EXTENSION);

		if(!in_array($extension, array('csv', 'txt', 'xml'))) {
			echo htmlentities(Zend_Json::encode(array(
				'msg' => utf8_encode('Dieses Dateiformat wird nicht unterstützt.'),
				'success' => false
			)));
			return;
		}

		$tmpdir = Shopware()->OldPath().'/engine/connectors/api/tmp';

		if(!file_exists($tmpdir) && !is_writeable($tmpdir)) {
			echo htmlentities(Zend_Json::encode(array(
				'msg' => utf8_encode('Für den Ordner "/engine/connectors/api/tmp" sind keine Schreibrechte vorhanden.'),
				'success' => false
			)));
			return;
		}

		$file = tempnam($tmpdir, 'import_');

		move_uploaded_file($_FILES['snippet_file']['tmp_name'], $file);
		chmod($file, 0644);

		if($extension=='csv') {
			$snippets = new Shopware_Components_CsvIterator($file, ';');
			$headers = $snippets->getHeader();
		} elseif($extension=='txt') {
			$snippets = new Shopware_Components_CsvIterator($file, "\t");
			$snippets->SetFieldmark('');
			$headers = $snippets->getHeader();
		} else {
			$xml = @simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
			$snippets = $xml->Worksheet->Table->Row;
			$headers = $this->readXmlRow(current($snippets));
		}

		if(empty($headers) || !in_array('namespace', $headers) || !in_array('name', $headers)) {
			echo htmlentities(json_encode(array(
				'msg' => utf8_encode('Die Datei enspricht nicht den Vorgaben.'),
				'success' => false
			)));
			return;
		}

		$translations = array();
		foreach ($headers as $header) {
			$pos = strpos($header, 'value-');
			if($pos === false) {
				continue;
			}
			$row = explode('-',$header);
			$translations[] = array(
				'both' => $row[1].'-'.$row[2],
				'localeID' => $this->getLocaleId($row[1]),
				'shopID' => $row[2],
			);
		}

		$counter = 0;
		foreach ($snippets as $key => $snippet) {
			if($extension=='xml') {
				$snippet = $this->readXmlRow($snippet, $headers);
				if($snippet['name']=='name') {
					continue;
				}
			}
			foreach ($translations as $translation) {
				if(empty($snippet['value-'.$translation['both']])) {
					continue;
				}
				$namespace = trim(ltrim($snippet['namespace'], "'"));
				$name = trim(ltrim($snippet['name'], "'"));
				if(empty($name)) {
					continue;
				}
				$value = trim(ltrim($snippet['value-'.$translation['both']], "'"));
				$value = $this->getFormatSnippetForSave($value);
				$sql = '
					INSERT INTO `s_core_snippets` (`namespace`, `name`, `localeID`, `shopID`, `value`, `updated`, `created`)
					VALUES (?, ?, ?, ?, ?, NOW(), NOW())
					ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), `updated`=NOW()
				';
				Shopware()->Db()->query($sql,array(
					$namespace, $name,
					$translation['localeID'], $translation['shopID'], $value
				));
				$counter++;
			}
		}
		echo htmlentities(json_encode(array(
			'msg' => utf8_encode('Es wurden '.$counter.' Textbausteine importiert.'),
			'success' => true
		)));
	}

	/**
	 * Format snippet for save
	 *
	 * @param string $string
	 * @return string
	 */
	protected function getFormatSnippetForSave($string)
	{
		if(function_exists('mb_convert_encoding')) {
			$string = mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
		}
		$string = str_replace(
			array('&nbsp;', '&amp;', '&lt;', '&gt;'),
			array('%%%SHOPWARE_NBSP%%%', '%%%SHOPWARE_AMP%%%', '%%%SHOPWARE_LT%%%', '%%%SHOPWARE_GT%%%'),
			$string
		);
		$string = html_entity_decode(utf8_decode($string), ENT_NOQUOTES);
		$string = str_replace(
			array('%%%SHOPWARE_NBSP%%%', '%%%SHOPWARE_AMP%%%', '%%%SHOPWARE_LT%%%', '%%%SHOPWARE_GT%%%'),
			array('&nbsp;', '&amp;', '&lt;', '&gt;'),
			$string
		);
		return $string;
	}

	/**
	 * Format snippet for grid
	 *
	 * @param string $string
	 * @return string
	 */
	protected function getFormatSnippetForGrid($string)
	{
		$string = str_replace(
			array('&nbsp;', '&amp;', '&lt;', '&gt;'),
			array('%%%SHOPWARE_NBSP%%%', '%%%SHOPWARE_AMP%%%', '%%%SHOPWARE_LT%%%', '%%%SHOPWARE_GT%%%'),
			$string
		);
		if(function_exists('mb_convert_encoding')) {
			$string = mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');
		} else {
			$string = utf8_encode(html_entity_decode($string, ENT_NOQUOTES));
		}
		$string = str_replace(
			array('%%%SHOPWARE_NBSP%%%', '%%%SHOPWARE_AMP%%%', '%%%SHOPWARE_LT%%%', '%%%SHOPWARE_GT%%%'),
			array('&nbsp;', '&amp;', '&lt;', '&gt;'),
			$string
		);
		return $string;
	}

	/**
	 * Format snippet for export
	 *
	 * @param string $string
	 * @return string
	 */
	protected function getFormatSnippetForExport($string)
	{
		if(function_exists('mb_convert_encoding')) {
			$string = mb_convert_encoding($string, 'HTML-ENTITIES', 'ISO-8859-1');
		} else {
			$string = htmlentities($string, ENT_NOQUOTES);
		}
		return $string;
	}

	/**
	 * Returns locales and ahop ids
	 *
	 * @return unknown
	 */
	protected function getAllLocalesAndShopIds()
	{
		$result = Shopware()->Db()->fetchAll("
			SELECT DISTINCT s.shopID as id, l.locale, CONCAT(IF(o.id=1, 'Default', o.name), ' / ', l.language) as name
			FROM s_core_snippets s, s_core_locales l, s_core_multilanguage o
			WHERE l.id = s.localeID
			AND o.id = s.shopID
		");
		return $result;
	}

	/**
	 * Returns locale id by locale
	 *
	 * @param unknown_type $locale
	 * @return unknown
	 */
	protected function getLocaleId($locale)
	{
		$sql = '
			SELECT `id`
			FROM `s_core_locales`
			WHERE `locale` = ?
		';
		return Shopware()->Db()->fetchOne($sql, array($locale));
	}

	/**
	 * Encode line for csv
	 *
	 * @param array $line
	 * @param array $keys
	 * @return string
	 */
	protected function encodeLine($line, $keys)
	{
		$settings = array(
			"separator" => ";",
			"encoding" => 'ISO-8859-1', //UTF-8
			"fieldmark" => '"',
			"escaped_fieldmark" => '""',
			"newline" => "\r\n",
			"escaped_newline" => '',
		);

		$lastKey = end($keys);
		foreach ($keys as $key) {
			if(!empty($line[$key])) {
				//if($settings['encoding']=="UTF-8") {
				//	$line[$key] = utf8_decode($line[$key]);
				//}
				if(strpos($line[$key], "\r") !== false
				  || strpos($line[$key], "\n") !== false
				  || strpos($line[$key], $settings['fieldmark']) !== false
				  || strpos($line[$key], $settings['separator']) !== false)
				{
					$csv .= $settings['fieldmark']
						  . str_replace($settings['fieldmark'], $settings['escaped_fieldmark'], $line[$key])
						  . $settings['fieldmark'];
				} else {
					$csv .= "'" . $line[$key];
				}
			}
			if($lastKey != $key){
				$csv .= $settings['separator'];
			} else{
				$csv .= $settings['newline'];
			}
		}
		return $csv;
	}
}
