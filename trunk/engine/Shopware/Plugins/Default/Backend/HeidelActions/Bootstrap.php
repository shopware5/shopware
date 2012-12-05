<?php
/**
 * Heidelpay
 *
 * @link http://www.heidelpay.de
 * @copyright Copyright (c) 2011, Heidelberger Payment AG
 * @author Tobias Eilers
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Backend_HeidelActions_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
  private static $_moduleDesc = 'HeidelActions';

  var $version	= 	"12.08" ;
  var $modulType  = 	"Standard" ;

  public function getInfo() {
  	$img = base64_encode(file_get_contents(dirname(__FILE__) . '/img/heidelpay.png'));
    return array(
      'version' => $this->version ,
      'autor' => 'Heidelberger Payment GmbH',
      'label' => "Heidelpay Actions ".$this->modulType ,
      'source' => "Community",
      'description' => '<p><img src="data:image/png;base64,' . $img . '" /></p> <p style="font-size:12px; font-weight: bold;">Heidelberger Payment GmbH - Ihr Full Service Payment Provider - alles aus einer Hand <p></p> <p style="font-size:12px">Die Heidelberger Payment GmbH kurz: heidelpay bietet als BaFin-zertifizierter Payment Service Provider alles was zum Online-Payment geh&ouml;rt.<br><br><a href="http://testshops.heidelpay.de/contactform/?campaign=shopware4.0&shop=shopware4.0" target="_blank" style="font-size: 12px; color: #000;  font-weight: bold;">&gt;&gt;&gt; Informationen anfordern &lt;&lt;&lt;</a><br/><p><br /> <p style="font-size:12px">Das Leistungsspektrum des PCI DSS zertifizierten Unternehmens reicht von weltweiten e-Payment L&ouml;sungen, inklusive eines vollst&auml;ndigen Debitorenmanagement-, Risk- und Fraud- Systems bis hin zu einem breiten Angebot alternativer Bezahlverfahren - schnell, sicher, einfach und umfassend - alles aus einer Hand.</p><br/> <a href="http://www.heidelpay.de" style="font-size: 12px; color: #000;  font-weight: bold;">www.heidelpay.de</a><br/> <br/> <p style="font-size: 12px; color: #f00";  font-weight: bold;">Hinweis:</p><p style="font-size:12px">Um unser "Heidelpay Actions Standard" Plug-in nutzen zu k&ouml;nnen, beantragen Sie bitte die Aufschaltung von push Benachrichtigungen bei unserem Technischen Support. Wenden Sie sich hierf&uuml;r bitte per email an technik@heidelpay.de oder Telefon +49 (0) 6221 65170-10 an uns. Bitte notieren Sie sich Sie sich vorher die URL ihres e-Shops plus dem Webpfad zur Heidelpay Action und teilen Sie uns diese dann mit, als Beispiel<br/><br/> <b>https://www.meinshop.de/payment_heidelpay/rawnotify</b></p>',
      'license' => 'commercial',
      'copyright' => 'Copyright © 2012, Heidelberger Payment GmbH',
      'support' => 'technik@heidelpay.de',
      'link' => 'http://www.heidelpay.de/'
    );
  }/*}*/

	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
  {

	$plugins = array("HeidelPayment");
		if (!$this->assertRequiredPluginsPresent($plugins)){
			$this->Logging("This plugin requires the plugin HeidelPaymentt","ERROR");
			$this->uninstall();
			throw new Enlight_Exception("Dieses Plugin benoetigt das Plugin HeidelPayment<br />This plugin requires the plugin payment");
	}

  if (ini_get('always_populate_raw_post_data') == 0) {
    $this->Logging("Unable to install plugin due to missing always_populate_raw_post_data.","ERROR");
    throw new Enlight_Exception('
      DE:<br />
      Dieses Plugin benoetig die PHP Einstellung "alwasy_populate_raw_post_data". Bitte setzen Sie diese auf "On". Sollten
      Sie weitere Informationen benoetigen, schreiben Sie bitte eine E-Mail an technik@heidelpay.de .
      <br/>
      <br/>
      EN:<br/>
      This Plugin needs the php setting "always_populate_raw_post_data". Please switch it to "On".
      If you need more information please send an email to technik@heidelpay.de.'
    );
  };

		$this->createEvents();

     $this->createTable();

    $this->addsnippets();
		return true;
  }

  /**
	 * Uninstall plugin method
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		$this->deleteConfig();
    $this->deleteForm();

    # Eintrag aus s_core_menu l�schen
    try {
			Shopware()->Db()->exec(" DELETE FROM `s_core_menu` WHERE `pluginID` = '". $this->getId()."'");
			$this->Logging('* delete menu entry successfull', 'INFO');
		}
		catch(Exception $e) {
			$this->Logging('* delete menu entry failed: '.$e->getMessage(),'ERROR');
		}
    # Eintrag aus s_core_plugins l�schen
    try {
			Shopware()->Db()->exec(" DELETE FROM `s_core_plugins` WHERE `id` = '". $this->getId()."'");
			$this->Logging('* delete plugin entry successfull', 'INFO');
		}
		catch(Exception $e) {
			$this->Logging('* delete plugin entry failed: '.$e->getMessage(),'ERROR');
    }

    /*
		try {
			Shopware()->Db()->exec(" DELETE FROM `s_core_paymentmeans` WHERE `pluginID` = '". $this->getId()."'");
			$this->Logging('* delete paymentypes successfull', 'INFO');
		}
		catch(Exception $e) {
			$this->Logging('* delete paymenttypes failed: '.$e->getMessage(),'ERROR');
		}
    */
    $this->Logging('uninstall HeidelActions '.$this->modulType.' Modul', 'INFO');

		return true;
	}/*}*/

	protected function createEvents()
  {
    // Wird es erst in 4.1 geben
    /*
    // Heidelpay Einstellungen
    $event = $this->createEvent(
      'Enlight_Controller_Dispatcher_ControllerPath_Backend_HeidelActions',
      'onGetControllerPathBackend'
    );
    $this->subscribeEvent($event);
    $parent = $this->Menu()->findOneBy('label', 'Einstellungen');
    $item = $this->createMenuItem(array(
      'label' => 'Heidelpay Einstellungen',
      'onclick' => 'openAction(\'HeidelActions\');',
      'class' => 'sprite-credit-cards',
      'active' => 1,
      'parent' => $parent,
      'style' => 'background-position: 5px 5px;'
    ));
    $this->Menu()->addItem($item);
    $this->Menu()->save();
     */

    // Heidelpay Kontaktform
    $event = $this->createEvent(
      'Enlight_Controller_Dispatcher_ControllerPath_Backend_HeidelContact',
      'onHeidelContact'
    );
    $this->subscribeEvent($event);
    $parent = $this->Menu()->findOneBy('label', '');
    $item = $this->createMenuItem(array(
      'label' => 'Heidelpay Info anfordern',
      'onclick' => 'openAction(\'HeidelContact\');',
      'class' => 'sprite-credit-cards',
      'active' => 1,
      'parent' => $parent,
      'style' => 'background-position: 5px 5px;'
    ));
    $this->Menu()->addItem($item);
    $this->Menu()->save();

    // Heidelpay Buchungsinfos
    $event = $this->createEvent(
	    'Enlight_Controller_Dispatcher_ControllerPath_Backend_HeidelBooking',
		'onHeidelBooking'
    );
    $this->subscribeEvent($event);

    $this->subscribeEvent(
	    'Enlight_Controller_Action_Init_Backend_Order',
		'onPreDispatch'
    );

  }

  public static function onHeidelBooking(Enlight_Event_EventArgs $args)
	{
    return dirname(__FILE__).'/HeidelBooking.php';
	}

  public static function onHeidelContact(Enlight_Event_EventArgs $args)
	{
    return dirname(__FILE__).'/HeidelContact.php';
	}

  public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
	{
    return dirname(__FILE__).'/HeidelActions.php';
	}


	/**
	 * Create payment table
	 */
	protected function createTable()
	{
		# set up config table
		$sql='CREATE TABLE IF NOT EXISTS  `s_plugin_heidelpay_config` (
			`id` bigint(20) NOT NULL auto_increment,
			`SECURITY_SENDER` varchar(32) NOT NULL,
			`allowABO` int(1) NOT NULL,
			`allowRATE` int(1) NOT NULL,
			`allowDEPOSIT` int(1) NOT NULL,
			`created` datetime NOT NULL,
			`lastChanged` datetime NOT NULL,
			`TRANSACTION_CHANNEL` varchar(32) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;';
		Shopware()->Db()->exec($sql);

		# set up rates table
		$sql='CREATE TABLE IF NOT EXISTS  `s_plugin_heidelpay_rates` (
			`id` bigint(20) NOT NULL auto_increment,
			`owner` bigint(20) NOT NULL,
			`kind` enum(\'abo\',\'rate\',\'deposit\') NOT NULL default \'rate\',
			`duration` int(11) NOT NULL,
			`durationtype` enum(\'day\',\'week\',\'month\',\'year\') NOT NULL default \'month\',
			`freq` int(11) NOT NULL,
			`freqtype` enum(\'day\',\'week\',\'month\',\'year\') NOT NULL default \'month\',
			`fee` int(11) NOT NULL,
			`feetype` enum(\'euro\',\'percent\') NOT NULL default \'percent\',
			`mini` int(11) NOT NULL,
			`maxi` int(11) NOT NULL,
			`sortorder` bigint(20) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1';
		Shopware()->Db()->exec($sql);

		# set up requests table
		$sql='CREATE TABLE IF NOT EXISTS  `s_plugin_heidelpay_requests` (
			`id` bigint(20) NOT NULL auto_increment,
			`IDENTIFICATION_UNIQUEID` varchar(32) NOT NULL,
			`IDENTIFICATION_SHORTID` varchar(14) NOT NULL,
			`IDENTIFICATION_TRANSACTIONID` varchar(255) NOT NULL,
			`IDENTIFICATION_REFERENCEID` varchar(32) NOT NULL,
			`PROCESSING_RESULT` varchar(20) NOT NULL,
			`PROCESSING_RETURN_CODE` varchar(11) NOT NULL,
			`PROCESSING_CODE` varchar(11) NOT NULL,
			`TRANSACTION_SOURCE` varchar(10) NOT NULL,
			`TRANSACTION_CHANNEL` varchar(32) NOT NULL,
			`TRANSACTION_RESPONSE` varchar(5) NOT NULL,
			`TRANSACTION_MODE` varchar(15) NOT NULL,
			`CRITERION_RESPONSE_URL` varchar(255) NOT NULL,
			`created` datetime NOT NULL,
			`SERIAL` mediumtext NOT NULL,
			`XML` mediumtext NOT NULL,
			`RESPONSE` mediumtext NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;';
		Shopware()->Db()->exec($sql);


			}

	/**
	 * Create payment config form
	 */
  protected function createForm()/*{{{*/
	{
		$form = $this->Form();

		$form->setElement('text', 'HEIDELPAY_LIVE_URL', array('label'=>'LIVE_URL','value'=>'https://heidelpay.hpcgw.net/sgw/gtw', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_TEST_URL', array('label'=>'TEST_URL','value'=>'https://test-heidelpay.hpcgw.net/sgw/gtw', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_SECURITY_SENDER', array('label'=>'SECURITY_SENDER','value'=>'31HA07BC8124AD82A9E96D9A35FAFD2A', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_USER_LOGIN', array('label'=>'USER_LOGIN','value'=>'31ha07bc8124ad82a9e96d486d19edaa', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_USER_PW', array('label'=>'USER_PW','value'=>'password', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('combo', 'HEIDELPAY_TRANSACTION_MODE', array('label'=>'TRANSACTION_MODE','value'=>'2','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => '
		new Ext.data.ArrayStore({
        id: 0,
        fields: [
            "myId",
            "displayText"
        ],
        data: [[1, "INTEGRATOR_TEST"], [2, "CONNECTOR_TEST"], [3, "LIVE"]]
    	})
		'
		), 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		//$form->setElement('', 'Channel', array('label'=>'Channel','value'=>''));
		$form->setElement('text', 'HEIDELPAY_CC_CHANNEL', array('label'=> 'Kreditkraten Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_DC_CHANNEL', array('label'=>'Debitkarten Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_DD_CHANNEL', array('label'=>'Lastschrift Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_PP_CHANNEL', array('label'=>'Vorkasse Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_IV_CHANNEL', array('label'=>'Rechnungs Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_SUE_CHANNEL', array('label'=>'Sofort�berweisungs Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_GIR_CHANNEL', array('label'=>'Giropay Channel','value'=>'31HA07BC81A71E2A47DA662C5EDD1112', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_PAY_CHANNEL', array('label'=>'PayPal Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_IDE_CHANNEL', array('label'=>'Ideal Channel','value'=>'31HA07BC81A71E2A47DA804F6CABDC59', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_EPS_CHANNEL', array('label'=>'EPS Channel','value'=>'31HA07BC812125981B4F52033DE486AB', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));

		$form->setElement('combo', 'HEIDELPAY_CC_BOOKING_MODE', array('label'=>'Kreditkarten Buchungsmodus','value'=>'1','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => '
		new Ext.data.ArrayStore({
        id: 0,
        fields: [
            "myId",
            "displayText"
        ],
        data: [[1, "Sofortbuchung"], [2, "Reservierung"]]
    	})
		'
		), 'scope'=>Shopware_Components_Form::SCOPE_SHOP));

		$form->setElement('combo', 'HEIDELPAY_DC_BOOKING_MODE', array('label'=>'Debitkarten Buchungsmodus','value'=>'1','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => '
		new Ext.data.ArrayStore({
        id: 0,
        fields: [
            "myId",
            "displayText"
        ],
        data: [[1, "Sofortbuchung"], [2, "Reservierung"]]
    	})
		'
		), 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('combo', 'HEIDELPAY_DD_BOOKING_MODE', array('label'=>'Lastschrift Buchungsmodus','value'=>'1','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => '
		new Ext.data.ArrayStore({
        id: 0,
        fields: [
            "myId",
            "displayText"
        ],
        data: [[1, "Sofortbuchung"], [2, "Reservierung"]]
    	})
		'
		), 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		//	$form->setElement('combo', 'HEIDELPAY_CANCEL_ORDER', array('label'=>'Warenkorb bei Abbruch in Bestellung umwandeln','value'=>'1','attributes'=>array(
		//'valueField'=>'myId','displayField'=>'displayText',
		//'mode' => 'local',
		//'triggerAction' => 'all',
		//'store' => '
		//new Ext.data.ArrayStore({
		//id: 0,
		//fields: [
		//    "myId",
		//    "displayText"
		//],
		//data: [[1, "Nein"], [2, "Ja"]]
		//})
		//'
		//)));
		//	$form->setElement('combo', 'HEIDELPAY_FAIL_ORDER', array('label'=>'Warenkorb bei Fehler in Bestellung umwandeln','value'=>'1','attributes'=>array(
		//'valueField'=>'myId','displayField'=>'displayText',
		//'mode' => 'local',
		//'triggerAction' => 'all',
		//'store' => '
		//new Ext.data.ArrayStore({
		//id: 0,
		//fields: [
		//    "myId",
		//    "displayText"
		//],
		//data: [[1, "Nein"], [2, "Ja"]]
		//})
		//'
		//)));
		$form->setElement('combo', 'HEIDELPAY_DEBUG', array('label'=>'DEBUG MODE','value'=>'1','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => '
		new Ext.data.ArrayStore({
        id: 0,
        fields: [
            "myId",
            "displayText"
        ],
        data: [[1, "Nein"], [2, "Ja"]]
    	})
		'
		), 'scope'=>Shopware_Components_Form::SCOPE_SHOP));

		$form->save();
  }/*}}}*/

      public function onPreDispatch(Enlight_Event_EventArgs $args) {
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();
        if($response->isException()) {
            return;
        }
        $templates = $view->Engine()->getTemplateDir();
        array_unshift($templates, dirname(__FILE__) . '/Views/');
        $view->setTemplateDir($templates);
    }

	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		if(!$request->isDispatched()
		|| $response->isException()
		|| $request->getModuleName()!='backend') {
			return;
		}
    $view->addTemplateDir(dirname(__FILE__).'/Views/');
    return $args;
	}

	/*
	 * function for loggin information
	 * posible log levels are:  DEBUG , INFO , WARN , ERROR
	 */
	public function Logging($message, $level = "ERROR" ) {
		$path = "files/log/heidelpay.log";
		$dir  = dirname($path);
		if (!file_exists($dir)) {
			mkdir($dir);
		}
		$MessageDate = date("M j Y H:i:s");
		$Message= $MessageDate." ".$_SERVER['SERVER_NAME']." : ".self::$_moduleDesc." (".$level.") : ".$message ;

		$file = fopen ($path, "a+");
		if ($file) {
			fwrite($file, $Message."\n");
			fclose($file);
		}
	}	/*}*/

	public function addsnippets()
	{
		$snippets = $this->snippets();


		foreach ($snippets as $key => $value) {

			if (!empty($value[0])) {
				$sql = " SELECT id FROM s_core_snippets WHERE  namespace = \"".$value[0]."\" AND shopID = 1 AND localeID = ".$value[1]." AND name = \"".$value[2]."\"" ;
				$data = Shopware()->Db()->fetchAll($sql);
				$sql = "";
				if ($data[0][id] > 0)
				{
					$sql = "UPDATE s_core_snippets SET namespace	= \"".$value[0]."\",
													shopID 		= 	1,
													localeID 	= 	".$value[1].",
													name 		= 	\"".$value[2]."\",
													value 		= \"".$value[3]."\"
				WHERE id=".$data[0]['id']." ";

				} else {
					$sql = "INSERT s_core_snippets SET namespace	= \"".$value[0]."\",
													shopID 		= 	1,
													localeID 	= 	".$value[1].",
													name 		= 	\"".$value[2]."\",
													value 		= \"".$value[3]."\"
				";
				}

				Shopware()->Db()->query($sql);
			}
		}
		return true ;
  }

	private function snippets()
	{
    $snippets 	= array();
    $snippets[] 	= array('backend/HeidelActions','1','MissingSender','Bitte tragen Sie den Security Sender im Heidelpay Payment Plugin ein.');
    $snippets[] 	= array('backend/HeidelActions','2','MissingSender','Please enter the Security Sender into the Heidelpay Payment Plugin.');
    $snippets[] 	= array('backend/HeidelActions','1','MissingChannel','Bitte tragen Sie den Transaction Channel im Heidelpay Payment Plugin ein.');
    $snippets[] 	= array('backend/HeidelActions','2','MissingChannel','Please enter the Transaction Channel into the Heidelpay Payment Plugin.');
    $snippets[] 	= array('backend/HeidelActions','1','MissingUser','Bitte tragen Sie den User Login im Heidelpay Payment Plugin ein.');
    $snippets[] 	= array('backend/HeidelActions','2','MissingUser','Please enter the User Login into the Heidelpay Payment Plugin.');
    $snippets[] 	= array('backend/HeidelActions','1','MissingPwd','Bitte tragen Sie das User Passwort im Heidelpay Payment Plugin ein.');
		$snippets[] 	= array('backend/HeidelActions','2','MissingPwd','Please enter the User Password into the Heidelpay Payment Plugin.');

		return $snippets ;
	}
}
