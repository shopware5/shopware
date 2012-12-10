<?php
/// \cond
require_once('pnag_article.php');
require_once('pnag_customer.php');

require_once('pnag_article.php');
require_once('pnag_article.php');
/**
 * Copyright (c) 2012 SOFORT AG
 *
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 11:34:40 +0100 (Fr, 23. Nov 2012) $
 * @version SofortLib 1.5.4  $Id: class.abstract_document.inc.php 5748 2012-11-23 10:34:40Z Niehoff $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 * @internal
 *
 */
class PnagAbstractDocument {
	
	/**
	 * Holds all items associated with this kind of document (instance might be invoice, bank transfer, ...)
	 * @var array
	 */
	protected $_items = array();
	
	/**
	 *
	 * Holds the instance of PnagCustomer associated with this kind of document
	 * @var object
	 */
	protected $_customer = null;
	
	/**
	 *
	 * Holds the currency associated with this kind of document
	 * @var String
	 */
	protected $_currency = 'EUR';
	
	/**
	 * Holds the amount/total of this kind of document
	 * @var float
	 */
	protected $_amount = 0.00;
	
	/**
	 *
	 * Holds the refunded amount/total
	 * @var float
	 */
	protected $_amountRefunded = 0.00;
	
	
	/**
	 * puts the given article into $this->_items
	 * should only be used for the articles from the shopsystem
	 * @todo change VAT according to legislation
	 */
	public function setItem($itemId, $productNumber = 0, $productType = 0, $title = '', $description = '', $quantity = 0, $unitPrice = '', $tax = '19') {
		array_push($this->_items, new PnagArticle($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax));
		return $this;
	}
	
	
	/**
	 * Getter for items
	 * @return array $this->_items
	 */
	public function getItems() {
		return $this->_items;
	}
	
	
	/**
	 * searches in the before given shoparticles for the highest tax and returns it
	 * @return int/float - highest found taxvalue e.g. 0 or 7 or 19...
	 */
	public function getHighestShoparticleTax() {
		$highestTax = 0;
		
		foreach ($this->_items as $item) {
			if ($item->getTax() > $highestTax) {
				$highestTax = $item->getTax();
			}
		}
		
		return $highestTax;
	}
	
	
	/**
	 * Set the customer's credentials
	 * @param $name	string
	 * @param $lastname string
	 * @param $firstname string
	 * @param $company string
	 * @param $csID string customer id in shop
	 * @param $vatId string - customer's VAT ID
	 * @param $shopId - shop's ID
	 * @param $Id
	 * @param $cIP
	 * @param $streetAddress string
	 * @param $suburb string
	 * @param $city string
	 * @param $postcode string
	 * @param $state string
	 * @param $country	string
	 * @param $formatId string
	 * @param $telephone string
	 * @param $emailAddress string
	 */
	public function setCustomer($name = '', $lastname = '', $firstname = '', $company = '', $csID = '', $vatId = '', $shopId = '', $Id = '', $cIP = '', $streetAddress = '', $suburb = '', $city = '', $postcode = '', $state = '', $country = '', $formatId = '', $telephone = '', $emailAddress = '') {
		$this->_customer = new PnagCustomer($name, $lastname, $firstname, $company, $csID, $vatId, $shopId, $Id, $cIP, $streetAddress, $suburb, $city, $postcode, $state, $country, $formatId, $telephone, $emailAddress);
		return $this;
	}
	
	
	/**
	 *
	 * Setter for currency
	 * @param $currency string
	 */
	public function setCurrency($currency) {
		$this->_currency = $currency;
		return $this;
	}
	
	
	/**
	 * Calculate the total amount
	 * @private
	 * @return $object
	 */
	private function _calcAmount() {
		$this->_amount = 0.0;
		foreach($this->_items as $item) {
			$this->_amount += $item->unitPrice * $item->quantity;
		}
		return $this;
	}
	
	
	/**
	 * get the total amount
	 */
	public function getAmount() {
		return $this->_amount;
	}
}
/// \endcond
?>