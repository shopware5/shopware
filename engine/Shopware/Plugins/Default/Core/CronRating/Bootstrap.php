<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

use Shopware\Models\Shop\Shop;

/**
 * Shopware Cron for article ratings
 */
class Shopware_Plugins_Core_CronRating_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Bootstrap Installation method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Shopware_CronJob_ArticleComment',
            'onRun'
        );

        return true;
    }

    /**
     * @throws \Exception
     *
     * @return void|string
     */
    public function onRun(Enlight_Components_Cron_EventArgs $job)
    {
        if (empty(Shopware()->Config()->voteSendCalling)) {
            return;
        }

        $sendTime = Shopware()->Config()->get('voteCallingTime', 10);
        $orders = $this->getOrders($sendTime);
        if (empty($orders)) {
            return 'No orders for rating mail found.';
        }

        $orderIds = array_keys($orders);
        $customers = $this->getCustomers($orderIds);
        $orderPositions = $this->getPositions($orderIds);

        $shopPositionBaseProducts = $this->structurePositionsArray($orderPositions);

        $shopPositionImages = $this->getPositionImages($shopPositionBaseProducts);

        $count = 0;
        foreach ($orders as $orderId => $order) {
            if (empty($customers[$orderId]['email']) || empty($orderPositions[$orderId])) {
                continue;
            }

            /** @var Shopware\Models\Shop\Repository $repository */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

            $shopId = is_numeric($order['language']) ? $order['language'] : $order['subshopID'];
            $shop = $repository->getActiveById($shopId);

            /** @var Shopware\Models\Shop\Currency $repository */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Currency');
            $shop->setCurrency($repository->find($order['currencyID']));
            $this->get('shopware.components.shop_registration_service')->registerShop($shop);

            foreach ($orderPositions[$orderId] as &$position) {
                $position['link'] = $this->get('router')->assemble([
                    'module' => 'frontend', 'sViewport' => 'detail',
                    'sArticle' => $position['articleID'],
                ]);

                $position['link_rating_tab'] = $this->get('router')->assemble([
                    'module' => 'frontend', 'sViewport' => 'detail',
                    'sArticle' => $position['articleID'],
                    'jumpTab' => 'rating',
                ]);

                if (!isset($shopPositionImages[$shopId][$position['articleordernumber']]['source'])) {
                    continue;
                }

                $position['image_original']
                    = $position['image_small']
                    = $position['image_large']
                    = $shopPositionImages[$shopId][$position['articleordernumber']]['source'];

                if (!isset($shopPositionImages[$shopId][$position['articleordernumber']]['thumbnails'])) {
                    continue;
                }

                $thumbnails = $shopPositionImages[$shopId][$position['articleordernumber']]['thumbnails'];

                $position['image_small'] = isset($thumbnails[0]) ? $thumbnails[0]['source'] : $position['image_original'];
                $position['image_large'] = isset($thumbnails[1]) ? $thumbnails[1]['source'] : $position['image_original'];
            }

            unset($position);

            $context = [
                'sOrder' => $order,
                'sUser' => $customers[$orderId],
                'sArticles' => $orderPositions[$orderId],
            ];

            $mail = Shopware()->TemplateMail()->createMail('sARTICLECOMMENT', $context);
            $mail->addTo($customers[$orderId]['email']);
            $mail->send();
            ++$count;
        }

        if ($count <= 0) {
            return 'No rating mails sent.';
        }

        return $count . ' rating mail(s) sent.';
    }

    /**
     * @param array $shopPositions
     *
     * @return array
     */
    public function getPositionImages($shopPositions)
    {
        $shopPositionImages = [];

        foreach ($shopPositions as $shopId => $positions) {
            $context = $this->get('shopware_storefront.context_service')->createShopContext($shopId);

            $shopPositionImages[$shopId] = $this->get('shopware_storefront.media_service')->getCovers(
                $positions,
                $context
            );

            $shopPositionImages[$shopId] = array_map(
                function ($mediaStruct) {
                    return $this->get('legacy_struct_converter')->convertMediaStruct($mediaStruct);
                },
                $shopPositionImages[$shopId]
            );
        }

        return $shopPositionImages;
    }

    /**
     * @param string $sendTime
     *
     * @return array
     */
    public function getOrders($sendTime)
    {
        $sql = "
            SELECT
                o.id,
                o.id as orderID,
                o.ordernumber,
                o.ordernumber as order_number,
                o.userID,
                o.userID as customerID,
                o.invoice_amount,
                o.invoice_amount_net,
                o.invoice_shipping,
                o.invoice_shipping_net,
                o.ordertime as ordertime,
                o.status,
                o.status as statusID,
                o.cleared as cleared,
                o.cleared as clearedID,
                o.paymentID as paymentID,
                o.transactionID as transactionID,
                o.comment,
                o.customercomment,
                o.net,
                o.net as netto,
                o.partnerID,
                o.temporaryID,
                o.referer,
                o.cleareddate,
                o.cleareddate as cleared_date,
                o.trackingcode,
                o.language,
                o.currency,
                o.currencyFactor,
                o.subshopID,
                o.dispatchID,
                cu.id as currencyID,
                c.description as cleared_description,
                s.description as status_description,
                p.description as payment_description,
                d.name        as dispatch_description,
                cu.name       as currency_description
            FROM
                s_order as o
            LEFT JOIN s_core_states as s
            ON  (o.status = s.id)
            LEFT JOIN s_core_states as c
            ON  (o.cleared = c.id)
            LEFT JOIN s_core_paymentmeans as p
            ON  (o.paymentID = p.id)
            LEFT JOIN s_premium_dispatch as d
            ON  (o.dispatchID = d.id)
            LEFT JOIN s_core_currencies as cu
            ON  (o.currency = cu.currency)

            WHERE o.status IN (2, 7)
            AND o.ordertime LIKE CONCAT(DATE_SUB(CURDATE(), INTERVAL ? DAY), '%')
        ";

        return Shopware()->Db()->fetchAssoc($sql, [$sendTime]);
    }

    /**
     * @param int[] $orderIds
     *
     * @return array
     */
    public function getCustomers($orderIds)
    {
        $orderIds = Shopware()->Db()->quote($orderIds);
        $sql = "
            SELECT
                b.orderID,
                b.company AS billing_company,
                b.department AS billing_department,
                b.salutation AS billing_salutation,
                u.customernumber,
                b.firstname AS billing_firstname,
                b.lastname AS billing_lastname,
                b.street AS billing_street,
                b.zipcode AS billing_zipcode,
                b.city AS billing_city,
                b.phone AS phone,
                b.phone AS billing_phone,
                b.countryID AS billing_countryID,
                bc.countryname AS billing_country,
                bc.countryiso AS billing_countryiso,
                bca.name AS billing_countryarea,
                bc.countryen AS billing_countryen,
                b.ustid,
                ba.text1 AS billing_text1,
                ba.text2 AS billing_text2,
                ba.text3 AS billing_text3,
                ba.text4 AS billing_text4,
                ba.text5 AS billing_text5,
                ba.text6 AS billing_text6,
                b.orderID as orderID,
                s.company AS shipping_company,
                s.department AS shipping_department,
                s.salutation AS shipping_salutation,
                s.firstname AS shipping_firstname,
                s.lastname AS shipping_lastname,
                s.street AS shipping_street,
                s.zipcode AS shipping_zipcode,
                s.city AS shipping_city,
                s.countryID AS shipping_countryID,
                sc.countryname AS shipping_country,
                sc.countryiso AS shipping_countryiso,
                sca.name AS shipping_countryarea,
                sc.countryen AS shipping_countryen,
                sa.text1 AS shipping_text1,
                sa.text2 AS shipping_text2,
                sa.text3 AS shipping_text3,
                sa.text4 AS shipping_text4,
                sa.text5 AS shipping_text5,
                sa.text6 AS shipping_text6,
                u.*,
                   g.id AS preisgruppe,
                   g.tax AS billing_net
            FROM
                s_order_billingaddress as b
            LEFT JOIN s_order_shippingaddress as s
                ON s.orderID = b.orderID
            LEFT JOIN s_user as u
                ON b.userID = u.id
            LEFT JOIN s_user_addresses as ub
                ON u.default_billing_address_id = ub.id
                AND u.id = ub.user_id
            LEFT JOIN s_core_countries as bc
                ON bc.id = b.countryID
            LEFT JOIN s_core_countries as sc
                ON sc.id = s.countryID
            LEFT JOIN s_core_customergroups as g
                ON u.customergroup = g.groupkey
            LEFT JOIN s_core_countries_areas bca
                ON bc.areaID = bca.id
            LEFT JOIN s_core_countries_areas sca
                ON sc.areaID = sca.id
            LEFT JOIN s_order_billingaddress_attributes ba
                ON b.id = ba.billingID
            LEFT JOIN s_order_shippingaddress_attributes sa
                ON s.id = sa.shippingID
            WHERE b.orderID IN ($orderIds)
        ";

        return Shopware()->Db()->fetchAssoc($sql);
    }

    /**
     * @param int[] $orderIds
     *
     * @return array
     */
    public function getPositions($orderIds)
    {
        $orderIds = Shopware()->Db()->quote($orderIds);
        $sql = "
            SELECT
                d.id as orderdetailsID,
                d.orderID as orderID,
                d.ordernumber,
                d.articleID,
                d.articleordernumber,
                d.price as price,
                d.quantity as quantity,
                d.price*d.quantity as invoice,
                d.name,
                d.status,
                d.shipped,
                d.shippedgroup,
                d.releasedate,
                d.modus,
                d.esdarticle,
                d.taxID,
                t.tax,
                d.esdarticle as esd,
                o.subshopID as subshopID,
                o.language as language
            FROM s_order_details as d
            LEFT JOIN s_core_tax as t
            ON t.id = d.taxID                        
            LEFT JOIN s_articles_details ad
            ON d.articleordernumber = ad.ordernumber
            LEFT JOIN s_order o
            ON d.orderID = o.id
            WHERE d.orderID IN ($orderIds)
            AND ad.active = 1
            AND d.modus = 0
            ORDER BY orderdetailsID ASC
        ";
        $result = Shopware()->Db()->fetchAll($sql);
        $rows = [];
        foreach ($result as $row) {
            $rows[$row['orderID']][$row['orderdetailsID']] = $row;
        }

        return $rows;
    }

    /**
     * Transforms the array from order => positions to shop => positions
     *
     * Input:
     *
     * [
     *   [order_1] => [
     *     position_a,
     *     position_b,
     *     ...
     *   ],
     *   [order_2] => [
     *     position_c,
     *     position_d,
     *     ...
     *   ], ...
     * ]
     *
     * Output:
     *
     * [
     *   [shop_1] => [
     *     position_a,
     *     position_b,
     *     ...
     *   ],
     *   [shop_2] => [
     *     position_c,
     *     position_d
     *   ]
     * ]
     *
     * by using the corresponding shopId for every order's positions.
     *
     * @param array $orderPositions
     *
     * @return array
     */
    private function structurePositionsArray($orderPositions)
    {
        $shopPositionNumbers = [];

        foreach ($orderPositions as $order_id => $positions) {
            $firstPosition = $positions[array_keys($positions)[0]];
            $shopId = is_numeric($firstPosition['language']) ? $firstPosition['language'] : $firstPosition['subshopID'];

            if (!is_array($shopPositionNumbers[$shopId])) {
                $shopPositionNumbers[$shopId] = [];
            }

            $shopPositionNumbers[$shopId] = array_merge(
                array_column($positions, 'articleordernumber'),
                $shopPositionNumbers[$shopId]);
        }

        $shopPositionBaseProducts = [];
        $baseProductFactory = $this->get('shopware_storefront.base_product_factory');

        foreach ($shopPositionNumbers as $shopId => $shopPositions) {
            $shopPositionBaseProducts[$shopId] = $baseProductFactory->createBaseProducts($shopPositions);
        }

        return $shopPositionBaseProducts;
    }
}
