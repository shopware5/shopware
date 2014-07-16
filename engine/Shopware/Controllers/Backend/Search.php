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
 * Backend search controller
 *
 * This controller provides the global search in the Shopware backend. The
 * search has the ability to provides search results from the different
 * areas starting from articles to orders
 */
class Shopware_Controllers_Backend_Search extends Shopware_Controllers_Backend_ExtJs
{

    /** @var int - The limit for all SQL queries */
    public $searchLimit = 5;

    /**
     * Sanitizes the passed term and queries the different areas of the search
     * @return mixed
     */
    public function indexAction()
    {
        if (!$this->Request()->isPost()) {
            return;
        }

        // Sanitize and clean up the search parameter for later processing
        $search = $this->Request()->get('search');
        $search = strtolower($search);
        $search = trim($search);

        $search = preg_replace("/[^\\w0-9]+/u", " ", $search);
        $search = trim(preg_replace('/\s+/', '%', $search), "%");

        $articles = $this->getArticles($search);
        $customers = $this->getCustomers($search);
        $orders = $this->getOrders($search);

        $this->View()->assign('searchResult', array(
            'articles' => $articles,
            'customers' => $customers,
            'orders' => $orders
        ));
    }

    /**
     * Queries the articles from the database based on the passed search term
     *
     * @param $search
     * @return array
     */
    public function getArticles($search)
    {
        $search2 = Shopware()->Db()->quote("$search%");
        $search = Shopware()->Db()->quote("%$search%");

        $sql = "
            SELECT DISTINCT
                a.id,
                a.name,
                a.description_long,
                a.description,
                IFNULL(d.ordernumber, m.ordernumber) as ordernumber
            FROM s_articles as a
            JOIN s_articles_details as m
            ON m.id = a.main_detail_id
            LEFT JOIN s_articles_details as d
            ON a.id = d.articleID
            AND d.ordernumber LIKE $search2
            LEFT JOIN s_articles_translations AS t
            ON a.id=t.articleID
            LEFT JOIN s_articles_supplier AS s
            ON a.supplierID=s.id
            WHERE ( a.name LIKE $search
                OR t.name LIKE $search
                OR s.name LIKE $search
                OR d.id IS NOT NULL
            )
        ";
        $sql = Shopware()->Db()->limit($sql, $this->searchLimit);
        return Shopware()->Db()->fetchAll($sql);
    }

    /**
     * Queries the customers from the database based on the passed search term
     *
     * @param $search
     * @return array
     */
    public function getCustomers($search)
    {
        $search2 = Shopware()->Db()->quote("$search%");
        $search = Shopware()->Db()->quote("%$search%");

        $sql = "
            SELECT userID as id,
            IF(b.company != '', b.company, CONCAT(b.firstname, ' ', b.lastname)) as name,
            CONCAT(street, ' ', streetnumber, ' ', zipcode, ' ', city) as description
            FROM s_user_billingaddress b, s_user u
            WHERE (
                email LIKE $search
                OR customernumber LIKE $search2
                OR TRIM(CONCAT(company,' ', department)) LIKE $search
                OR TRIM(CONCAT(firstname,' ',lastname)) LIKE $search
            )
            AND u.id = b.userID
            GROUP BY id
            ORDER BY name ASC
        ";

        $sql = Shopware()->Db()->limit($sql, $this->searchLimit);
        $result = Shopware()->Db()->fetchAll($sql);

        return $result;
    }

    /**
     * Queries the orders from the database based on the passed search term
     *
     * @param $search
     * @return array
     */
    public function getOrders($search)
    {
        $search = Shopware()->Db()->quote("$search%");

        $sql = "
            SELECT
                o.id,
                o.ordernumber as name,
                o.userID,
                o.invoice_amount as totalAmount,
                o.transactionID,
                o.status,
                o.cleared,
                d.type,
                d.docID,
                CONCAT(
                    IF(b.company != '', b.company, CONCAT(b.firstname, ' ', b.lastname)),
                    ', ',
                    p.description
                ) as description
            FROM s_order o
            LEFT JOIN s_order_documents d
            ON d.orderID=o.id AND docID != '0'
            LEFT JOIN s_order_billingaddress b
            ON o.id=b.orderID
            LEFT JOIN s_core_paymentmeans p
            ON o.paymentID = p.id
            WHERE o.id != '0'
            AND (o.ordernumber LIKE $search
            OR o.transactionID LIKE $search
            OR docID LIKE $search)
            GROUP BY o.id
            ORDER BY o.ordertime DESC
        ";
        $sql = Shopware()->Db()->limit($sql, $this->searchLimit);
        return Shopware()->Db()->fetchAll($sql);
    }
}
