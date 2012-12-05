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

class Shopware_Controllers_Frontend_BonusSystem extends Enlight_Controller_Action
{
    /**
     * Pre dispatch of the bonus system frontend controller
     * @return void
     */
    public function preDispatch()
    {
        $this->View()->extendsTemplate('frontend/plugins/swag_bonus_system/account/content_right.tpl');
    }

    /**
     * Standard index action of the bonus system frontend controller
     * @return void
     */
    public function indexAction()
    {
        $model = new BonusSystemDataModel();

        $settings = $model->getBonusSystemSettings();

        if ($settings["bonus_system_active"] && $settings["bonus_articles_active"]) {
            $this->View()->sBonusSystem = $model->getBonusSystemData();
        } else {
            $this->redirect('index', 'index');
        }
    }

    /**
     * This function calls the form jQuery slider to get the bonus article page
     * @return void
     */
    public function sliderAction()
    {
        $model = new BonusSystemDataModel();

        $perPage = $this->Request()->getParam('perPage', 3);
        $page    = $this->Request()->getParam('pages', 1);

        $pages = array_chunk($model->getBonusArticles(), $perPage);
        $count = count($pages);
        $result = $pages[$page-1];

        $this->View()->loadTemplate("frontend/plugins/swag_bonus_system/recommendation/slide_articles.tpl");
        $this->View()->assign('sSliderArticles', $result);
        $this->View()->assign('sSliderPages', $count);
    }

    /**
     * This function loads all user orders with bonus points
     * @return void
     */
    public function pointsAction()
    {
        if (!Shopware()->Session()->sUserId > 0) {
            $this->redirect('account', 'login');
        }

        $model = new BonusSystemDataModel();
        $settings = $model->getBonusSystemSettings();

        $sql = "SELECT
                    s_order.id,
                    s_order.currencyFactor,
                    ordertime,
                    ordernumber,
                    (CASE WHEN s_order.net = 1 THEN s_order.invoice_amount_net ELSE s_order.invoice_amount END)as amount,
                    s_core_plugins_bonus_order.id as bonusOrderId,
                    s_core_plugins_bonus_order.approval,
                    s_core_plugins_bonus_order.points as earning,
                    s_core_plugins_bonus_settings.bonus_point_conversion_factor
                FROM s_order
                  JOIN s_core_plugins_bonus_order
                    ON (s_core_plugins_bonus_order.orderID = s_order.id)
                 LEFT JOIN s_core_plugins_bonus_settings
                  ON (s_core_plugins_bonus_settings.shopID = s_order.subshopID)
                WHERE s_order.userID = ?";

        $userID = Shopware()->Session()->sUserId;

        $orders = Shopware()->Db()->fetchAll($sql, array($userID));

        // iterate the orders to get the order content and calculate the bonus points
        foreach ($orders as &$order) {
            $sql = "SELECT details.*, attributes.swag_bonus
                    FROM s_order_details details
                    LEFT JOIN s_order_details_attributes attributes ON attributes.detailID = details.id
                    WHERE orderID = ?
                    AND (modus = 0 AND swag_bonus = 1
                         OR modus = 2 AND articleordernumber LIKE 'GUT-BONUS%')";

            $content = Shopware()->Db()->fetchAll($sql, array($order["id"]));

            $spending = 0;
            foreach ($content as $article) {
                if ($article["modus"] == 2) {
                    // bonus-voucher
                    $price = floatval(str_replace(",", ".", $article["price"])) * -1;
                    $required_points = $price * $settings["bonus_voucher_conversion_factor"];
                    $required_points = round($required_points);
                } else {
                    // bonus-articles
                    $points_per_unit = $model->getRequiredPointsOfArticle($article["articleID"]);
                    $required_points = $points_per_unit * $article["quantity"];
                }

                $spending += $required_points;
            }
            $order["spending"] = $spending;
        }

        $this->View()->bonusOrders = $orders;
    }

    /**
     * This function creates dynamic a voucher and add him to the basket.
     * @return void
     */
    public function addVoucherAction()
    {
        $points        = $this->Request()->getParam('points');
        $sTargetAction = $this->Request()->getParam('sTargetAction');

        // generate a new voucher code
        $orderCode = "GUT-BONUS";
        $sql = "SELECT COUNT(*) FROM s_emarketing_vouchers WHERE ordercode like '$orderCode%'";
        $count = Shopware()->Db()->fetchOne($sql);
        $voucherCode = "bonusvoucher" . $count;
        $orderCode .= $count;

        $model = new BonusSystemDataModel();
        $settings = $model->getBonusSystemSettings();
        $value = $points / $settings["bonus_voucher_conversion_factor"];

        // insert the new voucher
        Shopware()->Db()->insert('s_emarketing_vouchers', array(
            'description'    => 'Gutschein',
            'vouchercode'    => $voucherCode,
            'numberofunits'  => 1,
            'value'          => $value,
            'minimumcharge'  => $value,
            'shippingfree'   => 0,
            'bindtosupplier' => 0,
            'ordercode'      => $orderCode,
            'modus'          => 2,
            'taxconfig'      => 'auto',
        ));

        // add voucher to basket
        Shopware()->Modules()->Basket()->sAddVoucher($voucherCode);

        $this->forward($sTargetAction, 'checkout');
    }
}
