<?php
/**
 * Shopware API Import
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class sShopwareImport
{
	public $sAPI;
	public $sSystem;
	public $sDB;
	public $sPath;
	public $sSettings = array(
		"chmod" => 0644,
		"dec_point" => ","
	);
	
	/**
	 * Importiert einen Artikel in Shopware
	 * 
	 * @param array $article
	 * 
	 * Beispiel-Felder (weitere Möglich):
	 * 
	 * name => Artikelbezeichnung
	 * 
	 * ordernumber => Bestellnummer
	 * 
	 * mainnumber => Falls Variante, Bestellnummer des Vater-Artikels
	 * 
	 * mainID => Falls Variante, ID des Vater-Artikels
	 * 
	 * supplier => Name des Herstellers
	 * 
	 * suppliernumber => Hersteller-Bestellnummer
	 * 
	 * shippingtime => Lieferzeit in Tagen
	 * 
	 * releasedate => Veröffentlichungsdatumd es Artikels im Format YYYY-MM-DD
	 * 
	 * instock => Lagerbestand des Artikels 
	 * 
	 * stockmin => Mindest-Lagerbestand
	 * 
	 * weight => Gewicht in KG (Dezimaltrennzeichen .)
	 * 
	 * active => Aktiv 1/0
	 * 
	 * description_long => Langbeschreibung
	 * 
	 * description => Kurzbeschreibung
	 * 
	 * attr1 bis attr20 => Inhalt des Attributs
	 * 
	 * additionaltext => Variantentext
	 * 
	 * taxID => MwSt.Satz (ID aus s_core_tax)
	 * 
	 * @param array $config 
	 * Enthält verschiedene Konfigurations-Optionen
	 * (optional)
	 * @access public
	 * @return array Alle offenen Bestellungen
	 */
	function sArticle ($article, $config = array())
	{
		if(!isset($config['update']))
			$config['update'] = true;
			
		if (empty($article['ordernumber'])&&empty($article['articledetailsID'])&&empty($article['articleID']))
		{
			$this->sAPI->sSetError("Ordernumber or articleID are required", 10200);
			return false;
		}
			
		### ELEMENTS OF s_articles ###
		if(isset($article["name"]))
			$article['name'] = $this->sDB->qstr($this->sValDescription($article['name']));
		if(isset($article["shippingtime"]))
			$article['shippingtime'] = $this->sDB->qstr((string)$article['shippingtime']);
		if(isset($article["description"]))
			$article['description'] = $this->sDB->qstr((string)$article['description']);
		if(isset($article["description_long"]))
			$article['description_long'] = $this->sDB->qstr((string)$article['description_long']);
		if(isset($article["keywords"]))
			$article['keywords'] = $this->sDB->qstr((string)$article['keywords']);
		if(isset($article["packunit"]))
			$article['packunit'] = $this->sDB->qstr((string)$article['packunit']);  	
		if(isset($article['referenceunit']))
			$article['referenceunit'] = $this->sValFloat($article['referenceunit']);
		if(isset($article['purchaseunit']))
			$article['purchaseunit'] = $this->sValFloat($article['purchaseunit']);
		if(isset($article['supplierID']))
			$article['supplierID'] = intval($article['supplierID']);
		if(isset($article['unitID']))
			$article['unitID'] = intval($article['unitID']);
		if(isset($article['taxID']))
			$article['taxID'] = intval($article['taxID']);		
		if(isset($article['filtergroupID']))
			$article['filtergroupID'] = intval($article['filtergroupID']);
		if(isset($article['pricegroupID']))
			$article['pricegroupID'] = intval($article['pricegroupID']);
		if(isset($article['maxpurchase']))
			$article['maxpurchase'] = intval($article['maxpurchase']);
		if(isset($article['purchasesteps']))
			$article['purchasesteps'] = intval($article['purchasesteps']);
		if(isset($article['minpurchase']))
			$article['minpurchase'] = intval($article['minpurchase']);
		if(isset($article['pseudosales']))
			$article['pseudosales'] = intval($article['pseudosales']);
		if(isset($article['topseller']))
			$article['topseller'] = empty($article['topseller']) ? 0 : 1;
		if(isset($article['shippingfree']))
			$article['shippingfree'] = empty($article['shippingfree']) ? 0 : 1;
		if(isset($article['notification']))
			$article['notification'] = empty($article['notification']) ? 0 : 1;
		if(isset($article['laststock']))
			$article['laststock'] = empty($article['laststock']) ? 0 : 1;
		if(isset($article['active']))
			$article['active'] = empty($article['active']) ? 0 : 1;
		if(isset($article['pricegroupActive']))
			$article['pricegroupActive'] = empty($article['pricegroupActive']) ? 0 : 1;
		if(!empty($article["releasedate"]))
			$article['releasedate'] = $this->sDB->DBDate($article['releasedate']);
		elseif(isset($article['releasedate']))
			$article['releasedate'] = '0000-00-00';
		if(!empty($article['added']))
			$article['added'] = $this->sDB->DBDate($article['added']);
		else 
			unset($article['added']);
		if(!empty($article['changed']))
			$article['changed'] = $this->sDB->DBTimeStamp($article['changed']);
		else 
			unset($article['changed']);
		if(isset($article['crossbundlelook']))
			$article['crossbundlelook'] = empty($article['crossbundlelook']) ? 0 : 1; 
		### ELEMENTS OF s_articles_details ###		
		if(isset($article["suppliernumber"]))
			$article['suppliernumber'] = $this->sDB->qstr($this->sValDescription($article['suppliernumber']));
		if(isset($article["ordernumber"]))
			$article['ordernumber'] = $this->sDB->qstr($this->sValDescription($article['ordernumber']));		
		if(isset($article["additionaltext"]))
			$article['additionaltext'] = $this->sDB->qstr((string)$article['additionaltext']);
		if(isset($article['instock']))
			$article['instock'] = intval($article['instock']);
		if(isset($article['stockmin']))
			$article['stockmin'] = intval($article['stockmin']);	
		if(isset($article['position']))
			$article['position'] = intval($article['position']);
		if(isset($article['weight']))
			$article['weight'] = $this->sValFloat($article['weight']);			

			
		### ELEMENTS OF s_articles_attributes ##
		if(isset($article['articledetailsID']))
			$article['articledetailsID'] = intval($article['articledetailsID']);
		if(isset($article['articleID']))
			$article['articleID'] = intval($article['articleID']);
		if(isset($article["attr"])&&is_array($article["attr"]))
		{
			foreach ($article["attr"] as $key=>$attr)
			{
				if(!is_int($key)||$key>20)
					unset ($article["attr"][$key]);
				else
					$article["attr"][$key] = $this->sDB->qstr((string) $attr);
			}
		}
		else 
		{
			$article["attr"] = array();
		}
		
		for($i=1;$i<=20;$i++)
		{
			if(isset($article["attr$i"]))
			{								
				$article["attr"][$i] = $this->sDB->qstr((string)$article["attr$i"]);
			}		
		}		


		if(!empty($article['mainID']))
			$article['mainID'] = intval($article['mainID']);
		if(!empty($article['maindetailsID']))
			$article['maindetailsID'] = intval($article['maindetailsID']);
		if(!empty($article['mainnumber']))
			$article['mainnumber'] = $this->sDB->qstr((string)$article['mainnumber']);

		// Varianten-Artikel überprüfen
		if(!empty($article['mainnumber'])||!empty($article['mainID'])||!empty($article['maindetailsID']))
		{
			if(!empty($article['maindetailsID']))
				$where = "id={$article['maindetailsID']}";
			elseif(!empty($article['mainID']))
				$where = "articleID={$article['mainID']}";
			else
				$where = "ordernumber={$article['mainnumber']}";
			$sql = "
				SELECT id, articleID FROM s_articles_details
				WHERE $where AND kind = 1
			";
			$row = $this->sDB->GetRow($sql);
			if(empty($row['id']))
			{
				$this->sAPI->sSetError("Main article with '$where' not found", 10201);
				return false;
			}
			$article['maindetailsID'] = $row['id'];
			$article['articleID'] = $row['articleID'];
		}
		// Wir überprüfen ob Artikel vorhanden ist, wenn ja holen wir die ArtikelDetailsID
		if(!empty($article['articledetailsID']))
			$where = "d.id={$article['articledetailsID']}";
		elseif(!empty($article['ordernumber']))
			$where = "d.ordernumber={$article['ordernumber']}";
		elseif(!empty($article['articleID']))
			$where = "d.articleID={$article['articleID']} AND d.kind = 1";
		$sql = "
			SELECT d.id, d.articleID, d.kind, a.taxID
			FROM s_articles a, s_articles_details d
			WHERE a.id=d.articleID
			AND $where
		";
		$row = $this->sDB->GetRow($sql);
		// Wenn vorhanden und Update unerwünscht
		if(!empty($row['id'])&&empty($config['update']))
		{
			$this->sAPI->sSetError("Update not allowed", 10202);
			return false;
		}
		// Varianten-Artikel überprüfen 2
		if(empty($article['maindetailsID'])
		  || (!empty($row['id']) && $article['maindetailsID'] == $row['id'])) {
			$article['kind'] = 1;
		} else {
			$article['kind'] = 2;
		}
		// Wenn nicht vorhanden und es ist keine Bestellnummer vorhanden
		if(empty($row['id'])&&!isset($article['ordernumber']))
		{
			$this->sAPI->sSetError("Article for update not found", 10203);
			return false;
		}
		// Artikel wird zu einer Variante
		if (!empty($row)&&$row['kind']==1&&$article['kind']==2)
		{
			$this->sDeleteArticle(array("articleID"=>$row['articleID']));
			unset($row);
		}
		// Variante ändert Hauptartikel
		// Variante wird zu einen Artikel
		// Variante beleibt eine Variante
		elseif(!empty($row))
		{
			$article['articledetailsID'] = $row['id'];
			if ($article['kind']==1) {
				$article['articleID'] = $row['articleID'];
			}
			if(empty($article['taxID']) && empty($article['tax'])) {
				$article['taxID'] = $row['taxID'];
			}
		}
		
		if($article['kind']==2 && !empty($article['ordernumber']))
		{
			//Bestellnummer ist schon für eine Konfigurator-Auswahl vergeben
			$sql = "SELECT articleID FROM s_articles_groups_value WHERE ordernumber={$article['ordernumber']}";
			$result = $this->sDB->GetOne($sql);
			if(!empty($result))
			{
				$this->sAPI->sSetError("Ordernumber '{$article['ordernumber']}' is already assigned", 10204);
				return false;
			}
			//Konfiguratoren und Varianten können nicht zusammen verwendet werden
			$sql = "SELECT COUNT(*) FROM s_articles_groups_value WHERE articleID={$article['articleID']}";
			$result = $this->sDB->GetOne($sql);
			if($result===false||!empty($result))
			{
				$this->sAPI->sSetError("Article is already a configurator", 10205);
				return false;
			}
		}

		//HerstellerID holen
		if (isset($article['supplierID']))
			$article['supplierID'] = $this->sSupplier(array('supplierID'=>$article['supplierID']));
		elseif (isset($article['supplier']))
			$article['supplierID'] = $this->sSupplier(array('supplier'=>$article['supplier']));
		if (empty($article['supplierID'])&&empty($article['articleID'])) // Hersteller wird benötigt
		{
			$this->sAPI->sSetError("Supplier are required", 10206);
			return false;
		}
			
		//TaxID holen
		if (!empty($article['taxID']))
			$where = ' WHERE id='.intval($article['taxID']);
		elseif (isset($article['tax']))
			$where = ' WHERE tax='.$this->sValFloat($article['tax']);
		else
			$where =  'ORDER BY id';
			
		$sql = 'SELECT id as taxID, tax FROM s_core_tax '.$where.' LIMIT 1';
		$row = $this->sDB->GetRow($sql);
		if(empty($row))
		{
			$this->sAPI->sSetError("Tax rate not found", 10207);
			return false;
		}
		$article['taxID'] = $row['taxID'];
		$article['tax'] = $row['tax'];
		
		//Wenn Artikel nicht vorhanden ist, legen wir ihn an
		
		$article_fields = array(
			"supplierID",
			"name",
			"description",
			"description_long",
			"shippingtime",
			"datum",
			"active",
			"shippingfree",
			"notification",
			"crossbundlelook",
			"releasedate",
			"taxID",
			"pseudosales",
			"topseller",
			"keywords",
			"minpurchase",
			"purchasesteps",
			"maxpurchase",
			"purchaseunit",
			"referenceunit",
			"unitID",
			"changetime",
			"pricegroupID",
			"pricegroupActive",
			"filtergroupID",
			"laststock",
			"packunit"
		);
		
		if(empty($article['articleID']))
		{
			if (!isset($article['active'])||$article['active']==1)
				$article['active'] = 1;
			else
				$article['active'] = 0;
			if (empty($article['taxID']))
				$article['taxID'] = 1;
			if (empty($article['added']))
				$article['added'] = $this->sDB->sysDate;
			if (empty($article['changed']))
				$article['changed'] = $this->sDB->sysTimeStamp;
				
			$article['datum'] = $article['added'];
			$article['changetime'] = $article['changed'];
			
			$insert_fields = array();
			$insert_values = array();
			foreach ($article_fields as $field)
			{
				if(isset($article[$field]))
				{
					$insert_fields[] = $field;
					$insert_values[] = $article[$field];
				}
			}
			$sql = "
				INSERT INTO s_articles (".implode(", ",$insert_fields).")
				VALUES (".implode(", ",$insert_values).") 
			";
			$this->sDB->Execute($sql);
			$article['articleID'] = $this->sDB->Insert_ID();
		}//Wenn Artikel vorhanden ist, aktualisieren wir ihn
		else
		{
			if(!empty($article['added']))
				$article['datum'] = $article['added'];
			if(empty($article['changed']))
				$article['changed'] = $this->sDB->sysTimeStamp;
			$article['changetime'] = $article['changed'];
			
			if($article['kind']==1)
			{
				$upset = array();
				foreach ($article_fields as $field)
				{
					if(isset($article[$field]))
					{
						$upset[] = $field."=".$article[$field];
					}
				}
				$upset = implode(", ",$upset);
			}
			else 
			{
				$upset = 'changetime='.$article['changetime'];
			}
			$sql = "
				UPDATE s_articles 
				SET 
					$upset
				WHERE id = {$article['articleID']}
			";
			$this->sDB->Execute($sql);
		}
		
		//Wenn die Artikeldetails nicht vorhanden sind, legen wir diese auch an
		if (empty($article['articledetailsID']))
		{
			if(empty($article['stockmin']))
				$article['stockmin'] = 0;
			if(empty($article['instock']))
				$article['instock'] = 0;
			if(empty($article['weight']))
				$article['weight'] = 0;
			if (empty($article['additionaltext']))
				$article['additionaltext'] = "''";
			if (empty($article['suppliernumber']))
				$article['suppliernumber'] = "''";
			if (!isset($article['active'])||$article['active']==1)
				$article['active'] = 1;
			else
				$article['active'] = 0;
			$sql = "
				INSERT INTO s_articles_details (articleID, ordernumber, kind, additionaltext, active, esd, weight, suppliernumber, instock, stockmin)
				VALUES ({$article['articleID']}, {$article['ordernumber']}, {$article['kind']}, {$article['additionaltext']}, {$article['active']}, 0, {$article['weight']}, {$article['suppliernumber']}, {$article['instock']}, {$article['stockmin']})
			";
			$this->sDB->Execute($sql);
			$article['articledetailsID'] = $this->sDB->Insert_ID();
		}
		else //Wenn die Artikeldetails vorhanden sind, aktualisieren wir diese
		{
			$upset = array();
			if(isset($article['additionaltext']))
				$upset[] = "additionaltext=".$article['additionaltext'];
			if(isset($article['ordernumber']))
				$upset[] = "ordernumber=".$article['ordernumber'];
			if(isset($article['suppliernumber']))
				$upset[] = "suppliernumber=".$article['suppliernumber'];
			if(isset($article['kind']))
				$upset[] = "kind=".$article['kind'];
			if(isset($article['additionaltext']))
				$upset[] = "additionaltext=".$article['additionaltext'];
			if(isset($article['active']))
				$upset[] = "active=".$article['active'];
			if(isset($article['instock']))
				$upset[] = "instock=".$article['instock'];
			if(isset($article['stockmin']))
				$upset[] = "stockmin=".$article['stockmin'];
			if(isset($article['weight']))
				$upset[] = "weight=".$article['weight'];
			if(isset($article['articleID']))
				$upset[] = "articleID=".$article['articleID'];
			if(!empty($upset))
			{
				$upset = implode(", ",$upset);
				$sql = "
					UPDATE 
						s_articles_details 
					SET 
						$upset
					WHERE 
						id = {$article['articledetailsID']}
				";
				$this->sDB->Execute($sql);
				
			}
		}
		$sql = "UPDATE `s_articles_prices` SET `articleID`=? WHERE `articledetailsID` = ?;";
		$this->sDB->Execute($sql,array($article['articleID'],$article['articledetailsID']));

		//Nachschauen ob die Artikelattribute schon angelegt sind
		$sql = "
			SELECT `id`
			FROM `s_articles_attributes`
			WHERE `articledetailsID` = {$article['articledetailsID']}
		";
		$article['articleattributesID'] = $this->sDB->GetOne($sql);
		if(!empty($article['articleattributesID']))
		{
			$upset = "";
			if(!empty($article['attr'])&&is_array($article['attr']))
			{
				foreach ($article['attr'] as $key=>$value)
				{
					$upset .= ", attr$key = $value";
				}
			}
			$sql = "
				UPDATE 
					s_articles_attributes
				SET 
					articleID = {$article['articleID']} $upset
				WHERE
					articledetailsID = {$article['articledetailsID']}
			";
			$this->sDB->Execute($sql);
		}
		else
		{
			if(!empty($article['attr']))
			{
				$upset = ", attr".implode(", attr",array_keys($article['attr']));
				$upset2 = ", ".implode(", ",$article['attr']);
			}
			else
			{
				$upset = "";
				$upset2 = "";
			}
			$sql = "
				INSERT INTO 
					s_articles_attributes 
				(
					articleID, articledetailsID 
					$upset
				)
				VALUES 
				(
					{$article['articleID']}, {$article['articledetailsID']}
					$upset2
				)
			";
			$this->sDB->Execute($sql);
			$article['articleattributesID'] = $this->sDB->Insert_ID();
		}
		return array(
			'articledetailsID' => $article['articledetailsID'],
			'articleID' => $article['articleID'],
			'articleattributesID' => $article['articleattributesID'],
			'kind' => $article['kind'],
			'supplierID' => $article['supplierID'],
			'tax' => $article['tax'],
			'taxID' => $article['taxID']
		);
	}
	
	/**
	 * Insert or update customer
	 *
	 * @param array $customer
	 * @return array
	 */
	function sCustomer ($customer)
	{
		if(isset($customer["password"]))
			$customer["password"] = trim($customer['password'],"\r\n");
		if(empty($customer["md5_password"])&&!empty($customer['password']))
			$customer["md5_password"] = md5($customer['password']);
		if(isset($customer["md5_password"]))
			$customer["md5_password"] = $this->sDB->qstr($customer['md5_password']);
		if(isset($customer["email"]))
			$customer["email"] = $this->sDB->qstr(trim($customer["email"]));
		if(isset($customer["customergroup"]))
			$customer["customergroup"] = $this->sDB->qstr((string)$customer["customergroup"]);
		if(isset($customer["validation"]))
			$customer["validation"] = $this->sDB->qstr((string)$customer["validation"]);
		if(isset($customer["language"]))
			$customer["language"] = $this->sDB->qstr((string)$customer["language"]);
		if(isset($customer["referer"]))
			$customer["referer"] = $this->sDB->qstr((string)$customer["referer"]);
			
		if(isset($customer['active']))
			$customer['active'] = empty($customer['active']) ? 0 : 1;
		if(isset($customer['accountmode']))
			$customer['accountmode'] = empty($customer['accountmode']) ? 0 : 1;
		if(isset($customer['newsletter']))
			$customer['newsletter'] = empty($customer['newsletter']) ? 0 : 1;
			
		if(isset($customer['paymentID']))
			$customer['paymentID'] = intval($customer['paymentID']);
		if(isset($customer['paymentpreset']))
			$customer['paymentpreset'] = intval($customer['paymentpreset']);
		if(isset($customer['subshopID']))
			$customer['subshopID '] = intval($customer['subshopID']);
		if(isset($customer['userID']))
			$customer['userID'] = intval($customer['userID']);
			
		if(isset($customer['firstlogin']))
			$customer['firstlogin'] = $this->sDB->DBDate($customer['firstlogin']);
		if(isset($customer['lastlogin']))
			$customer['lastlogin'] = $this->sDB->DBTimeStamp($customer['lastlogin']);
		
		#$reg = "/^\s*[a-z][a-z0-9]*(\.[a-z0-9][a-z0-9-]*)*(\+[a-z0-9]*)?@[a-z0-9][a-z0-9-]*(\.[a-z0-9][a-z0-9-]*)*\.[a-z]{2,6}\s*$/i";
		#if(!preg_match($reg, $customer["email"]))
		#	return false;
		
		if(empty($customer['userID'])&&empty($customer['email']))
			return false;
		
		if(empty($customer['userID'])&&!empty($customer['email']))
		{
			$sql = "SELECT id FROM s_user WHERE email={$customer["email"]}";
			$customer['userID'] = $this->sDB->GetOne($sql);
		}
		$fields = array(
			"email",
			"active",
			"accountmode",
			"paymentID",
			"firstlogin",
			"lastlogin",
			"newsletter",
			"validation",
			"customergroup",
			"paymentpreset",
			"language",
			"subshopID",
			"referer"
		);
		if(empty($customer["userID"]))
		{
			if(empty($customer['password'])&&empty($customer["md5_password"]))
			{
				$customer["password"] = "";
				for ($i = 0; $i < 10; $i++) {
				    $randnum = mt_rand(0,35);
				    if ($randnum < 10)
				        $customer["password"] .= $randnum;
				    else
				        $customer["password"] .= chr($randnum+87);
				}
				$customer["md5_password"] = $this->sDB->qstr(md5($customer['password']));
			}
			if(!isset($customer['active']))
				$customer['active'] = 1;
			if(empty($customer['customergroup']))
				$customer['customergroup'] = $this->sDB->qstr("EK");
			if(empty($customer['firstlogin']))
				$customer['firstlogin'] = $this->sDB->sysDate;
			if(empty($article['lastlogin']))
				$customer['lastlogin'] = $this->sDB->sysTimeStamp;
			if(!isset($customer['validation']))
				$customer['validation'] = $this->sDB->qstr("");

			$insert_fields = array();
			$insert_values = array();
			foreach ($fields as $field)
			{
				if(isset($customer[$field]))
				{
					$insert_fields[] = $field;
					$insert_values[] = $customer[$field];
				}
			}
			$insert_fields[] = "password";
			$insert_values[] = $customer["md5_password"];
			$sql = "
				INSERT INTO s_user (".implode(", ",$insert_fields).")
				VALUES (".implode(", ",$insert_values).") 
			";
			$result = $this->sDB->Execute($sql);
			if($result===false)
				return false;
			$customer['userID'] = $this->sDB->Insert_ID();
		}
		else 
		{
			
			$upset = array();
			foreach ($fields as $field)
			{
				if(isset($customer[$field]))
				{
					$upset[] = $field."=".$customer[$field];
				}
			}
			if(isset($customer["md5_password"]))
				$upset[] = "password=".$customer["md5_password"];
			if(!empty($upset))
			{
				$upset = implode(", ",$upset);
				$sql = "
					UPDATE s_user 
					SET $upset
					WHERE id = {$customer['userID']}
				";
				$this->sDB->Execute($sql);
			}
		}
		if(isset($customer["billing_company"]))
			$customer["billing_company"] = $this->sDB->qstr((string)$customer["billing_company"]);
		if(isset($customer["billing_department"]))
			$customer["billing_department"] = $this->sDB->qstr((string)$customer["billing_department"]);
		if(isset($customer["billing_salutation"]))
			$customer["billing_salutation"] = $this->sDB->qstr((string)$customer["billing_salutation"]);
		if(isset($customer["billing_firstname"]))
			$customer["billing_firstname"] = $this->sDB->qstr((string)$customer["billing_firstname"]);
		if(isset($customer["billing_lastname"]))
			$customer["billing_lastname"] = $this->sDB->qstr((string)$customer["billing_lastname"]);
		if(isset($customer["billing_street"]))
			$customer["billing_street"] = $this->sDB->qstr((string)$customer["billing_street"]);
		if(isset($customer["billing_streetnumber"]))
			$customer["billing_streetnumber"] = $this->sDB->qstr((string)$customer["billing_streetnumber"]);
		if(isset($customer["billing_zipcode"]))
			$customer["billing_zipcode"] = $this->sDB->qstr((string)$customer["billing_zipcode"]);
		if(isset($customer["billing_city"]))
			$customer["billing_city"] = $this->sDB->qstr((string)$customer["billing_city"]);
		if(isset($customer["phone"]))
			$customer["phone"] = $this->sDB->qstr((string)$customer["phone"]);
		if(isset($customer["fax"]))
			$customer["fax"] = $this->sDB->qstr((string)$customer["fax"]);
		if(isset($customer["ustid"]))
			$customer["ustid"] = $this->sDB->qstr((string)$customer["ustid"]);
		if(isset($customer["billing_countryID"]))
			$customer["billing_countryID"] = intval($customer["billing_countryID"]);
		if(empty($customer["billing_countryID"])&&!empty($customer["billing_countryiso"]))
			$customer["billing_countryID"] = (int) $this->sGetCountryID(array("iso"=>$customer["billing_countryiso"]));
		for($i=1;$i<7;$i++)
			if(isset($customer["billing_text$i"]))
				$customer["billing_text$i"] = $this->sDB->qstr((string)$customer["billing_text$i"]);
				
		if(isset($customer["customernumber"]))
			$customer["customernumber"] = $this->sDB->qstr((string)$customer["customernumber"]);
		if(isset($customer["birthday"]))
			$customer["birthday"] = $this->sDB->DBDate($customer['birthday']);	
		$fields = array(
			"userID"=>"userID",
			"company"=>"billing_company",
			"department"=>"billing_department",
			"salutation"=>"billing_salutation",
			"customernumber"=>"customernumber",
			"firstname"=>"billing_firstname",
			"lastname"=>"billing_lastname",
			"street"=>"billing_street",
			"streetnumber"=>"billing_streetnumber",
			"zipcode"=>"billing_zipcode",
			"city"=>"billing_city",
			"phone"=>"phone",
			"fax"=>"fax",
			"countryID"=>"billing_countryID",
			"ustid"=>"ustid",
			"birthday"=>"birthday",
			"text1"=>"billing_text1",
			"text2"=>"billing_text2",
			"text3"=>"billing_text3",
			"text4"=>"billing_text4",
			"text5"=>"billing_text5",
			"text6"=>"billing_text6"
		);
		
		$sql = "SELECT id FROM s_user_billingaddress WHERE userID=".$customer['userID'];
		$customer["billingaddressID"] = $this->sDB->GetOne($sql);
		if(empty($customer["billingaddressID"]))
		{
			$insert_fields = array();
			$insert_values = array();
			foreach ($fields as $field=>$field2)
			{
				if(isset($customer[$field2]))
				{
					$insert_fields[] = $field;
					$insert_values[] = $customer[$field2];
				}
			}
			$sql = "
				INSERT INTO s_user_billingaddress (".implode(", ",$insert_fields).")
				VALUES (".implode(", ",$insert_values).") 
			";
			$result = $this->sDB->Execute($sql);
			if($result===false)
				return false;
				
			$customer["billingaddressID"] = $this->sDB->Insert_ID();
		}
		else 
		{
			$upset = array();
			foreach ($fields as $field=>$field2)
			{
				if(isset($customer[$field2]))
				{
					$upset[] = $field."=".$customer[$field2];
				}
			}
			if(!empty($upset)&&count($upset)>1)
			{
				$upset = implode(", ",$upset);
				$sql = "
					UPDATE s_user_billingaddress 
					SET $upset
					WHERE id = {$customer['billingaddressID']}
				";
				$this->sDB->Execute($sql);
			}
		}
		
		if(!empty($customer["shipping_company"])||!empty($customer["shipping_firstname"])||!empty($customer["shipping_lastname"]))
		{
			if(isset($customer["shipping_company"]))
				$customer["shipping_company"] = $this->sDB->qstr((string)$customer["shipping_company"]);
			if(isset($customer["shipping_department"]))
				$customer["shipping_department"] = $this->sDB->qstr((string)$customer["shipping_department"]);
			if(isset($customer["shipping_salutation"]))
				$customer["shipping_salutation"] = $this->sDB->qstr((string)$customer["shipping_salutation"]);
			if(isset($customer["shipping_firstname"]))
				$customer["shipping_firstname"] = $this->sDB->qstr((string)$customer["shipping_firstname"]);
			if(isset($customer["shipping_lastname"]))
				$customer["shipping_lastname"] = $this->sDB->qstr((string)$customer["shipping_lastname"]);
			if(isset($customer["shipping_street"]))
				$customer["shipping_street"] = $this->sDB->qstr((string)$customer["shipping_street"]);
			if(isset($customer["shipping_streetnumber"]))
				$customer["shipping_streetnumber"] = $this->sDB->qstr((string)$customer["shipping_streetnumber"]);
			if(isset($customer["shipping_zipcode"]))
				$customer["shipping_zipcode"] = $this->sDB->qstr((string)$customer["shipping_zipcode"]);
			if(isset($customer["shipping_city"]))
				$customer["shipping_city"] = $this->sDB->qstr((string)$customer["shipping_city"]);
			if(isset($customer["shipping_countryID"]))
				$customer["shipping_countryID"] = intval($customer["shipping_countryID"]);
			if(empty($customer["shipping_countryID"])&&!empty($customer["shipping_countryiso"]))
				$customer["shipping_countryID"] = (int) $this->sGetCountryID(array("iso"=>$customer["shipping_countryiso"]));
			for($i=1;$i<7;$i++)
			if(isset($customer["shipping_text$i"]))
				$customer["shipping_text$i"] = $this->sDB->qstr((string)$customer["shipping_text$i"]);
			
			$fields = array(
				"userID"=>"userID",
				"company"=>"shipping_company",
				"department"=>"shipping_department",
				"salutation"=>"shipping_salutation",
				"firstname"=>"shipping_firstname",
				"lastname"=>"shipping_lastname",
				"street"=>"shipping_street",
				"streetnumber"=>"shipping_streetnumber",
				"zipcode"=>"shipping_zipcode",
				"city"=>"shipping_city",
				"countryID"=>"shipping_countryID",
				"text1"=>"shipping_text1",
				"text2"=>"shipping_text2",
				"text3"=>"shipping_text3",
				"text4"=>"shipping_text4",
				"text5"=>"shipping_text5",
				"text6"=>"shipping_text6"
			);
			$sql = "SELECT id FROM s_user_shippingaddress WHERE userID=".$customer['userID'];
			$customer["shippingaddressID"] = $this->sDB->GetOne($sql);
			if(empty($customer["shippingaddressID"]))
			{
				$insert_fields = array();
				$insert_values = array();
				foreach ($fields as $field=>$field2)
				{
					if(isset($customer[$field2]))
					{
						$insert_fields[] = $field;
						$insert_values[] = $customer[$field2];
					}
				}
				$sql = "
					INSERT INTO s_user_shippingaddress (".implode(", ",$insert_fields).")
					VALUES (".implode(", ",$insert_values).") 
				";
				$result = $this->sDB->Execute($sql);
				if($result===false)
					return false;
					
				$customer["shippingaddressID"] = $this->sDB->Insert_ID();
			}
			else 
			{
				$upset = array();
				foreach ($fields as $field=>$field2)
				{
					if(isset($customer[$field2]))
					{
						$upset[] = $field."=".$customer[$field2];
					}
				}
				if(!empty($upset)&&count($upset)>1)
				{
					$upset = implode(", ",$upset);
					$sql = "
						UPDATE s_user_shippingaddress 
						SET $upset
						WHERE id = {$customer['shippingaddressID']}
					";
					$this->sDB->Execute($sql);
				}
			}
			
		}
		elseif(isset($customer["shipping_company"])||isset($customer["shipping_firstname"])||isset($customer["shipping_lastname"]))
		{
			$sql = "DELETE FROM s_user_shippingaddress WHERE userID=".$customer["userID"];
			$result = $this->sDB->Execute($sql);
		}
		
		$customer["customernumber"] = $this->sDB->GetOne("SELECT customernumber FROM s_user_billingaddress WHERE userID=".$customer["userID"]);
		if ($this->sSystem->sCONFIG['sSHOPWAREMANAGEDCUSTOMERNUMBERS']&&empty($customer["customernumber"])) {
			$sql = "UPDATE s_order_number n, s_user_billingaddress b SET n.number=n.number+1, b.customernumber=n.number+1 WHERE n.name='user' AND b.userID=?";
			$this->sDB->Execute($sql, array($customer["userID"]));
			$customer["customernumber"] = $this->sDB->GetOne("SELECT customernumber FROM s_user_billingaddress WHERE userID=".$customer["userID"]);
		}
		
		if(isset($customer["newsletter"]))
		{
			if(empty($customer["newsletter"]))
			{
				$sql = "DELETE FROM s_campaigns_mailaddresses WHERE email=".$customer["email"];
				$this->sDB->Execute($sql);
			}
			else 
			{
				if(empty($customer["newslettergroupID"]))
					$customer["newslettergroupID"] = empty($this->sSystem->sCONFIG["sNEWSLETTERDEFAULTGROUP"]) ? 1 : (int) $this->sSystem->sCONFIG["sNEWSLETTERDEFAULTGROUP"];
				else
					$customer["newslettergroupID"] = intval($customer["newslettergroupID"]);
				$sql = "SELECT id FROM s_campaigns_mailaddresses WHERE email=".$customer["email"];
				$result = $this->sDB->GetOne($sql);
				if(empty($result))
				{
					
					$sql = "INSERT INTO s_campaigns_mailaddresses (customer, groupID, email) VALUES (1,{$customer["newslettergroupID"]},{$customer["email"]});";
					$this->sDB->Execute($sql);
				}
			}
		}
		
		return array(
			"userID"=> $customer["userID"],
			//"email"=> $customer["email"],
			"customernumber"=> $customer["customernumber"],
			"password" => $customer["password"],
			"billingaddressID" => $customer["billingaddressID"],
			"shippingaddressID" => $customer["shippingaddressID"],
		);
	}
	
	/**
	 * Returns country id
	 *
	 * @param array $country
	 * @return int|bool
	 */
	function sGetCountryID ($country)
	{
		if(!empty($country["name"]))
			$where = "countryname LIKE ".$this->sDB->qstr(trim((string)$country['name']));
		elseif(!empty($country["iso"]))
			$where = "countryiso=".$this->sDB->qstr(trim((string)$country['iso']));
		elseif(!empty($country["en"]))
			$where = "countryen LIKE ".$this->sDB->qstr(trim((string)$country['en']));
		else 
			return false;
		$sql = "
			SELECT  id
			FROM s_core_countries
			WHERE $where
		";
		$result = $this->sDB->GetOne($sql);
		if($result===false)
			return false;
		return $result;
	}
	
	/**
	 * Insert article images
	 *
	 * @param array $article
	 * @param array $images
	 * @return bool
	 */
	function sArticleImages ($article, $images)
	{
		if(!($articleID = $this->sGetArticleID($article)))
			return false;
		$inserts = array();
		if(!empty($images)&&is_array($images))
		{
			foreach ($images as $image)
			{
				if(is_string($image)) $image = array('image'=>$image);
				$image["articleID"] = $articleID;
				$insert = $this->sArticleImage($image);
				if(!empty($insert))
					$inserts[] = $insert;
			}
		}
		$this->sDeleteOtherArticleImages ($articleID, $inserts);
		return $inserts;		
	}
	
	/**
	 * Insert article prices
	 *
	 * @param array $article
	 * @param array $images
	 * @return bool
	 */
	function sArticlePrices ($article, $prices)
	{
		if(!($articledetailsID = $this->sGetArticledetailsID($article)))
			return false;
		if(!empty($prices)&&is_array($prices))
		{
			foreach ($prices as $price)
			{
				$price["articledetailsID"] = $articledetailsID;
				$insert = $this->sArticlePrice($price);
			}
		}
		return true;	
	}
	
	/**
	 * Importieren / Abgleichen von Kategorien
	 * 
	 * @param array $category 
	 * 
	 * parent => ID der Parent-Kategorie (1 für Hauptkategorie)
	 * 
	 * description => Bezeichnung der Kategorie
	 * 
	 * id => ID der Kategorie
	 * 
	 * metakeywords => Meta-Keywords der Kategorie
	 * 
	 * description => Kategorie-Beschreibungstext
	 *  
	 * active => aktiviert/deaktiviert
	 * 
	 * @access public
	 * @return int ID der eingefügten Kategorie
	 */
	function sCategory ($category = array())
	{
		if(isset($category['id']))
			$category['id'] = intval($category['id']);
		if(isset($category['parent']))
			$category['parent'] = intval($category['parent']);
		if(!empty($category['description']))
			$category['description'] = $this->sValDescription($category['description']);
		if(empty($category['description']) && empty($category['id']))
			return false;
			
		if(empty($category['id']))
		{
			if(empty($category['parent']))
				$parent = 1;
			else 
				$parent = $category['parent'];
			$description = $this->sDB->qstr($category['description']);
			$sql = "SELECT id FROM `s_categories` WHERE `description`=$description AND `parent`=$parent";
			$category['updateID'] = $this->sDB->GetOne($sql);
		} else {
			$sql = "SELECT id FROM s_categories WHERE id={$category['id']}";
			$category['updateID'] = $this->sDB->GetOne($sql);
		}
		
		if(empty($category['updateID']))
		{
			if(empty($category['parent']))
				$category['parent'] = 1;
			$category['description'] = $this->sDB->qstr($category['description']);
			if(isset($category['metakeywords']))
				$category['metakeywords'] = $this->sDB->qstr($category['metakeywords']);
			else 
				$category['metakeywords'] = "''";
			if(isset($category['cmsheadline']))
				$category['cmsheadline'] = $this->sDB->qstr($category['cmsheadline']);
			else 
				$category['cmsheadline'] = "''";
			if(isset($category['cmstext']))
				$category['cmstext'] = $this->sDB->qstr($category['cmstext']);
			else 
				$category['cmstext'] = "''";
			if(isset($category['active']))
				$category['active'] = empty($category['active']) ? 0 : 1;
			else 
				$category['active'] = 1;
			if(isset($category['template']))
				$category['template'] = $this->sDB->qstr($category['template']);
			else 
				$category['template'] = $this->sDB->qstr($this->sSystem->sCONFIG['sCATEGORY_DEFAULT_TPL']);
		
			if(empty($category['id']))
				$category['id'] = $this->sDB->qstr(null);
			if(isset($category['position']))
				$position = (int) $category['position'];
			else 
			{
				$sql = "
					SELECT MAX(position) as position
					FROM s_categories
					WHERE parent={$category['parent']}
				";
				$row = $this->sDB->GetRow($sql);
				if (!empty($row["position"]))
					$position = $row["position"]+1;
				else 
					$position = 1;
			}
			$sql = "
				INSERT INTO s_categories
				(
					id,parent,description,metakeywords,cmsheadline,cmstext,position,template,active
				)
				VALUE 
				(
					{$category['id']}, {$category['parent']}, {$category['description']}, {$category['metakeywords']}, {$category['cmsheadline']}, {$category['cmstext']}, $position, {$category['template']}, {$category['active']}
				)
			";
			$this->sDB->Execute($sql);
			if($this->sDB->Insert_ID())
				$category['id'] = $this->sDB->Insert_ID();
			if(empty($category['id']))
				return false;
		}
		else 
		{			
			$upset = array();
			if(isset($category['template']))
				$upset[] = 'template='.$this->sDB->qstr($category['template']);
			if(isset($category['description']))
				$upset[] = 'description='.$this->sDB->qstr($category['description']);
			if(isset($category['metakeywords']))
				$upset[] = 'metakeywords='.$this->sDB->qstr($category['metakeywords']);
			if(isset($category['cmsheadline']))
				$upset[] = 'cmsheadline='.$this->sDB->qstr($category['cmsheadline']);
			if(isset($category['cmstext']))
				$upset[] = 'cmstext='.$this->sDB->qstr($category['cmstext']);	
			if(!empty($category['parent']))
				$upset[] = 'parent='.$this->sDB->qstr($category['parent']);
			if(isset($category['position']))
				$upset[] = 'position='.intval($category['position']);
			if(isset($category['active']))
				$upset[] = 'active='.(empty($category['active']) ? 0 : 1);
			if(!empty($upset))
			{
				$sql = '
					UPDATE `s_categories` 
					SET '.implode(', ', $upset).'
					WHERE id = ?
				';
				$this->sDB->Execute($sql,array($category['updateID']));
			}
			$category['id'] = $category['updateID'];
		}
		
		$upset = array();
		for ($i=1;$i<=6;$i++)
		{
			if(isset($category['ac_attr'.$i]))
			{
				$upset[] = 'ac_attr'.$i.'='.$this->sDB->qstr((string)$category['ac_attr'.$i]);
			}
			elseif(isset($category['attr'][$i]))
			{
				$upset[] = 'ac_attr'.$i.'='.$this->sDB->qstr((string)$category['attr'][$i]);
			}
		}
		if(!empty($upset))
		{
			$sql = '
				UPDATE `s_categories` 
				SET '.implode(', ', $upset).'
				WHERE id = ?
			';
			$this->sDB->Execute($sql,array($category['id']));
		}
		
		return $category['id'];
	}
	
	/**
	 * Import von Artikel-Preisen
	 * 
	 * @param array $price Enthält die zu importierenden Preise in Array-Form
	 * 
	 * customergroup => Zugeordnete Kundengruppe (aus s_core_customergroups, Gäste/Shopbesucher='EK')
	 * 
	 * baseprice => Einkaufspreis
	 * 
	 * pseudoprice => Durchgestrichener Preis
	 * 
	 * from => Angabe ab welcher Menge der Preis gültig ist
	 * 
	 * price => Netto - VK
	 * 
	 * articledetailsID => Details-ID des Artikels (s_articles_details.id)
	 * 
	 * @access public
	 * @return
	 */
	function sArticlePrice ($price)
	{
		if(isset($price['price']))
			$price['price'] = $this->sValFloat($price['price']);
		if(isset($price['tax']))	
			$price['tax'] = $this->sValFloat($price['tax']);
		if(isset($price['pseudoprice']))
			$price['pseudoprice'] = $this->sValFloat($price['pseudoprice']);
		else 
			$price['pseudoprice'] = 0;
		if(isset($price['baseprice']))
			$price['baseprice'] = $this->sValFloat($price['baseprice']);
		else 
			$price['baseprice'] = 0;
		if(isset($price['percent']))
			$price['percent'] = $this->sValFloat($price['percent']);
		else 
			$price['percent'] = 0;
		if(empty($price['from']))
			$price['from'] = 1;
		else 
			$price['from'] = intval($price['from']);
		if(empty($price['pricegroup']))
			$price['pricegroup'] = "EK";
		$price['pricegroup'] = $this->sDB->qstr($price['pricegroup']);
		
		if(!empty($price['tax']))
			$price['price'] = $price['price']/(100+$price['tax'])*100;
		if(isset($price['pseudoprice'])&&!empty($price['tax']))
			$price['pseudoprice'] = $price['pseudoprice']/(100+$price['tax'])*100;
			
		$article = $this->sGetArticleNumbers($price);
		if(empty($article))
			return false;
		if(empty($price['price'])&&empty($price['percent']))
			return false;
		if($price['from']<=1 && empty($price['price']))
			return false;
		
		$sql = "
			DELETE FROM `s_articles_prices` 
			WHERE 
				pricegroup = {$price['pricegroup']}
			AND
				articledetailsID = {$article['articledetailsID']}
			AND
				CAST(`from` AS UNSIGNED) >= {$price['from']}
		";
		$this->sDB->Execute($sql);
		
		if(empty($price['price']))
		{
			$sql = "
				SELECT
					price
				FROM 
					s_articles_prices
				WHERE 
					pricegroup = {$price['pricegroup']}
				AND
					`from`=1
				AND
					articleID =  {$article['articleID']}
				AND
					articledetailsID = {$article['articledetailsID']}
			";
			$price['price'] = $this->sDB->GetOne($sql);
			if(empty($price['price']))
				return false;
			$price['price'] = $price['price']*(100-$price['percent'])/100;
		}
		
		if($price['from']!=1)
		{
			$sql = "
				UPDATE `s_articles_prices` 
				SET `to` = {$price['from']}-1
				WHERE 
					pricegroup = {$price['pricegroup']}
				AND
					articleID =  {$article['articleID']}
				AND
					articledetailsID = {$article['articledetailsID']}
				ORDER BY `from` DESC
				LIMIT 1
			";
			$result = $this->sDB->Execute($sql);
			if(empty($result)||!$this->sDB->Affected_Rows())
				return false;
		}
		
		$sql = "
			INSERT INTO s_articles_prices (pricegroup, `from`, `to`, articleID, articledetailsID, price, pseudoprice, baseprice, percent)
			VALUES ({$price['pricegroup']}, {$price['from']}, 'beliebig', {$article['articleID']}, {$article['articledetailsID']}, {$price['price']}, {$price['pseudoprice']}, {$price['baseprice']}, {$price['percent']})
		";
		$result = $this->sDB->Execute($sql);
		if(empty($result))
			return false;
		else
			return $this->sDB->Insert_ID();
	}
	
	/**
	 * Löschen von mehrdimensionalen Varianten (Artikel Konfigurator)
	 * 
	 * @param int $articleID ID des Artikels (s_articles.id)
	 * @return bool
	 */
	function sDeleteArticleConfigurator ($articleID)
	{
		$articleID = $this->sGetArticleID($articleID);
		$delete_tables = array(
			"s_articles_groups",
			"s_articles_groups_option",
			"s_articles_groups_prices",
			"s_articles_groups_value",
			"s_articles_groups_settings"
		);
		foreach ($delete_tables as $delete_table) {
			$sql = "DELETE FROM $delete_table WHERE articleID = $articleID";
			$this->sDB->Execute($sql);
		}
		return true;
	}
	
	/**
	 * Einfügen von  mehrdimensionalen Varianten
	 *
	 * @param array $article
	 * @param array $article_configurator
	 * @return bool
	 */
	function sArticleConfigurator ($article, $article_configurator = null)
	{
		$article["articleID"] = $this->sGetArticleID($article);
		if(isset($article_configurator)) $article = array_merge($article,$article_configurator);
		$this->sDeleteArticleConfigurator((int)$article["articleID"]);
		if(empty($article["values"])&&empty($article["groups"])) return true;
		$sql = "SELECT DISTINCT isocode FROM s_core_multilanguage WHERE skipbackend=0 AND isocode!='de'";
		$languages = $this->sDB->GetCol($sql);
		$groupmap = array();
		$optionmap = array();
		$optiontranslations = array();
		if(isset($article["type"]))
		{
			$sql = 'INSERT INTO s_articles_groups_settings (articleID, type) VALUES (?, ?);';
			$this->sDB->Execute($sql,array($article['articleID'], $article['type']));
		}
		
		$groupID = 1;
		if(!empty($article["groups"]))
		foreach ($article["groups"] as $group)
		{
			if(is_string($group)) $group = array('name'=>$group);
			if(empty($group['name'])) continue;
			$name = $this->sDB->qstr($this->sValDescription($group['name']));
			$description = $this->sDB->qstr(empty($group['description']) ? '' : $group['description']);
			$position = empty($group['position']) ? 0 : (int) $group['position'];
			$image = $this->sDB->qstr(empty($group['image']) ? '' : $group['image']);
			$sql = "
					INSERT INTO `s_articles_groups` (
						articleID, groupID, groupname, groupdescription, groupposition, groupimage
					) VALUE (
						{$article['articleID']}, $groupID, $name, $description, $position, $image
					)
				";
			$this->sDB->Execute($sql);
			$groupmap[$group['name']] = $groupID;
			$groupID++;
		}

		if(!empty($article["values"]))
		foreach ($article["values"] as $value)
		{
			$value["standard"] = empty($value["standard"])? 0 : 1;
			$value["active"] = empty($value["active"])? 0 : 1;
			$value["instock"] = empty($value["instock"])? 0 : (int)$value["instock"];
			
			if(empty($value['ordernumber']))
				continue;
			$value['ordernumber'] = $this->sDB->qstr($value['ordernumber']);
			
			if(empty($value["attr"])||!is_array($value["attr"]))
			{
				$value["attr"] = array();
				for($i=1;$i<=10;$i++)
				{
					if(isset($value["attr$i"])&&!empty($value["attr$i"]))
						$value["attr"][$i] = $value["attr$i"];
					unset($value["attr$i"]);
				}
			}
			if(empty($value["attr"])&&(empty($value["option1"])||empty($value["group1"])))
				continue;
			
			if(empty($value["attr"]))
			{
				for($i=1;$i<=10;$i++)
				{
					if(empty($value["option$i"])||empty($value["group$i"]))
						break;
					$value["option$i"] = $this->sValDescription($value["option$i"]);
					$value["group$i"] = $this->sValDescription($value["group$i"]);
					if(!isset($groupmap[$value["group$i"]]))
					{
						$sql = "
							INSERT INTO `s_articles_groups` (
								articleID, groupID, groupname
							) VALUE (
								?, ?, ?
							)
						";
						$this->sDB->Execute($sql,array($article['articleID'], $groupID, $value["group$i"]));
						$groupmap[$value["group$i"]] = $groupID;
						$groupID++;
					}
					$value["attr"][$groupmap[$value["group$i"]]] = $value["option$i"];
				}
			}

			$tmp = array();
			foreach ($value["attr"] as $groupID=>$option)//alle Optionen durchgehen und überprüfen
			{
				$option = $this->sValDescription($option);
				if(!isset($optionmap[$groupID][$option]))
				{
					$sql = "
						INSERT INTO `s_articles_groups_option` (
							articleID, groupID, optionname
						) VALUE (
							?, ?, ?
						)
					";
					$this->sDB->Execute($sql,array($article['articleID'], $groupID, $option));
					$optionmap[$groupID][$option] = $this->sDB->Insert_ID();
				}
				$tmp[$groupID] = $optionmap[$groupID][$option];
			}
			$value["attr"] = $tmp;
			if(empty($value["attr"]))
				continue;
			
			foreach ($languages as $language)
			{
				if(isset($value['attr_'.$language]))
				foreach ($value["attr"] as $groupID => $optionID)
				{
					if(!empty($value['attr_'.$language][$groupID]))
					{
						$optiontranslations[$language][$optionID] = $value['attr_'.$language][$groupID];
					}
				}
			}
			
			$set1 = ", attr".implode(", attr",array_keys($value["attr"]));
			$set2 = ", ".implode(", ",$value["attr"]);
			$sql = "
				INSERT INTO `s_articles_groups_value` (
					articleID, ordernumber, standard, active, instock $set1
				) VALUE (
					{$article['articleID']}, {$value['ordernumber']}, {$value['standard']}, {$value['active']}, {$value['instock']} $set2
				)
			";
			$this->sDB->Execute($sql);
			$valueID = $this->sDB->Insert_ID();
			
			if(empty($value["prices"]))
				$value["prices"] = array();
			if(isset($value["net_price"]))
				$value["prices"][] = array("net_price"=>$value["net_price"]);
			elseif(isset($value["price"]))
				$value["prices"][] = array("price"=>$value["price"]);
			
			if(!empty($value["prices"])&&is_array($value["prices"]))
			foreach ($value["prices"] as $key=>$price)
			{
				if(is_scalar($price))
					$price = array('net_price'=>$price);
				if(empty($price['pricegroup']))
					$price['pricegroup'] = is_int($key) ? 'EK' : $key;
				if(!isset($price['net_price'])&&!isset($price['price']))
					continue;
				if(!isset($price['net_price']))
				{
					$price['net_price'] = $this->sValFloat($price['price']);
					if(!empty($article['tax']))
					{
						$price['net_price']  = $price['net_price']/(100+$article['tax'])*100;
					}
				}
				$price['net_price'] = $this->sValFloat($price['net_price']);
				$price['pricegroup'] = $this->sDB->qstr($price['pricegroup']);
				$sql = "
					REPLACE INTO `s_articles_groups_prices` (
						articleID, valueID, groupkey, price
					) VALUE (
						{$article['articleID']}, $valueID, {$price['pricegroup']}, {$price['net_price']}
					)
				";
				$this->sDB->Execute($sql);
			}
		}
		foreach ($languages as $language)
		{
			if(isset($article['groups_'.$language]))
				$this->sTranslation('configuratorgroup', $article["articleID"], $language, $article['groups_'.$language]);
			if(isset($optiontranslations[$language]))
				$this->sTranslation('configuratoroption', $article["articleID"], $language, $optiontranslations[$language]);
		}
	}
	
	/**
	 * Bild-Konvertierungsfunktion zur Generierung der Artikelbilder - Thumbnails
	 * 
	 * @param string $picture Dateipfad des Quell-Bildes
	 * @param int $new_width Breite des Thumbnails
	 * @param int $new_height Höhe des Thumbnails
	 * @param string $newfile Dateiname des Thumbnails 
	 * @access public
	 * @return
	 */
	function sResizePicture (&$image, $size, $new_width, $new_height)
	{		
		$breite=$size[0]; //die Breite des Bildes
		$hoehe=$size[1]; //die Höhe des Bildes

		// Verhältnis Breite zu Höhe bestimmen
		$verhaeltnis = $breite/$hoehe;

		if ($breite < $new_width){
			$breite_neu = $breite;
		} else {
			$breite_neu = $new_width;
		}
		
		$hoehe_neu = round($breite_neu / $verhaeltnis,0);

		$newImage = imagecreatetruecolor($breite_neu,$hoehe_neu); //Thumbnail im Speicher erstellen
		
		imagealphablending($newImage, false);
		imagesavealpha($newImage, true);

		imagecopyresampled($newImage,$image,0,0,0,0,$breite_neu,$hoehe_neu,$breite,$hoehe);
		
		return $newImage;
	}
	
	/**
	 * Bild-Konvertierungsfunktion zur Generierung der Artikelbilder - Thumbnails
	 * Berücksichtigt zusätzlich die Höhe des Bildes bzw. das Verhältnis zwischen Höhe
	 * und Breite um auch "Hochkant-Bilder" passend skalieren zu können (feste Höhe)
	 * 
	 * @param string $picture Dateipfad des Quell-Bildes
	 * @param int $new_width Breite des Thumbnails
	 * @param int $new_height Höhe des Thumbnails
	 * @param string $newfile Dateiname des Thumbnails 
	 * @access public
	 * @return
	 */
	function sResizePictureDynamic (&$image, $size, $new_width, $new_height)
	{
		$breite=$size[0]; //die Breite des Bildes
		$hoehe=$size[1]; //die Höhe des Bildes

		// Verhältnis Breite zu Höhe bestimmen

		if ($breite > $hoehe){
			$verhaeltnis = $breite/$hoehe;
			$breite_neu = $new_width;
			$hoehe_neu = round($breite_neu / $verhaeltnis,0);
		}else {
			$verhaeltnis = $hoehe/$breite;
			$hoehe_neu = $new_height;
			$breite_neu = round($hoehe_neu / $verhaeltnis,0);
		}

		$newImage = imagecreatetruecolor($breite_neu,$hoehe_neu); //Thumbnail im Speicher erstellen
		
		imagealphablending($newImage, false);
		imagesavealpha($newImage, true);

		imagecopyresampled($newImage,$image,0,0,0,0,$breite_neu,$hoehe_neu,$breite,$hoehe);
		
		return $newImage;
	}
	
	/**
	 * Einfügen von Artikelbildern
	 * Hinweis: Falls keine anderen Bilder hinterlegt sind, wird das zuerst eingefügte Bild automatisch zum Hauptbild
	 * 
	 * @param array $article_image Array mit Informationen über die Bilder die konvertiert und dem Artikel zugewiesen werden müssen
	 * 
	 * articleID => ID des Artikels (s_articles.id)
	 * 
	 * position => optional, Position des Bildes (fortlaufend 1 bis x)
	 * 
	 * descripton => optional, Title/Alt-Text des Bildes
	 * 
	 * main => Wenn 1 übergeben wird, wird das Bild zum Hauptbild (für Übersichten etc.)
	 * 
	 * image => Http-Link oder lokaler Pfad zum Bild
	 * 
	 * <code>
	 * 
	 * Beispiel Laden von lokalen Bildern:
	 *	foreach ($article["images"] as $image)
	 *   {
	 *    $image["articleID"] = $image["articleID"];
	 *    $image["main"] = $image["mainpicture"];
	 *    $image["image"] = $api->load("file://".$api->sPath."/engine/connectors/api/sample/images/".$image["file"]);
	 *    $import->sArticleImage($image);
	 *   }
	 * 
	 * Beispiel Laden von externen Bildern:
	 * foreach ($article["images"] as $image)
     *	{
     * 		$image["articleID"] = $image["articleID"];
     * 		$image["main"] = $image["mainpicture"];
     * 		$image["image"] = $api->load("ftp://login:passwort@example.org/httpdocs/dbase/images/articles/".$image["file"]);
     * 		$import->sArticleImage($image);
     *  }
	 *
	 * </code> 
	 * @access public
	 * @return
	 */
	function sArticleImage ($article_image = array())
	{
		if (empty($article_image)||!is_array($article_image))
			return false;
		if(isset($article_image["link"]))
			$article_image["image"] = $article_image["link"];
		if(isset($article_image['articleID']))
			$article_image['articleID'] = intval($article_image['articleID']);
		if(empty($article_image['articleID'])||(empty($article_image['image'])&&empty($article_image['name'])))
			return false;
		if(!empty($article_image['position']))
			$article_image['position'] = intval($article_image['position']);
		else 
			$article_image['position'] = 0;
		if(empty($article_image['description']))
			$article_image['description'] = "";
		$article_image['description'] = $this->sDB->qstr((string)$article_image['description']);
		if(empty($article_image['relations']))
			$article_image['relations'] = "";
		$article_image['relations'] = $this->sDB->qstr((string)$article_image['relations']);
		
		if(!empty($article_image['main'])&&$article_image['main']==1)
			$article_image['main'] = 1;
		elseif(!empty($article_image['main']))
			$article_image['main'] = 2;
		
		if(empty($article_image['name']))
		{
			$i = 0;
			do
			{
				$article_image['name'] =  md5(uniqid(mt_rand(), true));
				$sql = "SELECT id FROM s_articles_img WHERE img=".$this->sDB->qstr($article_image['name']);
				$row = $this->sDB->GetOne($sql);
				if(!empty($row))
					$article_image['name'] = false;
				$i++;
			}
			while (empty($article_image['name'])&&$i<10);
			if(empty($article_image['name']))
			{
				return false;
			}
		}
		
		$uploaddir = realpath($this->sPath.$this->sSystem->sCONFIG['sARTICLEIMAGES'])."/";
		
		if(!empty($article_image['image']))
		{
			$uploadfile = $uploaddir.$article_image['name'].'.tmp';
			if(!copy($article_image['image'], $uploadfile))
			{
				$this->sAPI->sSetError("Copy image from '{$article_image['image']}' to '$uploadfile' not work", 10400);
				return false;
			}
		}
		else
		{
			foreach (array('png', 'gif', 'jpg') as $test)
			{
				if (file_exists($uploaddir.$article_image['name'].'.'.$test))
				{
					$extension = $test;
					$uploadfile = $uploaddir.$article_image['name'].'.'.$test;
					break;
				}
			}
			if(empty($uploadfile))
			{
				$this->sAPI->sSetError("Image source '$uploadfile' not found", 10401);
				return false;
			}
		}
				
		$imagesize = getimagesize($uploadfile);
		if(empty($imagesize))
		{
			unlink($uploadfile);
			$this->sAPI->sSetError("File '$uploadfile' is not a image", 10402);
			return false;
		}
		$article_image['width'] = $imagesize[0];
		$article_image['height'] = $imagesize[1];
			
		if(!empty($article_image['image']))
		{			
			switch ($imagesize[2])
			{
				case IMAGETYPE_GIF:
					$extension = 'gif';
					$function = 'imagecreatefromgif';
					break;
				case IMAGETYPE_JPEG:
					$extension = 'jpg';
					$function = 'imagecreatefromjpeg';
					break;
				case IMAGETYPE_PNG:
					$extension = 'png';
					$function = 'imagecreatefrompng';
					break;
				default:
					break;
			}
			if(empty($function)||!function_exists($function))
			{
				unlink($uploadfile);
				$this->sAPI->sSetError("Image type are not supported", 10403);
				return false;
			}
			
			$image = $function($uploadfile);
			
			$rename = $uploaddir.$article_image['name'].'.'.$extension;
			rename($uploadfile, $rename);
			$uploadfile = $rename;
			
			if($extension!='jpg')
			{
				imagejpeg($image, $uploaddir.$article_image['name'].'.jpg', 100);
			}
			
			$sizes = explode(";",$this->sSystem->sCONFIG['sIMAGESIZES']);
			foreach ($sizes as $size)
			{
				list($width,$height,$suffix) = explode(':', $size);
				if (empty($height)) {
					$new_image = $this->sResizePicture($image, $imagesize, $width, 0);
				} else {
					$new_image = $this->sResizePictureDynamic($image, $imagesize, $width, $height);
				}
				$new_file_jpg = $uploaddir.$article_image['name']."_$suffix.jpg";
				$new_file = $uploaddir.$article_image['name']."_$suffix.".$extension;
				
				imagejpeg($new_image, $new_file_jpg, 90);
				switch($extension)
				{
					case 'gif':
						imagegif($new_image, $new_file);
						break;
					case 'png':
						imagepng($new_image, $new_file);
						break;
					default:
						break;
				}
				imagedestroy($new_image);
			}
			imagedestroy($image);
		}

		if(empty($article_image['main']))
		{
			$sql = "SELECT id FROM s_articles_img WHERE articleID={$article_image['articleID']} AND main=1";
			$row = $this->sDB->GetRow($sql);
			if(empty($row['id']))
				$article_image['main'] = 1;
			else
				$article_image['main'] = 2;
		}
		elseif ($article_image['main']==1)
		{
			$sql = "UPDATE s_articles_img SET main=2 WHERE articleID={$article_image['articleID']}";
			$this->sDB->Execute($sql);
		}
		$sql = "
			INSERT INTO 
				`s_articles_img` 
				(`articleID`, `img`, `main`, `description`, `position`, `width`, `height`, relations) 
			VALUES 
				({$article_image['articleID']}, '{$article_image['name']}', {$article_image['main']}, {$article_image['description']}, {$article_image['position']}, {$article_image['width']}, {$article_image['height']}, {$article_image['relations']})
		";
		if($this->sDB->Execute($sql)===false)
			return false;
			
		$insertID = $this->sDB->Insert_ID();
		
		$sql = 'UPDATE s_articles_img SET `extension`=? WHERE id=?';
		if($this->sDB->Execute($sql, array($extension, $insertID))===false)
			return false;
		
		return $insertID;
	}
		
	/**
	 * Löschen von einem Artikel zugeordneten Bildern
	 * 
	 * @param array $article_image 
	 * 
	 * $articles_image[articleID]
	 * 
	 * @access public
	 * @return
	 */
	function sDeleteArticleImages  ($article_image = array())
	{
		$articleID = $this->sGetArticleID($article_image);
		if(!isset($article_image["unlink"])||!empty($article_image["unlink"]))
		{
			$sql = '
				SELECT ai.img
				FROM s_articles_img ai
				LEFT JOIN s_articles_img ai2
				ON ai2.img=ai.img
				AND ai.articleID!='.$articleID.'
				WHERE ai.articleID='.$articleID.'
				AND ai2.id IS NULL
			';
			$cols = $this->sDB->GetCol($sql);
			$articles_images = realpath($this->sPath.$this->sSystem->sCONFIG['sARTICLEIMAGES']);
			if(!empty($cols))
			foreach ($cols as $image)
			{
				foreach (glob("$articles_images/$image*") as $image2)
				{
					if(file_exists($image2))
						@unlink($image2);
				}
			}
		}
		$sql = "DELETE FROM s_articles_img WHERE articleID=$articleID";
		$this->sDB->Execute($sql);
		return true;
	}
	
	/**
	 * Löschen aller nicht angegebenen Artikel-Bilder
	 * 
	 * @param array $article_image 
	 * 
	 * $articles_image[articleID]
	 * 
	 * @access public
	 * @return
	 */
	function sDeleteOtherArticleImages ($articleID, $imageIds = null)
	{
		$articleID = (int) $articleID;
		if(empty($articleID)) {
			return false;
		}
		
		if(!empty($imageIds)) {
			$imageIds = $this->sDB->qstr($imageIds);
			$sql = '
				SELECT ai.img
				FROM s_articles_img ai
				LEFT JOIN s_articles_img ai2
				ON ai2.img=ai.img
				AND (ai.articleID!='.$articleID.'
				OR ai2.id IN ('.$imageIds.'))
				WHERE ai.articleID='.$articleID.'
				AND ai.id NOT IN ('.$imageIds.')
				AND ai2.id IS NULL
			';
		} else {
			$sql = '
				SELECT ai.img
				FROM s_articles_img ai
				LEFT JOIN s_articles_img ai2
				ON ai2.img=ai.img
				AND ai.articleID!='.$articleID.'
				WHERE ai.articleID='.$articleID.'
				AND ai2.id IS NULL
			';
		}
		$images = $this->sDB->GetCol($sql);
		if($images === false) {
			return false;
		}
		
		if(!empty($images)) {
			$path = realpath($this->sPath . $this->sSystem->sCONFIG['sARTICLEIMAGES']);
			foreach ($images as $image) {
				foreach (glob("$path/$image*") as $file) {
					if(file_exists($file)) {
						@unlink($file);
					}
				}
			}
		}
		
		if(!empty($imageIds)) {
			$sql = 'DELETE FROM s_articles_img WHERE id NOT IN ('.$imageIds.') AND articleID='.$articleID;
		} else {
			$sql = 'DELETE FROM s_articles_img WHERE articleID='.$articleID;
		}
		$result = $this->sDB->Execute($sql);
		if($result === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Einfügen einer Artikel-Kategorie-Zuordnung
	 * 
	 * @param int $articleID ID des Artikels (s_articles.id)
	 * @param int $categoryID ID der Kategorie (s_categories.id)
	 * @access public
	 * @return array  $inserts Array mit allen eingefügten IDs aus s_articles_categories
	 */
	function sArticleCategory  ($articleID, $categoryID)
	{
		$inserts = array();
		$categoryID = intval($categoryID);
		$articleID = intval($articleID);
		if(empty($categoryID)||empty($articleID))
			return false;
		$categoryparentID = $categoryID;
		$parentID = $categoryID;
		$categories = array();
		while ($categoryID!=1 && !empty($categoryID))
		{
			$categories[] = $categoryID;
			$sql = "SELECT parent FROM s_categories WHERE id=$categoryID";
			$tmp = $this->sDB->GetOne($sql);
			$parentID = $categoryID;
			if (!empty($tmp)){
				$categoryID = (int) $tmp;
			} else {
				$categoryID = 1;
			}
		}
		$categories = implode(',', $categories);
		
		$sql = "
			INSERT INTO s_articles_categories (articleID, categoryID, categoryparentID)
			
			SELECT $articleID as articleID, c.id as categoryID,
				IF((SELECT 1 FROM `s_categories` WHERE parent=c.id LIMIT 1),c.parent, c.id) as categoryparentID
			FROM `s_categories` c
			WHERE c.id IN ($categories)
			
			ON DUPLICATE KEY UPDATE categoryparentID=VALUES(categoryparentID)
		";
		
		if($this->sDB->Execute($sql)===false)
			return false;
			
		$sql = "
			SELECT ac.id
			FROM `s_articles_categories` ac
			WHERE ac.categoryID IN ($categories)
			AND ac.articleID=$articleID
		";
		$inserts = $this->sDB->GetCol($sql);
			
		return $inserts;
	}
	
	/**
	 * Einfügen von Artikel-Kategorie-Zuordnungen für mehrere Kategorien gleichzeitig
	 * 
	 * @param array $article Muss einen der folgenden Werte beeinhalten
	 * 
	 * ordernumber => Bestellnummer des Artikels
	 * 
	 * articleID => Artikel-ID (s_articles.id)
	 * 
	 * articledetailsID => Artikel-Details-Id (s_articles_details.id)
	 * 
	 * @param array $categoryIDs Array mit den IDs einzufügenden Kategorien (s_categories.id)
	 * @return
	 */
	function sArticleCategories ($article, $categoryIDs)
	{
		if(!($articleID = $this->sGetArticleID($article)))
			return false;
		$inserts = array();
		if(!empty($categoryIDs)&&is_array($categoryIDs))
		{
			foreach ($categoryIDs as $categoryID)
			{
				$insert = $this->sArticleCategory  ($articleID, $categoryID);			
				if(!empty($insert))
					$inserts = array_merge($inserts, $insert);
			}
		}
		$this->sDeleteOtherArticlesCategories ($articleID, $inserts);
		return $inserts;		
	}
	
	/**
	 * Einfügen von Cross-Selling Zuordnungen zwischen Artikeln
	 * 
	 * @param int $article ID des Artikels (s_articles.id)
	 * @param array $relatedarticleIDs IDs der Artikel die $article zugeordnet werden sollen [x,y,z]
	 * @return
	 */
	function sArticleCrossSelling ($article, $relatedarticleIDs)
	{
		$articleID = $this->sGetArticleID($article);
		if(empty($articleID))
			return false;
		$this->sDeleteArticleCrossSelling(array("articleID"=>$articleID));
		if(empty($relatedarticleIDs)||!is_array($relatedarticleIDs))
			return true;
		
		foreach ($relatedarticleIDs as $relatedarticleID)
		{
			if(empty($relatedarticleID)) continue;
			$relatedarticleID = $this->sDB->qstr($relatedarticleID);
			$sql = "
				INSERT IGNORE INTO s_articles_relationships (articleID, relatedarticle)
				VALUES ($articleID, $relatedarticleID)
			";
			$this->sDB->Execute($sql);
		}
		return true;
	}
	
	/**
	 * Löschen aller bestehenden Cross-Selling Zuordnungen eines Artikels
	 * 
	 * @param int $article ID des Artikels (s_articles.id)
	 * @access public
	 * @return
	 */
	function sDeleteArticleCrossSelling ($article)
	{
		if(!$article = $this->sGetArticleID($article))
			return false;
		$sql = '
			DELETE FROM 
				s_articles_relationships
			WHERE articleID=?
		';
		$this->sDB->Execute($sql,array($article));
		return true;
	}
	/**
	* Einfügen von Zugriffs Beschränkunden auf einen Artikeln
	*
	* @param int $article ID des Artikels (s_articles.id)
	* @param array $nopermissiongroupIDs IDs der Kundengruppen keine Zugriff auf $article haben sollen [x,y,z]
	* @access public
	* @return
	*
	* @author Holger
	*/
	function sArticlePermissions ($article, $nopermissiongroupIDs)
	{
		$articleID = $this->sGetArticleID($article);
		if(empty($articleID))
			return false;

		$this->sDeleteArticlePermissions(array("articleID"=>$articleID));

		if(empty($nopermissiongroupIDs)||!is_array($nopermissiongroupIDs))
			return true;

		foreach ($nopermissiongroupIDs as $nopermissiongroupID)
		{
			if(empty($nopermissiongroupID)) continue;
			$nopermissiongroupID = $this->sDB->qstr($nopermissiongroupID);
			$sql = "
			INSERT INTO s_articles_avoid_customergroups (articleID, customergroupID)
			VALUES ($articleID, $nopermissiongroupID)
			";
			$this->sDB->Execute($sql);
		}
		return true;
	}

	/**
	* Löschen aller bestehenden Zugriffs Beschränkungen eines Artikels
	*
	* @param int $article ID des Artikels (s_articles.id)
	* @access public
	* @return
	*
	* @author Holger
	*/
	function sDeleteArticlePermissions ($article)
	{
		if(!$article = $this->sGetArticleID($article))
		return false;

		$sql = '
		DELETE FROM
		s_articles_avoid_customergroups
		WHERE articleID=?
		';
		$this->sDB->Execute($sql,array($article));
		
		return true;
	}
	/**
	 * Einfügen von Cross-Selling Zuordnungen zwischen Artikeln
	 * 
	 * @param int $article ID des Artikels (s_articles.id)
	 * @param array $relatedarticleIDs IDs der Artikel die $article zugeordnet werden sollen [x,y,z]
	 * @access public
	 * @return
	 */
	function sArticleSimilar ($article, $relatedarticleIDs)
	{
		$articleID = $this->sGetArticleID($article);
		if(empty($articleID))
			return false;
    	$this->sDeleteArticleSimilar(array("articleID"=>$articleID));
    	if(empty($relatedarticleIDs)||!is_array($relatedarticleIDs))
			return true;
			
		foreach ($relatedarticleIDs as $relatedarticleID)
		{
			if(empty($relatedarticleID)) continue;
			$relatedarticleID = $this->sDB->qstr($relatedarticleID);
			$sql = "
				INSERT IGNORE INTO s_articles_similar (articleID, relatedarticle)
				VALUES ($articleID, $relatedarticleID)
			";
			$this->sDB->Execute($sql);
		}
   		return true;
	}


	
	
	/**
	 * Löschen aller bestehenden Cross-Selling Zuordnungen eines Artikels
	 * 
	 * @param int $article ID des Artikels (s_articles.id)
	 * @access public
	 * @return
	 */
	function sDeleteArticleSimilar ($article)
	{
		if(!$article = $this->sGetArticleID($article))
			return false;
		$sql = '
			DELETE FROM 
				s_articles_similar
			WHERE articleID=?
		';
		$this->sDB->Execute($sql,array($article));
		return true;
	}
	
	/**
	 * Einfügen von Links (Weitere Informationen) zu einem Artikel
	 * 
	 * @param int $article ID des Artikels (s_articles.id)
	 * @param string $article_link Hyperlink
	 * @access public
	 * @return
	 */
	function sArticleLink ($article_link)
	{
		if(!($article_link["articleID"] = $this->sGetArticleID($article_link)))
			return false;
		if(empty($article_link)||!is_array($article_link)||empty($article_link['link'])||empty($article_link['description']))
			return false;
		if(empty($article_link['target']))
			$article_link['target'] = "_blank";
		$sql = "
			INSERT INTO s_articles_information (articleID, description, link, target)
			VALUES (?, ?, ?, ?)
		";
		$this->sDB->Execute($sql,array($article_link["articleID"],$article_link['description'],$article_link['link'],$article_link['target']));
		return $this->sDB->Insert_ID();
	}
	
	/**
	 * Löschen aller bestehenden Artikel-Links
	 * 
	 * @param int $article ID des Artikels (s_articles.id)
	 * @access public
	 * @return
	 */
	function sDeleteArticleLinks ($article)
	{
		$articleID = $this->sGetArticleID($article);
		$sql = 'SELECT id FROM s_articles_information WHERE articleID='.$articleID;
		$links = $this->sDB->GetCol($sql);
		if(!empty($links))
			$this->sDeleteTranslation('link',$links);
		$sql = 'DELETE FROM s_articles_information WHERE articleID='.$articleID;
		$this->sDB->Execute($sql);
		return true;
	}
	
	/**
	 * Deletes not specified article category relations
	 * 
	 * @param int $article ID des Artikels (s_articles.id)
	 * @param array $categoryIDs
	 * @return
	 */
	function sDeleteOtherArticlesCategories ($articleID, $categoryIDs)
	{
		$articleID = intval($articleID);
		if (empty($articleID))
			return false;
		if(!empty($categoryIDs)&&is_array($categoryIDs))
		{
			$where =  "AND id!=".implode(" AND id!=",$categoryIDs)."";
		} elseif (!empty($categoryIDs))	{
			$where =  "AND id!=".intval($categoryIDs);
		} else {
			$where = "";
		}
		$sql = "
			DELETE FROM 
				s_articles_categories
			WHERE articleID=$articleID $where
		";
		$this->sDB->Execute($sql);
	}
	
	/**
	 * Deletes not specified categories
	 * 
	 * @param array $categoryIDs
	 * @access public
	 * @return
	 */
	function sDeleteOtherCategories ($categoryIDs)
	{
		if (empty($categoryIDs)||!is_array($categoryIDs))
			return false;
		
		$sql = "
			DELETE FROM s_categories
			WHERE id NOT IN (".implode(",",$categoryIDs).")
		";
		$this->sDB->Execute($sql);
		$sql = "
			DELETE ac FROM s_articles_categories ac
			LEFT JOIN s_categories c
			ON c.id=ac.categoryID
			LEFT JOIN s_articles a
			ON  a.id=ac.articleID
			WHERE c.id IS NULL
			OR a.id IS NULL
		";
		$this->sDB->Execute($sql);
	}
	
	/**
	 * Einfügen von Herstellern
	 * @param array $supplier 
	 * 
	 * supplier => Name des Herstellers
	 * 
	 * @access public
	 * @return int ID des eingefügten / aktualisierten Herstellers
	 */
	function sSupplier ($supplier)
	{	
		if(empty($supplier)||!is_array($supplier))
			return false;
		if(empty($supplier['supplier'])&&empty($supplier['supplierID']))
			return false;
			
		if(isset($supplier['supplier']))
			$supplier['supplier'] = $this->sDB->qstr($this->sValDescription($supplier['supplier']));
				
		if(empty($supplier['supplierID']))
		{			
			$sql = "SELECT id, img, link FROM s_articles_supplier WHERE name = {$supplier['supplier']}";
			$row = $this->sDB->GetRow($sql);
			if(empty($row['id']))
			{
				$supplier['supplierID'] = 0;
				$supplier['old_image'] = "";
				$supplier['old_link'] = "";
			}
			else
			{
				$supplier['supplierID'] = $row['id'];
				$supplier['old_image'] = $row['img'];
				$supplier['old_link'] = $row['link'];
			}
		}
		else
		{
			$supplier['supplierID'] = intval($supplier['supplierID']);
			$sql = "SELECT id, img, link, name FROM s_articles_supplier WHERE id = {$supplier['supplierID']}";
			$row = $this->sDB->GetRow($sql);
			if(empty($row['id']))
			{
				$supplier['supplierID'] = 0;
				$supplier['old_image'] = "";
				$supplier['old_link'] = "";
				$supplier['old_supplier'] = "";
			}
			else
			{
				$supplier['supplierID'] = $row['id'];
				$supplier['old_image'] = $row['img'];
				$supplier['old_link'] = $row['link'];
				$supplier['old_supplier'] = $row['name'];
			}
		}
				
		if(!isset($supplier['supplier']))
		{
			$supplier['supplier'] = $this->sDB->qstr($supplier['old_supplier']);
			unset($supplier['old_supplier']);
		}
		
		if(empty($supplier['supplier'])&&empty($supplier['supplierID']))
			return false;
		
		if(!isset($supplier['link']))
		{
			$supplier['link'] = $supplier['old_link'];
			unset($supplier['old_link']);
		}
		
		$supplierimages = realpath($this->sPath.$this->sSystem->sCONFIG['sSUPPLIERIMAGES'])."/";
		if(!empty($supplier['old_image'])&&isset($supplier['image']))
		{
			unlink($supplierimages . $supplier['old_image' ]. ".jpg");
			unset($supplier['old_image']);
		}
		
		if(!empty($supplier['image']))
		{
			$size = @getimagesize($supplier['image']);
			if(!empty($size[2])&&$size[2]==2)
			{
				$supplier['image'] = md5(uniqid(rand()));
				if (copy($supplier['image'], $supplierimages.$supplier['image'].".jpg")) {
					chmod($supplierimages.$supplier['image'].".jpg",$this->sSettings["chmod"]);
				}
				else 
				{
					$supplier['image'] = "";
				}
			}
			else 
			{
				$supplier['image'] = "";
			}
		}
		elseif(!empty($supplier['old_image'])&&!isset($supplier['image']))
		{
			$supplier['image'] = $supplier['old_image'];
		}
		else 
		{
			$supplier['image'] = "";
		}
		
		$supplier['link'] = $this->sDB->qstr($supplier['link']);
		$supplier['image'] = $this->sDB->qstr($supplier['image']);
		
		if(empty($supplier['supplierID']))
		{
			$sql = "
				INSERT INTO `s_articles_supplier` (name, img, link) 
				VALUES ({$supplier['supplier']}, {$supplier['image']}, {$supplier['link']})
			";
			$this->sDB->Execute($sql);
			$supplier['supplierID'] = $this->sDB->Insert_ID();
		}
		else 
		{
			$sql = "
				UPDATE 
					s_articles_supplier 
				SET 
					name = {$supplier['supplier']},
					img = {$supplier['image']},
					link = {$supplier['link']}
				WHERE 
					id = {$supplier['supplierID']} 
			";
			$this->sDB->Execute($sql);
		}
		
		return $supplier['supplierID'];
	}
	
	/**
	 * Aktualisierung von Lagerbeständen
	 * @param array $article_stock Array mit folgenden Optionen
	 * 
	 * articledetailsID => Detail-ID des Artikels (s_articles_details.id) oder
	 * 
	 * articleID => ID des Artikels (s_articles.id) oder
	 * 
	 * ordernumber => Bestellnummer des Artikels (s_articles_details.ordernumber)
	 * 
	 * instock => Lagerbestand 
	 * 
	 * stockmin => Mindestlagerbestand
	 * 
	 * shippingtime => Lieferzeit des Artikels in Tagen
	 * 
	 * 
	 * <code>
	 * $import->sArticleStock(array("articleID"=>1,"instock"=>10,"stockmin"=>5,"shippingtime"=>5));
	 * </code>
	 * 
	 * @access public
	 * @return 
	 */
	function sArticleStock ($article_stock = array())
	{
		if(!empty($article_stock['articledetailsID']))
			$where = 'd.id='.intval($article_stock['articledetailsID']);
		elseif(!empty($article_stock['ordernumber']))
			$where = 'd.ordernumber='.$this->sDB->qstr($article_stock['ordernumber']);
		elseif (!empty($article_stock['articleID']))
			$where = 'd.kind=1 AND a.id='.intval($article_stock['articleID']);
		else 
			return false;
			
		$sql = array();
		if(isset($article_stock['instock']))
			$sql[] = "d.instock=".intval($article_stock['instock']);
		if(isset($article_stock['stockmin']))
			$sql[] = "d.stockmin=".intval($article_stock['stockmin']);
		if(isset($article_stock['active']))
			$sql[] = "a.active=IF(d.kind!=1,a.active,".intval($article_stock['active']).")";
		if(isset($article_stock['active']))
			$sql[] = "d.active=".intval($article_stock['active']);
		if(isset($article_stock['shippingtime']))
			$sql[] = "a.shippingtime=IF(d.kind!=1,a.shippingtime,".$this->sDB->qstr($article_stock['shippingtime']).")";
		if(isset($article_stock['laststock']))
			$sql[] = "a.laststock=IF(d.kind!=1,a.laststock,". (empty($article_stock['laststock']) ? 0 : 1) .")";;
		$sql[] = "a.changetime=".$this->sDB->sysTimeStamp;
		$sql = implode(", ",$sql);
		$sql = "
			UPDATE s_articles a, s_articles_details d
			SET	$sql
			WHERE $where
			AND d.articleID=a.id
		";
		$result = $this->sDB->Execute($sql);
		if ($result === false)
			return false;
		return true;		
	}
	
	/** 
	 * Delete article method
	 * 
	 * @param mixed $article
	 * @return bool
	 */
	function sDeleteArticle ($article)
	{
		if(is_int($article))
			$article = array("articleID"=>$article);
		elseif(is_string($article)) 
			$article = array("ordernumber"=>$article);
		
		if(!empty($article["articleID"]))
		{
			$article["articleID"] = intval($article["articleID"]);
			$article["kind"] = 1;
			$article["articledetailsID"] = $this->sDB->GetOne("SELECT id FROM s_articles_details WHERE articleID={$article['articleID']}");
			if(empty($article["articledetailsID"]))
				return false;
		}
		else 
		{
			if(!empty($article["articledetailsID"])&&$article["articledetailsID"]=intval($article["articledetailsID"]))
				$article = $this->sDB->GetRow("SELECT id as articledetailsID, ordernumber, articleID, kind FROM s_articles_details WHERE id={$article['articledetailsID']}");
			elseif(!empty($article["ordernumber"])&&$article["ordernumber"]=$this->sDB->qstr($article["ordernumber"]))
				$article = $this->sDB->GetRow("SELECT id as articledetailsID, ordernumber, articleID, kind FROM s_articles_details WHERE ordernumber={$article['ordernumber']}");
			else 
				return false;
			if(empty($article))
				return false;
			if($this->sDB->GetOne("SELECT COUNT(id) FROM s_articles_details WHERE articleID={$article['articleID']}")<=1)
				$article["kind"] == 1;
		}
		if($article["kind"] == 1)
		{
			$this->sDeleteArticleImages($article);
			$this->sDeleteArticleConfigurator($article);
			$this->sDeleteArticleDownloads($article);
			$this->sDeleteArticleLinks($article);
			$this->sDeleteArticlePermissions($article);
			
			$sql = "DELETE FROM s_articles WHERE id = {$article['articleID']}";
			$this->sDB->Execute($sql);
			
			$sql = "SELECT id FROM s_articles_esd WHERE articleID = {$article['articleID']}";
			$esdIDs = $this->sDB->GetCol($sql);
			if(!empty($esdIDs))
			{
				$sql = "DELETE FROM s_articles_esd_serials WHERE esdID=".implode(" OR esdID=",$esdIDs);
				$this->sDB->Execute($sql);
			}
			$delete_tables = array(
				"s_articles_details",
				"s_articles_attributes",
				"s_articles_esd",
				"s_articles_prices",
				"s_articles_relationships",
				"s_articles_similar",
				"s_articles_vote",
				"s_articles_categories",
				"s_articles_translations",
				"s_export_articles",
				"s_filter_values",
				"s_articles_groups_accessories_option",
				"s_articles_groups_accessories",
				"s_emarketing_lastarticles",
				"s_articles_translations",
				"s_articles_avoid_customergroups"
			);
			foreach ($delete_tables as $delete_table) {
				$sql = "DELETE FROM $delete_table WHERE articleID={$article['articleID']}";
				$this->sDB->Execute($sql);
			}
			$this->sDeleteTranslation(array('article','configuratoroption','configuratorgroup','accessoryoption','accessorygroup','properties'),$article['articleID']);
			
			$sql = 'DELETE FROM s_core_rewrite_urls WHERE org_path=?';
			$this->sDB->Execute($sql, array('sViewport=detail&sArticle=' . $article['articleID']));
		}
		else 
		{
			$sql = "
				DELETE ae, aes
				FROM s_articles_esd ae
				LEFT JOIN s_articles_esd_serials aes
				ON ae.id=aes.esdID
				WHERE articledetailsID = {$article['articledetailsID']}
			";
			$this->sDB->Execute($sql);
	
			$sql = "DELETE FROM s_articles_details WHERE id = {$article['articledetailsID']}";
			$this->sDB->Execute($sql);
						
			$delete_tables = array(
				"s_articles_attributes",
				"s_articles_esd",
				"s_articles_prices"
			);
			foreach ($delete_tables as $delete_table) {
				$sql = "DELETE FROM $delete_table WHERE articledetailsID = {$article['articledetailsID']}";
				$this->sDB->Execute($sql);
			}
		}
		$delete_tables = array(
			"s_articles_similar"=>"relatedarticle",
			"s_articles_relationships"=>"relatedarticle",
			"s_emarketing_promotion_articles"=>"articleordernumber",
			"s_emarketing_promotions"=>"ordernumber"
		);
		foreach ($delete_tables as $delete_table => $delete_row) {
			$sql = "DELETE FROM $delete_table WHERE $delete_row=?";
			$this->sDB->Execute($sql,array($article['ordernumber']));
		}
		$this->sDeleteTranslation('objectkey',$article['articledetailsID']);
		return true;
	}
	
	/** 
	 * Deaktiviert einen Artikel
	 * 
	 * @param int $article ID aus s_articles.id
	 * @access public
	 * @return
	 */
	function sDeactivateArticle ($article)
	{
		$article = $this->sGetArticleID($article);
		$sql = 'UPDATE s_articles SET active=0 WHERE id='.$article;
		return $this->sDB->Execute($sql);
		 
	}
	
	/** 
	 * Löschen aller nihct angegebenen Artikel
	 * 
	 * @param array $article Array mit Artikel-IDs (s_articles.id)
	 * @access public
	 * @return
	 */
	function sDeleteOtherArticles ($articles = array())
	{
		
		if (empty($articles['articledetailsIDs'])&&empty($articles['articleIDs']))
		{
			if(!empty($articles)&&is_array($articles))	
				$articles['articledetailsIDs'] = $articles;
			else
				return false;
		}
		if(!empty($articles['articleIDs'])&&is_array($articles['articleIDs']))
		{
			$where =  "a.id NOT IN (".implode(",",$articles['articleIDs']).")";
		}
		else 
		{
			$where =  "d.id NOT IN (".implode(",",$articles['articledetailsIDs']).")";
		}
		$sql = "
			SELECT 
				d.id 
			FROM 
				s_articles a,
				s_articles_details d
			WHERE d.articleID = a.id
			AND ($where)
		";
		$result = $this->sDB->GetCol($sql);
		if ($result === false)
			return false;
		foreach ($result as $articleID)
			$this->sDeleteArticle(array("articledetailsID"=>$articleID));
		return true;
	}
	
	/** 
	 * Alle Artikel löschen (z.B. bei komplettem Neu-Import notwendig)
	 * 
	 * @return
	 */
	function sDeleteAllArticles ()
	{
		
		$articles_images = $this->sPath.$this->sSystem->sCONFIG['sARTICLEIMAGES'];
		if(!$this->sDeleteAllFiles($articles_images))
			return false;
		$articles_files = $this->sPath.$this->sSystem->sCONFIG['sARTICLEFILES'];
		if(!$this->sDeleteAllFiles($articles_files))
			return false;
		$tabellen = array(
			"s_articles",
			"s_articles_attributes",
			"s_articles_categories",
			"s_articles_details",
			"s_articles_downloads",
			"s_articles_esd",
			"s_articles_esd_serials",
			"s_articles_groups",
			"s_articles_groups_accessories",
			"s_articles_groups_accessories_option",
			"s_articles_groups_option",
			"s_articles_groups_prices",
			"s_articles_groups_value",
			"s_articles_img",
			"s_articles_information",
			"s_articles_prices",
			"s_articles_relationships",
			"s_articles_similar",
			"s_articles_vote",
			"s_articles_translations",
			"s_export_articles",
			"s_filter_values",
			"s_articles_groups_settings",
			"s_filter_values",
			"s_core_rewrite_urls",
			"s_articles_avoid_customergroups"
		);
		foreach($tabellen as $tabelle) {
			$this->sDB->Execute("TRUNCATE `$tabelle`;");
		}
		$sql = "DELETE FROM s_core_translations WHERE objecttype IN ('article','variant','configuratoroption','configuratorgroup','accessoryoption','accessorygroup','properties','link')";
		$this->sDB->Execute($sql);
		return true;
	}
	
	/** 
	 * Alle Kategorien löschen (z.B. bei komplettem Neu-Import notwendig)
	 * 
	 * @return
	 */
	function sDeleteAllCategories()
	{
		$sql = "SELECT parentID FROM s_core_multilanguage";
		$cols = $this->sDB->GetCol($sql);
		if(empty($cols))
		{
			$sql = "TRUNCATE s_categories";
		} else {
			$cols = "id!=".implode(" AND id!=",$cols);
			$sql = "DELETE FROM s_categories WHERE $cols";
		}
		$result = $this->sDB->Execute($sql);
		if ($result === false)
			return false;
		
		if ($this->sDB->Execute("TRUNCATE s_articles_categories") === false)
			return false;	
		if ($this->sDB->Execute("TRUNCATE s_emarketing_banners") === false)
			return false;
		if ($this->sDB->Execute("TRUNCATE s_emarketing_promotions") === false)
			return false;
			
		$sql = "SELECT MAX(parentID) FROM  s_core_multilanguage";
		$result = $this->sDB->GetOne($sql);
		if(empty($result))
			$auto_increment = 2;
		else 
			$auto_increment = $result+1;
		$auto_increment = max($auto_increment,10);
		$sql = "ALTER TABLE s_categories AUTO_INCREMENT = $auto_increment;";
		$result = $this->sDB->Execute($sql);
		if ($result === false)
			return false;
		return true;
	}
	
	/** 
	 * Alle Dateien in einem Verzeichnis löschen
	 * 
	 * @param string $dir Verzeichnis
	 * @param bool $rek Rekursives löschen
	 * @return
	 */
	function sDeleteAllFiles ($dir, $rek=false)
	{
		if(empty($dir)||!file_exists($dir)||!is_readable($dir))
			return false;
		
		$dir = realpath($dir)."/";
				
		$dh = opendir($dir);
		if(!$dh)
			return false;
		while (($file = readdir($dh)) !== false) {
			if($file=="."||$file=="..")
				continue;
	    	if(is_dir($dir . $file))
	    	{
	    		if($rek)
	    		{
		    		if(!$this->sDeleteAllFiles($dir . $file, $rek))
		    			continue;
		    		if(!rmdir($dir . $file))
		    			continue;
	    		}
	    	}
	    	elseif(is_file($dir . $file))
	    	{
	    		if(!unlink($dir . $file))
	    			continue;
	    	}
	    }
	    closedir($dh);
	    return true;
	}
		
	/** 
	 * Preis formatiert zurückgeben
	 * 
	 * @param double $price
	 * @access public
	 * @return
	 */
	function sFormatPrice ($price = 0)
	{
		return number_format($price,2,$this->sSettings['dec_point'],'');
	}
	
	/** 
	 * Komma durch Punkt als Dezimaltrennzeichen ersetzen
	 * 
	 * @param double $price
	 * @access public
	 * @return $price 
	 */
	function sValFloat ($value)
	{
		return floatval(str_replace(",",".",$value));
	}
	
	/**
	 * Clear description method
	 *
	 * @param string $description
	 * @return string
	 */
	function sValDescription($description)
	{
		$description = html_entity_decode($description);
		$description = preg_replace('!<[^>]*?>!', ' ', $description);
		$description = str_replace(chr(0xa0), " ", $description);
		$description = preg_replace('/\s\s+/', ' ', $description);
		$description = htmlspecialchars($description);
		$description = trim($description);
		return $description;
	}
	
	/** 
	 * Details-ID (s_articles_details.id) eines Artikels zurückgeben
	 * 
	 * @param array $article
	 * 
	 * articleID => s_articles.id 
	 * 
	 * @access public
	 * @return Details-ID des Artikels oder FALSE
	 */
	function sGetArticledetailsID ($article)
	{
		if(empty($article['articleID'])&&empty($article['articledetailsID'])&&empty($article['ordernumber']))
			return false;
		if(!empty($article['articledetailsID']))
		{
			$article['articledetailsID'] = intval($article['articledetailsID']);
			$sql = "id = {$article['articledetailsID']}";
		}
		elseif(!empty($article['articleID']))
		{
			$article['articleID'] = intval($article['articleID']);
			$sql = "articleID = {$article['articleID']} AND kind = 1";
		}
		else
		{
			$article['ordernumber'] = $this->sDB->qstr((string)$article['ordernumber']);
			$sql = "ordernumber = {$article['ordernumber']}";
		}
		$sql = "SELECT id FROM s_articles_details WHERE $sql";
		$row = $this->sDB->GetRow($sql);
		if(empty($row['id']))
			return false;
		return $row['id'];
	}
	
	/** 
	 * ID (s_articles.id) eines Artikels zurückgeben z.B. anhand der Bestellnummer
	 * 
	 * @param mixed $article
	 * @return Details-ID des Artikels oder FALSE
	 */
	function sGetArticleID ($article)
	{
		if(empty($article))
			return false;
		if(is_string($article))
			$article = array("ordernumber"=>$article);
		if(is_int($article))
			$article = array("articleID"=>$article);
		if(!is_array($article))
			return false;
		if(empty($article['articleID'])&&empty($article['articledetailsID'])&&empty($article['ordernumber']))
			return false;
		$sql = "SELECT articleID FROM s_articles_details WHERE ";
		if(!empty($article['articleID']))
		{
			$article['articleID'] = intval($article['articleID']);
			$sql .= "articleID = {$article['articleID']}";
		}
		elseif(!empty($article['articledetailsID']))
		{
			$article['articledetailsID'] = intval($article['articledetailsID']);
			$sql .= "id = {$article['articledetailsID']}";
		}
		else
		{
			$article['ordernumber'] = $this->sDB->qstr((string)$article['ordernumber']);
			$sql .= "ordernumber = {$article['ordernumber']}";
		}
		$row = $this->sDB->GetRow($sql);
		if(empty($row['articleID']))
			return false;
		return $row['articleID'];
	}
	
	/** 
	 * Return article numbers
	 * 
	 * @param mixed $article
	 * @return 
	 */
	function sGetArticleNumbers ($article)
	{
		if(empty($article['articleID'])&&empty($article['articledetailsID'])&&empty($article['ordernumber']))
			return false;
		if(!empty($article['articledetailsID']))
		{
			$article['articledetailsID'] = intval($article['articledetailsID']);
			$sql = "id = {$article['articledetailsID']}";
		}
		elseif(!empty($article['articleID']))
		{
			$article['articleID'] = intval($article['articleID']);
			$sql = "articleID = {$article['articleID']} AND kind = 1";
		}
		else
		{
			$article['ordernumber'] = $this->sDB->qstr((string)$article['ordernumber']);
			$sql = "ordernumber = {$article['ordernumber']}";
		}
		$sql = "SELECT id as articledetailsID, ordernumber, articleID FROM s_articles_details WHERE $sql";
		$row = $this->sDB->GetRow($sql);
		if(empty($row['articledetailsID']))
			return false;
		return $row;
	}
	
	/** 
	 * Gibt die Details-ID (s_articles_details) eines Hauptartikels zurück
	 * 
	 * @param mixed $article
	 * @access public
	 * @return Details-ID des Artikels oder FALSE
	 */
	function sGetMainArticleID ($article)
	{
		if(!$articleID= $this->sGetArticleID ($article))
				return false;
		$sql = "
			SELECT id
			FROM s_articles_details 
			WHERE kind = 1
			AND
			articleID = $articleID
		";
		$row = $this->sDB->GetRow($sql);
		if(empty($row['id']))
			return false;
		return $row['id'];
	}
	
	/**
	 * Delete shop cache
	 */
	function sDeleteCache ()
	{
		foreach (glob($this->sPath."/cache/templates*", GLOB_ONLYDIR) as $cachedir)
		{
		    $this->sDeleteAllFiles ($cachedir, true);
		}
		$this->sDeleteAllFiles ($this->sPath."/engine/vendor/html2ps/cache/", true);
		$this->sDeleteArticleCache();
	}
	
	/**
	 * Delete article cache
	 */
	function sDeleteArticleCache()
	{
		$this->sDeleteAllFiles ($this->sPath."/cache/database/", true);
		$this->sDeleteAllFiles ($this->sPath."/cache/vars/", true);
		//$this->sDeleteAllFiles ($this->sPath."/files/article_pdf/", true);
	}
		
	/**
	 * Delete empty categories
	 */
	function sDeleteEmptyCategories ()
	{
		$sql = "
			DELETE ac FROM s_articles_categories ac
			LEFT JOIN s_categories c
			ON c.id=ac.categoryID
			LEFT JOIN s_articles a
			ON  a.id=ac.articleID
			WHERE c.id IS NULL
			OR a.id IS NULL
		";
		$this->sDB->Execute($sql);
		$sql = "
			DELETE c FROM s_categories c
			LEFT JOIN s_articles_categories ac
			ON ac.categoryID=c.id
			WHERE ac.id IS NULL
			AND c.id NOT IN (SELECT parentID FROM s_core_multilanguage)
		";
		$this->sDB->Execute($sql);
	}
	
	/**
	 * Delete given categories
	 */
	function sDeleteCategories ($categories)
	{
		//@@TODO: nach sDeleteCategory verschieben
		if(empty($categories)||!is_array($categories))
			return false;
		
		$where = 'id IN ('.implode(',',$categories).')';
		$sql = "DELETE FROM s_categories WHERE $where;";
		if($this->sDB->Execute($sql)===false)
			return false;

		$where = 'categoryID IN ('.implode(',',$categories).')';
		$sql = "DELETE FROM s_articles_categories WHERE $where;";
		if($this->sDB->Execute($sql)===false)
			return false;
		return true;
	}
	
	/**
	 * Repair article category relations
	 */
	function sRepairArticleCategories()
	{
		$sql = "SELECT articleID, categoryID FROM s_articles_categories WHERE categoryID=categoryparentID";
		$article_categories = $this->sDB->GetAll($sql);
		$this->sDB->Execute("TRUNCATE TABLE s_articles_categories");
		foreach ($article_categories as $article_category)
			$this->sArticleCategory ($article_category["articleID"], $article_category["categoryID"]);
	}
		
	/**
	 * Delete translation method
	 *
	 * @param string $type
	 * @param string $objectkey
	 * @param string $language
	 * @return bool
	 */
	function sDeleteTranslation ($type, $objectkey = null, $language = null)
	{
		if(empty($type))
		{
			return false;
		}
		elseif(is_array($type))
		{
			foreach ($type as &$value)
			{
				$value = $this->sDB->qstr($value);
			}
			$type = implode(',',$type);
		}
		else
		{
			$type = $this->sDB->qstr($type);
		}
		$sql = 'DELETE FROM s_core_translations WHERE objecttype IN ('.$type.')';
		if(!empty($objectkey))
		{
			if(is_array($objectkey))
			{
				foreach ($objectkey as &$value)
				{
					$value = $this->sDB->qstr($value);
				}
				$objectkey = implode(',',$objectkey);
			}
			else 
			{
				$objectkey = $this->sDB->qstr($objectkey);
			}
			$sql .=  ' AND objectkey IN ('.$objectkey.')';
		}
		if(!empty($language))
		{
			$sql .=  ' AND objectlanguage='.$this->sDB->qstr($language);
		}
		$result = $this->sDB->Execute($sql,array($type));
		return (bool) $result;
	}
	
	/**
	 * Insert or update an translation
	 *
	 * @param unknown_type $type
	 * @param unknown_type $objectkey
	 * @param unknown_type $language
	 * @param unknown_type $data
	 * @return unknown
	 */
	function sTranslation ($type, $objectkey, $language, $data)
	{
		if(empty($type)||empty($objectkey)||empty($language))
			return false;
		if(empty($data))
			return $this->sDeleteTranslation($type, $objectkey, $language);
		switch ($type)
		{
			case "article":
			case "variant":
				if($type== "article")
				{
					$map = array(
						"txtArtikel"=>"name",
						"txtshortdescription"=>"description",
						"txtlangbeschreibung"=>"description_long",
						"txtzusatztxt"=>"additionaltext",
						"txtkeywords"=>"keywords",
					);
				}
				else 
				{
					$map = array(
						"txtzusatztxt"=>"additionaltext"
					);
				}
				$tmp = array();
				foreach ($map as $key => $name)
				{
					if(isset($data[$key]))
						$tmp[$key] = $data[$key];
					elseif(isset($data[$name."_".$language]))
						$tmp[$key] = $data[$name."_".$language];
					elseif(isset($data[$name]))
						$tmp[$key] = $data[$name];
					if(empty($tmp[$key])) unset($tmp[$key]);
				}
				for ($i=1;$i<=20;$i++)
				{
					if(!empty($data["attr{$i}_$language"]))
						$tmp["attr$i"] = $data["attr{$i}_$language"];
					elseif(!empty($data["attr$i"]))
						$tmp["attr$i"] = $data["attr$i"];
				}
				if(isset($tmp["txtArtikel"])) $tmp["txtArtikel"] = $this->sValDescription($tmp["txtArtikel"]);
				if(isset($tmp["txtzusatztxt"])) $tmp["txtzusatztxt"] = $this->sValDescription($tmp["txtzusatztxt"]);
				$data = $tmp;
				break;
			case "configuratoroption":
				if(!empty($data)&&is_array($data))
				foreach ($data as $key => &$value)
				{
					if(is_array($value)&&isset($value['gruppenName']))
						$value = $value['gruppenName'];
					if(!is_string($value)||!is_int($key))
					{
						unset($data[$key]);
						continue;
					}
					$value = array('optionName'=>$this->sValDescription($value));
				}
				else $data = false;
				break;
			case "configuratorgroup":
				if(!empty($data)&&is_array($data))
				foreach ($data as $key => &$value)
				{
					if(is_string($value))
						$value = array('gruppenName'=>$value);
					if(!is_array($value)||!is_int($key)||$key<1||$key>10)
					{
						unset($data[$key]);	continue;
					}
					if(isset($value['gruppenName']))						
						$value['gruppenName'] = $this->sValDescription($value['gruppenName']);
					$this->sValDescription($value);
				}
				else $data = false;
				break;
			case "accessorygroup":
				//1 => accessoryName
				break;
			case "accessoryoption":
				//1 => accessoryoption
				break;
			case "properties":
				if(!empty($data)&&is_array($data))
				{
					foreach ($data as $key => &$value)
					{
						if(!is_int($key)) unset($data[$key]);
						else $value = $this->sValDescription($value);
					}
				}
				else
				{
					$data = false;
				}
				break;
			case "link":
				if(is_scalar($data))
					$data = array("linkname"=>$this->sValDescription($data));
				elseif(isset($data["description"]))
					$data = array("linkname"=>$this->sValDescription($data["description"]));
				else 
					$data = false;
				break;
			case "download":
				if(is_scalar($data))
					$data = array("downloadname"=>$this->sValDescription($data));
				elseif(isset($data["description"]))
					$data = array("downloadname"=>$this->sValDescription($data["description"]));
				else 
					$data = false;
				break;
			default:
				break;
		}
		if(empty($data))
			return $this->sDeleteTranslation($type, $objectkey, $language);
		
		$type = $this->sDB->qstr((string)$type);
		$objectkey = $this->sDB->qstr((string)$objectkey);
		$language = $this->sDB->qstr((string)$language);
		$data = $this->sDB->qstr(serialize($data));
		$sql = "SELECT id FROM s_core_translations WHERE objecttype=$type AND objectkey=$objectkey AND objectlanguage=$language";
		$id = $this->sDB->GetOne($sql);
		if(empty($id))
		{
			$sql = "
				INSERT INTO `s_core_translations` 
					(objecttype, objectdata, objectkey, objectlanguage)
				VALUES
					($type, $data, $objectkey, $language)
			";
			$result = $this->sDB->Execute($sql);
			if(empty($result))
				return false;
			else 
				return $this->sDB->Insert_ID();
		}
		else
		{
			$sql = "
				UPDATE s_core_translations
				SET	objectdata = $data
				WHERE id=$id
			";
			$result = $this->sDB->Execute($sql);
			if(empty($result))
				return false;
			else 
				return $id;
		}
	}
	
	/**
	 * Delete article downloads
	 *
	 * @param array $article_download
	 * @return bool
	 */
	function sDeleteArticleDownloads ($article_download)
	{
		$articleID = $this->sGetArticleID($article_download);
		if(!isset($article_download["unlink"])||!empty($article_download["unlink"]))
		{
			$download_path = $this->sPath.$this->sSystem->sCONFIG['sARTICLEFILES']."/".
			$sql = "SELECT filename FROM s_articles_downloads WHERE articleID=$articleID";
			$downloads = $this->sDB->GetCol($sql);
			if($downloads===false)
				return false;
			if(empty($downloads))
				return true;
			foreach ($downloads as $download)
			{
				if(file_exists($download_path.$download))
					@unlink($download_path.$download);
			}
		}
		$sql = 'SELECT id FROM s_articles_downloads WHERE articleID='.$articleID;
		$downloads = $this->sDB->GetCol($sql);
		if(!empty($downloads))
			$this->sDeleteTranslation('download',$downloads);
		$sql = 'DELETE FROM s_articles_downloads WHERE articleID='.$articleID;
		$this->sDB->Execute($sql);
		return true;
	}
	
	/**
	 * Insert an article download
	 *
	 * @param unknown_type $article_download
	 * @return unknown
	 */
	function sArticleDownload ($article_download)
	{
		if (empty($article_download)||!is_array($article_download))
			return false;
		$article_download['articleID'] = $this->sGetArticleID($article_download);
		if(empty($article_download['articleID']))
			return false;
		if(empty($article_download["name"])&&empty($article_download["link"]))
			return false;
		
		if(empty($article_download["name"]))
		{
			$article_download["name"] = basename($article_download["link"]);
		}
		if(empty($article_download["description"]))
		{
			$article_download["description"] = basename($article_download["name"]);
		}
		$article_download["new_link"] = $this->sPath.$this->sSystem->sCONFIG['sARTICLEFILES']."/".$article_download["name"] ;
				
		if(!empty($article_download["link"]))
		{
			if(file_exists($article_download["new_link"]))
				unlink($article_download["new_link"]);
			if(!copy($article_download["link"],$article_download["new_link"]))
				return false;
			if(!empty($article_download["unlink"])&&file_exists($article_download["link"]))
				unlink($article_download["link"]);
		}
		else 
		{
			if(!file_exists($article_download["new_link"]))
				return false;
		}
		
		$article_download["size"] = filesize($article_download["new_link"]);
		if(!empty($article_download["description"]))
			$article_download["description"] = $this->sDB->qstr((string)$article_download["description"]);
		else 
			$article_download["description"] = $this->sDB->qstr("");
		$article_download["name"] = $this->sDB->qstr($article_download["name"]);
		$sql = "
			INSERT INTO s_articles_downloads
			(articleID, description, filename, size) 
			VALUES
			({$article_download["articleID"]}, {$article_download["description"]}, {$article_download["name"]}, {$article_download["size"]});
		";
		$this->sDB->Execute($sql);		
		return $this->sDB->Insert_ID();
	}
	
	/**
	 *  Delete article attribute group
	 *
	 * @param int|array $articleID
	 * @return bool
	 */
	function sDeleteArticleAttributeGroup ($articleID)
	{
		$articleID = $this->sGetArticleID($articleID);
		$this->sDeleteArticleAttributeGroupValues($articleID);
		$sql = 'UPDATE s_articles SET filtergroupID=NULL WHERE id='. $articleID;
		$this->sDB->Execute($sql);
		return true;
	}
	
	/**
	 *  Delete article attribute group values
	 *
	 * @param int|array $articleID
	 * @return bool
	 */
	function sDeleteArticleAttributeGroupValues ($articleID)
	{
		$articleID = $this->sGetArticleID($articleID);
		if (empty($articleID)) return false;
		$this->sDeleteTranslation('properties',$articleID);
		$sql = 'DELETE FROM s_filter_values WHERE articleID='.$articleID;
		$this->sDB->Execute($sql);
		return true;
	}
	
	/**
	 * Insert an article attribute group
	 *
	 * @param int|array $article
	 * @return bool
	 */
	function sArticleAttributeGroup ($article)
	{
		if(empty($article)||!is_array($article)) return false;
		$article["articleID"] = $this->sGetArticleID($article);
		if(empty($article["articleID"])) return false;
		
		if(empty($article["attributegroupID"]))
		{
			return $this->sDeleteArticleAttributeGroup((int)$article["articleID"]);
		}
		
		$sql = "UPDATE s_articles SET filtergroupID=? WHERE id=?";
		$this->sDB->Execute($sql, array($article["attributegroupID"], $article["articleID"]));

		$sql = "
			SELECT o.id as optionID, v.value, v.id as valueID, o.*
			FROM s_filter_relations r
			INNER JOIN s_filter_options o
			LEFT JOIN s_filter_values v
			ON v.groupID=r.groupID
			AND v.optionID=r.optionID
			AND v.articleID={$article["articleID"]}
			WHERE r.optionID = o.id
			AND r.groupID={$article["attributegroupID"]}
			ORDER BY r.position
		";
		$options = $this->sDB->GetAll($sql);
		if(empty($options))
		{
			return $this->sDeleteArticleAttributeGroupValues((int)$article["articleID"]);
		}
		
		foreach ($options as $key => $option)
		{
			if(empty($article["values"][$key+1])&&!empty($option["valueID"]))
			{
				$sql = "DELETE FROM s_filter_values WHERE id=?";
				$this->sDB->Execute($sql,array($option["valueID"]));
			}
			elseif(empty($option["valueID"])&&!empty($article["values"][$key+1]))
			{
				$sql = "INSERT INTO s_filter_values (groupID, optionID, articleID, value) VALUES (?, ?, ?, ?)";
				$this->sDB->Execute($sql,array(
					$article["attributegroupID"],
					$option["optionID"],
					$article["articleID"],
					$this->sValDescription($article["values"][$key+1])
				));
			}
			elseif (!empty($option["valueID"])&&$option["value"]!=$article["values"][$key+1])
			{
				$sql = "UPDATE s_filter_values SET value=? WHERE id=?";
				$this->sDB->Execute($sql,array($article["values"][$key+1],$option["valueID"]));
			}
		}
		
		$sql = "SELECT DISTINCT isocode FROM s_core_multilanguage WHERE skipbackend=0 AND isocode!='de'";
		$languages = $this->sDB->GetCol($sql);
		foreach ($languages as $language)
		{
			if(!isset($article['values_'.$language])) continue;
			$values = array();
			foreach ($options as $key => $option)
			{
				if(!empty($article['values_'.$language][$key+1]))
				{
					$values[$option['optionID']] = $article['values_'.$language][$key+1];
				}
			}
			$this->sTranslation("properties", $article["articleID"], $language, $values);
		}
		return true;
	}
	
	/**
	 * Update an order method
	 *
	 * @param array $order
	 * @return bool
	 */
	function sOrder ($order)
	{
		if(!empty($order['orderID']))
			$order['orderIDs'] = array($order['orderID']);
		if(!empty($order['orderIDs'])&&is_array($order['orderIDs'])) {
			foreach ($order['orderIDs'] as &$orderID) $orderID = (int) $orderID;
			$order['where'] = "`id` IN (".implode(",",$order['orderIDs']).")\n";
		} elseif(!empty($order['ordernumber'])) {
			$order['where'] = "`ordernumber` = ".$this->sDB->qstr($order['ordernumber'])."\n";
		} elseif(empty($order['where'])) {
			return false;
		}
		
		$upset = array();
		if(isset($order['statusID']))
			$upset[] = "status=".intval($order['statusID']);		
		if(isset($order['clearedID']))
			$upset[] = "cleared=".intval($order['clearedID']);
		if(isset($order['trackingID']))
			$upset[] = "trackingcode=".$this->sDB->qstr((string)$order['trackingID']);
		if(isset($order['transactionID']))
			$upset[] = "transactionID=".$this->sDB->qstr((string)$order['transactionID']);
		if(isset($order['comment']))
			$upset[] = "comment=".$this->sDB->qstr((string)$order['comment']);
		if(!empty($article['cleareddate']))
			$upset[] = "cleareddate=".$this->sDB->DBTimeStamp($order['cleareddate']);
		elseif(isset($article['cleareddate']))
			$upset[] = "cleareddate='0000-00-00'";
		if(empty($upset))
			return true;
			
		$sql = "
			UPDATE s_order 
			SET ".implode(", ",$upset)."
			WHERE {$order['where']} AND status!=-1
		";
		if($this->sDB->Execute($sql)===false)
			return false;
		return true;
	}
	
	/**
	 * Insert or update an article translation
	 *
	 * @param unknown_type $article
	 * @return unknown
	 */
	function sArticleTranslation($article)
	{
		$sql = "SELECT DISTINCT isocode FROM s_core_multilanguage WHERE skipbackend=0";
		$languages = $this->sDB->GetCol($sql);
		if(empty($languages)) return true;

		if(!empty($article['articledetailsID']))
			$where = 'id='.$this->sDB->qstr($article['articledetailsID']);
		elseif(!empty($article['ordernumber']))
			$where = 'ordernumber='.$this->sDB->qstr($article['ordernumber']);
		elseif(!empty($article['articleID']))
			$where = 'kind=1 AND articleID='.$this->sDB->qstr($article['articleID']);
		$sql = "
			SELECT id as articledetailsID, articleID, kind
			FROM s_articles_details
			WHERE $where
		";
		$article_data = $this->sDB->GetRow($sql);
		if(empty($article_data)) return false;
		if($article_data['kind']==1)
		{
			$translate_type = 'article';
			$translate_fields = array('name','keywords','description','description_long','additionaltext');
			$translate_key = $article_data['articleID'];
		}
		else
		{
			$translate_type = 'variant';
			$translate_fields = array('additionaltext');
			$translate_key = $article_data['articledetailsID'];
		}
		foreach ($languages as $language)
		{
			$translate = array();
			foreach ($translate_fields as $field)
			{
				if(!empty($article[$field.'_'.$language]))
				{
					$translate[$field] = $article[$field.'_'.$language];
				}
			}
			for ($i=1;$i<=20;$i++)
			{
				if(isset($article['attr'.$i.'_'.$language]))
				{
					$translate['attr'.$i] = $article['attr'.$i.'_'.$language];
				}
				elseif(isset($article['attr_'.$i.'_'.$language]))
				{
					$translate['attr'.$i] = $article['attr_'.$i.'_'.$language];
				}
			}
			$this->sTranslation($translate_type, $translate_key, $language, $translate);
		}
		return true;
	}
	
	/* Nachfolgende Funktionen sind veraltet und werden mit der Zeit entfernt */
	
	/**
	 * Returns article numbers by attribute
	 *
	 * @param array $attr
	 * @return array
	 */
	function sGetArticleByAttribute ($attr)
	{
		$tmp = array();
		if(!empty($attr)&&is_array($attr))
		foreach ($attr as $key=>$value)
		{
			if(is_numeric($key)&&$key>0)
				$key = "attr".$key;
			$value = $this->sDB->qstr((string)$value);
			$tmp[] = "$key = $value";
		}
		$upset = implode(" AND ",$tmp);
		if(empty($tmp))
			return false;
		$sql = "SELECT articleID, articledetailsID FROM s_articles_attributes WHERE $upset";
		return $this->sDB->GetRow($sql);
	}
	
	/**
	 * Returns article detailsIDs by attribute
	 *
	 * @param array $attr
	 * @return array
	 */
	function sGetArticledetailsIDsByAttribute ($attr)
	{
		//@@TODO: nach sGetArticledetailsIDs verschieben
		$tmp = array();
		if(!empty($attr)&&is_array($attr))
		foreach ($attr as $key=>$value)
		{
			if(is_numeric($key)&&$key>0)
				$key = "attr".$key;
			$value = $this->sDB->qstr((string)$value);
			$tmp[] = "$key = $value";
		}
		$upset = implode(" AND ",$tmp);
		if(empty($tmp))
			return false;
		$sql = "SELECT articledetailsID FROM s_articles_attributes WHERE $upset";
		return $this->sDB->GetCol($sql);
	}
	
	/**
	 * An deprecated method
	 *
	 * @param unknown_type $article
	 * @param unknown_type $attr
	 * @return unknown
	 */
	function sSetAttribute($article, $attr)
	{
		$article = array('articledetailsID'=>$this->sGetArticledetailsID($article),'attr'=>$attr);
		return $this->sArticle($article,array('update'=>false));
	}
	
	/**
	 * An deprecated method
	 *
	 * @param unknown_type $customer
	 * @return unknown
	 */
	function sCustomers ($customer)
	{
		return $this->sCustomer($customer);
	}
	
	/**
	 * An deprecated method
	 *
	 * @param unknown_type $article
	 * @return unknown
	 */
	function sDeleteArticleLink ($article)
	{
		return $this->sDeleteArticleLinks($article);
	}
	
	/**
	 * An deprecated method
	 *
	 * @param unknown_type $price
	 * @return unknown
	 */
	function sRoundPrice ($price)
	{
		return number_format($price,2,'.','');
	}
	
	/**
	 * An deprecated method
	 *
	 * @param unknown_type $articles
	 * @return unknown
	 */
	function sDeactiveOtherArticles ($articles)
	{
		return false;
	}
	
	/**
	 * An deprecated method
	 *
	 * @param unknown_type $dir
	 * @return unknown
	 */
	function sReadDir ($dir)
	{
		$files = array();
		$dir = realpath($dir)."/";
		if (is_dir($dir)) {
		    if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
		        	if($file=="."||$file=="..")
						continue;
		            $files[] = $file;
		        }
		        closedir($dh);
		    }
		}
		return $files;
	}
}