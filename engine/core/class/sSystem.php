<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 */

use Shopware\Components\LegacyRequestWrapper\PostWrapper;
use Shopware\Components\LegacyRequestWrapper\GetWrapper;
use Shopware\Components\LegacyRequestWrapper\CookieWrapper;

/**
 * Deprecated Shopware Class
 */
class sSystem
{

    public $sCONFIG;		// Pointer to configuration

    public $sSESSION_ID;	// Current Session-ID

    public $sSMARTY;		// Pointer to Smarty

    public $sDB_HOST;		// Database Host
    public $sDB_USER;		// Database User
    public $sDB_PASSWORD;	// Database Password
    public $sDB_DATABASE;	// Database to use
    public $sDB_CONNECTOR;	// Database Connector ('mysql' for example)

    public $sDB_CONNECTION;// Current Connection

    /**
     * @var Shopware_Components_Modules
     */
    public $sMODULES;		// Pointer to the different modules and its inherits

    public $sUSERGROUP;	// Current customer-group (Scope)
    public $sUSERGROUPDATA; // Information about customer-group

    public $sDEBUG;		// Array with Debug-Messages
    public $sBENCHRESULTS;	// Benchmark-results as array
    public $sBENCHMARK;	// Holds time for benchmark-purposes

    /**
     * @var Enlight_Components_Session_Namespace Session data
     */
    public $_SESSION;		// Session

    /**
     * @var \Shopware\Components\LegacyRequestWrapper\PostWrapper Wrapper for _POST
     */
    private $postWrapper;

    /**
     * @var \Shopware\Components\LegacyRequestWrapper\GetWrapper Wrapper for _GET
     */
    private $getWrapper;

    /**
     * @var \Shopware\Components\LegacyRequestWrapper\CookieWrapper Wrapper for _COOKIE
     */
    private $cookieWrapper;

    // Absolute paths
    public $sPathMedia;		    // Path to template images
    public $sPathArticleImg;	// Path to article images
    public $sPathBanner;		// Path to banners
    public $sPathArticleFiles;	// Path to Article-Downloads
    public $sPathStart;		    // Path to Start
    public $sBasefile;
    public $sBasePath;

    // Additional
    public $sExtractor;		    // Strip parts of rewritten urls and append them
    public $sLicenseData;		// License - Data
    public $sLanguageData;		// All active languages
    public $sLanguage;			// Current language

    public $sCurrency;			// Current active currency
    public $sCurrencyData;		// Array with active currencies

    public $sSubShop;			// Current active subshop
    public $sSubShops;			// Information about licensed subshops

    public $sMailer;			// Pointer to PHP-Mailer Object
    public $sBotSession;		// True if user is identified as bot

    /**
     * @param Enlight_Controller_Request_RequestHttp $request The request object
     */
    public function __construct(Enlight_Controller_Request_RequestHttp $request = null)
    {
        $request = $request ? : new Enlight_Controller_Request_RequestHttp();
        $this->sBasePath = dirname(dirname(dirname(dirname(__FILE__)))).'/';
        $this->postWrapper = new PostWrapper($request);
        $this->getWrapper = new GetWrapper($request);
        $this->cookieWrapper = new CookieWrapper($request);
    }

    public function __set($property, $value)
    {
        switch ($property) {
            case '_POST':
                $this->postWrapper->setAll($value);
                break;
            case '_GET':
                $this->getWrapper->setAll($value);
                break;
        }
    }

    public function __get($property) {

        switch ($property) {
            case '_POST':
                return $this->postWrapper;
                break;
            case '_GET':
                return $this->getWrapper;
                break;
            case '_COOKIE':
                return $this->cookieWrapper;
                break;
        }
        return null;
    }

    public function E_CORE_ERROR($ERROR_ID,$ERROR_MESSAGE)
    {
        throw new Enlight_Exception($ERROR_ID.': '.$ERROR_MESSAGE);
    }

    public function E_CORE_WARNING($WARNING_ID,$WARNING_MESSAGE)
    {
        throw new Enlight_Exception($WARNING_ID.': '.$WARNING_MESSAGE);
    }

    public function __call($name, $params=null)
    {
        return call_user_func_array(array($this->sMODULES['sCore'], $name), $params);
    }
}
