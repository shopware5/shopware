<?php
/**
 *
 * Data object that encapsulates article's data
 * $Date: 2012-09-05 14:27:56 +0200 (Mi, 05 Sep 2012) $
 * $ID$
 *
 */
class PnagArticle {
	
	public $itemId = '';
	
	public $productNumber = '';
	
	public $productType = '';
	
	public $title = '';
	
	public $description = '';
	
	public $quantity = '';
	
	public $unitPrice = '';
	
	public $tax = '';
	
	
	/**
	 * Constructor for PnagArticle
	 * @param $itemId int
	 * @param $productNumber string
	 * @param $productType string
	 * @param $title string
	 * @param $description string
	 * @param $quantity int
	 * @param $unitPrice float
	 * @param $tax float
	 */
	public function __construct($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax) {
		$this->itemId = $itemId;
		$this->productNumber = $productNumber;
		$this->productType = $productType;
		$this->title = $title;
		$this->description = $description;
		$this->quantity = $quantity;
		$this->unitPrice = $unitPrice;
		$this->tax = $tax;
	}
	
	
	/**
	 * 
	 * Getter for item id
	 */
	public function getItemId () {
		return $this->itemId;
	}
	
	
	/**
	 * 
	 * Getter for quantity
	 */
	public function getQuantity() {
		return $this->quantity;
	}
	
	
	/**
	 * 
	 * Setter for quantity
	 * @param int $quantity
	 */
	public function setQuantity($quantity) {
		$this->quantity = $quantity;
	}
	
	
	/**
	 * 
	 * Getter for unit price
	 */
	public function getUnitPrice() {
		return $this->unitPrice;
	}
	
	
	/**
	 * 
	 * Setter for unit price
	 * @param float $unitPrice
	 */
	public function setUnitPrice($unitPrice) {
		$this->unitPrice = $unitPrice;
	}
	
	
	/**
	 * 
	 * Getter for title
	 */
	public function getTitle() {
		return $this->title;
	}
	
	
	/**
	 * 
	 * Getter for tax value
	 */
	public function getTax() {
		return $this->tax;
	}
	
	
	/**
	 * 
	 * Setter for tax value
	 * @param float $value
	 */
	public function setTax($value) {
		$this->tax = $value;
	}
	
	
	/**
	 * 
	 * Setter for product number
	 * @param string $productNumber
	 */
	public function setProductNumber($productNumber) {
		$this->productNumber = $productNumber;
	}
	
	
	/**
	 * 
	 * Getter for product number
	 */
	public function getProductNumber() {
		return $this->productNumber;
	}
	
	
	/**
	 * 
	 * Setter for description
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	
	/**
	 * 
	 * Getter for description
	 */
	public function getDescription() {
		return $this->description;
	}
}
?>