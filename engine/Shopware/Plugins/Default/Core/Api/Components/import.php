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
 * Deprecated Shopware API Import
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @deprecated
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
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleRepository = null;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $mediaRepository = null;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $categoryRepository = null;

    /**
     * @return \Shopware\Components\Model\CategoryDenormalization
     */
    public function getCategoryComponent()
    {
        $component = Shopware()->CategoryDenormalization();
        $component->disableTransactions();

        return $component;
    }

    /**
     * Internal helper function to get access to the article repository.
     *
     * @return Shopware\Models\Article\Repository
     */
    private function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->articleRepository;
    }

    /**
     * Internal helper function to get access to the media repository.
     *
     * @return Shopware\Models\Media\Repository
     */
    private function getMediaRepository()
    {
        if ($this->mediaRepository === null) {
            $this->mediaRepository = Shopware()->Models()->getRepository('Shopware\Models\Media\Media');
        }
        return $this->mediaRepository;
    }

    /**
     * Internal helper function to get access to the article repository.
     *
     * @return Shopware\Models\Category\Repository
     */
    private function getCategoryRepository()
    {
        if ($this->categoryRepository === null) {
            $this->categoryRepository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
        }
        return $this->categoryRepository;
    }

    /**
     * Create a configurator
     * @param      $article
     * @param null $article_configurator
     */
    public function sArticleConfigurator($article, $article_configurator = null)
    {
        echo "This method has been deprecated";
        $this->sAPI->sSetError("This method has been deprecated", 10901);
        return false;
    }

    /**
     * @param Create a new style configurator from a simple variant
     */
    public function sArticleLegacyVariant($article)
    {
        if (empty($article['articleID']) || empty($article['maindetailsID'])) {
            $this->sAPI->sSetError("articleID and maindetailsID required", 10701);
            return false;
        }

        // get ordernumber of the main article if
        $sql = "SELECT a.configurator_set_id, d.ordernumber FROM s_articles a
            LEFT JOIN s_articles_details d ON d.id=".(int) $article['maindetailsID']."
            WHERE a.id=".(int) $article['articleID'];
        $result = Shopware()->Db()->fetchRow($sql);

        if ($result['configurator_set_id'] !== null) {
            $configuratorID = (int) $result['configurator_set_id'];
        } else {
            // create a new configurator form ordernumber
            $name = Shopware()->Db()->quote("Generated set ".$result['ordernumber']);
            $sql = "SELECT id FROM `s_article_configurator_sets` WHERE name=$name";
            $configuratorID = Shopware()->Db()->fetchOne($sql);
            if ($configuratorID === false) {
                Shopware()->Db()->query("INSERT INTO `s_article_configurator_sets` (name,public,type) VALUES({$name},0,0)");
                $configuratorID = (int) Shopware()->Db()->lastInsertId();
            }
        }

        $ordernumber = $result['ordernumber'];

        $optionIDs = array();
        $groupIDs = array();


        $groupNames = null;
        if (isset($article['variant_group_names'])) {
            // make sure that the number of group names matches the number of options
            if (count(explode("|", $article['additionaltext'])) == count(explode("|", $article['variant_group_names']))) {
                $groupNames = explode("|", $article['variant_group_names']);
            }

        }

        foreach (explode("|", $article['additionaltext']) as $idx => $option) {
            $hidx = $idx+1;
            $option = trim(str_replace("'", "", $option));

//            $genericGroupName = Shopware()->Db()->quote($result['ordernumber']."generatedOldVariant");
            if ($groupNames) {
                $genericGroupName = Shopware()->Db()->quote($groupNames[$idx]);
            } else {
                $genericGroupName = Shopware()->Db()->quote("Group #{$ordernumber}/{$hidx}");
            }
            $sql = "SELECT id FROM s_article_configurator_groups WHERE name={$genericGroupName}";
            $result = Shopware()->Db()->fetchOne($sql);

            if ($result === false) {
                $sql = "INSERT INTO `s_article_configurator_groups` (name,description,position) VALUES({$genericGroupName},'',1)";
                $this->sDB->Execute($sql);
                $groupID = (int) $this->sDB->Insert_ID();
            } else {
                $groupID = (int) $result;
            }
            $optionName = Shopware()->Db()->quote($option);
            $sql = "SELECT id FROM s_article_configurator_options WHERE name={$optionName} AND group_id={$groupID}";
            $result = Shopware()->Db()->fetchOne($sql);
            if ($result === false) {
                $sql = "INSERT INTO `s_article_configurator_options` (group_id,name,position) VALUES({$groupID},{$optionName},1)";
                $this->sDB->Execute($sql);
                $optionIDs[] = (int) $this->sDB->Insert_ID();
            } else {
                $optionIDs[] = (int) $result;
            }

            $groupIDs[] = $groupID;
        }

        foreach ($groupIDs as $groupID) {
            // set-group relations
            $sql = "SELECT COUNT(*) FROM `s_article_configurator_set_group_relations` WHERE group_id={$groupID} AND set_id={$configuratorID}";
            if ((int) Shopware()->Db()->fetchOne($sql) === 0) {
                $sql = "INSERT INTO `s_article_configurator_set_group_relations` (set_id,group_id) VALUES({$configuratorID},{$groupID})";
                $this->sDB->Execute($sql);
            }
        }

        foreach ($optionIDs as $optionID) {
            // set-option relations
            $sql = "SELECT COUNT(*) FROM `s_article_configurator_set_option_relations` WHERE option_id={$optionID} AND set_id={$configuratorID}";
            if ((int) Shopware()->Db()->fetchOne($sql) === 0) {
                $sql = "INSERT INTO `s_article_configurator_set_option_relations` (set_id,option_id) VALUES({$configuratorID},{$optionID})";
                $this->sDB->Execute($sql);
            }

            // option-article relations
            $sql = "SELECT COUNT(*) FROM `s_article_configurator_option_relations` WHERE option_id={$optionID} AND article_id={$article['articledetailsID']}";
            if ((int) Shopware()->Db()->fetchOne($sql) === 0) {
                $sql = "INSERT INTO `s_article_configurator_option_relations` (article_id,option_id) VALUES({$article['articledetailsID']},{$optionID})";
                $this->sDB->Execute($sql);
            }
        }

        return $configuratorID;

    }

    /**
     * Importiert einen Artikel in Shopware
     *
     * @param array $article
     *
     * Beispiel-Felder (weitere M�glich):
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
     * releasedate => Ver�ffentlichungsdatumd es Artikels im Format YYYY-MM-DD
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
     * Enth�lt verschiedene Konfigurations-Optionen
     * (optional)
     * @access public
     * @return array Alle offenen Bestellungen
     */
    public function sArticle ($article, $config = array())
    {
        if(!isset($config['update']))
            $config['update'] = true;

        if (empty($article['ordernumber'])&&empty($article['articledetailsID'])&&empty($article['articleID'])) {
            $this->sAPI->sSetError("Ordernumber or articleID are required", 10200);
            return false;
        }

        ### ELEMENTS OF s_articles ###
        if(isset($article["name"]))
            $article['name'] = $this->sDB->qstr($this->sValDescription($article['name']));
        if(isset($article["shippingtime"]))
            $article['shippingtime'] = $this->sDB->qstr((string) $article['shippingtime']);
        if(isset($article["description"]))
            $article['description'] = $this->sDB->qstr((string) $article['description']);
        if(isset($article["description_long"]))
            $article['description_long'] = $this->sDB->qstr((string) $article['description_long']);
        if(isset($article["keywords"]))
            $article['keywords'] = $this->sDB->qstr((string) $article['keywords']);
        if(isset($article['supplierID']))
            $article['supplierID'] = intval($article['supplierID']);
        if(isset($article['taxID']))
            $article['taxID'] = intval($article['taxID']);
        if(isset($article['filtergroupID']))
            $article['filtergroupID'] = intval($article['filtergroupID']);
        if(isset($article['pricegroupID']))
            $article['pricegroupID'] = intval($article['pricegroupID']);
        if(isset($article['pseudosales']))
            $article['pseudosales'] = intval($article['pseudosales']);
        if(isset($article['topseller']))
            $article['topseller'] = empty($article['topseller']) ? 0 : 1;
        if(isset($article['notification']))
            $article['notification'] = empty($article['notification']) ? 0 : 1;
        if(isset($article['laststock']))
            $article['laststock'] = empty($article['laststock']) ? 0 : 1;
        if(isset($article['active']))
            $article['active'] = empty($article['active']) ? 0 : 1;
        if(isset($article['pricegroupActive']))
            $article['pricegroupActive'] = empty($article['pricegroupActive']) ? 0 : 1;

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

        if(isset($article['main_detail_id']))
            $article['main_detail_id'] = intval($article['main_detail_id']);
        if(!empty($article['available_from']))
            $article['available_from'] = $this->sDB->DBTimeStamp($article['available_from']);
        else
            unset($article['available_from']);
        if(!empty($article['available_to']))
            $article['available_to'] = $this->sDB->DBTimeStamp($article['available_to']);
        else
            unset($article['available_to']);
        if(isset($article['configurator_set_id']))
            $article['configurator_set_id'] = intval($article['configurator_set_id']);
        if(isset($article['crossbundlelook']))
            $article['crossbundlelook'] = intval($article['crossbundlelook']);
        if(isset($article['notification']))
            $article['notification'] = intval($article['notification']);
        if(isset($article['template']))
            $article['template'] = intval($article['template']);
        if(isset($article['mode']))
            $article['mode'] = intval($article['mode']);

        ### ELEMENTS OF s_articles_details ###
        // Checked for availability in sw4
        if(isset($article["ordernumber"]))
            $article['ordernumber'] = $this->sDB->qstr($this->sValDescription($article['ordernumber']));
        if(isset($article["suppliernumber"]))
            $article['suppliernumber'] = $this->sDB->qstr($this->sValDescription($article['suppliernumber']));
        if(isset($article["additionaltext"]))
            $article['additionaltext'] = $this->sDB->qstr((string) $article['additionaltext']);
        if(isset($article['sales']))
            $article['sales'] = intval($article['sales']);
        if(isset($article['active']))
            $article['active'] = empty($article['active']) ? 0 : 1;
        if(isset($article['instock']))
            $article['instock'] = intval($article['instock']);
        if(isset($article['stockmin']))
            $article['stockmin'] = intval($article['stockmin']);
        if(isset($article['weight']))
                  $article['weight'] = $this->sValFloat($article['weight']);
        if(isset($article['position']))
            $article['position'] = intval($article['position']);
        if(isset($article['width']))
            $article['width'] = $this->sValFloat($article['width']);
        if(isset($article['height']))
            $article['height'] = $this->sValFloat($article['height']);
        if(isset($article['length']))
            $article['length'] = $this->sValFloat($article['length']);
        if(isset($article["ean"]))
            $article['ean'] = $this->sDB->qstr((string) $article['ean']);
        if(isset($article['unitID']))
            $article['unitID'] = intval($article['unitID']);
        if(isset($article['purchasesteps']))
            $article['purchasesteps'] = intval($article['purchasesteps']);
        if(isset($article['maxpurchase']))
            $article['maxpurchase'] = intval($article['maxpurchase']);
        if(isset($article['minpurchase']))
            $article['minpurchase'] = intval($article['minpurchase']);
        if(isset($article['purchaseunit']))
            $article['purchaseunit'] = $this->sValFloat($article['purchaseunit']);
        if(isset($article['referenceunit']))
            $article['referenceunit'] = $this->sValFloat($article['referenceunit']);
        if(isset($article["packunit"]))
            $article['packunit'] = $this->sDB->qstr((string) $article['packunit']);
        if(!empty($article["releasedate"]))
            $article['releasedate'] = $this->sDB->DBDate($article['releasedate']);
        elseif(isset($article['releasedate']))
            $article['releasedate'] = null;
        if(isset($article['shippingfree']))
            $article['shippingfree'] = empty($article['shippingfree']) ? 0 : 1;

        ### ELEMENTS OF s_articles_attributes ##
        if(isset($article['articledetailsID']))
            $article['articledetailsID'] = intval($article['articledetailsID']);
        if(isset($article['articleID']))
            $article['articleID'] = intval($article['articleID']);
        if (isset($article["attr"])&&is_array($article["attr"])) {
            foreach ($article["attr"] as $key=>$attr) {
                $key = (int) str_replace('attr', '', $key);
                if(!is_int($key)||$key>20)
                    unset ($article["attr"][$key]);
                else
                    $article["attr"][$key] = $this->sDB->qstr((string) $attr);
            }
        } else {
            $article["attr"] = array();
        }

        for ($i=1;$i<=20;$i++) {
            if (isset($article["attr$i"])) {
                $article["attr"][$i] = $this->sDB->qstr((string) $article["attr$i"]);
            }
        }

        if(!empty($article['mainID']))
            $article['mainID'] = intval($article['mainID']);
        if(!empty($article['maindetailsID']))
            $article['maindetailsID'] = intval($article['maindetailsID']);
        if(!empty($article['mainnumber']))
            $article['mainnumber'] = $this->sDB->qstr((string) $article['mainnumber']);

        $configuratorSetId = null;

        // Varianten-Artikel �berpr�fen
        if (!empty($article['mainnumber'])||!empty($article['mainID'])||!empty($article['maindetailsID'])) {
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
            if (empty($row['id'])) {
                $this->sAPI->sSetError("Main article with '$where' not found", 10201);
                return false;
            }
            $article['maindetailsID'] = $row['id'];
            $article['articleID'] = $row['articleID'];

        }

        // Wir �berpr�fen ob Artikel vorhanden ist, wenn ja holen wir die ArtikelDetailsID
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
        // Wenn vorhanden und Update unerw�nscht
        if (!empty($row['id'])&&empty($config['update'])) {
            $this->sAPI->sSetError("Update not allowed", 10202);
            return false;
        }
        // Varianten-Artikel �berpr�fen 2
        if(empty($article['maindetailsID'])
          || (!empty($row['id']) && $article['maindetailsID'] == $row['id'])) {
            $article['kind'] = 1;
        } else {
            $article['kind'] = 2;
        }
        // Wenn nicht vorhanden und es ist keine Bestellnummer vorhanden
        if (empty($row['id'])&&!isset($article['ordernumber'])) {
            $this->sAPI->sSetError("Article for update not found", 10203);
            return false;
        }
        // Artikel wird zu einer Variante
        if (!empty($row)&&$row['kind']==1&&$article['kind']==2) {
            $this->sDeleteArticle(array("articleID"=>$row['articleID']));
            unset($row);
        }
        // Variante �ndert Hauptartikel
        // Variante wird zu einen Artikel
        // Variante beleibt eine Variante
        elseif (!empty($row)) {
            $article['articledetailsID'] = $row['id'];
            if ($article['kind']==1) {
                $article['articleID'] = $row['articleID'];
            }
            if (empty($article['taxID']) && empty($article['tax'])) {
                $article['taxID'] = $row['taxID'];
            }
        }

//		if($article['kind']==2 && !empty($article['ordernumber']))
//		{
//			//Bestellnummer ist schon f�r eine Konfigurator-Auswahl vergeben
//			$sql = "SELECT articleID FROM s_articles_groups_value WHERE ordernumber={$article['ordernumber']}";
//			$result = $this->sDB->GetOne($sql);
//			if(!empty($result))
//			{
//				$this->sAPI->sSetError("Ordernumber '{$article['ordernumber']}' is already assigned", 10204);
//				return false;
//			}
//		}

        //HerstellerID holen
        if (isset($article['supplierID']))
            $article['supplierID'] = $this->sSupplier(array('supplierID'=>$article['supplierID']));
        elseif (isset($article['supplier']))
            $article['supplierID'] = $this->sSupplier(array('supplier'=>$article['supplier']));
        if (empty($article['supplierID'])&&empty($article['articleID'])) { // Hersteller wird ben�tigt
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
        if (empty($row)) {
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
            "taxID",
            "pseudosales",
            "topseller",
            "keywords",
            "changetime",
            "pricegroupID",
            "filtergroupID",
            "laststock",
            "crossbundlelook",
            "notification",
            "template",
            "mode",
            "main_detail_id",
            "available_from",
            "available_to",
            "configurator_set_id"
        );

        if (empty($article['articleID'])) {
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
            foreach ($article_fields as $field) {
                if (isset($article[$field])) {
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
        else {
            if(!empty($article['added']))
                $article['datum'] = $article['added'];
            if(empty($article['changed']))
                $article['changed'] = $this->sDB->sysTimeStamp;
            $article['changetime'] = $article['changed'];

            if ($article['kind']==1) {
                $upset = array();
                foreach ($article_fields as $field) {
                    if (isset($article[$field])) {
                        $upset[] = $field."=".$article[$field];
                    }
                }
                $upset = implode(", ",$upset);
            } else {
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

        $article_details_fields = array(
            "articleID",
            "ordernumber",
            "suppliernumber",
            "kind",
            "additionaltext",
            "sales",
            "active",
            "instock",
            "stockmin",
            "weight",
            "position",
            "width",
            "height",
            "length",
            "ean",
            "unitID",
            "purchasesteps",
            "maxpurchase",
            "minpurchase",
            "purchaseunit",
            "referenceunit",
            "packunit",
            "releasedate",
            "shippingfree",
            "shippingtime"
        );

        if (empty($article['articledetailsID'])) {
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

            $article['datum'] = $article['added'];
            $article['changetime'] = $article['changed'];

            $insert_fields = array();
            $insert_values = array();
            foreach ($article_details_fields as $field) {
                if (isset($article[$field])) {
                    $insert_fields[] = $field;
                    $insert_values[] = $article[$field];
                }
            }
            $sql = "
                INSERT INTO s_articles_details (".implode(", ",$insert_fields).")
                VALUES (".implode(", ",$insert_values).")
            ";
            $this->sDB->Execute($sql);
            $article['articledetailsID'] = $this->sDB->Insert_ID();
        }//Wenn Artikel vorhanden ist, aktualisieren wir ihn
        else {
            $upset = array();
            foreach ($article_details_fields as $field) {
                if (isset($article[$field])) {
                    $upset[] = $field."=".$article[$field];
                }
            }
            $upset = implode(", ",$upset);
            $sql = "
                UPDATE s_articles_details
                SET
                    $upset
                WHERE id = {$article['articledetailsID']}
            ";
            $this->sDB->Execute($sql);
        }

        // If the article was a main article, set the main detail id
        if (!empty($article['articleID']) && $article['kind'] === 1) {
            $sql = "UPDATE s_articles SET main_detail_id={$article['articledetailsID']} WHERE id = {$article['articleID']}";
            $this->sDB->Execute($sql);
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

        if (!empty($article['articleattributesID'])) {
            $upset = "";
            if (!empty($article['attr'])&&is_array($article['attr'])) {
                foreach ($article['attr'] as $key=>$value) {
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
        } else {
            if (!empty($article['attr'])) {
                $upset = ", attr".implode(", attr",array_keys($article['attr']));
                $upset2 = ", ".implode(", ",$article['attr']);
            } else {
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

        if (!empty($article['mainnumber'])||!empty($article['mainID'])||!empty($article['maindetailsID'])) {
            // setup variant
            $configuratorSetId = $this->sArticleLegacyVariant($article);
            if ($configuratorSetId === false) {
                return false;
            }
        }

        // Set configurator set id
        if ($configuratorSetId !== null && $configuratorSetId !== (int) $article['configurator_set_id']) {
            $sql = "UPDATE s_articles SET configurator_set_id={$configuratorSetId} WHERE id={$article['articleID']}";
            $this->sDB->Execute($sql);
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
    public function sCustomer($customer)
    {
        if(isset($customer["password"]))
            $customer["password"] = trim($customer['password'],"\r\n");
        if(empty($customer["md5_password"])&&!empty($customer['password']))
            $customer["md5_password"] = md5($customer['password']);
        if(isset($customer["md5_password"]))
            $customer["md5_password"] = $this->sDB->qstr($customer['md5_password']);
        if(isset($customer["encoder"]))
            $customer["encoder"] = $this->sDB->qstr($customer['encoder']);
        if(isset($customer["email"]))
            $customer["email"] = $this->sDB->qstr(trim($customer["email"]));
        if(isset($customer["customergroup"]))
            $customer["customergroup"] = $this->sDB->qstr((string) $customer["customergroup"]);
        if(isset($customer["validation"]))
            $customer["validation"] = $this->sDB->qstr((string) $customer["validation"]);
        if(isset($customer["language"]))
            $customer["language"] = $this->sDB->qstr((string) $customer["language"]);
        if(isset($customer["referer"]))
            $customer["referer"] = $this->sDB->qstr((string) $customer["referer"]);

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

        if (empty($customer['userID'])&&!empty($customer['email'])) {
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
            "referer",
            "encoder"
        );
        if (empty($customer["userID"])) {
            if (empty($customer['password'])&&empty($customer["md5_password"])) {
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
            foreach ($fields as $field) {
                if (isset($customer[$field])) {
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
        } else {

            $upset = array();
            foreach ($fields as $field) {
                if (isset($customer[$field])) {
                    $upset[] = $field."=".$customer[$field];
                }
            }
            if(isset($customer["md5_password"]))
                $upset[] = "password=".$customer["md5_password"];
            if (!empty($upset)) {
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
            $customer["billing_company"] = $this->sDB->qstr((string) $customer["billing_company"]);
        if(isset($customer["billing_department"]))
            $customer["billing_department"] = $this->sDB->qstr((string) $customer["billing_department"]);
        if(isset($customer["billing_salutation"]))
            $customer["billing_salutation"] = $this->sDB->qstr((string) $customer["billing_salutation"]);
        if(isset($customer["billing_firstname"]))
            $customer["billing_firstname"] = $this->sDB->qstr((string) $customer["billing_firstname"]);
        if(isset($customer["billing_lastname"]))
            $customer["billing_lastname"] = $this->sDB->qstr((string) $customer["billing_lastname"]);
        if(isset($customer["billing_street"]))
            $customer["billing_street"] = $this->sDB->qstr((string) $customer["billing_street"]);
        if(isset($customer["billing_streetnumber"]))
            $customer["billing_streetnumber"] = $this->sDB->qstr((string) $customer["billing_streetnumber"]);
        if(isset($customer["billing_zipcode"]))
            $customer["billing_zipcode"] = $this->sDB->qstr((string) $customer["billing_zipcode"]);
        if(isset($customer["billing_city"]))
            $customer["billing_city"] = $this->sDB->qstr((string) $customer["billing_city"]);
        if(isset($customer["phone"]))
            $customer["phone"] = $this->sDB->qstr((string) $customer["phone"]);
        if(isset($customer["fax"]))
            $customer["fax"] = $this->sDB->qstr((string) $customer["fax"]);
        if(isset($customer["ustid"]))
            $customer["ustid"] = $this->sDB->qstr((string) $customer["ustid"]);
        if(isset($customer["billing_countryID"]))
            $customer["billing_countryID"] = intval($customer["billing_countryID"]);
        if(empty($customer["billing_countryID"])&&!empty($customer["billing_countryiso"]))
            $customer["billing_countryID"] = (int) $this->sGetCountryID(array("iso"=>$customer["billing_countryiso"]));
        for($i=1;$i<7;$i++)
            if(isset($customer["billing_text$i"]))
                $customer["billing_text$i"] = $this->sDB->qstr((string) $customer["billing_text$i"]);

        if(isset($customer["customernumber"]))
            $customer["customernumber"] = $this->sDB->qstr((string) $customer["customernumber"]);
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
            "birthday"=>"birthday"
        );

        $sql = "SELECT id FROM s_user_billingaddress WHERE userID=".$customer['userID'];
        $customer["billingaddressID"] = $this->sDB->GetOne($sql);
        if (empty($customer["billingaddressID"])) {
            $insert_fields = array();
            $insert_values = array();
            foreach ($fields as $field=>$field2) {
                if (isset($customer[$field2])) {
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
        } else {
            $upset = array();
            foreach ($fields as $field=>$field2) {
                if (isset($customer[$field2])) {
                    $upset[] = $field."=".$customer[$field2];
                }
            }
            if (!empty($upset)&&count($upset)>1) {
                $upset = implode(", ",$upset);
                $sql = "
                    UPDATE s_user_billingaddress
                    SET $upset
                    WHERE id = {$customer['billingaddressID']}
                ";
                $this->sDB->Execute($sql);
            }
        }

        // Insert customer billingaddress attributes
        $sql = "SELECT id FROM s_user_billingaddress_attributes WHERE billingID=".$customer['billingaddressID'];
        $billingAttributeId = $this->sDB->GetOne($sql);
        $fields = array(
            "text1"=>"billing_text1",
            "text2"=>"billing_text2",
            "text3"=>"billing_text3",
            "text4"=>"billing_text4",
            "text5"=>"billing_text5",
            "text6"=>"billing_text6"
              );
        if (empty($billingAttributeId)) {
            $insert_fields = array('billingID');
            $insert_values = array($customer["billingaddressID"]);
            foreach ($fields as $field=>$field2) {
                if (isset($customer[$field2])) {
                    $insert_fields[] = $field;
                    $insert_values[] = $customer[$field2];
                }
            }
            $sql = "
                INSERT INTO s_user_billingaddress_attributes (".implode(", ",$insert_fields).")
                VALUES (".implode(", ",$insert_values).")
            ";
            $result = $this->sDB->Execute($sql);
            if($result===false)
                return false;
        } else {
            $upset = array();
            foreach ($fields as $field=>$field2) {
                if (isset($customer[$field2])) {
                    $upset[] = $field."=".$customer[$field2];
                }
            }
            if (!empty($upset)&&count($upset)>1) {
                $upset = implode(", ",$upset);
                $sql = "
                    UPDATE s_user_billingaddress_attributes
                    SET $upset
                    WHERE id = {$billingAttributeId}
                ";
                $this->sDB->Execute($sql);
            }
        }


        if (!empty($customer["shipping_company"])||!empty($customer["shipping_firstname"])||!empty($customer["shipping_lastname"])) {
            if(isset($customer["shipping_company"]))
                $customer["shipping_company"] = $this->sDB->qstr((string) $customer["shipping_company"]);
            if(isset($customer["shipping_department"]))
                $customer["shipping_department"] = $this->sDB->qstr((string) $customer["shipping_department"]);
            if(isset($customer["shipping_salutation"]))
                $customer["shipping_salutation"] = $this->sDB->qstr((string) $customer["shipping_salutation"]);
            if(isset($customer["shipping_firstname"]))
                $customer["shipping_firstname"] = $this->sDB->qstr((string) $customer["shipping_firstname"]);
            if(isset($customer["shipping_lastname"]))
                $customer["shipping_lastname"] = $this->sDB->qstr((string) $customer["shipping_lastname"]);
            if(isset($customer["shipping_street"]))
                $customer["shipping_street"] = $this->sDB->qstr((string) $customer["shipping_street"]);
            if(isset($customer["shipping_streetnumber"]))
                $customer["shipping_streetnumber"] = $this->sDB->qstr((string) $customer["shipping_streetnumber"]);
            if(isset($customer["shipping_zipcode"]))
                $customer["shipping_zipcode"] = $this->sDB->qstr((string) $customer["shipping_zipcode"]);
            if(isset($customer["shipping_city"]))
                $customer["shipping_city"] = $this->sDB->qstr((string) $customer["shipping_city"]);
            if(isset($customer["shipping_countryID"]))
                $customer["shipping_countryID"] = intval($customer["shipping_countryID"]);
            if(empty($customer["shipping_countryID"])&&!empty($customer["shipping_countryiso"]))
                $customer["shipping_countryID"] = (int) $this->sGetCountryID(array("iso"=>$customer["shipping_countryiso"]));
            for($i=1;$i<7;$i++)
            if(isset($customer["shipping_text$i"]))
                $customer["shipping_text$i"] = $this->sDB->qstr((string) $customer["shipping_text$i"]);

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
                "countryID"=>"shipping_countryID"
            );
            $sql = "SELECT id FROM s_user_shippingaddress WHERE userID=".$customer['userID'];
            $customer["shippingaddressID"] = $this->sDB->GetOne($sql);
            if (empty($customer["shippingaddressID"])) {
                $insert_fields = array();
                $insert_values = array();
                foreach ($fields as $field=>$field2) {
                    if (isset($customer[$field2])) {
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
            } else {
                $upset = array();
                foreach ($fields as $field=>$field2) {
                    if (isset($customer[$field2])) {
                        $upset[] = $field."=".$customer[$field2];
                    }
                }
                if (!empty($upset)&&count($upset)>1) {
                    $upset = implode(", ",$upset);
                    $sql = "
                        UPDATE s_user_shippingaddress
                        SET $upset
                        WHERE id = {$customer['shippingaddressID']}
                    ";
                    $this->sDB->Execute($sql);
                }
            }

            // Insert customer shippingaddres attributes
            $sql = "SELECT id FROM s_user_shippingaddress_attributes WHERE shippingID=".$customer['shippingaddressID'];
            $shippingAttributeId = $this->sDB->GetOne($sql);
            $fields = array(
                "text1"=>"shipping_text1",
                "text2"=>"shipping_text2",
                "text3"=>"shipping_text3",
                "text4"=>"shipping_text4",
                "text5"=>"shipping_text5",
                "text6"=>"shipping_text6"
                  );
            if (empty($shippingAttributeId)) {
                $insert_fields = array('shippingID');
                $insert_values = array($customer["shippingaddressID"]);
                foreach ($fields as $field=>$field2) {
                    if (isset($customer[$field2])) {
                        $insert_fields[] = $field;
                        $insert_values[] = $customer[$field2];
                    }
                }
                $sql = "
                    INSERT INTO s_user_shippingaddress_attributes (".implode(", ",$insert_fields).")
                    VALUES (".implode(", ",$insert_values).")
                ";
                $result = $this->sDB->Execute($sql);
                if($result===false)
                    return false;
            } else {
                $upset = array();
                foreach ($fields as $field=>$field2) {
                    if (isset($customer[$field2])) {
                        $upset[] = $field."=".$customer[$field2];
                    }
                }
                if (!empty($upset)&&count($upset)>1) {
                    $upset = implode(", ",$upset);
                    $sql = "
                        UPDATE s_user_shippingaddress_attributes
                        SET $upset
                        WHERE id = {$shippingAttributeId}
                    ";
                    $this->sDB->Execute($sql);
                }
            }
        } elseif (isset($customer["shipping_company"])||isset($customer["shipping_firstname"])||isset($customer["shipping_lastname"])) {
            $sql = "DELETE FROM s_user_shippingaddress WHERE userID=".$customer["userID"];
            $result = $this->sDB->Execute($sql);
        }

        $customer["customernumber"] = $this->sDB->GetOne("SELECT customernumber FROM s_user_billingaddress WHERE userID=".$customer["userID"]);
        if ($this->sSystem->sCONFIG['sSHOPWAREMANAGEDCUSTOMERNUMBERS']&&empty($customer["customernumber"])) {
            $sql = "UPDATE s_order_number n, s_user_billingaddress b SET n.number=n.number+1, b.customernumber=n.number+1 WHERE n.name='user' AND b.userID=?";
            $this->sDB->Execute($sql, array($customer["userID"]));
            $customer["customernumber"] = $this->sDB->GetOne("SELECT customernumber FROM s_user_billingaddress WHERE userID=".$customer["userID"]);
        }

        if (isset($customer["newsletter"])) {
            if (empty($customer["newsletter"])) {
                $sql = "DELETE FROM s_campaigns_mailaddresses WHERE email=".$customer["email"];
                $this->sDB->Execute($sql);
            } else {
                if(empty($customer["newslettergroupID"]))
                    $customer["newslettergroupID"] = empty($this->sSystem->sCONFIG["sNEWSLETTERDEFAULTGROUP"]) ? 1 : (int) $this->sSystem->sCONFIG["sNEWSLETTERDEFAULTGROUP"];
                else
                    $customer["newslettergroupID"] = intval($customer["newslettergroupID"]);
                $sql = "SELECT id FROM s_campaigns_mailaddresses WHERE email=".$customer["email"];
                $result = $this->sDB->GetOne($sql);
                if (empty($result)) {

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
    public function sGetCountryID($country)
    {
        if(!empty($country["name"]))
            $where = "countryname LIKE ".$this->sDB->qstr(trim((string) $country['name']));
        elseif(!empty($country["iso"]))
            $where = "countryiso=".$this->sDB->qstr(trim((string) $country['iso']));
        elseif(!empty($country["en"]))
            $where = "countryen LIKE ".$this->sDB->qstr(trim((string) $country['en']));
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
    public function sArticleImages($article, $images)
    {
        if(!($articleID = $this->sGetArticleID($article)))
            return false;
        $inserts = array();
        if (!empty($images)&&is_array($images)) {
            foreach ($images as $image) {
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
    public function sArticlePrices($article, $prices)
    {
        if(!($articledetailsID = $this->sGetArticledetailsID($article)))
            return false;
        if (!empty($prices)&&is_array($prices)) {
            foreach ($prices as $price) {
                $price["articledetailsID"] = $articledetailsID;
                $insert = $this->sArticlePrice($price);
            }
        }
        return true;
    }

    /**
     * New method to create categories
     * @param array $category
     */
    public function sCategory($category = array())
    {
        // In order to be compatible with the old API syntax but to also be able to use ->fromArray(),
        // we map from the old keys to doctrine keys
        $mappings = array(
            'description' => 'name',
            'cmsheadline' => 'cmsHeadline',
            'metakeywords' => 'metaKeywords',
            'metadescription' => 'metaDescription'
        );

        foreach ($mappings as $original => $new) {
            if (isset($category[$original])) {
                $category[$new] = $category[$original];
                unset($category[$original]);
            }
        }

        $model = null;

        $categoryRepository = $this->getCategoryRepository();
        // If user wants to update the category
        if ($category['updateID']) {
            $model = $categoryRepository->find((int) $category['updateID']);
            if ($model === null) {
                $this->sAPI->sSetError("Category {$category['updateID']} not found", 10405);
                return false;
            }
            $model->fromArray($category);

            Shopware()->Models()->persist($model);
            Shopware()->Models()->flush();
        }


        // Create a new category if no model was set, yet
        if (!$model) {
            // try to find a existing category by name and parent
            if (isset($category['parent']) && isset($category['name']) ) {
                $model = $categoryRepository->findOneBy(array('parent' => $category['parent'], 'name' => $category['name']));
            }
            if (!$model) {
                $model = new \Shopware\Models\Category\Category();
            }

            if (isset($category['parent'])) {
                $parentModel = $categoryRepository->find((int) $category['parent']);
                if ($parentModel === null) {
                    $this->sAPI->sSetError("Parent category {$category['parent']} not found", 10406);
                    return false;
                }
            }

            $model ->fromArray($category);
            $model->setParent($parentModel);

            Shopware()->Models()->persist($model);
            Shopware()->Models()->flush();

        }

        // set category attributes
        $upset = array();
        for ($i=1;$i<=6;$i++) {
            if (isset($category['ac_attr'.$i])) {
                $upset['attribute'.$i] = (string) $category['ac_attr'.$i];
            } elseif (isset($category['attr'][$i])) {
                $upset['attribute'.$i] = (string) $category['attr'][$i];
            }
        }
        if (!empty($upset)) {
            $attributeID = Shopware()->Db()->fetchOne("SELECT id FROM s_categories_attributes WHERE categoryID=?", array($model->getId()));
            if ($attributeID === false) {
                $upset['categoryID'] = $model->getId();
                Shopware()->Db()->insert('s_categories_attributes', $upset);
            } else {
                Shopware()->Db()->update('s_categories_attributes',
                    $upset,
                    array('categoryID = ?' => $model->getId())
                );
            }
        }

        return $model->getId();
    }



    /**
     * Import von Artikel-Preisen
     *
     * @param array $price Enth�lt die zu importierenden Preise in Array-Form
     *
     * customergroup => Zugeordnete Kundengruppe (aus s_core_customergroups, G�ste/Shopbesucher='EK')
     *
     * baseprice => Einkaufspreis
     *
     * pseudoprice => Durchgestrichener Preis
     *
     * from => Angabe ab welcher Menge der Preis g�ltig ist
     *
     * price => Netto - VK
     *
     * articledetailsID => Details-ID des Artikels (s_articles_details.id)
     *
     * @access public
     * @return
     */
    public function sArticlePrice($price)
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
        if (empty($article)) {
            return false;
        }
        if (empty($price['price'])&&empty($price['percent'])) {
            return false;
        }
        if ($price['from']<=1 && empty($price['price'])) {
            return false;
        }

        // Delete old price, if pricegroup, articleDetailId and 'from' matches
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

        if (empty($price['price'])) {
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
            if (empty($price['price'])) {
                return false;
            }
            $price['price'] = $price['price']*(100-$price['percent'])/100;
        }

        if ($price['from']!=1) {
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
            if (empty($result)||!$this->sDB->Affected_Rows()) {
                return false;
            }
        }

        $sql = "
            INSERT INTO s_articles_prices (pricegroup, `from`, `to`, articleID, articledetailsID, price, pseudoprice, baseprice, percent)
            VALUES ({$price['pricegroup']}, {$price['from']}, 'beliebig', {$article['articleID']}, {$article['articledetailsID']}, {$price['price']}, {$price['pseudoprice']}, {$price['baseprice']}, {$price['percent']})
        ";
        $result = $this->sDB->Execute($sql);
        if (empty($result)) {
            return false;
        } else {
            return $this->sDB->Insert_ID();
        }

    }

    /**
     * @deprecated
     * L�schen von mehrdimensionalen Varianten (Artikel Konfigurator)
     *
     * @param int $articleID ID des Artikels (s_articles.id)
     * @return bool
     */
    public function sDeleteArticleConfigurator($articleID)
    {
        // Configurator sets don't belong to a specific article any longer.
        return true;
    }

    /**
     * @deprecated
     * Bild-Konvertierungsfunktion zur Generierung der Artikelbilder - Thumbnails
     *
     * @param string $picture Dateipfad des Quell-Bildes
     * @param int $new_width Breite des Thumbnails
     * @param int $new_height H�he des Thumbnails
     * @param string $newfile Dateiname des Thumbnails
     * @access public
     * @return
     */
    public function sResizePicture(&$image, $size, $new_width, $new_height)
    {
        $breite=$size[0]; //die Breite des Bildes
        $hoehe=$size[1]; //die H�he des Bildes

        // Verh�ltnis Breite zu H�he bestimmen
        $verhaeltnis = $breite/$hoehe;

        if ($breite < $new_width) {
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
     * * @deprecated
     * Bild-Konvertierungsfunktion zur Generierung der Artikelbilder - Thumbnails
     * Ber�cksichtigt zus�tzlich die H�he des Bildes bzw. das Verh�ltnis zwischen H�he
     * und Breite um auch "Hochkant-Bilder" passend skalieren zu k�nnen (feste H�he)
     *
     * @param string $picture Dateipfad des Quell-Bildes
     * @param int $new_width Breite des Thumbnails
     * @param int $new_height H�he des Thumbnails
     * @param string $newfile Dateiname des Thumbnails
     * @access public
     * @return
     */
    public function sResizePictureDynamic(&$image, $size, $new_width, $new_height)
    {
        $breite=$size[0]; //die Breite des Bildes
        $hoehe=$size[1]; //die H�he des Bildes

        // Verh�ltnis Breite zu H�he bestimmen

        if ($breite > $hoehe) {
            $verhaeltnis = $breite/$hoehe;
            $breite_neu = $new_width;
            $hoehe_neu = round($breite_neu / $verhaeltnis,0);
        } else {
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
     * @param array $article_image Array mit Informationen �ber die Bilder die konvertiert und dem Artikel zugewiesen werden müssen
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
    public function sArticleImage($article_image = array())
    {
        // Some initial checks
        if (empty($article_image)||!is_array($article_image))
            return false;
        if(isset($article_image["link"]))
            $article_image["image"] = $article_image["link"];
        if(isset($article_image["albumID"]))
            $article_image["albumID"] = (int) $article_image["albumID"];
        if(isset($article_image['articleID']))
            $article_image['articleID'] = intval($article_image['articleID']);
        if(empty($article_image['articleID'])||(empty($article_image['image'])&&empty($article_image['name'])))
            return false;
        if(!empty($article_image['position']))
            $article_image['position'] = intval($article_image['position']);
        else
            $article_image['position'] = 0;
        if(empty($article_image['description']))
            $article_image['description'] = '';
        if(empty($article_image['relations']))
            $article_image['relations'] = '';

        // Checks for main flag
        if(!empty($article_image['main'])&&$article_image['main']==1)
            $article_image['main'] = 1;
        elseif(!empty($article_image['main']))
            $article_image['main'] = 2;
        // If main is not set and no other article image is main, set this to be main
        if (empty($article_image['main'])) {
            $sql = "SELECT id FROM s_articles_img WHERE articleID={$article_image['articleID']} AND main=1";
            $row = $this->sDB->GetRow($sql);
            if(empty($row['id']))
                $article_image['main'] = 1;
            else
                $article_image['main'] = 2;
        // if this article image is set to be main, set all other images to be not-main
        } elseif ($article_image['main']==1) {
            $sql = "UPDATE s_articles_img SET main=2 WHERE articleID={$article_image['articleID']}";
            $this->sDB->Execute($sql);
        }

        // First copy the image to temp dir
        $uploadDir = Shopware()->DocPath('media_' . 'temp');
        // If no name is set, choose a random one
        if (empty($article_image['name'])) {
            $article_image['name'] =  md5(uniqid(mt_rand(), true));
        } else {
            $article_image['name']  = pathinfo($article_image['name'],  PATHINFO_FILENAME);;

        }

        // Copy image to local temp dir
        if (!empty($article_image['image'])) {

            $uploadFile = $uploadDir.$article_image['name'];
            if (!copy($article_image['image'], $uploadFile)) {
                $this->sAPI->sSetError("Copying image from '{$article_image['image']}' to '$uploadFile' did not work", 10400);
                return false;
            }

            // check the copied image
            if (getimagesize($uploadFile) === false) {
                unlink($uploadFile);
                $this->sAPI->sSetError("The file'$uploadFile' is not a valid image", 10402);
                return false;
            };
        } else {
            foreach (array('png', 'gif', 'jpg') as $test) {
                if (file_exists($uploadDir.$article_image['name'].'.'.$test)) {
                    $extension = $test;
                    $uploadFile = $uploadDir.$article_image['name'].'.'.$test;
                    break;
                }
            }
            if (empty($uploadFile)) {
                $this->sAPI->sSetError("Image source '$uploadFile' not found", 10401);
                return false;
            }
        }
        // Create new Media object and set the image
        $media = new \Shopware\Models\Media\Media();
        $file = new \Symfony\Component\HttpFoundation\File\File($uploadFile);

        $media->setDescription($article_image['description']);
        $media->setCreated(new DateTime());

        $identity = Shopware()->Auth()->getIdentity();
        if ($identity !== null) {
            $media->setUserId($identity->id);
        } else {
            $media->setUserId(0);
        }

        //set the upload file into the model. The model saves the file to the directory
        $media->setFile($file);

        // Set article
        $articleRepository = $this->getArticleRepository();
        $article = $articleRepository->find((int) $article_image['articleID']);
        if ($article === null) {
            $this->sAPI->sSetError("Article '{$article_image['articleID']}' not found", 10404);
            return false;
        }
//        $media->setArticles($article);
        //set media album, if no one passed set the unsorted album.
        if (isset($article_image["albumID"])) {
            $media->setAlbumId($article_image["albumID"]);
            $media->setAlbum(Shopware()->Models()->find('Shopware\Models\Media\Album', $article_image["albumID"]));
        } else {
            $media->setAlbumId(-1);
            $media->setAlbum(Shopware()->Models()->find('Shopware\Models\Media\Album', -1));
        }

        // Create new article image object and set values
        $articleImage = new \Shopware\Models\Article\Image();
        // Get image infos for the article image
        list($width, $height, $type, $attr) = getimagesize($uploadFile);
        // ArticleImage does not get these infos automatically from media, so we set them manually
        $articleImage->setDescription($article_image['description']);
        $articleImage->setMedia($media);
        $articleImage->setArticle($article);
        $articleImage->setWidth($width);
        $articleImage->setHeight($height);
        $articleImage->setHeight($height);
        $articleImage->setPath($article_image['name']);
        $articleImage->setExtension($media->getExtension());
        $articleImage->setPosition($article_image['position']);
        $articleImage->setMain($article_image['main']);
        $articleImage->setRelations($article_image['relations']);

        $article->setImages($articleImage);

        Shopware()->Models()->persist($media);
        Shopware()->Models()->persist($article);
        Shopware()->Models()->persist($articleImage);
        Shopware()->Models()->flush();

//        if ($article_image['relations'] !== '') {
//            $this->createImageRelationsFromArticleNumber($articleImage->getId(), $article->getId(), $article_image['relations']);
//        }

        return $articleImage->getId();
    }

    /**
     * Helper function which gets the configurator options associated with a given "relations" ordernumber.
     *
     * In SW4 image relations cannot be set via ordernumber - images are related to articles over configurator options.
     * So we need to get the options associated with the given ordernumber, first.
     * Then the image mapping is created based on that configurator options
     *
     * @param $imageId
     * @param $articleId
     * @param $number
     */
    private function createImageRelationsFromArticleNumber($imageId,$articleId, $number)
    {
        if (!$number) {
            return;
        }

        $detailId = Shopware()->DB()->fetchOne("SELECT id FROM s_articles_details WHERE ordernumber=?", array($number));
        if ($detailId === false) {
                return;
        }

        $sql = "SELECT cor.option_id as optionId
        FROM s_article_configurator_option_relations cor
        WHERE cor.article_id=?";

        $relations = Shopware()->Db()->fetchAll($sql, array($detailId));

        // Set mappings
        if (!empty($relations)) {
            foreach ($relations as $relation) {
                $optionModel = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Option', $relation['optionId']);

                Shopware()->Db()->insert('s_article_img_mappings', array(
                    'image_id' => $imageId
                ));
                $mappingID = Shopware()->Db()->lastInsertId();
                Shopware()->Db()->insert('s_article_img_mapping_rules', array(
                    'mapping_id' => $mappingID,
                    'option_id' => $optionModel->getId()
                ));
            }

            $this->recreateVariantImages($articleId);

        }

    }

    /**
     * Helper method which creates images for variants based on the image mappings
     * @param $articleId
     */
    protected function recreateVariantImages($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $images = $builder->select(array('images', 'mappings', 'rules', 'option'))
                ->from('Shopware\Models\Article\Image', 'images')
                ->innerJoin('images.mappings', 'mappings')
                ->leftJoin('mappings.rules', 'rules')
                ->leftJoin('rules.option', 'option')
                ->where('images.articleId = ?1')
                ->andWhere('images.parentId IS NULL')
                ->setParameter(1, $articleId)
                ->getQuery();

        $images = $images->execute();

        /** @var \Shopware\Models\Article\Image $image */
        foreach ($images as $image) {
            $query      = $this->getArticleRepository()->getArticleImageDataQuery($image->getId());
            $imageData  = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $this->getArticleRepository()->getDeleteImageChildrenQuery($image->getId())->execute();

            foreach ($image->getMappings() as $mapping) {
                $options = array();

                foreach ($mapping->getRules() as $rule) {
                    $options[] = $rule->getOption();
                }

                $imageData['path'] = null;
                $imageData['parent'] = $image;

                $details = $this->getArticleRepository()->getDetailsForOptionIdsQuery($articleId, $options)->getResult();

                foreach ($details as $detail) {
                    $newImage = new \Shopware\Models\Article\Image();
                    $newImage->fromArray($imageData);
                    $newImage->setArticleDetail($detail);
                    Shopware()->Models()->persist($newImage);
                    Shopware()->Models()->flush();
                }
            }
        }
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
    public function sDeleteArticleImages  ($article_image = array())
    {
        $articleID = $this->sGetArticleID($article_image);
        if (empty($articleID)) {
            return false;
        }

        $articleRepository = $this->getArticleRepository();

        $result = $articleRepository->getArticleImagesQuery($articleID)->getResult();
        foreach ($result as $imageModel) {
            Shopware()->Models()->remove($imageModel);

            // This if clause was kept from the old API for compability reason
            if (!isset($article_image["unlink"])||!empty($article_image["unlink"])) {
                $media = $imageModel->getMedia();
                if ($media instanceof \Shopware\Models\Media\Media) {
                    try {
                        Shopware()->Models()->remove($media);
                    } catch (\Doctrine\ORM\ORMException $e) {
                        return false;
                    }

                }
            }
        }

        Shopware()->Models()->flush();
        return true;

    }

    /**
     * L�schen aller nicht angegebenen Artikel-Bilder
     *
     * @param array $article_image
     *
     * $articles_image[articleID]
     *
     * @access public
     * @return
     */
    public function sDeleteOtherArticleImages($articleID, $imageIds = null)
    {
        $articleID = (int) $articleID;
        if (empty($articleID)) {
            return false;
        }
        $articleRepository = $this->getArticleRepository();

        $result = $articleRepository->getArticleImagesQuery($articleID)->getResult();
        foreach ($result as $imageModel) {
            if (!in_array($imageModel->getId(), $imageIds)) {
                Shopware()->Models()->remove($imageModel);
                Shopware()->Models()->remove($imageModel->getMedia());
            }
        }

        Shopware()->Models()->flush();

        return true;
    }

    /**
     * Einfügen einer Artikel-Kategorie-Zuordnung
     *
     * @param int $articleID ID des Artikels (s_articles.id)
     * @param int $categoryID ID der Kategorie (s_categories.id)
     * @access public
     * @return array  $inserts Array mit allen eingef�gten IDs aus s_articles_categories
     */
    public function sArticleCategory($articleID, $categoryID, $setParentCategories = false)
    {
        $categoryID = intval($categoryID);
        $articleID  = intval($articleID);

        if (empty($categoryID) || empty($articleID)) {
            return false;
        }

        $sql = "
            INSERT IGNORE INTO s_articles_categories (articleID, categoryID)

            SELECT $articleID as articleID, c.id as categoryID
            FROM `s_categories` c
            WHERE c.id IN ($categoryID)
        ";

        if ($this->sDB->Execute($sql) === false) {
            return false;
        }

        $this->getCategoryComponent()->addAssignment($articleID, $categoryID);

        $sql = "
            SELECT ac.id
            FROM `s_articles_categories` ac
            WHERE ac.categoryID IN ($categoryID)
            AND ac.articleID=$articleID
        ";
        $inserts = $this->sDB->GetCol($sql);

        return $inserts;
    }

    /**
     * Einf�gen von Artikel-Kategorie-Zuordnungen f�r mehrere Kategorien gleichzeitig
     *
     * @param array $article Muss einen der folgenden Werte beeinhalten
     *
     * ordernumber => Bestellnummer des Artikels
     *
     * articleID => Artikel-ID (s_articles.id)
     *
     * articledetailsID => Artikel-Details-Id (s_articles_details.id)
     *
     * @param array $categoryIDs Array mit den IDs einzuf�genden Kategorien (s_categories.id)
     * @return
     */
    public function sArticleCategories($article, $categoryIDs)
    {
        if(!($articleID = $this->sGetArticleID($article)))
            return false;
        $inserts = array();
        if (!empty($categoryIDs)&&is_array($categoryIDs)) {
            foreach ($categoryIDs as $categoryID) {
                $insert = $this->sArticleCategory  ($articleID, $categoryID);
                if(!empty($insert))
                    $inserts = array_merge($inserts, $insert);
            }
        }
        $this->sDeleteOtherArticlesCategories ($articleID, $inserts);
        return $inserts;
    }

    /**
     * Einf�gen von Cross-Selling Zuordnungen zwischen Artikeln
     *
     * @param int $article ID des Artikels (s_articles.id)
     * @param array $relatedarticleIDs IDs der Artikel die $article zugeordnet werden sollen [x,y,z]
     * @return
     */
    public function sArticleCrossSelling($article, $relatedarticleIDs)
    {
        $articleID = $this->sGetArticleID($article);
        if(empty($articleID))
            return false;
        $this->sDeleteArticleCrossSelling(array("articleID"=>$articleID));
        if(empty($relatedarticleIDs)||!is_array($relatedarticleIDs))
            return true;

        // In sw similar articles are no longer stored as ordernumbers but as articleIDs
        $sql = "SELECT ordernumber, articleID from s_articles_details WHERE ordernumber IN ('".implode('\',\'',array_map(Shopware()->Db()->quote, $relatedarticleIDs))."')";
        $articleIDs = Shopware()->Db()->fetchAssoc($sql);

        if (empty($articleIDs)) {
            return false;
        }


        foreach ($relatedarticleIDs as $relatedarticleID) {
            if(empty($relatedarticleID)) continue;

            $sql = "
                INSERT IGNORE INTO s_articles_relationships (articleID, relatedarticle)
                VALUES ($articleID, {$articleIDs[$relatedarticleID]['articleID']})
            ";

            $this->sDB->Execute($sql);
        }
        return true;
    }

    /**
     * L�schen aller bestehenden Cross-Selling Zuordnungen eines Artikels
     *
     * @param int $article ID des Artikels (s_articles.id)
     * @access public
     * @return
     */
    public function sDeleteArticleCrossSelling($article)
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
    * Einf�gen von Zugriffs Beschr�nkunden auf einen Artikeln
    *
    * @param int $article ID des Artikels (s_articles.id)
    * @param array $nopermissiongroupIDs IDs der Kundengruppen keine Zugriff auf $article haben sollen [x,y,z]
    * @access public
    * @return
    *
    * @author Holger
    */
    public function sArticlePermissions($article, $nopermissiongroupIDs)
    {
        $articleID = $this->sGetArticleID($article);
        if(empty($articleID))
            return false;

        $this->sDeleteArticlePermissions(array("articleID"=>$articleID));

        if(empty($nopermissiongroupIDs)||!is_array($nopermissiongroupIDs))
            return true;

        foreach ($nopermissiongroupIDs as $nopermissiongroupID) {
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
    * L�schen aller bestehenden Zugriffs Beschr�nkungen eines Artikels
    *
    * @param int $article ID des Artikels (s_articles.id)
    * @access public
    * @return
    *
    * @author Holger
    */
    public function sDeleteArticlePermissions($article)
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
    public function sArticleSimilar($article, $relatedarticleIDs)
    {
        $articleID = $this->sGetArticleID($article);
        if(empty($articleID))
            return false;
        $this->sDeleteArticleSimilar(array("articleID"=>$articleID));
        if(empty($relatedarticleIDs)||!is_array($relatedarticleIDs))
            return true;


        // In sw similar articles are no longer stored as ordernumbers but as articleIDs
        $sql = "SELECT ordernumber, articleID from s_articles_details WHERE ordernumber IN ('".implode('\',\'',array_map(Shopware()->Db()->quote, $relatedarticleIDs))."')";
        $articleIDs = Shopware()->Db()->fetchAssoc($sql);

        if (empty($articleIDs)) {
            return false;
        }

        foreach ($relatedarticleIDs as $relatedarticleID) {
            if(empty($relatedarticleID)) continue;

            $sql = "
                INSERT IGNORE INTO s_articles_similar (articleID, relatedarticle)
                VALUES ($articleID, {$articleIDs[$relatedarticleID]['articleID']})
            ";
            $this->sDB->Execute($sql);
        }
        return true;
    }




    /**
     * L�schen aller bestehenden Cross-Selling Zuordnungen eines Artikels
     *
     * @param int $article ID des Artikels (s_articles.id)
     * @access public
     * @return
     */
    public function sDeleteArticleSimilar($article)
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
     * Einf�gen von Links (Weitere Informationen) zu einem Artikel
     *
     * @param int $article ID des Artikels (s_articles.id)
     * @param string $article_link Hyperlink
     * @access public
     * @return
     */
    public function sArticleLink($article_link)
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
     * L�schen aller bestehenden Artikel-Links
     *
     * @param int $article ID des Artikels (s_articles.id)
     * @access public
     * @return
     */
    public function sDeleteArticleLinks($article)
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
    public function sDeleteOtherArticlesCategories($articleID, $categoryIDs)
    {
        $articleID = intval($articleID);

        if (empty($articleID)) {
            return false;
        }

        if (!empty($categoryIDs) && is_array($categoryIDs)) {
            $where = "AND id !=" . implode(" AND id!=", $categoryIDs) . "";
        } elseif (!empty($categoryIDs)) {
            $where = "AND id !=" . intval($categoryIDs);
        } else {
            $where = "";
        }

        $categoriesToDeleteSql = "SELECT categoryID FROM
            s_articles_categories
            WHERE articleID=$articleID $where";

        $categoriesToDelete = $this->sDB->GetCol($categoriesToDeleteSql);

        $sql = "
            DELETE FROM
                s_articles_categories
            WHERE articleID=$articleID $where
        ";
        $this->sDB->Execute($sql);

        foreach ($categoriesToDelete as $categoryId) {
            $this->getCategoryComponent()->removeAssignment($articleID, $categoryId);
        }

        return true;
    }

    /**
     * Deletes not specified categories
     *
     * @param array $categoryIDs
     * @access public
     * @return
     */
    public function sDeleteOtherCategories($categoryIDs)
    {
        if (empty($categoryIDs)||!is_array($categoryIDs))
            return false;

        $sql = "
            DELETE c FROM s_categories c
            LEFT JOIN s_core_shops cs ON c.id=cs.category_id
            WHERE c.id NOT IN (".implode(",",$categoryIDs).")
            AND cs.id IS NULL
            AND c.parent IS NOT NULL
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

        foreach ($categoryIDs as $categoryId) {
            $this->getCategoryComponent()->removeCategoryAssignmentments($categoryId);
        }

        return true;
    }

    /**
     * Einf�gen von Herstellern
     * @param array $supplier
     *
     * supplier => Name des Herstellers
     *
     * @access public
     * @return int ID des eingef�gten / aktualisierten Herstellers
     */
    public function sSupplier($supplier)
    {
        if(empty($supplier)||!is_array($supplier))
            return false;
        if(empty($supplier['supplier'])&&empty($supplier['supplierID']))
            return false;

        // quote
        if(isset($supplier['supplier']))
            $supplier['supplier'] = $this->sDB->qstr($this->sValDescription($supplier['supplier']));

        // Get pre-existing supplier by id or by name if no id was given
        if (empty($supplier['supplierID'])) {
            $sql = "SELECT id, img, link FROM s_articles_supplier WHERE name = {$supplier['supplier']}";
            $row = $this->sDB->GetRow($sql);
            if (empty($row['id'])) {
                $supplier['supplierID'] = 0;
                $supplier['old_image'] = "";
                $supplier['old_link'] = "";
            } else {
                $supplier['supplierID'] = $row['id'];
                $supplier['old_image'] = $row['img'];
                $supplier['old_link'] = $row['link'];
            }
        } else {
            $supplier['supplierID'] = intval($supplier['supplierID']);
            $sql = "SELECT id, img, link, name FROM s_articles_supplier WHERE id = {$supplier['supplierID']}";
            $row = $this->sDB->GetRow($sql);
            if (empty($row['id'])) {
                $supplier['supplierID'] = 0;
                $supplier['old_image'] = "";
                $supplier['old_link'] = "";
                $supplier['old_supplier'] = "";
            } else {
                $supplier['supplierID'] = $row['id'];
                $supplier['old_image'] = $row['img'];
                $supplier['old_link'] = $row['link'];
                $supplier['old_supplier'] = $row['name'];
            }
        }

        if (!isset($supplier['supplier'])) {
            $supplier['supplier'] = $this->sDB->qstr($supplier['old_supplier']);
            unset($supplier['old_supplier']);
        }

        if(empty($supplier['supplier'])&&empty($supplier['supplierID']))
            return false;

        if (!isset($supplier['link'])) {
            $supplier['link'] = $supplier['old_link'];
            unset($supplier['old_link']);
        }

        //todo@dn: fix supplier paths. Probaly just /media/images...
        $supplierimages = realpath($this->sPath.$this->sSystem->sCONFIG['sSUPPLIERIMAGES'])."/";
        if (!empty($supplier['old_image'])&&isset($supplier['image'])) {
            unlink($supplierimages . $supplier['old_image' ]. ".jpg");
            unset($supplier['old_image']);
        }

        if (!empty($supplier['image'])) {
            $size = @getimagesize($supplier['image']);
            if (!empty($size[2])&&$size[2]==2) {
                $supplier['image'] = md5(uniqid(rand()));
                if (copy($supplier['image'], $supplierimages.$supplier['image'].".jpg")) {
                    chmod($supplierimages.$supplier['image'].".jpg",$this->sSettings["chmod"]);
                } else {
                    $supplier['image'] = "";
                }
            } else {
                $supplier['image'] = "";
            }
        } elseif (!empty($supplier['old_image'])&&!isset($supplier['image'])) {
            $supplier['image'] = $supplier['old_image'];
        } else {
            $supplier['image'] = "";
        }

        $supplier['link'] = $this->sDB->qstr($supplier['link']);
        $supplier['image'] = $this->sDB->qstr($supplier['image']);

        if (empty($supplier['supplierID'])) {
            $sql = "
                INSERT INTO `s_articles_supplier` (name, img, link)
                VALUES ({$supplier['supplier']}, {$supplier['image']}, {$supplier['link']})
            ";
            $this->sDB->Execute($sql);
            $supplier['supplierID'] = $this->sDB->Insert_ID();
        } else {
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
     * Aktualisierung von Lagerbest�nden
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
    public function sArticleStock ($article_stock = array())
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
     * Internal helper function to remove the detail esd configuration quickly.
     * @param $articleId
     */
    private function removeArticleEsd($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Esd', 'esd')
                ->where('esd.articleId = :id')
                ->setParameter('id',$articleId)
                ->getQuery()
                ->execute();
    }

    /**
     * @param $article \Shopware\Models\Article\Article
     */
    private function removeArticleDetails($article)
    {
        $sql= "SELECT id FROM s_articles_details WHERE articleID = ? AND kind != 1";
        $details = Shopware()->Db()->fetchAll($sql, array($article->getId()));

        foreach ($details as $detail) {
            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->delete('Shopware\Models\Article\Image', 'image')
                    ->where('image.articleDetailId = :id')
                    ->setParameter('id', $detail['id'])
                    ->getQuery()
                    ->execute();

            $sql= "DELETE FROM s_article_configurator_option_relations WHERE article_id = ?";
            Shopware()->Db()->query($sql, array($detail['id']));

            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->delete('Shopware\Models\Article\Detail', 'detail')
                    ->where('detail.id = :id')
                    ->setParameter('id', $detail['id'])
                    ->getQuery()
                    ->execute();
        }
    }

    /**
     * Delete article method
     *
     * @param mixed $article
     * @return bool
     */
    public function sDeleteArticle($article)
    {
        if(is_int($article))
            $article = array("articleID"=>$article);
        elseif(is_string($article))
            $article = array("ordernumber"=>$article);

        if (!empty($article["articleID"])) {
            $article["articleID"] = intval($article["articleID"]);
            $article["kind"] = 1;
            $article["articledetailsID"] = $this->sDB->GetOne("SELECT id FROM s_articles_details WHERE articleID={$article['articleID']}");
            if(empty($article["articledetailsID"]))
                return false;
        } else {
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
        if ($article["kind"] == 1) {
            $this->sDeleteArticleImages($article);
            $this->sDeleteArticleConfigurator($article);
            $this->sDeleteArticleDownloads($article);
            $this->sDeleteArticleLinks($article);
            $this->sDeleteArticlePermissions($article);

            $sql = "DELETE FROM s_articles WHERE id = {$article['articleID']}";
            $this->sDB->Execute($sql);

            $sql = "SELECT id FROM s_articles_esd WHERE articleID = {$article['articleID']}";
            $esdIDs = $this->sDB->GetCol($sql);
            if (!empty($esdIDs)) {
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
                "s_emarketing_lastarticles",
                "s_articles_translations",
                "s_articles_avoid_customergroups"
            );
            foreach ($delete_tables as $delete_table) {
                $sql = "DELETE FROM $delete_table WHERE articleID={$article['articleID']}";
                $this->sDB->Execute($sql);
            }
            $this->sDeleteTranslation(array('article','configuratoroption','configuratorgroup','accessoryoption','accessorygroup','propertyvalue'),$article['articleID']);

            $sql = 'DELETE FROM s_core_rewrite_urls WHERE org_path=?';
            $this->sDB->Execute($sql, array('sViewport=detail&sArticle=' . $article['articleID']));
        } else {
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
    public function sDeactivateArticle($article)
    {
        $article = $this->sGetArticleID($article);
        $sql = 'UPDATE s_articles SET active=0 WHERE id='.$article;
        return $this->sDB->Execute($sql);

    }

    /**
     * L�schen aller nihct angegebenen Artikel
     *
     * @param array $article Array mit Artikel-IDs (s_articles.id)
     * @access public
     * @return
     */
    public function sDeleteOtherArticles ($articles = array())
    {

        if (empty($articles['articledetailsIDs'])&&empty($articles['articleIDs'])) {
            if(!empty($articles)&&is_array($articles))
                $articles['articledetailsIDs'] = $articles;
            else
                return false;
        }
        if (!empty($articles['articleIDs'])&&is_array($articles['articleIDs'])) {
            $where =  "a.id NOT IN (".implode(",",$articles['articleIDs']).")";
        } else {
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
    public function sDeleteAllArticles()
    {

        // Deleting all files in a given directory is not valid in sw4. Instead get all Downloads and images from
        // s_article_downloads and s_article_img

        // Delete Downloads
        $sql = "SELECT filename AS path FROM s_articles_downloads
            UNION
            SELECT img AS path FROM s_articles_img";
        $result = $this->sDB->GetCol($sql);
        foreach ($result as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $tabellen = array(
            "s_articles",
            "s_articles_attributes",
            "s_articles_avoid_customergroups",
            "s_articles_categories",
            "s_articles_details",
            "s_articles_downloads",
            "s_articles_downloads_attributes",
            "s_articles_esd",
            "s_articles_esd_attributes",
            "s_articles_esd_serials",
            "s_articles_img",
            "s_articles_img_attributes",
            "s_articles_information",
            "s_articles_information_attributes",
            "s_articles_notification",
            "s_articles_prices",
            "s_articles_prices_attributes",
            "s_articles_relationships",
            "s_articles_similar",
            "s_articles_supplier",
            "s_articles_supplier_attributes",
            "s_articles_translations",
            "s_articles_vote",
            "s_article_configurator_dependencies",
            "s_article_configurator_groups",
            "s_article_configurator_options",
            "s_article_configurator_option_relations",
            "s_article_configurator_price_surcharges",
            "s_article_configurator_sets",
            "s_article_configurator_set_group_relations",
            "s_article_configurator_set_option_relations",
            "s_article_img_mappings",
            "s_article_img_mapping_rules"
        );
        foreach ($tabellen as $tabelle) {
            $this->sDB->Execute("TRUNCATE `$tabelle`;");
        }
        $sql = "DELETE FROM s_core_translations WHERE objecttype IN ('article','variant','configuratoroption','configuratorgroup','accessoryoption','accessorygroup','propertyvalue','link')";
        $this->sDB->Execute($sql);
        return true;
    }

    /**
     * Alle Kategorien l�schen (z.B. bei komplettem Neu-Import notwendig)
     *
     * @return
     */
    public function sDeleteAllCategories()
    {
        $sql = "SELECT category_id FROM s_core_shops";
        $cols = $this->sDB->GetCol($sql);
        if (empty($cols)) {
            $sql = "TRUNCATE s_categories";
        } else {
            $cols = "id!=".implode(" AND id!=",$cols);
            $sql = "DELETE FROM s_categories WHERE parent IS NOT NULL AND $cols";
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

        $sql = "SELECT MAX(category_id) FROM  s_core_shops";
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
     * Alle Dateien in einem Verzeichnis l�schen
     *
     * @param string $dir Verzeichnis
     * @param bool $rek Rekursives l�schen
     * @return
     */
    public function sDeleteAllFiles($dir, $rek=false)
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
            if (is_dir($dir . $file)) {
                if ($rek) {
                    if(!$this->sDeleteAllFiles($dir . $file, $rek))
                        continue;
                    if(!rmdir($dir . $file))
                        continue;
                }
            } elseif (is_file($dir . $file)) {
                if(!unlink($dir . $file))
                    continue;
            }
        }
        closedir($dh);
        return true;
    }

    /**
     * Preis formatiert zur�ckgeben
     *
     * @param double $price
     * @access public
     * @return
     */
    public function sFormatPrice($price = 0)
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
    public function sValFloat($value)
    {
        return floatval(str_replace(",",".",$value));
    }

    /**
     * Clear description method
     *
     * @param string $description
     * @return string
     */
    public function sValDescription($description)
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
     * Details-ID (s_articles_details.id) eines Artikels zur�ckgeben
     *
     * @param array $article
     *
     * articleID => s_articles.id
     *
     * @access public
     * @return Details-ID des Artikels oder FALSE
     */
    public function sGetArticledetailsID($article)
    {
        if(empty($article['articleID'])&&empty($article['articledetailsID'])&&empty($article['ordernumber']))
            return false;
        if (!empty($article['articledetailsID'])) {
            $article['articledetailsID'] = intval($article['articledetailsID']);
            $sql = "id = {$article['articledetailsID']}";
        } elseif (!empty($article['articleID'])) {
            $article['articleID'] = intval($article['articleID']);
            $sql = "articleID = {$article['articleID']} AND kind = 1";
        } else {
            $article['ordernumber'] = $this->sDB->qstr((string) $article['ordernumber']);
            $sql = "ordernumber = {$article['ordernumber']}";
        }
        $sql = "SELECT id FROM s_articles_details WHERE $sql";
        $row = $this->sDB->GetRow($sql);
        if(empty($row['id']))
            return false;
        return $row['id'];
    }

    /**
     * ID (s_articles.id) eines Artikels zur�ckgeben z.B. anhand der Bestellnummer
     *
     * @param mixed $article
     * @return Details-ID des Artikels oder FALSE
     */
    public function sGetArticleID($article)
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
        if (!empty($article['articleID'])) {
            $article['articleID'] = intval($article['articleID']);
            $sql .= "articleID = {$article['articleID']}";
        } elseif (!empty($article['articledetailsID'])) {
            $article['articledetailsID'] = intval($article['articledetailsID']);
            $sql .= "id = {$article['articledetailsID']}";
        } else {
            $article['ordernumber'] = $this->sDB->qstr((string) $article['ordernumber']);
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
    public function sGetArticleNumbers($article)
    {
        if(empty($article['articleID'])&&empty($article['articledetailsID'])&&empty($article['ordernumber']))
            return false;
        if (!empty($article['articledetailsID'])) {
            $article['articledetailsID'] = intval($article['articledetailsID']);
            $sql = "id = {$article['articledetailsID']}";
        } elseif (!empty($article['articleID'])) {
            $article['articleID'] = intval($article['articleID']);
            $sql = "articleID = {$article['articleID']} AND kind = 1";
        } else {
            $article['ordernumber'] = $this->sDB->qstr((string) $article['ordernumber']);
            $sql = "ordernumber = {$article['ordernumber']}";
        }
        $sql = "SELECT id as articledetailsID, ordernumber, articleID FROM s_articles_details WHERE $sql";
        $row = $this->sDB->GetRow($sql);
        if(empty($row['articledetailsID']))
            return false;
        return $row;
    }

    /**
     * Gibt die Details-ID (s_articles_details) eines Hauptartikels zur�ck
     *
     * @param mixed $article
     * @access public
     * @return Details-ID des Artikels oder FALSE
     */
    public function sGetMainArticleID($article)
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
    public function sDeleteCache()
    {
        foreach (glob($this->sPath."/cache/templates*", GLOB_ONLYDIR) as $cachedir) {
            $this->sDeleteAllFiles ($cachedir, true);
        }
        $this->sDeleteAllFiles ($this->sPath."/engine/vendor/html2ps/cache/", true);
        $this->sDeleteArticleCache();
    }

    /**
     * Delete article cache
     */
    public function sDeleteArticleCache()
    {
        $this->sDeleteAllFiles ($this->sPath."/cache/database/", true);
        $this->sDeleteAllFiles ($this->sPath."/cache/vars/", true);
        //$this->sDeleteAllFiles ($this->sPath."/files/article_pdf/", true);
    }

    /**
     * Delete empty categories
     */
    public function sDeleteEmptyCategories()
    {
        $sql = "
                DELETE ac
                FROM s_articles_categories ac
                LEFT JOIN s_categories c
                    ON c.id = ac.categoryID
                LEFT JOIN s_articles a
                    ON  a.id = ac.articleID
                WHERE c.id IS NULL OR a.id IS NULL
            ";
        Shopware()->Db()->exec($sql);

        $sql = "
                SELECT
                c.id, COUNT(ac.id) as articleCount
                FROM s_categories c
                    LEFT JOIN s_articles_categories ac
                        ON ac.categoryID = c.id
                GROUP BY c.id
                HAVING articleCount = 0
                AND c.id <> 1
                AND c.id NOT IN (SELECT category_id FROM s_core_shops)
         ";

        $emptyCategories = Shopware()->Db()->fetchCol($sql);
        $result = 0;
        if (count($emptyCategories)) {
            $result = Shopware()->Db()->delete('s_categories', array('id IN(?)' => $emptyCategories));
        }
    }

    /**
     * Delete given categories
     */
    public function sDeleteCategories($categories)
    {
        if(empty($categories)||!is_array($categories))
            return false;

        // Delete categories
        $categoryRepository = $this->getCategoryRepository();
        foreach ($categories as $category) {
            $model = $categoryRepository->find($category);
            if ($model !== null) {
                Shopware()->Models()->remove($model);
            }
        }
        Shopware()->Models()->flush();

        // Delete connected article_categories
        $where = 'categoryID IN ('.implode(',',$categories).')';
        $sql = "DELETE FROM s_articles_categories WHERE $where;";
        if($this->sDB->Execute($sql)===false)
            return false;

        return true;
    }

    /**
     * Repair article category relations
     */
    public function sRepairArticleCategories()
    {
        $sql = "SELECT articleID, categoryID FROM s_articles_categories";
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
    public function sDeleteTranslation($type, $objectkey = null, $language = null)
    {
        if (empty($type)) {
            return false;
        } elseif (is_array($type)) {
            foreach ($type as &$value) {
                $value = $this->sDB->qstr($value);
            }
            $type = implode(',',$type);
        } else {
            $type = $this->sDB->qstr($type);
        }
        $sql = 'DELETE FROM s_core_translations WHERE objecttype IN ('.$type.')';
        if (!empty($objectkey)) {
            if (is_array($objectkey)) {
                foreach ($objectkey as &$value) {
                    $value = $this->sDB->qstr($value);
                }
                $objectkey = implode(',',$objectkey);
            } else {
                $objectkey = $this->sDB->qstr($objectkey);
            }
            $sql .=  ' AND objectkey IN ('.$objectkey.')';
        }
        if (!empty($language)) {
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
    public function sTranslation($type, $objectkey, $language, $data)
    {
        if(empty($type)||empty($objectkey)||empty($language))
            return false;
        if(empty($data))
            return $this->sDeleteTranslation($type, $objectkey, $language);

        switch ($type) {
            case "article":
            case "variant":
                if ($type== "article") {
                    $map = array(
                        "txtArtikel"=>"name",
                        "txtshortdescription"=>"description",
                        "txtlangbeschreibung"=>"description_long",
                        "txtzusatztxt"=>"additionaltext",
                        "txtkeywords"=>"keywords",
                    );
                } else {
                    $map = array(
                        "txtzusatztxt"=>"additionaltext"
                    );
                }
                $tmp = array();
                foreach ($map as $key => $name) {
                    if(isset($data[$key]))
                        $tmp[$key] = $data[$key];
                    elseif(isset($data[$name."_".$language]))
                        $tmp[$key] = $data[$name."_".$language];
                    elseif(isset($data[$name]))
                        $tmp[$key] = $data[$name];
                    if(empty($tmp[$key])) unset($tmp[$key]);
                }
                for ($i=1;$i<=20;$i++) {
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
                foreach ($data as $key => &$value) {
                    if(is_array($value)&&isset($value['gruppenName']))
                        $value = $value['gruppenName'];
                    if (!is_string($value)||!is_int($key)) {
                        unset($data[$key]);
                        continue;
                    }
                    $value = array('optionName'=>$this->sValDescription($value));
                } else $data = false;
                break;
            case "configuratorgroup":
                if(!empty($data)&&is_array($data))
                foreach ($data as $key => &$value) {
                    if(is_string($value))
                        $value = array('gruppenName'=>$value);
                    if (!is_array($value)||!is_int($key)||$key<1||$key>10) {
                        unset($data[$key]);	continue;
                    }
                    if(isset($value['gruppenName']))
                        $value['gruppenName'] = $this->sValDescription($value['gruppenName']);
                    $this->sValDescription($value);
                } else $data = false;
                break;
            case "accessorygroup":
                //1 => accessoryName
                break;
            case "accessoryoption":
                //1 => accessoryoption
                break;
            case "propertyvalue":
                // in sw4 propertyvalue is a 1-dimensional array
                if (!empty($data)&&is_array($data)) {
                    $data = array('optionValue' => $this->sValDescription($data[0]));
                } else {
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

        $type = $this->sDB->qstr((string) $type);
        $objectkey = $this->sDB->qstr((string) $objectkey);
        $language = $this->sDB->qstr((string) $language);
        $data = $this->sDB->qstr(serialize($data));
        $sql = "SELECT id FROM s_core_translations WHERE objecttype=$type AND objectkey=$objectkey AND objectlanguage=$language";
        $id = $this->sDB->GetOne($sql);
        if (empty($id)) {
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
        } else {
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
    public function sDeleteArticleDownloads($article_download)
    {
        $articleID = $this->sGetArticleID($article_download);
        if (empty($articleID)) {
            return false;
        }

        $articleRepository = $this->getArticleRepository();
        $article = $articleRepository->find($articleID);
        if ($article === null) {
            return false;
        }

        $downloads = $article->getDownloads();

        foreach ($downloads as $downloadModel) {
            $filename = $downloadModel->getFile();
            Shopware()->Models()->remove($downloadModel);

            // This if clause was kept from the old API for compability reason
            if (!isset($article_download["unlink"])||!empty($article_download["unlink"])) {
                $mediaList = $this->getMediaRepository()->findBy(
                    array('path' => $filename)
                );
                foreach ($mediaList as $mediaModel) {
                    Shopware()->Models()->remove($mediaModel);
                }
            }
        }

        $sql = 'SELECT id FROM s_articles_downloads WHERE articleID='.$articleID;
              $downloads = $this->sDB->GetCol($sql);
              if(!empty($downloads))
                  $this->sDeleteTranslation('download',$downloads);

        Shopware()->Models()->flush();
        return true;

    }

    /**
     * Insert an article download
     *
     * @param unknown_type $article_download
     * @return unknown
     */
    public function sArticleDownload($article_download)
    {
        if (empty($article_download)||!is_array($article_download))
            return false;
        $article_download['articleID'] = $this->sGetArticleID($article_download);
        if(empty($article_download['articleID']))
            return false;
        if(empty($article_download["name"])&&empty($article_download["link"]))
            return false;

        if (empty($article_download["name"])) {
            $article_download["name"] = basename($article_download["link"]);
        }
        // In sw4 description will only be used for the media model.
        if (empty($article_download["description"])) {
            $article_download["description"] = basename($article_download["name"]);
        }

        if (!empty($article_download["link"])) {
            $article_download["new_link"] = Shopware()->DocPath('media_' . 'temp').basename($article_download["link"]) ;
        } else {
            $article_download["new_link"] = Shopware()->DocPath('media_' . 'temp').$article_download["name"] ;
        }

        if (strpos($article_download["link"], Shopware()->DocPath('media_' . 'temp')) !== false) {
            $this->sAPI->sSetError("Please don't copy your source files to media/temp", 10410);
            return false;
        }

        if (!empty($article_download["link"])) {
            if(file_exists($article_download["new_link"]))
                unlink($article_download["new_link"]);
            if(!copy($article_download["link"],$article_download["new_link"]))
                return false;
            if(!empty($article_download["unlink"])&&file_exists($article_download["link"]))
                unlink($article_download["link"]);
        } else {
            if(!file_exists($article_download["new_link"]))
                return false;
        }

        $article_download["size"] = filesize($article_download["new_link"]);
        $article_download["name"] = $article_download["name"];

        // Create new Media object and set the download
        $media = new \Shopware\Models\Media\Media();
        $file = new \Symfony\Component\HttpFoundation\File\File($article_download["new_link"]);

        $media->setDescription($article_download['description']);
        $media->setCreated(new DateTime());

        $identity = Shopware()->Auth()->getIdentity();
        if ($identity !== null) {
            $media->setUserId($identity->id);
        } else {
            $media->setUserId(0);
        }

        //set the upload file into the model. The model saves the file to the directory
        $media->setFile($file);

        $pathInfo = pathinfo($article_download["new_link"]);
        if ($media->getExtension() === null && $pathInfo['extension'] === null) {
            $this->sAPI->sSetError("In SW4 files must have an extension", 10409);
            return false;
        } elseif ($pathInfo['extension'] !== null) {
            $media->setExtension($pathInfo['extension']);
        }


        // Set article
        $articleRepository = $this->getArticleRepository();
        $article = $articleRepository->find((int) $article_download['articleID']);
        if ($article === null) {
            $this->sAPI->sSetError("Article '{$article_download['articleID']}' not found", 10408);
            return false;
        }
        $media->setArticles($article);
        //set media album, if no one passed set the unsorted album.
        if (isset($article_download["albumID"])) {
            $media->setAlbumId($article_download["albumID"]);
            $media->setAlbum(Shopware()->Models()->find('Shopware\Models\Media\Album', $article_download["albumID"]));
        } else {
            $media->setAlbumId(-10);
            $media->setAlbum(Shopware()->Models()->find('Shopware\Models\Media\Album', -10));
        }

        // Create new article download object and set values
        $articleDownload = new \Shopware\Models\Article\Download();
        // ArticleDownload does not get these infos automatically from media, so we set them manually
        $articleDownload->setArticle($article);
        $articleDownload->setSize($article_download["size"]);
        $articleDownload->setFile($media->getPath());
        $articleDownload->setName($article_download["name"]);

        $article->setDownloads($articleDownload);

        Shopware()->Models()->persist($media);
        Shopware()->Models()->persist($article);
        Shopware()->Models()->persist($articleDownload);
        Shopware()->Models()->flush();

        return (int) $articleDownload->getId();

    }

    /**
     *  Delete article attribute group
     *
     * @param int|array $articleID
     * @return bool
     */
    public function sDeleteArticleAttributeGroup($articleID)
    {
        $articleID = $this->sGetArticleID($articleID);

        $articleRepository = $this->getArticleRepository();
        $article = $articleRepository->find((int) $article['articleID']);
        $article->setPropertyGroup(null);

        Shopware()->Models()->persist($article);
        Shopware()->Models()->flush();

        return true;
    }

    /**
     *  This method has been deprecated as there is  no 1:1 article:value relation any more
     *
     * @param int|array $articleID
     * @return bool
     */
    public function sDeleteArticleAttributeGroupValues($articleID)
    {
        $this->sAPI->sSetError("This method has been deprecated, as articles now share attributes", 10501);
        return false;
    }

    /**
     * Insert an article attribute group. In respect of some changes in sw4, you'll need to pass a attributeoptionID as well
     * Also: Translations
     *
     * @param int|array $article
     * @return bool
     */
    public function sArticleAttributeGroup($article)
    {
        if(empty($article)||!is_array($article)) return false;
        $article["articleID"] = $this->sGetArticleID($article);
        if(empty($article["articleID"])) return false;

        // If attributegroupID is not set, set filtergroupID to null
        if (empty($article["attributegroupID"])) {
            return $this->sDeleteArticleAttributeGroup((int) $article["articleID"]);
        }

        // New for sw4: OptionID also needed!
        if (empty($article["attributeoptionID"])) {
            $this->sAPI->sSetError("You need to specify 'attributeoptionID'", 10502);
            return false;
        }


        // If no values were passed, we're done
        if (empty($article["values"]) || !is_array($article["values"])) {
            return true;
        }


        // Get locales as iso-code. Needed for backward compability
        $sql = "SELECT DISTINCT LEFT(locale, 2) as isocode, l.id as id FROM s_core_shops s
                  INNER JOIN s_core_locales l ON s.locale_id = l.id
                  AND LEFT(l.locale,2) <> 'de'";
        $languages = $this->sDB->getAll($sql);

        // Check if value-lengths match
        foreach ($languages as $languageArray) {
            $language = $languageArray['isocode'];
            if(!isset($article['values_'.$language])) continue;

            if (count($article['values_'.$language]) !== count($article['values'])) {
              $this->sAPI->sSetError("Value-Arrays may not differ in length", 10508);
              return false;
            }
        }

        // Models needed to persis
        $persistModels = array();

        //Get the property group
        $groupModel = Shopware()->Models()->getRepository('\Shopware\Models\Property\Group')->find((int) $article["attributegroupID"]);
        if ($groupModel === null) {
            $this->sAPI->sSetError("Group not found", 10503);
            return false;
        }

        $optionModel = Shopware()->Models()->getRepository('\Shopware\Models\Property\Option')->find((int) $article["attributeoptionID"]);
        if ($optionModel === null) {
            $this->sAPI->sSetError("Option not found", 10504);
            return false;
        }

        // Check if the group already owns this option. If it doesn't: Add it
        $found = false;
        foreach ($groupModel->getOptions() as $option) {
            if ($option == $optionModel) {
                $found = true;
            }
        }
        if ($found === false) {
            $groupModel->addOption($optionModel);
        }

        // get article, set option and group
        $articleRepository = $this->getArticleRepository();
        $articleModel = $articleRepository->find((int) $article['articleID']);
        if ($articleModel === null) {
            return false;
        }
        $articleModel->setPropertyGroup($groupModel);

        // Set persist models
        $persistModels[] = $articleModel;
        $persistModels[] = $groupModel;
        $persistModels[] = $optionModel;

        // Update/create value models
        $valueRepository = Shopware()->Models()->getRepository('\Shopware\Models\Property\Value');
        foreach ($article['values'] as $key => $value) {
            $valueModel = $valueRepository->findOneBy(array(
                'value' => $value,
                'option' => $optionModel
            ));
            if ($valueModel === null) {
                $valueModel = new \Shopware\Models\Property\Value();
                $valueModel->setValue($value);
                $valueModel->setOption($optionModel);
            }
            $persistModels[] = $valueModel;
        }

        // Persist models
        foreach ($persistModels as $model) {
            Shopware()->Models()->persist($model);
        }
        Shopware()->Models()->flush();


        foreach ($languages as $languageArray) {
            $language = $languageArray['isocode'];
            $localeID = $languageArray['id'];

            if(!isset($article['values_'.$language])) continue;

            foreach ($article['values_'.$language] as $key=>$value) {
                $valueModel = $valueRepository->findOneBy(array(
                    'value' => $article['values'][$key],
                    'option' => $optionModel
                ));
                $this->sTranslation("propertyvalue", $valueModel->getId(), $localeID, array($value));
            }
        }
        return true;
    }

    /**
     * Update an order method
     *
     * @param array $order
     * @return bool
     */
    public function sOrder($order)
    {
        if(!empty($order['orderID']))
            $order['orderIDs'] = array($order['orderID']);
        if (!empty($order['orderIDs'])&&is_array($order['orderIDs'])) {
            foreach ($order['orderIDs'] as &$orderID) $orderID = (int) $orderID;
            $order['where'] = "`id` IN (".implode(",",$order['orderIDs']).")\n";
        } elseif (!empty($order['ordernumber'])) {
            $order['where'] = "`ordernumber` = ".$this->sDB->qstr($order['ordernumber'])."\n";
        } elseif (empty($order['where'])) {
            return false;
        }

        $upset = array();
        if(isset($order['statusID']))
            $upset[] = "status=".intval($order['statusID']);
        if(isset($order['clearedID']))
            $upset[] = "cleared=".intval($order['clearedID']);
        if(isset($order['trackingID']))
            $upset[] = "trackingcode=".$this->sDB->qstr((string) $order['trackingID']);
        if(isset($order['transactionID']))
            $upset[] = "transactionID=".$this->sDB->qstr((string) $order['transactionID']);
        if(isset($order['comment']))
            $upset[] = "comment=".$this->sDB->qstr((string) $order['comment']);
        if(!empty($article['cleareddate']))
            $upset[] = "cleareddate=".$order['cleareddate'];
        elseif (isset($article['cleareddate'])) {
            $upset[] = "cleareddate='0000-00-00'";

        }
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
    public function sArticleTranslation($article)
    {
        $sql = "SELECT DISTINCT LEFT(locale, 2) as isocode, l.id as id FROM s_core_shops s
                  INNER JOIN s_core_locales l ON s.locale_id = l.id
                  AND LEFT(l.locale,2) <> 'de'";
        $languages = $this->sDB->getAll($sql);

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

        if ($article_data['kind']==1) {
            $translate_type = 'article';
            $translate_fields = array('name','keywords','description','description_long','additionaltext');
            $translate_key = $article_data['articleID'];
        } else {
            $translate_type = 'variant';
            $translate_fields = array('additionaltext');
            $translate_key = $article_data['articledetailsID'];
        }

        foreach ($languages as $languageArray) {
            $language = $languageArray['isocode'];
            $localeId= $languageArray['id'];

            $translate = array();
            foreach ($translate_fields as $field) {
                if (!empty($article[$field.'_'.$language])) {
                    $translate[$field] = $article[$field.'_'.$language];
                }
            }
            for ($i=1;$i<=20;$i++) {
                if (isset($article['attr'.$i.'_'.$language])) {
                    $translate['attr'.$i] = $article['attr'.$i.'_'.$language];
                } elseif (isset($article['attr_'.$i.'_'.$language])) {
                    $translate['attr'.$i] = $article['attr_'.$i.'_'.$language];
                }
            }

            $this->sTranslation($translate_type, $translate_key, $localeId, $translate);
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
    public function sGetArticleByAttribute($attr)
    {
        $tmp = array();
        if(!empty($attr)&&is_array($attr))
        foreach ($attr as $key=>$value) {
            if(is_numeric($key)&&$key>0)
                $key = "attr".$key;
            $value = $this->sDB->qstr((string) $value);
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
    public function sGetArticledetailsIDsByAttribute($attr)
    {
        //@@TODO: nach sGetArticledetailsIDs verschieben
        $tmp = array();
        if(!empty($attr)&&is_array($attr))
        foreach ($attr as $key=>$value) {
            if(is_numeric($key)&&$key>0)
                $key = "attr".$key;
            $value = $this->sDB->qstr((string) $value);
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
     * @param unknown_type $customer
     * @return unknown
     */
    public function sCustomers($customer)
    {
        return $this->sCustomer($customer);
    }

    /**
     * An deprecated method
     *
     * @param unknown_type $article
     * @return unknown
     */
    public function sDeleteArticleLink($article)
    {
        return $this->sDeleteArticleLinks($article);
    }

    /**
     * An deprecated method
     *
     * @param unknown_type $price
     * @return unknown
     */
    public function sRoundPrice($price)
    {
        return number_format($price,2,'.','');
    }

    /**
     * An deprecated method
     *
     * @param unknown_type $articles
     * @return unknown
     */
    public function sDeactiveOtherArticles($articles)
    {
        return false;
    }

    /**
     * An deprecated method
     *
     * @param unknown_type $dir
     * @return unknown
     */
    public function sReadDir($dir)
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
