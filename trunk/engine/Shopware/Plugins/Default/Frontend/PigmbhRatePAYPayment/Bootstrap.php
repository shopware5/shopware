<?php

/**
 * RatePAY Payment Module
 *
 * @author       PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @package      PiPaymentRatepay
 * @copyright(C) 2011 RatePAY GmbH. All rights reserved. <http://www.ratepay.com/>
 */
class Shopware_Plugins_Frontend_PigmbhRatePAYPayment_Bootstrap extends Shopware_Components_Plugin_Bootstrap {

    /**
     *  Install plugin method
     *
     *  @return bool
     */
    public function install() {
        $this->checkForWritableDirectoy();
        $this->createTables();
        $this->createPayments();
        $this->createEvents();
        $this->createForm();
        $this->createMenu();
        $this->createRuleset();
        $this->dbInserts();
        $this->activatePlugin();
        $this->getOldInvoices();
        return true;
    }

    /**
     *  If encryprion directory is not writable installation is abortet
     *
     */
    protected function checkForWritableDirectoy(){
      try {
          $path = dirname(__FILE__). '/Encryption';;
          if(!is_writable($path)){
              throw new Exception('Die Berechtigung des Ordners "PigmbhRatePAYPayment/Encryption" muss auf "755" gestellt werden, '
                      . 'bevor das Modul installiert werden kan. <br />');
          }
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception($e);
        }
    }


    /**
     * 	create and save payments
     *
     *  @return bool
     */
    protected function createPayments() {
        try {
            $nameArray = Array('Invoice','Rate','Debit');
            $descriptionArray = Array('Rechnung','Ratenzahlung','Lastschrift');

            foreach ($nameArray as $key  => $name) {
                $paymentRow = Shopware()->Payments()->createRow(array(
                    'name' => 'RatePAY' . $name,
                    'description' => 'RatePAY ' . $descriptionArray[$key],
                    'template' => '',
                    'additionaldescription' => '',
                    'action' => 'RatepayPayment',
                    'active' => 1,
                    'pluginID' => $this->getId()
                ))->save();
            }
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen der Zahlarten(createPayments)</b><br />' . $e);
        }
        return true;
    }

    /**
     * 	get Invoices that were made with this plugin and update payment ID
     */
    protected function getOldInvoices() {
        try {
            $sql = "SELECT order_number, payment_name from pi_ratepay_orders";
            $ratepayOrders = Shopware()->Db()->fetchAll($sql);
            foreach($ratepayOrders as $ratepayOrder){
                $sql = "SELECT `id` FROM `s_core_paymentmeans` WHERE `name` LIKE ?";
                $newPaymentId = Shopware()->Db()->fetchOne($sql, array($ratepayOrder['payment_name']));
                $sql = "UPDATE `s_order` SET `paymentID` = ?, `cleared` = ? WHERE `ordernumber` = ?";
                Shopware()->Db()->query($sql, array((int)$newPaymentId,(int)getAcceptedStatusId(), $ratepayOrder["order_number"]));
                $sql = "UPDATE `pi_ratepay_orders` SET `payment_id`= ? WHERE `order_number` = ?";
                Shopware()->Db()->query($sql, array((int)$newPaymentId, $ratepayOrder["order_number"]));
            }
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim laden alter RatePAY Rechnungen(getOldInvoices)</b><br />' . $e);
        }
    }

    /**
     * 	create and save configuration form
     */
    protected function createForm() {
        try {
            $form = $this->Form();
            $form->setElement('checkbox', 'multishopactive', array(
                'label' => 'Aktiv(Multishop)',
                'scope' => Shopware_Components_Form::SCOPE_SHOP,
                'value' => true,
                'attributes' => array(
                    "uniqueId" => 'multishopactive'
                )
            ));
            $form->setElement('button', 'button_placeholder1', array(
                'label' => '<br /><div style="width: 800px; padding: 0 0 2px 0px; border-bottom:1px solid #000; '
                         . 'color:red; font-weight:bold">RatePAY Rechnung Einstellungen</div>',
                'value' => ''
            ));
            $form->setElement('text', 'profile_id', array(
                'label' => 'Profil ID',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'security_code', array(
                'label' => 'Security Code',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'sandbox_mode', array(
                'label' => 'Sandbox',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'due_date_invoice', array(
                'label' => 'Dynamische F&auml;lligkeit in Tagen',
                'value' => 14,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'logging', array(
                'label' => 'Logging',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'b2b_invoice', array(
                'label' => 'B2B aktivieren',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'datenschutz_ratepay_invoice', array(
                'label' => 'RatePAY-Datenschutzerkl&auml;rung URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'datenschutz_merchant_invoice', array(
                'label' => 'H&auml;ndler-Datenschutzerkl&auml;rung URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'widerruf_invoice', array(
                'label' => 'Widerrufsrecht URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_min_invoice', array(
                'label' => 'Warenkorb Mindestbetrag f&uuml;r RatePAY Rechnung in &euro;',
                'value' => '-0,01',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_max_invoice', array('label' =>
                'Warenkorb Maximalbetrag f&uuml;r RatePAY Rechnung in &euro;',
                'value' => '500',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('button', 'button_placeholder2', array(
                'label' => '<br /><div style="width: 800px; padding: 0 0 2px 0px; border-bottom:1px solid #000;'
                         . ' font-weight:bold">H&auml;ndler-Daten f&uuml;r die Rechnung</div>',
                'value' => ''
            ));
            $form->setElement('text', 'merchant_address', array(
                'label' => 'Adresse',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_phone', array(
                'label' => 'Telefonnr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_fax', array(
                'label' => 'Faxnr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_email', array(
                'label' => 'E-Mail',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_name', array(
                'label' => 'Gesch&auml;ftsf&uuml;hrer',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_court', array(
                'label' => 'Amtsgericht',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_hr', array(
                'label' => 'Handelsregisternr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_ustid', array(
                'label' => 'Umsatzsteuer ID',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('button', 'button_placeholder3', array(
                'label' => '<br /><div style="width: 800px; padding: 0 0 2px 0px; border-bottom:1px solid #000;'
                         . ' font-weight:bold">RatePAY Rechnung Bankdaten</div>',
                'value' => ''
            ));
            $form->setElement('text', 'bank_firm', array(
                'label' => 'Beg&uuml;nstigte Firma',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_credit', array(
                'label' => 'Kreditinstitut',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_blz', array(
                'label' => 'Bankleitzahl',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_kto', array(
                'label' => 'Kontonummer',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_swift', array(
                'label' => 'SWIFT BIC',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_iban', array(
                'label' => 'IBAN',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('textarea', 'bank_additional', array(
                'label' => 'Zusatzfeld',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('button', 'button_placeholder4', array(
                'label' => '<br /><div style="width: 800px; padding: 0 0 2px 0px; border-bottom:1px solid #000;'
                         . ' color:red; font-weight:bold">RatePAY Ratenkauf Einstellungen</div>',
                'value' => ''
            ));
            $form->setElement('text', 'profile_id_rate', array(
                'label' => 'Profil ID',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'security_code_rate', array(
                'label' => 'Security Code',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'sandbox_mode_rate', array(
                'label' => 'Sandbox',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'logging_rate', array(
                'label' => 'Logging',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'b2b_rate', array(
                'label' => 'B2B aktivieren',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'bankdata_rate', array(
                'label' => 'Bankdaten in Datenbank speichern',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'activate_debit', array(
                'label' => 'Lastschriftoption f&uuml;r Ratenkauf aktiviert',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'payment_firstday_rate', array(
                'label' => 'Abweichende F&auml;lligkeit f&uuml;r den Kunden aktivieren',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'datenschutz_ratepay_rate', array(
                'label' => 'RatePAY-Datenschutzerkl&auml;rung URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'datenschutz_merchant_rate', array(
                'label' => 'H&auml;ndler-Datenschutzerkl&auml;rung URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'widerruf_rate', array(
                'label' => 'Widerrufsrecht URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_min_rate', array(
                'label' => 'Warenkorb Mindestbetrag f&uuml;r RatePAY Ratenkauf in &euro;',
                'value' => '200',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_max_rate', array(
                'label' => 'Warenkorb Maximalbetrag f&uuml;r RatePAY Ratenkauf in &euro;',
                'value' => '1500',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('button', 'button_placeholder5', array(
                'label' => '<br /><div style="width: 800px; padding: 0 0 2px 0px; border-bottom:1px solid #000;'
                         . ' font-weight:bold">H&auml;ndler-Daten f&uuml;r die Rechnung</div>',
                'value' => ''
            ));
            $form->setElement('text', 'merchant_address_rate', array(
                'label' => 'Adresse',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_phone_rate', array(
                'label' => 'Telefonnr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_fax_rate', array(
                'label' => 'Faxnr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_email_rate', array(
                'label' => 'E-Mail',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_name_rate', array(
                'label' => 'Gesch&auml;ftsf&uuml;hrer',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_court_rate', array(
                'label' => 'Amtsgericht',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_hr_rate', array(
                'label' => 'Handelsregisternr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_ustid_rate', array(
                'label' => 'Umsatzsteuer ID',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('button', 'button_placeholder6', array(
                'label' => '<br /><div style="width: 800px; padding: 0 0 2px 0px; border-bottom:1px solid #000;'
                         . ' font-weight:bold">RatePAY Ratenkauf Bankdaten</div>',
                'value' => ''
            ));
            $form->setElement('text', 'bank_firm_rate', array(
                'label' => 'Beg&uuml;nstigte Firma',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_credit_rate', array(
                'label' => 'Kreditinstitut',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_blz_rate', array(
                'label' => 'Bankleitzahl',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_kto_rate', array(
                'label' => 'Kontonummer',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_swift_rate', array(
                'label' => 'SWIFT BIC',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'bank_iban_rate', array(
                'label' => 'IBAN',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'forderungsinhaber_rate', array(
                'label' => 'Forderungsinhaber',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('textarea', 'bank_additional_rate', array(
                'label' => 'Zusatzfeld',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('button', 'button_placeholder7', array(
                'label' => '<br /><div style="width: 800px; padding: 0 0 2px 0px; border-bottom:1px solid #000; '
                         . 'color:red; font-weight:bold">RatePAY Lastschrift Einstellungen</div>',
                'value' => ''
            ));
            $form->setElement('text', 'profile_id_debit', array(
                'label' => 'Profil ID',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'security_code_debit', array(
                'label' => 'Security Code',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'sandbox_mode_debit', array(
                'label' => 'Sandbox',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'due_date_debit', array(
                'label' => 'F&auml;lligkeit in Tagen',
                'value' => 14,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'logging_debit', array(
                'label' => 'Logging',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'b2b_debit', array(
                'label' => 'B2B aktivieren',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('checkbox', 'bankdata_debit', array(
                'label' => 'Bankdaten in Datenbank speichern',
                'value' => true,
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'datenschutz_ratepay_debit', array(
                'label' => 'RatePAY-Datenschutzerkl&auml;rung URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'datenschutz_merchant_debit', array(
                'label' => 'H&auml;ndler-Datenschutzerkl&auml;rung URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'widerruf_debit', array(
                'label' => 'Widerrufsrecht URL(mit http(s)://)',
                'value' => 'http://',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_min_debit', array(
                'label' => 'Warenkorb Mindestbetrag f&uuml;r RatePAY Lastschrift in &euro;',
                'value' => '-0,01',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'basket_max_debit', array('label' =>
                'Warenkorb Maximalbetrag f&uuml;r RatePAY Lastschrift in &euro;',
                'value' => '500',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('button', 'button_placeholder8', array(
                'label' => '<br /><div style="width: 800px; padding: 0 0 2px 0px; border-bottom:1px solid #000;'
                         . ' font-weight:bold">H&auml;ndler-Daten f&uuml;r die Rechnung</div>',
                'value' => ''
            ));
            $form->setElement('text', 'merchant_address_debit', array(
                'label' => 'Adresse',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_phone_debit', array(
                'label' => 'Telefonnr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_fax_debit', array(
                'label' => 'Faxnr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_email_debit', array(
                'label' => 'E-Mail',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_name_debit', array(
                'label' => 'Gesch&auml;ftsf&uuml;hrer',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_court_debit', array(
                'label' => 'Amtsgericht',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_hr_debit', array(
                'label' => 'Handelsregisternr.',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));
            $form->setElement('text', 'merchant_ustid_debit', array(
                'label' => 'Umsatzsteuer ID',
                'value' => '',
                'scope' => Shopware_Components_Form::SCOPE_SHOP
            ));

            $form->save();
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen des Einstellungsformulars(createForm)</b><br />' . $e);
        }
    }

    /**
     * 	create backend menu for RatePAY payments
     */
    protected function createMenu() {
        try {
            $parent = $this->Menu()->findOneBy('label', 'Zahlungen');
            $item = $this->createMenuItem(array(
                'label' => 'RatePAY',
                'onclick' => 'openAction(\'ratepayBackend\');',
                'class' => 'ico2 date2',
                'active' => 1,
                'parent' => $parent,
                'style' => 'background-position: 5px 5px;'
            ));
            $this->Menu()->addItem($item);
            $this->Menu()->save();
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen des Men&uuml;punktes(createMenu)</b><br />' . $e);
        }
    }

    /**
     * 	save ruleset in database
     */
    protected function createRuleset() {
        try {
            $invoiceId = getInvoicePaymentId();
            $rateId = getRatePaymentId();
            $debitId = getDebitPaymentId();
            $sql = "INSERT INTO `s_core_rulesets` (`paymentID`, `rule1`, `value1`, `rule2`, `value2`) VALUES
                    (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?),
                    (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?),
                    (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array(
                $invoiceId, 'ZONEISNOT', 'deutschland', '0', '',
                $invoiceId, 'CURRENCIESISOISNOT', 'EUR', '0', '',
                $invoiceId, 'LANDISNOT', 'DE', '0', '',
                $invoiceId, 'DIFFER', '', '0', '',
                $rateId, 'ZONEISNOT', 'deutschland', '0', '',
                $rateId, 'CURRENCIESISOISNOT', 'EUR', '0', '',
                $rateId, 'LANDISNOT', 'DE', '0', '',
                $rateId, 'DIFFER', '', '0', '',
                $debitId, 'ZONEISNOT', 'deutschland', '0', '',
                $debitId, 'CURRENCIESISOISNOT', 'EUR', '0', '',
                $debitId, 'LANDISNOT', 'DE', '0', '',
                $debitId, 'DIFFER', '', '0', ''
            ));
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen der Rulesets(createRuleset)</b><br />' . $e);
        }
    }

    /**
     * 	DB inserts
     *  makes all database inserts
     */
    protected function dbInserts() {
        try {
            $invoiceId = getInvoicePaymentId();
            $rateId = getRatePaymentId();
            $debitId = getDebitPaymentId();

            //Creates and saves encryption key for bankdate in db
            require_once dirname(__FILE__) . '/Encryption/PiEncryption.php';
            $piEncryption= new Encryption_piEncryption();
            $sql = "INSERT INTO `pi_ratepay_private_key`(`key`)  VALUES (?)";
            Shopware()->Db()->query($sql, array($piEncryption->getEncodedString($piEncryption->createRandomString(64, true))));
            $sql = 'INSERT INTO `s_core_paymentmeans_countries` (`paymentID`, `countryID`) VALUES (?, ?), (?, ?), (?, ?);';
            Shopware()->Db()->query($sql, array($invoiceId, 2,$rateId, 2, $debitId, 2));

            //db inserts for changes in pdf invoices
            $descriptorDiv = '<div style="font-size:10px;">'
                . 'RatePAY-Referenznummer:{$ratepay.descriptor}<br />'
                . '</div>';
            $footerTd = '<td style="width: 86%;">'
                . '<p><span style="font-size: xx-small;">{$ratepay.shopname}</span>&nbsp;&bull;&nbsp;'
                . '<span style="font-size: xx-small;">{$ratepay.host}</span><br />'
                . '<span style="font-size: xx-small;">{$ratepay.merchant_address}</span>&nbsp;&bull;&nbsp;'
                . '<span style="font-size: xx-small;">Telefon: {$ratepay.merchant_phone}</span>&nbsp;&bull;&nbsp;'
                . '<span style="font-size: xx-small;">Fax: {$ratepay.merchant_fax}</span>&nbsp;&bull;&nbsp;'
                . '<span style="font-size: xx-small;">E-Mail: {$ratepay.merchant_email}</span>&nbsp;&bull;&nbsp;<br />'
                . '<span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer: {$ratepay.merchant_name}</span>&nbsp;&bull;&nbsp;'
                . '<span style="font-size: xx-small;">Amtsgericht: {$ratepay.merchant_court}</span>&nbsp;&bull;&nbsp;'
                . '<span style="font-size: xx-small;">HR: {$ratepay.merchant_hr}</span>&nbsp;&bull;&nbsp;'
                . '<span style="font-size: xx-small;">Ust-ID-NR: {$ratepay.merchant_ustid}</span>&nbsp;&bull;&nbsp;</p>'
                . '</td>';

            $paymentInstruction = '<div style="font-size:9px;">'
                . 'Kontoinhaber: {$ratepay.bank_firm}<br />'
                . 'Kreditinstitut: {$ratepay.bank_credit}<br />'
                . 'Bankleitzahl: {$ratepay.bank_blz}<br />'
                . 'Kontonummer: {$ratepay.bank_kto}<br />'
                . 'Verwendungszweck: {$ratepay.descriptor}<br />'
                . 'F&uuml;r den internationalen Zahlungstransfer:<br />'
                . 'SWIFT BIC:{$ratepay.bank_swift}<br />'
                . 'IBAN: {$ratepay.bank_iban}<br /></div>';
            $invoiceArray = array(
                '<table style="height: 90px;" border="0" width="100%;">'
                . '<tbody>'
                . '<tr valign="top">'
                . $footerTd
                . '<td style="width: 14%;">'
                . '<p><img src="engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_OTTO_Final_RGB_Farbe_01_Small.png" height="43px" width="130px"/></p>'
                . '</td>'
                . '</tr>'
                . '</tbody>'
                . '</table>',
                '<div style="font-size:9px;margin-top:3px; width:100%">'
                . 'Es gelten folgende Zahlungsbedingungen: {$ratepay.due_date} Tage nach Rechnungsdatum ohne Abzug<br/>'
                . 'Bitte &uuml;berweisen Sie den oben aufgef&uuml;hrten Betrag auf folgendes Konto:<br/>'
                . '</div>'
                . $paymentInstruction
                . '<div style="margin-top:3px; width:100%; font-size:9px">'
                . 'Die Zahlungsabwicklung erfolgt durch die RatePAY GmbH. Der Verk&auml;ufer hat die f&auml;llige Kaufpreisforderung '
                . 'aus Ihrer Bestellung einschlie&szlig; etwaiger Nebenforderungen an die RatePAY GmbH abgetreten. Forderungsinhaber '
                . 'ist damit die RatePAY GmbH. Eine Schuldbefreiende Leistung durch Zahlung ist gem&auml;&szlig; &sect; 407 '
                . 'B&uuml;rgerliches Gesetzbuch durch Sie nur an RatePAY GmbH m&ouml;glich.</div>'
                . '<div style="font-size:9px;">{$ratepay.bank_additional}</div>',
                $descriptorDiv
            );

            $rateArray = array(
                '<table style="height: 90px;" border="0" width="100%">'
                . '<tbody>'
                . '<tr valign="top">'
                . $footerTd
                . '<td style="width: 14%;">'
                . '<p><img src="engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_OTTO_Final_RGB_Farbe_01_Small.png" height="43px" width="130px"/></p>'
                . '</td>'
                . '</tr>'
                . '</tbody>'
                . '</table>',
                $paymentInstruction
                . '<div style="font-size:9px;">Die Zahlungsabwicklung erfolgt durch die {$ratepay.forderungsinhaber}. Der Verk&auml;ufer hat die f&auml;llige Kaufpreisforderung '
                . 'aus Ihrer Bestellung einschlie&szlig;lich etwaiger Nebenforderungen an die {$ratepay.forderungsinhaber} abgetreten. '
                . 'Forderungsinhaber ist damit die {$ratepay.forderungsinhaber}. Eine schuldbefreiende Leistung durch Zahlung ist gem&auml;&szlig; '
                . '&sect; 407 B&uuml;rgerliches Gesetzbuch durch Sie nur an die {$ratepay.forderungsinhaber} m&ouml;glich.</div>'
                . '<div style="font-size:9px;">{$ratepay.bank_additional}</div>',
                $descriptorDiv
            );

            $debitArray = array(
                '<table style="height: 90px;" border="0" width="100%" class="pi_ratepay_rechnung_footer">'
                . '<tbody>'
                . '<tr valign="top">'
                . $footerTd
                . '<td style="width: 14%;">'
                . '<p><img src="engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_OTTO_Final_RGB_Farbe_01_Small.png" height="43px" width="130px"/></p>' . '</td>'
                . '</tr>'
                . '</tbody>'
                . '</table>',
                '<div class="payment_note" style="margin-top:8px; width:100%; font-size:9px">'
                . 'Der f&auml;llige Rechnungsbetrag wird von Ihrem Konto abgebucht, '
                . 'welches Sie w&auml;hrend des Bestellprozesses angegeben haben.<br />'
                . 'Es gelten folgende Zahlungsbedingungen: {$ratepay.due_date} Tage nach Rechnungsdatum ohne Abzug<br/></div>',
                $descriptorDiv
            );

            $sql = "
                INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
                (1, 'pi_ratepay_rechnung_Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', ?),
                (1, 'pi_ratepay_rechnung_Content_Info', '', ?),
                (1, 'pi_ratepay_rechnung_Header_Right', '', ?)";
            Shopware()->Db()->query($sql, $invoiceArray);
            $sql = "
                INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
                (1, 'pi_ratepay_rate_Footer', 'width: 170mm; position:fixed; bottom:-20mm; height: 15mm;', ?),
                (1, 'pi_ratepay_rate_Content_Info', '', ?),
                (1, 'pi_ratepay_rate_Header_Right', '', ?)";
            Shopware()->Db()->query($sql, $rateArray);
            $sql = "
                INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
                (1, 'pi_ratepay_debit_Footer', 'width: 170mm; position:fixed; bottom:-20mm; height: 15mm;', ?),
                (1, 'pi_ratepay_debit_Content_Info', '', ?),
                (1, 'pi_ratepay_debit_Header_Right', '', ?)";
            Shopware()->Db()->query($sql, $debitArray);
                        $sql = "
                INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
                (4, 'pi_ratepay_rechnung_Footer', 'width: 170mm;position:fixed;bottom:-20mm;height: 15mm;', ?),
                (4, 'pi_ratepay_rechnung_Header_Right', '', ?)";
            Shopware()->Db()->query($sql, array(
                '<table style="height: 90px;" border="0" width="100%;">'
                . '<tbody>'
                . '<tr valign="top">'
                . $footerTd
                . '<td style="width: 14%;">'
                . '<p><img src="engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_OTTO_Final_RGB_Farbe_01_Small.png" height="43px" width="130px"/></p>' . '</td>'
                . '</tr>'
                . '</tbody>'
                . '</table>',
                $descriptorDiv
            ));
            $sql = "
                INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
                (4, 'pi_ratepay_rate_Footer', 'width: 170mm;position:fixed;bottom:-20mm;height: 15mm;', ?),
                (4, 'pi_ratepay_rate_Header_Right', '', ?)";
            Shopware()->Db()->query($sql, array(
                '<table style="height: 90px;" border="0" width="100%">'
                . '<tbody>'
                . '<tr valign="top">'
                . $footerTd
                . '<td style="width: 14%;">'
                . '<p><img src="engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_OTTO_Final_RGB_Farbe_01_Small.png" height="43px" width="130px"/></p>' . '</td>'
                . '</tr>'
                . '</tbody>'
                . '</table>',
                $descriptorDiv
            ));
            $sql = "
                INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
                (4, 'pi_ratepay_debit_Footer', 'width: 170mm;position:fixed;bottom:-20mm;height: 15mm;', ?),
                (4, 'pi_ratepay_debit_Header_Right', '', ?)";
            Shopware()->Db()->query($sql, array(
                '<table style="height: 90px;" border="0" width="100%" class="pi_ratepay_rechnung_footer">'
                . '<tbody>'
                . '<tr valign="top">'
                . $footerTd
                . '<td style="width: 14%;">'
                . '<p><img src="engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_OTTO_Final_RGB_Farbe_01_Small.png" height="43px" width="130px"/></p>' . '</td>'
                . '</tr>'
                . '</tbody>'
                . '</table>',
                $descriptorDiv
            ));

            //New ratepaystates
            $sql = "SELECT * from `s_core_states` WHERE `description` like '%ratepaystate%'";
            $checkRatepayStates = Shopware()->Db()->fetchOne($sql);
            if(!$checkRatepayStates){
                $sql = "SELECT max(`id`) from `s_core_states`";
                $statesMaxId = Shopware()->Db()->fetchOne($sql);
                $statesArray = array (
                    $statesMaxId+1,'<span class=\"ratepaystate\" style=\"color:green\">Zahlung von RatePAY akzeptiert</span>', '2', 'payment', '0',
                    $statesMaxId+2,'<span class=\"ratepaystate\" style=\"color:orange\">Teilweise versendet</span>',  '1', 'state', '0',
                    $statesMaxId+3,'<span class=\"ratepaystate\" style=\"color:green\">Komplett versendet</span>', '2', 'state', '0',
                    $statesMaxId+4,'<span class=\"ratepaystate\" style=\"color:orange\">Teilweise storniert</span>', '2', 'state', '0',
                    $statesMaxId+5,'<span class=\"ratepaystate\" style=\"color:red\">Komplett storniert</span>', '2', 'state', '0',
                    $statesMaxId+6,'<span class=\"ratepaystate\" style=\"color:orange\">Teilweise retourniert</span>', '2', 'state', '0',
                    $statesMaxId+7,'<span class=\"ratepaystate\" style=\"color:red\">Komplett retourniert</span>', '2', 'state', '0'
                );
                $sql = "INSERT INTO `s_core_states` (`id`, `description`, `position`, `group`, `mail`)
                        VALUES (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?),
                               (?, ?, ?, ?, ?), (?, ?, ?, ?, ?), (?, ?, ?, ?, ?)";
                Shopware()->Db()->query($sql, $statesArray);
            }
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler bei den Datenbanken Inserts (DbInserts)</b><br />' . $e);
        }
    }

    /**
     * 	Activates the plugin
     */
    protected function activatePlugin() {
        try {
            $sql = "UPDATE `s_core_plugins` SET `active` = 1 WHERE `name`  = 'PigmbhRatePAYPayment'";
            Shopware()->Db()->query($sql);
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim aktivieren des Plugins(activatePlugin)</b><br />' . $e);
        }
    }

    /**
     * Create and subscribe events and hooks
     */
    protected function createEvents() {
        try {
            $event = $this->createEvent(
                'Enlight_Controller_Dispatcher_ControllerPath_Frontend_RatepayPayment', 'onGetRatepayControllerPathFrontend'
            );
            $this->subscribeEvent($event);
            $event = $this->createEvent(
                'Enlight_Controller_Dispatcher_ControllerPath_Backend_RatepayBackend', 'onGetRatepayControllerPathBackend'
            );
            $this->subscribeEvent($event);
            $event = $this->createEvent(
                'Enlight_Controller_Action_PostDispatch', 'onPostRatepayDispatch'
            );
            $this->subscribeEvent($event);
            $event = $this->createEvent(
                'Enlight_Controller_Action_PreDispatch_Frontend_Checkout', 'onPreRatepayDispatchCheckout'
            );
            $this->subscribeEvent($event);
            $hook = $this->createHook(
                'Shopware_Components_Document', 'assignValues', 'onBeforeRenderDocument', Enlight_Hook_HookHandler::TypeAfter, 0
            );
            $this->subscribeHook($hook);
            /**
             * Shopware Comment:
             * Event has been removed during upgrade to shopware 4.0
             *
             * Instead the enable()-Method will be called every time the plugin config is saved. (at least HL told me that)
             */
            /*$event = $this->createEvent(
                'Enlight_Controller_Action_PreDispatch_Backend_Plugin', 'onRatepayBackendPlugin'
            );
            $this->subscribeEvent($event);*/
            /**
             * Because you can't do Subshop specific Actions with enable() we register a new Event for Form saving
             */
            //$event = $this->subscribeEvent('Enlight_Controller_Action_PreDispatch_Backend_Config', 'onRatepayBackendPlugin');
        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen der Events und Hooks(createEvents)</b><br />' . $e);
        }
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
        $this->onRatepayBackendPlugin($config);
        return true;
    }

    /**
     * Create tables that are needed for ratepay payment
     */
    protected function createTables() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_log`
            (
                `id`                    INT NOT NULL AUTO_INCREMENT,
                `order_number`          VARCHAR( 255 ) NULL,
                `transaction_id`        VARCHAR( 255 ) NULL,
                `payment_method`        VARCHAR( 40 ) NULL,
                `payment_type`          VARCHAR( 40 ) NULL,
                `payment_subtype`       VARCHAR( 40 ) NULL,
                `result`                VARCHAR( 40 ) NULL,
                `request`               MEDIUMTEXT NULL,
                `response`              MEDIUMTEXT NULL,
                `date`                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `result_code`           VARCHAR( 5 ) NULL,
                `response_reason`       VARCHAR( 255 ) NULL,
                `customer`              VARCHAR( 255 ) NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
            Shopware()->Db()->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_orders`
            (
                `id` 			INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `payment_id`		INT(10) UNSIGNED NOT NULL,
                `payment_name` 		VARCHAR( 30 ) NOT NULL ,
                `order_number`		VARCHAR(255) NULL,
                `transactionid`		VARCHAR(255) NULL,
                `transaction_short_id` 	VARCHAR(20) NOT NULL,
                `descriptor` 		VARCHAR(20) NOT NULL,
                `invoice_number`	VARCHAR(255) NULL,
                `userbirthdate` 	DATE NOT NULL DEFAULT '0000-00-00',
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_bills`
            (
                `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `date`                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `order_id`              VARCHAR(255) NULL,
                `order_number`          VARCHAR(255) NULL,
                `invoice_amount`        DOUBLE NULL ,
                `invoice_hash`          VARCHAR(255) NULL,
                `type`                  INT(1) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_rate_details`
            (
                `id`                        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ordernumber`               VARCHAR(255) NULL,
                `total_amount`              DOUBLE NULL ,
                `amount`                    DOUBLE NULL ,
                `interest_amount`           VARCHAR(255) NULL,
                `service_charge`            DOUBLE NULL ,
                `annual_percentage_rate`    VARCHAR(255) NULL,
                `monthly_debit_interest`    VARCHAR(255) NULL,
                `number_of_rates`           INT(10) NULL ,
                `rate`                      DOUBLE NULL ,
                `last_rate`                 DOUBLE NULL ,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_debit_details`
            (
                `id`                        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `userid`                    INT(10),
                `owner`                     VARBINARY(200),
                `accountnumber`             VARBINARY(200),
                `bankcode`                  VARBINARY(200),
                `bankname`                  VARBINARY(200),
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_order_detail`
            (
                `id` 			INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `ordernumber`		VARCHAR( 50 ) NOT NULL ,
                `artikel_id` 		VARCHAR( 30 ) NOT NULL ,
                `bestell_nr`		VARCHAR( 30 ) NOT NULL ,
                `anzahl` 		INT( 11 ) NOT NULL ,
                `name` 			VARCHAR( 255 ) NOT NULL ,
                `einzelpreis` 		DOUBLE NOT NULL ,
                `gesamtpreis` 		DOUBLE NOT NULL ,
                `bestellt` 		INT( 11 ) NOT NULL ,
                `offen` 		INT( 11 ) NOT NULL ,
                `geliefert` 		INT( 11 ) NOT NULL DEFAULT '0',
                `storniert` 		INT( 11 ) NOT NULL DEFAULT '0',
                `retourniert` 		INT( 11 ) NOT NULL DEFAULT '0',
                `bezahlstatus` 		INT( 11 ) NOT NULL DEFAULT '0',
                `versandstatus` 	INT( 11 ) NOT NULL DEFAULT '0',
                `einzelpreis_net` 	DOUBLE NOT NULL
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_history`
            (
                `id` 		INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ordernumber`	VARCHAR(255) NULL,
                `date` 		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `event`		VARCHAR(255) NULL,
                `name`		VARCHAR(255) NULL,
                `bestellnr`	VARCHAR(255) NULL,
                `anzahl`	VARCHAR(255) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_stats`
            (
                `id` 		INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `accepted` 	VARCHAR(255) NULL,
                `notaccepted`	VARCHAR(255) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = MYISAM CHARACTER SET utf8";
            Shopware()->Db()->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `pi_ratepay_private_key`
            (
                `id` 		INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `key`           VARBINARY(200),
                PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
            Shopware()->Db()->query($sql);

        }
        catch (Exception $e) {
            $this->uninstall();
            throw new Exception('<b>Fehler beim erstellen der Tabellen(createTables)</b><br />' . $e);
        }
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public static function onGetRatepayControllerPathFrontend(Enlight_Event_EventArgs $args) {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/controller/Frontend/ratepayFrontend.php';
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public static function onGetRatepayControllerPathBackend(Enlight_Event_EventArgs $args) {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/controller/Backend/ratepayBackend.php';
    }

    /**
     * Event listener method redirects to payment selection if something is changed at the confirm page
     *
     * @param Enlight_Event_EventArgs $args
     */
    static function onPreRatepayDispatchCheckout(Enlight_Event_EventArgs $args) {
        /*$request = $args->getSubject()->Request();
        if($request->getControllerName()=='checkout' && $request->getActionName()=='confirm'){
            $view = $args->getSubject()->View();
            $request = $args->getSubject()->Request();
            if (Shopware()->Session()->ratePAYadressDiff || Shopware()->Session()->ratepayUstidDiff  || Shopware()->Session()->ratepayCompanyDiff ) {
                $args->getSubject()->redirect(array('controller' => 'account', 'action' => 'payment', 'sTarget' => 'checkout'));
            }
        }*/
     }

    /**
     * Event listener method that saves the cart min/max in the plugin configuration for showing RatePAY
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onRatepayBackendPlugin($config = null) {
        //Checking if the Config Form is getting saved and the Config is saved for this Plugin
        if (!is_null($config)) {
            $invoiceId = getInvoicePaymentId();
            $rateId = getRatePaymentId();
            $debitId = getDebitPaymentId();

            $sql = "SELECT * FROM s_core_shops";
            $results = Shopware()->Db()->fetchAll($sql);

            foreach($results as $shop) {
               //Deleting old Rules for Min and Max Basket
                $sql = "DELETE FROM `s_core_rulesets` WHERE(`paymentID` = ? OR `paymentID` = ? OR `paymentID` = ?)
                        AND `rule1` like 'SUBSHOP' AND `value1` like ?
                        AND (`rule2` like 'ORDERVALUELESS' OR `rule2` like 'ORDERVALUEMORE')";
                Shopware()->Db()->query($sql, array((int)$invoiceId, (int)$rateId, (int)$debitId, $shop['id']));
            }
            foreach($config as $shopId => $shopConfig) {
                foreach($shopConfig as $name=>$value) {                    
                    switch($name) {
                        case 'basket_min_invoice':
                            $this->_saveMinMaxToRuleset($shopId, $invoiceId, 'ORDERVALUELESS', $value);
                            break;
                        case 'basket_max_invoice':
                            $this->_saveMinMaxToRuleset($shopId, $invoiceId, 'ORDERVALUEMORE', $value);
                            break;
                        case 'basket_min_rate':
                            $this->_saveMinMaxToRuleset($shopId, $rateId, 'ORDERVALUELESS', $value);
                            break;
                        case 'basket_max_rate':
                            $this->_saveMinMaxToRuleset($shopId, $rateId, 'ORDERVALUEMORE', $value);
                            break;
                        case 'basket_min_debit':
                            $this->_saveMinMaxToRuleset($shopId, $debitId, 'ORDERVALUELESS', $value);
                            break;
                        case 'basket_max_debit':
                            $this->_saveMinMaxToRuleset($shopId, $debitId, 'ORDERVALUEMORE', $value);
                            break;
                    }
                }
            }
        }
    }

    /**
     * Saving Minimum Allowed and Maximum Allowed Basketvalues for each Payment and each Shop
     *
     * @param int $shopId
     * @param int $paymentId
     * @param string $rule
     * @param float $value
     */
    private function _saveMinMaxToRuleset($shopId,$paymentId, $rule,$value) {
        $sql = "Insert into `s_core_rulesets` (`paymentID`, `rule1` , `value1`, `rule2` , `value2`) VALUES(?, ?, ?, ?, ?)";
        Shopware()->Db()->query($sql, array((int)$paymentId, 'SUBSHOP', $shopId,$rule, str_replace(',', '.', $value)));
    }

    /**
     * Event listener method handles all Frontend actions
     *
     * @param Enlight_Event_EventArgs $args
     */
    static function onPostRatepayDispatch(Enlight_Event_EventArgs $args) {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/Frontend/');
        /** @var $request Enlight_Controller_Request_RequestHttp */
        $request = $args->getSubject()->Request();
        // just be active in the frontend and while the plugin is enabled
        if ($request->getModuleName() == 'frontend' && $config->multishopactive == true) {
            $view = $args->getSubject()->View();
            Shopware()->Session()->pi_ratepay_rate_calc_path = Shopware()->Config()->get('basepath') . '/engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/Ratenrechner/';
            // return if no template could be found.
            if(!$view->hasTemplate()) {
                return;
            }
            $userData = $view->sUserData;
            if(isset($userData['billingadress']['id'])) {
                Shopware()->Session()->RatepayCustomerId=$userData['billingadress']['id'];
            }
            $ratepayPayment=checkRatepayPayment($userData);
            if ($userData && $ratepayPayment) {
                checkBillingEqualShipping($userData, $view);
                checkB2BAllowed($userData, $view);
                if(Shopware()->Session()->ratepayB2BInvoice && $userData["additional"]["payment"]["name"] == "RatePAYInvoice"
                    || Shopware()->Session()->ratepayB2BRate && $userData["additional"]["payment"]["name"] == "RatePAYRate"
                    || Shopware()->Session()->ratepayB2BDebit && $userData["additional"]["payment"]["name"] == "RatePAYDebit"
                    || Shopware()->Session()->ratePAYadressDiff) {
                    $sql = "UPDATE `s_user` SET `paymentID` = ? WHERE `id` = ?";
                    Shopware()->Db()->query($sql, array(
                        (int)Shopware()->Config()->Paymentdefault,
                        (int)$userData['billingaddress']['userID']
                    ));
                }
            }
            $view->extendsTemplate('index/header.tpl');
            //Checks if customer can pay with RatePAY, sets error messages and loads payment_fieldset template
            //Also implements fix for older templates and sets debit data
            // sViewport has been removed - instead $args->getSubject()->Request()->getControllerName() can be used
            if ($request->getControllerName() == 'account' || $request->getControllerName() == 'checkout' || $request->getControllerName() == 'register') {
                $basket = Shopware()->Session()->sOrderVariables['sBasket'];
                //gets userage and checks user data
                if ($userData) {
                    $userAge = getUserAge($userData, $view);
                    checkBillingEqualShipping($userData, $view);
                    checkUserData($userData, $view, $userAge);
                    checkB2BAllowed($userData, $view);
	                $view->debitData = getEncodedDebitData($userData['billingaddress']['userID']);
	                if(!isset($view->debitData)) $view->debitData = getDebitData();
                }
                //displays surcharge at payment selection
                if($basket){
                    setSurcharge($basket, $view);
                    Shopware()->Session()->pi_ratepay_Warenkorbbetrag = number_format($basket['AmountNumeric'], 2, ".", "");
                 }

                $view->activateDebit = $config->activate_debit;
                $view->extendsTemplate('register/payment_fieldset.tpl');
                //Saves data entered in RatePAY form
                if ($request->getActionName() == 'savePayment') {
                    $post = $args->getSubject()->Request()->getPost();
                    setDirectDebitSession($post);
                    if (isset($post['saveRatepayInvoiceData']) || isset($post['saveRatepayRateData']) || isset($post['saveRatepayDebitData'])){
                        saveUserData($args);
                    }
                    elseif($post['register']['payment'] == getDebitPaymentId()
                           || ($post['register']['payment']== getRatePaymentId() && Shopware()->Session()->RatepayDirectDebit)){
                        if(!checkDebitData($post)){
                            header('Location:'.str_replace("savePayment","payment",Shopware()->Config()->Host.$_SERVER['REQUEST_URI']));
                            //break;
                        } else{
                            Shopware()->Session()->RatepayRateMissingBankData = false;
                            Shopware()->Session()->RatepayDebitMissingBankData = false;
                            saveDebitData($args);
                        }
                    }
                }

                 //template fix and saving of direct debit selection for ratepay installment
                if($request->getActionName() == 'payment'){
                    if(Shopware()->Session()->RatepayDebitMissingBankData){
                            $view->RatepayDebitMissingBankData = true;
                    }
                    elseif(Shopware()->Session()->RatepayRateMissingBankData){
                            $view->RatepayRateMissingBankData = true;
                    }
                }

                //Sets debit data
                if(Shopware()->Session()->RatepayDirectDebit){
                    $view->ratepayDebitPayType = Shopware()->Session()->RatepayDirectDebit;
                }
                //Extends checkout with RatePAY AGB, rate calculator and payment notices and also sets template vars
                if ($request->getControllerName() == 'checkout' && $request->getActionName() != 'finish' && $request->getActionName() != 'cart'
                        && $request->sTargetAction != 'cart' && isset($ratepayPayment)) {
                    $view->extendsTemplate('checkout/confirm.tpl');
                    setTemplateVars($view, $request, $config, $userData);
                }
                //Confirms RatePAY order at finish page
                if ($request->getControllerName() == 'checkout' && $request->getActionName() == 'finish' && $request->getActionName() != 'cart'
                        && $ratepayPayment && !Shopware()->Session()->pi_ratepay_Confirm){
                    confirmPayment($config, $userData);
                }
            }
        }
    }


    /**
     * Event listener method to add extra information to the invoice(pdf)
     *
     * @param Enlight_Event_EventArgs $args
     */
    public static function onBeforeRenderDocument(Enlight_Hook_HookArgs $args) {
        $document = $args->getSubject();
        $paymentName = $document->_order->payment['name'];
        if ($paymentName != 'RatePAYInvoice' && $paymentName != 'RatePAYRate' && $paymentName != 'RatePAYDebit'){
            return;
        }
        $view = $document->_view;
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
        $containerData = array();
        $sql = "SELECT `descriptor` FROM `pi_ratepay_orders` WHERE `order_number` = ?";
        $descriptor = Shopware()->Db()->fetchOne($sql, array($document->_order->order['ordernumber']));
        $bankAdditional = $config->bank_additional;
        $configAdditional = "";
        $bankAdditionalRate = $config->bank_additional_rate;
        $configRateAdditional = "";
        //Ratepay Invoice
        if ($paymentName == 'RatePAYInvoice') {
            if($bankAdditional != ""){
                $configAdditional = "\n\n".$bankAdditional;
            }
            $ratepay = array(
                'shopname' => Shopware()->Config()->Shopname,
                'host' => Shopware()->Config()->Host,
                'merchant_address' => htmlentities($config->merchant_address),
                'merchant_phone' => htmlentities($config->merchant_phone),
                'merchant_fax' => htmlentities($config->merchant_fax),
                'merchant_email' => htmlentities($config->merchant_email),
                'merchant_name' => htmlentities($config->merchant_name),
                'merchant_court' => htmlentities($config->merchant_court),
                'merchant_hr' => htmlentities($config->merchant_hr),
                'merchant_ustid' => htmlentities($config->merchant_ustid),
                'bank_firm' => htmlentities($config->bank_firm),
                'bank_credit' => htmlentities($config->bank_credit),
                'bank_blz' => htmlentities($config->bank_blz),
                'bank_kto' => htmlentities($config->bank_kto),
                'bank_swift' => htmlentities($config->bank_swift),
                'bank_iban' => htmlentities($config->bank_iban),
                'descriptor' => htmlentities($descriptor),
                'bank_additional' => preg_replace('/[\t\r\n]+/', '<br/>', htmlentities($configAdditional)),
                'due_date' => htmlentities($config->due_date_invoice)
            );
            $document->_template->assign('ratepay', (array)$ratepay);
            $containerData = $view->getTemplateVars('Containers');
            $containerData['Footer'] = $containerData['pi_ratepay_rechnung_Footer'];
            $containerData['Header_Box_Right'] = $containerData['pi_ratepay_rechnung_Header_Right'];
            $containerData['Content_Info'] = $containerData['pi_ratepay_rechnung_Content_Info'];
        }
        //Ratepay Rate
        else if($paymentName == 'RatePAYRate') {
            if($bankAdditionalRate != ""){
                $configRateAdditional = "\n\n".$bankAdditionalRate;
            }
            $ratepay = array(
                'shopname' => Shopware()->Config()->Shopname,
                'host' => Shopware()->Config()->Host,
                'merchant_address' => htmlentities($config->merchant_address_rate),
                'merchant_phone' => htmlentities($config->merchant_phone_rate),
                'merchant_fax' => htmlentities($config->merchant_fax_rate),
                'merchant_email' => htmlentities($config->merchant_email_rate),
                'merchant_name' => htmlentities($config->merchant_name_rate),
                'merchant_court' => htmlentities($config->merchant_court_rate),
                'merchant_hr' => htmlentities($config->merchant_hr_rate),
                'merchant_ustid' => htmlentities($config->merchant_ustid_rate),
                'bank_firm' => htmlentities($config->bank_firm_rate),
                'bank_credit' => htmlentities($config->bank_credit_rate),
                'bank_blz' => htmlentities($config->bank_blz_rate),
                'bank_kto' => htmlentities($config->bank_kto_rate),
                'bank_swift' => htmlentities($config->bank_swift_rate),
                'bank_iban' => htmlentities($config->bank_iban_rate),
                'descriptor' => htmlentities($descriptor),
                'bank_additional' => preg_replace('/[\t\r\n]+/', '<br/>', htmlentities($configRateAdditional)),
                'forderungsinhaber' => htmlentities($config->forderungsinhaber_rate)
            );
            $document->_template->assign('ratepay', (array)$ratepay);
            $containerData = $view->getTemplateVars('Containers');
            $containerData['Footer']['value'] = $containerData['pi_ratepay_rate_Footer']['value'];
            $containerData['Header_Box_Right']['value'] =  $containerData['pi_ratepay_rate_Header_Right']['value'];
            $containerData['Content_Info'] = $containerData['pi_ratepay_rate_Content_Info'];
        }
        //Ratepay Debit
        else{
             $ratepay = array(
                'shopname' => Shopware()->Config()->Shopname,
                'host' => Shopware()->Config()->Host,
                'merchant_address' => htmlentities($config->merchant_address_debit),
                'merchant_phone' => htmlentities($config->merchant_phone_debit),
                'merchant_fax' => htmlentities($config->merchant_fax_debit),
                'merchant_email' => htmlentities($config->merchant_email_debit),
                'merchant_name' => htmlentities($config->merchant_name_debit),
                'merchant_court' => htmlentities($config->merchant_court_debit),
                'merchant_hr' => htmlentities($config->merchant_hr_debit),
                'merchant_ustid' => htmlentities($config->merchant_ustid_debit),
                'descriptor' => htmlentities($descriptor),
                'due_date' => htmlentities($config->due_date_debit)
            );
            $document->_template->assign('ratepay', (array)$ratepay);
            $containerData = $view->getTemplateVars('Containers');
            $containerData['Footer']['value'] = $containerData['pi_ratepay_debit_Footer']['value'];
            $containerData['Header_Box_Right']['value'] =  $containerData['pi_ratepay_debit_Header_Right']['value'];
            $containerData['Content_Info'] = $containerData['pi_ratepay_debit_Content_Info'];
        }
        $containerData['Footer']['value'] = $document->_template->fetch('string:' . $containerData['Footer']['value']);
        $containerData['Header_Box_Right']['value'] = $document->_template->fetch('string:' . $containerData['Header_Box_Right']['value']);
        $containerData['Content_Info']['value'] = $document->_template->fetch('string:' . $containerData['Content_Info']['value']);
        $containerData['Content_Info']['style'] =  $containerData['Content_Info']['style'];
        $view->assign('Containers', $containerData);
    }



    /**
     * Plugin Informations for the Plugin Manager
     *
     * @return	array with informations
     */
    public function getInfo() {
        return array(
            'version' => $this->getVersion(),
            'autor' => 'Payintelligent GmbH',
            'copyright' => 'Copyright (c) 2011-2012, Payintelligent GmbH',
            'label' => 'RatePAY Payment Module',
            'source' => 'Default',
            'description' => '',
            'changes' => 'Ready for Shopware 3.5.4 - 3.5.6',
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
       return "2.0.8";
    }

    /**
     * Plugin uninstall method
     *
     * @return	bool
     */
    public function uninstall() {
        $sql = "DELETE FROM `s_core_paymentmeans` WHERE `name` IN ('RatePAYInvoice','RatePAYRate','RatePAYDebit')";
        Shopware()->Db()->query($sql);
        $sql = "DELETE FROM `s_core_menu` WHERE `name` LIKE 'RatePAY'";
        Shopware()->Db()->query($sql);

        $sql = "DELETE FROM `s_core_documents_box` WHERE `name` LIKE '%pi_ratepay%' ";
        Shopware()->Db()->query($sql);
        $sql = "DROP TABLE IF EXISTS `pi_ratepay_private_key`";
        Shopware()->Db()->query($sql);
        return true;
    }
}
require_once dirname(__FILE__) . '/functions/functions.php';