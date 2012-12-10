<?php
require_once(dirname(__FILE__).'/../../library/sofortLib.php');
require_once(dirname(__FILE__).'/..//Helper/Observable.php');
require_once(dirname(__FILE__).'/../Helper/Helper.php');
/**
 *
 * This controller provides a callback for notifications coming from sofort.com
 * It provides an entry point for notifications via XML
 *
 * $Date: 2012-07-20 10:45:37 +0200 (Fri, 20 Jul 2012) $
 * @version sofort 1.0  $Id: SofortNotification.php 4860 2012-07-20 08:45:37Z dehn $
 * @author SOFORT AG http://www.sofort.com (f.dehn@sofort.com)
 * @package Shopware 4, sofort.com
 *
 */
class Shopware_Controllers_Frontend_SofortNotification extends Shopware_Controllers_Frontend_Payment implements Observable {
	
	private $configKey = '';
	
	private $transactionId = '';
	
	private $time = '';
	
	private $SofortLib_TransactionData = null;
	
	private $SofortLib_Notification = null;
	
	private $Shopware = null;
	
	private $PnagInvoice = null;
	
	private $testMode = false;
	
	private $paymentStatus = array();
	
	private $paymentMethod = ''; // sr, sv, su ...
	
	private $paymentMethodString = ''; // Rechnung by sofort, Sofortüberweisung ...
	
	private $shopItems = array();
	
	private $amountOfInvoice = 0;
	
	private $orderIdentifier = 0;
	
	private $statusOfInvoice = 0;
	
	private $observers = array();
	
	private $ShopwareUpdateHelper = null;
	
	/**
	 * Language Snippets
	 * @var Object
	 */
	private $Snippets = null;
	
	/**
	 *
	 * initiate this controller
	 */
	public function init() {
		if (!$this->Shopware) {
			$this->setShopware(Shopware());
		}
		
		$this->ShopwareUpdateHelper = new ShopwareUpdateHelper(Shopware());
		$this->Snippets = $this->Shopware->Snippets();
		$this->View()->addTemplateDir(dirname(__FILE__).'/../../Templates');
		$this->configKey              = $this->Config()->sofort_api_key;
		$sofortPendingState           = $this->Config()->sofort_pending_state;
		$sofortConfirmedState         = $this->Config()->sofort_confirmed_state;
		$sofortCanceledState          = $this->Config()->sofort_canceled_state;
		$sofortPartiallyCreditedState = $this->Config()->sofort_partially_credited_state;
		
		if ($this->SofortLib_Notification === null) {
			$this->SofortLib_Notification = new SofortLib_Notification();
		}
		if ($this->SofortLib_TransactionData === null) {
			$this->SofortLib_TransactionData = new SofortLib_TransactionData($this->configKey);
		}
		
		/**
		 * Matching the many states given by Payment Network to the local states
		 */
		$this->paymentStatus = array(
			'pending' => $sofortPendingState,
			'wait_for_money' => $sofortPendingState,
			'confirm_invoice' => $sofortPendingState,
			'not_credited_yet' => $sofortConfirmedState,
			'partially_credited' => $sofortPartiallyCreditedState,
			'credited' => $sofortConfirmedState,
			'canceled' => $sofortCanceledState,
			'refunded' => $sofortCanceledState,
			'loss' => $sofortCanceledState,
			'rejected' => $sofortPendingState,
		);
	}
	
	/**
	 * 
	 * Set the test mode
	 * @param boolean $flag
	 */
	public function setTestMode($flag) {
		$this->testMode = $flag;
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
		if (in_array($Observer, $this->observers)) {
			$key = array_search($Observer, $this->observers);
			unset($this->observers[$key]);
		}
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Observable::notify()
	 */
	public function notify($key, $message = '') {
		if (count($this->observers) == 0) {
			return;
		}
		
		foreach ($this->observers as $observer) {
			$observer->update($key, $message, $this);
		}
	}
	
	
	/**
	 * 
	 * Setter for Snippets from Shopware
	 * @param object $snippets
	 */
	public function setSnippets($snippets) {
		$this->Snippets = $snippets;
	}
	
	
	/**
	 * 
	 * Setter for Shopware
	 * @param Shopware $instance
	 */
	public function setShopware(Shopware $instance) {
		$this->Shopware = $instance;
	}
	
	
	/**
	 * 
	 * Getter for Shopware instance
	 */
	public function getShopware() {
		return $this->Shopware;
	}
	
	
	/**
	 * 
	 * Getter for Database Object
	 */
	public function getDb() {
		return $this->Shopware->Db();
	}
	
	
	/**
	 * 
	 * Setter for Config Key
	 * @param string $configKey
	 */
	public function setConfigKey($configKey) {
		$this->configKey = $configKey;
	}
	
	
	/**
	 * 
	 * Setter for Transaction Data
	 * @param object $SofortLib_TransactionData
	 */
	public function setTransactionData($SofortLib_TransactionData) {
		$this->SofortLib_TransactionData = $SofortLib_TransactionData;
	}
	
	
	/**
	 * 
	 * Setter for SofortLib Notification
	 * @param object $SofortLib_Notification
	 */
	public function setSofortLibNotification($SofortLib_Notification) {
		$this->SofortLib_Notification = $SofortLib_Notification;
	}
	
	
	/**
	 * 
	 * Setter for PnagInvoice
	 * @param PnagInvoice $PnagInvoice
	 */
	public function setPnagInvoice(PnagInvoice $PnagInvoice) {
		$this->PnagInvoice = $PnagInvoice;
	}
	
	
	/**
	 * 
	 * Setter for TransactionId
	 * @param string $transactionId
	 */
	public function setTransactionId($transactionId) {
		$this->transactionId = $transactionId;
	}
	
	
	/**
	 * 
	 * Setter for Shop Items
	 * @param array $shopItems
	 */
	public function setShopItems($shopItems) {
		$this->shopItems = $shopItems;
	}
	
	
	/**
	 * 
	 * Getter for Payment Method
	 */
	public function getPaymentMethod() {
		return $this->paymentMethod;
	}
	
	
	/**
	 * 
	 * Getter for PnagInvoice
	 */
	public function getPnagInvoice() {
		return $this->PnagInvoice;
	}
	
	
	/**
	 * 
	 * Setter for Invoice Amoun
	 * @param float $amount
	 */
	public function setAmountOfInvoice($amount) {
		$this->amountOfInvoice = $amount;
	}
	
	
	/**
	 * 
	 * Setter for Order Identifier
	 * @param string $orderIdentifier
	 */
	public function setOrderIdentifier($orderIdentifier) {
		$this->orderIdentifier = $orderIdentifier;
	}
	
	
	/**
	 * 
	 * Setter for Invoice's Status
	 * @param int $statusOfInvoice
	 */
	public function setStatusOfInvoice($statusOfInvoice) {
		$this->statusOfInvoice = $statusOfInvoice;
	}
	
	
	/**
	 *
	 * This method is called if no additional action is specified in URL
	 */
	public function indexAction() {
		// fetch the unique payment id
		$uniqueId = $this->Request()->getParam('unique');
		
		// in case there is no valid request, just exit
		if (empty($uniqueId) || $this->getPayment($uniqueId) != 1) {
			header("HTTP/1.0 404 Not Found");
			exit();
		}
		
		$this->View()->loadTemplate('Frontend/forbidden.tpl');
		$this->transactionId = $this->SofortLib_Notification->getNotification();
		$this->time = $this->SofortLib_Notification->getTime();
		// fetch transaction details
		$this->SofortLib_TransactionData->setTransaction($this->transactionId)->sendRequest();
		// fetch payment method (su, sr, ...)
		$this->paymentMethod = $this->SofortLib_TransactionData->getPaymentMethod();
		
		// Save Order and send email to customer
		$this->saveOrder($this->transactionId, $uniqueId);
		switch($this->paymentMethod) {
			case 'sr':
				$this->paymentMethodString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sr_public_title');
				$this->setRechnungBySofort();
				break;
			case 'su':
				$this->paymentMethodString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_public_title');
				$this->setSofortUeberweisung();
				break;
			case 'sl':
				$this->paymentMethodString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sl_public_title');
				$this->setSofortLastschrift();
				break;
			case 'ls':
				$this->paymentMethodString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_ls_public_title');
				$this->setLastschriftBySofort();
				break;
			case 'sv':
				$this->paymentMethodString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_public_title');
				$this->setVorkasseBySofort();
				break;
		}
		return true;
	}
	
	
	/**
	 * Notification for Rechnung by sofort
	 * @throws Exception
	 */
	private function setRechnungBySofort() {
		$this->PnagInvoice = new PnagInvoice($this->configKey, $this->transactionId);
		$oldStatus = $this->getStatusOfInvoice();
		$newStatus = $this->PnagInvoice->getState();
		$oldAmount = $this->getAmountOfInvoice();
		$newAmount = $this->PnagInvoice->getAmount();
		// see if amount has changed -> indicates an updated cart
		$amountChanged = ($oldAmount != $newAmount) ? true : false;
		$newAmountHigher = ($newAmount > $oldAmount) ? true : false;
		$oldAmountHigher = ($oldAmount > $newAmount) ? true : false;
		$statusChanged = ($oldStatus != $newStatus) ? true : false;
		// update timeline everytime a notification comes in
		$this->ShopwareUpdateHelper->updateTimeline($this->transactionId, $this->PnagInvoice->getState(), $this->PnagInvoice->getStatus(), $this->PnagInvoice->getStatusReason(), $this->PnagInvoice->getStatusOfInvoice(), $this->PnagInvoice->getInvoiceObjection(), serialize($this->PnagInvoice->getItems()));
		// update address items if changed in payment wizard
		$this->updateOrderAddresses();
		// unittesting
		$this->notify('oldStatus', $oldStatus);
		$this->notify('newStatus', $newStatus);
		$this->notify('oldAmount', $oldAmount);
		$this->notify('newAmount', $newAmount);
		$this->notify('amountChanged', $amountChanged);
		
		// handle invoice states
		switch ($newStatus) {
			case PnagInvoice::PENDING_CONFIRM_INVOICE:	// Offene Rechnung, noch nicht bestätigt #1
				if ($amountChanged && $oldAmountHigher) {
					$cartItemsEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('edit_invoice.CartItemsEdited');
					$newAmountEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.current_amount');
					$cartUpdatedWithNewAmount = $cartItemsEdited.' ('.$newAmountEdited.' '.$newAmount.' EUR)';
					// Merchant has to be informed
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $cartUpdatedWithNewAmount);
					$this->ShopwareUpdateHelper->updateCart($this->PnagInvoice);
					return true;
				} elseif ($newAmountHigher) {
					$cartReset = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.edit_invoice.CartReset');
					$newAmountEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.current_amount');
					$cartUpdatedWithNewAmount = $cartReset.' ('.$newAmountEdited.' '.$newAmount.' EUR)';
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $cartUpdatedWithNewAmount);
					$this->ShopwareUpdateHelper->updateCart($this->PnagInvoice);
				}
				
				if ($statusChanged) {
					$this->setPaymentStatus($this->PnagInvoice->getStatusReason());
					$srConfirmInvoiceString = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.PendingConfirmInvoice');
					$notificationString = $srConfirmInvoiceString.' '.$this->transactionId;
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $notificationString);
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $notificationString);
				}
				
				break;
			case PnagInvoice::LOSS_CANCELED:	// Vollstorno #2
				$this->setPaymentStatus($this->PnagInvoice->getStatusReason());
				$srCanceledString = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sr_canceled');
				// Merchant and customer have to be informed
				$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $srCanceledString);
				$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $srCanceledString);
				$this->ShopwareUpdateHelper->restockCanceledOrder($this->transactionId);
				$this->setOrderStatusToCanceled();
				break;
			case PnagInvoice::LOSS_CONFIRMATION_PERIOD_EXPIRED:	// Zeitraum von 30 Tagen zur Bestätigung abgelaufen #3
				$srPeriodExpiredString = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.LossConfirmationPeriodExpired');
				// Merchant has to be informed
				$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $srPeriodExpiredString);
				$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $srPeriodExpiredString);
				$this->setPaymentStatus($this->paymentStatus['canceled']);
				break;
			case PnagInvoice::PENDING_NOT_CREDITED_YET_PENDING:	// Rechnung bestätigt, noch keine Zahlung eingegangen #4
				// if cart has been changed, update cart
				if ($amountChanged && $oldAmountHigher) {
					$cartItemsEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('edit_invoice.CartItemsEdited');
					$newAmountEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.current_amount');
					$cartUpdatedWithNewAmount = $cartItemsEdited.' ('.$newAmountEdited.' '.$newAmount.' EUR)';
					// Merchant has to be informed
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $cartUpdatedWithNewAmount);
					$this->ShopwareUpdateHelper->updateCart($this->PnagInvoice);
					return true;
				} elseif($newAmountHigher) {
					$cartReset = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.edit_invoice.CartReset');
					$newAmountEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.current_amount');
					$cartUpdatedWithNewAmount = $cartReset.' ('.$newAmountEdited.' '.$newAmount.' EUR)';
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $cartUpdatedWithNewAmount);
					$this->ShopwareUpdateHelper->updateCart($this->PnagInvoice);
				}
				
				if ($statusChanged) {
					$this->setPaymentStatus($this->PnagInvoice->getStatusReason());
					$transactionStatus = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.ConfirmedWaitingForMoney');
					$invoiceStatus = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.ConfirmedWaitingForMoney');
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $transactionStatus);
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $invoiceStatus);
				}
				
				break;
				
			case PnagInvoice::REFUNDED_REFUNDED_REFUNDED:	// Gutschrift erstellt. #14
					$this->setPaymentStatus($this->PnagInvoice->getStatusReason());
				$transactionStatus = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.MoneyRefunded');
				// Merchant and customer have to be informed
				$invoiceStatus = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.InvoiceRefunded');
				$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $transactionStatus, $invoiceStatus);
				$invoiceStatus = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.statusRefunded');
				$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $invoiceStatus);
				$this->ShopwareUpdateHelper->restockCanceledOrder($this->transactionId);
				$this->setOrderStatusToCanceled();
				break;
				
			case PnagInvoice::RECEIVED_CREDITED_RECEIVED:	// Zahlungseingang auf Händlerkonto #16
					// if cart has been changed, update cart
					if ($amountChanged && $oldAmountHigher) {
					$cartItemsEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('edit_invoice.CartItemsEdited');
					$newAmountEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.current_amount');
					$cartUpdatedWithNewAmount = $cartItemsEdited.' ('.$newAmountEdited.' '.$newAmount.' EUR)';
					// Merchant has to be informed
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $cartUpdatedWithNewAmount);
					$this->ShopwareUpdateHelper->updateCart($this->PnagInvoice);
					return true;
				} elseif($newAmountHigher) {
					$cartReset = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.edit_invoice.CartReset');
					$newAmountEdited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('admin.sr.current_amount');
					$cartUpdatedWithNewAmount = $cartReset.' ('.$newAmountEdited.' '.$newAmount.' EUR)';
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $cartUpdatedWithNewAmount);
					$this->ShopwareUpdateHelper->updateCart($this->PnagInvoice);
				}
				
				if ($statusChanged) {
					$transactionStatus = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_credited_to_seller');
					$invoiceStatus = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sr.ReceiptOfPayment');
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $transactionStatus, $invoiceStatus);
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $invoiceStatus);
				}
				
				break;
			default:
				/*
				 throw new Exception('Notification did not succeed');
				 header("HTTP/1.0 404 Not Found");
				 break;
				 */
		}
	}
	
	
	/**
	 * Notification for sofortüberweisung
	 * Status reason [not_credited_yet|not_credited]
	 * @throws Exception
	 */
	private function setSofortUeberweisung() {
		if ($this->SofortLib_TransactionData->isError()) {
			throw new Exception('Error - '.__FILE__.' - '.__LINE__);
			return false;
		} else {
			$status = $this->SofortLib_TransactionData->getStatus();
			$statusReason = $this->SofortLib_TransactionData->getStatusReason();
			$this->setPaymentStatus($statusReason);
			$this->ShopwareUpdateHelper->updateTimeline($this->transactionId, 0, $status, $statusReason, '', '', '');
			
			switch($statusReason) {
				case 'not_credited_yet':
					$successfulOrder = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_su_not_credited_yet');
					$successfulOrder = str_replace('{{transaction}}', $this->transactionId, $successfulOrder);
					$successfulOrder = str_replace('{{tId}}', '', $successfulOrder);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $successfulOrder);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $successfulOrder);
					break;
				case 'not_credited':
					
					if ($status === 'loss') {
						$loss = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_su_status_loss');
						$loss = str_replace('{{time}}', '', $loss);
						// Merchant and customer have to be informed
						$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $loss);
						$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $loss);
					} else {
						$notCredited = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_not_credited');
						// Merchant and customer have to be informed
						$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $notCredited);
						$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $notCredited);
					}
					break;
				case 'credited':
					$credited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sv.status_credited');
					$credited = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $credited);
					$credited = str_replace('{{time}}', '', $credited);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $credited);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $credited);
					break;
				case 'refunded':
					$refunded = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_refunded');
					$refunded = str_replace('{{time}}', '', $refunded);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $refunded);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $refunded);
					break;
				case 'consumer_protection':
					$cpCredited = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_customerprotection_credited');
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $cpCredited);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $cpCredited);
					break;
				case 'complaint':
					break;
				case 'default':
					/*
					 throw new Exception('Notification did not succeed');
					 header("HTTP/1.0 404 Not Found");
					 break;
					 */
			}
			
			return true;
		}
	}
	
	
	/**
	 * Notification for sofortvorkasse
	 * Status reason [wait_for_money|not_credited]
	 * @throws Exception
	 */
	private function setVorkasseBySofort() {
		if ($this->SofortLib_TransactionData->isError()) {
			throw new Exception('Error - '.__FILE__.' - '.__LINE__);
			return false;
		} else {
			$status = $this->SofortLib_TransactionData->getStatus();
			$statusReason = $this->SofortLib_TransactionData->getStatusReason();
			$this->setPaymentStatus($statusReason);
			$this->ShopwareUpdateHelper->updateTimeline($this->transactionId, 0, $status, $statusReason, '', '', '');
			
			switch($statusReason) {
				case 'wait_for_money':
					$successfulOrder = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_confirm_invoice2_vorkasse');
					$successfulOrder = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $successfulOrder);
					$successfulOrder = str_replace('{{time}}', '', $successfulOrder);
					$successfulOrder = str_replace('{{tId}}', '', $successfulOrder);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $successfulOrder.$this->transactionId);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $successfulOrder.$this->transactionId);
					break;
				case 'not_credited':
					
					if ($status === 'loss') {
						$loss = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_su_status_loss');
						$loss = str_replace('{{time}}', '', $loss);
						// Merchant and customer have to be informed
						$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $loss);
						$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $loss);
					} else {
						$notCredited = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_not_credited');
						// Merchant and customer have to be informed
						$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $notCredited);
						$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $notCredited);
					}
					break;
				case 'credited':
					$credited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sv.status_credited');
					$credited = str_replace('{{time}}', '', $credited);
					$credited = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $credited);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $credited);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $credited);
					break;
				case 'refunded':
					$refunded = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_refunded');
					$refunded = str_replace('{{time}}', '', $refunded);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $refunded);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $refunded);
					break;
				case 'partially_credited':
					$partially = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_partially_credited');
					$partially = str_replace('{{time}}', '', $partially);
					$partially = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $partially);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $partially);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $partially);
					break;
				case 'overpayment':
					$overpayment = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_received_overpayment');
					$overpayment = str_replace('{{time}}', '', $overpayment);
					$overpayment = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $overpayment);
					$overpayment = str_replace('{{received_amount}}', $this->SofortLib_TransactionData->getAmountReceived().' &euro;', $overpayment);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $overpayment);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $overpayment);
					break;
				case 'compensation':
					$compensation = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_refunded_compensation');
					$compensation = str_replace('{{time}}', '', $compensation);
					$compensation = str_replace('{{refunded_amount}}', $this->SofortLib_TransactionData->getAmountRefunded().' &euro;', $compensation);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $compensation);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $compensation);
					break;
				case 'complaint':
					break;
				case 'default':
					/*
					 throw new Exception('Notification did not succeed');
					 header("HTTP/1.0 404 Not Found");
					 break;
					 */
			}
			
			return true;
		}
	}
	
	
	/**
	 * Notification for sofortlastschrift
	 * Status reason [not_credited_yet|rejected]
	 * @throws Exception
	 */
	private function setSofortLastschrift() {
		if ($this->SofortLib_TransactionData->isError()) {
			throw new Exception('Error - '.__FILE__.' - '.__LINE__);
		} else {
			$status = $this->SofortLib_TransactionData->getStatus();
			$statusReason = $this->SofortLib_TransactionData->getStatusReason();
			$this->setPaymentStatus($statusReason);
			$this->ShopwareUpdateHelper->updateTimeline($this->transactionId, 0, $status, $statusReason, '', '', '');
			
			switch($statusReason) {
				case 'not_credited_yet':
					$successfulOrder = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_confirm_invoice2_vorkasse');
					$successfulOrder = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $successfulOrder);
					$successfulOrder = str_replace('{{time}}', '', $successfulOrder);
					$successfulOrder = str_replace('{{tId}}', '', $successfulOrder);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $successfulOrder.$this->transactionId);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $successfulOrder.$this->transactionId);
					break;
				case 'rejected':
					$snippet = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_debit_returned2');
					$snippet = str_replace('{{time}}', $this->time, $snippet);
					$returnDebit = $snippet;
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $returnDebit);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $returnDebit);
					break;
				case 'credited':
					$credited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sv.status_credited');
					$credited = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $credited);
					$credited = str_replace('{{time}}', '', $credited);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $credited);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $credited);
					break;
				case 'refunded':
					$refunded = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_refunded');
					$refunded = str_replace('{{time}}', '', $refunded);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $refunded);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $refunded);
					break;
				case 'compensation':
					break;
				case 'default':
					/*
					 throw new Exception('Notification did not succeed');
					 header("HTTP/1.0 404 Not Found");
					 break;
					 */
			}
			
			return true;
		}
	}
	
	
	/**
	 * Notification for Lastschrift by sofort
	 * Status reason [not_credited_yet|rejected]
	 * @throws Exception
	 */
	private function setLastschriftBySofort() {
		if ($this->SofortLib_TransactionData->isError()) {
			throw new Exception('Error - '.__FILE__.' - '.__LINE__);
		} else {
			$status = $this->SofortLib_TransactionData->getStatus();
			$statusReason = $this->SofortLib_TransactionData->getStatusReason();
			$this->setPaymentStatus($statusReason);
			$this->ShopwareUpdateHelper->updateTimeline($this->transactionId, 0, $status, $statusReason, '', '', array());
			
			switch($statusReason) {
				case 'not_credited_yet':
					$successfulOrder = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_confirm_invoice2_vorkasse');
					$successfulOrder = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $successfulOrder);
					$successfulOrder = str_replace('{{time}}', '', $successfulOrder);
					$successfulOrder = str_replace('{{tId}}', '', $successfulOrder);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $successfulOrder.$this->transactionId);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $successfulOrder.$this->transactionId);
					break;
				case 'rejected':
					$snippet = $this->Snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_debit_returned2');
					$snippet = str_replace('{{time}}', $this->time, $snippet);
					$returnDebit = $snippet;
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $returnDebit);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $returnDebit);
					break;
				case 'credited':
					$credited = $this->Snippets->getSnippet('sofort_multipay_backend')->get('sv.status_credited');
					$credited = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $credited);
					$credited = str_replace('{{time}}', '', $credited);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $credited);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $credited);
					break;
				case 'refunded':
					$refunded = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_refunded');
					$refunded = str_replace('{{time}}', '', $refunded);
					// Merchant and customer have to be informed
					$this->ShopwareUpdateHelper->setCustomerComment($this->transactionId, $refunded);
					$this->ShopwareUpdateHelper->setMerchantComment($this->transactionId, $refunded);
					break;
				case 'compensation':
					
					break;
				case 'default':
					/*
					 throw new Exception('Notification did not succeed');
					 header("HTTP/1.0 404 Not Found");
					 break;
					 */
			}
		}
	}
	
	
	/**
	 * Update both the tables "sofort_orders" and "s_order" of shopware to set the payment status accordingly
	 * @param unknown_type $status
	 */
	private function setPaymentStatus($status) {
		$sql = 'UPDATE `sofort_orders` SET paymentStatus = ? WHERE `transactionId` = ?';
		$fields = array(
				$status,
				$this->transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
		$sql = 'UPDATE `s_order` SET cleared = ? WHERE `transactionId` = ?';
		$fields = array(
				$this->paymentStatus[$status],
				$this->transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
		return true;
	}
	
	
	/**
	 * 
	 * Set the status to canceled (table s_order)
	 */
	private function setOrderStatusToCanceled() {
		$sql = 'UPDATE `s_order` SET status = 4 WHERE `transactionId` = ?';
		$fields = array(
				$this->transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Getter for Status of Invoice
	 */
	private function getStatusOfInvoice() {
		if ($this->testMode) {
			return $this->statusOfInvoice;
		}
		$sql = 'SELECT s.status_id FROM `sofort_status` s
		JOIN sofort_products p on p.id = s.sofort_product_id
		WHERE p.transactionId = ?
		ORDER BY s.date_modified DESC LIMIT 0,1';
		$fields = array(
				$this->transactionId,
		);
		return $this->Shopware->Db()->fetchOne($sql, $fields);
	}
	
	
	/**
	 * 
	 * Getter for Timeline of Invoice
	 */
	private function getTimelineOfInvoice() {
		$sql = 'SELECT sofort_status.status_id FROM `sofort_status`
		JOIN sofort_products on sofort_products.id = sofort_status.sofort_product_id
		WHERE sofort_products.transactionId = ?
		ORDER BY sofort_status.id DESC';
		$fields = array(
				$this->transactionId,
		);
		$invoiceTimeline = $this->Shopware->Db()->fetchAll($sql, $fields);
		return $invoiceTimeline;
	}
	
	
	/**
	 * 
	 * Getter for Invoice's amount
	 */
	private function getAmountOfInvoice() {
		if ($this->testMode) {
			return $this->amountOfInvoice;
		}
		$sql = 'SELECT `invoice_amount` FROM `s_order` WHERE `transactionId` = ?';
		$fields = array(
				$this->transactionId,
		);
		$amount = $this->Shopware->Db()->fetchOne($sql, $fields);
		return number_format($amount, 2);
	}
	
	
	/**
	 * 
	 * Getter for the Order Identifier
	 */
	private function getOrderIdentifier() {
		if ($this->testMode) {
			return $this->orderIdentifier;
		}
		$sql = 'SELECT `id`, `ordernumber` FROM `s_order` WHERE `transactionId` = ?';
		$fields = array(
				$this->transactionId,
		);
		$identifier = $this->Shopware->Db()->fetchAll($sql, $fields);
		return $identifier[0];	// $identifier[0][id] as return value?
	}
	
	
	/**
	 *
	 * Fetches the payment from DB
	 * @param unknown_type $transactionId
	 */
	private function getPayment($secret) {
		if ($this->testMode) return true;
		$sql = 'SELECT COUNT(transactionId) FROM `sofort_orders` WHERE `secret` = ?';
		$fields = array(
				$secret,
		);
		return $this->Shopware->Db()->fetchOne($sql, $fields);
	}
	
	
	/**
	 * 
	 * Update the order addresses
	 */
	private function updateOrderAddresses() {
		$transactionId = $this->SofortLib_TransactionData->getTransaction();
		$invoiceAddress = $this->SofortLib_TransactionData->getInvoiceAddress();
		$shippingAddress = $this->SofortLib_TransactionData->getShippingAddress();
		$invoiceSalutation = ($invoiceAddress['salutation'] == 2) ? 'mr' : 'mrs';
		$shippingSalutation = ($invoiceAddress['salutation'] == 2) ? 'mr' : 'mrs';
		
		$sql = 'SELECT userID FROM s_order WHERE transactionID = ?';
		$fields = array(
				$transactionId,
		);
		$userId = $this->Shopware->Db()->fetchOne($sql, $fields);
		
		$invoiceLastname = $invoiceAddress['lastname'];
		$shippingLastname = $shippingAddress['lastname'];
		
		/*
		 $sql = 'SELECT company FROM `s_user_billingaddress` WHERE userID = '.$userId;
		$invoiceCompany = $this->Shopware->Db()->fetchOne($sql);
		$sql = 'SELECT company FROM `s_user_shippingaddress` WHERE userID = '.$userId;
		$shippingCompany = $this->Shopware->Db()->fetchOne($sql);
		
		if (!empty($invoiceCompany) && strpos($invoiceLastname, $invoiceCompany) !== false) {
		// remove company name from lastname if present
		$invoiceLastname = substr($invoiceLastname, 0, strpos($invoiceLastname, ' - '.$invoiceCompany));
		}
		if (!empty($shippingCompany) && strpos($shippingLastname, $shippingCompany) !== false) {
		// remove company name from lastname if present
		$shippingLastname = substr($shippingLastname, 0, strpos($shippingLastname, ' - '.$shippingCompany));
		}
		*/
		
		$sql = 'UPDATE s_user_billingaddress
		SET salutation = ?,
		firstname = ?,
		lastname = ?,
		street = ?,
		streetnumber = ?,
		zipcode = ?,
		city = ?
		WHERE userID = ?
		';
		$fields = array(
				$invoiceSalutation,
				$invoiceAddress['firstname'],
				$invoiceLastname,
				$invoiceAddress['street'],
				$invoiceAddress['street_number'],
				$invoiceAddress['zipcode'],
				$invoiceAddress['city'],
				$userId,
		);
		$this->Shopware->Db()->query($sql, $fields);
		$sql = 'UPDATE s_user_shippingaddress
		SET salutation = ?,
		firstname = ?,
		lastname = ?,
		street = ?,
		streetnumber = ?,
		zipcode = ?,
		city = ?
		WHERE userID = ?
		';
		$fields = array(
				$shippingSalutation,
				$shippingAddress['firstname'],
				$shippingLastname,
				$shippingAddress['street'],
				$shippingAddress['street_number'],
				$shippingAddress['zipcode'],
				$shippingAddress['city'],
				$userId,
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Get the Tax Id by it's value
	 * @param float $value
	 */
	private function getTaxIdByValue($value) {
		$sql = 'SELECT id FROM s_core_tax WHERE tax = ? ';
		$fields = array(
				$value,
		);
		return $this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Get all differences between firstArray and secondArray
	 * @param array $firstArray
	 * @param array $secondArray
	 */
	private function getArticleDifferences($firstArray, $secondArray) {
		$firstProductKeys = $this->getProductKeysFromPnagArticle($firstArray);
		$secondProductKeys = $this->getProductKeysFromPnagArticle($secondArray);
		$differences = array_diff($firstProductKeys, $secondProductKeys);
		return $differences;
	}
	
	
	/**
	 * 
	 * Getter for Product Keys
	 * @param array $array
	 * @throws Exception
	 */
	function getProductKeysFromPnagArticle($array) {
		$productKeys = array();
		
		foreach ($array as $element) {
			array_push($productKeys, $element->product_number);
		}
		
		return $productKeys;
	}
	
	
	/**
	 * 
	 * Fetch all Shop Items
	 * @param string $orderId
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
		return $this->Shopware->Db()->fetchAll($sql, $fields);
	}
	
	
	/**
	 * 
	 * Getter for Order Items
	 * @param string $orderId
	 */
	private function getOrderItemsFromShopware($orderId) {
		if ($this->testMode === false) {
			// first, get structured data from shopware
			$shopItems = $this->fetchShopItems($orderId);
		} else {
			$shopItems = $this->shopItems;
		}
		
		// make some nice objects to have a little more convenient base to compare
		$pnagItems = array();
		$PnagArticle = null;
		
		foreach ($shopItems as $article) {
			$PnagArticle = new PnagArticle($article['id'], $article['articleordernumber'], 0, $article['name'], '', $article['quantity'], $article['price'], $article['tax']);
			array_push($pnagItems, $PnagArticle);
		}
		
		// some shipping costs as well
		if (!empty($shopItems[0]['shipping_costs'])) {
			$shippingSnippet = $this->Snippets->getSnippet("sofort_multipay_checkout")->get("shipping_costs");
			$shippingSnippetShort = 'shpmnt_vk';
			$PnagArticle = new PnagArticle($shopItems[0]['orderID'], $shippingSnippetShort, 0, $shippingSnippet, '', 1, $shopItems[0]['shipping_costs'], $shopItems[0]['tax']);
			array_push($pnagItems, $PnagArticle);
		}
		
		$this->notify('pnagItems', $pnagItems);
		return $pnagItems;
	}
	
	
	/**
	 *
	 * Returns the payment plugin config data.
	 * @return Shopware_Models_Plugin_Config
	 */
	public function Config() {
		return Shopware()->Plugins()->Frontend()->PaymentSofort()->Config();
	}
}