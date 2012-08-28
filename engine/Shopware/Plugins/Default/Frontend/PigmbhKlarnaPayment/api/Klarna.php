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
 * This API provides a way to integrate with Klarna's services over the XMLRPC protocol.
 *
 * All strings inputted need to be encoded with ISO-8859-1.<br>
 * In addition you need to decode HTML entities, if they exist.<br>
 *
 * For more information see our {@link http://integration.klarna.com/en/api/step-by-step step by step} guide.
 *
 * Dependencies:<br>
 * xmlrpc-3.0.0.beta/lib/xmlrpc.inc          from {@link http://phpxmlrpc.sourceforge.net/}<br>
 * xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc from {@link http://phpxmlrpc.sourceforge.net/}<br>
 *
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2005-2011 Klarna AB (http://klarna.com)
 */
class Klarna {

    /**
     * Klarna PHP API version identifier.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    //protected $VERSION = 'php:api:2.1.2:PigmbhKlarna_Shopware_2.0.7';
    protected $VERSION = 'php:api:2.1.2:ShopwareCore_2.0.7';

    /**
     * Klarna protocol identifier.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $PROTO = '4.1';

    /**
     * Flag to indicate use of the report server Candice.
     *
     * @var bool
     */
    private static $candice = true;

    /**
     * URL/Address to the Candice server.
     * Port used is 80.
     *
     * @var string
     */
    private static $c_addr = "clientstat.kreditor.se";

    /**
     * Constants used with LIVE mode for the communications with Klarna.
     *
     * @var int
     */
    const LIVE = 0;

    /**
     * URL/Address to the live Klarna Online server.
     * Port used is 443 for SSL and 80 without.
     *
     * @var string
     */
    private static $live_addr = 'payment.klarna.com';

    /**
     * Constants used with BETA mode for the communications with Klarna.
     *
     * @var int
     */
    const BETA = 1;

    /**
     * URL/Address to the beta test Klarna Online server.
     * Port used is 443 for SSL and 80 without.
     *
     * @var string
     */
    private static $beta_addr = 'payment-beta.klarna.com';

    /**
     * Indicates whether the communications is over SSL or not.
     *
     * @ignore Do not show this in PHPDoc.
     * @var bool
     */
    protected $ssl = false;

    /**
     * An object of xmlrpc_client, used to communicate with Klarna.
     *
     * @link http://phpxmlrpc.sourceforge.net/
     *
     * @ignore Do not show this in PHPDoc.
     * @var xmlrpc_client
     */
    protected $xmlrpc;

    /**
     * Which server the Klarna API is using, LIVE or BETA (TESTING).
     *
     * @see Klarna::LIVE
     * @see Klarna::BETA
     *
     * @ignore Do not show this in PHPDoc.
     * @var int
     */
    protected $mode;

    /**
     * The URL/Address used to communicate with Klarna.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $addr;

    /**
     * The port number used to communicate with Klarna.
     *
     * @ignore Do not show this in PHPDoc.
     * @var int
     */
    protected $port;

    /**
     * The estore's identifier received from Klarna.
     *
     * @var int
     */
    private $eid;

    /**
     * The estore's shared secret received from Klarna.
     *
     * <b>Note</b>:<br>
     * DO NOT SHARE THIS WITH ANYONE!
     *
     * @var string
     */
    private $secret;

    /**
     * KlarnaCountry constant.
     *
     * @see KlarnaCountry
     *
     * @var int
     */
    private $country;

    /**
     * KlarnaCurrency constant.
     *
     * @see KlarnaCurrency
     *
     * @var int
     */
    private $currency;

    /**
     * KlarnaLanguage constant.
     *
     * @see KlarnaLanguage
     *
     * @var int
     */
    private $language;

    /**
     * An array of articles for the current order.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $goodsList;

    /**
     * An array of article numbers and quantity.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $artNos;

    /**
     * An KlarnaAddr object containing the billing address.
     *
     * @ignore Do not show this in PHPDoc.
     * @var KlarnaAddr
     */
    protected $billing;

    /**
     * An KlarnaAddr object containing the shipping address.
     *
     * @ignore Do not show this in PHPDoc.
     * @var KlarnaAddr
     */
    protected $shipping;

    /**
     * Estore's user(name) or identifier.
     * Only used in {@link Klarna::addTransaction()}.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $estoreUser = "";

    /**
     * External order numbers from other systems.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $orderid = array("", "");

    /**
     * Reference (person) parameter.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $reference = "";

    /**
     * Reference code parameter.
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $reference_code = "";

    /**
     * An array of named extra info.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $extraInfo = array();

    /**
     * An array of named bank info.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $bankInfo = array();

    /**
     * An array of named income expense info.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $incomeInfo = array();

    /**
     * An array of named shipment info.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $shipInfo = array();

    /**
     * An array of named travel info.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $travelInfo = array();

    /**
     * An array of named session id's.<br>
     * E.g. "dev_id_1" => ...<br>
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $sid = array();

    /**
     * A comment sent in the XMLRPC communications.
     * This is resetted using clear().
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $comment = "";

    /**
     * An array with all the checkoutHTML objects.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $coObjects = array();

    /**
     * Flag to indicate if the API should output verbose
     * debugging information.
     *
     * @var bool
     */
    public static $debug = false;

    /**
     * Turns on the internal XMLRPC debugging.
     *
     * @var bool
     */
    public static $xmlrpcDebug = false;

    /**
     * If this is set to true, XMLRPC invocation is disabled.
     *
     * @var bool
     */
    public static $disableXMLRPC = false;

    /**
     * If the estore is using a proxy which populates the clients IP to x_forwarded_for
     * then and only then should this be set to true.
     *
     * <b>Note</b>:<br>
     * USE WITH CARE!
     *
     * @var bool
     */
    public static $x_forwarded_for = false;

    /**
     * Array of HTML entities, used to create numeric htmlentities.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected static $htmlentities = false;

    /**
     * Populated with possible proxy information.
     * A comma separated list of IP addresses.
     *
     * @var string
     */
    private $x_fwd;

    /**
     * The storage class for PClasses.
     *
     * Use 'xml' for xmlstorage.class.php.<br>
     * Use 'mysql' for mysqlstorage.class.php.<br>
     * Use 'json' for jsonstorage.class.php.<br>
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $pcStorage;

    /**
     * The storage URI for PClasses.
     *
     * Use the absolute or relative URI to a file if {@link Klarna::$pcStorage} is set as 'xml' or 'json'.<br>
     * Use a HTTP-auth similar URL if {@link Klarna::$pcStorage} is set as 'mysql', <br>
     * e.g. user:passwd@addr:port/dbName.dbTable.<br>
     *
     * @ignore Do not show this in PHPDoc.
     * @var string
     */
    protected $pcURI;

    /**
     * PCStorage instance.
     *
     * @ignore Do not show this in PHPDoc.
     * @var PCStorage
     */
    protected $pclasses;

    /**
     * ArrayAccess instance.
     *
     * @ignore Do not show this in PHPDoc.
     * @var ArrayAccess
     */
    protected $config;

    /**
     * Class constructor
     *
     * @ignore Does nothing...
     */
    public function __construct() {
    }

    /**
     * Class destructor
     *
     * @ignore Does nothing...
     */
    public function __destruct() {
    }

    /**
     * Checks if the config has fields described in argument.<br>
     * Missing field(s) is in the exception message.
     *
     * To check that the config has eid and secret:<br>
     * <code>
     * try {
     *     $this->hasFields('eid', 'secret');
     * }
     * catch(Exception $e) {
     *     echo "Missing fields: " . $e->getMessage();
     * }
     * </code>
     *
     * @ignore Do not show this in PHPDoc.
     * @param  mixed  $field1  Field name.
     * @param  mixed  $field2  Field name.
     * @param  mixed  $field3  Field name.
     * @param  mixed  ...      Field name.
     * @throws Exception
     * @return void
     */
    protected function hasFields(/*variable arguments*/) {
        $missingFields = array();
        $args = func_get_args();
        foreach($args as $field) {
            if(!isset($this->config[$field])) {
                $missingFields[] = $field;
            }
        }
        if(count($missingFields) > 0) {
            throw new Exception('Missing config field(s): ' . implode(', ', $missingFields), 50001);
        }
    }

    /**
     * Initializes the Klarna object accordingly to the set config object.
     *
     * @ignore Do not show this in PHPDoc.
     * @throws KlarnaException|Exception
     * @return void
     */
    protected function init() {
        $this->hasFields(
                'eid', 'secret', 'mode',
                'pcStorage', 'pcURI'
        );

        if(!is_int($this->config['eid'])) {
            $this->config['eid'] = intval($this->config['eid']);
        }
        if($this->config['eid'] <= 0) {
            throw new Exception("Config field 'eid' is not valid!", 50001);
        }

        if(!is_string($this->config['secret'])) {
            $this->config['secret'] = strval($this->config['secret']);
        }
        if(strlen($this->config['secret']) == 0) {
            throw new Exception("Config field 'secret' not set!", 50001);
        }

        //Set the shop id and secret.
        $this->eid = $this->config['eid'];
        $this->secret = $this->config['secret'];

        if(!is_numeric($this->config['country']) && strlen($this->config['country']) == 2) {
            $this->setCountry($this->config['country']);
        }
        else {
            //Set the country specific attributes.
            try {
                $this->hasFields('country', 'language', 'currency');

                //If hasFields doesn't throw exception we can set them all.
                $this->setCountry($this->config['country']);
                $this->setLanguage($this->config['language']);
                $this->setCurrency($this->config['currency']);
            }
            catch(Exception $e) {
                //fields missing for country, language or currency
                $this->country = $this->language = $this->currency = null;
            }
        }

        //Set addr and port according to mode.
        $this->mode = (int)$this->config['mode'];
        if($this->mode === self::LIVE) {
            $this->addr = self::$live_addr;
            $this->ssl = true;
        }
        else {
            $this->addr = self::$beta_addr;
            $this->ssl = true;
        }

        try {
            $this->hasFields('ssl');
            $this->ssl = (bool)$this->config['ssl'];
        }
        catch(Exception $e) {
            //No 'ssl' field ignore it...
        }

        if($this->ssl) {
             $this->port = 443;
        }
        else {
            $this->port = 80;
        }

        try {
            $this->hasFields('candice');
            self::$candice = (bool)$this->config['candice'];
        }
        catch(Exception $e) {
            //No 'candice' field ignore it...
        }

        try {
            $this->hasFields('xmlrpcDebug');
            Klarna::$xmlrpcDebug = $this->config['xmlrpcDebug'];
        }
        catch(Exception $e) {
            //No 'xmlrpcDebug' field ignore it...
        }

        try {
            $this->hasFields('debug');
            Klarna::$debug = $this->config['debug'];
        }
        catch(Exception $e) {
            //No 'debug' field ignore it...
        }

        $this->pcStorage = $this->config['pcStorage'];
        $this->pcURI = $this->config['pcURI'];

        $this->xmlrpc = new xmlrpc_client('/', $this->addr, $this->port, (($this->ssl) ? 'https' : 'http'));
        $this->xmlrpc->request_charset_encoding = 'ISO-8859-1';
    }

    /**
     * Method of ease for setting common config fields.
     *
     * The storage module for PClasses:<br>
     * Use 'xml' for xmlstorage.class.php.<br>
     * Use 'mysql' for mysqlstorage.class.php.<br>
     * Use 'json' for jsonstorage.class.php.<br>
     *
     * The storage URI for PClasses:<br>
     * Use the absolute or relative URI to a file if {@link Klarna::$pcStorage} is set as 'xml' or 'json'.<br>
     * Use a HTTP-auth similar URL if {@link Klarna::$pcStorage} is set as 'mysql', <br>
     * e.g. user:passwd@addr:port/dbName.dbTable.<br>
     *
     * <b>Note</b>:<br>
     * This disables the config file storage.<br>
     *
     * @see Klarna::setConfig()
     * @see KlarnaConfig
     *
     * @param  int    $eid        Merchant ID/EID
     * @param  string $secret     Secret key/Shared key
     * @param  int    $country    {@link KlarnaCountry}
     * @param  int    $language   {@link KlarnaLanguage}
     * @param  int    $currency   {@link KlarnaCurrency}
     * @param  int    $mode       {@link Klarna::LIVE} or {@link Klarna::BETA}
     * @param  string $pcStorage  PClass storage module.
     * @param  string $pcURI      PClass URI.
     * @param  bool   $ssl        Whether HTTPS (HTTP over SSL) or HTTP is used.
     * @param  bool   $candice    Error reporting to Klarna.
     * @throws KlarnaException
     * @return void
     */
    public function config($eid, $secret, $country, $language, $currency, $mode = Klarna::LIVE, $pcStorage = 'json', $pcURI = 'pclasses.json', $ssl = true, $candice = true) {
        try {
            KlarnaConfig::$store = false;
            $this->config = new KlarnaConfig(null);

            $this->config['eid'] = $eid;
            $this->config['secret'] = $secret;
            $this->config['country']  = $country;
            $this->config['language'] = $language;
            $this->config['currency'] = $currency;
            $this->config['mode'] = $mode;
            $this->config['ssl'] = $ssl;
            $this->config['candice'] = $candice;
            $this->config['pcStorage'] = $pcStorage;
            $this->config['pcURI'] = $pcURI;

            $this->init();
        }
        catch(Exception $e) {
            $this->config = null;
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Sets and initializes this Klarna object using the supplied config object.
     *
     * @see KlarnaConfig
     * @param   KlarnaConfig  &$config  Config object.
     * @throws  KlarnaException
     * @return  void
     */
    public function setConfig(&$config) {
        try {
            if($config instanceof ArrayAccess) {
                $this->config = $config;
                $this->init();
            }
            else {
                throw new Exception('Supplied config is not a KlarnaConfig/ArrayAccess object!', 50001);
            }
        }
        catch(Exception $e) {
            $this->config = null;
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Sets the country used.
     *
     * <b>Note</b>:<br>
     * If you input 'dk', 'fi', 'de', 'nl', 'no' or 'se', <br>
     * then currency and language will be set to mirror that country.<br>
     *
     * @see KlarnaCountry
     *
     * @param  string|int  $country {@link KlarnaCountry}
     * @throws KlarnaException
     * @return void
     */
    public function setCountry($country) {
        if(!is_numeric($country) && (strlen($country) == 2 || strlen($country) == 3)) {
            $this->setCountry(self::getCountryForCode($country));
            $this->setCurrency($this->getCurrencyForCountry());
            $this->setLanguage($this->getLanguageForCountry());
        }
        else {
            $this->checkCountry($country, __METHOD__);
            $this->country = $country;
        }
    }

    /**
     * Returns the country code for the set country constant.
     *
     * @param  int     {@link KlarnaCountry Country} constant.
     * @return string  Two letter code, e.g. "se", "no", etc.
     */
    public function getCountryCode($country = null) {
        $country = ($country === null) ? $this->country : $country;

        $code = KlarnaCountry::getCode($country);
        return ($code === null) ? '' : $code;
    }

    /**
     * Returns the {@link KlarnaCountry country} constant from the country code.
     *
     * @param  string  $code  Two letter code, e.g. "se", "no", etc.
     * @throws KlarnaException
     * @return int  {@link KlarnaCountry Country} constant.
     */
    public static function getCountryForCode($code) {
        $country = KlarnaCountry::fromCode($code);
        if ($country === null) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Unknown country! ("'.$code.'")', 50002);
        }
        return $country;
    }

    /**
     * Returns the country constant.
     *
     * @return int  {@link KlarnaCountry}
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Sets the language used.
     *
     * <b>Note</b>:<br>
     * You can use the two letter language code instead of the constant.<br>
     * E.g. 'da' instead of using {@link KlarnaLanguage::DA}.<br>
     *
     * @see KlarnaLanguage
     *
     * @param  string|int  $language {@link KlarnaLanguage}
     * @throws KlarnaException
     * @return void
     */
    public function setLanguage($language) {
        if(!is_numeric($language) && strlen($language) == 2) {
            $this->setLanguage(self::getLanguageForCode($language));
        }
        else {
            $this->checkLanguage($language, __METHOD__);
            $this->language = $language;
        }
    }

    /**
     * Returns the language code for the set language constant.
     *
     * @param  int     {@link KlarnaLanguage Language} constant.
     * @return string  Two letter code, e.g. "da", "de", etc.
     */
    public function getLanguageCode($language = null) {
        $language = ($language === null) ? $this->language : $language;

        $code = KlarnaLanguage::getCode($language);
        return ($code === null) ? '' : $code;
    }

    /**
     * Returns the {@link KlarnaLanguage language} constant from the language code.
     *
     * @param  string  $code  Two letter code, e.g. "da", "de", etc.
     * @throws KlarnaException
     * @return int  {@link KlarnaLanguage Language} constant.
     */
    public static function getLanguageForCode($code) {
        $language = KlarnaLanguage::fromCode($code);
        if ($language === null) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Unknown language! ('.$code.')', 50003);
        }
        return $language;
    }

    /**
     * Returns the language constant.
     *
     * @return int  {@link KlarnaLanguage}
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Sets the currency used.
     *
     * <b>Note</b>:<br>
     * You can use the three letter shortening of the currency.<br>
     * E.g. "dkk", "eur", "nok" or "sek" instead of the constant.<br>
     *
     * @see KlarnaCurrency
     *
     * @param  string|int $currency {@link KlarnaCurrency}
     * @throws KlarnaException
     * @return void
     */
    public function setCurrency($currency) {
        if(!is_numeric($currency) && strlen($currency) == 3) {
            $this->setCurrency(self::getCurrencyForCode($currency));
        }
        else {
            $this->checkCurrency($currency, __METHOD__);
            $this->currency = $currency;
        }
    }

    /**
     * Returns the {@link KlarnaCurrency currency} constant from the currency code.
     *
     * @param  string  $code  Two letter code, e.g. "dkk", "eur", etc.
     * @throws KlarnaException
     * @return int  {@link KlarnaCurrency Currency} constant.
     */
    public static function getCurrencyForCode($code) {
        $currency = KlarnaCurrency::fromCode($code);
        if ($code === null) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Unknown currency! ('.$code.')', 50004);
        }
        return $currency;
    }

    /**
     * Returns the the currency code for the set currency constant.
     *
     * @param  int     {@link KlarnaCurrency Currency} constant.
     * @return string  Three letter currency code.
     */
    public function getCurrencyCode($currency = null) {
        $currency = ($currency === null) ? $this->currency : $currency;

        $code = KlarnaCurrency::getCode($currency);
        return ($code === null) ? '' : $code;
    }

    /**
     * Returns the set currency constant.
     *
     * @return int  {@link KlarnaCurrency}
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * Checks set country against set currency and returns true if they match.<br>
     * {@link KlarnaCountry} or {@link KlarnaCurrency} constants can be used, or letter codes.<br>
     * Uses set values if parameter is null.<br>
     *
     * E.g. Klarna allows Euro with Germany, Netherlands and Finland, thus true will be returned.
     *
     * @param  string|int $country  {@link KlarnaCountry}
     * @param  string|int $currency {@link KlarnaCurrency}
     * @throws KlarnaException
     * @return bool
     */
    public function checkCountryCurrency($country = null, $currency = null) {
        if($country === null) {
            $country = $this->country;
        }
        else {
            if(!is_numeric($country) && (strlen($country) == 2 || strlen($country) == 3)) {
                $country = self::getCountryForCode($country);
            }
        }

        if($currency === null) {
            $currency = $this->currency;
        }
        else {
            if(!is_numeric($currency) && strlen($currency) == 3) {
                $currency = self::getCurrencyForCode($currency);
            }
        }

        switch($country) {
            case KlarnaCountry::DE:
            case KlarnaCountry::NL:
            case KlarnaCountry::FI:
                return ($currency !== KlarnaCurrency::EUR) ? false : true;
            case KlarnaCountry::DK:
                return ($currency !== KlarnaCurrency::DKK) ? false : true;
            case KlarnaCountry::NO:
                return ($currency !== KlarnaCurrency::NOK) ? false : true;
            case KlarnaCountry::SE:
                return ($currency !== KlarnaCurrency::SEK) ? false : true;
            default:
                //Country not yet supported by Klarna.
                return false;
        }
    }

    /**
     * Returns the {@link KlarnaLanguage language} constant for the specified or set country.
     *
     * @param  int  $country  {@link KlarnaCountry Country} constant.
     * @return mixed  False, if no match otherwise {@link KlarnaLanguage language} constant.
     */
    public function getLanguageForCountry($country = null) {
        $country = ($country === null) ? $this->country : $country;
        switch($country) {
            case KlarnaCountry::DE:
                return KlarnaLanguage::DE;
            case KlarnaCountry::NL:
                return KlarnaLanguage::NL;
            case KlarnaCountry::FI:
                return KlarnaLanguage::FI;
            case KlarnaCountry::DK:
                return KlarnaLanguage::DA;
            case KlarnaCountry::NO:
                return KlarnaLanguage::NB;
            case KlarnaCountry::SE:
                return KlarnaLanguage::SV;
            default:
                //Country not yet supported by Klarna.
                return false;
        }
    }

    /**
     * Returns the {@link KlarnaCurrency currency} constant for the specified or set country.
     *
     * @param  int  $country  {@link KlarnaCountry country} constant.
     * @return mixed  False, if no match otherwise {@link KlarnaCurrency currency} constant.
     */
    public function getCurrencyForCountry($country = null) {
        $country = ($country === null) ? $this->country : $country;
        switch($country) {
            case KlarnaCountry::DE:
            case KlarnaCountry::NL:
            case KlarnaCountry::FI:
                return KlarnaCurrency::EUR;
            case KlarnaCountry::DK:
                return KlarnaCurrency::DKK;
            case KlarnaCountry::NO:
                return KlarnaCurrency::NOK;
            case KlarnaCountry::SE:
                return KlarnaCurrency::SEK;
            default:
                //Country not yet supported by Klarna.
                return false;
        }
    }

    /**
     * <b>STILL UNDER DEVELOPMENT</b><br>
     * Sets the session id's for various device identification,
     * behaviour identification software.
     *
     * <b>Available named session id's</b>:<br>
     * string - dev_id_1<br>
     * string - dev_id_2<br>
     * string - dev_id_3<br>
     * string - beh_id_1<br>
     * string - beh_id_2<br>
     * string - beh_id_3<br>
     *
     * @param  string  $name  Session ID identifier, e.g. 'dev_id_1'.
     * @param  string  $sid   Session ID.
     * @throws KlarnaException
     * @return void
     */
    public function setSessionID($name, $sid) {
        try {
            if(!is_string($name)) {
                $name = strval($name);
            }
            if(strlen($name) == 0) {
                throw new Exception("Argument 'name' is not set!", 50005);
            }

            if(!is_string($sid)) {
                $sid = strval($sid);
            }
            if(strlen($sid) == 0) {
                throw new Exception("Argument 'sid' is not set!", 50006);
            }

            if(!is_array($this->sid)) {
                $this->sid = array();
            }
            $this->sid[$name] = $sid;
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " .$e->getMessage(), $e->getCode());
        }
    }

    /**
     * <b>STILL UNDER DEVELOPMENT</b><br>
     * Sets the shipment information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * <b>Available named values are</b>:<br>
     * int    - delay_adjust<br>
     * string - shipping_company<br>
     * string - shipping_product<br>
     * string - tracking_no<br>
     * array  - warehouse_addr<br>
     *
     * "warehouse_addr" is sent using {@link KlarnaAddr::toArray()}.
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.<br>
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws KlarnaException
     * @return void
     */
    public function setShipmentInfo($name, $value) {
        try {
            if(!is_string($name)) {
                $name = strval($name);
            }
            if(strlen($name) == 0) {
                throw new Exception("Argument 'name' is not set!", 50005);
            }

            if(!is_array($this->shipInfo)) {
                $this->shipInfo = array();
            }
            $this->shipInfo[$name] = $value;
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " .$e->getMessage(), $e->getCode());
        }
    }

    /**
     * <b>STILL UNDER DEVELOPMENT</b><br>
     * Sets the extra information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * <b>Available named values are</b>:<br>
     * string - cust_no<br>
     * string - estore_user<br>
     * string - maiden_name<br>
     * string - place_of_birth<br>
     * string - password<br>
     * string - new_password<br>
     * string - captcha<br>
     * int    - poa_group<br>
     * string - poa_pno<br>
     * string - ready_date<br>
     * string - rand_string<br>
     * int    - bclass<br>
     * string - pin<br>
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.<br>
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws KlarnaException
     * @return void
     */
    public function setExtraInfo($name, $value) {
        try {
            if(!is_string($name)) {
                $name = strval($name);
            }
            if(strlen($name) == 0) {
                throw new Exception("Argument 'name' is not set!", 50005);
            }

            if(!is_array($this->extraInfo)) {
                $this->extraInfo = array();
            }
            $this->extraInfo[$name] = $value;
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " .$e->getMessage(), $e->getCode());
        }
    }

    /**
     * <b>STILL UNDER DEVELOPMENT</b><br>
     * Sets the income expense information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * <b>Available named values are</b>:<br>
     * int - yearly_salary<br>
     * int - no_people_in_household<br>
     * int - no_children_below_18<br>
     * int - net_monthly_household_income<br>
     * int - monthly_cost_accommodation<br>
     * int - monthly_cost_other_loans<br>
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.<br>
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws KlarnaException
     * @return void
     */
    public function setIncomeInfo($name, $value) {
        try {
            if(!is_string($name)) {
                $name = strval($name);
            }
            if(strlen($name) == 0) {
                throw new Exception("Argument 'name' is not set!", 50005);
            }

            if(!is_array($this->incomeInfo)) {
                $this->incomeInfo = array();
            }
            $this->incomeInfo[$name] = $value;
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " .$e->getMessage(), $e->getCode());
        }
    }

    /**
     * <b>STILL UNDER DEVELOPMENT</b><br>
     * Sets the bank information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * <b>Available named values are</b>:<br>
     * int    - bank_acc_bic<br>
     * int    - bank_acc_no<br>
     * int    - bank_acc_pin<br>
     * int    - bank_acc_tan<br>
     * string - bank_name<br>
     * string - bank_city<br>
     * string - iban<br>
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.<br>
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws KlarnaException
     * @return void
     */
    public function setBankInfo($name, $value) {
        try {
            if(!is_string($name)) {
                $name = strval($name);
            }
            if(strlen($name) == 0) {
                throw new Exception("Argument 'name' is not set!", 50005);
            }

            if(!is_array($this->bankInfo)) {
                $this->bankInfo = array();
            }
            $this->bankInfo[$name] = $value;
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " .$e->getMessage(), $e->getCode());
        }
    }

    /**
     * <b>STILL UNDER DEVELOPMENT</b><br>
     * Sets the travel information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * <b>Available named values are</b>:<br>
     * string - travel_company<br>
     * string - reseller_company<br>
     * string - departure_date<br>
     * string - return_date<br>
     * array  - destinations<br>
     * array  - passenger_list<br>
     * array  - passport_no<br>
     * array  - driver_license_no<br>
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.<br>
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws KlarnaException
     * @return void
     */
    public function setTravelInfo($name, $value) {
        try {
            if(!is_string($name)) {
                $name = strval($name);
            }
            if(strlen($name) == 0) {
                throw new Exception("Argument 'name' is not set!", 50005);
            }

            if(!is_array($this->travelInfo)) {
                $this->travelInfo = array();
            }
            $this->travelInfo[$name] = $value;
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " .$e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns the clients IP address.
     *
     * @return string
     */
    public function getClientIP() {
        //Proxy handling.
        $tmp_ip = $_SERVER['REMOTE_ADDR'];
        $x_fwd = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : null;
        if(self::$x_forwarded_for && $x_fwd !== null) {
            //Cut out the first IP address
            if(($cpos = strpos($x_fwd, ',')) !== false) {
                $tmp_ip = substr($x_fwd, 0, $cpos);
                $x_fwd = substr($x_fwd, $cpos+2);
            }
            else { //Only one IP address
                $tmp_ip = $x_fwd;
                $x_fwd = null;
            }
        }
        $this->x_fwd = $x_fwd;

        return $tmp_ip;
    }

    /**
     * Sets the specified address for the current order.
     *
     * <b>Address type can be</b>:<br>
     * {@link KlarnaFlags::IS_SHIPPING}<br>
     * {@link KlarnaFlags::IS_BILLING}<br>
     *
     * @param  int         $type  Address type.
     * @param  KlarnaAddr  $addr  Specified address.
     * @throws KlarnaException
     * @return void
     */
    public function setAddress($type, $addr) {
        if(!($addr instanceof KlarnaAddr)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Supplied address is not a KlarnaAddr object!', 50011);
        }

        if($addr->isCompany === null) {
            $addr->isCompany = false;
        }

        if($type === KlarnaFlags::IS_SHIPPING) {
            $this->shipping = $addr;
            self::printDebug("shipping address array", $this->shipping);
        }
        else if($type === KlarnaFlags::IS_BILLING) {
            $this->billing = $addr;
            self::printDebug("billing address array", $this->billing);
        }
        else {
            throw new KlarnaException("Error in " . __METHOD__ . ": Unknown address type ($type)", 50012);
        }
    }

    /**
     * Sets order id's from other systems for the upcoming transaction.<br>
     * User is only sent with {@link Klarna::addTransaction()}.<br>
     *
     * @see Klarna::setExtraInfo()
     * @param  string $orderid1
     * @param  string $orderid2
     * @param  string $user
     * @throws KlarnaException
     * @return void
     */
    public function setEstoreInfo($orderid1 = "", $orderid2 = "", $user = "") {
        if(!is_string($user)) {
            $user = strval($user);
        }
        if(!is_string($orderid1)) {
            $orderid1 = strval($orderid1);
        }
        if(!is_string($orderid2)) {
            $orderid2 = strval($orderid2);
        }
        if(!is_string($user)) {
            $user = strval($user);
        }
        if(strlen($user) > 0 ) {
            $this->setExtraInfo('estore_user', $user);
        }
        $this->orderid[0] = $orderid1;
        $this->orderid[1] = $orderid2;
    }

    /**
     * Sets the reference (person) and reference code, for the upcoming transaction.
     *
     * If this is omitted, it can grab first name, last name from the address and use that as a reference person.
     *
     * @param  string $ref   Reference person / message to customer on invoice.
     * @param  string $code  Reference code / message to customer on invoice.
     * @return void
     */
    public function setReference($ref, $code) {
        $this->checkRef($ref, $code, __METHOD__);
        $this->reference = $ref;
        $this->reference_code = $code;
    }

    /**
     * Returns the reference (person).
     *
     * @return string
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * Returns an associative array used to send the address to Klarna.
     *
     * @ignore Do not show this in PHPDoc.
     * @param  string      $method  __METHOD__, the method calling assembleAddr.
     * @param  KlarnaAddr  $addr    Address object to assemble.
     * @throws KlarnaException
     * @return array The address for the specified method.
     */
    protected function assembleAddr($method, $addr) {
        if(!($addr instanceof KlarnaAddr)) {
            throw new KlarnaException('Error in ' . $method . ': Specified address is not a KlarnaAddr object! (Call setAddress first?!)', 50013);
        }

        $tmp = $addr->toArray();

        //Check address!
        if($tmp['country'] === KlarnaCountry::NL || $tmp['country'] === KlarnaCountry::DE) {
            if(strlen($tmp['house_number']) == 0) {
                throw new KlarnaException("Error in " . $method . ': House number needs to be specified for Netherlands and Germany!', 50014);
            }
        }

        if(strlen($tmp['email']) == 0) {
            throw new KlarnaException("Error in " . $method . ': Email address not set!', 50015);
        }

        //Check email against a regular expression.
        if(!preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z0-9-][a-zA-Z0-9-]+)+$/", $tmp['email'])) {
            throw new KlarnaException("Error in " . $method . ': Email address is not valid! ('.$tmp['email'].')', 50016);
        }

        //Only check fname and lname if company isn't set.
        if(strlen($tmp['company']) == 0) {
            if(strlen($tmp['fname']) == 0) {
                throw new KlarnaException("Error in " . $method . ': First name not set!', 50017);
            }

            if(strlen($tmp['lname']) == 0) {
                throw new KlarnaException("Error in " . $method . ': Last name not set!', 50018);
            }
        }

        if(strlen($tmp['street']) == 0) {
            throw new KlarnaException("Error in " . $method . ': Street address not set!', 50019);
        }

        if(strlen($tmp['zip']) == 0) {
            throw new KlarnaException("Error in " . $method . ': Zip code not set!', 50020);
        }

        if(strlen($tmp['city']) == 0) {
            throw new KlarnaException("Error in " . $method . ': City not set!', 50021);
        }

        if($tmp['country'] <= 0) {
            throw new KlarnaException("Error in " . $method . ': Country not set!', 50022);
        }

        return $tmp;
    }

    /**
     * Sets the comment field, which can be shown in the invoice.
     *
     * @param  string  $data
     * @return void
     */
    public function setComment($data) {
        $this->comment = $data;
    }

    /**
     * Adds an additional comment to the comment field.
     *
     * @see Klarna::setComment()
     *
     * @param  string  $data
     * @return void
     */
    public function addComment($data) {
        $this->comment .= "\n".$data;
    }

    /**
     * Returns the PNO/SSN encoding constant for currently set country.
     *
     * <b>Note</b>:<br>
     * Country, language and currency needs to match!
     *
     * @throws KlarnaException
     * @return int  {@link KlarnaEncoding} constant.
     */
    public function getPNOEncoding() {
        if(!is_int($this->country) || !is_int($this->language) || !is_int($this->currency)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': You must set country, language and currency!', 50023);
        }

        switch($this->country) {
            case KlarnaCountry::DE:
                if($this->currency !== KlarnaCurrency::EUR || $this->language !== KlarnaLanguage::DE) {
                    throw new KlarnaException('Error in ' . __METHOD__ . ': Mismatching currency/language for DE! ('.$this->currency.','.$this->language.')', 50024);
                }
                return KlarnaEncoding::PNO_DE;
                break;

            case KlarnaCountry::DK:
                if($this->currency !== KlarnaCurrency::DKK || $this->language !== KlarnaLanguage::DA) {
                    throw new KlarnaException('Error in ' . __METHOD__ . ': Mismatching currency/language for DK! ('.$this->currency.','.$this->language.')', 50024);
                }
                return KlarnaEncoding::PNO_DK;
                break;

            case KlarnaCountry::FI:

                if($this->currency !== KlarnaCurrency::EUR || ($this->language !== KlarnaLanguage::FI && $this->language !== KlarnaLanguage::SV)) {
                    throw new KlarnaException('Error in ' . __METHOD__ . ': Mismatching currency/language for FI! ('.$this->currency.','.$this->language.')', 50024);
                }
                return KlarnaEncoding::PNO_FI;
                break;

            case KlarnaCountry::NL:
                if($this->currency !== KlarnaCurrency::EUR || $this->language !== KlarnaLanguage::NL) {
                    throw new KlarnaException('Error in ' . __METHOD__ . ': Mismatching currency/language for NL! ('.$this->currency.','.$this->language.')', 50024);
                }
                return KlarnaEncoding::PNO_NL;
                break;

            case KlarnaCountry::NO:
                if($this->currency !== KlarnaCurrency::NOK || $this->language !== KlarnaLanguage::NB) {
                    throw new KlarnaException('Error in ' . __METHOD__ . ': Mismatching currency/language for NO! ('.$this->currency.','.$this->language.')', 50024);
                }
                return KlarnaEncoding::PNO_NO;
                break;

            case KlarnaCountry::SE:
                if($this->currency !== KlarnaCurrency::SEK || $this->language !== KlarnaLanguage::SV) {
                    throw new KlarnaException('Error in ' . __METHOD__ . ': Mismatching currency/language for SE ('.$this->currency.','.$this->language.')', 50024);
                }
                return KlarnaEncoding::PNO_SE;
                break;

            default:
                throw new KlarnaException('Error in ' . __METHOD__ . ': Unknown/unsupported country! (ISO3166 '.$this->country.')', 50025);
        }
    }

    /**
     * The purpose of this method is to check if the customer has answered the ILT questions.<br>
     * If the questions need to be answered, an array will be returned where the key is the<br>
     * corresponding identifier for {@link Klarna::setIncomeInfo()}, which contains the question,<br>
     * the input type and the values.<br>
     *
     * Note:
     * You need to call {@link Klarna::setAddress()} with {@link KlarnaFlags::IS_SHIPPING} before calling this method.
     *
     * An example could be:<br>
     * <code>
     * array(
     *     'children_under_18' => array(
     *         'text' => 'Aantal kinderen onder de 18 jaar die thuiswonen?',
     *         'type' => 'drop-down',
     *         'values' => array('0','1','2','3','4','>5')
     *     )
     * )
     * </code>
     *
     * You need to render this question and then send the identifier<br>
     * and the user supplied answer in {@link Klarna::setIncomeInfo()}.
     *
     * @param  int|float   $amount    Amount including VAT.
     * @param  string      $pno       Personal number, SSN, date of birth, etc.
     * @param  int         $gender    {@link KlarnaFlags::FEMALE} or {@link KlarnaFlags::MALE}, null or "" for unspecified.
     * @param  int         $encoding  {@link KlarnaEncoding Encoding} constant for the PNO parameter.
     * @throws KlarnaException
     * @return array
     */
    public function checkILT($amount, $pno, $gender, $encoding = null) {
        if(!is_int($this->country) || !is_int($this->language) || !is_int($this->currency)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': You must set country, language and currency! ('.$this->country.','.$this->currency.','.$this->language.')', 50023);
        }

        $this->checkAmount($amount, __METHOD__);

        //Get the PNO/SSN encoding constant.
        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);

        if($gender === 'm') {
            $gender = KlarnaFlags::MALE;
        }
        else if($gender === 'f') {
            $gender = KlarnaFlags::FEMALE;
        }
        if($gender !== null) {
            $this->checkInt($gender, 'gender', __METHOD__);
        }

        if(!($this->shipping instanceof KlarnaAddr)) {
            throw new KlarnaException("Error in " . __METHOD__ . ": No shipping address set!", 50035);
        }
        $shipping = $this->assembleAddr(__METHOD__, $this->shipping);

        //Shipping country must match specified country!
        if($shipping['country'] !== $this->country) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Shipping address country must match the country set!', 50036);
        }

        $digestSecret = self::digest($this->colon($this->eid, $pno, $this->secret));
        $paramList = array($this->eid, $digestSecret, $amount, $pno, $gender, $shipping, $this->country, $this->language, $this->currency, $encoding);

        self::printDebug("check_ilt array", $paramList);

        $result = $this->xmlrpc_call('check_ilt', $paramList);

        self::printDebug("check_ilt result array", $result);

        return $result;
    }

    /**
     * Purpose: The get_addresses function is used to retrieve a customer's address(es).<br>
     * Using this, the customer is not required to enter any information only confirm the one presented to him/her.<br>
     *
     * The get_addresses function can also be used for companies.<br>
     * If the customer enters a company number, it will return all the addresses where the company is registered at.<br>
     *
     * The get_addresses function is ONLY allowed to be used for Swedish persons with the following conditions:
     * <ul>
     *     <li>It can be only used if invoice or part payment is the default payment method</li>
     *     <li>It has to disappear if the customer chooses another payment method</li>
     *     <li>The button is not allowed to be called "get address", but "continue" or<br>
     *         it can be picked up automatically when all the numbers have been typed.</li>
     * </ul>
     *
     * <b>Type can be one of these</b>:<br>
     * {@link KlarnaFlags::GA_ALL},<br>
     * {@link KlarnaFlags::GA_LAST},<br>
     * {@link KlarnaFlags::GA_GIVEN}.<br>
     *
     * @link http://integration.klarna.com/en/api/standard-integration/functions/getaddresses
     * @param  string  $pno       Social security number, personal number, ...
     * @param  int     $encoding  {@link KlarnaEncoding PNO Encoding} constant.
     * @param  int     $type      Specifies returned information.
     * @throws KlarnaException
     * @return array   An array of {@link KlarnaAddr} objects.
     */
    public function getAddresses($pno, $encoding = null, $type = KlarnaFlags::GA_GIVEN) {
        if($this->country !== KlarnaCountry::SE) {
            throw new KlarnaException("Error in " . __METHOD__ . ": This method is only available for Swedish customers!", 50025);
        }

        //Get the PNO/SSN encoding constant.
        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $pno, $this->secret));
        $paramList = array($pno, $this->eid, $digestSecret, $encoding, $type, $this->getClientIP());

        self::printDebug("get_addresses array", $paramList);

        $result = $this->xmlrpc_call('get_addresses', $paramList);

        self::printDebug("get_addresses result array", $result);

        $addrs = array();
        foreach($result as $tmpAddr) {
            try {
                $addr = new KlarnaAddr();
                if($type === KlarnaFlags::GA_GIVEN) {
                    $addr->isCompany = (count($tmpAddr) == 5) ? true : false;
                    if($addr->isCompany) {
                        $addr->setCompanyName($tmpAddr[0]);
                        $addr->setStreet($tmpAddr[1]);
                        $addr->setZipCode($tmpAddr[2]);
                        $addr->setCity($tmpAddr[3]);
                        $addr->setCountry($tmpAddr[4]);
                    }
                    else {
                        $addr->setFirstName($tmpAddr[0]);
                        $addr->setLastName($tmpAddr[1]);
                        $addr->setStreet($tmpAddr[2]);
                        $addr->setZipCode($tmpAddr[3]);
                        $addr->setCity($tmpAddr[4]);
                        $addr->setCountry($tmpAddr[5]);
                    }
                }
                else if($type === KlarnaFlags::GA_LAST) {
                    //Here we cannot decide if it is a company or not? Assume private person.
                    $addr->setLastName($tmpAddr[0]);
                    $addr->setStreet($tmpAddr[1]);
                    $addr->setZipCode($tmpAddr[2]);
                    $addr->setCity($tmpAddr[3]);
                    $addr->setCountry($tmpAddr[4]);
                }
                else if($type === KlarnaFlags::GA_ALL) {
                    if(strlen($tmpAddr[0]) > 0) {
                        $addr->setFirstName($tmpAddr[0]);
                        $addr->setLastName($tmpAddr[1]);
                    }
                    else {
                        $addr->isCompany = true;
                        $addr->setCompanyName($tmpAddr[1]);
                    }
                    $addr->setStreet($tmpAddr[2]);
                    $addr->setZipCode($tmpAddr[3]);
                    $addr->setCity($tmpAddr[4]);
                    $addr->setCountry($tmpAddr[5]);
                }
                else {
                    continue;
                }
                $addrs[] = $addr;
            }
            catch(Exception $e) {
                //Silently fail
            }
        }

        return $addrs;
    }

    /**
     * Adds an article to the current goods list for the current order.
     *
     * <b>Note</b>:<br>
     * It is recommended that you use {@link KlarnaFlags::INC_VAT}.<br>
     *
     * <b>Flags can be</b>:<br>
     * {@link KlarnaFlags::INC_VAT}<br>
     * {@link KlarnaFlags::IS_SHIPMENT}<br>
     * {@link KlarnaFlags::IS_HANDLING}<br>
     * {@link KlarnaFlags::PRINT_1000}<br>
     * {@link KlarnaFlags::PRINT_100}<br>
     * {@link KlarnaFlags::PRINT_10}<br>
     * {@link KlarnaFlags::NO_FLAG}<br>
     *
     * Some flags can be added to each other for multiple options.
     *
     * @see Klarna::addTransaction()
     * @see Klarna::reserveAmount()
     * @see Klarna::activateReservation()
     *
     * @param  int     $qty       Quantity.
     * @param  string  $artNo     Article number.
     * @param  string  $title     Article title.
     * @param  int     $price     Article price.
     * @param  float   $vat       VAT in percent, e.g. 25% is inputted as 25.
     * @param  float   $discount  Possible discount on article.
     * @param  int     $flags     Options which specify the article ({@link KlarnaFlags::IS_HANDLING}) and it's price ({@link KlarnaFlags::INC_VAT})
     * @throws KlarnaException
     * @return void
     */
    public function addArticle($qty, $artNo, $title, $price, $vat, $discount = 0, $flags = KlarnaFlags::INC_VAT) {
        $this->checkQty($qty, __METHOD__);

        $prevException = false;
        try {
            $this->checkArtNo($artNo, __METHOD__);
        }
        catch(KlarnaException $e) {
            $prevException = $e;
        }
        try {
            $this->checkArtTitle($title, __METHOD__);
        }
        catch(KlarnaException $e) {
            if($prevException instanceof KlarnaException) {
                throw new KlarnaException('Error in ' . __METHOD__ . ': Title or ArtNo needs to be set!', 50026);
            }
        }

        $this->checkPrice($price, __METHOD__);
        $this->checkVAT($vat, __METHOD__);
        $this->checkDiscount($discount, __METHOD__);
        $this->checkInt($flags, 'flags', __METHOD__);

        //Create goodsList array if not set.
        if(!$this->goodsList || !is_array($this->goodsList)) {
            $this->goodsList = array();
        }

        /*if(KlarnaFlags::PRINT_10 & $flags) {

        }*/

        //Populate a temp array with the article details.
        $tmpArr = array(
                "artno"    => $artNo,
                "title"    => $title,
                "price"    => $price,
                "vat"      => $vat,
                "discount" => $discount,
                "flags"    => $flags
        );

        //Add the temp array and quantity field to the internal goods list.
        $this->goodsList[] = array(
                "goods" => $tmpArr,
                "qty"   => $qty
        );

        if(count($this->goodsList) > 0) {
            self::printDebug("article added", $this->goodsList[count($this->goodsList)-1]);
        }
    }

    /**
     * Assembles and sends the current order to Klarna.<br>
     * This clears all relevant data if $clear is set to true.<br>
     *
     * <b>This method returns an array with</b>:<br>
     * Invoice number<br>
     * Order status flag<br>
     *
     * If the flag {@link KlarnaFlags::RETURN_OCR} is used:<br>
     * Invoice number<br>
     * OCR number <br>
     * Order status flag<br>
     *
     * <b>Order status can be</b>:<br>
     * {@link KlarnaFlags::ACCEPTED}<br>
     * {@link KlarnaFlags::PENDING}<br>
     * {@link KlarnaFlags::DENIED}<br>
     *
     * Gender is only required for Germany and Netherlands.<br>
     *
     * <b>Flags can be</b>:<br>
     * {@link KlarnaFlags::NO_FLAG}<br>
     * {@link KlarnaFlags::TEST_MODE}<br>
     * {@link KlarnaFlags::AUTO_ACTIVATE}<br>
     * {@link KlarnaFlags::PRE_PAY}<br>
     * {@link KlarnaFlags::SENSITIVE_ORDER}<br>
     * {@link KlarnaFlags::RETURN_OCR}<br>
     * {@link KlarnaFlags::M_PHONE_TRANSACTION}<br>
     * {@link KlarnaFlags::M_SEND_PHONE_PIN}<br>
     *
     * Some flags can be added to each other for multiple options.
     *
     * <b>Note</b>:<br>
     * Normal shipment type is assumed unless otherwise specified, you can do this by calling:<br>
     * {@link Klarna::setShipmentInfo() Klarna::setShipmentInfo('delay_adjust', ...)}<br>
     * with either:<br>
     * {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}<br>
     *
     * @link http://integration.klarna.com/en/api/standard-integration/functions/addtransaction
     * @param  string  $pno            Personal number, SSN, date of birth, etc.
     * @param  int     $gender         {@link KlarnaFlags::FEMALE} or {@link KlarnaFlags::MALE}, null or "" for unspecified.
     * @param  int     $flags          Options which affect the behaviour.
     * @param  int     $pclass         PClass id used for this invoice.
     * @param  int     $encoding       {@link KlarnaEncoding Encoding} constant for the PNO parameter.
     * @param  bool    $clear          Whether customer info should be cleared after this call.
     * @throws KlarnaException
     * @return array   An array with invoice number and order status. [string, int]
     */
    public function addTransaction($pno, $gender, $flags = KlarnaFlags::NO_FLAG, $pclass = KlarnaPClass::INVOICE, $encoding = null, $clear = true) {
        if(!is_int($this->country) || !is_int($this->language) || !is_int($this->currency)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': You must set country, language and currency! ('.$this->country.','.$this->currency.','.$this->language.')', 50023);
        }

        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);
        if($gender === 'm') {
            $gender = KlarnaFlags::MALE;
        }
        else if($gender === 'f') {
            $gender = KlarnaFlags::FEMALE;
        }
        if($gender !== null) {
            $this->checkInt($gender, 'gender', __METHOD__);
        }
        $this->checkInt($flags,  'flags',  __METHOD__);
        $this->checkInt($pclass, 'pclass', __METHOD__);

        //Check so required information is set.
        if(!is_array($this->goodsList) || empty($this->goodsList)) {
            throw new KlarnaException("Error in " . __METHOD__ . ": No articles in goods list!", 50034);
        }

        //If only one address is set, copy to the other address.
        if(!($this->shipping instanceof KlarnaAddr) && ($this->billing instanceof KlarnaAddr)) {
            $this->shipping = $this->billing;
        }
        else if(!($this->billing instanceof KlarnaAddr) && ($this->shipping instanceof KlarnaAddr)) {
            $this->billing = $this->shipping;
        }
        else if(!($this->billing instanceof KlarnaAddr) && !($this->shipping instanceof KlarnaAddr)) {
            throw new KlarnaException("Error in " . __METHOD__ . ": No address set!", 50035);
        }

        //Assume normal shipment unless otherwise specified.
        if(!isset($this->shipInfo['delay_adjust'])) {
            $this->setShipmentInfo('delay_adjust', KlarnaFlags::NORMAL_SHIPMENT);
        }

        //Make sure we get any session ID's or similar
        $this->initCheckout();

        //function add_transaction_digest
        $string = "";
        foreach($this->goodsList as $goods) {
            $string .= $goods['goods']['title'] .':';
        }
        $digestSecret = self::digest($string . $this->secret);
        //end function add_transaction_digest

        $billing = $this->assembleAddr(__METHOD__, $this->billing);
        $shipping = $this->assembleAddr(__METHOD__, $this->shipping);

        //Shipping country must match specified country!
        if($shipping['country'] !== $this->country) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Shipping address country must match the country set!', 50036);
        }

        $tmp = array(
            $pno, $gender, $this->reference, $this->reference_code, $this->orderid[0], $this->orderid[1],
            $shipping, $billing, $this->getClientIP(), $flags, $this->currency, $this->country,
            $this->language, $this->eid, $digestSecret, $encoding, $pclass, $this->goodsList,
            $this->comment, $this->shipInfo, $this->travelInfo, $this->incomeInfo, $this->bankInfo,
            $this->sid, $this->extraInfo
        );

        self::printDebug('add_invoice', $tmp);

        $result = $this->xmlrpc_call('add_invoice', $tmp);

        if($clear === true) {
            //Make sure any stored values that need to be unique between purchases are cleared.
            foreach($this->coObjects as $co) {
                $co->clear();
            }
            $this->clear();
        }

        self::printDebug('add_invoice result', $result);

        return $result;
    }


    /**
     * Activates previously created invoice (from {@link Klarna::addTransaction()}).
     *
     * <b>Note</b>:<br>
     * If you want to change the shipment type, you can specify it using:<br>
     * {@link Klarna::setShipmentInfo() Klarna::setShipmentInfo('delay_adjust', ...)}<br>
     * with either: {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}<br>
     *
     *
     * @see Klarna::setShipmentInfo()
     * @link http://integration.klarna.com/en/api/standard-integration/functions/activateinvoice
     * @param  string  $invNo   Invoice number.
     * @param  int     $pclass  PClass id used for this invoice.
     * @param  bool    $clear   Whether customer info should be cleared after this call.
     * @throws KlarnaException
     * @return string  An URL to the PDF invoice.
     */
    public function activateInvoice($invNo, $pclass = KlarnaPClass::INVOICE, $clear = true) {
        $this->checkInvNo($invNo, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $invNo, $this->secret));
        $paramList = array($this->eid, $invNo, $digestSecret, $pclass, $this->shipInfo);

        self::printDebug('activate_invoice', $paramList);

        $result = $this->xmlrpc_call('activate_invoice', $paramList);

        if($clear === true) {
            $this->clear();
        }

        self::printDebug('activate_invoice result', $result);

        return $result;
    }

    /**
     * Removes a passive invoices which has previously been created with {@link Klarna::addTransaction()}.<br>
     * True is returned if the invoice was successfully removed, otherwise an exception is thrown.<br>
     *
     * @param  string  $invno  Invoice number.
     * @throws KlarnaException
     * @return bool
     */
    public function deleteInvoice($invNo) {
        $this->checkInvNo($invNo, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $invNo, $this->secret));
        $paramList = array($this->eid, $invNo, $digestSecret);

        self::printDebug('delete_invoice', $paramList);

        $result = $this->xmlrpc_call('delete_invoice', $paramList);

        return ($result == 'ok') ? true : false;
    }

    /**
     * Reserves a purchase amount for a specific customer. <br>
     * The reservation is valid, by default, for 7 days.<br>
     *
     * <b>This method returns an array with</b>:<br>
     * A reservation number (rno)<br>
     * Order status flag<br>
     *
     * <b>Order status can be</b>:<br>
     * {@link KlarnaFlags::ACCEPTED}<br>
     * {@link KlarnaFlags::PENDING}<br>
     * {@link KlarnaFlags::DENIED}<br>
     *
     * <b>Please note</b>:<br>
     * Activation must be done with activate_reservation, i.e. you cannot activate through Klarna Online.<br>
     *
     * Gender is only required for Germany and Netherlands.<br>
     *
     * <b>Flags can be set to</b>:<br>
     * {@link KlarnaFlags::NO_FLAG}<br>
     * {@link KlarnaFlags::TEST_MODE}<br>
     * {@link KlarnaFlags::RSRV_SENSITIVE_ORDER}<br>
     * {@link KlarnaFlags::RSRV_PHONE_TRANSACTION}<br>
     * {@link KlarnaFlags::RSRV_SEND_PHONE_PIN}<br>
     *
     * Some flags can be added to each other for multiple options.
     *
     * <b>Note</b>:<br>
     * Normal shipment type is assumed unless otherwise specified, you can do this by calling:<br>
     * {@link Klarna::setShipmentInfo() Klarna::setShipmentInfo('delay_adjust', ...)}<br>
     * with either: {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}<br>
     *
     * @link http://integration.klarna.com/en/api/advanced-integration/functions/reserveamount
     * @param  string  $pno        Personal number, SSN, date of birth, etc.
     * @param  int     $gender     {@link KlarnaFlags::FEMALE} or {@link KlarnaFlags::MALE}, null for unspecified.
     * @param  int     $amount     Amount to be reserved, including VAT.
     * @param  int     $flags      Options which affect the behaviour.
     * @param  int     $pclass     {@link KlarnaPClass::getId() PClass ID}.
     * @param  int     $encoding   {@link KlarnaEncoding PNO Encoding} constant.
     * @param  bool    $clear      Whether customer info should be cleared after this call.
     * @throws KlarnaException
     * @return array   An array with reservation number and order status. [string, int]
     */
    public function reserveAmount($pno, $gender, $amount, $flags = 0, $pclass = KlarnaPClass::INVOICE, $encoding = null, $clear = true) {
        if(!is_int($this->country) || !is_int($this->language) || !is_int($this->currency)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': You must set country, language and currency!', 50023);
        }

        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);
        if($gender === 'm') {
            $gender = KlarnaFlags::MALE;
        }
        else if($gender === 'f') {
            $gender = KlarnaFlags::FEMALE;
        }
        if($gender !== null) {
            $this->checkInt($gender, 'gender', __METHOD__);
        }
        $this->checkInt($flags,  'flags',  __METHOD__);
        $this->checkInt($pclass, 'pclass', __METHOD__);

        if(!is_array($this->goodsList) || empty($this->goodsList)) {
            throw new KlarnaException("Error in " . __METHOD__ . ": No articles in goods list!", 50038);
        }

        //Calculate automatically the amount from goodsList.
        if($amount === -1) {
            $amount = 0;
            foreach($this->goodsList as $goods) {
                $amount += floatval($goods['goods']['price']) * intval($goods['qty']);
            }

            if(is_numeric($amount) && !is_int($amount)) {
                $amount = intval($amount);
            }
            if(!is_numeric($amount) || !is_int($amount)) {
                throw new KlarnaException("Error in " . __METHOD__ . ": Price not an integer! ($amount)", 50039);
            }
        }
        else {
            $this->checkAmount($amount, __METHOD__);
        }

        if($amount <= 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ": Amount needs to be larger than 0! ($amount)", 50040);
        }

        //No addresses used for phone transactions
        $billing = $shipping = '';
        if( !($flags & KlarnaFlags::RSRV_PHONE_TRANSACTION) ) {
            $billing = $this->assembleAddr(__METHOD__, $this->billing);
            $shipping = $this->assembleAddr(__METHOD__, $this->shipping);

            if($shipping['country'] !== $this->country) {
                throw new KlarnaException('Error in ' . __METHOD__ . ': Shipping address country must match the country set!', 50041);
            }
        }

        //Assume normal shipment unless otherwise specified.
        if(!isset($this->shipInfo['delay_adjust'])) {
            $this->setShipmentInfo('delay_adjust', KlarnaFlags::NORMAL_SHIPMENT);
        }

        //Make sure we get any session ID's or similar
        $this->initCheckout($this, $this->eid);

        $digestSecret = self::digest($this->colon($this->eid, $pno, $amount, $this->secret));

        $paramList = array(
                $pno, $gender, $amount, $this->reference, $this->reference_code, $this->orderid[0], $this->orderid[1],
                $shipping, $billing, $this->getClientIP(), $flags, $this->currency, $this->country, $this->language,
                $this->eid, $digestSecret, $encoding, $pclass, $this->goodsList, $this->comment,
                $this->shipInfo, $this->travelInfo, $this->incomeInfo, $this->bankInfo, $this->sid, $this->extraInfo
        );

        self::printDebug('reserve_amount', $paramList);

        $result = $this->xmlrpc_call('reserve_amount', $paramList);

        if($clear === true) {
            //Make sure any stored values that need to be unique between purchases are cleared.
            foreach($this->coObjects as $co) {
                $co->clear();
            }
            $this->clear();
        }

        self::printDebug('reserve_amount result', $result);

        return $result;
    }

    /**
     * Cancels a reservation.
     *
     * @link http://integration.klarna.com/en/api/advanced-integration/functions/cancelreservation
     * @param  string  $rno  Reservation number.
     * @throws KlarnaException
     * @return bool    True, if the cancellation was successful.
     */
    public function cancelReservation($rno) {
        $this->checkRNO($rno, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $rno, $this->secret));
        $paramList = array($rno, $this->eid, $digestSecret);

        self::printDebug('cancel_reservation', $paramList);

        $result = $this->xmlrpc_call('cancel_reservation', $paramList);

        return ($result == 'ok') ? true : false;
    }

    /**
     * Changes specified reservation to a new amount.
     *
     * <b>Flags can be either of these</b>:<br>
     * {@link KlarnaFlags::NEW_AMOUNT}<br>
     * {@link KlarnaFlags::ADD_AMOUNT}<br>
     *
     * @link http://integration.klarna.com/en/api/advanced-integration/functions/changereservation
     * @param  string  $rno     Reservation number.
     * @param  int     $amount  Amount including VAT.
     * @param  int     $flags   Options which affect the behaviour.
     * @throws KlarnaException
     * @return bool    True, if the change was successful.
     */
    public function changeReservation($rno, $amount, $flags = KlarnaFlags::NEW_AMOUNT) {
        $this->checkRNO($rno, __METHOD__);
        $this->checkAmount($amount, __METHOD__);
        $this->checkInt($flags, 'flags', __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $rno, $amount, $this->secret));
        $paramList = array($rno, $amount, $this->eid, $digestSecret, $flags);

        self::printDebug('change_reservation', $paramList);

        $result = $this->xmlrpc_call('change_reservation', $paramList);

        return ($result  == 'ok') ? true : false;
    }


    /**
     * Activates a previously created reservation.
     *
     * <b>This method returns an array with</b>:<br>
     * Risk status ("no_risk", "ok")<br>
     * Invoice number<br>
     *
     * Gender is only required for Germany and Netherlands.<br>
     *
     * Use of the OCR parameter is optional, a OCR number can be retrieved by using:<br>
     * {@link Klarna::reserveOCR()} or {@link Klarna::reserveOCRemail()}.<br>
     *
     * <b>Flags can be set to</b>:<br>
     * {@link KlarnaFlags::NO_FLAG}<br>
     * {@link KlarnaFlags::TEST_MODE}<br>
     * {@link KlarnaFlags::RSRV_SEND_BY_MAIL}<br>
     * {@link KlarnaFlags::RSRV_SEND_BY_EMAIL}<br>
     * {@link KlarnaFlags::RSRV_PRESERVE_RESERVATION}<br>
     * {@link KlarnaFlags::RSRV_SENSITIVE_ORDER}<br>
     *
     * Some flags can be added to each other for multiple options.
     *
     * <b>Note</b>:<br>
     * Normal shipment type is assumed unless otherwise specified, you can do this by calling:<br>
     * {@link Klarna::setShipmentInfo() Klarna::setShipmentInfo('delay_adjust', ...)}<br>
     * with either: {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}<br>
     *
     * @see Klarna::reserveAmount()
     *
     * @link http://integration.klarna.com/en/api/advanced-integration/functions/activatereservation
     * @param  string  $pno        Personal number, SSN, date of birth, etc.
     * @param  string  $rno        Reservation number.
     * @param  int     $gender     {@link KlarnaFlags::FEMALE} or {@link KlarnaFlags::MALE}, null for unspecified.
     * @param  string  $ocr        A OCR number.
     * @param  int     $flags      Options which affect the behaviour.
     * @param  int     $pclass     {@link KlarnaPClass::getId() PClass ID}.
     * @param  int     $encoding   {@link KlarnaEncoding PNO Encoding} constant.
     * @param  bool    $clear      Whether customer info should be cleared after this call.
     * @throws KlarnaException
     * @return array   An array with risk status and invoice number [string, string].
     */
    public function activateReservation($pno, $rno, $gender, $ocr = "", $flags = KlarnaFlags::NO_FLAG, $pclass = KlarnaPClass::INVOICE, $encoding = null, $clear = true) {
        if(!is_int($this->country) || !is_int($this->language) || !is_int($this->currency)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': You must set country, language and currency!', 50023);
        }

        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);
        $this->checkRNO($rno, __METHOD__);
        if($gender !== null) {
            $this->checkInt($gender, 'gender', __METHOD__);
        }
        $this->checkOCR($ocr, __METHOD__);
        $this->checkRef($this->reference, $this->reference_code, __METHOD__);

        if(!is_array($this->goodsList) || empty($this->goodsList)) {
            throw new KlarnaException("Error in " . __METHOD__ . ": No articles in goods list!", 50043);
        }

        //No addresses used for phone transactions
        $billing = $shipping = '';
        if( !($flags & KlarnaFlags::RSRV_PHONE_TRANSACTION) ) {
            $billing = $this->assembleAddr(__METHOD__, $this->billing);
            $shipping = $this->assembleAddr(__METHOD__, $this->shipping);

            if($shipping['country'] !== $this->country) {
                throw new KlarnaException('Error in ' . __METHOD__ . ': Shipping address country must match the country set!', 50044);
            }
        }

        //activate digest
        $string = $this->eid . ":" . $pno . ":";
        if(is_array($this->goodsList)) {
            foreach($this->goodsList as $goods) {
                $string .= $goods["goods"]["artno"] . ":" . $goods["qty"] . ":";
            }
        }
        $digestSecret = self::digest($string . $this->secret);
        //end digest

        //Assume normal shipment unless otherwise specified.
        if(!isset($this->shipInfo['delay_adjust'])) {
            $this->setShipmentInfo('delay_adjust', KlarnaFlags::NORMAL_SHIPMENT);
        }

        $paramList = array(
                $rno, $ocr, $pno, $gender, $this->reference, $this->reference_code, $this->orderid[0], $this->orderid[1],
                $shipping, $billing, $this->getClientIP(), $flags, $this->currency, $this->country, $this->language,
                $this->eid, $digestSecret, $encoding, $pclass, $this->goodsList, $this->comment,
                $this->shipInfo, $this->travelInfo, $this->incomeInfo, $this->bankInfo, $this->extraInfo
        );

        self::printDebug('activate_reservation', $paramList);

        $result = $this->xmlrpc_call('activate_reservation', $paramList);

        if($clear === true) {
            $this->clear();
        }

        self::printDebug('activate_reservation result', $result);

        return $result;
    }


    /**
     * Splits a reservation due to for example outstanding articles.
     *
     * <b>For flags usage see</b>:<br>
     * {@link Klarna::reserveAmount()}<br>
     *
     * @link http://integration.klarna.com/en/api/advanced-integration/functions/splitreservation
     * @param  string     $rno      Reservation number.
     * @param  int        $amount   The amount to be subtracted from the reservation.
     * @param  int        $flags    Options which affect the behaviour.
     * @throws KlarnaException
     * @return string     A new reservation number.
     */
    public function splitReservation($rno, $amount, $flags = KlarnaFlags::NO_FLAG) {
        //Check so required information is set.
        $this->checkRNO($rno, __METHOD__);
        $this->checkAmount($amount, __METHOD__);

        if($amount <= 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ": Amount needs to be above 0!", 50045);
        }

        $digestSecret = self::digest($this->colon($this->eid, $rno, $amount, $this->secret));
        $paramList = array($rno, $amount, $this->orderid[0], $this->orderid[1], $flags, $this->eid, $digestSecret);

        self::printDebug('split_reservation array', $paramList);

        $result = $this->xmlrpc_call('split_reservation', $paramList);

        self::printDebug('split_reservation result', $result);

        return $result;
    }

    /**
     * Reserves a specified number of OCR numbers.<br>
     * For the specified country or the {@link Klarna::setCountry() set country}.<br>
     *
     * @link http://integration.klarna.com/en/api/advanced-integration/functions/reserveocrnums
     * @param  int   $no       The number of OCR numbers to reserve.
     * @param  int   $country  {@link KlarnaCountry} constant.
     * @throws KlarnaException
     * @return array An array of OCR numbers.
     */
    public function reserveOCR($no, $country = null) {
        $this->checkNo($no, __METHOD__);
        if($country === null) {
            if(!$this->country) {
                throw new KlarnaException('Error in' . __METHOD__ . ': You must set country first!', 50046);
            }
            $country = $this->country;
        }
        else {
            $this->checkCountry($country, __METHOD__);
        }

        $digestSecret = self::digest($this->colon($this->eid, $no, $this->secret));
        $paramList = array($no, $this->eid, $digestSecret, $country);

        self::printDebug('reserve_ocr_nums array', $paramList);

        return $this->xmlrpc_call('reserve_ocr_nums', $paramList);
    }

    /**
     * Reserves the number of OCRs specified and sends them to the given email.
     *
     * @param  int     $no       Number of OCR numbers to reserve.
     * @param  string  $email    Email address.
     * @param  int     $country  {@link KlarnaCountry} constant.
     * @return bool    True, if the OCRs were reserved and sent.
     */
    public function reserveOCRemail($no, $email, $country = null) {
        $this->checkNo($no, __METHOD__);
        $this->checkPNO($email, KlarnaEncoding::EMAIL, __METHOD__);
        if($country === null) {
            if(!$this->country) {
                throw new KlarnaException('Error in' . __METHOD__ . ': You must set country first!', 50046);
            }
            $country = $this->country;
        }
        else {
            $this->checkCountry($country, __METHOD__);
        }

        $digestSecret = self::digest($this->colon($this->eid, $no, $this->secret));
        $paramList = array($no, $email, $this->eid, $digestSecret, $country);

        self::printDebug('reserve_ocr_nums_email array', $paramList);

        $result = $this->xmlrpc_call('reserve_ocr_nums_email', $paramList);

        return ($result == 'ok');
    }

    /**
     * Checks if the specified SSN/PNO has an part payment account with Klarna.
     *
     * @link http://integration.klarna.com/en/api/standard-integration/functions/hasaccount
     * @param  string  $pno       Social security number, Personal number, ...
     * @param  int     $encoding  {@link KlarnaEncoding PNO Encoding} constant.
     * @throws KlarnaException
     * @return bool    True, if customer has an account.
     */
    public function hasAccount($pno, $encoding = null) {
        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);

        $digest = self::digest($this->colon($this->eid, $pno, $this->secret));
        $paramList = array($this->eid, $pno, $digest, $encoding);

        self::printDebug('has_account', $paramList);

        $result = $this->xmlrpc_call('has_account', $paramList);

        return ($result === 'true') ? true : false;
    }

    /**
     * Adds an article number and quantity to be used in {@link Klarna::activatePart()}, {@link Klarna::creditPart()} and {@link Klarna::invoicePartAmount()}.
     *
     * @link http://integration.klarna.com/en/api/invoice-handling-functions/functions/mkartno
     * @param  int     $qty    Quantity of specified article.
     * @param  string  $artNo  Article number.
     * @throws KlarnaException
     * @return void
     */
    public function addArtNo($qty, $artNo) {
        $this->checkQty($qty, __METHOD__);
        $this->checkArtNo($artNo, __METHOD__);

        if(!is_array($this->artNos)) {
            $this->artNos = array();
        }

        $this->artNos[] = array('artno' => $artNo, 'qty' => $qty);
    }

    /**
     * Partially activates a passive invoice.
     *
     * Returned array contains index "url" and "invno".<br>
     * The value of "url" is a URL pointing to a temporary PDF-version of the activated invoice.<br>
     * The value of "invno" is either 0 if the entire invoice was activated or the number on the new passive invoice.<br>
     *
     * <b>Note</b>:<br>
     * You need to call {@link Klarna::addArtNo()} first, to specify which articles and how many you want to partially activate.<br><br>
     * If you want to change the shipment type, you can specify it using:<br>
     * {@link Klarna::setShipmentInfo() Klarna::setShipmentInfo('delay_adjust', ...)}<br>
     * with either: {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}<br>
     *
     * @see Klarna::addArtNo()
     * @see Klarna::activateInvoice()
     *
     * @link http://integration.klarna.com/en/api/standard-integration/functions/activatepart
     * @param  string  $invNo   Invoice numbers.
     * @param  int     $pclass  PClass id used for this invoice.
     * @param  bool    $clear   Whether customer info should be cleared after this call.
     * @throws KlarnaException
     * @return array   An array with invoice URL and invoice number. ['url' => val, 'invno' => val]
     */
    public function activatePart($invNo, $pclass = KlarnaPClass::INVOICE, $clear = true) {
        $this->checkInvNo($invNo, __METHOD__);
        $this->checkArtNos($this->artNos, __METHOD__);

        self::printDebug('activate_part artNos array', $this->artNos);

        //function activate_part_digest
        $string = $this->eid . ":" . $invNo . ":";
        foreach($this->artNos as $artNo) {
            $string .= $artNo["artno"] . ":". $artNo["qty"] . ":";
        }
        $digestSecret = self::digest($string . $this->secret);
        //end activate_part_digest

        $paramList = array($this->eid, $invNo, $this->artNos, $digestSecret, $pclass, $this->shipInfo);

        self::printDebug('activate_part array', $paramList);

        $result = $this->xmlrpc_call('activate_part', $paramList);

        if($clear === true) {
            $this->clear();
        }

        self::printDebug('activate_part result', $result);

        return $result;
    }

    /**
     * Retrieves the total amount for an active invoice.
     *
     * @link http://integration.klarna.com/en/api/other-functions/functions/invoiceamount
     * @param  string  $invNo  Invoice number.
     * @throws KlarnaException
     * @return float   The total amount.
     */
    public function invoiceAmount($invNo) {
        $this->checkInvNo($invNo, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $invNo, $this->secret));
        $paramList = array($this->eid, $invNo, $digestSecret);

        self::printDebug('invoice_amount array', $paramList);

        $result = $this->xmlrpc_call('invoice_amount', $paramList);

        //Result is in cents, fix it.
        return ($result / 100);
    }

    /**
     * Changes the order number of a purchase that was set when the order was made online.
     *
     * @link http://integration.klarna.com/en/api/other-functions/functions/updateorderno
     * @param  string  $invNo    Invoice number.
     * @param  string  $orderid  Estores order number.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function updateOrderNo($invNo, $orderid) {
        $this->checkInvNo($invNo, __METHOD__);
        $this->checkEstoreOrderNo($orderid, __METHOD__);

        $digestSecret = self::digest($this->colon($invNo, $orderid, $this->secret));
        $paramList = array($this->eid, $digestSecret, $invNo, $orderid);

        self::printDebug('update_orderno array', $paramList);

        $result = $this->xmlrpc_call('update_orderno', $paramList);

        return $result;
    }

    /**
     * Sends an activated invoice to the customer via e-mail. <br>
     * The email is sent in plain text format and contains a link to a PDF-invoice.<br>
     *
     * <b>Please note!</b><br>
     * Regular postal service is used if the customer has not entered his/her e-mail address when making the purchase (charges may apply).<br>
     *
     * @link http://integration.klarna.com/en/api/invoice-handling-functions/functions/emailinvoice
     * @param  string  $invNo  Invoice number.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function emailInvoice($invNo) {
        $this->checkInvNo($invNo, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $invNo, $this->secret));
        $paramList = array($this->eid, $invNo, $digestSecret);

        self::printDebug('email_invoice array', $paramList);

        $result = $this->xmlrpc_call('email_invoice', $paramList);

        return $result;
    }

    /**
     * Requests a postal send-out of an activated invoice to a customer by Klarna (charges may apply).
     *
     * @link http://integration.klarna.com/en/api/invoice-handling-functions/functions/sendinvoice
     * @param  string  $invNo  Invoice number.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function sendInvoice($invNo) {
        $this->checkInvNo($invNo, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $invNo, $this->secret));
        $paramList = array($this->eid, $invNo, $digestSecret);

        self::printDebug('send_invoice array', $paramList);

        $result = $this->xmlrpc_call('send_invoice', $paramList);

        return $result;
    }

    /**
     * Gives discounts on invoices.<br>
     * If you are using standard integration and the purchase is not yet activated (you have not yet delivered the goods), <br>
     * just change the article list in our online interface Klarna Online.<br>
     *
     * <b>Flags can be</b>:<br>
     * {@link KlarnaFlags::INC_VAT}<br>
     * {@link KlarnaFlags::NO_FLAG}, <b>NOT RECOMMENDED!</b><br>
     *
     * @link http://integration.klarna.com/en/api/invoice-handling-functions/functions/returnamount
     * @param  string  $invNo   Invoice number.
     * @param  int     $amount  The amount given as a discount.
     * @param  float   $vat     VAT in percent, e.g. 22.2 for 22.2%.
     * @param  int     $flags   If amount is {@link KlarnaFlags::INC_VAT including} or {@link KlarnaFlags::NO_FLAG excluding} VAT.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function returnAmount($invNo, $amount, $vat, $flags = KlarnaFlags::INC_VAT) {
        $this->checkInvNo($invNo, __METHOD__);
        $this->checkAmount($amount, __METHOD__);
        $this->checkVAT($vat, __METHOD__);
        $this->checkInt($flags, 'flags', __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $invNo, $this->secret));
        $paramList = array($this->eid, $invNo, $amount, $vat, $digestSecret, $flags);

        self::printDebug('return_amount', $paramList);

        $result = $this->xmlrpc_call('return_amount', $paramList);

        return $result;
    }

    /**
     * Performs a complete refund on an invoice, part payment and mobile purchase.
     *
     * @link http://integration.klarna.com/en/api/invoice-handling-functions/functions/creditinvoice
     * @param  string  $invNo   Invoice number.
     * @param  string  $credNo  Credit number.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function creditInvoice($invNo, $credNo = "") {
        $this->checkInvNo($invNo, __METHOD__);
        $this->checkCredNo($credNo, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $invNo, $this->secret));
        $paramList = array($this->eid, $invNo, $credNo, $digestSecret);

        self::printDebug('credit_invoice', $paramList);

        $result = $this->xmlrpc_call('credit_invoice', $paramList);

        return $result;
    }

    /**
     * Performs a partial refund on an invoice, part payment or mobile purchase.<br>
     *
     * <b>Note</b>:<br>
     * You need to call {@link Klarna::addArtNo()} first.<br>
     *
     * @see  Klarna::addArtNo()
     *
     * @link http://integration.klarna.com/en/api/invoice-handling-functions/functions/creditpart
     * @param  string  $invNo   Invoice number.
     * @param  string  $credNo  Credit number.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function creditPart($invNo, $credNo = "") {
        $this->checkInvNo($invNo, __METHOD__);
        $this->checkCredNo($credNo, __METHOD__);
        $this->checkArtNos($this->artNos, __METHOD__);

        //function activate_part_digest
        $string = $this->eid . ":" . $invNo . ":";
        foreach($this->artNos as $artNo) {
            $string .= $artNo["artno"] . ":". $artNo["qty"] . ":";
        }
        $digestSecret = self::digest($string . $this->secret);
        //end activate_part_digest

        $paramList = array($this->eid, $invNo, $this->artNos, $credNo, $digestSecret);
        $this->artNos = array();

        self::printDebug('credit_part', $paramList);

        $result = $this->xmlrpc_call('credit_part', $paramList);

        return $result;
    }

    /**
     * Changes the quantity of a specific item in a passive invoice.
     *
     * @link http://integration.klarna.com/en/api/other-functions/functions/updategoodsqty
     * @param  string  $invNo  Invoice number.
     * @param  string  $artNo  Article number.
     * @param  int     $qty    Quantity of specified article.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function updateGoodsQty($invNo, $artNo, $qty) {
        $this->checkInvNo($invNo, __METHOD__);
        $this->checkQty($qty, __METHOD__);
        $this->checkArtNo($artNo, __METHOD__);

        $digestSecret = self::digest($this->colon($invNo, $artNo, $qty, $this->secret));
        $paramList = array($this->eid, $digestSecret, $invNo, $artNo, $qty);

        self::printDebug('update_goods_qty', $paramList);

        $result = $this->xmlrpc_call('update_goods_qty', $paramList);

        return $result;
    }

    /**
     * Changes the amount of a fee (e.g. the invoice fee) in a passive invoice.
     *
     * <b>Type can be</b>:<br>
     * {@link KlarnaFlags::IS_SHIPMENT}<br>
     * {@link KlarnaFlags::IS_HANDLING}<br>
     *
     *
     * @link http://integration.klarna.com/en/api/other-functions/functions/updatechargeamount
     * @param  string  $invNo      Invoice number.
     * @param  int     $type       Charge type.
     * @param  int     $newAmount  The new amount for the charge.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function updateChargeAmount($invNo, $type, $newAmount) {
        $this->checkInvNo($invNo, __METHOD__);
        $this->checkInt($type, 'type', __METHOD__);
        $this->checkAmount($newAmount, __METHOD__);

        if($type === KlarnaFlags::IS_SHIPMENT) {
            $type = 1;
        }
        else if($type === KlarnaFlags::IS_HANDLING) {
            $type = 2;
        }

        $digestSecret = self::digest($this->colon($invNo, $type, $newAmount, $this->secret));
        $paramList = array($this->eid, $digestSecret, $invNo, $type, $newAmount);

        self::printDebug('update_charge_amount', $paramList);

        $result = $this->xmlrpc_call('update_charge_amount', $paramList);

        return $result;
    }

    /**
     * The invoice_address function is used to retrieve the address of a purchase.
     *
     *
     *
     * @link http://integration.klarna.com/en/api/other-functions/functions/invoiceaddress
     * @param  string  $invNo  Invoice number.
     * @throws KlarnaException
     * @return KlarnaAddr
     */
    public function invoiceAddress($invNo) {
        $this->checkInvNo($invNo, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $invNo, $this->secret));
        $paramList = array($this->eid, $invNo, $digestSecret);

        self::printDebug('invoice_address', $paramList);

        $result = $this->xmlrpc_call('invoice_address', $paramList);

        $addr = new KlarnaAddr();
        if(strlen($result[0]) > 0) {
           $addr->isCompany = false;
           $addr->setFirstName($result[0]);
           $addr->setLastName($result[1]);
        }
        else {
           $addr->isCompany = true;
           $addr->setCompanyName($result[1]);
        }
        $addr->setStreet($result[2]);
        $addr->setZipCode($result[3]);
        $addr->setCity($result[4]);
        $addr->setCountry($result[5]);

        return $addr;
    }

    /**
     * Retrieves the amount of a specific goods from a purchase.
     *
     * <b>Note</b>:<br>
     * You need to call {@link Klarna::addArtNo()} first.<br>
     *
     * @see  Klarna::addArtNo()
     *
     * @link http://integration.klarna.com/en/api/other-functions/functions/invoicepartamount
     * @param  string  $invNo   Invoice number.
     * @throws KlarnaException
     * @return float   The amount of the goods.
     */
    public function invoicePartAmount($invNo) {
        $this->checkInvNo($invNo, __METHOD__);
        $this->checkArtNos($this->artNos, __METHOD__);

        //function activate_part_digest
        $string = $this->eid . ":" . $invNo . ":";
        foreach($this->artNos as $artNo) {
            $string .= $artNo["artno"] . ":". $artNo["qty"] . ":";
        }
        $digestSecret = self::digest($string . $this->secret);
        //end activate_part_digest

        $paramList = array($this->eid, $invNo, $this->artNos, $digestSecret);
        $this->artNos = array();

        self::printDebug('invoice_part_amount', $paramList);

        $result = $this->xmlrpc_call('invoice_part_amount', $paramList);

        return ($result / 100);
    }

    /**
     * Returns the current order status for a specific reservation or invoice.<br>
     * Use this when {@link Klarna::addTransaction()} or {@link Klarna::reserveAmount()} returns a {@link KlarnaFlags::PENDING} status.<br>
     *
     * <b>Order status can be</b>:<br>
     * {@link KlarnaFlags::ACCEPTED}<br>
     * {@link KlarnaFlags::PENDING}<br>
     * {@link KlarnaFlags::DENIED}<br>
     *
     * @link http://integration.klarna.com/en/api/other-functions/functions/checkorderstatus
     * @param  string  $id    Reservation number or invoice number.
     * @param  int     $type  0, if $id is an invoice or reservation, 1 for order id.
     * @throws KlarnaException
     * @return string  The order status.
     */
    public function checkOrderStatus($id, $type = 0) {
        if(!is_string($id)) {
            $id = strval($id);
        }
        if(strlen($id) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ": No ID set!", 50048);
        }

        $this->checkInt($type, 'type', __METHOD__);
        if($type !== 0 && $type !== 1) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Unknown type! ('.$type.')', 50049);
        }

        $digestSecret = self::digest($this->colon($this->eid, $id, $this->secret));
        $paramList = array($this->eid, $digestSecret, $id, $type);

        self::printDebug('check_order_status', $paramList);

        $result = $this->xmlrpc_call('check_order_status', $paramList);

        return $result;
    }

    /**
     * Retrieves a list of all the customer numbers associated with the specified pno.
     *
     * @param  string  $pno       Social security number, Personal number, ...
     * @param  int     $encoding  {@link KlarnaEncoding PNO Encoding} constant.
     * @throws KlarnaException
     * @return array   An array containing all customer numbers associated with that pno.
     */
    public function getCustomerNo($pno, $encoding = null) {
        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);

        $digestSecret = self::digest($this->colon($this->eid, $pno, $this->secret));
        $paramList = array($pno, $this->eid, $digestSecret, $encoding);

        self::printDebug('get_customer_no', $paramList);

        $result = $this->xmlrpc_call('get_customer_no', $paramList);

        return $result;
    }

    /**
     * Associates a pno with a customer number when you want to make future purchases without a pno.
     *
     * @param  string  $pno       Social security number, Personal number, ...
     * @param  string  $custNo    The customer number.
     * @param  int     $encoding  {@link KlarnaEncoding PNO Encoding} constant.
     * @throws KlarnaException
     * @return bool    True, if the customer number was associated with the pno.
     */
    public function setCustomerNo($pno, $custNo, $encoding = null) {
        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);

        if(!is_string($custNo)) {
            $custNo = strval($custNo);
        }
        if(strlen($custNo) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ": No customer number set!", 50050);
        }

        $digestSecret = self::digest($this->colon($this->eid, $pno, $custNo, $this->secret));
        $paramList = array($pno, $custNo, $this->eid, $digestSecret, $encoding);

        self::printDebug('set_customer_no', $paramList);

        $result = $this->xmlrpc_call('set_customer_no', $paramList);

        return ($result == 'ok');
    }

    /**
     * Removes a customer number from association with a pno.
     *
     * @param  string  $custNo  The customer number.
     * @throws KlarnaException
     * @return bool    True, if the customer number association was removed.
     */
    public function removeCustomerNo($custNo) {
        if(!is_string($custNo)) {
            $custNo = strval($custNo);
        }
        if(strlen($custNo) == 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ": No customer number set!", 50051);
        }

        $digestSecret = self::digest($this->colon($this->eid, $custNo, $this->secret));
        $paramList = array($custNo, $this->eid, $digestSecret);

        self::printDebug('remove_customer_no', $paramList);

        $result = $this->xmlrpc_call('remove_customer_no', $paramList);

        return ($result == 'ok');
    }

    /**
     * Updates email on all invoices (and reservations?) for specified pno and store/eid.
     *
     * @param  string  $pno    Social security number, Personal number, ...
     * @param  string  $email  Email address.
     * @return bool    True, if the email was successfully updated for specified pno.
     */
    public function updateEmail($pno, $email) {
        //check $pno how? which encoding?
        $this->checkPNO($email, KlarnaEncoding::EMAIL, __METHOD__);

        $digestSecret = self::digest($this->colon($pno, $email, $this->secret));
        $paramList = array($this->eid, $digestSecret, $pno, $email);

        self::printDebug('update_email', $paramList);

        $result = $this->xmlrpc_call('update_email', $paramList);

        return ($result == 'ok');
    }

    /**
     * Sets notes/log information for the specified invoice  number.
     *
     * @param  string  $invNo  Invoice number.
     * @param  string  $notes  Note(s) to be associated with the invoice.
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function updateNotes($invNo, $notes) {
        $this->checkInvNo($invNo, __METHOD__);

        if(!is_string($notes)) {
            $notes = strval($notes);
        }

        $digestSecret = self::digest($this->colon($invNo, $notes, $this->secret));
        $paramList = array($this->eid, $digestSecret, $invNo, $notes);

        self::printDebug('update_notes', $paramList);

        return $this->xmlrpc_call('update_notes', $paramList);
    }

    /**
     * Returns the specified PCStorage object.
     *
     * @ignore Do not show this in PHPDoc.
     * @throws Exception|KlarnaException
     * @return PCStorage
     */
    protected function getPCStorage() {
        require_once('pclasses/storage.intf.php');
        $className = $this->pcStorage.'storage';
        $pclassStorage = dirname(__FILE__).'/pclasses/'.$className.'.class.php';
        require_once($pclassStorage);
        $storage = new $className;
        if(!($storage instanceof PCStorage)) {
            throw new Exception($className . ' located in ' . $pclassStorage . ' is not a PCStorage instance.', 50052);
        }
        return $storage;
    }

    /**
     * Fetches the PClasses from backend.<br>
     * Removes the cached/stored pclasses and updates.<br>
     * You are only allowed to call this once, or once per update of PClasses in KO.<br>
     *
     * <b>Note</b>:<br>
     * If all parameters are omitted or null, all PClasses (for all countries) will be fetched.<br>
     * If language and/or currency is null, then they will be set to mirror the specified country.<br/>
     * Short codes like DE, SV or EUR can also be used instead of the constants.<br/>
     *
     * @param  string|int  $country   {@link KlarnaCountry Country} constant, or two letter code.
     * @param  mixed       $language  {@link KlarnaLanguage Language} constant, or two letter code.
     * @param  mixed       $currency  {@link KlarnaCurrency Currency} constant, or three letter code.
     * @throws KlarnaException
     * @return void
     */
    public function fetchPClasses($country = null, $language = null, $currency = null) {
        $countries = array();
        if($country === null && $language === null && $currency === null) {
            $countries = array(
                array(
                    'country' => KlarnaCountry::DE,
                    'language' => KlarnaLanguage::DE,
                    'currency' => KlarnaCurrency::EUR
                ),array(
                    'country' => KlarnaCountry::DK,
                    'language' => KlarnaLanguage::DA,
                    'currency' => KlarnaCurrency::DKK
                ),array(
                    'country' => KlarnaCountry::FI,
                    'language' => KlarnaLanguage::FI,
                    'currency' => KlarnaCurrency::EUR
                ),array(
                    'country' => KlarnaCountry::NL,
                    'language' => KlarnaLanguage::NL,
                    'currency' => KlarnaCurrency::EUR
                ),array(
                    'country' => KlarnaCountry::NO,
                    'language' => KlarnaLanguage::NB,
                    'currency' => KlarnaCurrency::NOK
                ),array(
                    'country' => KlarnaCountry::SE,
                    'language' => KlarnaLanguage::SV,
                    'currency' => KlarnaCurrency::SEK
                ),
            );
        }
        else {
            if(!is_numeric($country) && (strlen($country) == 2 || strlen($country) == 3)) {
                $country = self::getCountryForCode($country);
            }
            $this->checkCountry($country, __METHOD__);

            if($currency === null) {
                $currency = $this->getCurrencyForCountry($country);
            }
            else if(!is_numeric($currency) && strlen($currency) == 3) {
                $currency = self::getCurrencyForCode($currency);
            }
            $this->checkCurrency($currency, __METHOD__);

            if($language === null) {
                $language = $this->getLanguageForCountry($country);
            }
            else if(!is_numeric($language) && strlen($language) == 2) {
                $language = self::getLanguageForCode($language);
            }
            $this->checkLanguage($language, __METHOD__);

            $countries = array(
                array(
                    'country' => $country,
                    'language' => $language,
                    'currency' => $currency
                )
            );
        }

        try {
            if($this->config instanceof ArrayAccess) {
                $pclasses = $this->getPCStorage();
                try {
                    //Attempt to load previously stored, so they aren't accidentially removed.
                    $pclasses->load($this->pcURI);
                }
                catch(Exception $e) {
                    //Silently fail
                }
                catch(KlarnaException $e) {
                    //Silently fail
                }

                foreach($countries as $item) {
                    $digestSecret = self::digest($this->colon($this->eid, $item['currency'], $this->secret));
                    $paramList = array($this->eid, $item['currency'], $digestSecret, $item['country'], $item['language']);

                    self::printDebug('get_pclasses array', $paramList);

                    $result = $this->xmlrpc_call('get_pclasses', $paramList);

                    self::printDebug('get_pclasses result', $result);

                    foreach($result as &$pclass) {
                        //numeric htmlentities
                        $pclass[1] = Klarna::num_htmlentities($pclass[1]);

                        //Below values are in "cents", fix them.
                        $pclass[3] /= 100; //divide start fee with 100
                        $pclass[4] /= 100; //divide invoice fee with 100
                        $pclass[5] /= 100; //divide interest rate with 100
                        $pclass[6] /= 100; //divide min amount with 100

                        if($pclass[9] != '-') {
                            $pclass[9] = strtotime($pclass[9]); //unix timestamp instead of yyyy-mm-dd
                        }

                        array_unshift($pclass, $this->eid); //Associate the PClass with this estore.

                        $pclasses->addPClass(new KlarnaPClass($pclass));
                    }
                }

                $pclasses->save($this->pcURI);
                $this->pclasses = $pclasses;
            }
            else {
                throw new Exception('Klarna instance not fully configured!', 50001);
            }
        }
        catch(Exception $e) {
            $this->pclasses = null;
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Removes the stored PClasses, if you need to update them.
     *
     * @throws KlarnaException
     * @return void
     */
    public function clearPClasses() {
        if($this->config instanceof ArrayAccess) {
            $pclasses = $this->getPCStorage();
            $pclasses->clear($this->pcURI);
        }
        else {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Klarna instance not fully configured!', 50001);
        }
    }

    /**
     * Retrieves the specified PClasses.
     *
     * <b>Type can be</b>:<br>
     * {@link KlarnaPClass::CAMPAIGN}<br>
     * {@link KlarnaPClass::ACCOUNT}<br>
     * {@link KlarnaPClass::SPECIAL}<br>
     * {@link KlarnaPClass::FIXED}<br>
     * {@link KlarnaPClass::DELAY}<br>
     * {@link KlarnaPClass::MOBILE}<br>
     *
     * @param  int   $type  PClass type identifier.
     * @throws KlarnaException
     * @return array An array of PClasses. [KlarnaPClass]
     */
    public function getPClasses($type = null) {
        try {
            if(!($this->config instanceof ArrayAccess)) {
                throw new Exception('Klarna instance not fully configured!', 50001);
            }
            if(!$this->pclasses) {
                $this->pclasses = $this->getPCStorage();
                $this->pclasses->load($this->pcURI);
            }
            $tmp = $this->pclasses->getPClasses($this->eid, $this->country, $type);
            $this->sortPClasses($tmp[$this->eid]);
            return $tmp[$this->eid];
        }
        catch(Exception $e) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns the specified PClass.
     *
     * @param  int  $id  The PClass ID.
     * @return KlarnaPClass
     */
    public function getPClass($id) {
        try {
            if(!is_numeric($id)) {
                throw new Exception('Argument id is not an integer!', 50055);
            }
            else if(!is_int($id)) {
                $id = intval($id);
            }

            if(!($this->config instanceof ArrayAccess)) {
                throw new Exception('Klarna instance not fully configured!', 50001);
            }
            if(!$this->pclasses || !($this->pclasses instanceof PCStorage)) {
                $this->pclasses = $this->getPCStorage();
                $this->pclasses->load($this->pcURI);
            }
            return $this->pclasses->getPClass($id, $this->eid, $this->country);
        }
        catch(Exception $e) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Sorts the specified array of KlarnaPClasses.
     *
     * @param  array &$array An array of {@link KlarnaPClass PClasses}.
     * @return void
     */
    public function sortPClasses(&$array) {
        if(!is_array($array)) {
            //Input is not an array!
            $array = array();
            return;
        }
        //Sort pclasses array after natural sort (natcmp)
        if(!function_exists('pc_cmp')) {
            function pc_cmp($a, $b) {
                if($a->getDescription() == null && $b->getDescription() == null) {
                    return 0;
                }
                else if($a->getDescription() == null) {
                    return 1;
                }
                else if($b->getDescription() == null) {
                    return -1;
                }
                else if($b->getType() === 2 && $a->getType() !== 2) {
                    return 1;
                }
                else if($b->getType() !== 2 && $a->getType() === 2) {
                    return -1;
                }

                return strnatcmp($a->getDescription(), $b->getDescription())*-1;
            }
        }
        usort($array, "pc_cmp");
    }

    /**
     * Returns the cheapest, per month, PClass related to the specified sum.
     *
     * <b>Note</b>: This choose the cheapest PClass for the current country.<br>
     * {@link Klarna::setCountry()}
     *
     * <b>Flags can be</b>:<br>
     * {@link KlarnaFlags::CHECKOUT_PAGE}<br>
     * {@link KlarnaFlags::PRODUCT_PAGE}<br>
     *
     * @param  float  $sum       The product cost, or total sum of the cart.
     * @param  int    $flags     Which type of page the info will be displayed on.
     * @throws KlarnaException
     * @return KlarnaPClass or false if none was found.
     */
    public function getCheapestPClass($sum, $flags) {
        if (!is_numeric ($sum)) {
            throw new KlarnaException(
                'Error in ' . __METHOD__ . ': Argument sum is not numeric!');
        }

        if(!is_numeric ($flags) || !in_array ($flags,
                array(KlarnaFlags::CHECKOUT_PAGE, KlarnaFlags::PRODUCT_PAGE))) {
            throw new KlarnaException(
                'Error in ' . __METHOD__ . ': Flags argument invalid!');
        }

        $lowest_pp = $lowest = false;
        foreach($this->getPClasses() as $pclass) {
            $lowest_payment = KlarnaCalc::get_lowest_payment_for_account($pclass->getCountry());
            if($pclass->getType() < 2 && $sum >= $pclass->getMinAmount()) {
                $minpay = KlarnaCalc::calc_monthly_cost($sum, $pclass, $flags);

                if($minpay < $lowest_pp || $lowest_pp === false) {
                    if($pclass->getType() == KlarnaPClass::ACCOUNT || $minpay >= $lowest_payment) {
                        $lowest_pp = $minpay;
                        $lowest = $pclass;
                    }
                }
            }
        }

        return $lowest;
    }

    /**
     * Initializes the checkoutHTML objects.
     *
     * @see Klarna::checkoutHTML()
     * @return void
     */
    protected function initCheckout() {
        $dir = dirname(__FILE__);

        //Require the CheckoutHTML interface/abstract class
        require_once($dir.'/checkout/checkouthtml.intf.php');

        //Iterate over all .class.php files in checkout/
        foreach(glob($dir.'/checkout/*.class.php') as $checkout) {
            if(!self::$debug) ob_start();
            include_once($checkout);
            $className = basename($checkout, '.class.php');
            $cObj = new $className;
            if($cObj instanceof CheckoutHTML) {
                $cObj->init($this, $this->eid);
                $this->coObjects[$className] = $cObj;
            }
            if(!self::$debug) ob_end_clean();
        }
    }

    /**
     * Returns the checkout page HTML from the checkout classes.
     *
     * <b>Note</b>:<br>
     * This method uses output buffering to silence unwanted echoes.<br>
     *
     * @see CheckoutHTML
     *
     * @return string  A HTML string.
     */
    public function checkoutHTML() {
        if(empty($this->coObjects)) {
            $this->initCheckout();
        }
        $dir = dirname(__FILE__);

        //Require the CheckoutHTML interface/abstract class
        require_once($dir.'/checkout/checkouthtml.intf.php');

        //Iterate over all .class.php files in
        $html = "\n";
        foreach($this->coObjects as $cObj) {
            if(!self::$debug) ob_start();
            if($cObj instanceof CheckoutHTML) {
                $html .= $cObj->toHTML() . "\n";
            }
            if(!self::$debug) ob_end_clean();
        }

        return $html;
    }

    /**
     * Creates a XMLRPC call with specified XMLRPC method and parameters from array.
     *
     * @ignore Do not show this in PHPDoc.
     * @param  string  $method  XMLRPC method.
     * @param  array   $array   XMLRPC parameters.
     * @throws KlarnaException
     * @return mixed
     */
    protected function xmlrpc_call($method, $array) {
        try {
            if(!($this->xmlrpc instanceof xmlrpc_client)) {
                throw new Exception('Klarna instance not fully configured!', 50001);
            }
            if(!isset($method) || !is_string($method)) {
                throw new Exception("Argument 'method' not a string!", 50066);
            }
            if(!$array || count($array) === 0) {
                throw new Exception("Array empty or null!", 50067);
            }
            if(self::$disableXMLRPC) {
                return true;
            }

            /*
             * Disable verifypeer for CURL, so below error is avoided.
             * CURL error: SSL certificate problem, verify that the CA cert is OK.
             * Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed (#8)
             */
            $this->xmlrpc->verifypeer = false;

            $timestart = microtime(true);

            //Create the XMLRPC message.
            $msg = new xmlrpcmsg($method);
            $params = array_merge(array($this->PROTO, $this->VERSION), $array);

            $msg = new xmlrpcmsg($method);
            foreach($params as $p) {
                if(!$msg->addParam(php_xmlrpc_encode($p, array('extension_api')))) {
                    throw new Exception("Failed to add parameters to XMLRPC message.", 50068);
                }
            }

            //Send the message.
            $selectDateTime = microtime(true);
            if(self::$xmlrpcDebug) $this->xmlrpc->setDebug(2);
            $xmlrpcresp = $this->xmlrpc->send($msg);

            //Calculate time and selectTime.
            $timeend = microtime(true);
            $time = (int) (($selectDateTime - $timestart) * 1000);
            $selectTime = (int) (($timeend - $timestart) * 1000);

            $status = $xmlrpcresp->faultCode();

            //Send report to candice.
            if(self::$candice === true) {
                $this->sendStat($method, $time, $selectTime, $status);
            }

            if($status !== 0) {
                throw new KlarnaException($xmlrpcresp->faultString(), $status);
            }

            return php_xmlrpc_decode($xmlrpcresp->value());
        }
        catch(KlarnaException $e) {
            //Otherwise it is catched below, and error in, is prepended.
            throw $e;
        }
        catch(Exception $e) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Removes all relevant order/customer data from the internal structure.
     *
     * @return void
     */
    public function clear() {
        $this->goodsList = null;
        $this->comment = "";

        $this->billing = null;
        $this->shipping = null;

        $this->shipInfo = array();
        $this->extraInfo = array();
        $this->bankInfo = array();
        $this->incomeInfo = array();

        $this->reference = "";
        $this->reference_code = "";

        $this->orderid[0] = "";
        $this->orderid[1] = "";

        $this->artNos = array();
        $this->coObjects = array();
    }

    /**
     * Sends a report to Candice.
     *
     * @ignore Do not show this in PHPDoc.
     * @param  string  $function    XMLRPC method.
     * @param  int     $time        Elapsed time of entire XMLRPC call.
     * @param  int     $selectTime  Time to create the XMLRPC parameters.
     * @param  int     $status      XMLRPC error code.
     * @return void
     */
    protected function sendStat($method, $time, $selectTime, $status) {
        if(($fp = @fsockopen('udp://'.self::$c_addr, 80, $errno, $errstr, 1500))) {
            $url = (($this->ssl) ? 'https://' : 'http://').$this->addr;
            $data = $this->pipe($this->eid, $method, $time, $selectTime, $status, $url.':'.$this->port);
            $digest = self::digest($this->pipe($data, $this->secret));

            self::printDebug("candice report", $data);

            @fwrite($fp, $this->pipe($data, $digest));
            @fclose($fp);
        }
    }

    /**
     * Implodes parameters with delimiter ':'.
     *
     * @param  mixed  $field1  Data to be separated by colons.
     * @param  mixed  $field2  More data.
     * @param  mixed  $field3  More data..
     * @param  mixed  $...     More data...
     * @return string Colon separated string.
     */
    public static function colon(/* variable parameters */) {
        $args = func_get_args();
        return implode(':', $args);
    }

    /**
     * Implodes parameters with delimiter '|'.
     *
     * @param  mixed  $field1  Data to be piped together.
     * @param  mixed  $field2  More data.
     * @param  mixed  $field3  More data..
     * @param  mixed  $...     More data...
     * @return string Pipe separated string.
     */
    public static function pipe(/* variable parameters */) {
        $args = func_get_args();
        return implode('|', $args);
    }

    /**
     * Creates a digest hash from the inputted string,
     * and the specified or the preferred hash algorithm.
     *
     * @param  string  $data Data to be hashed.
     * @throws KlarnaException
     * @return string  Base64 encoded hash.
     */
    public static function digest($data, $hash = null) {
        if($hash===null) {
            $preferred = array(
                'sha512',
                'sha384',
                'sha256',
                'sha224',
                'md5'
            );

            $hashes = array_intersect($preferred, hash_algos());
            if(count($hashes) == 0) {
                throw new KlarnaException("Error in " . __METHOD__ . ": No available hash algorithm supported!");
            }
            $hash = array_shift($hashes);
        }
        self::printDebug(__METHOD__.' using hash', $hash);

        return base64_encode(pack("H*", hash($hash, $data)));
    }

    /**
     * Converts special characters to numeric htmlentities.
     *
     * <b>Note</b>:<br>
     * If supplied string is encoded with UTF-8, o umlaut ("�") will become two HTML entities instead of one.
     *
     * @param  string  $str  String to be converted.
     * @return string  String converted to numeric HTML entities.
     */
    public static function num_htmlentities($str) {
        if(!self::$htmlentities) {
            self::$htmlentities = array();
            foreach(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES) as $char => $entity) {
                self::$htmlentities[$entity] = '&#' . ord($char) . ';';
            }
        }

        return str_replace(array_keys(self::$htmlentities), self::$htmlentities, htmlentities($str));
    }

    /**
     * Prints debug information if debug is set to true.
     * $msg is used as header/footer in the output.
     *
     * if FirePHP is available it will be used instead of
     * dumping the debug info into the document.
     *
     * It uses print_r and encapsulates it in HTML/XML comments.
     * (<!-- -->)
     *
     * @param  string  $msg    Debug identifier, e.g. "my array".
     * @param  mixed   $mixed  Object, type, etc, to be debugged.
     * @return void
     */
    public static function printDebug($msg, $mixed) {
        if(self::$debug) {
            if(class_exists('FB', false)) {
                FB::send($mixed, $msg);
            } else {
                echo "\n<!-- ".$msg.": \n";
                print_r($mixed);
                echo "\n end ".$msg." -->\n";
            }
        }
    }

    /**
     * Checks/fixes so the invNo input is valid.
     *
     * @param  string  &$invNo   Invoice number.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkInvNo(&$invNo, $method) {
        if(!isset($invNo)) {
            throw new KlarnaException("Error in " . $method . ": Invoice number not set!", 50055);
        }
        if(!is_string($invNo)) {
            $invNo = strval($invNo);
        }
        if(strlen($invNo) == 0) {
            throw new KlarnaException("Error in " . $method . ": Invoice number not set!", 50056);
        }
    }

    /**
     * Checks/fixes so the quantity input is valid.
     *
     * @param  int     &$qty     Quantity.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkQty(&$qty, $method) {
        if(!isset($qty)) {
            throw new KlarnaException("Error in " . $method . ": Quantity is not set!", 50057);
        }
        if(is_numeric($qty) && !is_int($qty)) {
            $qty = intval($qty);
        }
        if(!is_int($qty)) {
            throw new KlarnaException("Error in " . $method . ": Quantity is not an integer! ($qty)", 50058);
        }
    }

    /**
     * Checks/fixes so the artTitle input is valid.
     *
     * @param  string  &$artTitle  Article title.
     * @param  string  $method     __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkArtTitle(&$artTitle, $method) {
        if(!is_string($artTitle)) {
            $artTitle = strval($artTitle);
        }
        if(!isset($artTitle) || strlen($artTitle) == 0) {
            throw new KlarnaException("Error in " . $method . ": No artTitle specified!", 50059);
        }
    }

    /**
     * Checks/fixes so the artNo input is valid.
     *
     * @param  int|string  &$artNo  Article number.
     * @param  string      $method  __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkArtNo(&$artNo, $method) {
        if(is_numeric($artNo) && !is_string($artNo)) {
            //Convert artNo to string if integer.
            $artNo = strval($artNo);
        }
        if(!isset($artNo) || strlen($artNo) == 0 || (!is_string($artNo))) {
            throw new KlarnaException("Error in " . $method . ": No artNo specified! ($artNo)", 50060);
        }
    }

    /**
     * Checks/fixes so the credNo input is valid.
     *
     * @param  string  &$credNo  Credit number.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkCredNo(&$credNo, $method) {
        if(!isset($credNo)) {
            throw new KlarnaException("Error in " . $method . ": Credit number not set!", 50061);
        }

        if($credNo === false || $credNo === null) {
            $credNo = "";
        }
        if(!is_string($credNo)) {
            $credNo = strval($credNo);
            if(!is_string($credNo)) {
                throw new KlarnaException("Error in " . $method . ": Credit number couldn't be converted to string! ($credNo)", 50062);
            }
        }
    }

    /**
     * Checks so that artNos is an array and is not empty.
     *
     * @param  array   &$artNos Array from {@link Klarna::addArtNo()}.
     * @param  string  $method  __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkArtNos(&$artNos, $method) {
        if(!is_array($artNos)) {
            throw new KlarnaException("Error in " . $method . ": No artNos array specified!", 50063);
        }
        if(empty($artNos)) {
            throw new KlarnaException('Error in ' . $method . ': ArtNo array is empty!', 50064);
        }
    }

    /**
     * Checks/fixes so the integer input is valid.
     *
     * @param  int     &$int    {@link KlarnaFlags flags} constant.
     * @param  string  $field   Name of the field.
     * @param  string  $method  __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkInt(&$int, $field, $method) {
        if(!isset($int)) {
            throw new KlarnaException("Error in " . $method . ": $field is not set!", 50065);
        }
        if(is_numeric($int) && !is_int($int)) {
            $int = intval($int);
        }
        if(!is_numeric($int) || !is_int($int)) {
            throw new KlarnaException("Error in " . $method . ": $field not an integer! ($int)", 50066);
        }
    }

    /**
     * Checks/fixes so the VAT input is valid.
     *
     * @param  float   &$vat     VAT.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkVAT(&$vat, $method) {
        if(!isset($vat)) {
            throw new KlarnaException("Error in " . $method . ": VAT is not set!", 50067);
        }
        if(is_numeric($vat) && (!is_int($vat) || !is_float($vat))) {
            $vat = floatval($vat);
        }
        if(!is_numeric($vat) || (!is_int($vat) && !is_float($vat))) {
            throw new KlarnaException("Error in " . $method . ": VAT not an integer or float! ($vat)", 50068);
        }
    }

    /**
     * Checks/fixes so the amount input is valid.
     *
     * @param  int     &$amount  Amount.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkAmount(&$amount, $method) {
        if(!isset($amount)) {
            throw new KlarnaException("Error in " . $method . ": Amount not set!", 50069);
        }
        if(is_numeric($amount)) {
            $this->fixValue($amount);
        }
        if(is_numeric($amount) && !is_int($amount)) {
            $amount = intval($amount);
        }
        if(!is_numeric($amount) || !is_int($amount)) {
            throw new KlarnaException("Error in " . $method . ": Amount not an integer! ($amount)", 50070);
        }
    }

    /**
     * Checks/fixes so the price input is valid.
     *
     * @param  int     &$price   Price.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkPrice(&$price, $method) {
        if(!isset($price)) {
            throw new KlarnaException("Error in " . $method . ": Price is not set!", 50071);
        }
        if(is_numeric($price)) {
            $this->fixValue($price);
        }
        if(is_numeric($price) && !is_int($price)) {
            $price = intval($price);
        }
        if(!is_numeric($price) || !is_int($price)) {
            throw new KlarnaException("Error in " . $method . ": Price not an integer! ($price)", 50072);
        }
    }

    /**
     * Multiplies value with 100 and rounds it.
     * This fixes value/price/amount inputs so that KO can handle them.
     *
     * @param  float  &$value
     * @return void
     */
    private function fixValue(&$value) {
        $value = round($value * 100);
    }

    /**
     * Checks/fixes so the discount input is valid.
     *
     * @param  float   &$discount  Discount amount.
     * @param  string  $method     __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkDiscount(&$discount, $method) {
        if(!isset($discount)) {
            throw new KlarnaException("Error in " . $method . ": Discount is not set!", 50073);
        }
        if(is_numeric($discount) && (!is_int($discount) || !is_float($discount))) {
            $discount = floatval($discount);
        }
        if(!is_numeric($discount) || (!is_int($discount) && !is_float($discount))) {
            throw new KlarnaException("Error in " . $method . ": Discount not an integer or float! ($discount)", 50074);
        }
    }

    /**
     * Checks/fixes so that the estoreOrderNo input is valid.
     *
     * @param  string  &$estoreOrderNo  Estores order number.
     * @param  string  $method          __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkEstoreOrderNo(&$estoreOrderNo, $method) {
        if(!isset($estoreOrderNo)) {
            throw new KlarnaException("Error in " . $method . ": Order number not set!", 50075);
        }
        if(!is_string($estoreOrderNo)) {
            $estoreOrderNo = strval($estoreOrderNo);
            if(!is_string($estoreOrderNo)) {
                throw new KlarnaException("Error in " . $method . ": Order number couldn't be converted to string! ($estoreOrderNo)", 50076);
            }
        }
    }

    /**
     * Checks/fixes to the PNO/SSN input is valid.
     *
     * @param  string  &$pno    Personal number, social security  number, ...
     * @param  int     $enc     {@link KlarnaEncoding PNO Encoding} constant.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkPNO(&$pno, $enc, $method) {
        if(!$pno) {
            throw new KlarnaException("Error in " . $method . ": PNO/SSN not set for customer or given as a parameter!", 50077);
        }

        if(!KlarnaEncoding::checkPNO($pno, $enc)) {
            throw new KlarnaException("Error in " . $method . ": PNO/SSN is not valid!", 50078);
        }
    }

    /**
     * Checks/fixes to the country input is valid.
     *
     * @param  int     &$country  {@link KlarnaCountry Country} constant.
     * @param  string  $method    __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkCountry(&$country, $method) {
        if(!isset($country)) {
            throw new KlarnaException("Error in " . $method . ": Country argument not set!", 50079);
        }
        if(is_numeric($country) && !is_int($country)) {
            $country = intval($country);
        }
        if(!is_numeric($country) || !is_int($country)) {
            throw new KlarnaException("Error in " . $method . ": Country must be an integer! ($country)", 50080);
        }
    }

    /**
     * Checks/fixes to the language input is valid.
     *
     * @param  int     &$language  {@link KlarnaLanguage Language} constant.
     * @param  string  $method     __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkLanguage(&$language, $method) {
        if(!isset($language)) {
            throw new KlarnaException("Error in " . $method . ": Language argument not set!", 50081);
        }
        if(is_numeric($language) && !is_int($language)) {
            $language = intval($language);
        }
        if(!is_numeric($language) || !is_int($language)) {
            throw new KlarnaException("Error in " . $method . ": Language must be an integer! ($language)", 50082);
        }
    }

    /**
     * Checks/fixes to the currency input is valid.
     *
     * @param  int     &$currency  {@link KlarnaCurrency Currency} constant.
     * @param  string  $method     __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkCurrency(&$currency, $method) {
        if(!isset($currency)) {
            throw new KlarnaException("Error in " . $method . ": Currency argument not set!", 50083);
        }
        if(is_numeric($currency) && !is_int($currency)) {
            $currency = intval($currency);
        }
        if(!is_numeric($currency) || !is_int($currency)) {
            throw new KlarnaException("Error in " . $method . ": Currency must be an integer! ($currency)", 50084);
        }
    }

    /**
     * Checks/fixes so no/number is a valid input.
     *
     * @param  int     &$no      Number.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkNo(&$no, $method) {
        if(!isset($no)) {
            throw new KlarnaException("Error in " . $method . ": Argument no not set!", 50085);
        }
        if(is_numeric($no) && !is_int($no)) {
            $no = intval($no);
        }
        if(!is_numeric($no) || !is_int($no) || $no <= 0) {
            throw new KlarnaException("Error in " . $method . ": Argument no must be an integer and above 0! ($no)", 50086);
        }
    }

    /**
     * Checks/fixes so reservation number is a valid input.
     *
     * @param  string  &$rno     Reservation number.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkRNO(&$rno, $method) {
        if(!is_string($rno)) {
            $rno = strval($rno);
        }
        if(strlen($rno) == 0) {
            throw new KlarnaException("Error in " . $method . ": RNO isn't set!", 50087);
        }
    }

    /**
     * Checks/fixes so that reference/refCode are valid.
     *
     * @param  string  &$reference   Reference string.
     * @param  string  &$refCode     Reference code.
     * @param  string  $method       __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkRef(&$reference, &$refCode, $method) {
        if(!is_string($reference)) {
            $reference = strval($reference);
            if(!is_string($reference)) {
                throw new KlarnaException("Error in " . $method . ": Reference couldn't be converted to string! ($reference)", 50088);
            }
        }

        if(!is_string($refCode)) {
            $refCode = strval($refCode);
            if(!is_string($refCode)) {
                throw new KlarnaException("Error in " . $method . ": Reference code couldn't be converted to string! ($refCode)", 50089);
            }
        }
    }

    /**
     * Checks/fixes so that the OCR input is valid.
     *
     * @param  string  &$ocr     OCR number.
     * @param  string  $method   __METHOD__
     * @throws KlarnaException
     * @return void
     */
    private function checkOCR(&$ocr, $method) {
        if(!is_string($ocr)) {
            $ocr = strval($ocr);
            if(!is_string($ocr)) {
                throw new KlarnaException("Error in " . $method . ": OCR couldn't be converted to string or isn't a string! ($ocr)", 50090);
            }
        }
    }

} //End Klarna


/**
 * Provides encoding constants.
 *
 * @package KlarnaAPI
 */
class KlarnaEncoding {

    /**
     * PNO/SSN encoding for Sweden.
     *
     * @var int
     */
    const PNO_SE = 2;

    /**
     * PNO/SSN encoding for Norway.
     *
     * @var int
     */
    const PNO_NO = 3;

    /**
     * PNO/SSN encoding for Finland.
     *
     * @var int
     */
    const PNO_FI = 4;

    /**
     * PNO/SSN encoding for Denmark.
     *
     * @var int
     */
    const PNO_DK = 5;

    /**
     * PNO/SSN encoding for Germany.
     *
     * @var int
     */
    const PNO_DE = 6;

    /**
     * PNO/SSN encoding for Netherlands.
     *
     * @var int
     */
    const PNO_NL = 7;

    /**
     * Encoding constant for customer numbers.
     *
     * @see Klarna::setCustomerNo()
     * @var int
     */
    const CUSTNO = 1000;

    /**
     * Encoding constant for email address.
     *
     * @var int
     */
    const EMAIL = 1001;

    /**
     * Encoding constant for cell numbers.
     *
     * @var int
     */
    const CELLNO = 1002;

    /**
     * Encoding constant for bank bic + account number.
     *
     * @var int
     */
    const BANK_BIC_ACC_NO = 1003;

    /**
     * Returns a regexp string for the specified encoding constant.
     *
     * @param  int    $enc    PNO/SSN encoding constant.
     * @return string The regular expression.
     * @throws KlarnaException
     */
    public static function getRegexp($enc) {
        switch($enc) {
            /**
             * All positions except C contain numbers 0-9.
             *
             * PNO:
             * YYYYMMDDCNNNN, C = -|+  length 13
             * YYYYMMDDNNNN                   12
             * YYMMDDCNNNN                    11
             * YYMMDDNNNN                     10
             *
             * ORGNO:
             * XXXXXXNNNN
             * XXXXXX-NNNN
             * 16XXXXXXNNNN
             * 16XXXXXX-NNNN
             *
             */
            case self::PNO_SE:
                return '/^[0-9]{6,6}(([0-9]{2,2}[-\+]{1,1}[0-9]{4,4})|([-\+]{1,1}[0-9]{4,4})|([0-9]{4,6}))$/';
                break;

            /**
             * All positions contain numbers 0-9.
             *
             * Pno
             * DDMMYYIIIKK    ("fodelsenummer" or "D-nummer") length = 11
             * DDMMYY-IIIKK   ("fodelsenummer" or "D-nummer") length = 12
             * DDMMYYYYIIIKK  ("fodelsenummer" or "D-nummer") length = 13
             * DDMMYYYY-IIIKK ("fodelsenummer" or "D-nummer") length = 14
             *
             * Orgno
             * Starts with 8 or 9.
             *
             * NNNNNNNNK      (orgno)                         length = 9
             */
            case self::PNO_NO:
                return '/^[0-9]{6,6}((-[0-9]{5,5})|([0-9]{2,2}((-[0-9]{5,5})|([0-9]{1,1})|([0-9]{3,3})|([0-9]{5,5))))$/';
                break;

            /**
             * Pno
             * DDMMYYCIIIT
             * DDMMYYIIIT
             * C = century, '+' = 1800, '-' = 1900 och 'A' = 2000.
             * I = 0-9
             * T = 0-9, A-F, H, J, K-N, P, R-Y
             *
             * Orgno
             * NNNNNNN-T
             * NNNNNNNT
             * T = 0-9, A-F, H, J, K-N, P, R-Y
             */
            case self::PNO_FI:
                return '/^[0-9]{6,6}(([A\+-]{1,1}[0-9]{3,3}[0-9A-FHJK-NPR-Y]{1,1})|([0-9]{3,3}[0-9A-FHJK-NPR-Y]{1,1})|([0-9]{1,1}-{0,1}[0-9A-FHJK-NPR-Y]{1,1}))$/i';
                break;

            /**
             * Pno
             * DDMMYYNNNG       length 10
             * G = gender, odd/even for men/women.
             *
             * Orgno
             * XXXXXXXX         length 8
             */
            case self::PNO_DK:
                return '/^[0-9]{8,8}([0-9]{2,2})?$/';
                break;

            /**
             * Pno
             * DDMMYYYYG         length 9
             * DDMMYYYY                 8
             *
             * Orgno
             * XXXXXXX                  7  company org nr
             */
            case self::PNO_NL:
            case self::PNO_DE:
                return '/^[0-9]{7,9}$/';
                break;

            /**
             * Validates an email.
             */
            case self::EMAIL:
                return '/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z0-9-][a-zA-Z0-9-]+)+$/';
                break;

            /**
             * Validates a cellno.
             *
             */
            case self::CELLNO:
                return '/^07[\ \-0-9]{8,13}$/';
                break;

            default:
                throw new KlarnaException('Error in ' . __METHOD__ . ': Unknown PNO/SSN encoding constant! ('.$enc.')', 50091);
        }
    }

    /**
     * Checks if the specified PNO is correct according to specified encoding constant.
     *
     * @param  string $pno  PNO/SSN string.
     * @param  int    $enc  {@link KlarnaEncoding PNO/SSN encoding} constant.
     * @return bool   True if correct.
     * @throws KlarnaException
     */
    public static function checkPNO($pno, $enc) {
        $regexp = self::getRegexp($enc);

        if($regexp === false) {
            return true;
        }
        else {
            return (preg_match($regexp, $pno)) ? true : false;
        }
    }

    /**
     * Class constructor.
     * Disable instantiation.
     */
    private function __construct() {

    }

} //End KlarnaEncoding


/**
 * Provides flags/constants used for various methods.
 *
 * @package KlarnaAPI
 */
class KlarnaFlags {

    /**
     * Specifies that no flag is to be used.
     *
     * @var int
     */
    const NO_FLAG = 0;

//Gender flags
    /**
     * Indicates that the person is a female.<br>
     * Use "" or null when unspecified.<br>
     *
     * @var int
     */
    const FEMALE = 0;

    /**
     * Indicates that the person is a male.<br>
     * Use "" or null when unspecified.<br>
     *
     * @var int
     */
    const MALE = 1;

//Order status constants
    /**
     * This signifies that the invoice or reservation is accepted.
     *
     * @var int
     */
    const ACCEPTED = 1;

    /**
     * This signifies that the invoice or reservation is pending, will be set to accepted or denied.
     *
     * @var int
     */
    const PENDING = 2;

    /**
     * This signifies that the invoice or reservation is <b>denied</b>.
     *
     * @var int
     */
    const DENIED = 3;

//Get_address constants
    /**
     * A code which indicates that all first names should be returned with the address.<br>
     *
     * Formerly refered to as GA_OLD.
     *
     * @var int
     */
    const GA_ALL = 1;

    /**
     * A code which indicates that only the last name should be returned with the address.<br>
     *
     * Formerly referd to as GA_NEW.
     *
     * @var int
     */
    const GA_LAST = 2;

    /**
     * A code which indicates that the given name should be returned with the address.
     * If no given name is registered, this will behave as {@link KlarnaFlags::GA_ALL GA_ALL}.
     *
     */
    const GA_GIVEN = 5;

//Article/goods constants
    /**
     * Quantity measured in 1/1000s.
     *
     * @var int
     */
    const PRINT_1000 = 1;

    /**
     * Quantity measured in 1/100s.
     *
     * @var int
     */
    const PRINT_100 = 2;

    /**
     * Quantity measured in 1/10s.
     *
     * @var int
     */
    const PRINT_10 = 4;

    /**
     * Indicates that the item is a shipment fee.
     *
     * Update_charge_amount (1)
     *
     * @var int
     */
    const IS_SHIPMENT = 8;

    /**
     * Indicates that the item is a handling fee.
     *
     * Update_charge_amount (2)
     *
     * @var int
     */
    const IS_HANDLING = 16;

    /**
     * Article price including VAT.
     *
     * @var int
     */
    const INC_VAT = 32;

//Miscellaneous
    /**
     * Signifies that this is to be displayed in the checkout.<br>
     * Used for part payment.<br>
     *
     * @var int
     */
    const CHECKOUT_PAGE = 0;

    /**
     * Signifies that this is to be displayed in the product page.<br>
     * Used for part payment.<br>
     *
     * @var int
     */
    const PRODUCT_PAGE = 1;

    /**
     * Signifies that the specified address is billing address.
     *
     * @var int
     */
    const IS_BILLING = 100;

    /**
     * Signifies that the specified address is shipping address.
     *
     * @var int
     */
    const IS_SHIPPING = 101;

//Invoice and Reservation
    /**
     * Indicates that the purchase is a test invoice/part payment.
     *
     * @var int
     */
    const TEST_MODE = 2;

    /**
     * PClass id/value for invoices.
     *
     * @see KlarnaPClass::INVOICE.
     * @var int
     */
    const PCLASS_INVOICE = -1;

//Invoice
    /**
     * Activates an invoices automatically, requires setting in Klarna Online.
     *
     * If you designate this flag an invoice is created directly in the active state,
     * i.e. Klarna will buy the invoice immediately.
     *
     * @var int
     */
    const AUTO_ACTIVATE = 1;

    /**
     * Creates a pre-pay invoice.
     *
     * @var int
     */
    const PRE_PAY = 8;

    /**
     * Used to flag a purchase as sensitive order.
     *
     * @var int
     */
    const SENSITIVE_ORDER = 1024;

    /**
     * Used to return an array with long and short ocr number.
     *
     * @see Klarna::addTransaction()
     * @var int
     */
    const RETURN_OCR = 8192;

    /**
     * Specifies the shipment type as normal.
     *
     * @var int
     */
    const NORMAL_SHIPMENT = 1;

    /**
     * Specifies the shipment type as express.
     *
     * @var int
     */
    const EXPRESS_SHIPMENT = 2;

//Mobile (Invoice) flags
    /**
     * Marks the transaction as Klarna mobile.
     *
     * @var int
     */
    const M_PHONE_TRANSACTION = 262144;

    /**
     * Sends a pin code to the phone sent in pno.
     *
     * @var int
     */
    const M_SEND_PHONE_PIN = 524288;

//Reservation flags
    /**
     * Signifies that the amount specified is the new amount.
     *
     * @var int
     */
    const NEW_AMOUNT = 0;

    /**
     * Signifies that the amount specified is to be added.
     *
     * @var int
     */
    const ADD_AMOUNT = 1;

    /**
     * Sends the invoice by mail when activating a reservation.
     *
     * @var int
     */
    const RSRV_SEND_BY_MAIL = 4;

    /**
     * Sends the invoice by e-mail when activating a reservation.
     *
     * @var int
     */
    const RSRV_SEND_BY_EMAIL = 8;

    /**
     * Used for partial deliveries, this flag saves the reservation number so it can be used again.
     *
     * @var int
     */
    const RSRV_PRESERVE_RESERVATION = 16;

    /**
     * Used to flag a purchase as sensitive order.
     *
     * @var int
     */
    const RSRV_SENSITIVE_ORDER = 32;

    /**
     * Marks the transaction as Klarna mobile.
     *
     * @var int
     */
    const RSRV_PHONE_TRANSACTION = 512;

    /**
     * Sends a pin code to the mobile number.
     *
     * @var int
     */
    const RSRV_SEND_PHONE_PIN = 1024;

    /**
     * Class constructor.
     * Disable instantiation.
     */
    private function __construct() {

    }
}


/**
 * Provides currency constants for the supported countries.
 *
 * @package KlarnaAPI
 */
class KlarnaCurrency {

    /**
     * Currency constant for Swedish Crowns (SEK).
     *
     * @var int
     */
    const SEK = 0;

    /**
     * Currency constant for Norwegian Crowns (NOK).
     *
     * @var int
     */
    const NOK = 1;

    /**
     * Currency constant for Euro.
     *
     * @var int
     */
    const EUR = 2;

    /**
     * Currency constant for Danish Crowns (DKK).
     *
     * @var int
     */
    const DKK = 3;

    /**
     * Class constructor.
     * Disable instantiation.
     */
    private function __construct() {

    }

    /**
     * Converts a currency code, e.g. 'eur' to the KlarnaCurrency constant.
     *
     * @param  string  $val
     * @return int|null
     */
    public static function fromCode($val) {
       switch(strtolower($val)) {
            case 'dkk':
                return self::DKK;
            case 'eur':
            case 'euro':
                return self::EUR;
            case 'nok':
                return self::NOK;
            case 'sek':
                return self::SEK;
            default:
                return null;
       }
    }

    /**
     * Converts a KlarnaCurrency constant to the respective language code.
     *
     * @param  int  $val
     * @return string|null
     */
    public static function getCode($val) {
        switch($val) {
            case self::DKK:
                return 'dkk';
            case self::EUR:
                return 'eur';
            case self::NOK:
                return 'nok';
            case self::SEK:
                return 'sek';
            default:
                return null;
        }
    }

} //End KlarnaCurrency


/**
 * Provides language constants (ISO639) for the supported countries.
 *
 * @package KlarnaAPI
 */
class KlarnaLanguage {

    /**
     * Language constant for Danish (DA).<br>
     * ISO639_DA
     *
     * @var int
     */
    const DA = 27;

    /**
     * Language constant for German (DE).<br>
     * ISO639_DE
     *
     * @var int
     */
    const DE = 28;

    /**
     * Language constant for English (EN).<br>
     * ISO639_EN
     *
     * @var int
     */
    const EN = 31;

    /**
     * Language constant for Finnish (FI).<br>
     * ISO639_FI
     *
     * @var int
     */
    const FI = 37;

    /**
     * Language constant for Norwegian (NB).<br>
     * ISO639_NB
     *
     * @var int
     */
    const NB = 97;

    /**
     * Language constant for Dutch (NL).<br>
     * ISO639_NL
     *
     * @var int
     */
    const NL = 101;

    /**
     * Language constant for Swedish (SV).<br>
     * ISO639_SV
     *
     * @var int
     */
    const SV = 138;

    /**
     * Class constructor.
     * Disable instantiation.
     */
    private function __construct() {

    }

    /**
     * Converts a language code, e.g. 'de' to the KlarnaLanguage constant.
     *
     * @param  string  $val
     * @return int|null
     */
    public static function fromCode($val) {
        switch(strtolower($val)) {
            case 'en':
                return self::EN;
            case 'da':
                return self::DA;
            case 'de':
                return self::DE;
            case 'fi':
                return self::FI;
            case 'nb':
                return self::NB;
            case 'nl':
                return self::NL;
            case 'sv':
                return self::SV;
            default:
                return null;
        }
    }

    /**
     * Converts a KlarnaLanguage constant to the respective language code.
     *
     * @param  int  $val
     * @return string|null
     */
    public static function getCode($val) {
        switch($val) {
            case self::EN:
                return 'en';
            case self::DA:
                return 'da';
            case self::DE:
                return 'de';
            case self::FI:
                return 'fi';
            case self::NB:
                return 'nb';
            case self::NL:
                return 'nl';
            case self::SV:
                return 'sv';
            default:
                return null;
        }
    }

} //End KlarnaLanguage


/**
 * Provides country constants (ISO3166) for the supported countries.
 *
 * @package KlarnaAPI
 */
class KlarnaCountry {

    /**
     * Country constant for Denmark (DK).<br>
     * ISO3166_DK
     *
     * @var int
     */
    const DK = 59;

    /**
     * Country constant for Finland (FI).<br>
     * ISO3166_FI
     *
     * @var int
     */
    const FI = 73;

    /**
     * Country constant for Germany (DE).<br>
     * ISO3166_DE
     *
     * @var int
     */
    const DE = 81;

    /**
     * Country constant for Netherlands (NL).<br>
     * ISO3166_NL
     *
     * @var int
     */
    const NL = 154;

    /**
     * Country constant for Norway (NO).<br>
     * ISO3166_NO
     *
     * @var int
     */
    const NO = 164;

    /**
     * Country constant for Sweden (SE).<br>
     * ISO3166_SE
     *
     * @var int
     */
    const SE = 209;

    /**
     * Class constructor.
     * Disable instantiation.
     */
    private function __construct() {
    }

    /**
     * Converts a country code, e.g. 'de' or 'deu' to the KlarnaCountry constant.
     *
     * @param  string  $val
     * @return int|null
     */
    public static function fromCode($val) {
        switch(strtolower($val)) {
            case 'swe':
            case 'se':
                return self::SE;
            case 'nor':
            case 'no':
                return self::NO;
            case 'dnk':
            case 'dk':
                return self::DK;
            case 'fin':
            case 'fi':
                return self::FI;
            case 'deu':
            case 'de':
                return self::DE;
            case 'nld':
            case 'nl':
                return self::NL;
            default:
                return null;
        }
    }

    /**
     * Converts a KlarnaCountry constant to the respective country code.
     *
     * @param  int  $val
     * @param  bool $alpha3  Whether to return a ISO-3166-1 alpha-3 code
     * @return string|null
     */
    public static function getCode($val, $alpha3 = false) {
         switch($val) {
            case KlarnaCountry::SE:
                return ($alpha3) ? 'swe' : 'se';
            case KlarnaCountry::NO:
                return ($alpha3) ? 'nor' : 'no';
            case KlarnaCountry::DK:
                return ($alpha3) ? 'dnk' : 'dk';
            case KlarnaCountry::FI:
                return ($alpha3) ? 'fin' : 'fi';
            case KlarnaCountry::DE:
                return ($alpha3) ? 'deu' : 'de';
            case self::NL:
                return ($alpha3) ? 'nld' : 'nl';
            default:
                return null;
        }
    }

} //End KlarnaCountry


/**
 * KlarnaException class, only used so it says "KlarnaException" instead of Exception.
 *
 * @package KlarnaAPI
 */
class KlarnaException extends Exception {

    /**
     * Returns an error message readable by end customers.
     *
     * @return string
     */
    public function __toString() {
        //API error codes
        if($this->code >= 50000) {

            $message = $this->getMessage();
            return $message . " (#".$this->code.")";
        }
        else { //KO error codes
            return $this->getMessage() . " (#".$this->code.")";
        }
    }
}


/**
 * Include the {@link KlarnaConfig} class.
 */
require_once('klarnaconfig.php');

/**
 * Include the {@link KlarnaPClass} class.
 */
require_once('klarnapclass.php');

/**
 * Include the {@link KlarnaCalc} class.
 */
require_once('klarnacalc.php');

/**
 * Include the {@link KlarnaAddr} class.
 */
require_once('klarnaaddr.php');
