<?php
/**
 *
 * This controller provides a callback for notifications coming from sofort.com
 * It provides an entry point for notifications via XML
 *
 * $Date: 2012-07-20 10:45:37 +0200 (Fri, 20 Jul 2012) $
 * @version sofort 1.0  $Id: Helper.php 4860 2012-07-20 08:45:37Z dehn $
 * @author SOFORT AG http://www.sofort.com (f.dehn@sofort.com)
 * @package Shopware 4, sofort.com
 *
 */
require_once(dirname(__FILE__).'/Observable.php');
class ShopwareUpdateHelper implements Observable{
	
	private $Shopware = null;
	
	private $Snippets = null;
	
	private $dateFormat = 'd.m.Y H:i:s';
	
	private $observers = array();
	
	/**
	 * 
	 * Constructor
	 * @param object $Shopware
	 */
	function __construct($Shopware) {
		$this->Shopware = $Shopware;
		$this->Snippets = $this->Shopware->Snippets();
	}
	
	
	/**
	 * 
	 * Update Timeline
	 * @param string $transactionId
	 * @param int $invoiceStatusId
	 * @param string $status
	 * @param string $statusReason
	 * @param string $invoiceStatus
	 * @param string $invoiceObjection
	 * @param array $items
	 */
	public function updateTimeline($transactionId, $invoiceStatusId, $status, $statusReason, $invoiceStatus, $invoiceObjection, $items = array()) {
		$sql = "INSERT INTO sofort_status
				(sofort_product_id, status_id, status, status_reason, invoice_status, invoice_objection, items)
				VALUES ((SELECT id FROM sofort_products WHERE transactionId = ?), ?, ?, ?, ?, ?, ?)
		";
		$fields = array(
			$transactionId,
			$invoiceStatusId,
			$status,
			$statusReason,
			$invoiceStatus,
			$invoiceObjection,
			$items,
		);
		$this->Shopware->Db()->query($sql, $fields);
		
		$sql = 'UPDATE sofort_orders set paymentStatus = ? WHERE transactionId = ?';
		return $this->Shopware->Db()->query($sql, array($statusReason, $transactionId));
	}
	
	
	/**
	 * 
	 * initiate order tables and status table (timeline)
	 * @param string $transactionId
	 * @param string $orderId
	 * @param string $paymentMethod
	 * @param float $amount
	 */
	public function initOrderTablesAndTimeLine($transactionId, $orderId, $paymentMethod, $amount) {
		
		switch($paymentMethod['name']) {
			case 'sofortueberweisung_multipay' :
			case 'su':
				$suPendingString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_pending');
				$this->initStatusAndProductTable($orderId, $transactionId, $amount, $suPendingString);
				break;
			case 'vorkassebysofort_multipay' :
			case 'sv':
				$svPendingString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_pending');
				$this->initStatusAndProductTable($orderId, $transactionId, $amount, $svPendingString);
				break;
			case 'sofortrechnung_multipay' :
			case 'sr':
				$srPendingString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sr_pending');
				$this->initStatusAndProductTable($orderId, $transactionId, $amount, $srPendingString);
				break;
			case 'sofortlastschrift_multipay' :
			case 'sl':
				$slPendingString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sl_pending');
				$this->initStatusAndProductTable($orderId, $transactionId, $amount, $slPendingString);
				break;
			case 'lastschriftbysofort_multipay' :
			case 'ls':
				$lsPendingString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_ls_pending');
				$this->initStatusAndProductTable($orderId, $transactionId, $amount, $lsPendingString);
				break;
			default:
				die('Order tables + timeline could not be updated in a correct manner: '.__CLASS__.' '.__LINE__);
		}
	}
	
	
	/**
	 * 
	 * Fetch the details from "s_core_paymentmeans" by short name like sr, su, ...
	 */
	public function getPaymentDetails($paymentMethod) {
		$sql = 'SELECT name, description, additionaldescription FROM `s_core_paymentmeans` WHERE `name` = ? ';
		$fields = array(
			$paymentMethod,
		);
		$paymentDetails = $this->Shopware->Db()->fetchAll($sql, $fields);
		return $paymentDetails[0];
	}
	
	
	/**
	 * 
	 * Clear cart contents
	 * @param int $userId
	 */
	private function clearCart($userId) {
		$sql = 'DELETE FROM s_order_basket WHERE userID = '.(int)$userId.' ';
		return $this->Shopware->Db()->query($sql);
	}
	
	
	/**
	 * 
	 * Save the cart contents by serializing
	 * @param string $orderNumber
	 * @param string $unique
	 * @param array $cartContents
	 * @param int $userData
	 * @param string $customerComment
	 * @param string $comment
	 */
	public function saveAndSerializeCartContents($orderNumber, $unique, $cartContents, $userData, $customerComment, $comment) {
		$sql = 'INSERT INTO sofort_temp_orders (ordernumber, order_time, secret, cart_content, user_data, customer_comment, comment) VALUES (?, ?, ?, ?, ?, ?, ?)';
		$fields = array(
			$orderNumber,
			date('Y-m-d H:i:s', time()),
			$unique,
			serialize($cartContents),
			serialize($userData),
			$customerComment,
			$comment,
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Initiate status and product table
	 * @param int $orderId
	 * @param string $transactionId
	 * @param float $amount
	 * @param string $comment
	 */
	private function initStatusAndProductTable($orderId, $transactionId, $amount, $comment) {
		$sql = "INSERT INTO `sofort_products`
				(`order_id`, `transactionId`, `amount`, `comment`)
					VALUES (?, ?, ?, ?);
		";
		$time = date($this->dateFormat, time());
		$fields = array(
					$orderId,
					$transactionId,
					$amount,
					$time.' - '.$comment,
				);
		$this->Shopware->Db()->query($sql, $fields);
		$productId = $this->Shopware->Db()->lastInsertId();
		$sql = 'INSERT INTO sofort_status
				(sofort_product_id, status_id, status, status_reason, invoice_status, invoice_objection)
				VALUES (?, ?, "empty", "empty", "empty", ?)
		';
		$invoiceStatus = ($this->paymentMethod['name'] == 'sofortrechnung_multipay') ? $this->PnagInvoice->getInvoiceStatus() : '';
		$invoiceItems = ($this->paymentMethod['name'] == 'sofortrechnung_multipay') ? serialize($this->PnagInvoice->getItems()) : '';
		$fields = array(
			$productId,
			$invoiceStatus,
			$invoiceItems,
		);
		$this->Shopware->Db()->query($sql, $fields);
		return true;
	}
	
	
	/**
	 * 
	 * Updates the cart existing in the shop. Takes an invoice object as a parameter and returns true|false
	 * @param PnagInvoice $PnagInvoice
	 */
	public function updateCart(PnagInvoice $PnagInvoice) {
		$transactionId = $PnagInvoice->getTransactionId();
		$orderIdentifier = $this->getOrderIdentifier($transactionId);
		$orderId = $orderIdentifier['ordernumber'];
		
		// normalize articleId itemId|articleNumber
		$PnagInvoice = $this->normalizeArticles($PnagInvoice);
		$boughtArticles = $this->fetchArticlesOfOrder($transactionId);
		
		// walk through every bought article and see what quantity has changed since the last update
		// set all stocks and seller statistics back accordingly
		foreach ($boughtArticles as $article) {
			foreach($PnagInvoice->getItems() as $invoiceItem) {
				if($invoiceItem->itemId == $article['articleID']) {
					if($invoiceItem->quantity > $article['quantity']) {
						$this->doRestockAndResetSoldArticle($article['articleID'], $invoiceItem->quantity);
					} elseif($invoiceItem->quantity < $article['quantity']) {
						$difference = $article['quantity'] - $invoiceItem->quantity;
						$this->doRestockAndResetSoldArticle($article['articleID'], $difference);
					} else {
						continue;
					}
				}
			}
		}
		
		// update and delete articles
		$this->updateSofortProductItems($orderId, $PnagInvoice, $transactionId);
		$this->updateOrder($orderId, $PnagInvoice, $transactionId);
		$this->updateShipping($orderId, $PnagInvoice, $transactionId);
		
		return $this->updateOrderDetails($orderId, $PnagInvoice);
	}
	
	
	/**
	 * 
	 * A wrapper for setting stock and statistics
	 * @param int $articleId
	 * @param number $quantity 
	 */
	private function doRestockAndResetSoldArticle($articleId, $quantity) {
		$this->restockArticle($articleId, $quantity);
		$this->resetSoldArticle($articleId, $quantity);
	}
	
	
	/**
	 * 
	 * Reset sales (how many times an article has been sold)
	 * @param string $articleId
	 * @param int $quantity
	 */
	private function resetSoldArticle($articleId, $quantity) {
		$sql = 'UPDATE s_articles_details SET sales = sales - ? WHERE articleID = ?';
		$fields = array(
			$quantity,
			$articleId,
		);
		return $this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Raise stock
	 * @param int $articleId
	 * @param int $quantity
	 */
	private function restockArticle($articleId, $quantity) {
		$sql = 'UPDATE s_articles_details SET instock = instock + ? WHERE articleID = ?';
		$fields = array(
			$quantity,
			$articleId,
		);
		return $this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Fetch all articles of an order by transaction id
	 * @param string $transactionId
	 */
	private function fetchArticlesOfOrder($transactionId) {
		$sql = 'SELECT od.articleID, od.quantity FROM `s_order` o
				JOIN s_order_details od ON o.id = od.orderID
				WHERE o.transactionID = ?';
		$fields = array(
			$transactionId,
		);
		return $this->Shopware->Db()->fetchAll($sql, $fields);
	}
	
	
	/**
	 * 
	 * Normalize articles (article id's may consist of many criterias seperated by pipes)
	 * @param object $PnagInvoice
	 */
	private function normalizeArticles($PnagInvoice) {
		$newItems = array();
		foreach($PnagInvoice->getItems() as $item) {
			$newItems[] = $item;
			$itemId = explode('|', $item->itemId);
			$item->itemId = $itemId[0];
		}
		$PnagInvoice->setItems($newItems);
		return $PnagInvoice;
	}
	
	
	/**
	 * 
	 * Get order id by transaction id
	 * @param string $transactionId
	 */
	private function getOrderIdentifier($transactionId) {
		$sql = 'SELECT `id`, `ordernumber` FROM `s_order` WHERE `transactionId` = ?';
		$fields = array(
			$transactionId,
		);
		$identifier = $this->Shopware->Db()->fetchAll($sql, $fields);
		return $identifier[0];
	}
	
	
	/**
	 * 
	 * set bought and returned articles back into stock
	 * @param string $transactionId
	 */
	public function restockCanceledOrder($transactionId) {
		$articlesToRestock = $this->fetchArticlesOfOrder($transactionId);
		
		if (is_array($articlesToRestock) && !empty($articlesToRestock)) {
			foreach($articlesToRestock as $article) {
				$this->doRestockAndResetSoldArticle($article['articleID'], $article['quantity']);
			}
		} else {
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * 
	 * update sofort_products, set new amount here
	 * @param unknown_type $orderId
	 * @param PnagInvoice $PnagInvoice
	 * @param unknown_type $transactionId
	 */
	private function updateSofortProductItems($orderId, PnagInvoice $PnagInvoice, $transactionId) {
		$sql = "UPDATE `sofort_products` SET `amount` = ? WHERE transactionId = ?";
		$fields = array(
			$PnagInvoice->getAmount(),
			$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * Update order, set quantity of items and them
	 * @param int $orderId
	 * @param PnagInvoice $PnagInvoice
	 */
	private function updateOrder($orderId, PnagInvoice $PnagInvoice, $transactionId) {
		$invoiceArticles = $PnagInvoice->getItems();
		$amountNet = 0;
		
		foreach ($invoiceArticles as $item) {
			$amountNet += $item->quantity * $item->unitPrice * 100 / ((100 + $item->tax));
		}
		
		$sql = 'UPDATE `s_order` SET `invoice_amount` = ?, `invoice_amount_net` =  ? WHERE `transactionID` = ?';
		$fields = array(
			$PnagInvoice->getAmount(),
			round($amountNet, 2),
			$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Update shipping
	 * @param int $orderId
	 * @param object $PnagInvoice
	 * @param string $transactionId
	 */
	private function updateShipping($orderId, $PnagInvoice, $transactionId) {
		$invoiceArticles = $PnagInvoice->getItems();
		$shippingNet = 0;
		$shipping = 0;
		
		foreach ($invoiceArticles as $item) {
			
			if($item->itemId == 'shpmntvk') {
				$shippingNet = $this->makeNetFromGross($item->unitPrice, $item->tax);
				$shipping = $item->unitPrice;
			}
			
		}
		
		$sql = 'UPDATE `s_order` SET `invoice_shipping` = ?, `invoice_shipping_net` = ? WHERE `transactionID` = ?';
		$fields = array(
			$shipping,
			$shippingNet,
			$transactionId
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Make net from gross
	 * @param float $value
	 * @param float $tax
	 * @param int $precision
	 */
	public function makeNetFromGross($value, $tax, $precision = 2) {
		$value = $value / (100 + $tax) * 100;
		return number_format($value, $precision);
	}
	
	
	/**
	 * 
	 * Get the latest state of invoice
	 * @param string $transactionId
	 */
	private function getLatestInvoiceState($transactionId) {
		$sql = "SELECT st.* FROM sofort_status st
			JOIN sofort_products pr on pr.id = st.sofort_product_id
			WHERE pr.transactionId = ?'
			order by st.date_modified DESC limit 0,1
		";
		$fields = array(
			$transactionId,
		);
		$actualState = $this->Shopware->Db()->fetchAll($sql, $fields);
		return $actualState[0];
	}
	
	
	/**
	 * 
	 * Update core table to new incoming values in case a cart/basket has been edited
	 * Handle shipping as well as any additional values
	 * @param int $orderId
	 * @param PnagInvoice $PnagInvoice
	 */
	private function updateOrderDetails($orderId, PnagInvoice $PnagInvoice) {
		$invoiceArticles = array();
		$newItems = array();
		$shopArticles = $this->getOrderItemsFromShopware($orderId);
		$invoiceArticles = $PnagInvoice->getItems();
		$this->synchInvoiceItems($orderId, $shopArticles, $invoiceArticles);
		$i = 0;
		
		foreach ($invoiceArticles as $item) {
			$itemId = $item->getItemId();
			$newItems[0]['id'] = (!empty($itemId)) ? $itemId : '';
			$newItems[0]['quantity'] = $item->getQuantity();
			$newItems[0]['price'] = $item->getUnitPrice();
			$newItems[0]['productNumber'] = $item->getProductNumber();
			
			if (!empty($itemId)) {
				$sql = 'UPDATE `s_order_details` SET `quantity` = ?, price = ? WHERE articleID = ? AND ordernumber = ? AND articleordernumber = ?';
				$fields = array(
					$newItems[0]['quantity'],
					$newItems[0]['price'],
					$itemId,
					$orderId,
					$newItems[0]['productNumber'],
				);
				$this->Shopware->Db()->query($sql, $fields);
			}
			
			$i++;
		}
		
		return true;
	}
	
	
	/**
	 * 
	 * For testing puroposes: test the synch functionality
	 * @param array $shopItems
	 * @param array $invoiceItems
	 */
	public function testSynchInvoiceItems($shopItems, $invoiceItems) {
		return $this->synchInvoiceItems('08154711', $shopItems, $invoiceItems);
	}
	
	
	/**
	 * 
	 * Add or remove articles by keeping the request's invoice in synch with shop's invoice
	 * @param int $orderId
	 * @param array $shopItems
	 * @param array $invoiceItems
	 * @return boolean
	 */
	private function synchInvoiceItems($orderId, $shopItems, $invoiceItems) {
		$missingItems = $this->getItemDifferences($shopItems, $invoiceItems);
		$this->notify('missingItems', $missingItems);
		$newItems = $this->getItemDifferences($invoiceItems, $shopItems);
		$shippingSnippet = 'shpmntvk';	// had to choose sth similar to shipment/versandkosten, there's no article representing shipment in database (s_order_details)
		
		// some items have been deleted
		if (!empty($missingItems)) {
			foreach ($missingItems as $item) {
				$sql = 'DELETE FROM s_order_details WHERE ordernumber = ? AND articleordernumber = ? ';
				$fields = array(
					$orderId,
					$item,
				);
				$this->Shopware->Db()->query($sql, $fields);
				
				if($item === $shippingSnippet) {
					$sql = 'UPDATE s_order SET invoice_shipping = 0, invoice_shipping_net = 0 WHERE ordernumber = ? ';
					$fields = array(
						$orderId,
					);
					$this->Shopware->Db()->query($sql, $fields);
				}
			}
			// some items were set back from external
		} elseif(!empty($newItems)) {
			foreach($invoiceItems as $item) {
				if($item->getProductNumber() === $shippingSnippet) {
					$sql = 'UPDATE s_order SET invoice_shipping = ?, invoice_shipping_net = ? WHERE ordernumber = ? ';
					$fields = array(
						$item->getUnitPrice(),
						number_format(($item->getUnitPrice() / (1+ $item->getTax() / 100)), 2),
						$orderId,
					);
					$this->Shopware->Db()->query($sql, $fields);
				} else {
					$modus = 0;
					
					if($item->getUnitPrice() == 0.00 && $item->getTax() == 0.00) {
						$modus = 1;
					}
					// if there ain't an article yet, add it to core table
					if(!$this->articleExists($orderId, $item->getItemId(), $item->getProductNumber())) {	
						$sql = 'INSERT INTO s_order_details 
								(orderID, ordernumber, articleID, articleordernumber, price, quantity, name, modus, taxID)
								VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
						';
						$fields = array(
							$this->getOrderIdByOrderNumber($orderId),
							$orderId,
							$item->getItemId(),
							utf8_decode($item->getProductNumber()),
							$item->getUnitPrice(),
							$item->getQuantity(),
							utf8_decode($item->getTitle()),
							$modus,
							$this->getTaxIdByValue((int)$item->getTax()),
						);
						$this->Shopware->Db()->query($sql, $fields);
					}
				}
				
			}
			
		}
		
		return true;
	}
	
	
	/**
	 * 
	 * Does an article exist within an order
	 * @param int $orderNumber
	 * @param int $articleId
	 * @param int $articleOrdernumber
	 */
	private function articleExists($orderNumber, $articleId, $articleOrdernumber) {
		$sql = 'SELECT COUNT(*) FROM s_order_details WHERE ordernumber = ? AND articleID = ? AND articleordernumber = ?';
		$fields = array(
			$orderNumber,
			$articleId,
			$articleOrdernumber,
		);
		return $this->Shopware->Db()->fetchOne($sql, $fields);
	}
	
	
	/**
	 * 
	 * Get order id by it's order number
	 * @param int $orderNumber
	 */
	private function getOrderIdByOrderNumber($orderNumber) {
		$sql = 'SELECT id FROM s_order WHERE ordernumber = ?';
		$fields = array(
			$orderNumber,
		);
		return $this->Shopware->Db()->fetchOne($sql, $fields);
	}
	
	
	/**
	 * 
	 * Get the tax id by value
	 * @param float $value
	 */
	private function getTaxIdByValue($value) {
		$sql = 'SELECT id FROM s_core_tax WHERE tax = ? ';
		$fields = array(
			$value,
		);
		return $this->Shopware->Db()->fetchOne($sql, $fields);
	}
	
	
	/**
	 * 
	 * Get the differences between two arrays
	 * @param array $firstArray
	 * @param array $secondArray
	 */
	private function getItemDifferences($firstArray, $secondArray) {
		$firstProductKeys = $this->getProductKeysFromItem($firstArray);
		$secondProductKeys = $this->getProductKeysFromItem($secondArray);
		$differences = array_diff($firstProductKeys, $secondProductKeys);
		return $differences;
	}
	
	
	/**
	 * 
	 * Get the product keys
	 * @param array $array
	 */
	function getProductKeysFromItem($array) {
		$productKeys = array();
		
		foreach ($array as $element) {
			array_push($productKeys, $element->productNumber);
		}
		
		return $productKeys;
	}
	
	
	/**
	 * 
	 * Get an order's items
	 * @param int $orderId
	 */
	private function getOrderItemsFromShopware($orderId) {
		$shopItems = $this->fetchShopItems($orderId);
		
		// make some nice objects to have a little more convenient base to compare
		$pnagItems = array();
		$PnagArticle = null;
		
		foreach ($shopItems as $article) {
			$PnagArticle = new PnagArticle($article['articleID'], utf8_encode($article['articleordernumber']), 0, utf8_encode($article['name']), '', $article['quantity'], number_format($article['price'], 2), number_format($article['tax'], 2));
			array_push($pnagItems, $PnagArticle);
		}
		
		// some shipping costs as well
		if (!empty($shopItems[0]['shipping_costs'])) {
			$shippingSnippet = $this->Snippets->getSnippet("sofort_multipay_checkout")->get("shipping_costs");
			$shippingSnippetShort = 'shpmntvk';
			$PnagArticle = new PnagArticle($shopItems[0]['orderID'], $shippingSnippetShort, 0, $shippingSnippet, '', 1, $shopItems[0]['shipping_costs'], $shopItems[0]['tax']);
			array_push($pnagItems, $PnagArticle);
		}
		
		return $pnagItems;
	}
	
	
	/**
	 * 
	 * Get the items of an order
	 * @param int $orderId
	 */
	private function fetchShopItems($orderId) {
		$sql = 'SELECT od.*, tax.tax,
			(SELECT invoice_shipping FROM s_order WHERE s_order.ordernumber = ?) as shipping_costs
			FROM s_order_details od
			JOIN s_order o on o.id = od.orderID
			JOIN s_core_tax tax on tax.id = od.taxID
			WHERE od.ordernumber = ?';
		$fields = array(
			$orderId,
			$orderId,
		);
		$elements = $this->Shopware->Db()->fetchAll($sql, $fields);
		return $elements;
	}
	
	
	/**
	 * 
	 * Set the comment for the merchant
	 * @param string $transactionId
	 * @param srring $transactionStatus
	 * @param string $invoiceStatus
	 * @param boolean $overwrite
	 */
	public function setMerchantComment($transactionId, $transactionStatus, $invoiceStatus = '', $overwrite = false) {
		
		if(empty($invoiceStatus)) {
			$comment = $transactionStatus;
		} else {
			$transactionStateLabel = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.InvoiceStateLabel');
			$invoiceStateLabel = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.transactionStateLabel');
			$comment = $transactionStateLabel.' '.$transactionStatus.' '.$invoiceStateLabel.' '.$invoiceStatus;
		}
		
		$this->setInternalComment($transactionId, $comment, $overwrite);
		$this->updateTimelineComment($transactionId, $comment);
	}
	
	
	/**
	 * 
	 * Set a comment to last updated status
	 * @param transactionId $transactionId
	 * @param string $comment
	 */
	private function updateTimelineComment($transactionId, $comment) {
		$sql = 'SELECT MAX(sofort_status.id) FROM sofort_status, sofort_products WHERE sofort_status.sofort_product_id = sofort_products.id AND sofort_products.transactionId = ?';
		$fields = array($transactionId);
		$lastStatusId = $this->Shopware->Db()->FetchOne($sql, $fields);
		$sql = 'UPDATE sofort_status  
				JOIN sofort_products on sofort_products.id = sofort_status.sofort_product_id
				SET sofort_status.comment = ?
				WHERE sofort_status.id = ?';
		$fields = array(
			$comment,
			$lastStatusId,
		);
		return $this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Set the customer's comment
	 * @param string $transactionId
	 * @param string $comment
	 * @param string $overwrite
	 */
	public function setCustomerComment($transactionId, $comment, $overwrite = false) {
		$this->setExternalComment($transactionId, $comment, $overwrite);
	}
	
	
	/**
	 * 
	 * Set the internal comment
	 * @param string $transactionId
	 * @param string $comment
	 * @param string $overwrite
	 */
	private function setInternalComment($transactionId, $comment, $overwrite = false) {
		$sql = 'SELECT `comment` FROM `sofort_products` WHERE `transactionId` = ?';
		$fields = array(
			$transactionId,
		);
		$oldComment = $this->Shopware->Db()->fetchOne($sql, $fields);
		$time = date($this->dateFormat, time());
		$comment = $time.' - '.$comment;
		$newComment = (!$overwrite) ? $oldComment."\n".$comment : $comment;
		$sql = 'UPDATE `sofort_products` SET `comment` = ?, `date_modified` = NOW() WHERE `transactionId` = ?';
		$fields = array(
			$newComment,
			$transactionId,
		);
		return $this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * Set the order's comment according to the message sent by notification
	 * @param string $transactionId
	 * @param string $comment
	 * @param boolean $overwrite overwrite the old comments, set the current one as new
	 */
	private function setExternalComment($transactionId, $comment, $overwrite = false) {
		$oldComment = $newComment = '';
		
		if(!$overwrite) { // if false, old comment is not being overwritten
			$sql = 'SELECT `comment` FROM `s_order` WHERE `transactionID` = ?';
			$fields = array(
				$this->transactionId,
			);
			$oldComment = $this->Shopware->Db()->FetchOne($sql, $fields);
		}
		
		$actDate = date($this->dateFormat, time());
		// add new comment at the end of the old one
		$newComment = (empty($oldComment)) ? $actDate.' - '.$comment: $oldComment.'<br />'.$actDate.' - '.$comment;
		$newComment = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $newComment);
		$newComment = str_replace('{{tId}}', '', $newComment);
		$newComment = str_replace('{{time}}', '', $newComment);
		// update the order comment
		$sql = 'UPDATE `s_order` SET `comment` = ? WHERE `transactionID` = ?';
		$fields = array(
			$newComment,
			$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * save the order
	 * @param string $transactionId
	 * @param int $orderNumber
	 * @param string $orderTime
	 * @param array $cartContents
	 * @param int $userData
	 * @param string $customerComment
	 */
	public function saveOrder($transactionId, $orderNumber, $orderTime, $cartContents, $userData, $customerComment) {
       
        if(empty($customerComment)) {
            $customerComment = '';
        }
        
        $sql = 'INSERT INTO s_order 
                (ordernumber, userID, invoice_amount, invoice_amount_net, invoice_shipping, 
                invoice_shipping_net, ordertime, status, cleared, paymentID, transactionID, 
                comment, customercomment, internalcomment, net, taxfree, partnerID, temporaryID, 
                referer, cleareddate, trackingcode, language, dispatchID, currency, 
                currencyFactor, subshopID, remote_addr)
                VALUES
                (?, ?, ?, ?, ?, ?, ? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,?);
        ';
        $comment = $internalComment = $net = $taxFree = $partnerId = $temporaryId = $referer = $clearedDate = $trackingCode = $language = $dispatchId = $currency = $currencyFactor = $subshopId = $oAttr1 = $oAttr2 = $oAttr3 = $oAttr4 = $oAttr5 = $oAttr6 = $remoteAddr = '';

        $amount = str_replace(',', '.', $cartContents['Amount']);
        $amountNet = str_replace(',', '.', $cartContents['AmountNet']);
        $shipping = str_replace(',', '.', $cartContents['sShippingcostsWithTax']);
        $shippingNet = str_replace(',', '.', $cartContents['sShippingcostsNet']);
        
        // add shipping if available
        $amount = $amount + $shipping;
        $amountNet = $amountNet + $shippingNet;
        
        $fields = array(
            'ordernumber'  =>$orderNumber,
            'userID'  =>$userData['billingaddress']['id'],
            'invoice_amount'  =>number_format($amount, 2),
            'invoice_amount_net'  =>number_format($amountNet, 2),
            'invoice_shipping' =>number_format($shipping, 2),
            'invoice_shipping_net' => number_format($shippingNet, 2),
            'ordertime' => $orderTime,
            'status' => 0,	// status
            'cleared' => 17,	// cleared
            'paymentID' => $userData['additional']['payment'][id],
            'transactionID' => $transactionId,
            'comment' => $comment,
            'customercomment' => $customerComment,
            'internalcomment' => $internalComment,
            'net' => $net,
            'taxfree' => $taxFree,
            'partnerID'=> $partnerId,
            'temporaryID' => $temporaryId,
            'referer' => $referer,
            'cleareddate' => $clearedDate,
            'trackingcode' => $trackingCode,
            'language' => $language,
            'dispatchID' => $dispatchId,
            'currency' => $currency,
            'currencyFactor' => 1, //$currencyFactor,
            'subshopID' => $subshopId,
            'remote_addr' => $remoteAddr,
        );

        $this->Shopware->Db()->query($sql, $fields);  
        $orderId = $this->Shopware->Db()->lastInsertId();
          
        // Save Attributes
        $sql = "INSERT INTO `s_order_attributes` (`orderID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`) VALUES (?,?,?,?,?,?,?,?) ";
        $emptyAttr = "";
        $oAtt = array($orderId, $emptyAttr, $emptyAttr, $emptyAttr, $emptyAttr, $emptyAttr, $emptyAttr);
        $this->Shopware->Db->query($sql, $oAtt); 

        
        $sql = 'INSERT INTO `s_order_details` 
                (`orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, 
                `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, 
                `taxID`, `config`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        foreach($cartContents['content'] as $item) {
            $fields = array(
                $orderId,
                $orderNumber,
                $item['articleID'],
                $item['ordernumber'],
                number_format(str_replace(',', '.', $item['price']), 2),
                $item['quantity'],
                $item['articlename'],
                0,
                0,
                0,
                '',
                $item['modus'],
                0,
                $item['taxID'],
                $item['config']
            );
            $this->Shopware->Db()->query($sql, $fields);
            
            /// Order detail attributes 
            $orderDetailId = $this->Shopware->Db()->lastInsertId();
            $detailInsertSql = "INSERT INTO `s_order_details_attributes` (`detailID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`) VALUES (?,?,?,?,?,?,?,?) ";
            $detailData = array($orderDetailId,$item['ob_attr1'], $item['ob_attr2'], $item['ob_attr3'], $item['ob_attr4'], $item['ob_attr5'],  $item['ob_attr6']);
            $this->Shopware->Db()->query($detailInsertSql, $detailData);
        }
        
        // do some clean up 
        $sql = 'DELETE FROM s_order WHERE paymentID = ? AND ordernumber = 0';
        $fields = array(
            $userData['additional']['payment'][id],
        );
        $this->Shopware->Db()->query($sql, $fields);
        
    }
	
	
	/**
	* (non-PHPdoc)
	* @see Observable::attach()
	*/
	public function attach(Observer $Observer) {
		array_push($this->observers, $Observer);
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Observable::detach()
	 */
	public function detach(Observer $Observer) {
		if(in_array($Observer, $this->observers)) {
			$key = array_search($Observer, $this->observers);
			unset($this->observers[$key]);
		}
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Observable::notify()
	 */
	public function notify($key, $message = '') {
		if(count($this->observers) == 0) {
			return;
		}
		
		foreach ($this->observers as $observer) {
			$observer->update($key, $message, $this);
		}
	}
}