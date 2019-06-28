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

class Shopware_Controllers_Backend_Overview extends Shopware_Controllers_Backend_ExtJs
{
    public function getOrderSummaryAction()
    {
        $startDate = $this->Request()->getParam('fromDate', date('Y-m-d', mktime(0, 0, 0, (int) date('m'), 1, (int) date('Y'))));
        $endDate = $this->Request()->getParam('toDate', date('Y-m-d'));

        $sql = '
            SELECT
                SUM(visitors.uniquevisits) AS visits,
                SUM(visitors.uniquevisits)/SUM(order_count.order_count) AS averageUsers,
                SUM(visitors.pageimpressions) AS hits,
                order_count.order_count AS countOrders,
                customer_count.new_customer_count AS countUsers,
                customer_count.new_customer_order_count AS countCustomers,
                order_amount.amount AS amount,
                visitors.datum AS `date`
            FROM s_statistics_visitors AS visitors
            LEFT OUTER JOIN
            (
                SELECT
                    COUNT(DISTINCT id) AS order_count,
                    DATE (ordertime) AS order_date
                FROM s_order
                WHERE status NOT IN (-1, 4)
                GROUP BY DATE (order_date)
            ) AS order_count
            ON order_count.order_date = visitors.datum
            LEFT OUTER JOIN
            (
                SELECT
                    SUM(invoice_amount/currencyFactor) AS amount,
                    DATE (ordertime) AS order_date
                FROM s_order
                WHERE status NOT IN (-1, 4)
                GROUP BY DATE (order_date)
            ) AS order_amount
            ON order_amount.order_date = visitors.datum
            LEFT OUTER JOIN
            (
                SELECT
                    COUNT(DISTINCT s_user.id) AS new_customer_count,
                    COUNT(DISTINCT s_order.id) AS new_customer_order_count,
                    firstlogin AS first_login_date
                FROM s_user
                LEFT JOIN s_order ON s_order.userID = s_user.id
                    AND (DATE(s_order.ordertime) = DATE(s_user.firstlogin))
                    AND s_order.status NOT IN (-1, 4)
                GROUP BY first_login_date
            ) AS customer_count
            ON customer_count.first_login_date = visitors.datum
            WHERE visitors.datum <= :endDate
                AND visitors.datum >= :startDate
            GROUP BY TO_DAYS(visitors.datum)
            ORDER BY visitors.datum DESC
        ';

        $stmt = Shopware()->Db()->query($sql, [
            'endDate' => $endDate,
            'startDate' => $startDate,
        ]);

        $orders = [];

        while ($order = $stmt->fetch()) {
            foreach ($order as $key => $value) {
                if (empty($value)) {
                    $order[$key] = 0;
                }
            }
            if ($order['countOrders'] != 0) {
                $order['averageOrders'] = $order['amount'] / $order['countOrders'];
            } else {
                $order['averageOrders'] = 0;
            }
            $order['amount'] = round($order['amount'], 2);
            $orders[] = $order;
        }

        $this->View()->assign([
            'success' => true,
            'data' => $orders,
            'total' => count($orders),
        ]);
    }

    /**
     * Method to define acl dependencies in backend controllers
     */
    protected function initAcl()
    {
        $this->addAclPermission('getOrderSummary', 'read');
    }
}
