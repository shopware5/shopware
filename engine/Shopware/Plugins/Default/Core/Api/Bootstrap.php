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
 *
 */
class Shopware_Plugins_Core_Api_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Api',
	 		'onInitResourceApi'
	 	);
		$this->subscribeEvent($event);
		return true;
	}

	public static function onInitResourceApi(Enlight_Event_EventArgs $args)
	{
		$api = new sAPI();

		$api->sSystem = $api;
		$api->sDB = Shopware()->Adodb();
		$api->sPath = Shopware()->OldPath();
		$api->sCONFIG = Shopware()->Config();

		return $api;
	}

    public function getVersion() {
        return '1.0.0';
    }

    public function getLabel() {
        return 'API';
    }

    public function getInfo() {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'name' => $this->getLabel(),
            'description' => 'The old Shopware Import/Export API'
        );
    }


}

class sAPI
{
    /**
     * Zugriff auf adoDB-Objekt
     * @access public
     * @var object
     */
	var $sDB;
	 /**
     * Enthält absoluten Pfad zur Shopware Installation
     * @access public
     * @var string
     */
	var $sPath;
	 /**
     * ???
     * @access public
     * @var string
     */
	var $sFiles;
	 /**
     * Zugriff auf Shopware System-Klasse
     * @access public
     * @var object
     */
	var $sSystem;
	 /**
     * Enth�lt Fehlermeldungen
     * @access public
     * @var array
     */
	var $sErrors = array();
	 /**
     * Zugriff auf verschiedene Sub-Objekte
     * @access public
     * @var array
     */
	var $sResource = array();

	var $sCONFIG;

	function Import(){
		return $this->import->shopware;
	}

	function Export(){
		return $this->export->shopware;
	}
	/**
	  * L�dt externe Daten und speichert diese in einem File-Cache
	  * Derzeit wird ausschlie�lich das HTTP Protokoll unterst�tzt
	  * @param string $url Der Pfad (inkl. Protokoll) zur Datei
	  * @access public
	  */
	function load ($url)
	{
		$url_array = parse_url($url);
		$url_array['path'] = explode("/",$url_array['path']);
		switch ($url_array['scheme']) {
			case "ftp":
			case "http":
			case "https":
			case "file":
				$hash = "";
				$dir = Shopware()->DocPath('media_' . 'temp');
				while (empty($hash)) {
					$hash = md5(uniqid(rand(), true));
					if(file_exists("$dir/$hash.api_tmp"))
						$hash = "";
				}
				if (!$put_handle = fopen("$dir/$hash.api_tmp", "w+")) {
					return false;
				}
				if (!$get_handle = fopen($url, "r")) {
					return false;
				}
				while (!feof($get_handle)) {
					fwrite($put_handle, fgets($get_handle, 4096));
				}
				fclose($get_handle);
				fclose($put_handle);
				$this->sFiles[] = $hash;
				return "$dir/$hash.api_tmp";
			default:
				break;
		}
	}

	/**
	  * Garbage-Collector
	  * Delete temp files on exit
	  * @access public
	  */
	function __destruct  ()
	{
		if(!empty($this->sFiles))
		foreach ($this->sFiles as $hash) {
			if(file_exists(Shopware()->DocPath('media_' . 'temp')."/$hash.api_tmp"))
				@unlink(Shopware()->DocPath('media_' . 'temp')."$hash.api_tmp");
		}

	}

	/**
	  * Lokales Speichern von Daten - derzeit ohne Funktion
	  * @param string $url Der Pfad (inkl. Protokoll) zur Datei
	  * @access public
	  */
	function save ($url)
	{
		$url_array = parse_url($url);
		$url_array['path'] = explode("/",$url_array['path']);
		switch ($url_array['scheme']) {
			case "ftp":
			case "http":
			case "file":
				break;
			case "post":
				break;
			case "shopware":
				break;
			case "mail":
			case "tcp":
			case "udp":
			case "php":
			default:
				break;
		}
	}

	protected $throwError = false;

	function sSetError($message, $code)
	{
		$this->sErrors[] = array('message'=>$message, 'code'=>$code);
    }

    function sGetErrors()
    {
    	return $this->sErrors;
    }

    function sGetLastError()
    {
    	return end($this->sErrors);
    }

    /**
	  * Einbinden von externen Klassen / Objekten
	 * <code>
	 * <?php
	 *	$api = new sAPI();
	 *	$export =& $api->export->shopware;	// Lädt Klasse /api/export/shopware.php
	 *	$xml =& $api->convert->xml;			// Lädt Klasse /api/convert/xml.php
	 *  $mapping =& $api->convert->mapping;	// Lädt Klasse /api/convert/mapping.php
	 *	$xml->sSettings['encoding'] = "ISO-8859-1";
	 * ?>
	 * </code>
	  * @param string $res Enth�lt den Pfad / Dateinamen des einzubindenen Objekts
	  * @access public
	  */
    function __get ($res)
    {
    	switch ($res)
	   	{
	    	case "sConvert":
	    	case "convert":
	    		$res = "convert"; break;
	    	case "sSave":
	    	case "save":
	    	case "import":
	    		$res = "import"; break;
	    	case "sLoad":
	    	case "load":
	    	case "export":
	    		$res = "export"; break;
	    		break;
	    	default:
	    		return false;
	    }
    	if(!isset($this->sResource[$res]))
		{
	    	$this->sResource[$res] = new sClassHandler($this, $res);
		}
		return $this->sResource[$res];
    }

}

// Still needed for compability reasons
class sClassHandler
{
	private $sAPI = null;
	private $sType = null;
	protected $sClass = array();

	function __construct ($sAPI, $sType)
	{
		$this->sType = $sType;
		$this->sAPI = $sAPI;
	}


	function __get  ($class)
	{
		if(!isset($this->sClass[$class]))
		{
            if($this->sType === "convert") {
                $filename = $class;
            }else{
                $filename = $this->sType;
            }
            // construct include file name
			if(!file_exists(dirname(__FILE__)."/Components/{$filename}.php"))
				return false;
			include(dirname(__FILE__)."/Components/{$filename}.php");

            // construct class name
			$name = "s".ucfirst($class).ucfirst($this->sType);
			if(class_exists($name))
				$this->sClass[$class] = new $name;
			elseif(class_exists($class))
				$this->sClass[$class] = new $class;
			else
				return false;

			$this->sClass[$class]->sSystem =& $this->sAPI->sSystem;
			$this->sClass[$class]->sDB =& $this->sAPI->sDB;
			$this->sClass[$class]->sPath =& $this->sAPI->sPath;
			$this->sClass[$class]->sAPI =& $this->sAPI;
		}
		return $this->sClass[$class];
	}

}
