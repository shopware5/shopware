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

class BonusSystemDataModel
{
    /**
     * Array of all types of points: earning, spending and user points
     * @var array
     */
    public $points;

    /**
     * Internal basket array with the content and amount
     * @var array
     */
    protected $basket;

    /**
     * Settings and articles of the bonus system
     * @var array
     */
    protected $data;

    /**
     * Array of the bonus system settings
     * @var array
     */
    protected $settings;

    /**
     * Array of all defined bonus articles
     * @var array
     */
    protected $articles;

    /**
     * class constructor to get access to the config and set soap settings
     * @param  array $basket
     * @return \BonusSystemDataModel
     */
    public function __construct($basket = null)
    {
        $this->basket = $basket;
    }

    /**
     * Loads all data of the bonus system
     * @return array
     */
    public function getBonusSystemData()
    {
        //init
        $sBonusSystem = array();

        $articles = $this->getBonusArticles();
        $settings = $this->getBonusSystemSettings();

        $this->points = $this->getPoints();

        //get all types of points
        $sBonusSystem["points"] = $this->points;

        //get specify settings for the bonus voucher
        $voucherSettings = $this->getVoucherSettings();

        //merge arrays into one
        $settings = array_merge($settings, $voucherSettings);
        $sBonusSystem["settings"] = $settings;
        $sBonusSystem["articles"] = $articles;

        $topFive = array_chunk($articles, 5);
        $sBonusSystem["accordion"] = $topFive[0];

        return $sBonusSystem;
    }

    /**
     * Helper function to get the required points of an article
     * @param $articleID
     * @return int
     */
    public function getRequiredPointsOfArticle($articleID)
    {
        $sql = "SELECT required_points FROM s_core_plugins_bonus_articles WHERE shopID = ? AND articleID = ?";

        $points = Shopware()->Db()->fetchOne($sql, array(Shopware()->Shop()->getId(), $articleID));

        return $points;
    }

    /**
     * This function loads the basic settings of the bonus system.
     * For the voucher settings call the function "getVoucherSettings"
     *
     * @return array
     */
    public function getBonusSystemSettings()
    {
        if (null === $this->settings) {
            //select the basic settings
            $sql = "SELECT * FROM s_core_plugins_bonus_settings WHERE shopID = ?";
            $settings = Shopware()->Db()->fetchRow($sql, array(Shopware()->Shop()->getId()));

            if (!$settings) {
                $this->settings = array(
                    'bonus_system_active' => false
                );

                return $this->settings;
            }

            //convert the data row
            $settings["bonus_system_active"] = ($settings["bonus_maintenance_mode"] == 0);
            $settings["displaySlider"] = false;

            $currencyFactor = Shopware()->Shop()->getCurrency()->getFactor();

            $settings["bonus_point_conversion_factor"]   = $settings["bonus_point_conversion_factor"] * $currencyFactor;
            $settings["bonus_voucher_limitation_value"]  = $settings["bonus_voucher_limitation_value"] * $currencyFactor;
            $settings["bonus_voucher_conversion_factor"] = $settings["bonus_voucher_conversion_factor"] * $currencyFactor;

            $this->settings = $settings;
        }

        return $this->settings;
    }

    /**
     * This function loads all point types.
     *  - Points of the user
     *  - The spent points for the basket
     *  - The earning points for the basket amount
     *
     * @return array
     */
    public function getPoints()
    {
        //init
        $points = array(
            'user'      => 0,
            'earning'   => 0,
            'remaining' => 0,
            'spending'  => 0
        );

        $basket = $this->getBasket();

        if (!empty($basket)) {
            $settings = $this->getBonusSystemSettings();
            //has user bought a normal article?
            if ($basket["Amount"] > 0) {
                $factor = $settings["bonus_point_conversion_factor"];
                $amount = str_replace(",", ".", $basket["Amount"]);

                $earning = $amount / $factor;
                if ($earning >= 1) {
                    $points["earning"] = round($earning);
                } else {
                    $points["earning"] = 0;
                }
            }

            foreach ($basket["content"] as $item) {
                if ($item["isBonusArticle"] || $item["isBonusVoucher"]) {

                    $points["spending"] += $item["required_points"];
                }
            }
        }

        if (Zend_Session::sessionExists()) {
            $userID = Shopware()->Session()->sUserId;

            //if user logged in, load the user points, otherwise return null
            if ($userID != 0) {
                //get user points
                $points["user"] = Shopware()->Db()->fetchOne("SELECT points FROM `s_core_plugins_bonus_user_points` WHERE `userID` = ?;", array($userID));
                if (empty($points["user"])) {
                    $points["user"] = 0;
                }
            }
        }

        //calculated the remaining points
        if ($points["user"] > 0) {
            $points["remaining"] = $points["user"] - $points["spending"];
        }

        return $points;
    }

    /**
     * Returns the content and amount of the current basket
     *
     * @return array
     */
    public function getBasket()
    {
        if (null === $this->basket) {
            if (!Zend_Session::sessionExists()) {
                return array();
            }

            $basket = array();
            $sql = "SELECT basket.*, attribute.swag_bonus
                    FROM s_order_basket basket
                    LEFT JOIN s_order_basket_attributes attribute
                        ON attribute.basketID = basket.id
                    WHERE sessionID = ?";
            $basket["content"] = Shopware()->Db()->fetchAll($sql, array(Shopware()->SessionID()));

            $amount = Shopware()->Modules()->sBasket()->sGetAmount();
            $basket["Amount"] = $amount["totalAmount"];
            $basket["content"] = $this->setBasketContentFlags($basket["content"]);

            $this->basket = $basket;
        }

        return $this->basket;
    }

    /**
     * @param array $basket
     * @return \BonusSystemDataModel
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;

        return $this;
    }


    /**
     * This function control the basket spending points with the user point score.
     * If the user has not enough points for the basket this function will remove the bonus articles until the user score reach
     *
     * @return array
     */
    public function controlBasket()
    {
        $points   = $this->points;
        $removed  = array();
        $user     = $points["user"];
        $spending = $points["spending"];

        //check spending points and user points
        if ($user < $spending) {
            while ($user < $spending) {
                if ($spending <= 0) {
                    return $removed;
                }

                // make sure basket is loaded
                $this->getBasket();

                //iterate the basket content and find the bonus items
                foreach ($this->basket["content"] as $i => $item) {

                    if ($item["isBonusVoucher"] || $item["isBonusArticle"]) {
                        $spending -= $item["required_points"];

                        if ($item["isBonusVoucher"]) {
                            $item["articleName"] = $item["ordernumber"];
                        }

                        $removed[] = $item;
                        unset($this->basket["content"][$i]);

                        // remove bonus item from database
                        $sql = "DELETE FROM s_order_basket WHERE id = ? and sessionID = ?";
                        Shopware()->Db()->query($sql, array($item["id"], Shopware()->SessionID()));
                        break;
                    }
                }
            }
        }

        return $removed;
    }

    /**
     * This function lazy loads all defined bonus articles
     *
     * @return array
     */
    public function getBonusArticles()
    {
        if (null === $this->articles) {
            //select the bonus articles
            $sql = "SELECT * FROM s_core_plugins_bonus_articles WHERE shopID = ? ORDER BY position";
            $bonusArticles = Shopware()->Db()->fetchAll($sql, array(Shopware()->Shop()->getId()));
            $articles = array();

            foreach ($bonusArticles as $bonusArticle) {
                //extend the bonus article data with the original aritcle data
                $article = Shopware()->Modules()->Articles()->sGetPromotionById("fix", 0, $bonusArticle['articleID']);
                $article["required_points"] = $bonusArticle["required_points"];
                $articles[] = $article;
            }

            $this->articles = $articles;
        }

        return $this->articles;
    }

    /**
     * Checks if the admin allows, to convert the bonus points back to euro in form of a voucher.
     * Additional the functions sets the maximum voucher value.
     * If the admin set a voucher limitation manuel the max voucher value will be calculated, otherwise
     * the max voucher value will set to the basket amount.
     *
     * @return array Settings for the bonus system voucher
     */
    public function getVoucherSettings()
    {
        $settings = $this->getBonusSystemSettings();

        $voucherSettings = array(
            'displaySlider'     => false,
            'sliderMaxInEuro'   => 0,
            'sliderMaxInPoints' => 0
        );

        $basket = $this->getBasket();

        //basket empty?
        if (empty($basket)) {
            return $voucherSettings;
        }

        //no settings loaded or bonus voucher deactivated?
        if (empty($settings) || $settings["bonus_voucher_active"] == 0) {
            return $voucherSettings;
        }

        //calculate the maximum voucher value by user points
        $maxUserConversion = $this->points["remaining"] / $settings["bonus_voucher_conversion_factor"];
        $maxUserConversion = floor($maxUserConversion);

        //If the user can afford a voucher
        if ($maxUserConversion <= 0) {
            return $voucherSettings;
        }

        //set flag to display voucher slider
        $voucherSettings["displaySlider"] = !$this->isVoucherInBasket();

        //check if the admin set a voucher limitation
        if ($settings["bonus_voucher_limitation_value"] <= 0) {
            //if not, set the basket amount as maximum
            $maxOrderConversion = $basket["Amount"];
        } else {
            //check the limitation type
            if ($settings["bonus_voucher_limitation_type"] == "relative") {
                //if relative the voucher maximum is the basket amount minus limitation value
                $maxOrderConversion = ($basket["Amount"] - $settings["bonus_voucher_limitation_value"]);
            } else {
                //if the limitation type is set to fix, take over the limitation value direct
                $maxOrderConversion = $settings["bonus_voucher_limitation_value"];
            }
        }

        if ($maxOrderConversion == null) {
            $maxOrderConversion = 0;
        }

        //if the basket amount is lower then the max order conversion, take the basket amount as max
        $maxOrderConversion = min((int) $basket["Amount"], (int) $maxOrderConversion);

        //select the minimum between the order and user maximum
        $voucherSettings["sliderMaxInEuro"] = floor(min((int) $maxUserConversion, (int) $maxOrderConversion));

        //calculate the points
        $voucherSettings["sliderMaxInPoints"] = $voucherSettings["sliderMaxInEuro"] * $settings["bonus_voucher_conversion_factor"];

        //It can only be calculated with whole points
        if (!is_integer($voucherSettings["sliderMaxInPoints"])) {
            $voucherSettings["sliderMaxInPoints"] = (int) $voucherSettings["sliderMaxInPoints"];
            $voucherSettings["sliderMaxInEuro"] = $voucherSettings["sliderMaxInPoints"] / $settings["bonus_voucher_conversion_factor"];
        }
        $voucherSettings["displaySlider"] = ($voucherSettings["displaySlider"] && $voucherSettings["sliderMaxInPoints"] > 0);

        return $voucherSettings;
    }

    /**
     * Internal helper function to add a bonus article into the basket
     *
     * @param string $orderNumber  number of the order
     * @param int     $quantity     quantity of the added article
     *
     * @return array
     */
    public function addBonusArticleToBasket($orderNumber, $quantity = 1)
    {
        //get session id
        $sessionID = Shopware()->SessionID();

        //get user data and set user id
        $userID = Shopware()->Session()->sUserId;
        if (!isset($userID)) {
            $userID = 0;
        }

        //get the article id by the article order number
        $articleID = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($orderNumber);

        $pointsPerUnit = $this->getRequiredPointsOfArticle($articleID);

        $article = Shopware()->Modules()->Articles()->sGetPromotionById("fix", 0, $articleID);

        $sql = "SELECT shippingfree FROM s_articles_details WHERE ordernumber LIKE ?";
        $article["shippingfree"] = Shopware()->Db()->fetchOne($sql, array($orderNumber));

        $article["required_points"] = $pointsPerUnit * $quantity;

        $insertTime = date("Y-m-d H:i:s");

        //insert bonus article into basket
        $sql = "INSERT INTO s_order_basket (sessionID,userID,articlename,articleID, ordernumber, shippingfree, quantity, price, netprice, datum, esdarticle, modus)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

        Shopware()->Db()->query($sql, array($sessionID, $userID, $article["articleName"], $articleID, $orderNumber, $article["shippingfree"], $quantity, 0, 0, $insertTime, $article["esd"], 0));

        $basketId = Shopware()->Db()->lastInsertId();

        $sql = "INSERT INTO s_order_basket_attributes (basketID, swag_bonus) VALUES (?, ?)";
        Shopware()->Db()->query($sql, array($basketId, 1));

        return $article;
    }

    /**
     * Helper function to unlock the earning points of an order
     *
     * @param  string $orderID id of the sOrder
     * @param  int    $points  points which will be added for the order
     * @param  int    $userID  id of the user
     *
     * @return void
     */
    public function unlockBonusPoints($orderID, $points, $userID)
    {
        //check if the user already exist in s_core_plugins_bonus_user_points
        $sql = "SELECT * FROM s_core_plugins_bonus_user_points WHERE userID = ?";
        $exist = Shopware()->Db()->fetchAll($sql, array($userID));

        //if the user don't exist in s_core_plugins_bonus_user_points insert a new row otherwise update the row
        if (empty($exist)) {
            $sql = "INSERT INTO s_core_plugins_bonus_user_points (points, userID) VALUES (?,?)";
        } else {
            $sql = "UPDATE s_core_plugins_bonus_user_points SET points = (points + ?) WHERE userID = ?";
        }

        Shopware()->Db()->query($sql, array($points, $userID));

        $sql = "UPDATE s_core_plugins_bonus_order, s_order
                    SET s_core_plugins_bonus_order.approval = 1
                WHERE s_core_plugins_bonus_order.orderID = s_order.id
                    AND s_order.id = ?";

        Shopware()->Db()->query($sql, array($orderID));
    }

    /**
     * This function updates the user point score and save the earning points for the given order
     *
     * @param string $orderNumber
     */
    public function finishOrder($orderNumber)
    {
        $settings = $this->getBonusSystemSettings();

        if ($settings["bonus_system_active"] == 0) {
            return;
        }

        //select whole order object
        $order = Shopware()->Db()->fetchRow("SELECT * FROM s_order WHERE ordernumber = ?", array($orderNumber));

        $this->basket = $this->convertOrderToBasket($order);

        $this->points = $this->getPoints();

        //remove the spending points from user score
        $sql = "UPDATE s_core_plugins_bonus_user_points
                SET points = ?
                WHERE userID = ?";
        Shopware()->Db()->query($sql, array($this->points["remaining"], $order["userID"]));

        if (!empty($order)) {
            //Get the factor of bonus points per unit
            $sBonusPointsPerUnit = $settings["bonus_point_conversion_factor"];
            $sBonusPointsPerUnit = str_replace(',', '.', $sBonusPointsPerUnit);
            $sBonusPointsPerUnit = floatval($sBonusPointsPerUnit);

            if (empty($sBonusPointsPerUnit)) {
                $sBonusPointsPerUnit = 1;
            }

            $value = $order["invoice_amount"] - $order["invoice_shipping"];
            $value = str_replace(',', '.', $value);

            //Calculate the points for this order
            $points = $value / $sBonusPointsPerUnit;

            $points = round($points);

            //save order with earning bonus points
            $sql = "INSERT INTO s_core_plugins_bonus_order (orderID, userID, points, approval)
                    VALUES(?,?,?,?)";

            Shopware()->Db()->query($sql, array($order["id"], $order["userID"], $points, 0));

            //unlock bonus points direct
            if ($settings["bonus_point_unlock_type"] == "direct") {
                $this->unlockBonusPoints($order["id"], $points, $order["userID"]);
            }
        }
    }

    /**
     * Checks if the user has a voucher in basket
     *
     * @return bool
     */
    private function isVoucherInBasket()
    {
        $basket = $this->getBasket();

        if ($basket && count($basket["content"]) > 0) {
            foreach ($basket["content"] as $item) {
                if ($item["modus"] == 2) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Internal helper function to convert the order to basket
     * @param $order
     * @return array
     */
    public function convertOrderToBasket($order)
    {
        $basket = $order;
        $basket["Amount"] = $basket['invoice_amount'] - $basket['invoice_shipping'];
        $basket["Amount"] = str_replace(",", ".", $basket["Amount"]);

        $sql = "SELECT details.*, attribute.swag_bonus
                FROM s_order_details details
                LEFT JOIN s_order_details_attributes attribute
                    ON attribute.detailID = details.id
                WHERE details.orderID = ?";

        $basket["content"] = Shopware()->Db()->fetchAll($sql, array($order["id"]));
        $basket["content"] = $this->setBasketContentFlags($basket["content"]);

        return $basket;
    }

    /**
     * Internal helper function to set the article flags for bonus items
     * @param $content
     * @return
     */
    public function setBasketContentFlags($content)
    {
        foreach ($content as &$item) {
            //bonus article?
            if ($item['swag_bonus'] == 1) {
                if ($item["modus"] == 0) {
                    $item["isBonusArticle"] = true;
                    $item["points_per_unit"] = $this->getRequiredPointsOfArticle($item["articleID"]);
                    $item["required_points"] = $item["points_per_unit"] * $item["quantity"];
                    $item["priceNumeric"] = 0;
                }
                //bonus voucher?
            } elseif ($item["modus"] == 2) {
                $settings = $this->getBonusSystemSettings();
                if (substr($item["articleordernumber"], 0, 9) == 'GUT-BONUS' || substr($item["ordernumber"], 0, 9) == 'GUT-BONUS') {
                    $item["isBonusVoucher"] = true;
                    $price = floatval(str_replace(",", ".", $item["price"])) * -1;
                    $item["required_points"] = $price * $settings["bonus_voucher_conversion_factor"];
                    $item["required_points"] = round($item["required_points"]);
                }
            }
        }

        return $content;
    }

    /**
     * @return bool
     */
    public function isBonusSystemActive()
    {
        $settings = $this->getBonusSystemSettings();

        return (bool) $settings['bonus_system_active'];
    }
}
