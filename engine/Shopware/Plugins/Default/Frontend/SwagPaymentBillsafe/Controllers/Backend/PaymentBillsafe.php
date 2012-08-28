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
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Billsafe payment controller
 * 
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_PaymentBillsafe extends Shopware_Controllers_Backend_ExtJs
{
	/**
	 * Skeleton action method.
	 *  
	 * Does nothing else.
	 */
	public function skeletonAction ()
	{
	}

	/**
	 * List payments action.
	 * 
	 * Outputs the payment data as json list.
	 */
	public function listAction()
	{
		$limit = $this->Request()->getParam('limit', 20);
		$start = $this->Request()->getParam('start', 0);
		
		if($sort = $this->Request()->getParam('sort')) {
			//$sort = Zend_Json::decode($sort);
			$sort = current($sort);
		}
		$direction = empty($sort['direction']) || $sort['direction'] == 'DESC' ? 'DESC' : 'ASC';
		$property = empty($sort['property']) ? 'order_date' : $sort['property'];
		
		if($filter = $this->Request()->getParam('filter')) {
			//$filter = Zend_Json::decode($filter);
			foreach ($filter as $value) {
				if(empty($value['property']) || empty($value['value'])) {
					continue;
				}
				if($value['property'] == 'search') {
					$this->Request()->setParam('search', $value['value']);
				}
			}
		}
		
		$select = Shopware()->Db()
			->select()
			->from(array('o' => 's_order'), array(
				new Zend_Db_Expr('SQL_CALC_FOUND_ROWS o.id'),
				'clearedID' => 'cleared',
				'statusID' => 'status',
				'amount' => 'invoice_amount', 'currency',
				'order_date' => 'ordertime', 'order_number' => 'ordernumber',
				'transactionID', 'comment' => 'customercomment', 'cleared_date' => 'cleareddate',
				'trackingID' => 'trackingcode',
                'userID' => 'u.userID',
				'invoice_number' => new Zend_Db_Expr('(' . Shopware()->Db()
					->select()
					->from(array('s_order_documents'), array('docID'))
					->where('orderID=o.id')
					->order('docID DESC')
					->limit(1) . ')'),
				'invoice_hash' => new Zend_Db_Expr('(' . Shopware()->Db()
					->select()
					->from(array('s_order_documents'), array('hash'))
					->where('orderID=o.id')
					->order('docID DESC')
					->limit(1) . ')')
			))
			->join(
				array('p' => 's_core_paymentmeans'),
				'p.id =  o.paymentID',
				array(
					'payment_description' => 'p.description'
				)
			)
			->joinLeft(
				array('so' => 's_core_states'),
				'so.id =  o.status',
				array(
					'status_description' => 'so.description'
				)
			)
			->joinLeft(
				array('sc' => 's_core_states'),
				'sc.id =  o.cleared',
				array(
					'cleared_description' => 'sc.description'
				)
			)
			->joinLeft(
				array('u' => 's_user_billingaddress'),
				'u.userID = o.userID',
				array()
			)
			->joinLeft(
				array('b' => 's_order_billingaddress'),
				'b.orderID = o.id',
				new Zend_Db_Expr("
					IF(b.id IS NULL,
						IF(u.company='', CONCAT(u.firstname, ' ', u.lastname), u.company),
						IF(b.company='', CONCAT(b.firstname, ' ', b.lastname), b.company)
					) as customer
				")
			)
			->joinLeft(
				array('d' => 's_premium_dispatch'),
				'd.id = o.dispatchID',
				array(
					'dispatch_description' => 'd.name'
				)
			)
			->where('p.name LIKE ?', 'billsafe_%')
			->where('o.status >= 0')
			->order(array($property . ' ' . $direction))
			->limit($limit, $start);

		if($search = $this->Request()->getParam('search')) {
			$search = trim($search);
			$search = '%'.$search.'%';
			$search = Shopware()->Db()->quote($search);
						
			$select->where('o.transactionID LIKE ' . $search
                . ' OR o.ordernumber LIKE ' . $search
                . ' OR b.lastname LIKE ' . $search
                . ' OR u.lastname LIKE ' . $search
                . ' OR b.company LIKE ' . $search
                . ' OR u.company LIKE ' . $search);
		}
		
		
		$rows = Shopware()->Db()->fetchAll($select);
        $total = Shopware()->Db()->fetchOne('SELECT FOUND_ROWS()');
		
		foreach ($rows as $key=>$row) {			

			if($rows[$key]['cleared_date'] == '0000-00-00 00:00:00') {
				$rows[$key]['cleared_date'] = null;
			}
			$rows[$key]['amount_format'] = Shopware()->Currency()->toCurrency($row['amount'], array('currency' => $row['currency']));
		}

		$this->View()->assign(array('data'=>$rows, 'total'=>$total, 'success'=>true));
	}
	
	/**
	 * List payouts action.
	 * 
	 * Outputs the payout data as json list.
	 */
	public function payoutListAction()
	{
		$filter = $this->Request()->getParam('filter');
		if(empty($filter[0]['value'])) {
			return;
		}
		$transactionId = (int) $filter[0]['value'];
		
		try {
			$client = Shopware()->BillsafeClient();
			$client->setEncoding('UTF-8');
			
			$result = $client->getPayoutStatus(array('transactionId' => $transactionId));
			
			if($result->ack == 'ERROR') {
				throw new Exception($result->errorList->message, $result->errorList->code);
			}
			
			if(is_array($result->payoutList)) {
				$rows = $result->payoutList;
			} else {
				$rows = array($result->payoutList);
			}
			
			if(is_array($result->returnList)) {
				$rows = array_merge($rows, $result->returnList);
			} else {
				$rows[] = $result->returnList;
			}
			
			foreach ($rows as $key => $row) {
				$row->number = $row->settlementNumber;
				$row->amount_format = Shopware()->Currency()->toCurrency($row->amount, array('currency' => 'EUR'));
				unset($row->settlementNumber);
			}
			
			$this->View()->assign(array('data'=>$rows, 'total'=>count($rows), 'success'=>true));
		} catch (Exception $e) {
			$this->View()->assign(array('message' => $e->getMessage(), 'success' => false));
		}
	}
		
	/**
	 * List articles action.
	 * 
	 * Outputs the article data as json list.
	 */
	public function articleListAction()
	{
		$filter = $this->Request()->getParam('filter');
		if(empty($filter[0]['value'])) {
			return;
		}
		$transactionId = (int) $filter[0]['value'];
		
		try {
			$client = Shopware()->BillsafeClient();
			$client->setEncoding('UTF-8');
			
			$result = $client->getArticleList(array('transactionId' => $transactionId));
			if($result->ack == 'ERROR') {
				throw new Exception($result->errorList->message, $result->errorList->code);
			}
			if(is_array($result->articleList)) {
				$rows = $result->articleList;
			} else {
				$rows = array($result->articleList);
			}
			foreach ($rows as $key => $row) {
				$row->quantity_shipped = $row->quantityShipped;
				$row->price = $row->grossPrice;
				$row->price_format = Shopware()->Currency()->toCurrency($row->grossPrice, array('currency' => 'EUR'));
				unset($row->quantityShipped, $row->grossPrice);
			}
			$this->View()->assign(array('data'=>$rows, 'total'=>count($rows), 'success'=>true));
		} catch (Exception $e) {
			$this->View()->assign(array('message' => $e->getMessage(), 'success' => false));
		}
	}
	
	/**
	 * Report direct payment action.
	 * 
	 * Reports the direct payment to payment provider.
	 */
	public function bookAction()
	{
		$transactionId = $this->Request()->getParam('transactionID');
		$amount = $this->Request()->getParam('book_amount');
		$amount = str_replace(',', '.', $amount);
		$date = $this->Request()->getParam('book_date');
		$date = new Zend_Date($date);
		$date = $date->toString('y-MM-dd');
		$currency = $this->Request()->getParam('currency');
		
		try {
			$client = Shopware()->BillsafeClient();
			$client->setEncoding('UTF-8');
			
			$result = $client->reportDirectPayment(array(
				'transactionId' => $transactionId,
				'amount' => $amount,
				'date' => $date,
				'currencyCode' => $currency
			));
			if($result->ack == 'ERROR') {
				throw new Exception($result->errorList->message, $result->errorList->code);
			}
			
			$this->View()->assign(array('success' => true));
		} catch (Exception $e) {
			$this->View()->assign(array('message' => $e->getMessage(), 'success' => false));
		}
	}
	
	/**
	 * Report pause payment action.
	 * 
	 * Reports the payment pause to payment provider.
	 */
	public function pauseAction()
	{
		$transactionId = $this->Request()->getParam('transactionID');
		
		$pause = new Zend_Date($this->Request()->getParam('pause'));
		$now = new Zend_Date();
		$now->setHour(0)->setMinute(0)->setSecond(0);
		$pause->sub($now);
		$pause = round($pause->getTimestamp() / 60 / 60 / 24);
		$pause = $pause > 10 ? 10 : $pause;
		
		try {
			$client = Shopware()->BillsafeClient();
			$client->setEncoding('UTF-8');
			
			$result = $client->pauseTransaction(array(
				'transactionId' => $transactionId,
				'pause' => $pause
			));
			if($result->ack == 'ERROR') {
				throw new Exception($result->errorList->message, $result->errorList->code);
			}

			$this->setPaymentStatus($transactionId, 19); // Verzoegert
					
			$this->View()->assign(array('success' => true));
		} catch (Exception $e) {
			$this->View()->assign(array('message' => $e->getMessage(), 'success' => false));
		}
	}
	
	/**
	 * Cancel action method
	 * 
	 * Reports the payment cancel to payment provider
	 */
	public function cancelAction()
	{
		$transactionId = $this->Request()->getParam('transactionID');
		$amount = 0;
		$taxAmount = 0;
		$currency = $this->Request()->getParam('currency');
		
		$articleList = $this->Request()->getParam('articleList', array());
		foreach ($articleList as $key => $article) {

			if($article['tax'] < 1) {
				$article['tax'] *= 100;
			}
			
			$articleAmount = $article['price'] * $article['quantity'];
			$amount += $articleAmount;
			$taxAmount += round($articleAmount / (100+$article['tax']) * $article['tax'], 2);
			
			$articleList[$key] = array(
				'number' => $article['number'],
				'name' => $article['name'],
				'type' => $article['type'],
				'quantity' => $article['quantity'],
				'quantityShipped' => $article['quantity_shipped'],
				'grossPrice' => $article['price'],
				'tax' => $article['tax'],
			);
		}
		$articleList = array_values($articleList);
				
		try {
			$client = Shopware()->BillsafeClient();
			$client->setEncoding('UTF-8');
			
			$result = $client->updateArticleList(array(
				'transactionId' => $transactionId,
				'order' => array(
					'amount' => $amount,
					'taxAmount' => $taxAmount,
					'currencyCode' => $currency
				),
				'articleList' => $articleList
			));
			if($result->ack == 'ERROR') {
				throw new Exception($result->errorList->message, $result->errorList->code);
			}
			
			if(!empty($articleList)) {
				$this->updateOrderDetails($transactionId, $articleList);
				
				$sql = '
					UPDATE s_order o
					SET o.invoice_amount=?, o.invoice_amount_net=?
					WHERE o.transactionID=? AND o.status >= 0
				';
				Shopware()->Db()->query($sql, array(
					$amount,
					$amount - $taxAmount,
					//$comment, , o.comment=?
					$transactionId
				));
			}
			if(empty($articleList)) {
				$this->setOrderStatus($transactionId, 4); // Storniert 
			}
					
			$this->View()->assign(array('success' => true));
		} catch (Exception $e) {
			$this->View()->assign(array('message' => $e->getMessage(), 'success' => false));
		}
	}
	
	/**
	 * Change shipment action
	 * 
	 * Reports the shipment to payment provider
	 */
	public function shipmentAction()
	{
		$transactionId = $this->Request()->getParam('transactionID');
		$trackingId = $this->Request()->getParam('trackingID');
		$parcelCompany = $this->Request()->getParam('dispatch_description');
		$invoiceNumber = $this->Request()->getParam('invoice_number');
		
		$articleList = $this->Request()->getParam('articleList', array());
		foreach ($articleList as $key => $article) {
			$articleList[$key] = array(
				'number' => $article['number'],
				'name' => $article['name'],
				'type' => $article['type'],
				'quantity' => $article['quantity_shipped'],
				//'quantityShipped' => $article['quantity_shipped'],
				'grossPrice' => $article['price'],
				'tax' => $article['tax'],
			);
		}
		$articleList = array_values($articleList);
				
		try {
			$client = Shopware()->BillsafeClient();
			$client->setEncoding('UTF-8');
						
			$result = $client->reportShipment(array(
				'transactionId' => $transactionId,
				//'shippingDate',
				'parcel' => array(
					'service' => !empty($parcelCompany) ? 'OTHER' : null,
					'company' => $parcelCompany,
					'trackingId' => $trackingId
				),
				'articleList' => $articleList
			));
			if($result->ack == 'ERROR') {
				throw new Exception($result->errorList->message, $result->errorList->code);
			}
						
			if(!empty($articleList)) {
				$this->updateOrderDetails($transactionId, $articleList);
			}
			
			if(empty($articleList)) {
				//$this->setOrderStatus($transactionId, 7); // Komplett ausgeliefert
				$this->setPaymentStatus($transactionId, 10); // Komplett in Rechnung gestellt
			} else {
				//$this->setOrderStatus($transactionId, 6); // Teilweise ausgeliefert
				$this->setPaymentStatus($transactionId, 9); // Teilweise in Rechnung gestellt
			}
					
			$this->View()->assign(array('success' => true));
		} catch (Exception $e) {
			$this->View()->assign(array('message' => $e->getMessage(), 'success' => false));
		}
	}
	
	/**
	 * Updates the order to the shop data.
	 *
	 * @param string $transactionId
	 * @param array $articleList
	 */
	public function updateOrderDetails($transactionId, $articleList)
	{		
		foreach ($articleList as $article) {
			if(empty($article['number'])) {
				continue;
			}
			
			if($article['type'] == 'shipment') {
				$sql = '
					UPDATE `s_order` o
					SET
						`invoice_shipping`=?,
						`invoice_shipping_net`=ROUND(? / (100 + ?) * 100, 2)
					WHERE o.transactionID=?
					AND o.status >= 0
				';
				Shopware()->Db()->query($sql, array(
					$article['grossPrice'] * $article['quantity'],
					$article['grossPrice'] * $article['quantity'],
					$article['tax'],
					$transactionId,
				));
			} else {
				if(empty($article['quantity'])) {
					$status = 2;
				} elseif($article['quantityShipped'] >= $article['quantity']) {
					$status = 3;
				} elseif(!empty($article['quantityShipped'])) {
					$status = 1;
				} else {
					$status = 0;
				}
				
				$sql = '
					SELECT od.id
					FROM `s_order_details` od, `s_order` o
					WHERE o.id = od.orderID
					AND o.transactionID=?
					AND o.status >= 0
					AND od.articleordernumber=?
				';
				$orderDetailsId = Shopware()->Db()->fetchOne($sql, array($transactionId, $article['number']));
				
				if(!empty($orderDetailsId)) {
					$sql = '
						UPDATE `s_order_details` od, `s_order` o
						SET od.status=?, od.quantity=?, od.shipped=?, od.price=ROUND(? / (100 + IF(o.net=0, 0, ?)) * 100, 2)
						WHERE o.id = od.orderID
						AND o.transactionID=?
						AND o.status >= 0
						AND od.articleordernumber=?
					';
					Shopware()->Db()->query($sql, array(
						$status,
						$article['quantity'],
						$article['quantityShipped'],
						$article['grossPrice'],
						$article['tax'],
						$transactionId,
						$article['number']
					));
				} elseif(!empty($article['quantity'])) {
										
					if($article['type'] == 'handling') {
						$modus = 4;
					} elseif($article['type'] == 'voucher') {
						$modus = 3;
					} else /*if($article['type'] == 'goods')*/ {
						$modus = 0;
					}
					
					$sql = "
						INSERT INTO `s_order_details` (
							`orderID`, `ordernumber`,
							`articleordernumber`, `name`,
							`status`, `quantity`, `shipped`,
							`price`,
							`modus`, `taxID`
						)
						SELECT
							o.id, o.ordernumber,
							?, ?,
							?, ?, ?,
							ROUND(? / (100 + IF(o.net=0, 0, ?)) * 100, 2),
							?,
							(SELECT `id` FROM `s_core_tax` ORDER BY `tax`=? DESC, `id` LIMIT 1)
						FROM `s_order` o
						LEFT JOIN `s_articles_details` ad
						ON ad.ordernumber=?
						WHERE o.transactionID=?
						AND o.status >= 0
					";
					
					if(function_exists('mb_convert_encoding')) {
						$article['name'] = html_entity_decode(mb_convert_encoding($article['name'], 'HTML-ENTITIES', 'UTF-8'));
					} else {
						$article['name'] = utf8_decode($article['name']);
					}
					
					Shopware()->Db()->query($sql, array(
						$article['number'],
						$article['name'],
						$status,
						$article['quantity'],
						$article['quantityShipped'],
						$article['grossPrice'],
						$article['tax'],
						$modus,
						$article['tax'],
						$article['number'],
						$transactionId,
					));
				}
			}
		}
	}
	
	/**
	 * Sets order status and if necessary, it sends a status email
	 *
	 * @param string $transactionId
	 * @param int $statusId
	 */
	public function setOrderStatus($transactionId, $statusId)
	{
		$config = Shopware()->Plugins()->Frontend()->SwagPaymentBillsafe()->Config();
		$sendStatusMail = (bool) $config->paymentStatusMail;
		$order = Shopware()->Modules()->Order();
		
		$sql = '
			SELECT id FROM s_order WHERE transactionID=? AND status>=0
		';
		$orderId = Shopware()->Db()->fetchOne($sql, array(
			$transactionId
		));

        $order = Shopware()->Modules()->Order();
        $order->setOrderStatus($orderId, $statusId, $sendStatusMail);
	}
	
	/**
	 * Sets payment status and if necessary, it sends a status email
	 *
	 * @param string $transactionId
	 * @param int $paymentStatusId
	 */
	public function setPaymentStatus($transactionId, $paymentStatusId)
	{
		$sql = '
			SELECT id FROM s_order WHERE transactionID=? AND status!=-1
		';
		$orderId = Shopware()->Db()->fetchOne($sql, array(
			$transactionId
		));
		$config = Shopware()->Plugins()->Frontend()->SwagPaymentBillsafe()->Config();
		$sendStatusMail = (bool) $config->paymentStatusMail;
		
		$order = Shopware()->Modules()->Order();
        $order->setPaymentStatus($orderId, $paymentStatusId, $sendStatusMail);
	}
}