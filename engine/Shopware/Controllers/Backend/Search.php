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
 * @package    Shopware_Controllers, Shopware_Models
 * @subpackage Backend, Frontend, Article, Adapter
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
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
	public function indexAction() {
		if(!$this->Request()->isPost()) {
			return;
		}

		// Sanitize and clean up the search parameter for later processing
		$search =  $this->Request()->get('search');
		$search = strtolower($search);
		$search = trim($search);

		$search = preg_replace("/[^a-z0-9äöüß]/", " ", $search);
		$search = trim(preg_replace('/\s+/', '%', $search), "%");

		$articles = $this->getArticles($search);
		$customers = $this->getCustomers($search);
		$orders = $this->getOrders($search);

		$this->View()->assign('searchResult', array(
			'articles'  => $articles,
			'customers' => $customers,
			'orders'    => $orders
		));
 	}

	/**
	 * Queries the articles from the database based on the passed search term
	 *
	 * @param $search
	 * @return array
	 */
	public function getArticles($search) {

		$search = Shopware()->Db()->quote("%$search%");

		$sql = "
			SELECT DISTINCT
				a.id,
				a.name,
				a.description_long,
				a.description,
				d.ordernumber
			FROM
				s_articles as a
			INNER JOIN s_articles_details as d
				ON a.id = d.articleID
			LEFT JOIN s_articles_translations AS t
				ON a.id=t.articleID
			LEFT JOIN s_articles_supplier AS s
			    ON a.supplierID=s.id
			WHERE
			    d.kind = 1
		    AND
				(
						a.name LIKE $search
					OR
						d.ordernumber LIKE $search
					OR
						t.name LIKE $search
				    OR
				        s.name LIKE $search
				)";

		$sql = Shopware()->Db()->limit($sql, $this->searchLimit);
		return Shopware()->Db()->fetchAll($sql);
	}

	/**
	 * Queries the customers from the database based on the passed search term
	 *
	 * @param $search
	 * @return array
	 */
	public function getCustomers($search) {

		$search = Shopware()->Db()->quote("%$search%");

		$sql = "
			SELECT DISTINCT userID as id, firstname, lastname, company,
			CONCAT(street, ' ', streetnumber, ' ', zipcode, ' ', city) as description
			FROM s_user_billingaddress, s_user
			WHERE (
				email LIKE $search
				OR customernumber LIKE $search
				OR TRIM(CONCAT(company,' ',department)) LIKE $search
				OR TRIM(CONCAT(firstname,' ',lastname)) LIKE $search
			)
			AND s_user.id=s_user_billingaddress.userID
			GROUP BY id
			ORDER BY lastname, company ASC
		";

		$sql = Shopware()->Db()->limit($sql, $this->searchLimit);
		$result = Shopware()->Db()->fetchAll($sql);

		foreach($result as &$item) {
			if ($item["company"]){
				$item["name"] = $item["company"];
			} else {
				$item["name"] = $item["firstname"] . " " . $item["lastname"];
			}
		}

		return $result;
	}

	/**
	 * Queries the orders from the database based on the passed search term
	 *
	 * @param $search
	 * @return array
	 */
	public function getOrders($search) {
		$search = Shopware()->Db()->quote("%$search%");

		$sql = "
			SELECT
				s_order.id,
				s_order.ordernumber as name,
				s_order.userID,
				s_order.invoice_amount as totalAmount,
				s_order.transactionID,
				`status`,
				`cleared`,
				`type`,
				docID,
				CONCAT(
                    (
                    CASE s_order_billingaddress.salutation
                       WHEN 'company'
                       THEN s_order_billingaddress.company
                       ELSE CONCAT(s_order_billingaddress.firstname, ' ', s_order_billingaddress.lastname)
                    END
                    ), ', ',
                    s_core_paymentmeans.description
				) AS description
			FROM
				s_order
			LEFT JOIN s_order_documents
			    ON s_order_documents.orderID=s_order.id AND docID != '0'
			LEFT JOIN s_order_billingaddress
			    ON s_order.id=s_order_billingaddress.orderID
			LEFT JOIN s_core_paymentmeans
			    ON s_order.paymentID = s_core_paymentmeans.id
			WHERE  s_order.id != '0'
			AND (s_order.ordernumber LIKE $search OR s_order.transactionID LIKE $search OR docID LIKE $search)
			GROUP BY s_order.id
			ORDER BY s_order.ordertime DESC
		";
		$sql = Shopware()->Db()->limit($sql, $this->searchLimit);
		return Shopware()->Db()->fetchAll($sql);
	}
}