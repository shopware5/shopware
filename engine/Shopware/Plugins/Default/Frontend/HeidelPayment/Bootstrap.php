<?php
/**
 * Heidelpay
 *
 * @link http://www.heidelpay.de
 * @copyright Copyright (c) 2011, Heidelberger Payment AG
 * @author Jens Richter
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_HeidelPayment_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	private static $_moduleDesc = 'HeidelPayment';

	var $version	= 	"12.08" ;
	var $modulType  = 	"Standard" ;



	public function getInfo() {
		$img = base64_encode(file_get_contents(dirname(__FILE__) . '/img/heidelpay.png'));
		return array(
    		'version' => $this->version ,
			'autor' => 'Heidelberger Payment GmbH',
			'label' => "Heidelpay Payment ".$this->modulType ,
			'source' => "Default",
            'description' => '<p><img src="data:image/png;base64,' . $img . '" /></p> <p style="font-size:12px; font-weight: bold;">Heidelberger Payment GmbH - Ihr Full Service Payment Provider - alles aus einer Hand <p></p> <p style="font-size:12px">Die Heidelberger Payment GmbH kurz: heidelpay bietet als BaFin-zertifizierter Payment Service Provider alles was zum Online-Payment geh&ouml;rt.<br><br><a href="http://testshops.heidelpay.de/contactform/?campaign=shopware4.0&shop=shopware4.0" target="_blank" style="font-size: 12px; color: #000;  font-weight: bold;">&gt;&gt;&gt; Informationen anfordern &lt;&lt;&lt;</a><br/><p><br /> <p style="font-size:12px">Das Leistungsspektrum des PCI DSS zertifizierten Unternehmens reicht von weltweiten e-Payment L&ouml;sungen, inklusive eines vollst&auml;ndigen Debitorenmanagement-, Risk- und Fraud- Systems bis hin zu einem breiten Angebot alternativer Bezahlverfahren - schnell, sicher, einfach und umfassend - alles aus einer Hand.</p><br/> <a href="http://www.heidelpay.de" style="font-size: 12px; color: #000;  font-weight: bold;">www.heidelpay.de</a><br/> <br/> <p style="font-size: 12px; color: #f00";  font-weight: bold;">Hinweis:</p><p style="font-size:12px">Um unser "Heidelpay Actions Standard" Plug-in nutzen zu k&ouml;nnen, beantragen Sie bitte die Aufschaltung von push Benachrichtigungen bei unserem Technischen Support. Wenden Sie sich hierf&uuml;r bitte per email an technik@heidelpay.de oder Telefon +49 (0) 6221 65170-10 an uns. Bitte notieren Sie sich Sie sich vorher die URL ihres e-Shops plus dem Webpfad zur Heidelpay Action und teilen Sie uns diese dann mit, als Beispiel<br/><br/> <b>https://www.meinshop.de/payment_heidelpay/rawnotify</b></p>',
            'license' => 'commercial',
    		'copyright' => 'Copyright © 2012, Heidelberger Payment GmbH',
			'support' => 'technik@heidelpay.de',
			'link' => 'http://www.heidelpay.de/'
			);
	}/*}*

	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		if (!$this->assertVersionGreaterThen("3.5.5")){
     		throw new Enlight_Exception("This Plugin needs min shopware 3.5.5");
		}

		$plugins = array("Payment");
		if (!$this->assertRequiredPluginsPresent($plugins)){
			$this->Logging("This plugin requires the plugin payment","ERROR");
			$this->uninstall();
			throw new Enlight_Exception("This plugin requires the plugin payment");
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

		$this->Logging('Install Heidelpay '.$this->modulType.' Plugin', 'INFO');

		$this->createEvents();
		$this->Logging(' * register eventhandler', 'INFO');
		$this->createPayments();
		$this->Logging('* install payments', 'INFO');
		// $this->createTable();
		$this->createForm();
		$this->Logging('* create form', 'INFO');
		$this->addsnippets();
		return true;
	}

	protected function createEvents()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentHeidelpay',
			'onGetControllerPathFrontend'
			);
			$this->subscribeEvent($event);

	}

	/**
	 * Create and save payments
	 */
	protected function createPayments()
	{
		$inst = $this->paymentMethod();

		foreach ($inst as $key => $val ) {
			//$getOldPayments = Shopware()->Payments()->fetchAll(array('name'=>"heidelpay_".$val['name'],));
			$getOldPayments = Shopware()->Payments()->fetchRow(array('name=?'=>"heidelpay_".$val['name']));
			if (!empty($getOldPayments['id'])) {
				$newData = array( "pluginID"   => (int)$this->getId() );
				$where   = array( "id = ".(int)$getOldPayments['id']  );
				Shopware()->Payments()->update($newData, $where);
			} else {
				$paymentRow = Shopware()->Payments()->createRow(array(
					'name' => "heidelpay_".$val['name'],
					'description' => "Heidelpay ".$val['description'],
					'action' => 'payment_heidelpay',
					'active' => 0,
					'pluginID' => $this->getId(),
					'position' => "1".$key
				))->save();
			}
		};
	}


	/**
	 * Create payment table
	 */
	protected function createTable()
	{
	}

	/**
	 * Create payment config form
	 */
	protected function createForm()
	{
		$form = $this->Form();

		$form->setElement('text', 'HEIDELPAY_LIVE_URL', array('label'=>'LIVE_URL','value'=>'https://heidelpay.hpcgw.net/sgw/gtwu', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_TEST_URL', array('label'=>'TEST_URL','value'=>'https://test-heidelpay.hpcgw.net/sgw/gtwu', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_SECURITY_SENDER', array('label'=>'SECURITY_SENDER','value'=>'31HA07BC8124AD82A9E96D9A35FAFD2A', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_USER_LOGIN', array('label'=>'USER_LOGIN','value'=>'31ha07bc8124ad82a9e96d486d19edaa', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_USER_PW', array('label'=>'USER_PW','value'=>'password', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('combo', 'HEIDELPAY_TRANSACTION_MODE', array('label'=>'TRANSACTION_MODE','value'=>'CONNECTOR_TEST','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => 'new Ext.data.ArrayStore({
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
		$form->setElement('text', 'HEIDELPAY_CC_CHANNEL', array('label'=> 'Kreditkarten Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_DC_CHANNEL', array('label'=>'Debitkarten Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_DD_CHANNEL', array('label'=>'Lastschrift Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_PP_CHANNEL', array('label'=>'Vorkasse Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_IV_CHANNEL', array('label'=>'Rechnungs Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_SUE_CHANNEL', array('label'=>'Sofortüberweisungs Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_GIR_CHANNEL', array('label'=>'Giropay Channel','value'=>'31HA07BC81A71E2A47DA662C5EDD1112', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_PAY_CHANNEL', array('label'=>'PayPal Channel','value'=>'31HA07BC81A71E2A47DA94B6ADC524D8', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_IDE_CHANNEL', array('label'=>'Ideal Channel','value'=>'31HA07BC81A71E2A47DA804F6CABDC59', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_EPS_CHANNEL', array('label'=>'EPS Channel','value'=>'31HA07BC812125981B4F52033DE486AB', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));

		$form->setElement('combo', 'HEIDELPAY_CC_BOOKING_MODE', array('label'=>'Kreditkarten Buchungsmodus','value'=>'Sofortbuchung','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => 'new Ext.data.ArrayStore({
        id: 0,
        fields: [
            "myId",
            "displayText"
        ],
        data: [[1, "Sofortbuchung"], [2, "Reservierung"]]
    	})
		'
		), 'scope'=>Shopware_Components_Form::SCOPE_SHOP));

		$form->setElement('combo', 'HEIDELPAY_DC_BOOKING_MODE', array('label'=>'Debitkarten Buchungsmodus','value'=>'Sofortbuchung','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => 'new Ext.data.ArrayStore({
        id: 0,
        fields: [
            "myId",
            "displayText"
        ],
        data: [[1, "Sofortbuchung"], [2, "Reservierung"]]
    	})
		'
		), 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('combo', 'HEIDELPAY_DD_BOOKING_MODE', array('label'=>'Lastschrift Buchungsmodus','value'=>'Sofortbuchung','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => 'new Ext.data.ArrayStore({
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
		$form->setElement('combo', 'HEIDELPAY_DEBUG', array('label'=>'DEBUG MODE','value'=>'Nein','attributes'=>array(
		'valueField'=>'myId','displayField'=>'displayText',
		'mode' => 'local',
		'triggerAction' => 'all',
		'store' => 'new Ext.data.ArrayStore({
        id: 0,
        fields: [
            "myId",
            "displayText"
        ],
        data: [[1, "Nein"], [2, "Ja"]]
    	})
		'
		), 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$secret= strtoupper(sha1(mt_rand(10000, mt_getrandmax())));
		$form->setElement('text', 'HEIDELPAY_SECRET', array('label'=>'SECRET','value'=>''.$secret.'', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_ERRORMAIL', array('label'=>'Error E-Mail Adresse','value'=>'', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'HEIDELPAY_NOTIFY_IP', array('label'=>'IP der Heidelpay Notify Server','value'=>'217.68.2.209,217.7.205.227', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));

		$form->save();
	}

	/**
	 * Uninstall plugin method
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		//Shopware()->Payments()->update(array("active"=>0), array("pluginID=?"=>$this->getId()));
		$newData = array("active"   => 0 );
		$where   = array("pluginID = ".(int)$this->getId());
		Shopware()->Payments()->update($newData, $where);
		$this->Logging('uninstall Heidelpay '.$this->modulType.' Modul', 'INFO');

		return true;
	}/*}*/

	public static function onGetControllerPathFrontend(Enlight_Event_EventArgs $args)
	{
		Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Views/');
		return dirname(__FILE__).'/Controllers/Frontend/PaymentHeidelpay.php';
	}


	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();

		if(!$request->isDispatched()
		|| $response->isException()
		|| $request->getModuleName()!='frontend') {
			return;
		}

		$view->addTemplateDir(dirname(__FILE__).'/Views/');
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
		/*
		 * Send error mail if mail addresse set
		 */
		if (!empty($this->Config()->HEIDELPAY_ERRORMAIL)) {
		 if ( $level == "ERROR") mail($this->Config()->HEIDELPAY_ERRORMAIL , 'Shopware Heidelpay Plugin error',  self::$_moduleDesc." (".$level.") : ".$message );
		}


	}	/*}*/

	public function paymentMethod()
	{
		$inst = array();

		$inst[] = array(
		 'name'       => 'cc',
		 'description'     => 'Kreditkarte',
		);
		$inst[] = array(
		 'name'       => 'dc',
		 'description'     => 'Debitkarte',
		);
		$inst[] = array(
		 'name'       => 'dd',
		 'description'     => 'Lastschrift',
		);
		$inst[] = array(
		 'name'       => 'iv',
		 'description'     => 'Rechnung',
		);
		$inst[] = array(
		 'name'       => 'pp',
		 'description'     => 'Vorkasse',
		);
		$inst[] = array(
		 'name'       => 'sue',
		 'description'     => 'Sofortüberweisung',
		);
		$inst[] = array(
		 'name'       => 'gir',
		 'description'     => 'Giropay',
		);
		$inst[] = array(
		 'name'       => 'pay',
		 'description'     => 'PayPal',
		);
		$inst[] = array(
		 'name'       => 'ide',
		 'description'     => 'Ideal',
		);
		$inst[] = array(
		 'name'       => 'eps',
		 'description'     => 'EPS',
		);

		return $inst ;
	}

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
    $snippets[] 	= array('frontend/payment_heidelpay/prepayment','1','OrderOverview','Weiter zur Bestell&uuml;bersicht');
    $snippets[] 	= array('frontend/payment_heidelpay/prepayment','2','OrderOverview','go to order summary');

    $snippets[] 	= array('frontend/payment_heidelpay/gateway','1','PaymentHeader','Bitte f&uuml;hren Sie nun die Zahlung durch:');
    $snippets[] 	= array('frontend/payment_heidelpay/gateway','2','PaymentHeader','Please confirm your payment:');

    $snippets[] 	= array('frontend/payment_heidelpay/gateway','1','PaymentInfoWait','Bitte warten...');
    $snippets[] 	= array('frontend/payment_heidelpay/gateway','2','PaymentInfoWait','Please wait ..');

    $snippets[] 	= array('frontend/payment_heidelpay/prepayment','1','PaymentSuccess','Ihr Bezahlvorgang war erfolgreich!');
    $snippets[] 	= array('frontend/payment_heidelpay/prepayment','2','PaymentSuccess','Your transaction was successfull!');

    $snippets[] 	= array('frontend/payment_heidelpay/fail','1','PaymentProcess','Bezahlvorgang');
    $snippets[] 	= array('frontend/payment_heidelpay/fail','2','PaymentProcess','Payment process');

		$snippets[] 	= array('frontend/payment_heidelpay/fail','1','basket','Zur&uuml;ck zum Warenkorb');
		$snippets[] 	= array('frontend/payment_heidelpay/fail','2','basket','back to basket');

    $snippets[] 	= array('frontend/payment_heidelpay/prepayment','1','PaymentProcess','Bezahlvorgang');
    $snippets[] 	= array('frontend/payment_heidelpay/prepayment','2','PaymentProcess','Payment process');

    $snippets[] 	= array('frontend/payment_heidelpay/error','1','basket','Zur&uuml;ck zum Warenkorb');
    $snippets[] 	= array('frontend/payment_heidelpay/error','2','basket','back to basket');

		$snippets[] 	= array('frontend/payment_heidelpay/fail','1','PaymentFailed','Ihr Bezahlvorgang konnte aus folgenden Grund nicht bearbeitet werden:');
		$snippets[] 	= array('frontend/payment_heidelpay/fail','2','PaymentFailed','Your payment process could not be finished, because of the following reason:');

    $snippets[] 	= array('frontend/payment_heidelpay/cancel','1','PaymentProcess','Bezahlvorgang');
    $snippets[] 	= array('frontend/payment_heidelpay/cancel','2','PaymentProcess','Payment process');

    $snippets[] 	= array('frontend/payment_heidelpay/cancel','1','PaymentCancel','Der Bezahlvorgang wurde von Ihnen abgebrochen.');
    $snippets[] 	= array('frontend/payment_heidelpay/cancel','2','PaymentCancel','The payment process was canceled by you.');

		$snippets[] 	= array('frontend/payment_heidelpay/cancel','1','basket','Zur&uuml;ck zum Warenkorb');
		$snippets[] 	= array('frontend/payment_heidelpay/cancel','2','basket','back to basket');

    $snippets[] 	= array('frontend/payment_heidelpay/error','1','PaymentProcess','Bezahlvorgang');
    $snippets[] 	= array('frontend/payment_heidelpay/error','2','PaymentProcess','Payment process');

		$snippets[] 	= array('frontend/payment_heidelpay/error','1','PaymentError','Es ist ein Fehler bei Ihrem Bezahlvorgang aufgetreten. Bitte wenden Sie sich an den Shopbetreiber.');
		$snippets[] 	= array('frontend/payment_heidelpay/error','2','PaymentError','An error occurred during your payment process. Please contact the shop owner.');

    $snippets[] 	= array('frontend/payment_heidelpay/success','1','PaymentSuccess','Ihr Bezahlvorgang war erfolgreich!');
    $snippets[] 	= array('frontend/payment_heidelpay/success','2','PaymentSuccess','Your transaction was successfull!');

    $snippets[] 	= array('frontend/payment_heidelpay/success','1','PaymentProcess','Bezahlvorgang');
    $snippets[] 	= array('frontend/payment_heidelpay/success','2','PaymentProcess','Payment process');

    $snippets[] 	= array('frontend/payment_heidelpay/success','1','PrepaymentText','Bitte überweisen Sie uns den Betrag von {AMOUNT} {CURRENCY} auf folgendes Konto:'."\n"
                    .'Land: {CONNECTOR_ACCOUNT_COUNTRY}'."\n"
					          .'Kontoinhaber: {CONNECTOR_ACCOUNT_HOLDER}'."\n"
					          .'Konto-Nr.: {CONNECTOR_ACCOUNT_NUMBER}'."\n"
					          .'Bankleitzahl: {CONNECTOR_ACCOUNT_BANK}'."\n"
					          .'IBAN: {CONNECTOR_ACCOUNT_IBAN}'."\n"
					          .'BIC: {CONNECTOR_ACCOUNT_BIC}'."\n"
                    .'Geben sie bitte im Verwendungszweck UNBEDINGT die Identifikationsnummer'."\n"
                    .'{IDENTIFICATION_SHORTID}'."\n"
                    .'und NICHTS ANDERES an.');
    $snippets[] 	= array('frontend/payment_heidelpay/success','2','PrepaymentText','Please transfer the amount of {AMOUNT} {CURRENCY} to the following account:'."\n"
                    .'Country: {CONNECTOR_ACCOUNT_COUNTRY}'."\n"
					          .'Account holder: {CONNECTOR_ACCOUNT_HOLDER}'."\n"
					          .'Account No.: {CONNECTOR_ACCOUNT_NUMBER}'."\n"
					          .'Bank Code: {CONNECTOR_ACCOUNT_BANK}'."\n"
					          .'IBAN: {CONNECTOR_ACCOUNT_IBAN}'."\n"
					          .'BIC: {CONNECTOR_ACCOUNT_BIC}'."\n"
                    .'When you transfer the money you HAVE TO use the identification number'."\n"
                    .'{IDENTIFICATION_SHORTID}'."\n"
					          .'as the descriptor and nothing else. Otherwise we cannot match your transaction!');

    $snippets[] 	= array('frontend/payment_heidelpay/success','1','InvoiceHeader','Rechnungsinformation');
    $snippets[] 	= array('frontend/payment_heidelpay/success','2','InvoiceHeader','Invoiceinformation');

		return $snippets ;
	}
	  public function checkTable($table)/*{{{*/
  {
    $sql = 'SHOW TABLES LIKE "'.$table.'"';
    #echo $sql;
    $exists = Shopware()->Db()->fetchAll($sql);
    return $exists;
  }/*}}}*/

   public  function createSenderTable($table)/*{{{*/
  {
    $sql = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `meth` char(2) NOT NULL,
      `typ` char(2) NOT NULL,
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
      `CAPTURED` int(1) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `typ` (`typ`),
      KEY `meth` (`meth`),
      KEY `IDENTIFICATION_UNIQUEID` (`IDENTIFICATION_UNIQUEID`),
      KEY `IDENTIFICATION_SHORTID` (`IDENTIFICATION_SHORTID`),
      KEY `IDENTIFICATION_TRANSACTIONID` (`IDENTIFICATION_TRANSACTIONID`),
      KEY `IDENTIFICATION_REFERENCEID` (`IDENTIFICATION_REFERENCEID`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
    return Shopware()->Db()->query($sql);
  }/*}}}*/

}
