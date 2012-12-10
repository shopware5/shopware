<?php
/**
 *
 * Data object that encapsulates user's data
 * $Date: 2012-09-05 14:27:56 +0200 (Mi, 05 Sep 2012) $
 * $ID$
 *
 */
class PnagCustomer {
	
	public $name = '';
	
	public $lastname = '';
	
	public $firstname = '';
	
	public $company = '';
	
	public $csID = '';
	
	public $vatId = '';
	
	public $shopId = '';
	
	public $Id = '';
	
	public $cIP = '';
	
	public $streetAddress = '';
	
	public $suburb = '';
	
	public $city = '';
	
	public $postcode = '';
	
	public $state = '';
	
	public $country = '';
	
	public $formatId = '';
	
	public $telephone = '';
	
	public $emailAddress = '';
	
	
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
	public function PnagCustomer($name = '', $lastname = '', $firstname = '', $company = '', $csID = '', $vatId = '', $shopId = '', $Id = '', $cIP = '', $streetAddress = '', $suburb = '', $city = '', $postcode = '', $state = '', $country = '', $formatId = '', $telephone = '', $emailAddress = '') {
		$this->name = $name;
		$this->lastname = $lastname;
		$this->firstname = $firstname;
		$this->company = $company;
		$this->csID = $csID;
		$this->vatId = $vatId;
		$this->shopId = $shopId;
		$this->Id = $Id;
		$this->cIP = $cIP;
		$this->street_address = $streetAddress;
		$this->suburb = $suburb;
		$this->city = $city;
		$this->postcode = $postcode;
		$this->state = $state;
		$this->country = $country;
		$this->formatId = $formatId;
		$this->telephone = $telephone;
		$this->emailAddress = $emailAddress;
	}
}
?>