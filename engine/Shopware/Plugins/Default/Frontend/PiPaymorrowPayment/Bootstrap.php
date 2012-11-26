<?php

/**
 * paymorrow Payment Module
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @author       PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @package      PayIntelligent_Paymorrow
 * @copyright    (C) 2011 Paymorrow GmbH. All rights reserved. <http://www.paymorrow.de/>

 */
class Shopware_Plugins_Frontend_PiPaymorrowPayment_Bootstrap extends Shopware_Components_Plugin_Bootstrap {

    /**
     * Install plugin method
     *
     * @throws Exception $e
     *
     * @return bool
     */
    public function install() {
        $this->createPayments();
        $this->createEvents();
        $this->createForm();
        $this->createRuleset();
        $this->DbInserts();
        $this->createTables();
        $this->getOldInvoices();
        $this->activatePlugin();
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
        $this->onPaymorrowBackendPlugin($config);
        return true;
    }

    /**
     * create and save payments
     *
     * @throws Exception $e4545
     *
     * @return bool
     */
    protected function createPayments() {
        try {
            $paymentRow = Shopware()->Payments()->createRow(array(
                        'name' => 'PaymorrowInvoice',
                        'description' => 'Rechnungskauf',
                        'additionaldescription' => '&bull; Rechnungskauf &uuml;ber paymorrow wird ausschlie&szlig;lich'
                        . ' f&uuml;r voll gesch&auml;ftsf&auml;hige Privatpersonen angeboten.<br />'
                        . '&bull; Die Zustellung an Packstationen oder Express-Lieferungen'
                        . ' sind nicht m&ouml;glich.<br />'
                        . '&bull; Bei der Erstbestellung m&uuml;ssen Wohnadresse'
                        . ' und Lieferadresse &Uuml;bereinstimmen.',
                        'action' => 'PiPaymentPaymorrow',
                        'active' => 0,
                        'pluginID' => $this->getId()
                    ))->save();
            $paymentRow = Shopware()->Payments()->createRow(array(
                        'name' => 'PaymorrowRate',
                        'description' => 'Ratenzahlung',
                        'additionaldescription' => '&bull; Ratenzahlung &uuml;ber paymorrow wird ausschlie&szlig;lich f&uuml;r'
                        . ' voll gesch&auml;ftsf&auml;hige Privatpersonen angeboten.<br />'
                        . '&bull; Die Zustellung an Packstationen oder Express-Lieferungen'
                        . ' sind nicht m&ouml;glich.<br />'
                        . '&bull; Bei der Erstbestellung m&uuml;ssen Wohnadresse'
                        . ' und Lieferadresse &Uuml;bereinstimmen.',
                        'action' => 'PiPaymentPaymorrow',
                        'active' => 0,
                        'pluginID' => $this->getId()
                    ))->save();
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen der Zahlarten(createPayments)</b><br />' . $e);
        }
        return true;
    }

    /**
     * create and save configuration form
     *
     * @throws Exception $e
     *
     * @return bool
     */
    protected function createForm() {
        try {
            $form = $this->Form();
            $form->setElement('checkbox', 'paymorrow_active', array(
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'label' => 'Aktiv',
                'value' => false,
                'attributes' => array(
                    "uniqueId" => 'paymorrow_active'
                )
            ));
            $form->setElement('button', 'button_1', array(
                'label' => '<b style="color:red;width: 800px;">Livemodus Einstellungen:</b>',
                'value' => ''
            ));
            $form->setElement('text', 'merchant_id', array(
                'label' => 'MerchantID',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'security_code', array(
                'label' => 'Passwort',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'server_url', array(
                'label' => 'Server URL',
                'value' => 'paymorrow.net',
                'attributes' => array(
                    "uniqueId" => 'server_url'
                )
            ));
            $form->setElement('button', 'button_2', array(
                'label' => '<b style="color:red;width: 800px;">'
                . 'Testmodus Einstellungen:</b>',
                'value' => ''
            ));
            $form->setElement('text', 'merchant_id_sandbox', array(
                'label' => 'MerchantID Testmodus',
                'value' => 'shopware4test',
                'attributes' => array(
                    "uniqueId" => 'merchant_id_sandbox'
                )
            ));
            $form->setElement('text', 'security_code_sandbox', array(
                'label' => 'Passwort Testmodus',
                'value' => 'shopware4key',
                'attributes' => array(
                    "uniqueId" => 'security_code_sandbox'
                )
            ));
            $form->setElement('text', 'server_url_sandbox', array(
                'label' => 'Server URL Testmodus',
                'value' => 'test.paymorrow.net',
                'attributes' => array(
                    "uniqueId" => 'server_url_sandbox'
                )
            ));
            $form->setElement('button', 'button_3', array(
                'label' => '<b style="color:red;width: 800px;">'
                . 'Allgemeine Einstellungen:</b>',
                'value' => ''
            ));
            $form->setElement('checkbox', 'sandbox_mode', array(
                'label' => 'Testmodus',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'server_path', array(
                'label' => 'Server Pfad',
                'value' => '/perth/services/PaymorrowService.Paymorrow',
                'required' => true,
                'attributes' => array(
                    "uniqueId" => 'server_path'
                )
            ));
            $form->setElement('text', 'server_port', array(
                'label' => 'Server Port',
                'value' => '443',
                'required' => true,
                'attributes' => array(
                    "uniqueId" => 'server_port'
                )
            ));
            $form->setElement('text', 'basket_min', array(
                'label' => 'Warenkorb Mindestbetrag f&uuml;r Paymorrow Rechnung in &euro;',
                'value' => '10',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_max', array(
                'label' => 'Warenkorb Maximalbetrag f&uuml;r Paymorrow Rechnung in &euro;',
                'value' => '1500',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_min_rate', array(
                'label' => 'Warenkorb Mindestbetrag f&uuml;r Paymorrow Ratenkauf in &euro;',
                'value' => '10',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_max_rate', array(
                'label' => 'Warenkorb Maximalbetrag f&uuml;r Paymorrow Ratenkauf in &euro;',
                'value' => '1500',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('button', 'button_4', array(
                'label' => '<b style="width: 800px;">'
                . 'Weisen Sie hier die Kategorie zu die in Ihrem Shop angeboten wird</b>',
                'value' => ''
            ));

            $form->setElement('combo', 'katergorie', array(
                'label' => 'Kategorie zuweisen',
                'value' => 'Misc',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => array(
                    array("Accessoires", "Accessoires"), array("Alcohol", "Alcohol"), array("Antiques", "Antiques"), array("Art", "Art"), array("Books", "Books"),
                    array("Cameras", "Cameras"), array("Caraccessoires", "Caraccessoires"), array("Clothing", "Clothing"), array("Computergames", "Computergames"),
                    array("Computers", "Computers"), array("Craft", "Craft"), array("Decoration","Decoration"), array("Diy","Diy"), array("Electronics","Electronics"),
                    array("Ethnic", "Ethnic"), array("Fashion", "Fashion"), array("Flatscreensrt", "Flatscreensrt"), array("Flowers", "Flowers"), array("Food", "Food"),
                    array("Furniture", "Furniture"), array("Garden", "Garden"), array("Gifts", "Gifts"), array("Health", "Health"), array("Hobby", "Hobby"),
                    array("Jewelry", "Jewelry"), array("Laptops", "Laptops"), array("Magazines", "Magazines"), array("Misc", "Misc"), array("Movies", "Movies"),
                    array("Music", "Music"), array("Niche", "Niche"), array("Officesupplies", "Officesupplies"), array("OTCDrugs", "OTCDrugs"), array("Petsupplies", "Petsupplies"),
                    array("Photography", "Photography"), array("Prescriptiondrugs", "Prescriptiondrugs"), array("religious", "religious"), array("Shoes", "Shoes"),
                    array("Software", "Software"), array("Sports", "Sports"), array("Stationary", "Stationary"), array("Tickets", "Tickets"), array("Tobacco", "Tobacco"),
                    array("Tools", "Tools"), array("Toys", "Toys"), array("Watches", "Watches"), array("Wedding", "Wedding"), array("Whiteware", "Whiteware")
                )
            ));
            $form->setElement('button', 'button_5', array(
                'label' => '<b style="width: 800px;">'
                . 'Aktivieren Sie hier die Versandarten, die Sie mit Paymorrow anbieten</b>',
                'value' => ''
            ));
            $form->setElement('select', 'versand', array(
                'label' => 'Versandart zuweisen',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'value' => 'MISC STANDARD',
                'store' => array(
                    array("DHL STANDARD", "DHL STANDARD"), array("DHL EXPRESS","DHL EXPRESS"),
                    array("DPD STANDARD","DPD STANDARD"), array("DPD EXPRESS","DPD EXPRESS"),
                    array("FEDEX STANDARD","FEDEX STANDARD"), array("FEDEX EXPRESS","FEDEX EXPRESS"),
                    array("GLS STANDARD","GLS STANDARD"), array("GLS EXPRESS","GLS EXPRESS"),
                    array("GO! STANDARD","GO! STANDARD"), array("GO! EXPRESS","GO! EXPRESS"),
                    array("HERMES STANDARD","HERMES STANDARD"),array("HERMES EXPRESS","HERMES EXPRESS"),
                    array("MISC STANDARD","MISC STANDARD"), array("MISC EXPRESS","MISC EXPRESS"),
                    array("TNT STANDARD","TNT STANDARD"), array("TNT EXPRESS","TNT EXPRESS"),
                    array("TRANSOFLEX STANDARD","TRANSOFLEX STANDARD"), array("TRANSOFLEX EXPRESS","TRANSOFLEX EXPRESS"),
                    array("UPS STANDARD","UPS STANDARD"), array("UPS EXPRESS","UPS EXPRESS")
                )
            ));
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen des Einstellungsformulars(createForm)</b><br />' . $e);
        }
        return true;
    }

    /**
     * save ruleset in database
     *
     * @throws Exception $e
     *
     * @return bool
     */
    protected function createRuleset() {
        try {          
            $piPaymorrowInvoicePaymentId = piPaymorrowGetInvoicePaymentId();
            $piPaymorrowRatePaymentId = piPaymorrowGetRatePaymentId();           
            $sql = "INSERT INTO `s_core_rulesets` (`paymentID`, `rule1`, `value1`, `rule2`, `value2`) VALUES
                    (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array(
                $piPaymorrowInvoicePaymentId, 'ZONEISNOT', 'deutschland', '', '',
                $piPaymorrowInvoicePaymentId, 'CURRENCIESISOISNOT', 'EUR', '', '',
                $piPaymorrowInvoicePaymentId, 'LANDISNOT', 'DE', '', '',
                $piPaymorrowRatePaymentId, 'ZONEISNOT', 'deutschland', '', '',
                $piPaymorrowRatePaymentId, 'CURRENCIESISOISNOT', 'EUR', '', '',
                $piPaymorrowRatePaymentId, 'LANDISNOT', 'DE', '', ''
            ));      
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen der Rulesets(createRuleset)</b><br />' . $e);
        }
        return true;
    }

    /**
     *     Database insert
     *
     * @throws Exception $e
     *
     * @return bool
     */
    protected function DbInserts() {
        try {
            $piPaymorrowInvoicePaymentId = piPaymorrowGetInvoicePaymentId();
            $sql = 'INSERT INTO `s_core_paymentmeans_countries` (`paymentID`, `countryID`) VALUES (?, ?);';
            Shopware()->Db()->query($sql, array($piPaymorrowInvoicePaymentId, 2));
            $piPaymorrowRatePaymentId = piPaymorrowGetRatePaymentId();
            $sql = 'INSERT INTO `s_core_paymentmeans_countries` (`paymentID`, `countryID`) VALUES (?, ?);';
            Shopware()->Db()->query($sql, array($piPaymorrowRatePaymentId, 2));
            $piPaymorrowStatesMaxId = Shopware()->Db()->fetchOne("SELECT max(id) from s_core_states");
            $piPaymorrowStatesPendingId = $piPaymorrowStatesMaxId + 1;
            $piPaymorrowStatesAcceptedId = $piPaymorrowStatesMaxId + 2;
            $piPaymorrowStatesDeclinedId = $piPaymorrowStatesMaxId + 3;
            $sql = "
                INSERT INTO `s_core_states` (`id`, `description`, `position`, `group`, `mail`) 
                VALUES (?, ?, ?, ?, ?),(?, ?, ?, ?, ?),(?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array(
                $piPaymorrowStatesPendingId, '<span style=color:orange>Paymorrow Pending</span>', 1, 'payment', 0,
                $piPaymorrowStatesAcceptedId, '<span style=color:green>Paymorrow Accepted</span>', 2, 'payment', 1,
                $piPaymorrowStatesDeclinedId, 'Paymorrow Declined', 3, 'payment', 0
            ));
            $sql = "
                INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
                (1, 'Paymorrow_Content_Info', '', ?),
                (1, 'Paymorrow_no_Bankdata_Content_Info', '', ?),
                (1, 'Paymorrow_Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 13mm;', ?)
            ";
            Shopware()->Db()->query($sql, array(
                '<span class="piPaymorrowSmallerFont">Bitte &Uuml;berweisen Sie den Betrag auf folgendes Konto:</span>
                <table width="80%" border="0" cellpadding="0" cellspacing="1" class="piPaymorrowBankTable">
                    <tr>
                      <td bgcolor="#F7F7F2" class="piPaymorrowBankTableTd" style="width:150px">'
                . '<strong>Empf&auml;nger:</strong></td>
                      <td>paymorrow GmbH</td>		   
                    </tr>
                    <tr>
                      <td bgcolor="#F7F7F2" class="piPaymorrowBankTableTd"><strong>Geldinstitut:</strong></td>
                      <td>{$paymorrow.bankName}</td>		   
                    </tr>
                    <tr>
                      <td bgcolor="#F7F7F2" class="piPaymorrowBankTableTd">'
                . '<strong>Bankleitzahl:</strong></td>
                      <td>{$paymorrow.bankCode}</td>		   
                    </tr>
                    <tr>
                      <td bgcolor="#F7F7F2" class="piPaymorrowBankTableTd"><strong>Kontonummer:</strong></td>
                      <td>{$paymorrow.accountNumber}</td>		   
                    </tr>
                    <tr>
                      <td bgcolor="#F7F7F2" class="piPaymorrowBankTableTd"><strong>Verwendungszweck 1: </strong></td>
                      <td>{$paymorrow.reference}</td>		   
                    </tr>
                    <tr>
                      <td bgcolor="#F7F7F2" class="piPaymorrowBankTableTd"><strong>Verwendungszweck 2:</strong></td>
                      <td>{$paymorrow.reference2}</td>		   
                    </tr>
                    <tr style="padding-top:5px;">
                      <td bgcolor="#F7F7F2" class="piPaymorrowBankTableTd"><strong>bic:</strong></td>
                      <td>{$paymorrow.bic}</td>		   
                    </tr>
                    <tr>
                      <td bgcolor="#F7F7F2" class="piPaymorrowBankTableTd"><strong>iban:</strong></td>
                      <td>{$paymorrow.iban}</td>		   
                    </tr>
                </table>',
                '<span class="piPaymorrowSmallFont">'
                . 'Bei der Abwicklung des Rechnungskaufs greifen wir auf den Service der paymorrow'
                . ' GmbH zur&uuml;ck. Verwenden Sie deshalb zur Bezahlung bitte ausschlie&szlig;lich die'
                . ' Kontoverbindung, welche Ihnen f&uuml;r diesen Kauf von der paymorrow GmbH bereits'
                . ' per E-Mail mitgeteilt wurde.</span>',
                '<table style="height: 90px;" border="0" width="100%">
                    <tbody>
                        <tr valign="top">
                            <td style="width: 33%;">
                                <p><span style="font-size: xx-small;">Demo GmbH</span></p>
                                <p><span style="font-size: xx-small;">'
                . 'Steuer-Nr <br />'
                . 'UST-ID: <br />'
                . 'Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>
                            </td>
                            <td style="width: 33%;">
                                <p><span style="font-size: xx-small;">AGB<br /></span></p>
                                <p><span style="font-size: xx-small;">'
                . 'Gerichtsstand ist Musterstadt<br />'
                . 'Erf&uuml;llungsort Musterstadt<br />'
                . 'Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>
                            </td>
                            <td style="width: 33%;">
                                <p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>
                                <p><span style="font-size: xx-small;">Max Mustermann</span></p>
                            </td>
                        </tr>
                    </tbody>
                 </table>'
            ));
        }
        catch (Exception $e) {
            $this->uninstall();
            $sql = "DELETE FROM s_crontab WHERE name like '%Paymorrow%'";
            Shopware()->Db()->query($sql);
            throw new Exception('<b>Fehler beim Eintragen in die Datenbanken(DbInserts)</b><br />' . $e);
        }
        return true;
    }

    /**
     * Create and subscribe events and hooks
     *
     * @throws Exception $e
     *
     * @return bool
     */
    protected function createEvents() {
        try {
            $event = $this->createEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PiPaymentPaymorrow', 
                'onGetPaymorrowControllerPathFrontend'
            );
            $this->subscribeEvent($event);

            $event = $this->createEvent(
                    'Enlight_Controller_Action_PostDispatch', 
                'onPostPaymorrowDispatch'
            );
            $this->subscribeEvent($event);

            $event = $this->createHook(
                    'Shopware_Components_Document', 'assignValues', 'onBeforeRenderDocument', Enlight_Hook_HookHandler::TypeAfter, 0
            );
            $this->subscribeHook($event);

//            $event = $this->createEvent(
//                    'Enlight_Controller_Action_PreDispatch_Backend_Plugin', 'onPaymorrowBackendPlugin'
//            );
//            $this->subscribeEvent($event);

            $event = $this->createEvent(
                    'Shopware_CronJob_PiPaymorrowPayment', 'myPaymorrowCron'
            );
            $this->subscribeEvent($event);
            $this->subscribeCron('PiPaymorrowPayment', 'PiPaymorrowPayment', 5, true); // 86400
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen der Events und Hooks(createEvents)</b><br />' . $e);
        }
        return true;
    }

    /**
     * Updates orders previously made with this plugin and updates pament id
     *
     * @throws Exception $e
     *
     * @return bool
     */
    protected function getOldInvoices() {
        try {
            $sql = "SELECT ordernumber, responseResultCode FROM pi_paymorrow_orders  WHERE type = ?";
            $piPaymorrowInvoiceOrdernumber = Shopware()->Db()->fetchAll($sql, array('PaymorrowInvoice'));
            $piPaymorrowRateOrdernumber = Shopware()->Db()->fetchAll($sql, array('PaymorrowRate'));
            $piPaymorrowInvoiceId = piPaymorrowGetInvoicePaymentId();
            $piPaymorrowRateId = piPaymorrowGetRatePaymentId();
            $sql = "SELECT id  FROM s_core_states  WHERE description like ?";
            $piPaymorrowPendingId = Shopware()->Db()->fetchOne($sql, array('%Paymorrow Pending%'));
            $piPaymorrowAcceptedId = Shopware()->Db()->fetchOne($sql, array('%Paymorrow Accepted%'));
            $sql = "UPDATE s_order SET paymentID= ?, cleared= ? WHERE ordernumber = ?";
            for ($i = 0; $i < sizeof($piPaymorrowInvoiceOrdernumber); $i++) {
                if ($piPaymorrowInvoiceOrdernumber[$i]["responseResultCode"] == 'PENDING') $clearedvar = $piPaymorrowPendingId;
                else $clearedvar = $piPaymorrowAcceptedId;
                Shopware()->Db()->query($sql, array(
                    (int)$piPaymorrowInvoiceId, 
                    (int)$clearedvar, 
                    $piPaymorrowInvoiceOrdernumber[$i]["ordernumber"]));
            }
            for ($i = 0; $i < sizeof($piPaymorrowRateOrdernumber); $i++) {
                if ($piPaymorrowRateOrdernumber[$i]["responseResultCode"] == 'PENDING') $clearedvar = $piPaymorrowPendingId;
                else $clearedvar = $piPaymorrowAcceptedId;
                Shopware()->Db()->query($sql, array(
                    (int)$piPaymorrowRateId, 
                    (int)$clearedvar, 
                    $piPaymorrowRateOrdernumber[$i]["ordernumber"]));
            }
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim holen der alten Rechnungen(getOldInvoices)</b><br />' . $e);
        }
        return true;
    }

    /**
     * Creates tables
     *
     * @throws Exception $e
     *
     * @return bool
     */
    protected function createTables() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `pi_paymorrow_orders`
                (     
                  `id`                              INT NOT NULL AUTO_INCREMENT,
                  `ordernumber`                     VARCHAR( 255 ) NULL, 
                  `type`                            VARCHAR( 255 ) NULL, 
                  `transactionid`                   VARCHAR( 255 ) NULL, 
                  `requestid`                       VARCHAR( 255 ) NULL,
                  `responseResultCode`              VARCHAR( 255 ) NULL,
                  `bic`                             VARCHAR( 255 ) NULL,
                  `iban`                            VARCHAR( 255 ) NULL,
                  `nationalBankName`                VARCHAR( 255 ) NULL,
                  `nationalBankCode`                VARCHAR( 255 ) NULL,
                  `nationalBankAccountNumber`       VARCHAR( 255 ) NULL,
                  `paymentReference`                VARCHAR( 255 ) NULL,
                  `signature`                       VARCHAR( 255 ) NULL,
                  `fullSend`                        INT( 1 ) NULL,
                   PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
            Shopware()->Db()->query($sql);
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen der Tabellen(createTables)</b><br />' . $e);
        }
        return true;
    }

    /**
     * Activate Plugin
     */
    protected function activatePlugin() {
        try {
            $pluginId = piPaymorrowGetPluginId();
            $sql = "UPDATE s_core_plugins SET active='1' WHERE id = ?";
            Shopware()->Db()->query($sql, array((int)$pluginId));
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim aktivieren des Plugins(activatePlugin)</b><br />' . $e);
        }
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public static function onGetPaymorrowControllerPathFrontend(Enlight_Event_EventArgs $args) {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/templates/');
        return dirname(__FILE__) . '/controller/PiPaymentPaymorrow.php';
    }

    /**
     * Event listener method
     *
     * @param null $config
     * @return void
     * @internal param \Enlight_Event_EventArgs $args
     */
    function onPaymorrowBackendPlugin($config = null) {
        $piPaymorrowConfig    = $config;
        $piPaymorrowInvoiceId = piPaymorrowGetInvoicePaymentId();
        $piPaymorrowRateId    = piPaymorrowGetRatePaymentId();
        if (!is_null($config)) {
            $sql = "DELETE FROM s_core_rulesets WHERE paymentID = ? AND(rule1 like 'SUBSHOP')";
            Shopware()->Db()->query($sql, array((int)$piPaymorrowRateId));
            $sql = "DELETE FROM s_core_rulesets WHERE paymentID = ? AND(rule1 like 'SUBSHOP')";
            Shopware()->Db()->query($sql, array((int)$piPaymorrowInvoiceId));
            //for ($i = 1; $i <= count($piPaymorrowConfig); $i++) {
            foreach($piPaymorrowConfig as $key => $value) {
                if ($value['paymorrow_active'] == false) {
                    $sql = "Insert into s_core_rulesets (`paymentID`, `rule1`, `value1`, `rule2`, `value2`)
                            VALUES(?,'SUBSHOP',?,'',''), (?,'SUBSHOP',?,'','')";
                    Shopware()->Db()->query($sql, array((int)$piPaymorrowInvoiceId, (int)$key, (int)$piPaymorrowRateId, (int)$key));
                }
                elseif ($value['paymorrow_active'] == true) {
                    $sql ="DELETE FROM s_core_rulesets WHERE paymentID = ? AND rule1 like 'SUBSHOP' AND value1 = ?";
                    Shopware()->Db()->query($sql,array((int)$piPaymorrowInvoiceId, (int)$key));
                    Shopware()->Db()->query($sql,array((int)$piPaymorrowRateId, (int)$key));
                }
                $value['basket_min']      = str_replace(',', '.', $value['basket_min']);
                $value['basket_max']      = str_replace(',', '.', $value['basket_max']);
                $value['basket_min_rate'] = str_replace(',', '.', $value['basket_min_rate']);
                $value['basket_max_rate'] = str_replace(',', '.', $value['basket_max_rate']);
                
                $sql = "Insert into s_core_rulesets(`paymentID`, `rule1`, `value1`, `rule2`, `value2`)
                        VALUES
                        (?, 'SUBSHOP', ?, 'ORDERVALUELESS', ?), 
                        (?, 'SUBSHOP', ?, 'ORDERVALUELESS', ?), 
                        (?, 'SUBSHOP', ?, 'ORDERVALUEMORE', ?), 
                        (?, 'SUBSHOP', ?, 'ORDERVALUEMORE', ?)";
                Shopware()->Db()->query($sql, array( 
                    (int)$piPaymorrowInvoiceId, (int)$key, $value['basket_min'],
                    (int)$piPaymorrowRateId, (int)$key, $value['basket_min_rate'],  
                    (int)$piPaymorrowInvoiceId, (int)$key, $value['basket_max'],               
                    (int)$piPaymorrowRateId, (int)$key, $value['basket_max_rate']
                ));
            }
        }
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $piPaymorrowArgs
     * @return void
     * @internal param \Enlight_Event_EventArgs $args
     */
    function onPostPaymorrowDispatch(Enlight_Event_EventArgs $piPaymorrowArgs) {
        
        $piPaymorrowView    = $piPaymorrowArgs->getSubject()->View();
        /** @var $piPaymorrowRequest Enlight_Controller_Request_Request */
        $piPaymorrowRequest = $piPaymorrowArgs->getSubject()->Request();
        // break if no template is set.
        if(!$piPaymorrowView->hasTemplate()) {
            return;
        }
        
        if ($piPaymorrowArgs->getSubject()->Request()->getModuleName() == 'frontend') {
//            $piPaymorrowView->addTemplateDir(dirname(__FILE__) . '/templates/');
            $piPaymorrowLanguage                    = piPaymorrowGetLanguage($piPaymorrowArgs);
            
            $piPaymorrowView->pi_Paymorrow_lang     = $piPaymorrowLanguage;
            $piPaymorrowView->pi_Paymorrow_Viewport = $piPaymorrowRequest->getControllerName();
            
            $piPaymorrowConfig                      = Shopware()->Plugins()->Frontend()->PiPaymorrowPayment()->Config();
            $piPaymorrowUserdata                    = $piPaymorrowView->sUserData;
            $piPaymorrowView->addTemplateDir(dirname(__FILE__) . '/templates/_default/frontend/');
            
//             account and payment selection OR checkout and confirm
            if (($piPaymorrowRequest->getControllerName() == 'account' && $piPaymorrowRequest->getActionName() == 'payment')
                    || $piPaymorrowRequest->getControllerName() == 'checkout' && $piPaymorrowRequest->getActionName() == 'confirm') 
            {               
                $piPaymorrowBasket = Shopware()->Modules()->sBasket()->sGetAmountArticles();
                $piPaymorrowView->pi_Paymorrow_paymentWarningText = false;
                // error handling                
                if (Shopware()->Session()->sPaymorrowPaymentError) {
                    $piPaymorrowView->sPaymorrowPaymentError = true;
                    $piPaymorrowView->pi_Paymorrow_paymentWarningText = Shopware()->Session()->sPaymorrowPaymentError;
                }
                if (Shopware()->Session()->pi_Paymorrow_no_paymorrow) {
                    $piPaymorrowView->pi_Paymorrow_no_Paymorrow = true;
                    $piPaymorrowView->pi_Paymorrow_paymentWarningText = $piPaymorrowLanguage['payment_warning']['declined'];
                }
                $piPaymorrowView->extendsTemplate('index/header.tpl');
                $piPaymorrowInvoiceSurcharge = piPaymorrowGetSurcharge('PaymorrowInvoice', $piPaymorrowBasket);
                $piPaymorrowRateSurcharge    = piPaymorrowGetSurcharge('PaymorrowRate', $piPaymorrowBasket);

                if ($piPaymorrowInvoiceSurcharge == '0,00') {
                    $piPaymorrowInvoiceSurcharge = false;
                }
                if ($piPaymorrowRateSurcharge == '0,00') {
                    $piPaymorrowRateSurcharge = false;
                }
                $piPaymorrowView->pi_Paymorrow_invoice_surcharge = $piPaymorrowInvoiceSurcharge;
                $piPaymorrowView->pi_Paymorrow_rate_surcharge    = $piPaymorrowRateSurcharge;;
                
                if($piPaymorrowUserdata){
                    if ($piPaymorrowUserdata["billingaddress"]["birthday"] != "0000-00-00" && !$piPaymorrowRequest->success && $piPaymorrowRequest->sAction != 'saveRegister') {
                        $piPaymorrowUserbirthday = explode("-", $piPaymorrowUserdata["billingaddress"]["birthday"]);
                        $piPaymorrowUserage = piPaymorrowAgeCalculator($piPaymorrowUserbirthday[2], $piPaymorrowUserbirthday[1], $piPaymorrowUserbirthday[0]);
                        if ($piPaymorrowUserage < 18) {
                            $piPaymorrowView->pi_Paymorrow_paymentWarningText = $piPaymorrowView->pi_Paymorrow_lang['warning']['toyoung'];
                            $piPaymorrowView->sPaymorrowPaymentError = true;
                        }
                    }
                    if ($piPaymorrowUserdata["billingaddress"]["company"] || $piPaymorrowUserdata["shippingaddress"]["company"]) {
                        $piPaymorrowView->pi_Paymorrow_paymentWarningText = $piPaymorrowView->pi_Paymorrow_lang["warning"]["company"];
                        $piPaymorrowView->sPaymorrowPaymentError = true;
                    }
                }
                $piPaymorrowView->extendsTemplate('register/payment_fieldset.tpl');
                piPaymorrowCheckUserdata($piPaymorrowUserdata, $piPaymorrowView);
                if ($piPaymorrowRequest->getControllerName() == 'checkout' && ($piPaymorrowRequest->getActionName() == 'confirm' || $piPaymorrowRequest->getActionName() == 'changeQuantity')
                ){
                    $piPaymorrowUserdata["additional"]["payment"]["embediframe"] = true;
                    $piPaymorrowView->sUserData = $piPaymorrowUserdata;
                    piPaymorrowSetTemplateVars($piPaymorrowView, $piPaymorrowRequest, $piPaymorrowConfig, $piPaymorrowUserdata);
                    $piPaymorrowBasket = Shopware()->Session()->sOrderVariables['sBasket'];
                    Shopware()->Session()->pi_Paymorrow_Warenkorbbetrag = number_format($piPaymorrowBasket['AmountNumeric'], 2, ".", "");
                    $piPaymorrowView->extendsTemplate('checkout/confirm.tpl');
                }
                
                if ($piPaymorrowRequest->getControllerName() == 'account') {
                    $piPaymorrowView->extendsTemplate('account/index.tpl');
                }
            }
            if ($piPaymorrowRequest->getActionName() == 'savePayment') 
            {
                $piPaymorrowGetPost = $piPaymorrowArgs->getSubject()->Request()->getPost();
                if ($piPaymorrowGetPost['pi_Paymorrow_saveBirthday'] || $piPaymorrowGetPost['pi_Paymorrow_saveBirthday_rate']) {
                    piPaymorrowSaveNewUserdata($piPaymorrowArgs);
                }
            }
        }
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs|Enlight_Hook_HookArgs $args
     */
    public function onBeforeRenderDocument(Enlight_Hook_HookArgs $args) {
        $document = $args->getSubject();
        if ($document->_order->payment['name'] != 'PaymorrowInvoice'
                && $document->_order->payment['name'] != 'PaymorrowRate'
        ) {
            return;
        }
        $view          = $document->_view;
        $orderData     = array();
        $orderData     = $view->getTemplateVars('Order');
        $invoiceNumber = $orderData['_order']['ordernumber'];
        $paymorrowData = Shopware()->Db()->fetchRow("
            SELECT * 
            FROM pi_paymorrow_orders 
            WHERE ordernumber = '" . $invoiceNumber . "'
        ");
        $paymorrow = array(
            'payment' => $orderData['_order']['payment_description'],
            'bankName' => htmlentities($paymorrowData['nationalBankName']),
            'bankCode' => htmlentities($paymorrowData['nationalBankCode']),
            'accountNumber' => htmlentities($paymorrowData['nationalBankAccountNumber']),
            'reference' => htmlentities($paymorrowData['paymentReference']),
            'reference2' => htmlentities(Shopware()->Config()->Shopname),
            'bic' => htmlentities($paymorrowData['bic']),
            'iban' => htmlentities($paymorrowData['iban'])
        );
        $document->_template->assign('paymorrow', (array) $paymorrow);
        $containerData = $view->getTemplateVars('Containers');
        $containerData['Footer'] = $containerData['Paymorrow_Footer'];
        if ($paymorrowData["nationalBankName"]
                && $paymorrowData["nationalBankCode"]
                && $paymorrowData["nationalBankAccountNumber"]
                && $paymorrowData["paymentReference"]
        ) {
            $containerData['Content_Info'] = $containerData['Paymorrow_Content_Info'];
        }
        else {
            $containerData['Content_Info'] = $containerData['Paymorrow_no_Bankdata_Content_Info'];
        }
        $containerData['Content_Info']['value'] = $document->_template->fetch('string:' . $containerData['Content_Info']['value']);
        $containerData['Content_Info']['style'] = '}' . $containerData['Content_Info']['style'] . ' #info {';
        $view->assign('Containers', $containerData);
    }

    /**
     * Event listener method
     *
     * @param Shopware_Components_Cron_CronJob $job
     * @internal param \Enlight_Event_EventArgs $args
     */
    public function myPaymorrowCron(Shopware_Components_Cron_CronJob $job) {
        try{
            require_once dirname(__FILE__) . '/paymorrow_direct_webservice_client/inc/paymorrow_merchant_portal_api.php';
            $OrdersNotSend = Shopware()->Db()->fetchAll("
                SELECT ordernumber 
                FROM pi_paymorrow_orders 
                WHERE fullSend = 0
            ");
            foreach ($OrdersNotSend as $OrderNotSend) {
                $OrderState = Shopware()->Db()->fetchRow("
                    SELECT * 
                    FROM s_order 
                    WHERE ordernumber ='" . $OrderNotSend['ordernumber'] . "'
                ");

                $orderstatusAccepted = Shopware()->Db()->fetchOne("
                    SELECT id 
                    FROM s_core_states 
                    WHERE description like '%Paymorrow Accepted%'
                ");
                
                $orderstatusSended = Shopware()->Db()->fetchOne("
                    SELECT id 
                    FROM s_core_states 
                    WHERE description like 'Komplett ausgeliefert'
                    AND `group` like 'state'
                ");
                // handle order which are 'Komplett ausgeliefert' AND 'Paymorrow Accepted'
                if ($OrderState['status'] == $orderstatusSended && $OrderState['cleared'] == $orderstatusAccepted) {
                    $invoiceNumber = Shopware()->Db()->fetchOne("
                        SELECT docID 
                        FROM s_order_documents 
                        WHERE orderID =" . $OrderState['id'] . "
                    ");
                    // if there is ANY document generated for this order
                    // update paymorrow order tables
                    if ($invoiceNumber) {
                        $registerPaymorrow = register_paymorrow($OrderNotSend['ordernumber'], $invoiceNumber);
                        if ($registerPaymorrow) {
                            Shopware()->Db()->query("
                                UPDATE `pi_paymorrow_orders` 
                                SET `fullSend` = 1
                                WHERE ordernumber = '" . $OrderNotSend['ordernumber'] . "'
                            ");
                            $cronData = Shopware()->Db()->fetchOne("
                                SELECT data 
                                FROM s_crontab 
                                WHERE name like 'PiPaymorrowPayment'
                            ");
                            if(!isset($cronData) || empty($cronData)) $cronData= "Folgende Bestellnummer(n) wurden an Paymorrow gesendet: ";
                            else {
                                $cronData = substr(substr(strstr(substr(strstr($cronData , ':'), 1) , ':'), 1), 0, -2);
                            }
                            $cronData .= $OrderNotSend['ordernumber'].', ';
                            $job->data = $cronData;
                        }
                    }
                }
            }
        } catch(Exception $e){
            $job->data = $e->getMessage();
        }
    }

    /**
     * Plugin Informations for the Plugin Manager
     *
     * @return    array with informations
     */
    public function getInfo() {
        return array(
            'version' => $this->getVersion(),
            'autor' => 'Payintelligent GmbH',
            'copyright' => 'Copyright (c) 2012, Payintelligent GmbH',
            'label' => 'Paymorrow Payment Module',
            'support' => 'http://www.payintelligent.de/'
        );
    }
    
    /**
     * returns current Plugin Version 
     *
     * @return String with Plugin Version
     */
    public function getVersion(){
       return "1.2.3";
    }

    /**
     * Plugin unistall method
     *
     * @return    bool
     */
    public function uninstall() {
        $piPaymorrowInvoicePaymentId = piPaymorrowGetInvoicePaymentId();
        $piPaymorrowRatePaymentId = piPaymorrowGetRatePaymentId();  
        $sql = "DELETE FROM s_core_rulesets WHERE paymentID = ".(int)$piPaymorrowInvoicePaymentId." OR paymentID = ".(int)$piPaymorrowRatePaymentId."";
        Shopware()->Db()->query($sql);
        $sql = "DELETE FROM s_core_paymentmeans WHERE name IN ('PaymorrowInvoice','PaymorrowRate')";
        Shopware()->Db()->query($sql);
        $sql = "delete from s_core_subscribes where listener like '%PaymorrowPayment%'";
        Shopware()->Db()->query($sql);
        $sql = "DELETE FROM s_core_states WHERE description like '%Paymorrow%'";
        Shopware()->Db()->query($sql);
        $sql = "DELETE FROM s_crontab WHERE name like '%Paymorrow%'";
        Shopware()->Db()->query($sql);
        $sql = "DELETE FROM s_core_documents_box WHERE name like '%Paymorrow%'";
        Shopware()->Db()->query($sql);
        return true;
    }

}

require_once dirname(__FILE__) . '/functions/paymorrow_functions.php';