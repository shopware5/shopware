<?php
require_once(dirname(__FILE__).'/../../library/sofortLib.php');
require_once(dirname(__FILE__).'/../Helper/Helper.php');

/**
 *
 * Controller for handling payments via sofort multipay gateway
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version sofort 1.0  $Id: SofortOrders.php 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (f.dehn@sofort.com)
 * @package Shopware 4, sofort.com
 *
 */
class Shopware_Controllers_Backend_SofortOrders extends Enlight_Controller_Action {
	
	private $SofortLib_Multipay = null;
	
	private $configKey = '';
	
	private $paymentStatus = array();
	
	private $Snippets;
	
	private $ShopwareUpdateHelper = null;
	
	
	/**
	 * 
	 * Initiate this controller
	 */
	public function init() {
		$this->Snippets = Shopware()->Snippets();
		$sofortPendingState   = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->sofort_pending_state;
		$sofortConfirmedState = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->sofort_confirmed_state;
		$sofortCanceledState  = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->sofort_canceled_state;

		
		/**
		 * Mapping the statuses of SOFORT AG
		 * @var array
		 */
		$this->paymentStatus = array(
			'pending'		   => $sofortPendingState,
			'wait_for_money'	=> $sofortPendingState,
			'confirm_invoice'   => $sofortPendingState,
			'not_credited_yet'  => $sofortConfirmedState,
			'wait_for_money'	=> $sofortConfirmedState,
			'credited'		  => $sofortConfirmedState,
			'canceled'		  => $sofortCanceledState,
			'refunded'		  => $sofortCanceledState,
			'loss'			  => $sofortCanceledState,
			'rejected'		  => $sofortPendingState,
		);
		
		$this->View()->addTemplateDir(dirname(__FILE__).'/../../Templates');
		$this->configKey = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->sofort_api_key;
		// API
		$this->SofortLib_Multipay = new SofortLib_Multipay($this->configKey);
		$this->ShopwareUpdateHelper = new ShopwareUpdateHelper(Shopware());
	}
	
	
	/**
	 * 
	 * Test the API by sending a request
	 */
	public function testApiAction() {
		   $this->Front()->Plugins()->ViewRenderer()->setNoRender();
			// API
			$SofortLib_TransactionData = new SofortLib_TransactionData($this->Request()->apiKey);
			$SofortLib_TransactionData->setTransaction('00000')->sendRequest();
			
			if (!$SofortLib_TransactionData->isError()) {
				echo 1;
				return ;
			}
			echo 0;
		
	}
	
	
	/**
	 *
	 * Fetch all orders and provide a json-encoded array of order elements to shopware
	 * Sorting and showing only some orders (limit the sql-query) is made possible through some AJAX calls by ExtJS
	 */
	public function getSofortOrdersAction($search = false) {
		$this->View()->setTemplate();
		$start		  = $this->Request()->getParam('start');
		$limit		  = $this->Request()->getParam('limit');
		$sort		   = $this->Request()->getParam('sort');
		$dir			= $this->Request()->getParam('dir');
		$filter		 = $this->Request()->getParam('filter');
		$sort		   = (empty($sort)) ? 'o.ordertime' : $sort;
		$dir			= ($dir === 'ASC') ? 'ASC' : 'DESC';
		$filterCriteria = array();
		
		// filter criteria
		for ($i=0; $i<count($filter); $i++) {
			$filterCriteria[$i]['field'] = $filter[$i]['field'];
			$filterCriteria[$i]['data']['value'] = $filter[$i]['data']['value'];
		}
		
		$sqlWhere = '';
		
		foreach($filterCriteria as $singleCriteria) {
			if($singleCriteria['field'] == 'paymentDescription') {
				$filterValues = explode(',', $singleCriteria['data']['value']);
				$sqlWhere = ' AND ( s.paymentDescription LIKE ';
				$i = 0;
				
				foreach($filterValues as $filterValue) {
					if($i == 0) $sqlWhere .= '?';
					else $sqlWhere .= ' OR s.paymentDescription LIKE ?';
					$fields[] = $filterValue.'%';//utf8
					$i++;
				}
				
				$sqlWhere .= ' )';
			} elseif ($singleCriteria['field'] == 'cleared_description') {
				$filterValues = explode(',', $singleCriteria['data']['value']);
				$sqlWhere .= ' AND ( c.description LIKE ';
				$i = 0;
				
				foreach($filterValues as $filterValue) {
					if($i == 0) $sqlWhere .= '?';
					else $sqlWhere .= ' OR c.description LIKE ?';
					$fields[] = $filterValue.'%';//utf8
					$i++;
				}
				
				$sqlWhere .= ' )';
			}
		}
		
		if ($search !== false && $search != '') {
			$sqlWhere .= ' AND s.transactionId LIKE ? OR o.userID LIKE ? OR s.paymentMethod LIKE ? OR o.ordernumber LIKE ? OR s.paymentDescription LIKE ? OR o.invoice_amount LIKE ?';
			$fields[] = '%'.$search.'%';
			$fields[] = '%'.$search.'%';
			$fields[] = '%'.$search.'%';
			$fields[] = '%'.$search.'%';
			$fields[] = '%'.$search.'%';
			$fields[] = '%'.$search.'%';
		}
		
		if (empty($start) || empty($limit)) {
			$start = 0;
			$limit = 20;
		}
		// whitelist covering all possible columns
		$columns = array(
			'o.id' => 'orderID',
			'o.userId' => 'userId',
			'o.ordertime' => 'dateTime',
			's.paymentMethod' => 'paymentMethod',
			's.paymentDescription' => 'paymentDescription',
			's.transactionId' => 'transactionId',
			's.paymentStatus' => 'paymentStatus',
			'o.cleared' => 'cleared',
			'c.description' => 'cleared_description',
			'o.ordernumber' => 'ordernumber',
			'o.invoice_amount' => 'amount',
			'o.comment' => 'comment',
			'o.customercomment' => 'customercomment',
			'sis.status_id' => 'status_id',
			'si.comment' => 'internal_comment',
			'MAX(sis.date_modified)' => 'dateModified',
			'UNIX_TIMESTAMP(MAX(sis.date_modified))' => 'dateModifiedTimestamp',
		);
		$columnSql = '';
		foreach($columns as $key => $value) {
			$columnSql .= $key.' AS '.$value.', ';
		}
		
		// if requested column is not covered by whitelist, fall back to standard
		if(!in_array($sort, $columns)) {
			$sort = 'dateTime';
		}
		
		$columnSql = substr($columnSql, 0, -2);
		$sql = 'SELECT SQL_CALC_FOUND_ROWS '.$columnSql.'
				FROM sofort_orders s
				LEFT JOIN sofort_products si on si.transactionId = s.transactionId
				LEFT JOIN sofort_status sis on sis.sofort_product_id = si.id
				JOIN s_order o ON s.transactionId = o.transactionId
				JOIN s_core_states c ON o.cleared = c.id
				WHERE s.transactionId != "" '.$sqlWhere.' GROUP BY o.ordernumber ORDER BY '.$sort.' '.$dir.' LIMIT '.(int)$start.', '.(int)$limit.'
		';
		$orders = Shopware()->Db()->fetchAll($sql, $fields);
		$totalOrders = Shopware()->Db()->fetchOne('SELECT FOUND_ROWS()');
		$elements = array();
		$buttons = array();
		$confirmSr = $this->Snippets->getSnippet('sofort_multipay_backend')->get('button_confirm_sr'); //utf8
		$cancelSr = $this->Snippets->getSnippet('sofort_multipay_backend')->get('button_cancel_sr'); //utf8
		$printSr = $this->Snippets->getSnippet('sofort_multipay_backend')->get('button_download_sr'); //utf8
		$printSr2 = $this->Snippets->getSnippet('sofort_multipay_backend')->get('button_download_sr2'); //utf8
		
		$confirmSr = 'confirm'; //utf8
		$cancelSr = 'cancel'; //utf8
		$printSr = 'print1'; //utf8
		$printSr2 = 'print2'; //utf8
		
		$i = 0;
		$amount = 0;
		$cancelButton = array('title' => $cancelSr, 'action' => 'cancel');
		$confirmButton = array('title' => $confirmSr, 'action' => 'confirm');
		$printButton = array('title' => $printSr, 'action' => 'print');
		$printButton2 = array('title' => $printSr2, 'action' => 'print');
		
		foreach ($orders as $order) {
			$amount += $order['amount'];
			
			if ($order['paymentMethod'] == 'sofortrechnung_multipay') {
				if ($order['paymentStatus'] == 'confirm_invoice') {
					$buttons = array($confirmButton, $cancelButton);
				} elseif ($order['paymentStatus'] == 'not_credited_yet') {
					$buttons = array($cancelButton, $printButton);
				} elseif ($order['paymentStatus'] == 'refunded') {
					$buttons = array($printButton2);
				} elseif ($order['paymentStatus'] == 'credited') {
					$buttons = array($printButton2);
				} elseif ($order['paymentStatus'] == 'canceled') {
					$buttons = array();
				}
			}
			
			$elements[$i] = $order;
			$elements[$i]['dateTime'] = date('d.m.Y H:i:s', strtotime($elements[$i]['dateTime']));
			$elements[$i]['dateModified'] = date('d.m.Y H:i:s', strtotime($elements[$i]['dateModified']));
			$elements[$i]['paymentDescription'] = $order['paymentDescription'];//utf8
			$elements[$i]['comment'] = $order['comment'];//utf8
			$elements[$i]['internal_comment'] = nl2br($elements[$i]['internal_comment']);//utf8
			$elements[$i]['amount'] = $elements[$i]['amount'];//utf8
			$elements[$i]['cleared_description'] = $order['cleared_description'];//utf8
			//$elements[$i]['invoice_objection'] = $this->getInvoiceObjection($order['transactionId']);
			$elements[$i]['status'] = $this->getLatestStatus($order['transactionId']);
			
			if($order['paymentMethod'] == 'sofortrechnung_multipay' && !empty($buttons)) {
				$elements[$i]['actions'] = $buttons;	// set actions in addition
			}
			
			$i++;
		}
		
		echo json_encode(array('count' => $totalOrders, 'data' => $elements, 'amount' => number_format($amount, 2)));
	}
	
	
	/**
	 * 
	 * Get the invoice's objection
	 * @param string $transactionId
	 */
	private function getInvoiceObjection($transactionId) {
		$sql = 'SELECT DISTINCT invoice_objection FROM sofort_status
			JOIN sofort_products on sofort_products.id = sofort_status.sofort_product_id
			JOIN sofort_orders ON sofort_orders.transactionId = sofort_products.transactionId
			WHERE sofort_products.transactionId = ? AND sofort_status.invoice_objection NOT LIKE ""';
		$fields = array(
			$transactionId,
		);
		$objection = '';
		$objection = Shopware()->Db()->fetchOne($sql, $fields);
		
		if(!empty($objection)) {
			return $objection;
		};
		
		return $objection;
	}
	
	
	/**
	 * Fetch order details
	 * @param array $orderId
	 */
	private function getOrderDetails($orderId) {
		$sql = 'SELECT (s_order_details.quantity * s_order_details.price) AS sum, s_order.invoice_shipping, 
				s_order.invoice_shipping_net, s_order.currency, s_order.ordernumber, 
				s_order_details.articleordernumber, s_order_details.orderID, 
				s_order_details.articleID as articleId, s_order_details.quantity, s_order_details.name, 
				s_order_details.price, tax.tax, s_order.transactionId
				FROM s_order_details
				JOIN s_order on s_order.id = s_order_details.orderID
				JOIN s_core_tax tax on s_order_details.taxID = tax.id
				WHERE s_order.ordernumber = ?
				GROUP BY s_order_details.articleordernumber';
		$fields = array(
			$orderId,
		);
		$details = Shopware()->Db()->fetchAll($sql, $fields);
		$i = 0;
		
		foreach ($details as $detail) {
//			foreach($detail as $key => $value) {
//				$details[$i][$key] = utf8_encode($value);//utf8
//			}
			
			$detail['delete'] = false;	// add a delete flag to set later in extjs grid
			
			if ($detail['articleordernumber'] == $this->getSurchargeNumber()) {
				$details[$i]['articleId'] = 'swsurcharge';
				$details[$i]['productType'] = '2';
			} elseif ($detail['articleordernumber'] == $this->getDiscountNumber()) {
				$details[$i]['articleId'] = 'swdiscount';
				$details[$i]['productType'] = '2';
			} elseif ($detail['articleordernumber'] == $this->getPaymentDiscountNumber()) {
				$details[$i]['articleId'] = 'swpayment';
				$details[$i]['productType'] = '2';
			} elseif ($detail['articleordernumber'] == $this->getPaymentSurchargeNumber()) {
				$details[$i]['articleId'] = 'swpaymentabs';
				$details[$i]['productType'] = '2';
			} else {
				$details[$i]['productType'] = '0';
			}
			
			$details[$i]['netPrice'] = $this->ShopwareUpdateHelper->makeNetFromGross($detail['price'], $detail['tax']);
			
			$detail['name'] = html_entity_decode($detail['name']);
			$realArticleName = html_entity_decode($this->getArticleName($detail['articleId']), array('Gutschein'));
			
			if(empty($realArticleName)) {
				$realArticleName = $detail['name'];
			}
			
			if (strlen($realArticleName) != strlen($detail['name'])) {
				$realDescription = trim(substr($detail['name'], strlen($realArticleName), strlen($detail['name']) -1));
			} else {
				$realDescription = '';
			}
			
			$details[$i]['description'] = $realDescription;//utf8
			$details[$i]['name'] = $realArticleName;
			$i++;
		}
		
		// if shipping costs exist include them in basket
		if ($details[0]['invoice_shipping'] > 0 && $details[0]['invoice_shipping_net'] > 0) {
			$shippingTax = $details[0]['invoice_shipping'] / $details[0]['invoice_shipping_net'];
			$shippingTaxNet = $this->getTaxRateFromAbsoluteValue($shippingTax);
			
			$shipping = array(
				'sum' => number_format($details[0]['invoice_shipping'], 2), 
				'invoice_shipping' => $details[0]['invoice_shipping'],
				'invoice_shipping_net' => $details[0]['invoice_shipping_net'],
				'currency' => $details[0]['currency'], 
				'ordernumber' => $details[0]['ordernumber'],
				'articleordernumber' => 'shpmntvk',
				'orderId' => $details[0]['orderID'],
				'articleId' => 'shpmntvk', 
				'quantity' => "1", 
				'name' => $this->Snippets->getSnippet("sofort_multipay_checkout")->get("shipping_costs"), 
				'price' => $details[0]['invoice_shipping'],
				'tax' => $shippingTaxNet,
				'netPrice' => $this->ShopwareUpdateHelper->makeNetFromGross($details[0]['invoice_shipping'], $shippingTaxNet),
				'transactionId' => $details[0]['transactionId'],
				'delete' => false, 
				'description' => '',
				'productType' => '1', 
			);
			// include shipping (+ tax) in basket
			array_push($details, $shipping);
		}
		return $details;
	}
	
	
	/**
	 * 
	 * Get the order's status
	 * @param string $orderId
	 * @return Ambiguous
	 */
	private function getOrderStatus($orderId) {
		$sql = '
			SELECT st.status_id, st.status, st.status_reason, (select unix_timestamp(max(date_modified)) from sofort_status where sofort_product_id = sp.id) as dateModifiedTimestamp, st.invoice_status
			FROM sofort_status st
			JOIN sofort_products sp on st.sofort_product_id = sp.id
			WHERE sp.order_id = ?
			ORDER BY st.date_modified DESC
			LIMIT 1
		';
		$fields = array($orderId);
		$status = Shopware()->Db()->fetchAll($sql, $fields);
		return $status;
	}
	
	
	/**
	 * 
	 * Get the article name by specifying the article's ID, take other "special" articles into account (like "Gutschein")
	 * @param int $articleId
	 * @param array $specialArticles
	 */
	private function getArticleName($articleId, $specialArticles = array()) {
		
		$sql = 'SELECT name FROM s_articles WHERE id = ?';
		$articleName = Shopware()->Db()->fetchOne($sql, array($articleId));
		
		if(in_array('Gutschein', $articleName)) {
			$sql = 'SELECT description from `s_emarketing_vouchers` WHERE id = ?';
			$articleName = Shopware()->Db()->fetchOne($sql, array($articleId));
		}
		
		return $articleName;
	}
	
	
	/**
	 * 
	 * Get the absolute percentage value
	 * @param float $value
	 */
	function getTaxRateFromAbsoluteValue($value) {
		$value = number_format($value, 2);	// 1.19047619 -> 1.19
		$value = ($value - 1) * 100; // (1.19 -1) * 100 = 19
		return $value;
	}
	
	
	/**
	 * 
	 * Get the surcharge number
	 */
	private function getSurchargeNumber() {
		return Shopware()->Config()->surchargenumber;
	}
	
	
	/**
	 * 
	 * Get the discount number
	 */
	private function getDiscountNumber() {
		return Shopware()->Config()->discountNumber;
	}
	
	
	/**
	 * 
	 * Get the payment discount number
	 */
	private function getPaymentDiscountNumber() {
		return Shopware()->Config()->paymentSurchageNumber;
	}
	
	
	/**
	 * 
	 * Get the payment surcharge number
	 */
	private function getPaymentSurchargeNumber() {
		return Shopware()->Config()->paymentSurchargeAbsoluteNumber;
	}
	
	
	/**
	 * 
	 * Get the order's details
	 */
	public function getOrderDetailsAction() {
		// No Renderer verwenden
		$this->View()->loadTemplate('empty.tpl');
		$orderId = $this->Request()->orderId;
		echo json_encode($this->getOrderDetails($orderId));
	}
	
	
	/**
	 * 
	 * Get the order's status
	 */
	public function getOrderStatusAction() {
		$this->View()->loadTemplate('empty.tpl');
		$orderId = $this->Request()->orderId;
		echo json_encode($this->getOrderStatus($orderId));
	}
	
	
	/**
	 * 
	 * Get the order's history
	 */
	function getTransactionHistoryAction() {
		$transactionId = $this->Request()->transactionId;
		$this->View()->loadTemplate('empty.tpl');
		$sql = 'SELECT sofort_status.comment, sofort_status.date_modified FROM `sofort_status` 
				JOIN sofort_products on sofort_products.id = sofort_status.sofort_product_id
				WHERE sofort_products.transactionId = ? AND sofort_status.comment != "" ORDER BY sofort_status.date_modified DESC';
		$fields = array($transactionId);
		$items = Shopware()->Db()->fetchAll($sql, $fields);
		$historyItems = array();
		$i=0;
		foreach($items as $item) {
			$historyItems[$i]['comment'] = $item['comment']; //utf8
			$historyItems[$i]['date_modified'] = $item['date_modified'];
			$i++;
		}
		
		echo json_encode($historyItems);
	}
	
	
	/**
	 * 
	 * Get the latest status
	 * @param string $transactionId
	 */
	private function getLatestStatus($transactionId) {
		$sql = '
			SELECT s.status, s.status_reason, s.invoice_status, s.invoice_objection
			FROM sofort_status s
			JOIN sofort_products sp ON sp.id = s.sofort_product_id
			WHERE sp.transactionID = ?
			ORDER BY s.date_modified DESC LIMIT 0,1
		';
		$fields = array(
			$transactionId,
		);
		$status = Shopware()->Db()->fetchAll($sql, $fields);
		return $status;
	}
	
	
	/**
	 *
	 * Confirm an invoice
	 * @throws Exception
	 */
	public function confirmInvoiceAction() {
		$this->View()->loadTemplate('empty.tpl');
		// API
		$PnagInvoice = new PnagInvoice($this->configKey, $this->Request()->transactionId);
		$PnagInvoice->confirmInvoice();
		
		if (!$PnagInvoice->isError()) {
			$this->setPaymentStatus($PnagInvoice->transactionId, $PnagInvoice->getStatusReason());
			return true;
		} else {
			throw new Exception('Error occured while updating order');
			return $this->handleErrors($PnagInvoice);
		}
	}
	
	
	/**
	 * cancelation of an article
	 * @throws Exception
	 */
	public function cancelArticleInCartAction() {
	// set no renderer
		$this->View()->loadTemplate('empty.tpl');
		$transactionId = $this->Request()->transactionId;
		$itemId = $this->Request()->articleId;
		$articleOrderNumber = $this->Request()->articleOrderNumber;
		$articleName = $this->Request()->articleName;
		$quantity = $this->Request()->value;
		$unitPrice = $this->Request()->unitPrice;
		$tax = $this->Request()->tax;
		$PnagInvoice = new PnagInvoice($this->configKey, $this->Request()->transactionId);
		$PnagArticle = new PnagArticle($itemId, '', '', $articleName, '', 0, $unitPrice, $tax);
		$PnagInvoice->updateInvoiceItem($PnagArticle, $quantity, $unitPrice);
		
		if (!$PnagInvoice->isError()) {
			return true;
		} else {
			throw new Exception('Error occured while updating cart');
		}
		
	}
	
	
	/**
	 * 
	 * Edit Articles of an order
	 * @throws Exception
	 */
	public function editCartAction() {
	// set no renderer
		$this->View()->loadTemplate('empty.tpl');
		$articles = json_decode($this->Request()->articles);
		$comment = $this->Request()->comment;
		$transactionId = $articles[0];
		$PnagInvoice = new PnagInvoice($this->configKey, $transactionId);
		$PnagInvoice->setTransactionId($transactionId);
		$pnagArticles = array();
		
		for($i=1;$i<=count($articles);$i++) {
			if($articles[$i]->articleQuantity > 0) {
				array_push($pnagArticles, array(
					'itemId' => $articles[$i]->articleId.'|'.$articles[$i]->articleNumber, 
					'productNumber' => $articles[$i]->articleNumber, 
					'title' => $articles[$i]->articleTitle, 
					//'description' => $articles[$i]->articleTitle,	// TODO: Description
					'quantity' => $articles[$i]->articleQuantity, 
					'unitPrice' => number_format($articles[$i]->articlePrice, 2, '.', ''), 
					'tax' => number_format($articles[$i]->articleTax, 2, '.', '')));
			}
		}
		
		$PnagInvoice->updateInvoice($transactionId, $pnagArticles, $comment);
		if (!$PnagInvoice->isError()) {
			$PnagInvoice->refreshTransactionData();	// refresh transaction data
			$ShopwareUpdateHelper = new ShopwareUpdateHelper(Shopware());
			$ShopwareUpdateHelper->updateCart($PnagInvoice);
			$ShopwareUpdateHelper->updateTimeline($transactionId, $PnagInvoice->getInvoiceStatus(), $PnagInvoice->getStatus(), $PnagInvoice->getStatusReason(), $PnagInvoice->getStatusOfInvoice(), $PnagInvoice->getInvoiceObjection(), serialize($PnagInvoice->getItems()));
			$cartItemsEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('edit_invoice.CartItemsEdited');
			$newAmountEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.current_amount');
			$cartUpdatedWithNewAmount = $cartItemsEdited.' ('.$newAmountEdited.' '.$PnagInvoice->getAmount().' EUR)';
			$ShopwareUpdateHelper->setMerchantComment($transactionId, $cartUpdatedWithNewAmount);
			//$ShopwareUpdateHelper->setCustomerComment($transactionId, $cartEditedMessage.' ('.$PnagInvoice->getAmount().' EUR)');
			return true;
		} else {
			throw new Exception('Error occured while updating cart');
			return $this->handleErrors($PnagInvoice);
		}
	}
	
	
	/**
	 * Cancel an Invoice
	 * @throws Exception
	 */
	public function cancelInvoiceAction() {
		$this->View()->loadTemplate('empty.tpl');
		// API
		$PnagInvoice = new PnagInvoice($this->configKey, $this->Request()->transactionId);
		$PnagInvoice->cancelInvoice();
		
		if(!$PnagInvoice->isError()) {
			$this->setPaymentStatus($PnagInvoice->transactionId, $PnagInvoice->getStatusReason());
			return true;
		} else {
			throw new Exception('Error occured while updating order');
			return $this->handleErrors($PnagInvoice);
		}
	}
	
	
	/**
	 * Download an invoice
	 */
	public function getInvoiceAction() {
		$this->View()->loadTemplate('empty.tpl');
		// API
		$PnagInvoice = new PnagInvoice($this->configKey, $this->Request()->transactionId);
		
		if(!$PnagInvoice->isError()) {
			echo $PnagInvoice->getInvoiceUrl();
			return true;
		} else {
			throw new Exception('Error occured while downloading invoice');
			return $this->handleErrors($PnagInvoice);
		}
	}
	
	
	/**
	 * 
	 * Error handling
	 * @param PnagInvoice $PnagInvoice
	 */
	private function handleErrors(PnagInvoice $PnagInvoice) {
		$errors = $PnagInvoice->getErrors();
		return json_encode($errors);
	}
	
	
	/**
	 * Search something
	 */
	public function searchSomethingAction() {
		$this->View()->loadTemplate('empty.tpl');
		$this->getSofortOrdersAction($this->Request()->searchWord);
	}
	
	
	/**
	 * Set the status of a transaction
	 * @param Transaction-ID $transactionId
	 * @param Payment Status $status
	 */
	private function setPaymentStatus($transactionId, $status) {
		$sql = 'UPDATE `sofort_orders` SET paymentStatus = ? WHERE `transactionId` = ?';
		$fields = array(
			$status,
			$transactionId,
		);
		Shopware()->Db()->query($sql, $fields);
		$sql = 'UPDATE `s_order` SET cleared = ? WHERE `transactionId` = ?';
		$fields = array(
			$this->paymentStatus[$status],
			$transactionId,
		);
		Shopware()->Db()->query($sql, $fields);
		return true;
	}
	
	
	/**
	 * Index Action
	 */
	public function indexAction() {
		$this->View()->loadTemplate("orders.tpl");
		$this->View()->path = dirname(__FILE__);
	}
	
	
	/**
	 * Skeleton Action
	 */
	public function skeletonAction(){
		$this->View()->loadTemplate("skeleton.tpl");
	}
}