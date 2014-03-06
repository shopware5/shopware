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
 * Deprecated Shopware class that handle static shop pages and dynamic content
 * Used to handle pages such as "Help", etc
 *
 * Used by Frontend_Custom and Frontend_Content controllers
 */
class sCms
{
    /**
     * The use of this variable in this class is now limited to calling sBuildLink, which is actually part of sCore.
     *
     * Shopware sore sSystem instance
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

    /**
     * The Front controller object
     *
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * Shopware Core core module
     *
     * @var sCore
     */
    private $coreModule;

    public function __construct($db = null, $config = null, $front = null, $coreModule = null)
    {
        $this->db = $db ? : Shopware()->Db();
        $this->config = $config ? : Shopware()->Config();
        $this->front = $front ? : Shopware()->Front();
        $this->coreModule = $coreModule ? : Shopware()->Modules()->Core();
    }

    /**
     * Read a specific, static page (E.g. terms and conditions, etc.)
     *
     * @param int $staticId The page id
     * @return array|false Page data, or false if none found by given id
     */
    public function sGetStaticPage($staticId = null)
    {
        if (empty($staticId)) {
            $staticId = (int) $this->front->Request()->getQuery('sCustom', $staticId);
        }
        if (empty($staticId)) {
            return false;
        }

        // Load static page data from database
        $staticPage = $this->db->fetchRow(
            "SELECT * FROM s_cms_static WHERE id = ?",
            array($staticId)
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
      * Get dynamic content of a group
      *
      * @param int $group Group id
      * @param int $sPage Current page
      * @return array
     */
    public function sGetDynamicContentByGroup($group, $sPage = 1)
    {
        // Get count of topics
        $sql = "
        SELECT COUNT(id) as countTopics FROM s_cms_content WHERE groupID = ? GROUP BY groupID
        ";

        $getCountTopics = $this->db->fetchOne($sql, array($group));

        if ($sPage > $getCountTopics || $sPage <= 0 ) {
            $sPage = 1;
        }

        $limitStart = ($sPage - 1) * $this->config->get('sCONTENTPERPAGE');
        $limitEnd = intval($this->config->get('sCONTENTPERPAGE'));

        // Calculate number of pages
        $numberPages = ceil($getCountTopics / $this->config->get('sCONTENTPERPAGE'));

        // Make Array with page-structure to render in template
        $pages = array();

        for ($i = 1; $i <= $numberPages; $i++) {
            $pages["numbers"][$i]["markup"] = ($i == $sPage);
            $pages["numbers"][$i]["value"] = $i;
            $pages["numbers"][$i]["link"] = $this->config->get('sBASEFILE') . $this->coreModule->sBuildLink(array("sPage"=>$i));
        }

        // Query - Topic
        $sql = "
            SELECT id, description, text, img, link, attachment,
              datum as `date`, DATE_FORMAT(datum,'%d.%m.%Y') AS datumFormated
            FROM s_cms_content WHERE groupID=?
            ORDER BY datum DESC
        ";
        $sql = $this->db->limit($sql, $limitEnd, $limitStart);

        $queryDynamic = $this->db->fetchAll($sql, array($group));

        foreach ($queryDynamic as &$dynamicValue) {
            $tempDate = explode(".", $dynamicValue["datum"]);

            // Building Link for more information page (optional)
            $dynamicValue["linkDetails"] = $this->config->get('sBASEFILE') . $this->coreModule->sBuildLink(array("sCid" => $dynamicValue["id"]));

            // Get Image
            if ($dynamicValue["img"]) {
                $dynamicValue["imgBig"] = $this->sSYSTEM->sPathCmsImg . $dynamicValue["img"] . ".jpg";
                $dynamicValue["img"] = $this->sSYSTEM->sPathCmsImg . $dynamicValue["img"] . "Thumb.jpg";
            }
            // Get attachment
            if ($dynamicValue["attachment"]) {
                $dynamicValue["attachment"] = "http://" . $this->config->get('sBASEPATH') . $this->config->get('sCMSFILES') . "/" . $dynamicValue["attachment"];
            }

            $dynamicValue["dateExploded"] = $tempDate;
        }
        return array("sContent" => $queryDynamic, "sPages" => $pages);
    }

     /**
      * @deprecated This code seems to be legacy, dead code. See ticket SW-8142
      *
      * Details of a group entry
      *
      * @param int $group Group ID
      * @param int $id Entry ID
      * @return array
      * @throws Enlight_Exception If provided arguments don't match any content
      */
    public function sGetDynamicContentById($group, $id)
    {
        // Query - Topic
        $sql = "
            SELECT id, description, text, img, link, attachment, DATE_FORMAT(datum,'%d.%m.%Y') AS datum
            FROM s_cms_content WHERE groupID = ?
            AND id = ?
        ";

        $queryDynamic = $this->db->fetchRow($sql, array($group, $id));

        if ($queryDynamic["id"]) {
            $tempDate = explode(".", $queryDynamic["datum"]);

            // Building Link for more information page (optional)
            $queryDynamic["linkDetails"] = $this->config->get('sBASEFILE') . $this->coreModule->sBuildLink(array("sCid"=>$queryDynamic["id"]));

            // Get Image
            if ($queryDynamic["img"]) {
                $queryDynamic["imgBig"] = $this->sSYSTEM->sPathCmsImg . $queryDynamic["img"] . ".jpg";
                $queryDynamic["img"] = $this->sSYSTEM->sPathCmsImg . $queryDynamic["img"] . "Thumb.jpg";
            }
            // Get attachment
            if ($queryDynamic["attachment"]) {
                $queryDynamic["attachment"] =  "http://" . $this->config->get("sBASEPATH") . $this->config->get("sCMSFILES") . "/".$queryDynamic["attachment"];
            }

            $queryDynamic["dateExploded"] = $tempDate;
        } else {
            // No content found, throw an Exception
            throw new Enlight_Exception("sCMS##sGetContentById: Content with id '$id' not found");
        }

        return array("sContent" => $queryDynamic);
    }

     /**
      * @deprecated This code seems to be legacy, dead code. See ticket SW-8142
      *
      * Gets the name of a group
      * @param int $group Group id
      * @return string Name of the group
      */
    public function sGetDynamicGroupName($group)
    {
        $sql = "
          SELECT description FROM s_cms_groups WHERE id = ?
        ";

        return $this->db->fetchOne($sql, array($group));
    }
}
?>
