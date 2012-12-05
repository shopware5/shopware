<?php
/**
 *  Copyright 2010 KLARNA AB. All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without modification, are
 *  permitted provided that the following conditions are met:
 *
 *     1. Redistributions of source code must retain the above copyright notice, this list of
 *        conditions and the following disclaimer.
 *
 *     2. Redistributions in binary form must reproduce the above copyright notice, this list
 *        of conditions and the following disclaimer in the documentation and/or other materials
 *        provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY KLARNA AB "AS IS" AND ANY EXPRESS OR IMPLIED
 *  WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 *  FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KLARNA AB OR
 *  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 *  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 *  ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  The views and conclusions contained in the software and documentation are those of the
 *  authors and should not be interpreted as representing official policies, either expressed
 *  or implied, of KLARNA AB.
 *
 * @package KlarnaAPI
 */

/**
 * KlarnaAddr is an object of convenience, to parse and create addresses.
 *
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
class KlarnaAddr {

    /**
     * Email address.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $email;

    /**
     * Phone number.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $telno;

    /**
     * Cellphone number.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $cellno;

    /**
     * First name.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $fname;

    /**
     * Last name.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $lname;

    /**
     * Company name.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $company;

    /**
     * Care of, C/O.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $careof;

    /**
     * Street address.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $street;

    /**
     * Zip code.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $zip;

    /**
     * City.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $city;

    /**
     * {@link KlarnaCountry} constant.<br>
     *
     * @ignore Do not show this in PHPDoc.
     * @var int
     */
    protected $country;

    /**
     * House number.<br>
     * Only for NL and DE!<br>
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $houseNo;

    /**
     * House extension.<br>
     * Only for NL!<br>
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $houseExt;

    /**
     * When using {@link Klarna::getAddresses()} this might be guessed depending on type used.<br>
     *
     * Signifies if address is for a company or a private person.<br>
     * If isCompany is null, then it is unknown and will be assumed to be a private person.<br>
     *
     * <b>Note</b>:<br>
     * This has no effect on transmitted data.<br>
     *
     * @var bool|null
     */
    public $isCompany = null;

    /**
     * Class constructor.
     *
     * Calls the set methods for all arguments.
     *
     * @param  string      $email     Email address.
     * @param  string      $telno     Phone number.
     * @param  string      $cellno    Cellphone number.
     * @param  string      $fname     First name.
     * @param  string      $lname     Last name.
     * @param  string      $careof    Care of, C/O.
     * @param  string      $street    Street address.
     * @param  string      $zip       Zip code.
     * @param  string      $city      City.
     * @param  string|int  $country   {@link KlarnaCountry} constant or two letter code.
     * @param  string      $houseNo   House number, only used in DE and NL.
     * @param  string      $houseExt  House extension, only used in NL.
     * @throws KlarnaException
     */
    public function __construct($email = null, $telno = null, $cellno = null, $fname = null, $lname = null,
            $careof = "", $street = null, $zip = null, $city = null, $country = null, $houseNo = "", $houseExt = "") {

        //Set all string values to ""
        $this->company = "";
        $this->telno = "";
        $this->careof = "";
        $this->cellno = "";
        $this->city = "";
        $this->email = "";
        $this->fname = "";
        $this->lname = "";
        $this->zip = "";

        if($email !== null) {
            $this->setEmail($email);
        }
        if($telno !== null) {
            $this->setTelno($telno);
        }
        if($cellno !== null) {
            $this->setCellno($cellno);
        }
        if($fname !== null) {
            $this->setFirstName($fname);
        }
        if($lname !== null) {
            $this->setLastName($lname);
        }
        $this->setCareof($careof);
        if($street !== null) {
            $this->setStreet($street);
        }
        if($zip !== null) {
            $this->setZipCode($zip);
        }
        if($city !== null) {
            $this->setCity($city);
        }
        if($country !== null) {
            $this->setCountry($country);
        }
        $this->setHouseNumber($houseNo);
        $this->setHouseExt($houseExt);
    }

    /**
     * Returns the email address.
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Sets the email address.
     *
     * @param  string $email
     * @return void
     */
    public function setEmail($email) {
        if(!is_string($email)) {
            $email = strval($email);
        }
        if(strlen($email) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ': Email address not specified!');
        }
        $this->email = $email;
    }

    /**
     * Returns the phone number.
     *
     * @return string
     */
    public function getTelno() {
        return $this->telno;
    }

    /**
     * Sets the phone number.
     *
     * @param  string $telno
     * @return void
     */
    public function setTelno($telno) {
        if(!is_string($telno)) {
            $telno = strval($telno);
        }
        $this->telno = $telno;
    }

    /**
     * Returns the cellphone number.
     *
     * @return string
     */
    public function getCellno() {
        return $this->cellno;
    }

    /**
     * Sets the cellphone number.
     *
     * @param  string $cellno
     * @return void
     */
    public function setCellno($cellno) {
        if(!is_string($cellno)) {
            $cellno = strval($cellno);
        }
        $this->cellno = $cellno;
    }

    /**
     * Returns the first name.
     *
     * @return string
     */
    public function getFirstName() {
        return $this->fname;
    }

    /**
     * Sets the first name.
     *
     * @param  string  $fname
     * @return void
     */
    public function setFirstName($fname) {
        if(!is_string($fname)) {
            $fname = strval($fname);
        }
        if(strlen($fname) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ': First name not specified!');
        }
        $this->fname = $fname;
    }

    /**
     * Returns the last name.
     *
     * @return string
     */
    public function getLastName() {
        return $this->lname;
    }

    /**
     * Sets the last name.
     *
     * @param  string  $lname
     * @return void
     */
    public function setLastName($lname) {
        if(!is_string($lname)) {
            $lname = strval($lname);
        }
        if(strlen($lname) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ': Last name not specified!');
        }
        $this->lname = $lname;
    }

    /**
     * Returns the company name.
     *
     * @return string
     */
    public function getCompanyName() {
        return $this->company;
    }

    /**
     * Sets the company name.<br>
     * If the purchase results in a company purchase, reference person will be used from first and last name,<br>
     * or the value set with {@link Klarna::setReference()}.<br>
     *
     * @see Klarna::setReference
     * @param  string  $company
     * @return void
     */
    public function setCompanyName($company) {
        if(!is_string($company)) {
            $company = strval($company);
        }
        if(strlen($company) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ': Company name not specified!');
        }
        $this->company = $company;
    }

    /**
     * Returns the care of, C/O.
     *
     * @return string
     */
    public function getCareof() {
        return $this->careof;
    }

    /**
     * Sets the care of, C/O.
     *
     * @param  string  $careof
     * @return void
     */
    public function setCareof($careof) {
        if(!is_string($careof)) {
            $careof = strval($careof);
        }
        $this->careof = $careof;
    }

    /**
     * Returns the street address.
     *
     * @return string
     */
    public function getStreet() {
        return $this->street;
    }

    /**
     * Sets the street address.
     *
     * @param  string  $street
     * @return void
     */
    public function setStreet($street) {
        if(!is_string($street)) {
            $street = strval($street);
        }
        if(strlen($street) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ': Street address not specified!');
        }
        $this->street = $street;
    }

    /**
     * Returns the zip code.
     *
     * @return string
     */
    public function getZipCode() {
        return $this->zip;
    }

    /**
     * Sets the zip code.
     *
     * @param  string  $zip
     * @return void
     */
    public function setZipCode($zip) {
        if(is_string($zip)) {
            $zip = str_replace(' ', '', $zip); //remove spaces
        }
        else {
            $zip = strval($zip);
        }
        if(strlen($zip) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ': Zip code not specified!');
        }
        $this->zip = $zip;
    }

    /**
     * Returns the city.
     *
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Sets the city.
     *
     * @param  string  $city
     * @return void
     */
    public function setCity($city) {
        if(!is_string($city)) {
            $city = strval($city);
        }
        if(strlen($city) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ': City not specified!');
        }
        $this->city = $city;
    }

    /**
     * Returns the country as a integer constant.
     *
     * @return int {@link KlarnaCountry}
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Returns the country as a two letter representation.
     *
     * @throws KlarnaException
     * @return string  E.g. 'de', 'dk', ...
     */
    public function getCountryCode() {
        switch($this->country) {
            case KlarnaCountry::DE:
                return 'de';
            case KlarnaCountry::DK:
                return 'dk';
            case KlarnaCountry::FI:
                return 'fi';
            case KlarnaCountry::NL:
                return 'nl';
            case KlarnaCountry::NO:
                return 'no';
            case KlarnaCountry::SE:
                return 'se';
            default:
                throw new KlarnaException('Error in' . __METHOD__ . ': Unknown country! ('.$this->country.')');
        }
    }

    /**
     * Sets the country, use either a two letter representation or the integer constant.
     *
     * @param  int  $country {@link KlarnaCountry}
     * @throws KlarnaException
     * @return void
     */
    public function setCountry($country) {
        if(!is_numeric($country) && strlen($country) == 2) {
            $this->setCountry(Klarna::getCountryForCode($country));
        }
        else {
            if(!isset($country)) {
                throw new KlarnaException("Error in " . __METHOD__ . ": Country is not set!");
            }
            if(is_numeric($country) && !is_int($country)) {
                $country = intval($country);
            }
            if(!is_numeric($country) || !is_int($country)) {
                throw new KlarnaException("Error in " . __METHOD__ . ": Country not an integer! ($country)");
            }
            $this->country = $country;
        }
    }

    /**
     * Returns the house number.<br>
     * Only used in Germany and Netherlands.<br>
     *
     * @return string
     */
    public function getHouseNumber() {
        return $this->houseNo;
    }

    /**
     * Sets the house number.<br>
     * Only used in Germany and Netherlands.<br>
     *
     * @param  string  $houseNo
     * @return void
     */
    public function setHouseNumber($houseNo) {
        if(!is_string($houseNo)) {
            $houseNo = strval($houseNo);
        }
        $this->houseNo = $houseNo;
    }

    /**
     * Returns the house extension.<br>
     * Only used in Netherlands.<br>
     *
     * @return string
     */
    public function getHouseExt() {
        return $this->houseExt;
    }

    /**
     * Sets the house extension.<br>
     * Only used in Netherlands.<br>
     *
     * @param  string  $houseExt
     * @return void
     */
    public function setHouseExt($houseExt) {
        if(!is_string($houseExt)) {
            $houseExt = strval($houseExt);
        }
        $this->houseExt = $houseExt;
    }

    /**
     * Returns an associative array representing this object.
     *
     * @return array
     */
    public function toArray() {
        return array(
            'email'           => $this->getEmail(),
            'telno'           => $this->getTelno(),
            'cellno'          => $this->getCellno(),
            'fname'           => $this->getFirstName(),
            'lname'           => $this->getLastName(),
            'company'         => $this->getCompanyName(),
            'careof'          => $this->getCareof(),
            'street'          => $this->getStreet(),
            'house_number'    => $this->getHouseNumber(),
            'house_extension' => $this->getHouseExt(),
            'zip'             => $this->getZipCode(),
            'city'            => $this->getCity(),
            'country'         => $this->getCountry(),
        );
    }

}
