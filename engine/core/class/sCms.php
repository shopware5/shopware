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
 * Deprecated Shopware Class that handle static shop pages and dynamic content
 */
class sCms
{
    /**
     * Shopware Core sSystem instance
     * @var sSystem
    */
    public $sSYSTEM;

    /**
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * Shopware configuration object which used for
     * each config access in this class.
     * Injected over the class constructor
     *
     * @var Shopware_Components_Config
     */
    private $config;

    public function __construct()
    {
        $this->db = Shopware()->Db();
        $this->config = Shopware()->Config();
    }

    /**
     * Read a specific, static page (E.g. terms and conditions, etc.)
     *
     * @param int $staticId The page id
     * @return array|false Page data, or false if none found by given id
     */
    public function sGetStaticPage($staticId = null)
    {
        if (empty($staticId) && !empty($this->sSYSTEM->_GET['sCustom'])) {
            $staticId = (int) $this->sSYSTEM->_GET['sCustom'];
        } else {
            $staticId = (int) $staticId;
        }
        if (empty($staticId)) {
            return false;
        }

        // Load static page data from database
        $sql = "SELECT * FROM s_cms_static WHERE id=?";
        $staticPage = $this->db->fetchRow(
            $sql, array($staticId)
        );
        if (empty($staticPage)) {
            return false;
        }

        /**
         * Add support for sub pages
         */
        if (!empty($staticPage['parentID'])) {
            $sql = '
                SELECT p.id, p.description, p.link, p.target, IF(p.id=?, 1, 0) as active, p.page_title
                FROM s_cms_static p
                WHERE p.parentID = ?
                ORDER BY p.position
            ';
            $staticPage['siblingPages'] = $this->db->fetchAll(
                $sql, array($staticId, $staticPage['parentID'])
            );
            $sql = '
                SELECT p.id, p.description, p.link, p.target, p.page_title
                FROM s_cms_static p
                WHERE p.id = ?
            ';
            $staticPage['parent'] = $this->db->fetchRow(
                $sql, array($staticPage['parentID'])
            );
        } else {
            $sql = '
                SELECT p.id, p.description, p.link, p.target, p.page_title
                FROM s_cms_static p
                WHERE p.parentID = ?
                ORDER BY p.position
            ';
            $staticPage['subPages'] = $this->db->fetchAll(
                $sql, array($staticId)
            );
        }
        return $staticPage;
    }

     /**
      * @deprecated This code seems to be legacy, dead code. See ticket SW-8142
      *
      * Dynamische Inhalte einer Gruppe auslesen
      *
      * @param int $group Group id
      * @param int $sPage Current page
      * @return array
     */
    public function sGetDynamicContentByGroup($group,$sPage=1)
    {
        // Get count of topics
        $sql = "
        SELECT COUNT(id) as countTopics FROM s_cms_content WHERE groupID=? GROUP BY groupID
        ";

        $getCountTopics = $this->db->fetchRow($sql, array($group));

        if ($sPage > $getCountTopics["countTopics"] || $sPage <= 0 ) $sPage = 1;

        $limitStart = $sPage * $this->config->get('sCONTENTPERPAGE') - $this->config->get('sCONTENTPERPAGE');
        $limitEnd = intval($this->config->get('sCONTENTPERPAGE'));

        // Calculate number of pages
        $numberPages = intval($getCountTopics["countTopics"] / $this->config->get('sCONTENTPERPAGE')) != $getCountTopics["countTopics"] / $this->config->get('sCONTENTPERPAGE') ? intval($getCountTopics["countTopics"] / $this->config->get('sCONTENTPERPAGE'))+1 : intval($getCountTopics["countTopics"] / $this->config->get('sCONTENTPERPAGE'));

        // Make Array with page-structure to render in template
        $pages = array();

        for ($i=1;$i<=$numberPages;$i++) {
            if ($i==$sPage) {
                $pages["numbers"][$i]["markup"] = true;
            } else {
                $pages["numbers"][$i]["markup"] = false;
            }
            $pages["numbers"][$i]["value"] = $i;
            $pages["numbers"][$i]["link"] = $this->config->get('sBASEFILE').$this->sSYSTEM->sBuildLink(array("sPage"=>$i),false);
        }


        // Query - Topic
        $sql = "
            SELECT id, description,text,img,link,attachment, datum as `date`, DATE_FORMAT(datum,'%d.%m.%Y') AS datumFormated
            FROM s_cms_content WHERE groupID=?
            ORDER BY datum DESC
        ";
        $sql = $this->db->limit($sql, $limitEnd, $limitStart);

        $queryDynamic = $this->db->fetchAll($sql, array($group));

        foreach ($queryDynamic as $dynamicKey => $dynamicValue) {
            $tempDatum = explode(".",$queryDynamic[$dynamicKey]["datum"]);

            // Building Link for more information page (optional)
            $queryDynamic[$dynamicKey]["linkDetails"] = $this->config->get('sBASEFILE').$this->sSYSTEM->sBuildLink(array("sCid"=>$dynamicValue["id"]),false);

            // Get Image
            if ($queryDynamic[$dynamicKey]["img"]) {
                $queryDynamic[$dynamicKey]["imgBig"] = $this->sSYSTEM->sPathCmsImg.$queryDynamic[$dynamicKey]["img"].".jpg";
                $queryDynamic[$dynamicKey]["img"] = $this->sSYSTEM->sPathCmsImg.$queryDynamic[$dynamicKey]["img"]."Thumb.jpg";
            }
            // Get attachment
            if ($queryDynamic[$dynamicKey]["attachment"]) {
                $queryDynamic[$dynamicKey]["attachment"] =  "http://".$this->config->get('sBASEPATH').$this->config->get('sCMSFILES')."/".$queryDynamic[$dynamicKey]["attachment"];
            }

            $queryDynamic[$dynamicKey]["dateExploded"] = $tempDatum;
        }
        return array("sContent"=>$queryDynamic,"sPages"=>$pages);
    }

     /**
      * @deprecated This code seems to be legacy, dead code. See ticket SW-8142
      *
      * Detailinformationen eines Gruppen-Eintrags
      *
      * @param int $group Gruppen-ID
      * @param int $id ID des Eintrags
      * @access public
      * @return array
      */
    public function sGetDynamicContentById($group,$id)
    {
        // Query - Topic
        $sql = "
        SELECT id, description,text,img,link,attachment,DATE_FORMAT(datum,'%d.%m.%Y') AS datum FROM s_cms_content WHERE groupID=?
        AND id=?
        ";

        $queryDynamic = $this->db->fetchRow($sql, array($group,$id));

        if ($queryDynamic["id"]) {
            $tempDatum = explode(".",$queryDynamic["datum"]);

            // Building Link for more information page (optional)
            $queryDynamic["linkDetails"] = $this->config->get('sBASEFILE').$this->sSYSTEM->sBuildLink(array("sCid"=>$queryDynamic["id"]),false);

            // Get Image
            if ($queryDynamic["img"]) {
                $queryDynamic["imgBig"] = $this->sSYSTEM->sPathCmsImg.$queryDynamic["img"].".jpg";
                $queryDynamic["img"] = $this->sSYSTEM->sPathCmsImg.$queryDynamic["img"]."Thumb.jpg";
            }
            // Get attachment
            if ($queryDynamic["attachment"]) {
                $queryDynamic["attachment"] =  "http://".$this->config->get("sBASEPATH").$this->config->get("sCMSFILES")."/".$queryDynamic["attachment"];
            }

            $queryDynamic["dateExploded"] = $tempDatum;
        } else {
            // Error-Handler
            $this->sSYSTEM->E_CORE_WARNING ("sCMS##sGetContentById","Content with id $id not found");
            return false;
        }

        return array("sContent"=>$queryDynamic);
    }

     /**
      * @deprecated This code seems to be legacy, dead code. See ticket SW-8142
      *
      * Name einer Gruppe anhand der ID
      * @param int $group Gruppen-ID
      * @access public
      * @return string Name
      */
    public function sGetDynamicGroupName($group)
    {
        $sql = "
        SELECT description FROM s_cms_groups WHERE id=?
        ";

        $queryDynamic = $this->db->fetchRow($sql, array($group));

        return $queryDynamic["description"];
    }
}
?>
