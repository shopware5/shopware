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
 * Deprecated Shopware API Export-Funktionen
 * <code>
 * <?php
 * require_once 'api.php';
 *
 *	$api = new sAPI();
 *	$export =& $api->export->shopware;
 *	$csv =& $api->convert->csv;
 *	$mapping =& $api->convert->mapping;
 *	$orders = $export->sGetOrders (array("where"=>"status=0"));
 * ?>
 * </code>
 *
 * @author      shopware AG
 * @package     Shopware 3.5.0
 * @subpackage  API-Export
 * @version		1.0.0
 * @deprecated
 */
class sShopwareExport
{
    public $sSystem;
    public $sDB;
    public $sPath;
    public $sSettings = array();

    /**
     * Export aller gespeicherten Artikel
     * Inkl. Bilderzuordnungen/Kategorien/Preisen
     * Beispiel zur Anwendung unter /sample/xml/export.articles.php
     *
     * @access public
     * @return array Array mit allen Artikeln
     */
    public function sFullArticles()
    {
        $articles = $this->sArticles();

        if (count($articles) === 0) {
            return false;
        }

        $articledetailIDs = array_keys($articles);
        $articleIDs = array();
        foreach ($articles as $article) {
            $articleIDs[] = $article["articleID"];
        }
        $articleIDs = array_unique($articleIDs);
        $articleprices = $this->sArticlePrices(array("articledetailsIDs"=>$articledetailIDs));

        $articledetails = $this->sArticlesDetails(array("articleIDs"=>$articleIDs));
        $articlecategories = $this->sArticleCategories(array("articleIDs"=>$articleIDs));
        $articleimages = $this->sArticleImages(array("articleIDs"=>$articleIDs));
        $tmp = array();

        foreach ($articles as $articledetailID=>$article) {
            $articleID = $article["articleID"];
            if ($article["kind"]==1) {
                if(!empty($articledetails[$articleID]))
                    $article = array_merge($article,$articledetails[$articleID]);
                if(!empty($articlecategories[$articleID]))
                    $article["categories"] = $articlecategories[$articleID];
                if(!empty($articleimages[$articleID]))
                    $article["images"] = $articleimages[$articleID];
            }
            $article["prices"] = $articleprices[$articledetailID];
            $tmp[$articledetailID] = $article;
        }

        return $tmp;
    }

    /**
     * Export of all stored items (only master data)
     *
     * @access public
     * @return array Array with all articles
     */
    public function sArticles()
    {
        $sql = "
            SELECT
                a.id as `articleID`,
                d.id as `articledetailsID`,
                d.ordernumber,
                d2.articleID as `mainID`,
                d2.id as `maindetailsID`,
                d2.ordernumber as `mainnumber`,
                a.name,
                a.description,
                a.description_long,
                a.shippingtime,
                IF(a.datum='0000-00-00','',a.datum) as added,
                IF(a.changetime='0000-00-00 00:00:00','',a.changetime) as `changed`,
                IF(d.releasedate='0000-00-00','',d.releasedate) as releasedate,
                d.shippingfree,
                a.notification,
                a.topseller,
                a.keywords,
                d.minpurchase,
                d.purchasesteps,
                d.maxpurchase,
                a.mode,
                d.purchaseunit,
                d.referenceunit,
                d.packunit,
                d.unitID,
                a.pricegroupID,
                a.pricegroupActive,
                a.laststock,
                d.suppliernumber,
                d.additionaltext,
                COALESCE(sai.impressions, 0) as impressions,
                d.sales,
                a.active,
                d.kind,
                d.instock,
                d.stockmin,
                IF(e.file IS NULL,0,1) as esd,
                d.weight,
                d.width,
                d.height,
                d.length,
                at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10,
                at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20,
                s.name as supplier,
                u.unit,
                t.tax,
                a.filtergroupID as attributegroupID,
                acs.type as configuratortype,
                IF(acs.id,1,NULL) as configurator

            FROM s_articles a

            INNER JOIN s_articles_details d
            ON d.articleID = a.id

            LEFT JOIN s_articles_details d2
            ON d2.articleID = a.id
            AND d2.kind=1
            AND d.kind=2

            INNER JOIN s_articles_attributes at
            ON at.articledetailsID = d.id

            LEFT JOIN `s_core_units` as u
            ON d.unitID = u.id

            LEFT JOIN s_core_tax as t
            ON a.taxID = t.id

            LEFT JOIN s_articles_supplier as s
            ON a.supplierID = s.id

            LEFT JOIN s_articles_esd e
            ON e.articledetailsID=d.id

            LEFT JOIN s_article_configurator_sets acs
            ON a.configurator_set_id = acs.id

            LEFT JOIN
            (
              SELECT articleId AS id, SUM(s.impressions) AS impressions
              FROM s_statistics_article_impression s
              GROUP BY articleId
            ) sai ON sai.id = a.id

            WHERE
            a.mode = 0
            ORDER BY a.id, d.kind
        ";

        $result = $this->sDB->Execute($sql);
        if(!$result)
            return false;
        $rows = array();
        while ($row = $result->FetchRow()) {
               $row['name'] = htmlspecialchars_decode($row['name']);
               $row['supplier'] = htmlspecialchars_decode($row['supplier']);
            for ($i=1;$i<=20;$i++) {
                $row["attr"][$i] = $row["attr$i"];
                unset($row["attr$i"]);
            }
            $rows[$row["articledetailsID"]] = $row;
        }
        return $rows;
    }

    /**
     * Abfrage von Artikel-Detail-Informationen
     * Zugeordnete Downloads, Links, Verkn�pfungen, Cross-Selling
     * Beispiel zur Anwendung sFullArticles()
     *
     * @param array $article_details
     *
     * articleID = ID des abzufragenden Artikels (s_articles.id) oder
     *
     * articleIDs = IDs der abzufragenden Artikel in Array-Form
     *
     * @access public
     * @return array Array mit Ergebnisdatens�tzen
     */
    public function sArticlesDetails($article_details)
    {
        if(!empty($article_details['articleID']))
            $article_details['articleIDs'][] = $article_details['articleID'];
        if (!empty($article_details['articleIDs'])&&is_array($article_details['articleIDs'])) {
            $article_details['articleIDs'] = array_map("intval",$article_details['articleIDs']);
            $article_details['where'] = "(`articleID`=".implode(" OR `articleID`=",$article_details['articleIDs']).")";
            // as we need to join over s_article_details in SW4 in order to get the ordernumber,
            // we also need to prevent 'articleID' from being ambiguous
            $article_details['where_reference'] = "(`ref`.`articleID`=".implode(" OR `ref`.`articleID`=",$article_details['articleIDs']).")";
        }
        if(empty($article_details['where']))
            return false;
        // Downloads
        $sql = "SELECT `description`, `filename` as link, `size`, `articleID` FROM `s_articles_downloads` WHERE {$article_details['where']}";
        if(($result = $this->sDB->Execute($sql))===false)
            return false;
        while ($row = $result->FetchRow()) {
            $id = $row["articleID"]; unset($row["articleID"]);
            $row["name"] =  basename($row["link"]);
            $rows[$id]["downloads"][] = $row;
        }
        // Informations (links)
        $sql = "SELECT `description` ,`link` ,`target`, `articleID` FROM `s_articles_information` WHERE {$article_details['where']}";
        if(($result = $this->sDB->Execute($sql))===false)
            return false;
        while ($row = $result->FetchRow()) {
            $id = $row["articleID"]; unset($row["articleID"]);
            $rows[$id]["information"][] = $row;
        }
        // Related articles
        $sql = "SELECT `ad`.`ordernumber` as relatedarticle, `ref`.`articleID`
                FROM `s_articles_relationships` ref
                LEFT JOIN s_articles_details ad ON ad.articleID=ref.relatedarticle
                WHERE {$article_details['where_reference']}";
        if(($result = $this->sDB->Execute($sql))===false)
            return false;
        while ($row = $result->FetchRow()) {
            $rows[$row["articleID"]]["relationships"][] = $row["relatedarticle"];
            $rows[$row["articleID"]]["crossellings"][] = $row["relatedarticle"];
        }
        // Similar articles
        $sql = "SELECT `ad`.`ordernumber` as relatedarticle, `ref`.`articleID`
        FROM `s_articles_similar` ref
        LEFT JOIN s_articles_details ad ON ad.articleID=ref.relatedarticle
        WHERE {$article_details['where_reference']}";
        if(($result = $this->sDB->Execute($sql))===false)
            return false;
        while ($row = $result->FetchRow()) {
            $rows[$row["articleID"]]["similars"][] = $row["relatedarticle"];
        }
        return $rows;
    }

    /**
     * Abfrage von Artikel Preisen
     *
     * @param array $article_prices
     *
     * articledetailsID = Artikel-Detail-Id (s_articles_details.id) oder
     *
     * articledetailsIDs = Mehrere Artikel-Detail-Ids in Array-Form
     *
     * @access public
     * @return array Array mit Ergebnisdatens�tzen
     */
    public function sArticlePrices($article_prices)
    {
        if(!empty($article_prices['articledetailsID']))
            $article_prices['articledetailsIDs'][] = $article_prices['articledetailsID'];
        if (!empty($article_prices['articledetailsIDs'])&&is_array($article_prices['articledetailsIDs'])) {
            $article_prices['articledetailsIDs'] = array_map("intval",$article_prices['articledetailsIDs']);
            $article_prices['where'] = "(`articledetailsID`=".implode(" OR `articledetailsID`=",$article_prices['articledetailsIDs']).")";
        }
        $sql = "
            SELECT
                p.`pricegroup`,
                p.`from`,
                p.`to`,
                p.`articleID`,
                p.`articledetailsID`,
                REPLACE(ROUND(p.price*IF(cg.taxinput=1,(100+t.tax)/100,1),2),'.',',') as price,
                REPLACE(ROUND(p.pseudoprice*IF(cg.taxinput=1,(100+t.tax)/100,1),2),'.',',') as pseudoprice,
                REPLACE(p.pseudoprice,'.',',') as net_pseudoprice,
                REPLACE(p.price,'.',',') as net_price,
                REPLACE(ROUND(p.baseprice,2),'.',',') as baseprice,
                p.`percent`,
                a.`taxID`,
                t.`tax`,
                IF(cg.`taxinput`=1,0,1) as `netto`
            FROM `s_articles_prices` p
            INNER JOIN `s_articles` a
            INNER JOIN `s_core_tax` t

            LEFT JOIN s_core_customergroups cg
            ON cg.groupkey = p.pricegroup

            WHERE {$article_prices['where']}
            AND a.id = p.articleID
            AND a.taxID = t.id

            ORDER BY `pricegroup`, `from`
        ";

        $result = $this->sDB->Execute($sql);
        if(!$result)
            return false;
        $rows = array();
        while ($row = $result->FetchRow()) {
            $rows[$row["articledetailsID"]][] = $row;
        }
        return $rows;
    }

    /**
     * Abfrage der Bilder die einem Artikel zugeordnet sind
     *
     * @param array $article_images
     *
     * articleID = ID des abzufragenden Artikels (s_articles.id) oder
     *
     * articleIDs = IDs der abzufragenden Artikel in Array-Form
     *
     * @access public
     * @return array Array mit Ergebnisdatens�tzen
     */
    public function sArticleImages($article_images)
    {
        if(!empty($article_images['articleID']))
            $article_images['articleIDs'][] = $article_images['articleID'];
        if (!empty($article_images['articleIDs'])&&is_array($article_images['articleIDs'])) {
            foreach ($article_images['articleIDs'] as &$articleID)
                $articleID = (int) $articleID;
            $article_images['where'] = "`articleID` IN (".implode(",",$article_images['articleIDs']).")";
        }
        //need to join over s_media to get the path
        $sql = "
            SELECT ai.*, m.path FROM `s_articles_img` ai
            JOIN s_media m ON m.id = ai.media_id
            WHERE {$article_images['where']}
            ORDER BY articleID, main, position, id
        ";
        $result = $this->sDB->Execute($sql);
        if(!$result)
            return false;
        $rows = array();
        while ($row = $result->FetchRow()) {
            if(empty($row["extension"])) $row["extension"] = 'jpg';
            $row["link"] = "http://".Shopware()->Config()->Basepath."/".$row["path"];
            $rows[$row["articleID"]][] = $row;
        }
        return $rows;
    }

    /**
     * Abfrage der Kategorien die ein Artikel zugeordnet ist
     *
     * @param array $article_categories
     *
     * articleID = ID des abzufragenden Artikels (s_articles.id) oder
     *
     * articleIDs = IDs der abzufragenden Artikel in Array-Form
     *
     * @access public
     * @return array Array mit Ergebnisdatens�tzen
     */
    public function sArticleCategories($article_categories)
    {
        if(!empty($article_categories['articleID']))
            $article_categories['articleIDs'][] = $article_categories['articleID'];
        if (!empty($article_categories['articleIDs'])&&is_array($article_categories['articleIDs'])) {
            $article_categories['articleIDs'] = array_map("intval",$article_categories['articleIDs']);
            $article_categories['where'] = "(`articleID`=".implode(" OR `articleID`=",$article_categories['articleIDs']).")";
        }
        $sql = "SELECT DISTINCT `articleID`, `categoryID` FROM `s_articles_categories_ro` WHERE {$article_categories['where']}";

        if(!$result = $this->sDB->Execute($sql))
            return false;
        while ($row = $result->FetchRow()) {
            $rows[$row["articleID"]][] = $row["categoryID"];
        }
        return $rows;
    }

    /**
     * Abfrage / Export der Kategorien / Warengruppen
     *
     * @param array $categories
     * @param int $parentID ID der Elternkategorie, bei der der Export starten soll (1 = alle Kategorien)
     * @access public
     * @return array Array mit Ergebnisdatens�tzen
     */
    public function sCategories ($categories = array(), $parentID = 1)
    {
        $sql = "
            SELECT DISTINCT id as categoryID, parent as parentID, s_categories.*
            FROM s_categories
            WHERE parent=$parentID
            AND id!=parent
        ";
        if(!($result = $this->sDB->Execute($sql)))
            return false;
        while ($row = $result->FetchRow()) {
            unset($row['id'],$row['parent']);
            $categories[$row["categoryID"]] = $row;
            $categories += array_diff_key($this->sCategories($categories, $row["categoryID"]),$categories);
        }
        return $categories;
    }

    /**
     * Exportiert alle hinterlegten Hersteller
     * @return
     */
    public function sSuppliers()
    {
        $sql = "
            SELECT id, id as supplierID, name, img, link, CONCAT('http://".Shopware()->Config()->Basepath."/',img) as `img_link`
            FROM `s_articles_supplier`";
        return $this->sDB->GetAssoc($sql);
    }
    /**
     * Gibt ein Array mit allen offenen Bestellungen zur�ck
     * @access public
     * @return array Alle offenen Bestellungen
     */
    public function sGetOpenOrders()
    {
        $orders = $this->sGetOrders (array("where"=>"status=0"));
        $orderIDs = array_keys($orders);
        if(empty($orders))
            return false;
        $customers = $this->sOrderCustomers(array("orderIDs"=> $orderIDs));
        if(empty($customers))
            return false;
        $details = $this->sOrderDetails(array("orderIDs"=> $orderIDs));
        if(empty($details))
            return false;
        $open_order = array();
        foreach ($orderIDs as $orderID) {
            $customers[$orderID]["paymentID"] = $orders[$orderID]["paymentID"];
            $open_orders[$orderID] = array_merge($orders[$orderID],$customers[$orderID]);
            $open_orders[$orderID]['details'] = $details[$orderID];
        }
        return $open_orders;
    }
    /**
     * �ndert den Status ein oder mehrerer Bestellungen
     * @param array $order
     *   - [orderID] ID der Bestellung oder
     *   - [orderIDs] mehrere Bestell-IDs
     *   - [status] neue StatusID
     *   - [comment] Ein Kommentar der dem Kunden angezeigt werden soll
     * @access public
     * @return bool gibt bei Erfolg true zur�ck
     */
    public function sUpdateOrderStatus($order)
    {
        if(isset($order['status']))
            $order['status'] = intval($order['status']);
        else
            $order['status'] = 1;
        if (!empty($order['where'])&&!is_array($order['where'])) {
            $order['where'] = array($order['where']);
        } else {
            $order['where'] = array();
        }
        if(!empty($order['orderID']))
            $order['orderIDs'] = array($order['orderID']);
        if (!empty($order['orderIDs'])&&is_array($order['orderIDs'])) {
            foreach ($order['orderIDs'] as &$orderID) $orderID = (int) $orderID;
            $order['where'] = "`id` IN (".implode(",",$order['orderIDs']).")\n";
        }

        if(isset($order['comment']))
            $upset = ", comment=".$this->sDB->qstr((string) $order['comment']);
        else
            $upset = "";
        $sql = "
            UPDATE s_order
            SET
                `status` = {$order['status']} $upset
            WHERE {$order['where']} AND status!=-1
        ";

        if($this->sDB->Execute($sql)===false)
            return false;
        return true;
    }

    /**
     * Auslesen von Bestellungen (ein oder mehrere)
     * @param array $order
     *   - [orderID] ID der Bestellung oder
     *   - [orderIDs] mehre IDs [orderIDs] = array (x,y,z)
     *   - [where] eine SQL-Bedingung z.b. "status=0 OR status=1"
     * @access public
     * @return array|bool Gibt bei Erfolg die Bestellungen zur�ck
     */
    public function sGetOrders($order = null)
    {
        if(!empty($order['orderID']))
            $order['orderIDs'] = array($order['orderID']);
        if (!empty($order['orderIDs'])&&is_array($order['orderIDs'])) {
            $order['where'] = '`o`.`id` IN ('.implode(',',$order['orderIDs']).')';
        }
        if (empty($order['where'])) {
            $sql_where = '';
        } else {
            $sql_where = 'WHERE '.$order['where'];
        }

        $sql = "
            SELECT
                `o`.`id`,
                `o`.`id` as `orderID`,
                `o`.`ordernumber`,
                `o`.`ordernumber` as `order_number`,
                `o`.`userID`,
                `o`.`userID` as `customerID`,
                `o`.`invoice_amount`,
                `o`.`invoice_amount_net`,
                `o`.`invoice_shipping`,
                `o`.`invoice_shipping_net`,
                `o`.`ordertime` as `ordertime`,
                `o`.`status`,
                `o`.`status` as `statusID`,
                `o`.`cleared` as `cleared`,
                `o`.`cleared` as `clearedID`,
                `o`.`paymentID` as `paymentID`,
                `o`.`transactionID` as `transactionID`,
                `o`.`comment`,
                `o`.`customercomment`,
                `o`.`net`,
                `o`.`net` as `netto`,
                `o`.`partnerID`,
                `o`.`temporaryID`,
                `o`.`referer`,
                o.cleareddate,
                o.cleareddate as cleared_date,
                o.trackingcode,
                o.language,
                o.currency,
                o.currencyFactor,
                o.subshopID,
                o.dispatchID,
                cu.id as currencyID,
                `c`.`description` as `cleared_description`,
                `s`.`description` as `status_description`,
                `p`.`description` as `payment_description`,
                `d`.`name` 		  as `dispatch_description`,
                `cu`.`name` 	  as `currency_description`
            FROM
                `s_order` as `o`
            LEFT JOIN `s_core_states` as `s`
            ON	(`o`.`status` = `s`.`id`)
            LEFT JOIN `s_core_states` as `c`
            ON	(`o`.`cleared` = `c`.`id`)
            LEFT JOIN `s_core_paymentmeans` as `p`
            ON	(`o`.`paymentID` = `p`.`id`)
            LEFT JOIN `s_premium_dispatch` as `d`
            ON	(`o`.`dispatchID` = `d`.`id`)
            LEFT JOIN `s_core_currencies` as `cu`
            ON	(`o`.`currency` = `cu`.`currency`)
            $sql_where
        ";

        $rows = $this->sDB->GetAssoc($sql);
        if(empty($rows)||!is_array($rows)||!count($rows))
            return false;
        foreach ($rows as $row)
            $orders[intval($row['orderID'])] = $row;
        return $orders;
    }
    /**
     * R�ckgabe der Bestelldetails (Artikel) zu einer oder mehreren Bestellungen
     * @param array $order
     *   - [orderID] BestellID oder
     *   - [orderIDs] mehrere IDs in Form eines Arrays
     *   - [where] eine SQL.Bedingung z.b. "status=0 OR status=1"
     * @access public
     * @return array|bool Gibt bei Erfolg die Bestellpositionen und bei einem Fehler false zur�ck
     */
    public function sOrderDetails($order)
    {
        if(!empty($order['orderID']))
            $order['orderIDs'][] = $order['orderID'];
        if (!empty($order['orderIDs'])&&is_array($order['orderIDs'])) {
            $order['orderIDs'] = array_map("intval",$order['orderIDs']);
            $order['where'] = "`d`.`orderID`=".implode(" OR `d`.`orderID`=",$order['orderIDs'])."";
        }
        if (empty($order['where'])) {
            return false;
        }

        $sql = "
            SELECT
                `d`.`id` as `orderdetailsID`,
                `d`.`orderID` as `orderID`,
                `d`.`ordernumber`,
                `d`.`articleID`,
                `d`.`articleordernumber`,
                `d`.`price` as `price`,
                `d`.`quantity` as `quantity`,
                `d`.`price`*`d`.`quantity` as `invoice`,
                `d`.`name`,
                `d`.`status`,
                `d`.`shipped`,
                `d`.`shippedgroup`,
                `d`.`releasedate`,
                `d`.`modus`,
                `d`.`esdarticle`,
                `d`.`taxID`,
                `t`.`tax`,
                `d`.`tax_rate`,
                `d`.`esdarticle` as `esd`
            FROM
                `s_order_details` as `d`
            LEFT JOIN
                `s_core_tax` as `t`
            ON
                `t`.`id` = `d`.`taxID`
            WHERE
                ({$order['where']})
            ORDER BY `orderdetailsID` ASC
        "; // Fix #5830 backported from github

        $rows = $this->sDB->GetAll($sql);
        if(empty($rows)||!is_array($rows))
            return false;
        $orderdetails = array();
        foreach ($rows as $row) {
            $orderdetails[$row['orderID']][$row['orderdetailsID']] = $row;
        }
        if(isset($order['orderID']))
            return current($orderdetails);
        return 	$orderdetails;
    }
    /**
     * Gibt die Kundendaten f�r angegebene Bestellung(en) zur�ck
     * @param array $order
     *   - [orderID] ID der Bestellung oder
     *   - [orderIDs] mehrere IDs in Array-Form
     * @access public
     * @return array|bool Gibt bei Erfolg die Kundendaten und bei einem Fehler false zur�ck
     */
    public function sOrderCustomers($order)
    {
        if (empty($order['orderIDs'])||!is_array($order['orderIDs']))
            $order['orderIDs'] = array();
        if (!empty($order['orderID'])) {
            $order['orderIDs'][] = $order['orderID'];
        }
        $order['orderIDs'] = array_map("intval",$order['orderIDs']);

        if(!count($order['orderIDs']))
            return false;

        $where = "`b`.`orderID`=".implode(" OR `b`.`orderID`=",$order['orderIDs'])."\n";
        $sql = "
            SELECT
                `b`.`company` AS `billing_company`,
                `b`.`department` AS `billing_department`,
                `b`.`salutation` AS `billing_salutation`,
                `ub`.`customernumber`,
                `b`.`firstname` AS `billing_firstname`,
                `b`.`lastname` AS `billing_lastname`,
                `b`.`street` AS `billing_street`,
                `b`.`streetnumber` AS `billing_streetnumber`,
                `b`.`zipcode` AS `billing_zipcode`,
                `b`.`city` AS `billing_city`,
                `b`.`phone` AS `phone`,
                `b`.`phone` AS `billing_phone`,
                `b`.`fax` AS `fax`,
                `b`.`fax` AS `billing_fax`,
                `b`.`countryID` AS `billing_countryID`,
                `b`.`stateID` AS `billing_stateID`,
                `bc`.`countryname` AS `billing_country`,
                `bc`.`countryiso` AS `billing_countryiso`,
                `bca`.`name` AS `billing_countryarea`,
                `bc`.`countryen` AS `billing_countryen`,
                `b`.`ustid`,
                `ba`.`text1` AS `billing_text1`,
                `ba`.`text2` AS `billing_text2`,
                `ba`.`text3` AS `billing_text3`,
                `ba`.`text4` AS `billing_text4`,
                `ba`.`text5` AS `billing_text5`,
                `ba`.`text6` AS `billing_text6`,
                `b`.`orderID` as `orderID`,
                `s`.`company` AS `shipping_company`,
                `s`.`department` AS `shipping_department`,
                `s`.`salutation` AS `shipping_salutation`,
                `s`.`firstname` AS `shipping_firstname`,
                `s`.`lastname` AS `shipping_lastname`,
                `s`.`street` AS `shipping_street`,
                `s`.`streetnumber` AS `shipping_streetnumber`,
                `s`.`zipcode` AS `shipping_zipcode`,
                `s`.`city` AS `shipping_city`,
                `s`.`stateID` AS `shipping_stateID`,
                `s`.`countryID` AS `shipping_countryID`,
                `sc`.`countryname` AS `shipping_country`,
                `sc`.`countryiso` AS `shipping_countryiso`,
                `sca`.`name` AS `shipping_countryarea`,
                `sc`.`countryen` AS `shipping_countryen`,
                `sa`.`text1` AS `shipping_text1`,
                `sa`.`text2` AS `shipping_text2`,
                `sa`.`text3` AS `shipping_text3`,
                `sa`.`text4` AS `shipping_text4`,
                `sa`.`text5` AS `shipping_text5`,
                `sa`.`text6` AS `shipping_text6`,
                `u`.*,
                   ub.birthday,
                   `g`.`id` AS `preisgruppe`,
                   `g`.`tax` AS `billing_net`
            FROM
                `s_order_billingaddress` as `b`
            LEFT JOIN `s_order_shippingaddress` as `s`
                ON `s`.`orderID` = `b`.`orderID`
            LEFT JOIN `s_user_billingaddress` as `ub`
                ON `ub`.`userID` = `b`.`userID`
            LEFT JOIN `s_user` as `u`
                ON `b`.`userID` = `u`.`id`
            LEFT JOIN `s_core_countries` as `bc`
                ON `bc`.`id` = `b`.`countryID`
            LEFT JOIN `s_core_countries` as `sc`
                ON `sc`.`id` = `s`.`countryID`
            LEFT JOIN `s_core_customergroups` as `g`
                ON `u`.`customergroup` = `g`.`groupkey`
            LEFT JOIN s_core_countries_areas bca
                ON bc.areaID = bca.id
            LEFT JOIN s_core_countries_areas sca
                ON sc.areaID = sca.id
            LEFT JOIN s_order_billingaddress_attributes ba
                ON b.id = ba.billingID
            LEFT JOIN s_order_shippingaddress_attributes sa
                ON s.id = sa.shippingID
            WHERE
                $where
        ";  // Fix #5830 backported from github

        $rows = $this->sDB->GetAll($sql);
        if(empty($rows)||!is_array($rows)||!count($rows))
            return false;
        $customers = array();
        foreach ($rows as $row)
            $customers[intval($row['orderID'])] = $row;
        return 	$customers;
    }
    /**
     * Vergabe von Kundennummern
     * Der Kunde mit der ID $userID erh�lt die Kundennummer $customernumber
     * @param int $userID Die ID des Kunden (s_user.id)
     * @param string $customernumber Die Kundennummer (s_user_billingaddress.customernumber)
     * @access public
     * @return bool Gibt bei Erfolg true und bei einem Fehler false zur�ck
     */
    public function sSetCustomernumber($userID, $customernumber)
    {
        if(empty($userID)||empty($customernumber))
            return false;
        $customernumber = $this->sDB->qstr((string) $customernumber);
        $userID = intval($userID);
        $sql = "UPDATE s_user_billingaddress SET customernumber=$customernumber	WHERE userID=$userID";
        if($this->sDB->Execute($sql)===false)
            return false;
        $sql = "UPDATE s_order_billingaddress SET customernumber=$customernumber WHERE userID=$userID";
        if($this->sDB->Execute($sql)===false)
            return false;
        return true;
    }

    /**
     * Setzt die Tracking-ID / URL des Logistikdienstleisters zur Anzeige in der Storefront
     * @param int/string/array $order (int) s_order.id ODER (string) s_order.ordernumber ODER (array) s_order => array ("ordernumber"=>"101B","orderID"=>12)
     * @param string $trackingcode Tracking-ID/URL
     * @access public
     * @return bool Gibt bei Erfolg true und bei einem Fehler false zur�ck
     */
    public function sSetTrackingID($order, $trackingcode)
    {
        if(is_string($order))
            $order = array('ordernumber'=>$order);
        elseif(is_int($order))
            $order = array('orderID'=>$order);
        elseif(!is_array($order))
            return false;
        if(!empty($order['orderID']))
            $where = "id=".$this->sDB->qstr((int) $order['orderID']);
        elseif(!empty($order['ordernumber']))
            $where = "ordernumber=".$this->sDB->qstr((string) $order['ordernumber']);
        else
            return false;
        $trackingcode = $this->sDB->qstr((string) $trackingcode);
        $sql = "UPDATE s_order SET trackingcode=$trackingcode WHERE $where";
        if($this->sDB->Execute($sql)===false)
            return false;
        return true;
    }
    /**
     * Gibt alle Kategorien zur�ck, die unterhalb von $parentID liegen, falls $parentID nicht �bergeben wird
     * werden automatisch der gesamte Kategoriebaum exportiert
     * @param array $categorie_mask Optional. Hier k�nnen Sie bestimmen, wie die Felder im Export benannt werden sollen
     * @param int $parentID Optional. Definiert von welchem Zweig aus die Kategorien zur�ckgegeben werden sollen
     * @access public
     * @return array Gibt die Kategorien in einer Baumstruktur zur�ck
     */
    public function sCategoryTree ($categorie_mask = array(), $parentID = 1, $rek=true)
    {
        if(empty($categorie_mask)||!is_array($categorie_mask))
            $categorie_mask = array(
                "childs"=>"childs",
                "id"=>"id",
                "parent"=>"parent",
                "description" => "description"
            );

        $sql = "
            SELECT id as categoryID, parent as parentID, s_categories.*
            FROM s_categories
            WHERE parent=$parentID
        ";
        $rows = $this->sDB->GetAll($sql);
        $ret = array();
        foreach ($rows as $row) {
            $tmp = array();
            foreach ($categorie_mask as $key=>$value)
                if(isset($row[$key]))
                    $tmp[$value] = $row[$key];
            if(!empty($rek))
                $childs = $this->sCategoryTree ($categorie_mask, $row["categoryID"]);
            if(!empty($childs))
                $tmp[$categorie_mask["childs"]] = $childs;
            $ret[$row["categoryID"]] = $tmp;
        }
        return $ret;
    }
    /**
     * Hier�ber k�nnen Sie ID der letzten Bestellung abfragen
     * @access public
     * @return array [count] => Anzahl der Bestellungen, [lastID] => die zuletzt vergebene Bestell-ID
     */
    public function sGetLastOrderID()
    {
        $sql = "SELECT MAX( id ) AS lastID, COUNT( id ) AS count FROM s_order";
        return $this->sDB->GetRow($sql);
    }

    /**
     * Export aller Shopware Einstellungen f�r den Import / Abgleich in die Warenwirtschaft
     * Exportiert:
     *
     * [result][tax] = Shopware MwSt. - S�tze (aus s_core_tax)
     *
     * [result][order_states] = Shopware Bestell-Stati (aus s_core_states)
     *
     * [result][payment_states] = Shopware Zahl-Stati (aus s_core_states)
     *
     * [result][units] = Shopware Preiseinheiten (St�ck/Liter etc.) (aus s_core_units)
     *
     * [result][customer_groups] = Shopware Kundengruppen (aus s_core_customergroups)
     *
     * @access public
     * @return array [result]
     */
    public function sSettings()
    {
        $ret = array();
        $sql = "SELECT id, tax, description FROM `s_core_tax`";
        $ret["tax"] = $this->sDB->GetAssoc($sql,false,$force_array=true);
        $sql = "SELECT id, description FROM `s_core_states` WHERE `group` = 'state' ORDER BY position";
        $ret["order_states"] = $this->sDB->GetAssoc($sql,false,$force_array=true);
        $sql = "SELECT id, description FROM `s_core_states` WHERE `group` = 'payment' ORDER BY position";
        $ret["payment_states"] = $this->sDB->GetAssoc($sql,false,$force_array=true);
        $sql = "
            SELECT `id`, `name`, `description`, `debit_percent`, `surcharge`, `position`, `active`, `esdactive` as `esd`
            FROM `s_core_paymentmeans`
            ORDER BY `position`
        ";
        $ret["payment_means"] = $this->sDB->GetAssoc($sql,false,$force_array=true);
        $sql = "SELECT id ,unit ,description FROM `s_core_units`";
        $ret["units"] = $this->sDB->GetAssoc($sql,false,$force_array=true);
        $sql = "
            SELECT `groupkey`, `description`, `tax`, `taxinput`, `minimumorder`, `minimumordersurcharge`
            FROM `s_core_customergroups`
            WHERE `mode`=0
        ";
        $ret["customer_groups"] = $this->sDB->GetAssoc($sql,false,$force_array=true);

        // Did not add the new field `description` here, in order not to brake the id=>name scheme
        $sql = "SELECT `id`, `name` FROM `s_articles_supplier`";
        $ret["manufacturers"] = $this->sDB->GetAssoc($sql,false,$force_array=true);

        return $ret;
    }

    /**
     * Runden von Preisen
     *
     * @param double $price
     * @access public
     * @return double gerundeter Preis
     */
    public function sRoundPrice($price = 0)
    {
        $money_str = explode(".",$price);
        if (!empty($money_str[1])) {
            $money_str[1] = substr($money_str[1],0, 3);
            $money_str = $money_str[0].".".$money_str[1];
        } else {
            $money_str = $money_str[0];
        }
        return round($money_str,2);
    }

    /**
     * Exportiert Kunden - falls keine ID definiert ist ($user) - alle!
     * @param int $user
     * @return
     */
    public function sCustomers($user=0)
    {
        if(!empty($user)&&is_int($user))
            $user['userID'] = $user;
        if(!empty($user['userID']))
            $user['userIDs'] = array($user['userID']);
        if (!empty($user['userIDs'])||is_array($user['userIDs'])) {
            $user['userIDs'] = array_map("intval",$user['userIDs']);
            $where = "WHERE `u`.`id`=".implode(" OR `u`.`id`=",$user['userIDs'])."\n";
        } else {
            $where = "";
        }

        $sql = "
                SELECT
                    `u`.`id`,
                    `u`.`id` AS userID,
                    `b`.`company` AS `billing_company`,
                    `b`.`department` AS `billing_department`,
                    `b`.`salutation` AS `billing_salutation`,
                    `b`.`customernumber`,
                    `b`.`firstname` AS `billing_firstname`,
                    `b`.`lastname` AS `billing_lastname`,
                    `b`.`street` AS `billing_street`,
                    `b`.`streetnumber` AS `billing_streetnumber`,
                    `b`.`zipcode` AS `billing_zipcode`,
                    `b`.`city` AS `billing_city`,
                    `b`.`phone` AS `billing_phoney`,
                    `b`.`fax` AS `billing_fax`,
                    `b`.`countryID` AS `billing_countryID`,
                    `b`.`ustid`,
                    `b`.`stateID` AS `billing_stateID`,
                    `ba`.`text1` AS `billing_text1`,
                    `ba`.`text2` AS `billing_text2`,
                    `ba`.`text3` AS `billing_text3`,
                    `ba`.`text4` AS `billing_text4`,
                    `ba`.`text5` AS `billing_text5`,
                    `ba`.`text6` AS `billing_text6`,
                    `s`.`company` AS `shipping_company`,
                    `s`.`department` AS `shipping_department`,
                    `s`.`salutation` AS `shipping_salutation`,
                    `s`.`firstname` AS `shipping_firstname`,
                    `s`.`lastname` AS `shipping_lastname`,
                    `s`.`street` AS `shipping_street`,
                    `s`.`streetnumber` AS `shipping_streetnumber`,
                    `s`.`zipcode` AS `shipping_zipcode`,
                    `s`.`city` AS `shipping_city`,
                    `s`.`countryID` AS `shipping_countryID`,
                    `s`.`stateID` AS `shipping_stateID`,
                    `sa`.`text1` AS `shipping_text1`,
                    `sa`.`text2` AS `shipping_text2`,
                    `sa`.`text3` AS `shipping_text3`,
                    `sa`.`text4` AS `shipping_text4`,
                    `sa`.`text5` AS `shipping_text5`,
                    `sa`.`text6` AS `shipping_text6`,
                    `u`.`email`,
                    `u`.`paymentID` ,
                    `u`.`newsletter` ,
                    `u`.`affiliate` ,
                    `u`.`customergroup`,
                       u.subshopID ,
                    bc.countryname as billing_country,
                    bc.countryiso as billing_country_iso,
                    bca.name as billing_country_area,
                    bc.countryen as billing_country_en,
                    sc.countryname as shipping_country,
                    sc.countryiso as shipping_country_iso,
                    sca.name as shipping_country_area,
                    sc.countryen as shipping_country_en
                FROM
                    `s_user` as `u`
                LEFT JOIN `s_user_billingaddress` as `b` ON (`b`.`userID`=`u`.`id`)
                LEFT JOIN `s_user_shippingaddress` as `s` ON (`s`.`userID`=`u`.`id`)
                LEFT JOIN s_core_countries bc ON bc.id = b.countryID
                LEFT JOIN s_core_countries sc ON sc.id = s.countryID
                LEFT JOIN s_core_countries_areas bca
                    ON bc.areaID = bca.id
                LEFT JOIN s_core_countries_areas sca
                    ON sc.areaID = sca.id
                LEFT JOIN s_user_billingaddress_attributes ba
                    ON b.id = ba.billingID
                LEFT JOIN s_user_shippingaddress_attributes sa
                    ON s.id = sa.shippingID
            $where
        ";
        if (!empty($user['userID'])) {
            return $this->sDB->GetRow($sql);
        } else {
            return $this->sDB->GetAssoc($sql,false,$force_array=true);
        }
    }
}
?>
