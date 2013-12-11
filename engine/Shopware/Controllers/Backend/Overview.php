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
 * Shopware Backend Controller for the overview module
 */
class Shopware_Controllers_Backend_Overview extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Method to define acl dependencies in backend controllers
     */
    protected function initAcl()
    {
        $this->setAclResourceName('overview');
        $this->addAclPermission('getOrderSummary', 'read');
    }

    public function getOrderSummaryAction()
    {
        $startDate = $this->Request()->getParam('fromDate', date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))));
        $endDate   = $this->Request()->getParam('toDate', date("Y-m-d"));

        $sql = "
         SELECT
           SUM(v.uniquevisits) AS `visits`,
           SUM(v.uniquevisits)/SUM(o.`Bestellungen`) AS `averageUsers`,
           SUM(v.pageimpressions) AS `hits`,
           o.`Bestellungen` AS `countOrders`,
           SUM(u.`Neukunden`) AS `countCustomers`,
           ou.`Umsatz` AS `amount`,
           v.datum AS `date`
         FROM
          `s_statistics_visitors` as v
         LEFT OUTER JOIN
          (
           SELECT
            COUNT(DISTINCT id) AS `Bestellungen`,
            DATE (ordertime) as `date`
           FROM
            `s_order`
           WHERE
            status != 4
           AND
            status != -1
           GROUP BY
            DATE (ordertime)
          ) as o
         ON
          `o`.`date`=v.datum
         LEFT OUTER JOIN
          (
           SELECT
            SUM(invoice_amount/currencyFactor) AS `Umsatz`,
            DATE (ordertime) as `date`
           FROM
            `s_order`
           WHERE
            status != 4
           AND
            status != -1
           GROUP BY
            DATE (ordertime)
          ) as ou
         ON
          `ou`.`date`=v.datum
         LEFT OUTER JOIN
          (
           SELECT
            COUNT(DISTINCT  id) AS `Neukunden`,
            firstlogin as `date`
           FROM
            `s_user`
           GROUP BY
            firstlogin
          ) as u
         ON
          `u`.`date`=v.datum
         WHERE
          v.datum <= :endDate
         AND
          v.datum >= :startDate
         GROUP BY TO_DAYS(v.datum)
         ORDER BY v.datum DESC
        ";

        $stmt = Shopware()->Db()->query($sql, array(
            'endDate'   => $endDate,
            'startDate' => $startDate,
        ));

        $orders = array();

        while ($order = $stmt->fetch()) {
            foreach ($order as $key => $value) {
                if (empty($value)) {
                    $order[$key] = 0;
                }
            }
            if (!empty($order['countOrders'])) {
                $order['averageOrders'] = $order['amount'] / $order['countOrders'];
            } else {
                $order['averageOrders'] = 0;
            }
            $order['amount'] = round($order['amount'], 2);
            $orders[] = $order;
        }
        $this->View()->assign(array(
            'success' => true,
            'data'    => $orders,
            'total'   => count($orders),
        ));
    }
}
