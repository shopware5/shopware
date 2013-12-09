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

	var $sCONFIG;		// Pointer to configuration

	var $sSESSION_ID;	// Current Session-ID

	var $sSMARTY;		// Pointer to Smarty

	var $sDB_HOST;		// Database Host
	var $sDB_USER;		// Database User
	var $sDB_PASSWORD;	// Database Password
	var $sDB_DATABASE;	// Database to use
	var $sDB_CONNECTOR;	// Database Connector ('mysql' for example)

	var $sDB_CONNECTION;// Current Connection

	var $sMODULES;		// Pointer to the different modules and its inherits

	var $sUSERGROUP;	// Current customer-group (Scope)
	var $sUSERGROUPDATA; // Information about customer-group

	var $sDEBUG;		// Array with Debug-Messages
	var $sBENCHRESULTS;	// Benchmark-results as array
	var $sBENCHMARK;	// Holds time for benchmark-purposes

	var $_GET;			// Get-Variables
	var $_POST;			// Post-Variables
	var $_COOKIE;		// Cookies
	var $_SESSION;		// Session

	// Absolute pathes
	var $sPathMedia;		// Path to template images
	var $sPathArticleImg;	// Path to article images
	var $sPathBanner;		// Path to banners
	var $sPathCmsImg;		// Path to CMS-Images
	var $sPathCmsFiles;		// Path to CMS-Files
	var $sPathArticleFiles;	// Path to Article-Downloads
	var $sPathStart;		// Path to Start
	var $sBasefile;
	var $sBasePath;

	// Additionals
	var $sExtractor;		// Strip parts of rewrited urls and append them
	var $sLicenseData;		// License - Data
	var $sLanguageData;		// All active languages
	var $sLanguage;			// Current language

	var $sCurrency;			// Current active currency
	var $sCurrencyData;		// Array with active currencies

	var $sSubShop;			// Current active subshop
	var $sSubShops;			// Information about licensed subshops


	var $sMailer;			// Pointer to PHP-Mailer Object
	var $sBotSession;		// True if user is identified as bot

	function __construct()
	{
		$this->sBasePath = dirname(dirname(dirname(dirname(__FILE__)))).'/';
	}

    /**
     * @deprecated
     */
	function sPreProcess()
	{

	}


    /**
     * @deprecated
     */
	function sInitMailer()
    {
        // removed mailer initialisation code
	}

	function sGetTranslation($data,$id,$object,$language)
    {
        return $data;
	}

	function sInitAdo()
	{
	}

	function sTranslateConfig()
	{
	}

	function sInitConfig()
	{

	}

	function sInitSmarty()
	{
	}

	function sInitSession()
	{
	}

    /**
     * DEPRECATED
     * @param $hook
     * @return string
     */
	function sCallHookPoint($hook)
	{
		return '';
	}

    /**
     * DEPRECATED
     */
	function sLoadHookPoints()
	{
	}

	function sInitFactory ()
	{

	}

    /**
     * DEPRECATED
     * @param null $host
     * @param null $module
     * @param $key
     * @return bool
     */
	function sCheckLicense($host=null, $module=null, $key)
	{
        return true;
	}

	function E_CORE_ERROR($ERROR_ID,$ERROR_MESSAGE){
		throw new Enlight_Exception($ERROR_ID.': '.$ERROR_MESSAGE);
	}

	function E_CORE_WARNING ($WARNING_ID,$WARNING_MESSAGE){
		throw new Enlight_Exception($WARNING_ID.': '.$WARNING_MESSAGE);
	}

	public function __call($name, $params=null)
	{
		return call_user_func_array(array($this->sMODULES['sCore'], $name), $params);
	}

	public function __get($name)
	{
		switch ($name)
		{
			case '_d':
				return $this;
			default:
				return null;
		}
	}
}
