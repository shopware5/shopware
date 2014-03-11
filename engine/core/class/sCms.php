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
}