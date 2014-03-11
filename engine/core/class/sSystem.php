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

    public $sMODULES;		// Pointer to the different modules and its inherits

    public $sUSERGROUP;	// Current customer-group (Scope)
    public $sUSERGROUPDATA; // Information about customer-group

    public $sDEBUG;		// Array with Debug-Messages
    public $sBENCHRESULTS;	// Benchmark-results as array
    public $sBENCHMARK;	// Holds time for benchmark-purposes

    public $_GET;			// Get-Variables
    public $_POST;			// Post-Variables
    public $_COOKIE;		// Cookies
    public $_SESSION;		// Session

    // Absolute pathes
    public $sPathMedia;		// Path to template images
    public $sPathArticleImg;	// Path to article images
    public $sPathBanner;		// Path to banners
    public $sPathArticleFiles;	// Path to Article-Downloads
    public $sPathStart;		// Path to Start
    public $sBasefile;
    public $sBasePath;

    // Additionals
    public $sExtractor;		// Strip parts of rewrited urls and append them
    public $sLicenseData;		// License - Data
    public $sLanguageData;		// All active languages
    public $sLanguage;			// Current language

    public $sCurrency;			// Current active currency
    public $sCurrencyData;		// Array with active currencies

    public $sSubShop;			// Current active subshop
    public $sSubShops;			// Information about licensed subshops


    public $sMailer;			// Pointer to PHP-Mailer Object
    public $sBotSession;		// True if user is identified as bot

    public function __construct()
    {
        $this->sBasePath = dirname(dirname(dirname(dirname(__FILE__)))).'/';
    }

    /**
     * @deprecated
     */
    public function sPreProcess()
    {

    }


    /**
     * @deprecated
     */
    public function sInitMailer()
    {
        // removed mailer initialisation code
    }

    public function sGetTranslation($data,$id,$object,$language)
    {
        return $data;
    }

    public function sInitAdo()
    {
    }

    public function sTranslateConfig()
    {
    }

    public function sInitConfig()
    {

    }

    public function sInitSmarty()
    {
    }

    public function sInitSession()
    {
    }

    /**
     * DEPRECATED
     * @param $hook
     * @return string
     */
    public function sCallHookPoint($hook)
    {
        return '';
    }

    /**
     * DEPRECATED
     */
    public function sLoadHookPoints()
    {
    }

    public function sInitFactory()
    {

    }

    /**
     * DEPRECATED
     * @param null $host
     * @param null $module
     * @param $key
     * @return bool
     */
    public function sCheckLicense($host=null, $module=null, $key)
    {
        return true;
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

    public function __get($name)
    {
        switch ($name) {
            case '_d':
                return $this;
            default:
                return null;
        }
    }
}
