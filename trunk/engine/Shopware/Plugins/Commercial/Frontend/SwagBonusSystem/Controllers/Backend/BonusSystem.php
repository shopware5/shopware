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
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

class Shopware_Controllers_Backend_BonusSystem extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Doing global licence check for this controller
     * @return void
     */
    public function init()
    {
        parent::init();

        $licenceCheck = Shopware()->Plugins()->Frontend()->SwagBonusSystem()->checkLicense(false);
        $this->View()->licenceCheck = $licenceCheck;
    }

    /**
     * This function loads the bonus system settings
     * @return void
     */
    public function getSettingsAction()
    {
        $shopID = $this->Request()->getParam('shopID', 1);

        $sql = "SELECT * FROM s_core_plugins_bonus_settings WHERE shopID = ?";
        $data = Shopware()->Db()->fetchRow($sql, array($shopID));

        if (empty($data)) {
            $data = array(
                "id" => $shopID,
                "shopID" => $shopID,
                "bonus_maintenance_mode" => '1',
                "bonus_articles_active" => '1',
                "bonus_voucher_active" => '1',
                "bonus_point_conversion_factor" => '10',
                "bonus_voucher_conversion_factor" => '10',
                "bonus_voucher_limitation_type" => 'fix',
                "bonus_voucher_limitation_value" => '1',
                "bonus_point_unlock_type" => 'paid',
                "bonus_point_unlock_day" => '14',
                "bonus_listing_text" => '',
                "bonus_listing_banner" => '',
                "display_banner" => '1',
                "display_accordion" => '1',
                "display_article_slider" => '1'
            );
        }

        $this->View()->assign(array(
            'success' => true,
            'data'    => $data
        ));
    }

    /**
     * This function save the edited bonus system settings.
     * @return void
     */
    public function saveSettingsAction()
    {
        $data   = $this->Request()->getParams();
        $shopID = $this->Request()->getParam('shopID');

        //only execute sql if data and shopID given
        if (empty($data) || empty($shopID)) {
            $this->View()->assign(array(
                'success' => false,
            ));
        }

        //validate input fields
        if ($data["bonus_voucher_conversion_factor"] == null || empty($data["bonus_voucher_conversion_factor"])) {
            $data["bonus_voucher_conversion_factor"] = 0;
        }

        if ($data["bonus_point_conversion_factor"] == null || empty($data["bonus_point_conversion_factor"])) {
            $data["bonus_point_conversion_factor"] = 0;
        }

        if ($data["bonus_point_unlock_day"] == null || empty($data["bonus_point_unlock_day"])) {
            $data["bonus_point_unlock_day"] = 0;
        }

        if ($data["bonus_voucher_limitation_value"] == null || empty($data["bonus_voucher_limitation_value"])) {
            $data["bonus_voucher_limitation_value"] = '0,00';
        }

        //if limitation value not set, set default
        if (empty($data["bonus_voucher_limitation_value"])) {
            $data["bonus_voucher_limitation_value"] = '0,00';
        }

        $sql = "SELECT * FROM s_core_plugins_bonus_settings WHERE shopID = ?";
        $exist = Shopware()->Db()->fetchRow($sql, array($shopID));

        if (empty($exist)) {
            $sql = "INSERT INTO s_core_plugins_bonus_settings (shopID,bonus_maintenance_mode,bonus_articles_active,bonus_voucher_active,bonus_point_conversion_factor,bonus_voucher_conversion_factor,bonus_voucher_limitation_type,bonus_voucher_limitation_value,bonus_point_unlock_type,bonus_point_unlock_day,bonus_listing_text,bonus_listing_banner,display_banner,display_accordion,display_article_slider)
                      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            //insert new settings
            Shopware()->Db()->query($sql, array($shopID, $data["bonus_maintenance_mode"], $data["bonus_articles_active"], $data["bonus_voucher_active"], $data["bonus_point_conversion_factor"], $data["bonus_voucher_conversion_factor"], $data["bonus_voucher_limitation_type"], $data["bonus_voucher_limitation_value"], $data["bonus_point_unlock_type"], $data["bonus_point_unlock_day"], $data["bonus_listing_text"], $data["bonus_listing_banner"], $data["display_banner"], $data["display_accordion"], $data["display_article_slider"]));
        } else {
            $sql = "UPDATE s_core_plugins_bonus_settings
                    SET bonus_maintenance_mode = ?,
                        bonus_articles_active = ?,
                        bonus_voucher_active = ?,
                        bonus_point_conversion_factor = ?,
                        bonus_voucher_conversion_factor = ?,
                        bonus_voucher_limitation_type = ?,
                        bonus_voucher_limitation_value = ?,
                        bonus_point_unlock_type = ?,
                        bonus_point_unlock_day = ?,
                        bonus_listing_text = ?,
                        bonus_listing_banner = ?,
                        display_banner = ?,
                        display_accordion = ?,
                        display_article_slider = ?
                    WHERE shopID = ?
                    ";

            //insert new settings
            Shopware()->Db()->query($sql, array($data["bonus_maintenance_mode"], $data["bonus_articles_active"], $data["bonus_voucher_active"], $data["bonus_point_conversion_factor"], $data["bonus_voucher_conversion_factor"], $data["bonus_voucher_limitation_type"], $data["bonus_voucher_limitation_value"], $data["bonus_point_unlock_type"], $data["bonus_point_unlock_day"], $data["bonus_listing_text"], $data["bonus_listing_banner"], $data["display_banner"], $data["display_accordion"], $data["display_article_slider"], $shopID));
        }

        $sql = "SELECT * FROM s_core_plugins_bonus_settings WHERE shopID = ?";
        $data = Shopware()->Db()->fetchRow($sql, array($shopID));

        $this->View()->assign(array(
            'data'    => $data,
            'success' => true,
        ));
    }

    /**
     * This function loads all defined bonus articles into the grid.
     * @return void
     */
    public function getBonusArticlesAction()
    {
        $shopID = $this->Request()->getParam('shopID', 1);

        $sql = "SELECT * FROM s_core_plugins_bonus_articles WHERE shopID = ? ORDER BY position";

        $data = Shopware()->Db()->fetchAll($sql, array($shopID));

        $this->View()->assign(array(
            'success' => true,
            'data'    => $data,
            'total'   => count($data),
        ));
    }

    /**
     * Save the new bonus article into the database
     * @return void
     */
    public function insertBonusArticleAction()
    {
        $shopID = $this->Request()->getParam('shopID');
        $articles = $this->Request()->getPost();

        if (empty($articles)) {
            $this->View()->assign(array(
                'success' => false,
                'data'    => $articles,
            ));
        }

        if (is_array($articles[0])) {
            foreach ($articles as &$article) {
                $article = $this->insertBonusArticle($article, $shopID);
            }
        } else {
            $articles = $this->insertBonusArticle($articles, $shopID);
        }

        $this->View()->assign(array(
            'success' => true,
            'data'    => $articles,
        ));
    }

    /**
     * Internal function to insert the given data into the bonus system tables
     * @param $data
     * @param $shopID
     * @return array
     */
    private function insertBonusArticle($data, $shopID)
    {
        $sql = "INSERT INTO s_core_plugins_bonus_articles (shopID, articleID, articleName, ordernumber, required_points, `position`)
                        VALUES (?,?,?,?,?,?)
                ";
        Shopware()->Db()->query($sql, array($shopID, $data["articleID"], $data["articleName"], $data["ordernumber"], $data["required_points"], $data["position"]));
        $data["id"] = Shopware()->Db()->lastInsertId();

        return $data;
    }

    /**
     * Save the edited bonus article into the database.
     * @return void
     */
    public function updateBonusArticleAction()
    {
        $articles = $this->Request()->getPost();

        if (empty($articles)) {
            $this->View()->assign(array(
                'success' => false,
                'data'    => $articles,
            ));
        }


        $this->updateBonusArticle($articles);

        $this->View()->assign(array(
            'success' => true,
            'data'    => $articles,
        ));
    }

    /**
     * Internal function to update the given data in the database tables of the bonus system
     * @param $data
     * @return void
     */
    private function updateBonusArticle($data)
    {
        $sql = "UPDATE s_core_plugins_bonus_articles
                    SET articleID = ?,
                        articleName = ?,
                        ordernumber = ?,
                        required_points = ?,
                        position = ?
                    WHERE id = ?
            ";

        Shopware()->Db()->query($sql, array($data["articleID"], $data["articleName"], $data["ordernumber"], $data["required_points"], $data["position"], $data["id"]));
    }

    /**
     * This function delete the selected bonus article of the bonus article grid.
     * @return void
     */
    public function deleteBonusArticleAction()
    {
        $details = $this->Request()->getParam('details', array(array('id' => $this->Request()->getParam('id'))));
        try {
            foreach ($details as $detail) {
                if (empty($detail['id'])) {
                    continue;
                }

                $sql = "DELETE FROM s_core_plugins_bonus_articles WHERE id = ?";
                Shopware()->Db()->query($sql, array($detail["id"]));
            }

            $this->View()->assign(array(
                'success' => true
            ));

        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * This function returns all users and their point score
     * @return void
     */
    public function getUsersAction()
    {
        $params = $this->Request()->getParams();
        $filterParams = $this->Request()->getParam('filter');

        $filters = array();
        foreach ($filterParams as $singleFilter) {
            $filters[$singleFilter['property']] = $singleFilter['value'];
        }

        if (isset($filters['filter'])) {
            $params["filter"] = $filters['filter'];
        }

        //sql header with the selected columns
        $sqlHeader = "SELECT
                    s_user.id as userID,
                    s_user_billingaddress.customernumber,
                    CONCAT(s_user_billingaddress.firstname, ' ', s_user_billingaddress.lastname) as name,
                    s_user.email,
                    CONCAT(s_user_billingaddress.zipcode, ' ', s_user_billingaddress.city) as address,
                    IFNULL(plugin.points,0) as points ";

        //sql from with the joined tables
        $sqlFrom = " FROM `s_user`
                        JOIN s_user_billingaddress
                         ON (s_user_billingaddress.userID = s_user.id)
                        LEFT JOIN s_core_plugins_bonus_user_points plugin
                         ON (s_user.id = plugin.userID) ";

        //sql limit
        $sqlLimit = " LIMIT " . $params["start"] . "," . $params["limit"];
        $sqlWhere = "";

        if (!empty($params)) {
            //add shop id parameter
            $concat = " WHERE";
            if (!empty($params["shopID"])) {
                $sqlWhere = " WHERE subshopID = " . $params["shopID"];
                $concat = " AND";
            }

            //add filter paramater
            if (!empty($params["filter"])) {
                $filter = $params["filter"];
                $sqlWhere .= $concat . " (
                        s_user_billingaddress.customernumber LIKE '%$filter%'
                    OR 	s_user_billingaddress.firstname LIKE '%$filter%'
                    OR 	s_user_billingaddress.lastname LIKE '%$filter%'
                    OR 	s_user_billingaddress.city LIKE '%$filter%'
                )";
            }
        }
        //concat the two sqls
        $dataSql = $sqlHeader . $sqlFrom . $sqlWhere . $sqlLimit;
        $countSql = "SELECT COUNT(*) " . $sqlFrom . $sqlWhere;

        $users = Shopware()->Db()->fetchAll($dataSql);
        $count = Shopware()->Db()->fetchOne($countSql);

        $this->View()->assign(array(
            'success' => true,
            'data'    => $users,
            'total'   => $count,
        ));
    }

    /**
     * This function saves the edited users point scores
     * @return void
     */
    public function saveUsersAction()
    {
        $params = $this->Request()->getPost();

        $this->addPointsToUserAccount($params["userID"], $params["points"]);

        $this->View()->assign(array(
            'success' => true,
            'data'    => $params,
        ));
    }

    /**
     * This function loads all orders with bonus points into the grid.
     * @return void
     */
    public function getBonusPointOrdersAction()
    {
        $params = $this->Request()->getParams();
        $filterParams = $this->Request()->getParam('filter');

        $filters = array();
        foreach ($filterParams as $singleFilter) {
            $filters[$singleFilter['property']] = $singleFilter['value'];
        }

        if (isset($filters['filter'])) {
            $params["filter"] = $filters['filter'];
        }

        //sql header with the selected columns
        $header = " SELECT
                    s_user.id as userID,
                    s_core_plugins_bonus_order.id,
                    s_core_plugins_bonus_order.orderID,
                    s_order.ordernumber,
                    s_order.currencyFactor,
                    s_user_billingaddress.customernumber,
                    CONCAT(s_user_billingaddress.firstname, ' ', s_user_billingaddress.lastname) as user,
                    s_user.email,
                    CONCAT(s_user_billingaddress.zipcode, ' ', s_user_billingaddress.city) as address,
                    CONCAT((CASE WHEN s_order.net = 1 THEN s_order.invoice_amount_net ELSE s_order.invoice_amount END), ' ', s_order.currency) as amount,
                    s_order.ordertime,
                    s_core_plugins_bonus_order.approval,
                    s_core_plugins_bonus_order.points,
                    s_core_plugins_bonus_settings.bonus_point_conversion_factor";

        //sql join, with the joined tables
        $join = " FROM s_order
                 JOIN s_core_plugins_bonus_order
                  ON (s_order.id = s_core_plugins_bonus_order.orderID)
                 JOIN s_user_billingaddress
                  ON (s_user_billingaddress.userID = s_order.userID)
                 JOIN s_user
                  ON (s_order.userID = s_user.id)
                 LEFT JOIN s_core_plugins_bonus_settings
                  ON (s_core_plugins_bonus_settings.shopID = s_order.subshopID)";

        $where = "";

        if (!empty($params)) {
            //add shop id parameter to the sql statement
            $concat = " WHERE";
            if (!empty($params["shopID"])) {
                $where .= " WHERE s_order.subshopID = " . $params["shopID"];
                $concat = " AND";
            }

            //add filter parameter to the sql statement
            if (!empty($params["filter"])) {
                $filter = $params["filter"];
                $where .= $concat . " (
                        s_user_billingaddress.customernumber LIKE '%$filter%'
                    OR 	s_user_billingaddress.firstname LIKE '%$filter%'
                    OR 	s_user_billingaddress.lastname LIKE '%$filter%'
                    OR 	s_user_billingaddress.city LIKE '%$filter%'
                )";
            }
        }

        //concat the two sql statements
        $limit = " LIMIT " . $params["start"] . "," . $params["limit"];

        $countSQL = "SELECT COUNT(*) " . $join . $where;

        $dataSQL = $header . $join . $where . $limit;

        $count = Shopware()->Db()->fetchOne($countSQL);
        $data = Shopware()->Db()->fetchAll($dataSQL);

        $this->View()->assign(array(
            'success' => true,
            'data'    => $data,
            'total'   => $count,
        ));
    }

    /**
     * This function unlock the bonus points of the selected order. The points of order will added to the user account.
     *
     * @return void
     */
    public function saveBonusPointOrdersAction()
    {
        $details = $this->Request()->getParam('details', array(array('id' => $this->Request()->getParam('id'))));

        foreach ($details as $item) {
            $sql = "SELECT * FROM s_core_plugins_bonus_order WHERE id = ?";
            $data = Shopware()->Db()->fetchRow($sql, array($item['id']));

            if ($data['approval'] == 1) {
                continue;
            }

            //set approval flag in the bonus system table
            $sql = "UPDATE s_core_plugins_bonus_order SET approval = 1 WHERE id = ?";

            Shopware()->Db()->query($sql, array($item['id']));

            //check if the user already exist in s_core_plugins_bonus_user_points
            $sql = "SELECT * FROM s_core_plugins_bonus_user_points WHERE userID = ?";
            $existingPoints = Shopware()->Db()->fetchRow($sql, array($data["userID"]));

            $points = intval($existingPoints["points"]) + $data["points"];

            //unlock the bonus points of the given order
            $this->addPointsToUserAccount($data["userID"], $points);
        }

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Internal helper function to add the given points to the given user account
     * @param $userID
     * @param $points
     * @return void
     */
    private function addPointsToUserAccount($userID, $points)
    {
        //check if the user already exist in s_core_plugins_bonus_user_points
        $sql = "SELECT * FROM s_core_plugins_bonus_user_points WHERE userID = ?";
        $exist = Shopware()->Db()->fetchAll($sql, array($userID));

        //if the user don't exist in s_core_plugins_bonus_user_points insert a new row otherwise update the row
        if (empty($exist)) {
            $sql = "INSERT INTO s_core_plugins_bonus_user_points (points, userID) VALUES (?,?)";
        } else {
            $sql = "UPDATE s_core_plugins_bonus_user_points SET points = ? WHERE userID = ?";
        }
        Shopware()->Db()->query($sql, array($points, $userID));
    }
}
