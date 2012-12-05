<?php

/**
 * Klarna Payment Module
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @author       PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @package      PayIntelligent_Klarna
 * @copyright(C) 2011 Klarna GmbH. All rights reserved. <http://www.klarna.com/>
 */
class Shopware_Plugins_Frontend_PigmbhKlarnaPayment_Bootstrap extends Shopware_Components_Plugin_Bootstrap { 

    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install() {
        $this->uninstall();
        $this->piKlarnaCreateTables();
        $this->piKlarnaCreatePayments();
        $this->piKlarnaCreateEvents();
        $this->piKlarnaCreateForm();
        $this->piKlarnaCreateMenu();
        $this->piKlarnaGetOldInvoices();
        $this->piKlarnaActivatePlugin();
        return true;
    }

    /**
     * This Method will be called everytime the plugin config is saved
     * @return bool
     */
    public function enable()
    {
        $repository = $this->Application()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shops = $repository->findBy(array('mainId' => null));
        $elements = $this->Form()->getElements();

        $config = array();
        foreach($elements as $element) {
            $config[1][$element->getName()] = $element->getValue();
            foreach($element->getValues() as $value) {
                $config[$value->getShop()->getId()][$element->getName()] = $value->getValue();
            }
            foreach($shops as $shop) {
                if(!isset($config[$shop->getId()][$element->getName()])) {
                    $config[$shop->getId()][$element->getName()] = $config[1][$element->getName()];
                }
            }
        }
        $this->piKlarnaOnBackendPlugin($config);
        return true;
    }

    /**
     * 	create and save payments
     *
     * @return bool
     */
    protected function piKlarnaCreatePayments() {
        try {
            $paymentRow = Shopware()->Payments()->createRow(array(
                'name' => 'KlarnaInvoice',
                'description' => 'Klarna Rechnung',
                'template' => '',
                'additionaldescription' => 'Bezahlung via Rechnung innerhalb von 14 Tagen!',
                'action' => 'PiPaymentKlarna',
                'active' => 1,
                'pluginID' => $this->getId()
            ))->save();
            $paymentRow = Shopware()->Payments()->createRow(array(
                'name' => 'KlarnaPartPayment',
                'description' => 'Klarna Ratenkauf',
                'template' => '',
                'additionaldescription' => 'Finanzieren Sie Ihre Bestellung in bequemen Raten!',
                'action' => 'PiPaymentKlarna',
                'active' => 1,
                'pluginID' => $this->getId()
            ))->save();
        }
        catch (Exception $e) {
            throw new Exception('<b>Fehler beim erstellen der Zahlarten(createPayments)</b><br />' . $e);
        }
        return true;
    }

    /**
     * Create and subscribe events and hooks
     *
     *  @return bool
     */
    protected function piKlarnaCreateEvents() {
        try {
            $event = $this->createEvent(
                'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PiPaymentKlarna', 'piKlarnaOnGetControllerPathFrontend'
            );
            $this->subscribeEvent($event);
            $event = $this->createEvent(
                'Enlight_Controller_Dispatcher_ControllerPath_Backend_PiPaymentKlarnaBackend', 'piKlarnaOnGetControllerPathBackend'
            );
            $this->subscribeEvent($event);
            $event = $this->createEvent(
                'Enlight_Controller_Action_PostDispatch_Frontend_Detail', 'piKlarnaOnPostDispatchDetail'
            );
            $this->subscribeEvent($event);
            $event = $this->createEvent(
                'Enlight_Controller_Action_PostDispatch_Frontend_Listing', 'piKlarnaOnPostDispatchListing'
            );
            $this->subscribeEvent($event);
//            $event = $this->createEvent(
//                'Enlight_Controller_Action_PreDispatch_Backend_Plugin', 'piKlarnaOnBackendPlugin'
//            );
//            $this->subscribeEvent($event);
            $event = $this->createHook(
                'sOrder', 'sendMail', 'piKlarnaOnSendMail', Enlight_Hook_HookHandler::TypeBefore, 0
            );
            $this->subscribeHook($event);
            $event = $this->createEvent(
                'Enlight_Controller_Action_PostDispatch', 'piKlarnaOnPostDispatch'
            );
            $this->subscribeEvent($event);
            $event = $this->createEvent(
                'Enlight_Controller_Action_PreDispatch_Frontend_Checkout', 'piKlarnaOnPreDispatchCheckout'
            );
            $this->subscribeEvent($event);
        }
        catch (Exception $e) {
            throw new Exception('<b>Fehler beim erstellen der Events und Hooks(createEvents)</b><br />' . $e);
        }
        return true;
    }

    /**
     * Create Form for plugin configuration
     */
    protected function piKlarnaCreateForm() {
        try {
            $piKlarnaForm = $this->form();
            $piKlarnaForm->setElement('button', 'button0', array(
                'label' => '<b style="color:red;">Grundeinstellungen:</b>',
                'value' => ''
            ));
            $piKlarnaForm->setElement('checkbox', 'pi_klarna_active', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Aktiv',
                'value' => false,
                'attributes' => array(
                    "uniqueId" => 'pi_klarna_active'
                )
            ));
            $piKlarnaForm->setElement('text', 'pi_klarna_Merchant_ID', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'H&auml;ndler ID',
                'value' => '',
                'attributes' => array(
                    "uniqueId" => 'pi_klarna_eid'
                )
            ));
            $piKlarnaForm->setElement('text', 'pi_klarna_Secret', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Shared Secret',
                'value' => '',
                'attributes' => array(
                    "uniqueId" => 'pi_klarna_secret'
                )
            ));
            $piKlarnaForm->setElement('checkbox', 'pi_klarna_liveserver', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Liveserver',
                'value' => true
            ));
            $piKlarnaForm->setElement('button', 'PClass', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => '',
                'attributes' => array(
                    'handler' => 'function (){
                        // define one of the keywords (new or store) at this place.
                        var eidTextfieldObject = this.up(\'form\').down(\'textfield[uniqueId=pi_klarna_eid]\');
                        var secretTextfieldObject = this.up(\'form\').down(\'textfield[uniqueId=pi_klarna_secret]\');
                        // Get value from this field
                        var eidKey = eidTextfieldObject.getValue();
                        var secretKey = secretTextfieldObject.getValue();
                        // Only proceed if input available
                        if (!eidKey){
                            Ext.MessageBox.alert(\'Ups\',\'Bitte geben Sie die H&auml;ndler ID ein!\');
                            //eidTextfieldObject.getEl().setStyle(\'background\',\'red\');
                            return;
                        }
                        if (!secretKey){
                            Ext.MessageBox.alert(\'Ups\',\'Bitte geben Sie das shared secret ein!\');
                            //secretTextfieldObject.getEl().setStyle(\'background\',\'red\');
                            return;
                        }
                        Ext.MessageBox.progress(\'Vorgang wird durchgef&uuml;hrt...\', \'PClass wird geholt...\');
                        var url = window.location.pathname.split(\'/backend\')[0]
                                + \'/backend/pi_payment_klarna_backend/PiKlarnaFetchPClass\';
                        Ext.Ajax.request({
                           scope:this,
                           url: url,
                           success: function(result,request) {
                                //Ext.getCmp(\'myloadingwindow\').destroy();
                                var jsonData = Ext.JSON.decode(result.responseText);
                                var resultMessage = jsonData.pi_klarna_error;
                                if (resultMessage.substr(0, 12) == "Ratenzahlung"){
                                        Ext.MessageBox.alert(\'Status\',resultMessage);
                                        //eidTextfieldObject.getEl().setStyle(\'background\',\'green\');
                                        //secretTextfieldObject.getEl().setStyle(\'background\',\'green\');
                                }
                                else{
                                        Ext.MessageBox.alert(\'Status\',resultMessage);
                                        //eidTextfieldObject.getEl().setStyle(\'background\',\'red\');
                                        //secretTextfieldObject.getEl().setStyle(\'background\',\'red\');
                                }
                           },
                           failure: function() {
                                Ext.getCmp(\'myloadingwindow\').destroy();
                                //eidTextfieldObject.getEl().setStyle(\'background\',\'red\');
                                //secretTextfieldObject.getEl().setStyle(\'background\',\'red\');
                                Ext.MessageBox.alert(\'Ups\',\'Klarna ist zur Zeit nicht erreichbar. Bitte versuchen Sie es sp&auml;ter erneut.\');
                           },
                           // Pass all needed parameters
                           params: { pi_klarna_eidKey: eidKey, pi_klarna_secret: secretKey }
                        });
                     }',
                    'text' => '<b>Ratenzahlungsmodalit&auml;ten von Klarna holen und auf dem Server speichern (H&auml;ndler ID, Shared Secret und Liveservereinstellung m&uuml;ssen erst abgespeichert werden)</b>',
                    'xtype' => 'button'
                    )));
            $piKlarnaForm->setElement('checkbox', 'pi_klarna_Testmode', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Test Modus',
                'value' => false
            ));
            $piKlarnaForm->setElement('button', 'button1', array(
                'label' => '<b style="color:red;">Banner- und Logoeinstellungen:</b>',
                'value' => ''
            ));
            $piKlarnaForm->setElement('combo', 'showLogos', array(
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'label' => 'Logos anzeigen(Ist eine Zahlungsart deaktiviert, wird diese nicht angezeigt)',
                'value' => 'links',
                'store' => array(array("links", "links"),array("rechts", "rechts"),array("nicht anzeigen", "nicht anzeigen"))
            ));
            $piKlarnaForm->setElement('combo', 'InvoiceBanner', array(
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'label' => 'Klarna Rechnungskauf Banner anzeigen',
                'value' => 'links',
                'store' => array(array("links", "links"),array("rechts", "rechts"),array("oben", "oben"),array("nicht anzeigen", "nicht anzeigen"))
            ));
            $piKlarnaForm->setElement('combo', 'RatepayBanner', array(
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'label' => 'Klarna Ratenkauf Banner anzeigen',
                'value' => 'links',
                'store' => array(array("links", "links"),array("rechts", "rechts"),array("oben", "oben"),array("nicht anzeigen", "nicht anzeigen"))
            ));
            $piKlarnaForm->setElement('checkbox', 'piKlarnaShowBannerOnStartpage', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Logos und Banner nur auf der Startseite anzeigen',
                'value' => false
            ));
            $piKlarnaForm->setElement('checkbox', 'piKlarnaShowOneBanner', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Anstatt Rechnungs- und Ratenkaufbanner ein Banner f&uuml;r beides anzeigen(nur f&uuml;r Deutschland)',
                'value' => false
            ));
            $piKlarnaForm->setElement('button', 'button2', array(
                'label' => '<b style="color:red;">Anzeige des Ratenzahlungsblocks f&uuml;r Klarna Ratenkauf:</b>',
                'value' => ''
            ));
            $piKlarnaForm->setElement('checkbox', 'pi_klarna_rate_produkt', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Ratenzahlungsblock bei Produktdetails anzeigen',
                'value' => true
            ));
            $piKlarnaForm->setElement('checkbox', 'pi_klarna_rate_cart', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Ratenzahlungsblock im Warenkorb anzeigen',
                'value' => true
            ));
            $piKlarnaForm->setElement('checkbox', 'pi_klarna_rate_checkout', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Ratenzahlungsblock im Checkout anzeigen',
                'value' => true
            ));
            $piKlarnaForm->setElement('checkbox', 'pi_klarna_rate_listing', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Ratenzahlungsblock im Produktlisting anzeigen',
                'value' => true
            ));
            $piKlarnaForm->setElement('button', 'button3', array(
                'label' => '<b style="color:red;">Warenkorb Mindest- und Maximalbetrag, damit Klarna als Zahlungsart ausgew&auml;hlt werden kann:</b>',
                'value' => ''
            ));
            $piKlarnaForm->setElement('text', 'pi_klarna_basket_min', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Warenkorb Mindestbetrag in &euro;',
                'value' => '-0,01'
            ));
            $piKlarnaForm->setElement('text', 'pi_klarna_basket_max', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Warenkorb Maximalbetrag in &euro;',
                'value' => '1500'
            ));
        }
        catch (Exception $e) {
            throw new Exception('<b>Fehler beim erstellen des Einstellungsformulars(createForm)</b><br />' . $e);
        }
    }

    /**
     * Create backend menu
     */
    protected function piKlarnaCreateMenu() {
        try {
            $piKlarnaParent = $this->Menu()->findOneBy('label', 'Zahlungen');
            
            $piKlarnaItem = $this->createMenuItem(array(
                'label' => 'Klarna',
                'onclick' => 'openAction(\'PiPaymentKlarnaBackend\');',
                'class' => 'ico2 date2',
                'active' => 1,
                'parent' => $piKlarnaParent,
                'style' => 'background-position: 5px 5px;'
                    ));
            $this->Menu()->addItem($piKlarnaItem);
            $this->Menu()->save();
        }
        catch (Exception $e) {
            throw new Exception('<b>Fehler beim erstellen des Men&uuml;punktes(createMenu)</b><br />' . $e);
        }
    }

    /**
     *  updates payment id´s of orders made with previous versions of this plugin
     */
    protected function piKlarnaGetOldInvoices() {
        try {
            $sql = "SELECT order_number FROM Pi_klarna_payment_order_data WHERE payment_name ='KlarnaInvoice'";
            $piKlarnaInvoiceOrderNumber = Shopware()->Db()->fetchAll($sql);
            $sql = "SELECT order_number FROM Pi_klarna_payment_order_data WHERE payment_name ='KlarnaPartPayment'";
            $piKlarnaRateOrderNumber = Shopware()->Db()->fetchAll($sql);
            $piKlarnaInvoiceId = piKlarnaGetInvoicePaymentId();
            $piKlarnaRateId = piKlarnaGetRatePaymentId();
            $sql = "UPDATE s_order SET paymentID = ?, dispatchID = 9 WHERE ordernumber = ?";
            for ($i = 0; $i < sizeof($piKlarnaInvoiceOrderNumber); $i++) {
                Shopware()->Db()->query($sql, array((int)$piKlarnaInvoiceId, $piKlarnaInvoiceOrderNumber[$i]["order_number"]));
            };
            for ($i = 0; $i < sizeof($piKlarnaRateOrderNumber); $i++) {
                Shopware()->Db()->query($sql, array((int)$piKlarnaRateId, $piKlarnaRateOrderNumber[$i]["order_number"]));
            };
        }
        catch (Exception $e) {
            throw new Exception('<b>Fehler beim erstellen der Rulesets(createRuleset)</b><br />' . $e);
        }
    }

    /**
     * Create tables and make db inserts
     */
    protected function piKlarnaCreateTables() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_user_data`(
                `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id`               INT(10) UNSIGNED NOT NULL,
                `method`                VARCHAR(100) NOT NULL,
                `birthday`              VARCHAR(50) NULL,
                `cellphone`             VARCHAR(50) NULL,
                `gender`                VARCHAR(1) NULL,
                `street`                VARCHAR(255) NULL,
                `housenr`               VARCHAR(10) NULL,
                `firstname`             VARCHAR(255) NULL,
                `lastname`              VARCHAR(255) NULL,
                `zip`                   VARCHAR(255) NULL,
                `city`                  VARCHAR(255) NULL,
                `mail`                  VARCHAR(255) NULL,
                `ordernumber`           VARCHAR(255) NULL,
                PRIMARY KEY (`id`),
                INDEX(`user_id`)
            )ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_order_data`(
                `id` 			INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `payment_id`		INT(10) UNSIGNED NOT NULL,
                `payment_name` 		VARCHAR( 30 ) NOT NULL ,
                `order_number`		VARCHAR(255) NULL,
                `transactionid`		VARCHAR(255) NULL,
                `invoice_number`	VARCHAR(255) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_bills`(
                `id` 			INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `date` 			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `method`		VARCHAR(255) NULL,
                `order_number`		VARCHAR(255) NULL,
                `invoice_amount` 	DOUBLE NULL ,
                `invoice_number`	VARCHAR(255) NULL,
                `liveserver`		VARCHAR(255) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_bills_articles`(
                `id` 			INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_number`		VARCHAR(255) NULL,
                `invoice_number`	VARCHAR(255) NULL,
                `name`			VARCHAR( 30 ) NOT NULL ,
                `bestell_nr`		VARCHAR( 30 ) NOT NULL ,
                `anzahl` 		INT( 11 ) NOT NULL ,
                `einzelpreis` 		DOUBLE NOT NULL ,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_multistore`(
                `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_number`          VARCHAR(255) NULL,
                `shop_id`		VARCHAR(255) NULL,
                `secret`		VARCHAR(255) NULL,
                `liveserver`            VARCHAR(255) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_history`(
                `id` 			INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ordernumber`           VARCHAR(255) NULL,
                `date` 			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `event`			VARCHAR(255) NULL,
                `name`			VARCHAR(255) NULL,
                `bestellnr`		VARCHAR(255) NULL,
                `anzahl`		VARCHAR(255) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_order_detail` (
                `id` 			INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `ordernumber`           VARCHAR( 50 ) NOT NULL ,
                `artikel_id`            VARCHAR( 30 ) NOT NULL ,
                `bestell_nr`            VARCHAR( 30 ) NOT NULL ,
                `anzahl` 		INT( 11 ) NOT NULL ,
                `name` 			VARCHAR( 255 ) NOT NULL ,
                `einzelpreis`           DOUBLE NOT NULL ,
                `gesamtpreis`           DOUBLE NOT NULL ,
                `bestellt` 		INT( 11 ) NOT NULL ,
                `offen`             	INT( 11 ) NOT NULL ,
                `geliefert`             INT( 11 ) NOT NULL DEFAULT '0',
                `storniert`             INT( 11 ) NOT NULL DEFAULT '0',
                `retourniert`           INT( 11 ) NOT NULL DEFAULT '0',
                `bezahlstatus`          INT( 11 ) NOT NULL DEFAULT '0',
                `versandstatus`         INT( 11 ) NOT NULL DEFAULT '0'
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $sql = "	CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_order_stats` (
                `id`                    INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `status`                VARCHAR( 255 ) NOT NULL ,
                `group`                 VARCHAR( 255 ) NOT NULL
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `Pi_klarna_payment_pclass`(
                `id` 			INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ordernumber`   VARCHAR(255) NULL,
                `pclassid` 		VARCHAR(255) NULL,
                `eid`			VARCHAR(255) NULL,
                `description`	VARCHAR(255) NULL,
                `months`		VARCHAR(255) NULL,
                `startfee`		VARCHAR(255) NULL,
                `invoicefee`	VARCHAR(255) NULL,
                `interestrate`	VARCHAR(255) NULL,
                `minamount`		VARCHAR(255) NULL,
                `country`		VARCHAR(255) NULL,
                `type`			VARCHAR(255) NULL,
                `expire`		VARCHAR(255) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);
            $piKlarnaStatesMaxId = Shopware()->Db()->fetchOne("SELECT max(id) from s_core_states");
            $sql = "INSERT INTO `s_core_states` (`id`, `description`, `position`, `group`, `mail`)
                    VALUES
                    ( ?, '<span style=\"color:orange\">Reservierung teilweise aktiviert</span>', '1', 'payment', '0'),
                    ( ?, '<span style=\"color:green\">Reservierung komplett aktiviert</span>', '2', 'payment', '0'),
                    ( ?, '<span style=\"color:red\">Reservierung abgebrochen</span>', '2', 'payment', '0'),
                    ( ?, '<span style=\"color:orange\">Zahlung wird von Klarna gepr&uuml;ft</span>', '2', 'payment', '0'),
                    ( ?, '<span style=\"color:green\">Zahlung von Klarna akzeptiert</span>', '2', 'payment', '0'),
                    ( ?, '<span style=\"color:red\">Zahlung von Klarna nicht akzeptiert</span>', '2', 'payment', '0'),
                    ( ?, '<span style=\"color:orange\">Teilweise storniert</span>', '2', 'state', '0'),
                    ( ?, '<span style=\"color:red\">Komplett storniert</span>', '2', 'state', '0'),
                    ( ?, '<span style=\"color:orange\">Teilweise retourniert</span>', '2', 'state', '0'),
                    ( ?,'<span style=\"color:red\">Komplett retourniert</span>', '2', 'state', '0')";
            Shopware()->Db()->query($sql,array(
                (int)$piKlarnaStatesMaxId + 1,
                (int)$piKlarnaStatesMaxId + 2,
                (int)$piKlarnaStatesMaxId + 3,
                (int)$piKlarnaStatesMaxId + 4,
                (int)$piKlarnaStatesMaxId + 5,
                (int)$piKlarnaStatesMaxId + 6,
                (int)$piKlarnaStatesMaxId + 7,
                (int)$piKlarnaStatesMaxId + 8,
                (int)$piKlarnaStatesMaxId + 9,
                (int)$piKlarnaStatesMaxId + 10,  
            ));
        }
        catch (Exception $e) {
            throw new Exception('<b>Fehler beim erstellen der Tabellen(createTables)</b><br />' . $e);
        }
    }

    /**
     * Activate Plugin after installation
     */
    protected function piKlarnaActivatePlugin() {
        try {
            $piKlarnaPluginId = piKlarnaGetPluginId();
            $sql = "UPDATE s_core_plugins SET active= 1 WHERE id = ?";
            Shopware()->Db()->query($sql, array($piKlarnaPluginId));
        }
        catch (Exception $e) {
            throw new Exception('<b>Fehler beim aktivieren des Plugins(activatePlugin)</b><br />' . $e);
        }
    }

    /**
     *  Saves the basketamount(min/max)configuration that are made in the Backend
     *
     * @param null $config
     * @return void
     * @internal param \Enlight_Event_EventArgs $piKlarnaArgs
     */
//    static function piKlarnaOnBackendPlugin(Enlight_Event_EventArgs $piKlarnaArgs) {
    public function piKlarnaOnBackendPlugin($config=null) {
        $piKlarnaConfig = $config;

        $piKlarnaInvoiceId = piKlarnaGetInvoicePaymentId();
        $piKlarnaRateId    = piKlarnaGetRatePaymentId();
        
        if (!is_null($piKlarnaConfig)) {
            $sql = "DELETE FROM s_core_rulesets WHERE paymentID = ? OR paymentID = ? AND(rule2 like 'ORDERVALUELESS' OR rule2 like 'ORDERVALUEMORE')";
            Shopware()->Db()->query($sql, array((int)$piKlarnaRateId,(int)$piKlarnaInvoiceId));
            foreach($piKlarnaConfig as $key => $value) {
                if ($value['pi_klarna_active'] == true) {
                    $sql ="DELETE FROM s_core_rulesets WHERE paymentID = ? AND rule1 like 'SUBSHOP' AND value1 = ?";
                    Shopware()->Db()->query($sql,array((int)$piKlarnaInvoiceId, (int)$key));
                    Shopware()->Db()->query($sql,array((int)$piKlarnaRateId, (int)$key));
                }
                $value['pi_klarna_basket_min'] = str_replace(',', '.', $value['pi_klarna_basket_min']);
                $value['pi_klarna_basket_max'] = str_replace(',', '.', $value['pi_klarna_basket_max']);
                $sql = "Insert into s_core_rulesets(`paymentID`, `rule1`, `value1`, `rule2`, `value2`)
                        VALUES
                        (?, 'SUBSHOP', ?, 'ORDERVALUELESS', ?), 
                        (?, 'SUBSHOP', ?, 'ORDERVALUELESS', ?), 
                        (?, 'SUBSHOP', ?, 'ORDERVALUEMORE', ?), 
                        (?, 'SUBSHOP', ?, 'ORDERVALUEMORE', ?)";
                Shopware()->Db()->query($sql, array( 
                    (int)$piKlarnaInvoiceId, (int)$key, $value['pi_klarna_basket_min'],
                    (int)$piKlarnaRateId, (int)$key, $value['pi_klarna_basket_min'],
                    (int)$piKlarnaInvoiceId, (int)$key, $value['pi_klarna_basket_max'],
                    (int)$piKlarnaRateId, (int)$key, $value['pi_klarna_basket_max']
                ));
            }
        }
    }

    /**
     *  Displays the Klarna banner
     *
     *  @param Enlight_Event_EventArgs $piKlarnaArgs
     */
    static function piKlarnaOnPostDispatch(Enlight_Event_EventArgs $piKlarnaArgs) {
        $piKlarnaRequest = $piKlarnaArgs->getSubject()->Request();
        $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
        $piKlarnaView = $piKlarnaArgs->getSubject()->View();
        $piKlarnaAction = $piKlarnaRequest->getActionName();
        // break if no template is set.
        if(!$piKlarnaView->hasTemplate()) {
            return;
        }
        $templates = Shopware()->Template()->getTemplateDir();
        $firstTemplate = array_shift($templates);
        $piKlarnaView->_isEmotion = true;
        
        $piKlarnaView->addTemplateDir(dirname(__FILE__) . '/Views/Frontend/');
        if ($piKlarnaRequest->getModuleName() == 'frontend' && $piKlarnaConfig->pi_klarna_active == true) {
            $klarnaShopLang = checkKlarnaCountryCurrencys();
            if ($klarnaShopLang == 'de') $klarnaShopLang = Shopware()->Locale()->getLanguage();
            if ($piKlarnaView->sUserData) $piKlarnaImgPathLocale = strtolower($piKlarnaView->sUserData['additional']['country']['countryiso']);
            else $piKlarnaImgPathLocale = Shopware()->Locale()->getLanguage();
            if ($piKlarnaImgPathLocale == "sv") $piKlarnaImgPathLocale = "se";
            if (!checkKlarnaCountrys($piKlarnaImgPathLocale)) $piKlarnaImgPathLocale = 'de';
            
            $piKlarnaView->piKlarnaImgDir = 'http://' . Shopware()->Config()->Basepath
                    . '/engine/Shopware/Plugins/Default/Frontend/PigmbhKlarnaPayment/img/'
                    . $piKlarnaImgPathLocale
                    . '/';
            $piKlarnaView->piKlarnaShopLang = Shopware()->Locale()->getLanguage();
            $piKlarnaView->piKlarnaShopCurrency = Shopware()->Currency()->getShortName();
            $piKlarnaView->sPaymentErrorMethod = Shopware()->Session()->sPaymentErrorMethod;
            $piKlarnaView->pi_Klarna_lang = piKlarnaGetLanguage(Shopware()->Locale()->getLanguage());
            $piKlarnaView->pi_klarna_shopid = $piKlarnaConfig->pi_klarna_Merchant_ID;
            $sql = "SELECT active FROM s_core_paymentmeans  WHERE name = ?";
            $piKlarnaView->pi_klarna_active = Shopware()->Db()->fetchOne($sql,array('KlarnaInvoice'));
            $piKlarnaView->pi_klarna_rate_active = Shopware()->Db()->fetchOne($sql,array('KlarnaPartPayment'));
            $paymentMeans = Shopware()->Modules()->Admin()->sGetPaymentMeans();
            $allowKlarnaPartPayment = false;
            foreach($paymentMeans as $payment) {
                if('KlarnaPartPayment' == $payment['name']) { 
                    $allowKlarnaPartPayment = true;
                    break;
                }
            }
            if(!$allowKlarnaPartPayment) {
                $piKlarnaView->pi_klarna_rate_active = null;
            }

            
            $piKlarnaView->extendsTemplate('index/header.tpl');
            $piKlarnaUserdata = $piKlarnaView->sUserData;

            if($piKlarnaAction == 'payment' && $piKlarnaRequest->getControllerName() == 'checkout'
                 && ($piKlarnaUserdata["additional"]["payment"]["id"] == piKlarnaGetInvoicePaymentId()
                     || $piKlarnaUserdata["additional"]["payment"]["id"] == piKlarnaGetRatePaymentId())
            ){
            }
            if (($piKlarnaRequest->getControllerName() == 'account' 
                    && ($piKlarnaAction == 'payment' || $piKlarnaAction == 'savePayment' 
                        || $piKlarnaAction == 'saveBilling' || $piKlarnaAction == 'orders' || $piKlarnaAction == 'stornoOrder'))
                    || ($piKlarnaRequest->getControllerName() == 'checkout' 
                            && ($piKlarnaAction == 'confirm' || $piKlarnaAction == 'savePayment' || $piKlarnaAction == 'saveBilling'))
                    || ($piKlarnaRequest->getControllerName() == 'register' 
                            && ($piKlarnaAction == 'confirm' || $piKlarnaAction == 'savePayment' || $piKlarnaAction == 'saveBilling'))
                    || $piKlarnaAction == 'cart'
            ) {
                $piKlarnaCountryIso = getBillingCountry($piKlarnaView->sUserData);
                if ($piKlarnaCountryIso) {
                    $piKlarnaView->piKlarnaCountryIso = $piKlarnaCountryIso;
                    if (!checkKlarnaCurrency(strtolower($piKlarnaCountryIso))) $piKlarnaView->klarnaWrongCurrency = 1;
                }
                $piKlarnaView->KlarnaJS = true;
                $piKlarnaView->extendsTemplate('register/payment_fieldset.tpl');
                if (($piKlarnaAction == 'cart' || $piKlarnaAction == 'confirm' || $piKlarnaAction == 'payment') && $piKlarnaUserdata && checkKlarnaCountrys($klarnaShopLang)) {
                    $k = piKlarnaCreateKlarnaInstance();
                    $k->setCountry($klarnaShopLang);
                    $piKlarnaAmount = Shopware()->Modules()->Basket()->sgetAmount();
                    $piKlarnaSurcharge = piKlarnaGetSurcharge();
                    $piKlarnaBasketAmount = $piKlarnaAmount["totalAmount"];
                    $piKlarnaBasketAmount-= $piKlarnaSurcharge;
                    if ($piKlarnaBasketAmount == null) $piKlarnaBasketAmount = 0;
                    if($piKlarnaBasketAmount){
                        $pClass = $k->getCheapestPClass($piKlarnaBasketAmount, KlarnaFlags::CHECKOUT_PAGE);
                        if ($pClass) {
                            $piKlarnaView->pi_klarna_rateAmount = number_format(KlarnaCalc::calc_monthly_cost(
                                            $piKlarnaBasketAmount, 
                                            $pClass, 
                                            KlarnaFlags::CHECKOUT_PAGE), 2, ',', '.');
                            $piKlarnaView->pi_klarna_sum = $piKlarnaBasketAmount;
                            if ($piKlarnaCountryIso == "NO") {
                                $piKlarnaView->NorwayTotalCost = number_format(KlarnaCalc::total_credit_purchase_cost($piKlarnaBasketAmount, $pClass, 1), 2, ',', '.');
                                $piKlarnaView->NorwayAprCost = number_format(KlarnaCalc::apr_annuity(intval($piKlarnaBasketAmount), intval($pClass->getMonths()), $pClass->getInvoiceFee(), $pClass->getInterestRate(), $pClass->getStartFee(), 1), 2, ',', '.');
                            }
                            if ($piKlarnaConfig->pi_klarna_rate_checkout == true && $piKlarnaAction == 'confirm') $piKlarnaView->extendsTemplate('checkout/confirm_footer.tpl');
                            elseif (($piKlarnaAction == 'cart' || $piKlarnaRequest->sTargetAction == 'cart') && $piKlarnaConfig->pi_klarna_rate_cart == true) $piKlarnaView->extendsTemplate('checkout/cart_footer.tpl');
                            $piKlarnaView->RateIsTrue = true;
                        }
                    }
                }

                if ($piKlarnaAction == 'confirm' || $piKlarnaAction == 'payment') {
                    
                    $piKlarnaSurcharge = piKlarnaGetInvoiceSurcharge($piKlarnaUserdata);
                    if ($piKlarnaUserdata["additional"]["payment"]["id"] == piKlarnaGetInvoicePaymentId()
                            || $piKlarnaUserdata["additional"]["payment"]["id"] == piKlarnaGetRatePaymentId()
                    ) {
                        $piKlarnaUserdata["additional"]["payment"]["embediframe"] = true;
                        $piKlarnaView->sUserData = $piKlarnaUserdata;
                    }
                    if ($piKlarnaAction == 'confirm') {
                         if(Shopware()->Session()->klarnaAgb == true){
                             if(Shopware()->Session()->klarnaAgbChecked){
                                 $piKlarnaView->agbChecked = true;
                             }else{
                                 $piKlarnaView->agbChecked = false;
                             }
                             $piKlarnaView->piKlarnaError = true;
                             Shopware()->Session()->klarnaAgb =false;
                         }    
                         if (Shopware()->Session()->klarnaStandardAgb == true) {
                             if(Shopware()->Session()->klarnaStandardAgbChecked){
                                 $piKlarnaView->standardAgbChecked = true;
                             }else{
                                 $piKlarnaView->standardAgbChecked = false;
                             }
                             $piKlarnaView->sError = true;
                             Shopware()->Session()->klarnaStandardAgb =false;
                         } 
                         Shopware()->Session()->klarnaAgbChecked = false;
                         Shopware()->Session()->klarnaStandardAgbChecked = false;
                    }
                    $piKlarnaView->pi_klarna_surcharge = $piKlarnaSurcharge;
                    $piKlarnaResult = piKlarnaCheckBillingEqalShipping($piKlarnaUserdata);
                    if (Shopware()->Session()->sPaymentError) {
                        $piKlarnaView->piKlarnaSesssionWarning = true;
                        $piKlarnaView->PigmbhKlarnaPaymentInvoiceWarningText = Shopware()->Session()->sPaymentError;
                        $piKlarnaView->PigmbhKlarnaPaymentRateWarningText = Shopware()->Session()->sPaymentError;
                    }
                    if (Shopware()->Session()->klarnaDenied) $piKlarnaView->klarnaDenied = true;
                    if (($piKlarnaUserdata["billingaddress"]["company"] || $piKlarnaUserdata["shippingaddress"]["company"]) && ($piKlarnaCountryIso == "DE" || $piKlarnaCountryIso == "NL")) {
                        $piKlarnaView->sCompanyError = true;
                        $piKlarnaView->PigmbhKlarnaPaymentInvoiceWarningText = $piKlarnaView->pi_Klarna_lang['invoice']['companyerror'];
                        $piKlarnaView->PigmbhKlarnaPaymentRateWarningText = $piKlarnaView->pi_Klarna_lang['rate']['companyerror'];
                        $piKlarnaView->sPaymentRegisterError = true;
                    }
                    if ($piKlarnaUserdata["billingaddress"]["birthday"] == "0000-00-00" && ($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE")) {
                        $piKlarnaView->PigmbhKlarnaPaymentInvoiceWarningText = $piKlarnaView->pi_Klarna_lang['invoice']['birthdayerror'];
                        $piKlarnaView->PigmbhKlarnaPaymentRateWarningText = $piKlarnaView->pi_Klarna_lang['rate']['birthdayerror'];
                        $piKlarnaView->sPaymentRegisterError = true;
                    }
                    if (count($piKlarnaResult) && !$piKlarnaArgs->getSubject()->View()->sCompanyError) {
                        $piKlarnaView->sAddressError = true;
                        $piKlarnaInvoiceError = str_replace("{_COUNTRYNAME_}", $piKlarnaView->pi_Klarna_lang['countryName'][$piKlarnaCountryIso], $piKlarnaView->pi_Klarna_lang['invoice']['addresserror']);
                        $piKlarnaRatepayError = str_replace("{_COUNTRYNAME_}", $piKlarnaView->pi_Klarna_lang['countryName'][$piKlarnaCountryIso], $piKlarnaView->pi_Klarna_lang['rate']['addresserror']);
                        $piKlarnaView->PigmbhKlarnaPaymentInvoiceWarningText = $piKlarnaInvoiceError;
                        $piKlarnaView->PigmbhKlarnaPaymentRateWarningText = $piKlarnaRatepayError;
                        $piKlarnaView->sPaymentRegisterError = true;
                    }
                    elseif (!$piKlarnaUserdata["billingaddress"]["text4"] && !Shopware()->Session()->sPaymentError 
                            && ($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")) {
                        $piKlarnaInvoiceError = str_replace("{_COUNTRYNAME_}", $piKlarnaView->pi_Klarna_lang['countryName'][$piKlarnaCountryIso], $piKlarnaView->pi_Klarna_lang['invoice']['skanderror']);
                        $piKlarnaRatepayError = str_replace("{_COUNTRYNAME_}", $piKlarnaView->pi_Klarna_lang['countryName'][$piKlarnaCountryIso], $piKlarnaView->pi_Klarna_lang['rate']['skanderror']);
                        if ($piKlarnaView->PigmbhKlarnaPaymentInvoiceWarningText != '') $piKlarnaView->PigmbhKlarnaPaymentInvoiceWarningText.='<br />';
                        if ($piKlarnaView->PigmbhKlarnaPaymentRateWarningText != '') $piKlarnaView->PigmbhKlarnaPaymentRateWarningText.='<br />';
                        $piKlarnaView->PigmbhKlarnaPaymentInvoiceWarningText.=$piKlarnaInvoiceError;
                        $piKlarnaView->PigmbhKlarnaPaymentRateWarningText.=$piKlarnaRatepayError;
                        $piKlarnaView->sPaymentRegisterError = true;
                    }
                    if (!$piKlarnaView->RateIsTrue) {
                        $piKlarnaView->PigmbhKlarnaPaymentRateWarningText = $piKlarnaView->pi_Klarna_lang['rate']['noPclass'];
                    }
                    $piKlarnaView->pi_klarna_viewport = $piKlarnaArgs->getSubject()->Request()->sViewport;
                    $piKlarnaView->pi_klarna_actions = $piKlarnaArgs->getSubject()->Request()->getActionName();
                    if ($piKlarnaUserdata["additional"]["payment"]["name"] == "KlarnaInvoice"
                            || $piKlarnaUserdata["additional"]["payment"]["name"] == "KlarnaPartPayment"
                    ) {
                        $piKlarnaView->extendsTemplate('checkout/confirm.tpl');
                    }
                }
            }
           
            if (($piKlarnaAction == 'billing') && $piKlarnaConfig->pi_klarna_active && $piKlarnaRequest->sViewport == 'account') {
                $piKlarnaCountryIso = getBillingCountry($piKlarnaView->sUserData);
                $piKlarnaView->piKlarnaCountryIso = $piKlarnaCountryIso;
                $piKlarnaView->extendsTemplate('register/personal_fieldset.tpl');
            }
            if (($piKlarnaAction == 'orders' || $piKlarnaAction == 'stornoOrder') && $piKlarnaConfig->pi_klarna_active) {
                $piKlarnaView->klarnaStatusIds = piKlarnaGetAllStatusIds();
                piKlarnaCheckPendingOrders();
                $piKlarnaView->extendsTemplate(dirname(__FILE__) . '/Views/Frontend/account/order_item.tpl');
                $PigmbhKlarnaPaymentIds = piKlarnaGetPaymentIds();
                $piKlarnaArgs->getSubject()->View()->pi_klarna_invoice_ids = $PigmbhKlarnaPaymentIds;
            }
            $piKlarnaGetPost = $piKlarnaArgs->getSubject()->Request()->getPost();
            if (isset($piKlarnaGetPost['KlarnaSubmit']) || $piKlarnaAction == 'saveBilling') {
                $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
                $piKlarnaInvoiceId = piKlarnaGetInvoicePaymentId();
                $piKlarnaRateId = piKlarnaGetRatePaymentId();
                if ($piKlarnaConfig->pi_klarna_active == true && ($piKlarnaGetPost['KlarnaSubmit'] || $piKlarnaAction == 'saveBilling')) {
                    $piKlarnaResponse = array();
                    $textVar = false;
                    $piKlarnaUserdata = $piKlarnaArgs->getSubject()->View()->sUserData;
                    $piKlarnaResponse = $piKlarnaArgs->getSubject()->Response();
                    $piKlarnaRequest = $piKlarnaArgs->getSubject()->Request();
                    $piKlarnaResult = piKlarnaCheckBillingEqalShipping($piKlarnaUserdata);
                    if ($piKlarnaGetPost['klarnaRegister']['personal']['additional']) {
                        $textVar = "";
                        $invoiceFlag = boolean;
                        $textVar = $piKlarnaGetPost['klarnaRegister']['personal']['additional'];
                        $invoiceFlag = true;
                    }
                    elseif ($piKlarnaGetPost['klarnaRegister']['personal']['additionalRate']){
                        $textVar = $piKlarnaGetPost['klarnaRegister']['personal']['additionalRate'];
                    }
                    if ($textVar) {
                        $sql = "INSERT INTO s_user_billingaddress_attributes (billingID, text4) VALUES (?, ?) ON DUPLICATE KEY UPDATE text4=VALUES(text4)";
//                        $sql = "UPDATE s_user_billingaddress SET text4 = ? WHERE ID= ?";
                        Shopware()->Db()->query($sql, array($textVar, (int)$piKlarnaUserdata['billingaddress']['id']));
                    }
                    $piKlarnaArgs->getSubject()->View()->pi_klarna_selectedPayment = $piKlarnaUserdata['additional']['payment']['name'];
                    $piKlarnaBirthday = $piKlarnaGetPost['klarnaRegister']['personal']['birthyear'] . "-"
                            . $piKlarnaGetPost['klarnaRegister']['personal']['birthmonth'] . "-"
                            . $piKlarnaGetPost['klarnaRegister']['personal']['birthday'];
                    if ($piKlarnaBirthday == '--') {
                        $piKlarnaBirthday = $piKlarnaGetPost['klarnaRegister']['personal']['birthyearRate'] . "-"
                                . $piKlarnaGetPost['klarnaRegister']['personal']['birthmonthRate'] . "-"
                                . $piKlarnaGetPost['klarnaRegister']['personal']['birthdayRate'];
                    }
                    else $invoiceFlag = true;
                    if ($piKlarnaBirthday != '--') {
                        $sql = " UPDATE s_user_billingaddress SET birthday = ? WHERE ID = ?";
                        Shopware()->Db()->query($sql, array($piKlarnaBirthday, (int)$piKlarnaUserdata['billingaddress']['id']));
                    }
                    if ($invoiceFlag) $piKlarnaId = $piKlarnaInvoiceId;
                    else $piKlarnaId = $piKlarnaRateId;
                    if (($piKlarnaUserdata['additional']['country']['countryiso'] == 'NL' && $piKlarnaBirthday != '--' 
                        && !$piKlarnaUserdata["billingaddress"]["company"] && !$piKlarnaUserdata["shippingaddress"]["company"])
                        || ($piKlarnaUserdata['additional']['country']['countryiso'] == 'DE' && $piKlarnaBirthday != '--' 
                                && !$piKlarnaUserdata["billingaddress"]["company"] && !$piKlarnaUserdata["shippingaddress"]["company"])
                        && (count($piKlarnaResult)
                        && $piKlarnaAction != 'saveBilling')
                    ) {
                        $sql = "UPDATE s_user SET paymentID = ? WHERE id= ?";
                        Shopware()->Db()->query($sql, array((int)$piKlarnaId, (int)$piKlarnaUserdata['billingaddress']['id']));
                    }
                    elseif ($textVar && count($piKlarnaResult)) {
                        $sql = "UPDATE s_user SET paymentID = ? WHERE id= ?";
                        Shopware()->Db()->query($sql, array((int)$piKlarnaId, (int)$piKlarnaUserdata['billingaddress']['id']));
                    }
                }
            }
            $piKlarnaResponse = $piKlarnaArgs->getSubject()->Response();
            if ($piKlarnaConfig->piKlarnaShowBannerOnStartpage
                    && (!$piKlarnaRequest->isDispatched()
                    || $piKlarnaResponse->isException()
                    || $piKlarnaRequest->getModuleName() != 'frontend'
                    || $piKlarnaRequest->getControllerName() != 'index'
                    )) return;
            elseif (!$piKlarnaConfig->piKlarnaShowBannerOnStartpage
                    && (!$piKlarnaRequest->isDispatched()
                    || $piKlarnaResponse->isException()
                    || $piKlarnaRequest->getModuleName() != 'frontend'
                    )) return;
            $piKlarnaView->piKlarnaConfig = $piKlarnaConfig;
            $piKlarnaView->extendsTemplate('index/left.tpl');
            $piKlarnaView->extendsTemplate('home/right.tpl');
            $piKlarnaView->extendsTemplate('index/index.tpl');
        }
    }
    
    /**
     *  Checks if AGB are checked and sets errormessages
     *
     *  @param Enlight_Event_EventArgs $piKlarnaArgs
     */
    public static function piKlarnaOnPreDispatchCheckout(Enlight_Event_EventArgs $piKlarnaArgs) {
        $piKlarnaRequest = $piKlarnaArgs->getSubject()->Request();
        $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
        $piKlarnaView = $piKlarnaArgs->getSubject()->View();
        $piKlarnaAction = $piKlarnaRequest->getActionName();
        $piKlarnaGetPost = $piKlarnaArgs->getSubject()->Request()->getPost();
        if($piKlarnaAction=="payment"){
             if (!$piKlarnaGetPost['sAGB']) { 
                 Shopware()->Session()->klarnaStandardAgb = true;
             }
             if (!$piKlarnaGetPost['klarnaAGB']) {
                 Shopware()->Session()->klarnaAgb = true;
             }
             if($piKlarnaGetPost['sAGB'] && !$piKlarnaGetPost['klarnaAGB']){
                 Shopware()->Session()->klarnaAgbChecked = true;
             }
             else if(!$piKlarnaGetPost['sAGB'] && $piKlarnaGetPost['klarnaAGB']){
                 Shopware()->Session()->klarnaStandardAgbChecked = true;
             }
        }
    }
    

    /**
     *  Expands product details template. Adds monthly rates.
     *
     *  @param Enlight_Event_EventArgs $piKlarnaArgs
     */
    public static function piKlarnaOnPostDispatchDetail(Enlight_Event_EventArgs $piKlarnaArgs) {
        $klarnaShopLang = checkKlarnaCountryCurrencys();
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/Frontend/');
        if ($klarnaShopLang == 'de') $klarnaShopLang = Shopware()->Locale()->getLanguage();
        $piKlarnaConfig = array();
        $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
        if ($piKlarnaConfig->pi_klarna_rate_produkt && $piKlarnaConfig->pi_klarna_active && checkKlarnaCountrys($klarnaShopLang)) {
            $k = piKlarnaCreateKlarnaInstance();
            $k->setCountry($klarnaShopLang);
            $piKlarnaView = $piKlarnaArgs->getSubject()->View();
            $piKlarnaView->KlarnaJS = true;
            $articleprice = str_replace(",", ".", $piKlarnaView->sArticle['price']);
            if($articleprice){
                if ($k->getCheapestPClass($articleprice, KlarnaFlags::PRODUCT_PAGE)) {
                    $piKlarnaView->pi_klarna_rate = number_format(KlarnaCalc::calc_monthly_cost(
                        $articleprice, $k->getCheapestPClass($articleprice, KlarnaFlags::PRODUCT_PAGE), KlarnaFlags::PRODUCT_PAGE
                    ), 2, ',', '.');
                }
                $piKlarnaView->pi_klarna_sum = $piKlarnaView->pi_klarna_rate;
                $piKlarnaView->extendsTemplate('detail/index.tpl');
            }
        }
    }

    /**
     * add rates to Productlistings
     *
     * @param Enlight_Event_EventArgs $piKlarnaArgs
     */
    public static function piKlarnaOnPostDispatchListing(Enlight_Event_EventArgs $piKlarnaArgs) {
        $piKlarnaConfig = array();
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/Frontend/');
        $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
        $klarnaShopLang = checkKlarnaCountryCurrencys();
        if ($klarnaShopLang == 'de') $klarnaShopLang = Shopware()->Locale()->getLanguage();
        
        if ($piKlarnaConfig->pi_klarna_rate_listing && $piKlarnaConfig->pi_klarna_active && checkKlarnaCountrys($klarnaShopLang)) {
            $piKlarnaArticlePrice = array();
            $piKlarnaView = $piKlarnaArgs->getSubject()->View();
            $piKlarnaView->KlarnaJS = true;

            if (sizeof($piKlarnaView->sArticles) > 0) {
                $piKlarnaView->piKlarnaArticles = true;
                foreach($piKlarnaView->sArticles as $key => $article) {
                    $piKlarnaArticlePrice[$key] = str_replace(",", ".", $article['price']);
                }
            }
            else {
                $piKlarnaArticlePrice[0] = 0;
                $piKlarnaView->piKlarnaOffers = true;
                foreach($piKlarnaView->sOffers as $key => $article) {
                    $piKlarnaArticlePrice[$key] = str_replace(",", ".", $article['price']);
                }
            }
            $counter = array();
            $pi_klarna_value = array();
            //for ($i = 0; $i < sizeof($piKlarnaArticlePrice); $i++) {
            $i = 0;
            foreach($piKlarnaArticlePrice as $key=>$price) {
                if($price){
                    $k = piKlarnaCreateKlarnaInstance();
                    $k->setCountry($klarnaShopLang);
                    if ($k->getCheapestPClass($price, KlarnaFlags::PRODUCT_PAGE)){ 
                        $pi_klarna_value[$key] = number_format(KlarnaCalc::calc_monthly_cost($piKlarnaArticlePrice[$key], 
                            $k->getCheapestPClass($piKlarnaArticlePrice[$key], KlarnaFlags::PRODUCT_PAGE), 
                            KlarnaFlags::PRODUCT_PAGE), 2, ',', '.');
                    }
                    $counter[$key] = $key;
                }
                $i++;
            }
            $piKlarnaView->pi_klarna_counter = $counter;
            $piKlarnaView->pi_klarna_rate = $pi_klarna_value;
            $piKlarnaView->pi_klarna_sum = $piKlarnaView->extendsTemplate('listing/box_article.tpl');
        }
    }

    /**
     *  Expands the e-mail with Klarna inpi_klarna_formations
     *
     *  @param Enlight_Hook_HookArgs $piKlarnaArgs
     */
    public static function piKlarnaOnSendMail(Enlight_Hook_HookArgs $piKlarnaArgs) {
        $piKlarnaLang = array();
        $piKlarnaLang = piKlarnaGetLanguage(Shopware()->Locale()->getLanguage());
        $piKlarnaMailVars = $piKlarnaArgs->variables;
        $PigmbhKlarnaPaymentAction = $piKlarnaArgs->variables['additional']['payment']['action'];
        if ($PigmbhKlarnaPaymentAction == "PiPaymentKlarna") {
            if ($piKlarnaMailVars['additional']['payment']['additionaldescription'] == "Bezahlung via Rechnung innerhalb von 14 Tagen!"){ 
                $piKlarnaMailVars['additional']['payment']['additionaldescription'] = $piKlarnaLang['invoice']['mailtext'];
            }
            else $piKlarnaMailVars['additional']['payment']['additionaldescription'] = $piKlarnaLang['rate']['mailtext'];
            $piKlarnaArgs->set('variables', $piKlarnaMailVars);
        }
    }

    /**
     * Event listener methods
     *
     * @param Enlight_Event_EventArgs $pi_klarna_args
     */
    public static function piKlarnaOnGetControllerPathFrontend(Enlight_Event_EventArgs $piKlarnaArgs) {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/'); 
        return dirname(__FILE__) . '/controller/Frontend/PiKlarnaControllerFrontend.php';
    }
    
     /**
     * Event listener methods
     *
     * @param Enlight_Event_EventArgs $pi_klarna_args
     */
    public static function piKlarnaOnGetControllerPathBackend(Enlight_Event_EventArgs $piKlarnaArgs) {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/'); 
        return dirname(__FILE__) . '/controller/Backend/PiKlarnaControllerBackend.php';
    }

    /**
     * Plugin Inpi_klarna_formations for the Plugin Manager
     *
     *  @return	array with inpi_klarna_formations
     */
    public function getInfo() {
        $image = base64_encode(file_get_contents(dirname(__FILE__) . '/img/klarnaLogo.png'));
        return array(
            'version' => $this->getVersion(),
            'autor' => 'Payintelligent GmbH',
            'copyright' => 'Copyright (c) 2012, Payintelligent GmbH',
            'label' => 'Klarna Payment Module',
            'source' => 'Default',
            'description' => '<p><img src="data:image/png;base64,' . $image . '" /></p>
                <p style="font-size:12px">
                Bitte bearbeiten Sie alle mit Klarna Rechnung und Klarna Ratenzahlung gezahlten Rechnungen'
            . ' nur in unserer eigenen Rechnungsverwaltung, damit alle &Auml;nderungen an Klarna &uuml;bermittelt werden.<br />
                Diese finden Sie unter Kunden->Zahlungen->Klarna (nach dem aktualisieren der Seite)<br />
                <span style="color:red;font-size">Bitte beachten Sie, das f&uuml;r Klarna Ratenzahlung '
            . 'kein Zahlartenaufschlag festgelegt werden darf(laut dt. Gesetz). Jeder Aufschlag auf Klarna '
            . 'Rate wird im Checkout auf 0 gesetz.</span></p>'
            . '<p style="font-size:80%">Wenn Sie keine H&auml;ndler ID und/oder kein Secret haben, besuchen Sie nun unsere '
            . '<a href="https://merchants.klarna.com/signup/de?locale=de&partner_id=26e1c567a525192f6dcb8bfbfe2e714e488a84bf"'
            . 'target="_blank" title="Zur H&auml;ndlerregistrierung von Klarna">H&auml;ndlerregistrierung'
            . '</a> und erstellen Sie sich Ihr Konto bei Klarna.</p>
            ',
            //'changes' => 'Ready for Shopware 3.5.4 - 3.5.6. Supports multishops',
            'license' => '',
            'link' => 'http://www.payintelligent.de/',
            'support' => 'http://www.payintelligent.de/'
        );
    }
    
    /**
     * returns current Plugin Version 
     *
     * @return String with Plugin Version
     */
    public function getVersion(){
       return "2.0.7";
    }

    /**
     * Plugin unistall method
     *
     * @return	bool
     */
    public function uninstall() {
        try {
            $piKlarnaInvoiceId = piKlarnaGetInvoicePaymentId();
            $piKlarnaRateId = piKlarnaGetRatePaymentId();
            $piKlarnaPluginId = piKlarnaGetPluginId();
            $sql = "DELETE FROM s_core_rulesets WHERE paymentID = ? OR paymentID = ?";
            Shopware()->Db()->query($sql, array((int)$piKlarnaInvoiceId, (int)$piKlarnaRateId));
            $sql = "DELETE FROM s_core_paymentmeans WHERE name IN ('KlarnaInvoice','KlarnaPartPayment')";
            Shopware()->Db()->query($sql);
            $sql = "DELETE FROM s_core_states
                    WHERE description IN
                    (
                        '<span style=\"color:orange\">Zahlung wird von Klarna gepr&uuml;ft</span>',
                        '<span style=\"color:green\">Zahlung von Klarna akzeptiert</span>',
                        '<span style=\"color:red\">Zahlung von Klarna nicht akzeptiert</span>',
                        '<span style=\"color:red\">Reservierung abgebrochen</span>',
                        '<span style=\"color:orange\">Reservierung teilweise aktiviert</span>',
                        '<span style=\"color:green\">Reservierung komplett aktiviert</span>',
                        '<span style=\"color:orange\">Teilweise retourniert</span>',
                        '<span style=\"color:red\">Komplett retourniert</span>',
                        '<span style=\"color:orange\">Teilweise storniert</span>',
                        '<span style=\"color:red\">Komplett storniert</span>'
                    )";    
            Shopware()->Db()->query($sql);
            return parent::uninstall();
        }
        catch (Exception $e) {
            throw new Exception('Fehler beim deinstallieren(uninstall)');
        }
        return true;
    }
}
require_once dirname(__FILE__) . '/functions/PiKlarnaFunctions.php';
