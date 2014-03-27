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
 * Deprecated Shopware Class that handles several
 * functions around customer / order related things
 */
class sAdmin
{
    /**
     * Pointer to sSystem object
     *
     * @var sSystem
     */
    public $sSYSTEM;

    /**
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * Shopware configuration object which used for
     * each config access in this class.
     * Injected over the class constructor
     *
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    public $snippetObject;

    /**
     * Check if current active shop has own registration
     * @var bool s_core_multilanguage.scoped_registration
     */
    public $scopedRegistration;

    /**
     * Id of current active shop
     * @var int s_core_multilanguage.id
     */
    public $subshopId;

    public function __construct($db = null, $config = null)
    {
        $this->db = $db ? : Shopware()->Db();
        $this->config = $config ? : Shopware()->Config();

        $this->snippetObject = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages');
        $shop = Shopware()->Shop()->getMain() !== null ? Shopware()->Shop()->getMain() : Shopware()->Shop();
        $this->scopedRegistration = $shop->getCustomerScope();
        $this->subshopId = $shop->getId();
    }

    /**
     * This function seems to be unused. See SW-8161
     *
     * Logout user and destroy session
     *
     * @return bool|void
     */
    public function sLogout()
    {
        if (Enlight()->Events()->notifyUntil('Shopware_Modules_Admin_Logout_Start', array('subject' => $this))) {
            return false;
        }

        unset($this->sSYSTEM->_SESSION);
        unset($_SESSION);
        unset($this->sSYSTEM->sUSERGROUPDATA);

        $this->sSYSTEM->sUSERGROUPDATA = $this->db->fetchRow(
            "SELECT * FROM s_core_customergroups WHERE `groupkey` = 'EK'"
        );
        $this->sSYSTEM->sUSERGROUPDATA = $this->sSYSTEM->sUSERGROUPDATA ? : array();
        session_destroy();
        $this->sSYSTEM->_SESSION["sUserGroup"] = "EK";
        $this->sSYSTEM->_SESSION["sUserGroupData"] = $this->sSYSTEM->sUSERGROUPDATA;

        session_regenerate_id();
    }

    /**
     * This function seems to be unused. See SW-8161
     *
     * @deprecated Old vat id check
     * @param $id
     * @param $country
     * @return array an empty array
     */
    public function sCheckTaxID($id, $country)
    {
        return array();
    }

    /**
     * Checks vat id with webservice
     *
     * @return array Associative array with success / error codes
     */
    public function sValidateVat()
    {
        if (empty($this->sSYSTEM->sCONFIG['sVATCHECKENDABLED'])) {
            return array();
        }
        if (empty($this->sSYSTEM->_POST["ustid"]) && empty($this->sSYSTEM->sCONFIG['sVATCHECKREQUIRED'])) {
            return array();
        }

        $messages = array();
        $ustid = preg_replace('#[^0-9A-Z\+\*\.]#', '', strtoupper($this->sSYSTEM->_POST['ustid']));
        $country = $this->db->fetchOne(
            'SELECT countryiso FROM s_core_countries WHERE id=?',
            array($this->sSYSTEM->_POST['country'])
        );
        if (empty($this->sSYSTEM->_POST["ustid"])) {
            $messages[] = $this->snippetObject->get('VatFailureEmpty', 'Please enter a vat id');
        } elseif (empty($ustid) || !preg_match("#^([A-Z]{2})([0-9A-Z+*.]{2,12})$#", $ustid, $vat)) {
            $messages[] = $this->snippetObject->get('VatFailureInvalid', 'The vat id entered is invalid');
        } elseif (empty($country) || $country != $vat[1]) {
            $field_names = explode(',', $this->snippetObject->get('VatFailureErrorFields', 'Company,City,Zip,Street,Country'));
            $field_name = isset($field_names[4]) ? $field_names[4] : 'Land';
            $messages[] = sprintf($this->snippetObject->get('VatFailureErrorField', 'The field %s does not match to the vat id entered'), $field_name);
        } elseif ($country == 'DE') {

        } elseif (!empty($this->sSYSTEM->sCONFIG['sVATCHECKADVANCEDNUMBER'])) {
            $data = array(
                'UstId_1' => $this->sSYSTEM->sCONFIG['sVATCHECKADVANCEDNUMBER'],
                'UstId_2' => $vat[1] . $vat[2],
                'Firmenname' => '',
                'Ort' => '',
                'PLZ' => '',
                'Strasse' => '',
                'Druck' => empty($this->sSYSTEM->sCONFIG['sVATCHECKCONFIRMATION']) ? 'nein' : 'ja'
            );

            if (!empty($this->sSYSTEM->sCONFIG['sVATCHECKADVANCED'])
                && strpos($this->sSYSTEM->sCONFIG['sVATCHECKADVANCEDCOUNTRIES'], $vat[1]) !== false
            ) {
                $data['Firmenname'] = $this->sSYSTEM->_POST['company'];
                $data['Ort'] = $this->sSYSTEM->_POST['city'];
                $data['PLZ'] = $this->sSYSTEM->_POST['zipcode'];
                $data['Strasse'] = $this->sSYSTEM->_POST['street'] . ' ' . $this->sSYSTEM->_POST['streetnumber'];
            }

            $request = 'http://evatr.bff-online.de/evatrRPC?';
            $request .= http_build_query($data, '', '&');

            $context = stream_context_create(array('http' => array(
                'method' => 'GET',
                'header' => 'Content-Type: text/html; charset=utf-8',
                'timeout' => 5,
                'user_agent' => 'Shopware/' . $this->sSYSTEM->sCONFIG['sVERSION']
            )));
            $response = @file_get_contents($request, false, $context);

            $reg = '#<param>\s*<value><array><data>\s*<value><string>([^<]*)</string></value>\s*<value><string>([^<]*)</string></value>\s*</data></array></value>\s*</param>#msi';
            if (!empty($response) && preg_match_all($reg, $response, $matches)) {
                $response = array_combine($matches[1], $matches[2]);
                $messages = $this->sCheckVatResponse($response);
            } elseif (empty($this->sSYSTEM->sCONFIG['sVATCHECKNOSERVICE'])) {
                $messages[] = sprintf($this->snippetObject->get('VatFailureUnknownError', 'An unknown error occurs while checking your vat id. Error code %d'), 10);
            }
        } elseif (false && class_exists('SoapClient')) {
            $url = 'http://ec.europa.eu/taxation_customs/vies/services/checkVatService.wsdl';
            if (!file_get_contents($url)) {
                $messages[] = sprintf($this->snippetObject->get('VatFailureUnknownError', 'An unknown error occurs while checking your vat id. Error code %d'), 11);
            } else {
                $client = new SoapClient($url, array('exceptions' => 0, 'connection_timeout' => 5));
                $response = $client->checkVat(array('countryCode' => $vat[1], 'vatNumber' => $vat[2]));
            }
            if (is_soap_fault($response)) {
                $messages[] = sprintf($this->snippetObject->get('VatFailureUnknownError', 'An unknown error occurs while checking your vat id. Error code %d'), 12);
                if (!empty($this->sSYSTEM->sCONFIG['sVATCHECKDEBUG'])) {
                    $messages[] = "SOAP-error: (errorcode: {$response->faultcode}, errormsg: {$response->faultstring})";
                }
            } elseif (empty($response->valid)) {
                $messages[] = $this->snippetObject->get('VatFailureInvalid', 'The vat id entered is invalid');
            }
        } else {
            $messages[] = sprintf($this->snippetObject->get('VatFailureUnknownError', 'An unknown error occurs while checking your vat id. Error code %d'), 20);
        }
        if (!empty($messages) && empty($this->sSYSTEM->sCONFIG['sVATCHECKREQUIRED'])) {
            $messages[] = $this->snippetObject->get('VatFailureErrorInfo', ''); // todo@all In the case vat is not required in registration, this info message should occur
        }
        $messages = Enlight()->Events()->filter('Shopware_Modules_Admin_CheckTaxID_MessagesFilter', $messages,
            array('subject' => $this, "post" => $this->sSYSTEM->_POST->toArray())
        );
        return $messages;
    }

    /**
     * Handles the response from the german VAT id validation
     * Used only in sAdmin::sValidateVat()
     *
     * @param  $response The response from the validation webservice
     * @return array List of errors found by the remote service
     */
    public function sCheckVatResponse($response)
    {
        if (!empty($this->sSYSTEM->sCONFIG['sVATCHECKNOSERVICE'])) {
            if (in_array($response['ErrorCode'], array(999, 205, 218, 208, 217, 219))) {
                return array();
            }
        }
        // todo@all remove if no longer needed, else fix this unicode mess
        if (!empty($this->sSYSTEM->sCONFIG['sVATCHECKDEBUG'])) {
            switch ($response['ErrorCode']) {
                case 200: break;
                case 201: $msg = 'Die eingegebene USt-IdNr. ist ungültig.'; break;
                case 202: $msg = 'Die eingegebene USt-IdNr. ist ungültig. Sie ist nicht in der Unternehmerdatei des betreffenden EU-Mitgliedstaates registriert.'; break;
                case 203: $msg = 'Die eingegebene USt-IdNr. ist ungültig. Sie ist erst ab dem '.$response['Gueltig_ab'].' gültig.'; break;
                case 204: $msg = 'Die eingegebene USt-IdNr. ist ungültig. Sie war im Zeitraum von '.$response['Gueltig_ab'].' bis '.$response['Gueltig_bis'].' gültig.'; break;
                case 209: $msg = 'Die eingegebene USt-IdNr. ist ungültig. Sie entspricht nicht dem Aufbau der für diesen EU-Mitgliedstaat gilt.'; break;
                case 210: $msg = 'Die eingegebene USt-IdNr. ist ungültig. Sie entspricht nicht den Prüfziffernregeln die für diesen EU-Mitgliedstaat gelten.'; break;
                case 211: $msg = 'Die eingegebene USt-IdNr. ist ungültig. Sie enthält unzulässige Zeichen.'; break;
                case 212: $msg = 'Die eingegebene USt-IdNr. ist ungültig. Sie enthält ein unzulässiges Länderkennzeichen.'; break;

                case 207: $msg = 'Ihnen wurde die deutsche USt-IdNr. ausschliesslich zu Zwecken der Besteuerung des innergemeinschaftlichen Erwerbs erteilt. Sie sind somit nicht berechtigt, Bestätigungsanfragen zu stellen.'; break;
                case 206: $msg = 'Ihre deutsche USt-IdNr. ist ungültig. Eine Bestätigungsanfrage ist daher nicht möglich. Den Grund hierfür können Sie beim Bundeszentralamt für Steuern - Dienstsitz Saarlouis - erfragen.'; break;
                case 208: $msg = 'Für die von Ihnen angefragte USt-IdNr. läuft gerade eine Anfrage von einem anderen Nutzer. Eine Bearbeitung ist daher nicht möglich. Bitte versuchen Sie es später noch einmal.'; break;
                case 213: $msg = 'Die Abfrage einer deutschen USt-IdNr. ist nicht möglich.'; break;
                case 214: $msg = 'Ihre deutsche USt-IdNr. ist fehlerhaft. Sie beginnt mit \'DE\' gefolgt von 9 Ziffern.'; break;
                case 215: $msg = 'Ihre Anfrage enthält nicht alle notwendigen Angaben für eine einfache Bestätigungsanfrage'; break;
                case 216: $msg = 'Ihre Anfrage enthält nicht alle notwendigen Angaben für eine qualifizierte Bestätigungsanfrage'; break;
                case 217: $msg = 'Bei der Verarbeitung der Daten aus dem angefragten EU-Mitgliedstaat ist ein Fehler aufgetreten. Ihre Anfrage kann deshalb nicht bearbeitet werden.'; break;
                case 218: $msg = 'Eine qualifizierte Bestätigung ist zur Zeit nicht möglich.'; break;
                case 219: $msg = 'Bei der Durchführung der qualifizierten Bestätigungsanfrage ist ein Fehler aufgetreten.'; break;
                case 220: $msg = 'Bei der Anforderung der amtlichen Bestätigungsmitteilung ist ein Fehler aufgetreten. Sie werden kein Schreiben erhalten.'; break;
                case 999: $msg = 'Eine Bearbeitung Ihrer Anfrage ist zurzeit nicht möglich. Bitte versuchen Sie es später noch einmal.'; break;
                case 205: $msg = 'Ihre Anfrage kann derzeit durch den angefragten EU-Mitgliedstaat oder aus anderen Gründen nicht beantwortet werden'; break;

                default:  $msg = sprintf($this->snippetObject->get('VatFailureUnknownError','An unknown error occurs while checking your vat id. Error code %d'), 30); break;
            }
        } else {
            switch ($response['ErrorCode']) {
                case 200:
                    break;
                case 201:
                case 202:
                case 204:
                case 209:
                case 210:
                case 211:
                case 212:
                    $msg = $this->snippetObject->get('VatFailureInvalid', 'The vat id entered is invalid');
                    break;
                case 203:
                    $msg = sprintf($this->snippetObject->get('VatFailureDate', 'The vat id entered is invalid.Is valid from %s'), $response['Gueltig_ab']);
                    break;
                default:
                    $msg = sprintf($this->snippetObject->get('VatFailureUnknownError', 'An unknown error occurs while checking your vat id. Error code %d'), 31);
                    break;
            }
        }
        $result = array();
        if (!empty($msg)) {
            $result[] = $msg;
        } else {
            $fields = array('Erg_Name', 'Erg_Ort', 'Erg_PLZ', 'Erg_Str');
            $field_names = explode(',', $this->snippetObject->get('VatFailureErrorFields', 'Company,City,Zip,Street,Country'));
            foreach ($fields as $key => $field) {
                if (isset($response[$field]) && strpos($this->sSYSTEM->sCONFIG['sVATCHECKVALIDRESPONSE'], $response[$field]) === false) {
                    $name = isset($field_names[$key]) ? $field_names[$key] : $field;
                    $result[] = sprintf($this->snippetObject->get('VatFailureErrorField', 'The field %s does not match to the vat id entered'), $name);
                }
            }
        }
        return $result;
    }

    /**
     * Get data from a certain payment mean
     * If user data is provided, the current user payment
     * mean is validated against current country, risk management, etc
     * and reset to default if necessary.
     *
     * Used in several places to get the payment mean data
     *
     * @param int $id Payment mean id
     * @param array|bool $user Array with user data (sGetUserData)
     * @return array Payment data
     */
    public function sGetPaymentMeanById($id, $user = false)
    {
        $id = intval($id);
        $sql = "
            SELECT * FROM s_core_paymentmeans WHERE id = ?
        ";
        $data = $this->db->fetchRow($sql, array($id)) ? : array();

        if ($this->sSYSTEM->sMODULES['sBasket']->sCheckForESD()) {
            $sEsd = true;
        }

        if (!count($user)) {
            $user = array();
        }

        $basket = $this->sSYSTEM->sMODULES['sBasket']->sBASKET;

        // Check for risk-management
        // If rule matches, reset to default payment mean if this payment mean was not
        // set by shop-owner

        // Hide payment means which are not active
        if (!$data["active"] && $data["id"] != $user["additional"]["user"]["paymentpreset"]) {
            $resetPayment = $this->sSYSTEM->sCONFIG["sPAYMENTDEFAULT"];
        }

        // If esd - order, hide payment means which
        // are not available for esd
        if (!$data["esdactive"] && $sEsd) {
            $resetPayment = $this->sSYSTEM->sCONFIG["sPAYMENTDEFAULT"];
        }

        // Check additional rules
        if ($this->sManageRisks($data["id"], $basket, $user)
            && $data["id"] != $user["additional"]["user"]["paymentpreset"]
        ) {
            $resetPayment = $this->sSYSTEM->sCONFIG["sPAYMENTDEFAULT"];
        }

        if (!empty($user['additional']['countryShipping']['id'])) {
            $sql = "
                SELECT 1
                FROM s_core_paymentmeans p

                LEFT JOIN s_core_paymentmeans_subshops ps
                ON ps.subshopID=?
                AND ps.paymentID=p.id

                LEFT JOIN s_core_paymentmeans_countries pc
                ON pc.countryID=?
                AND pc.paymentID=p.id

                WHERE (ps.paymentID IS NOT NULL OR (
                  SELECT paymentID FROM s_core_paymentmeans_subshops WHERE paymentID=p.id LIMIT 1
                ) IS NULL)
                AND (pc.paymentID IS NOT NULL OR (
                  SELECT paymentID FROM s_core_paymentmeans_countries WHERE paymentID=p.id LIMIT 1
                ) IS NULL)

                AND id = ?
            ";
            $active = $this->db->fetchOne($sql, array(
                $this->sSYSTEM->sSubShop['id'],
                $user['additional']['countryShipping']['id'],
                $id
            ));
            if (empty($active)) {
                $resetPayment = $this->sSYSTEM->sCONFIG["sPAYMENTDEFAULT"];
            }
        }

        if ($resetPayment && $user["additional"]["user"]["id"]) {
            $updateAccount = $this->db->query(
                "UPDATE s_user SET paymentID = ? WHERE id = ?",
                array($resetPayment, $user["additional"]["user"]["id"])
            );
            $sql = "SELECT * FROM s_core_paymentmeans WHERE id = ?";
            $data = $this->db->fetchRow($sql, array($resetPayment)) ? : array();
        }

        // Get Translation
        $data = $this->sGetPaymentTranslation($data);

        $data = Enlight()->Events()->filter('Shopware_Modules_Admin_GetPaymentMeanById_DataFilter', $data, array('subject' => $this,"id" => $id,"user" => $user));

        return $data;
    }

    /**
     * Get all available payments
     *
     * @return array Payments data
     */
    public function sGetPaymentMeans()
    {
        $basket = $this->sSYSTEM->sMODULES['sBasket']->sBASKET;

        $user = $this->sGetUserData();

        if ($this->sSYSTEM->sMODULES['sBasket']->sCheckForESD()) {
            $sEsd = true;
        } else {
            $sEsd = false;
        }

        $countryID = (int) $user['additional']['countryShipping']['id'];
        $subshopID = (int) $this->sSYSTEM->sSubShop['id'];
        if (empty($countryID)) {
            $countryID = $this->db->fetchOne("
            SELECT id FROM s_core_countries ORDER BY position ASC LIMIT 1
            ");
        }
        $sql = "
            SELECT p.*
            FROM s_core_paymentmeans p

            LEFT JOIN s_core_paymentmeans_subshops ps
            ON ps.subshopID=$subshopID
            AND ps.paymentID=p.id

            LEFT JOIN s_core_paymentmeans_countries pc
            ON pc.countryID=$countryID
            AND pc.paymentID=p.id

            WHERE (ps.paymentID IS NOT NULL OR (SELECT paymentID FROM s_core_paymentmeans_subshops WHERE paymentID=p.id LIMIT 1) IS NULL)
            AND (pc.paymentID IS NOT NULL OR (SELECT paymentID FROM s_core_paymentmeans_countries WHERE paymentID=p.id LIMIT 1) IS NULL)

            ORDER BY position, name
        ";


        $getPaymentMeans = $this->db->fetchAll($sql);

        if ($getPaymentMeans===false) {
            $sql = "SELECT * FROM s_core_paymentmeans ORDER BY position, name";
            $getPaymentMeans = $this->db->fetchAll($sql);
        }

        foreach ($getPaymentMeans as $payKey => $payValue) {

            // Hide payment means which are not active
            if (empty($payValue["active"]) && $payValue["id"] != $user["additional"]["user"]["paymentpreset"]) {
                unset($getPaymentMeans[$payKey]);
                continue;
            }

            // If esd - order, hide payment means, which
            // are not accessible for esd
            if (empty($payValue["esdactive"]) && $sEsd) {
                unset($getPaymentMeans[$payKey]);
                continue;
            }

            // Check additional rules
            if ($this->sManageRisks($payValue["id"], $basket, $user)
                && $payValue["id"] != $user["additional"]["user"]["paymentpreset"]
            ) {
                unset($getPaymentMeans[$payKey]);
                continue;
            }

            // Get possible translation
            $getPaymentMeans[$payKey] = $this->sGetPaymentTranslation($getPaymentMeans[$payKey]);

        }

        //if no payment is left use always the fallback payment no matter if it has any restrictions too
        if (!count($getPaymentMeans)) {
            $sql = "SELECT * FROM s_core_paymentmeans WHERE id =?";
            $fallBackPayment = $this->db->fetchRow($sql, array(Shopware()->Config()->get('paymentdefault')));
            $fallBackPayment = $fallBackPayment ? : array();

            $getPaymentMeans[] = $this->sGetPaymentTranslation($fallBackPayment);
        }

        $getPaymentMeans = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_GetPaymentMeans_DataFilter',
            $getPaymentMeans,
            array('subject' => $this)
        );

        return $getPaymentMeans;

    }

    /**
     * Loads the system class of the specified payment mean
     *
     * @param array $paymentData Array with payment data
     * @return ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod
     * The payment mean handling class instance
     */
    public function sInitiatePaymentClass($paymentData)
    {
        $dirs = array();

        if (substr($paymentData['class'], -strlen('.php')) === '.php') {
            $index = substr($paymentData['class'], 0, strpos($paymentData['class'], '.php'));
        } else {
            $index = $paymentData['class'];
        }

        $dirs = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_InitiatePaymentClass_AddClass',
            $dirs,
            array('subject' => $this)
        );

        $class = array_key_exists($index, $dirs) ? $dirs[$index] : $dirs['default'];
        if (!$class) {
            $this->sSYSTEM->E_CORE_WARNING("sValidateStep3 #02","Payment classes dir not loaded");
            return false;
        }

        $sPaymentObject = new $class();

        if (!$sPaymentObject) {
            $this->sSYSTEM->E_CORE_WARNING("sValidateStep3 #02","Payment class not found");
            return false;
        } else {
            return $sPaymentObject;
        }
    }

    /**
     * Last step of the registration - validate all user fields that exists in session and
     * stores the data into database
     *
     * @param array $paymentmeans - Array with payment data
     * @return array Payment data
     */
    public function sValidateStep3($paymentmeans = array())
    {
        if (empty($this->sSYSTEM->_POST['sPayment'])) {
            $this->sSYSTEM->E_CORE_WARNING("sValidateStep3 #00","No payment id");
            return;
        }

        $user = $this->sGetUserData();
        $paymentData = $this->sGetPaymentMeanById($this->sSYSTEM->_POST['sPayment'], $user);

        if (!count($paymentData)) {
            $this->sSYSTEM->E_CORE_ERROR("sValidateStep3 #01","Could not load paymentmean");
        } else {
            // Include management class and check input data
            if (!empty($paymentData['class'])) {
                $sPaymentObject = $this->sInitiatePaymentClass($paymentData);
                $checkPayment = $sPaymentObject->validate(Shopware()->Front()->Request());
            }
            return array(
                "checkPayment" => $checkPayment,
                "paymentData" => $paymentData,
                "sProcessed" => true,
                "sPaymentObject" => &$sPaymentObject
            );
        }
    }

    /**
     * Updates the billing address of the user
     *
     * @return boolean If operation was successful
     */
    public function sUpdateBilling()
    {
        $userObject = $this->sSYSTEM->_POST;

        if (!empty($userObject['birthmonth']) && !empty($userObject['birthday']) && !empty($userObject['birthyear'])) {
            $userObject['birthday'] = mktime(0,0,0, (int) $userObject['birthmonth'], (int) $userObject['birthday'], (int) $userObject['birthyear']);
            if ($userObject['birthday'] > 0) {
                $userObject['birthday'] = date('Y-m-d', $userObject['birthday']);
            } else {
                $userObject['birthday'] = '0000-00-00';
            }
        } else {
            unset($userObject['birthday']);
        }

        $fields = array(
            'company',
            'department',
            'salutation',
            'firstname',
            'lastname',
            'street',
            'streetnumber',
            'zipcode',
            'city',
            'phone',
            'fax',
            'countryID',
            'stateID',
            'ustid',
            'birthday'
        );

        $data = array();
        foreach ($fields as $field) {
            if (isset($userObject[$field])) {
                $data[$field] = $userObject[$field];
            }
        }

        $data["countryID"] = $userObject["country"];

        $where = array(
            'userID='.(int) $this->sSYSTEM->_SESSION['sUserId']
        );

        list($data, $where) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_UpdateBilling_FilterSql',
            array($data,$where),
            array(
                'subject' => $this,
                "id" => $this->sSYSTEM->_SESSION['sUserId'],
                "user" => $userObject
            )
        );

        $result = $this->db->update('s_user_billingaddress', $data, $where);

        if ($this->db->getErrorMessage()) {
            $this->sSYSTEM->E_CORE_WARNING("sUpdateBilling #01","Could not save data (billing-adress)".$this->db->getErrorMessage());
            return false;
        }

        //new attribute tables.
        $data = array(
            "text1" => $userObject['text1'],
            "text2" => $userObject['text2'],
            "text3" => $userObject['text3'],
            "text4" => $userObject['text4'],
            "text5" => $userObject['text5'],
            "text6" => $userObject['text6'],
        );

        $sql = "SELECT id FROM s_user_billingaddress WHERE userID = " . (int) $this->sSYSTEM->_SESSION['sUserId'];
        $billingId = $this->db->fetchOne($sql);
        $where = array(" billingID = " . $billingId);

        list($data, $where) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_UpdateBillingAttributes_FilterSql',
            array($data, $where),
            array(
                'subject' => $this,
                "id" => $this->sSYSTEM->_SESSION['sUserId'],
                "user" => $userObject
            )
        );

        $this->db->update('s_user_billingaddress_attributes', $data, $where);

        return true;
    }

    /**
     * Add or remove an email address from the mailing list
     *
     * @param boolean $status True if insert, false if remove
     * @param string $email Email address
     * @param boolean $customer If email address belongs to a customer
     * @return boolean If operation was successful
     */
    public function sUpdateNewsletter($status, $email, $customer = false)
    {
        if (!$status) {
            // Delete
            $changeLetterState = $this->db->query(
                "DELETE FROM s_campaigns_mailaddresses WHERE email = ?",
                array($email)
            );
        } else {
            // Check if mail address receives already our newsletter
            if ($this->db->fetchOne(
                "SELECT id FROM s_campaigns_mailaddresses WHERE email = ?",
                array($email))
            ) {
                return false;
            }

            if (Shopware()->Config()->optinnewsletter) {
                $hash = md5(uniqid(rand()));
                $data = serialize(array("newsletter"=>$email,"subscribeToNewsletter"=>true));

                $link = Shopware()->Front()->Router()->assemble(array(
                        'sViewport' => 'newsletter',
                        'action' => 'index',
                        'sConfirmation' => $hash
                    )
                );

                $this->sendMail($email, 'sOPTINNEWSLETTER', $link);

                Shopware()->Db()->query('
                    INSERT INTO s_core_optin (datum, hash, data)
                    VALUES (now(),?,?)
                ',array($hash,$data));
                return true;
            }

            $groupID = $this->sSYSTEM->sCONFIG['sNEWSLETTERDEFAULTGROUP'];
            if (!$groupID) {
                $groupID = "0";
            }
            // Insert
            if (!empty($customer)) {
                $changeLetterState = $this->db->query("
                INSERT INTO s_campaigns_mailaddresses (customer, email)
                VALUES (?,?)
                ", array(1, $email));
            } else {
                $changeLetterState = $this->db->query("
                INSERT INTO s_campaigns_mailaddresses (groupID, email)
                VALUES (?,?)
                ", array($groupID, $email));
            }
        }

        return true;
    }

    /**
     * Sends a mail to the given recipient with a given template.
     * If the optin parameter is set, the sConfirmLink variable will be filled by the optin link.
     *
     * @param $recipient
     * @param $template
     * @param string $optin
     */
    private function sendMail($recipient, $template, $optin='')
    {
        $context = array();

        if (!empty($optin)) {
            $context['sConfirmLink'] = $optin;
        }

        $mail = Shopware()->TemplateMail()->createMail($template, $context);
        $mail->addTo($recipient);
        $mail->send();
    }

    /**
     * Gets the current order addresses for a given type and current user
     * If a valid address hash is provided, only that address is returned
     * Used on frontend controllers to get and set addresses
     *
     * @param string $type shipping / billing
     * @param string $request_hash secure hash
     * @return array|bool Array with addresses if no match found, or array with address details
     * if match found, or false on failure
     */
    public function sGetPreviousAddresses($type, $request_hash = null)
    {
        if (empty($type)) {
            return false;
        }
        if (empty($this->sSYSTEM->_SESSION['sUserId'])) {
            return false;
        }

        if ($type=='shipping') {
            $type = 'shipping';
        } else {
            $type = 'billing';
        }

        $sql = '
            SELECT
                MD5(CONCAT(company, department, salutation, firstname, lastname, street, streetnumber, zipcode, city, countryID)) as hash,
                company, department, salutation, firstname, lastname,
                street, streetnumber, zipcode, city, countryID as country, countryID, countryname
            FROM s_order_'.$type.'address AS a
            LEFT JOIN s_core_countries co
            ON a.countryID=co.id
            WHERE a.userID=?
            GROUP BY hash
            ORDER BY MAX(a.id) DESC
        ';

        $addresses = $this->db->fetchAll($sql, array($this->sSYSTEM->_SESSION['sUserId']));

        foreach ($addresses as $address) {
            if (!empty($request_hash) && $address['hash'] == $request_hash) {
                return $address;
            }
            $address[$address['hash']]['country'] = array();
            $address[$address['hash']]['country']['id'] = $address['countryID'];
            $address[$address['hash']]['country']['countryname'] = $address['countryname'];
            $address[$address['hash']]['country'] = $this->sGetCountryTranslation($address["country"]);
        }

        if (!empty($request_hash)) {
            return false;
        }

        return $addresses;
    }

    /**
     * Updates the shipping address of the user
     * Used in the Frontend Account controller
     *
     * @return boolean If operation was successful
     */
    public function sUpdateShipping()
    {
        $userObject = $this->sSYSTEM->_POST;

        if (empty($this->sSYSTEM->_SESSION["sUserId"])) {
            return false;
        }

        $sql = 'SELECT id FROM s_user_shippingaddress WHERE userID = ?';
        $shippingID = $this->db->fetchOne($sql, array($this->sSYSTEM->_SESSION['sUserId']));

        $fields = array(
            'company',
            'department',
            'salutation',
            'firstname',
            'lastname',
            'street',
            'streetnumber',
            'zipcode',
            'city',
            'countryID',
            'stateID'
        );

        $data = array();
        foreach ($fields as $field) {
            if (isset($userObject[$field])) {
                $data[$field] = $userObject[$field];
            }
        }
        $data["countryID"] = isset($userObject["country"]) ? $userObject["country"] : 0;

        list($data) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_UpdateShipping_FilterSql',
            array($data), array(
                'subject' => $this,
                "id" => $this->sSYSTEM->_SESSION['sUserId'],
                "user" => $userObject
            )
        );

        if (empty($shippingID)) {
            $data["userID"] = (int) $this->sSYSTEM->_SESSION['sUserId'];
            $result = $this->db->insert('s_user_shippingaddress', $data);

            $shippingID = $this->db->lastInsertId('s_user_shippingaddress');
            $attributeData = array(
                'shippingID' => $shippingID,
                'text1' => $userObject['text1'],
                'text2' => $userObject['text2'],
                'text3' => $userObject['text3'],
                'text4' => $userObject['text4'],
                'text5' => $userObject['text5'],
                'text6' => $userObject['text6']
            );
            list($attributeData) = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_UpdateShippingAttributes_FilterSql',
                array($attributeData),
                array(
                    'subject' => $this,
                    "id" => $this->sSYSTEM->_SESSION['sUserId'],
                    "user" => $userObject
                )
            );
            $this->db->insert('s_user_shippingaddress_attributes', $attributeData);
        } else {
            $where = array('id='.(int) $shippingID);
            $result = $this->db->update('s_user_shippingaddress', $data, $where);

            $attributeData = array(
                'text1' => $userObject['text1'],
                'text2' => $userObject['text2'],
                'text3' => $userObject['text3'],
                'text4' => $userObject['text4'],
                'text5' => $userObject['text5'],
                'text6' => $userObject['text6']
            );
            $where = array('shippingID='.(int) $shippingID);
            list($attributeData) = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_UpdateShippingAttributes_FilterSql',
                array($attributeData),
                array(
                    'subject' => $this,
                    "id" => $this->sSYSTEM->_SESSION['sUserId'],
                    "user" => $userObject
                )
            );
            $this->db->update('s_user_shippingaddress_attributes', $attributeData, $where);
        }

        if ($this->db->getErrorMessage()) {
            $this->sSYSTEM->E_CORE_WARNING("sUpdateShipping #01","Could not save data (billing-adress)".$this->db->getErrorMessage());
            return false;
        }
        return true;
    }

    /**
     * Updates the payment mean of the user
     * Used in the Frontend Account controller
     *
     * @return boolean If operation was successful
     */
    public function sUpdatePayment()
    {
        if (empty($this->sSYSTEM->_SESSION["sUserId"])) {
            return false;
        }
        $sqlPayment = "
        UPDATE s_user SET paymentID = ? WHERE id = ?";

        $sqlPayment = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_UpdatePayment_FilterSql',
            $sqlPayment,
            array(
                'subject' => $this,
                "id" => $this->sSYSTEM->_SESSION['sUserId']
            )
        );

        $saveUserData = $this->db->query(
            $sqlPayment,
            array($this->sSYSTEM->_POST["sPayment"], $this->sSYSTEM->_SESSION["sUserId"])
        );

        if ($this->db->getErrorMessage()) {
            $this->sSYSTEM->E_CORE_WARNING("sUpdatePayment #01","Could not save data (payment)".$this->db->getErrorMessage());
            return false;
        }
        return true;
    }

    /**
     * Update user's email address and password
     * Used in the Frontend Account controller
     *
     * @return boolean If operation was successful
     */
    public function sUpdateAccount()
    {
        $p = $this->sSYSTEM->_POST;

        $email = strtolower($p["email"]);

        $password = $p["password"];
        $passwordConfirmation = $p["passwordConfirmation"];

        if ($password && $passwordConfirmation) {
            $encoderName = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();
            $password = Shopware()->PasswordEncoder()->encodePassword($password, $encoderName);

            $this->sSYSTEM->_SESSION["sUserMail"] = $email;
            $this->sSYSTEM->_SESSION["sUserPassword"] = $password;
            $sqlAccount = "
              UPDATE s_user SET email = ?, password = ?, encoder = ? WHERE id = ?";
            $sqlAccount = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_UpdateAccount_FilterPasswordSql',
                $sqlAccount,
                array(
                    'email' => $email,
                    'password' => $password,
                    'encoder' => $encoderName,
                    'subject' => $this,
                    "id" => $this->sSYSTEM->_SESSION['sUserId']
                )
            );

            $saveUserData = $this->db->query(
                $sqlAccount,
                array($email, $password, $encoderName, $this->sSYSTEM->_SESSION["sUserId"])
            );
        } else {
            $this->sSYSTEM->_SESSION["sUserMail"] = $email;
            $sqlAccount = "
              UPDATE s_user SET email=? WHERE id=?";
            $sqlAccount = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_UpdateAccount_FilterEmailSql',
                $sqlAccount,
                array(
                    'email' => $email,
                    'password' => $password,
                    'subject' => $this,
                    "id" => $this->sSYSTEM->_SESSION['sUserId']
                )
            );

            $saveUserData = $this->db->query(
                $sqlAccount,
                array($email, $this->sSYSTEM->_SESSION["sUserId"])
            );
        }

        if ($this->db->getErrorMessage()) {
            $this->sSYSTEM->E_CORE_WARNING("sUpdateAccount #01","Could not save data (account)".$this->db->getErrorMessage());
            return false;
        }
        return true;

    }

    /**
     * Validates the billing address against the provided rule set
     * Used in the Frontend Account and Register controllers
     *
     * @param array $rules Set of rules that specify which fields are required
     * @param boolean $edit If the current call is editing data from a new or existing customer
     * @return array Array with errors that may have occurred
     */
    public function sValidateStep2($rules, $edit = false)
    {
        $sErrorMessages = array();
        $sErrorFlag = array();

        list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_ValidateStep2_FilterStart',
            array($sErrorMessages,$sErrorFlag),
            array(
                'edit' => $edit,
                'rules' => $rules,
                'subject' => $this,
                "post" => $this->sSYSTEM->_POST->toArray()
            )
        );

        $postData = $this->sSYSTEM->_POST;

        foreach ($rules as $ruleKey => $ruleValue) {
            $postData[$ruleKey] = trim($postData[$ruleKey]);

            if (empty($postData[$ruleKey])
                && !empty($rules[$ruleKey]["required"]) 
                && empty($rules[$ruleKey]["addicted"])
            ) {
                $sErrorFlag[$ruleKey] = true;
            }
        }

        if (count($sErrorFlag)) {
            // Some error occurs
            $sErrorMessages[] = $this->snippetObject->get('ErrorFillIn','Please fill in all red fields');
        }

        if (isset($rules['ustid'])) {
            $sVatMessages = $this->sValidateVat();
            if (!empty($sVatMessages)) {
                $sErrorFlag["ustid"] = true;
                $sErrorMessages = array_merge($sErrorMessages, $sVatMessages);
            }
        }

        if (!$edit) {
            if (!count($sErrorMessages)) {
                $register = $this->sSYSTEM->_SESSION['sRegister'];
                foreach ($rules as $ruleKey => $ruleValue) {
                    $register['billing'][$ruleKey] = $postData[$ruleKey];
                }
                $this->sSYSTEM->_SESSION['sRegister'] = $register;
            } else {
                foreach ($rules as $ruleKey => $ruleValue) {
                    unset($this->sSYSTEM->_SESSION["sRegister"]["billing"][$ruleKey]);
                }
            }
        }
        list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_ValidateStep2_FilterResult', 
            array($sErrorMessages,$sErrorFlag), 
            array(
                'edit' => $edit,
                'rules' => $rules,
                'subject' => $this,
                "post" => $this->sSYSTEM->_POST->toArray()
            )
        );

        return array("sErrorFlag" => $sErrorFlag, "sErrorMessages" => $sErrorMessages);
    }

    /**
     * Validates the shipping address against the provided rule set
     * Used in the Frontend Account and Register controllers
     *
     * @param array $rules Set of rules that specify which fields are required
     * @param boolean If the current call is editing data from a new or existing customer
     * @return array Array with errors that may have occurred
     */
    public function sValidateStep2ShippingAddress($rules, $edit = false)
    {
        $postData = $this->sSYSTEM->_POST;

        foreach ($rules as $ruleKey => $ruleValue) {
            if ($rules[$ruleKey]["addicted"]) {
                $addictedField = array_keys($rules[$ruleKey]["addicted"]);
                if ($postData[$addictedField[0]] == $rules[$ruleKey]["addicted"][$addictedField[0]]
                    && !$postData[$ruleKey]
                ) {
                    $sErrorFlag[$ruleKey] = true;
                }
            } else {
                if (!$postData[$ruleKey] && $rules[$ruleKey]["required"]) {
                    $sErrorFlag[$ruleKey] = true;
                }

                // Fix, to support billing and shipping addresses in one step
                if (preg_match("/SHIPPING/",$ruleKey)) {
                    $clearedRuleKey = str_replace("SHIPPING", "", $ruleKey);
                    $postData[$clearedRuleKey] = $postData[$ruleKey];
                    $rules[$clearedRuleKey] = $rules[$ruleKey];
                    unset($rules[$ruleKey]);
                }
                // --
            }
        }

        if (count($sErrorFlag)) {
            // Some error occurs
            $sErrorMessages[] = $this->snippetObject->get('ErrorFillIn', 'Please fill in all red fields');
        }

        $registerSession = $this->sSYSTEM->_SESSION["sRegister"];
        if (!$edit) {
            if (!count($sErrorMessages)) {
                foreach ($rules as $ruleKey => $ruleValue) {
                    $registerSession["shipping"][$ruleKey] = $postData[$ruleKey];
                }
            } else {
                foreach ($rules as $ruleKey => $ruleValue) {
                    unset($registerSession["shipping"][$ruleKey]);
                }
            }
        }
        $this->sSYSTEM->_SESSION["sRegister"] = $registerSession;

        list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_ValidateStep2Shipping_FilterResult',
            array($sErrorMessages, $sErrorFlag),
            array(
                'edit' => $edit,
                'rules' => $rules,
                'subject' => $this,
                "post" => $this->sSYSTEM->_POST->toArray()
            )
        );

        return array("sErrorFlag" => $sErrorFlag, "sErrorMessages" => $sErrorMessages);
    }

    /**
     * Validate account information
     * Used in the Frontend Account and Register controllers
     *
     * @param boolean $edit If the current call is editing data from a new or existing customer
     * @return array Array with errors that may have occurred
     */
    public function sValidateStep1($edit = false)
    {
        $postData = $this->sSYSTEM->_POST;
        $encoderName =  Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();

        if (isset($postData["emailConfirmation"]) || isset($postData["email"])) {
            $postData["email"] = strtolower(trim($postData["email"]));
            // Check email

            $validator = new Zend_Validate_EmailAddress();
			$validator->getHostnameValidator()->setValidateTld(false);
            
			if (empty($postData["email"]) || !$validator->isValid($postData["email"])) {
                $sErrorFlag["email"] = true;
                $sErrorMessages[] = $this->snippetObject->get('MailFailure','Please enter a valid mail address');
            }

            // Check email confirmation if needed
            if (isset($postData["emailConfirmation"])) {
                $postData["emailConfirmation"] = strtolower(trim($postData["emailConfirmation"]));
                if ($postData["email"] != $postData["emailConfirmation"]) {
                    $sErrorFlag["emailConfirmation"] = true;
                    $sErrorMessages[] = $this->snippetObject->get('MailFailureNotEqual','The mail addresses entered are not equal');
                }
            }
        } elseif ($edit && empty($postData["email"])) {
            $this->sSYSTEM->_POST["email"] = $postData["email"] = $this->sSYSTEM->_SESSION["sUserMail"];
        }

        if (empty($this->sSYSTEM->_SESSION['sRegister'])) {
            $this->sSYSTEM->_SESSION['sRegister'] = array();
        }

        // Check password if account should be created
        if (!$postData["skipLogin"] || $edit) {
            if ($edit && (!$postData["password"] && !$postData["passwordConfirmation"])) {

            } else {
                if (strlen(trim($postData["password"])) == 0
                    || !$postData["password"]
                    || !$postData["passwordConfirmation"]
                    || (strlen($postData["password"]) < $this->sSYSTEM->sCONFIG['sMINPASSWORD'])
                ) {
                    $sErrorMessages[] = Shopware()->Snippets()->getNamespace("frontend")->get('RegisterPasswordLength','',true);

                    $sErrorFlag["password"] = true;
                    $sErrorFlag["passwordConfirmation"] = true;
                } elseif ($postData["password"] != $postData["passwordConfirmation"]) {
                    $sErrorMessages[] = Shopware()->Snippets()->getNamespace("frontend")->get('AccountPasswordNotEqual', 'The passwords are not equal', true);
                    $sErrorFlag["password"] = true;
                    $sErrorFlag["passwordConfirmation"] = true;
                }
            }
            $register = $this->sSYSTEM->_SESSION["sRegister"];
            $register["auth"]["accountmode"] = "0"; // Setting account mode to ACCOUNT
            $this->sSYSTEM->_SESSION["sRegister"] = $register;
        } else {
            // Enforce the creation of an md5 hashed password for anonymous accounts
            $postData["password"] = md5(uniqid(rand()));
            $encoderName = 'md5';

            $register = $this->sSYSTEM->_SESSION["sRegister"];
            $register["auth"]["accountmode"] = "1";  // Setting account mode to NO_ACCOUNT
            $this->sSYSTEM->_SESSION["sRegister"] = $register;
        }

        // Check current password
        if ($edit && !empty(Shopware()->Config()->accountPasswordCheck)) {
            $password = $postData["currentPassword"];
            $current = Shopware()->Session()->sUserPassword;
            $snippet = Shopware()->Snippets()->getNamespace("frontend");
            if (empty($password) || !Shopware()->PasswordEncoder()->isPasswordValid($password, $current, $encoderName)) {
                $sErrorFlag['currentPassword'] = true;
                if ($postData["password"]) {
                    $sErrorFlag['password'] = true;
                } else {
                    $sErrorFlag['email'] = true;
                }
                $sErrorMessages[] = $snippet->get('AccountCurrentPassword', 'Das aktuelle Passwort stimmt nicht!', true);
            }
        }

        // Check if email is already registered
        if (isset($postData["email"]) && ($postData["email"] != $this->sSYSTEM->_SESSION["sUserMail"])) {
            $addScopeSql = "";
            if ($this->scopedRegistration == true) {
                $addScopeSql = "
                  AND subshopID = ".$this->subshopId;
            }
            $checkIfMailExists = $this->db->fetchRow(
                "SELECT id FROM s_user WHERE email = ? AND accountmode != 1 $addScopeSql",
                array($postData["email"])
            );
            if ($checkIfMailExists && !$postData["skipLogin"]) {
                $sErrorFlag["email"] = true;
                $sErrorMessages[] = $this->snippetObject->get('MailFailureAlreadyRegistered', 'This mail address is already registered');
            }
        }

        // Save data in session
        if (!$edit) {
            $register = $this->sSYSTEM->_SESSION["sRegister"];

            if (!count($sErrorFlag) && !count($sErrorMessages)) {
                $register['auth']["email"] = $postData["email"];
                // Receive Newsletter yes / no
                $register['auth']["receiveNewsletter"] = $postData["receiveNewsletter"];
                if ($postData["password"]) {
                    $register['auth']["encoderName"] = $encoderName;
                    $register['auth']["password"] = Shopware()->PasswordEncoder()->encodePassword($postData["password"], $encoderName);
                } else {
                    unset($register['auth']["password"]);
                    unset($register['auth']["encoderName"]);
                }
            } else {
                unset ($register['auth']["email"]);
                unset ($register['auth']["password"]);
                unset ($register['auth']["encoderName"]);
            }

            $this->sSYSTEM->_SESSION["sRegister"] = $register;
        }

        list($sErrorMessages, $sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_ValidateStep1_FilterResult',
            array($sErrorMessages, $sErrorFlag),
            array('edit' => $edit, 'subject' => $this, "post" => $this->sSYSTEM->_POST->toArray())
        );

        return array("sErrorFlag" => $sErrorFlag, "sErrorMessages" => $sErrorMessages);
    }

    /**
     * Login a user in the frontend
     * Used for login and registration in frontend, also for user impersonation
     * from backend
     *
     * @param boolean $ignoreAccountMode Allows customers who have chosen
     * the fast registration, one-time login after registration
     * @throws Exception If no password encoder is specified
     * @return array|false Array with errors that may have occurred, or false if
     * the process is interrupted by an event
     */
    public function sLogin($ignoreAccountMode = false)
    {
        if (Enlight()->Events()->notifyUntil(
            'Shopware_Modules_Admin_Login_Start',
            array(
                'subject'           => $this,
                'ignoreAccountMode' => $ignoreAccountMode,
                'post'              => $this->sSYSTEM->_POST->toArray()
            )
        )) {
            return false;
        }

        // If fields are not set, markup these fields
        $email = strtolower($this->sSYSTEM->_POST["email"]);
        if (empty($email)) {
            $sErrorFlag['email'] = true;
        }

        // If password is already md5-decrypted or the parameter $ignoreAccountMode is set, use it directly
        if ($ignoreAccountMode && $this->sSYSTEM->_POST['passwordMD5']) {
            $password = $this->sSYSTEM->_POST["passwordMD5"];
            $isPreHashed = true;
        } else {
            $password = $this->sSYSTEM->_POST["password"];
            $isPreHashed = false;
        }

        if (empty($password)) {
            $sErrorFlag["password"] = true;
        }

        if (!empty($sErrorFlag)) {
            $sErrorMessages[] = $this->snippetObject->get('LoginFailure', 'Wrong email or password');
            unset($this->sSYSTEM->_SESSION["sUserMail"]);
            unset($this->sSYSTEM->_SESSION["sUserPassword"]);
            unset($this->sSYSTEM->_SESSION["sUserId"]);
        }

        if (count($sErrorMessages)) {
            list($sErrorMessages, $sErrorFlag) = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_Login_FilterResult',
                array($sErrorMessages, $sErrorFlag),
                array('subject' => $this, 'email' => null, 'password' => null, 'error' => $sErrorMessages)
            );

            return array("sErrorFlag" => $sErrorFlag, "sErrorMessages" => $sErrorMessages);
        }

        $addScopeSql = "";
        if ($this->scopedRegistration == true) {
            $addScopeSql = " AND subshopID = " . $this->subshopId;
        }

        // When working with a prehashed password, we need to limit the getUser query by password,
        // as there might be multiple users with the same mail address (accountmode = 1).
        $preHashedSql = '';
        if ($isPreHashed) {
            $preHashedSql = " AND password = '{$password}'";
        }

        if ($ignoreAccountMode) {
            $sql = "SELECT id, customergroup, password, encoder FROM s_user WHERE email=? AND active=1 AND (lockeduntil < now() OR lockeduntil IS NULL) " . $addScopeSql . $preHashedSql;
        } else {
            $sql = "SELECT id, customergroup, password, encoder FROM s_user WHERE email=? AND active=1 AND accountmode!=1 AND (lockeduntil < now() OR lockeduntil IS NULL) " . $addScopeSql;
        }

        $getUser = $this->db->fetchRow($sql, array($email)) ? : array();

        if (!count($getUser)) {
            $isValidLogin = false;
        } else {
            if ($isPreHashed) {
                $encoderName = 'Prehashed';
            } else {
                $encoderName = $getUser['encoder'];
                $encoderName = strtolower($encoderName);
            }

            if (empty($encoderName)) {
                throw new \Exception('No encoder name given.');
            }

            $hash      = $getUser['password'];
            $plaintext = $password;
            $password  = $hash;

            $isValidLogin = Shopware()->PasswordEncoder()->isPasswordValid($plaintext, $hash, $encoderName);
        }

        if ($isValidLogin) {
            $this->regenerateSessionId();

            $this->db->query(
                "UPDATE s_user SET lastlogin=NOW(),failedlogins = 0, lockeduntil = NULL, sessionID=? WHERE id=?",
                array($this->sSYSTEM->sSESSION_ID, $getUser["id"])
            );

            Enlight()->Events()->notify(
                'Shopware_Modules_Admin_Login_Successful',
                array('subject' => $this, 'email' => $email, 'password' => $password, 'user' => $getUser)
            );

            $newHash = '';
            $liveMigration = Shopware()->Config()->liveMigration;
            $defaultEncoderName = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();

            // Do not allow live migration when the password is prehashed
            if ($liveMigration && !$isPreHashed && $encoderName !== $defaultEncoderName) {
                $newHash = Shopware()->PasswordEncoder()->encodePassword($plaintext, $defaultEncoderName);
                $encoderName = $defaultEncoderName;
            }

            if (empty($newHash)) {
                $newHash = Shopware()->PasswordEncoder()->reencodePassword($plaintext, $hash, $encoderName);
            }

            if (!empty($newHash) && $newHash !== $hash) {
                $hash = $newHash;
                $userId = (int) $getUser['id'];
                $this->db->update(
                    's_user',
                    array(
                        'password' => $hash,
                        'encoder'  => $encoderName,
                    ),
                    'id = ' . $userId
                );
            }

            $this->sSYSTEM->_SESSION["sUserMail"]     = $email;
            $this->sSYSTEM->_SESSION["sUserPassword"] = $hash;
            $this->sSYSTEM->_SESSION["sUserId"]       = $getUser["id"];

            $this->sCheckUser();
        } else {
            // Check if account is disabled
            $sql = "SELECT id FROM s_user WHERE email=? AND active=0 " . $addScopeSql;
            $getUser = $this->db->fetchOne($sql, array($email));
            if ($getUser) {
                $sErrorMessages[] = $this->snippetObject->get(
                    'LoginFailureActive',
                    'Your account is disabled. Please contact us.'
                );
            } else {
                $getLockedUntilTime = $this->db->fetchOne(
                    "SELECT 1 FROM s_user WHERE email = ? AND lockeduntil > NOW()",
                    array($email)
                );
                if (!empty($getLockedUntilTime)) {
                    $sErrorMessages[] = $this->snippetObject->get(
                        'LoginFailureLocked',
                        'Too many failed logins. Your account was temporary deactivated.'
                    );
                } else {
                    $sErrorMessages[] = $this->snippetObject->get('LoginFailure', 'Wrong email or password');
                }
            }

            // Prevent brute force login attempts
            if (!empty($email)) {
                $sql = "
                    UPDATE s_user SET
                        failedlogins = failedlogins + 1,
                        lockeduntil = IF(
                            failedlogins > 4,
                            DATE_ADD(NOW(), INTERVAL (failedlogins + 1) * 30 SECOND),
                            NULL
                        )
                    WHERE email = ? " . $addScopeSql;
                $this->db->query($sql, array($email));
            }

            Enlight()->Events()->notify(
                'Shopware_Modules_Admin_Login_Failure',
                array('subject' => $this, 'email' => $email, 'password' => $password, 'error' => $sErrorMessages)
            );

            unset($this->sSYSTEM->_SESSION["sUserMail"]);
            unset($this->sSYSTEM->_SESSION["sUserPassword"]);
            unset($this->sSYSTEM->_SESSION["sUserId"]);
        }

        list($sErrorMessages, $sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_Login_FilterResult',
            array($sErrorMessages, $sErrorFlag),
            array('subject' => $this, 'email' => $email, 'password' => $password, 'error' => $sErrorMessages)
        );

        return array("sErrorFlag" => $sErrorFlag, "sErrorMessages" => $sErrorMessages);
    }

    /**
     * Regenerates session id and updates references in the db
     * Used by sAdmin::sLogin
     */
    public function regenerateSessionId()
    {
        $oldSessionId = session_id();
        session_regenerate_id(true);
        $newSessionId = session_id();

        // close and restart session to make sure the db session handler writes updates.
        session_write_close();
        session_start();

        $this->sSYSTEM->sSESSION_ID = $newSessionId;
        Shopware()->Session()->offsetSet('sessionId', $newSessionId);
        Shopware()->Bootstrap()->resetResource('SessionId');
        Shopware()->Bootstrap()->registerResource('SessionId', $newSessionId);

        Enlight()->Events()->notify(
            'Shopware_Modules_Admin_Regenerate_Session_Id',
            array(
                'subject' => $this,
                'oldSessionId' => $oldSessionId,
                'newSessionId' => $newSessionId,
            )
        );

        $sessions = array(
            's_order_basket'            => 'sessionID',
            's_user'                    => 'sessionID',
            's_emarketing_lastarticles' => 'sessionID',
            's_order_comparisons'       => 'sessionID',
        );

        $conn = Shopware()->Models()->getConnection();
        foreach ($sessions as $tableName => $column) {
            $conn->update($tableName, array($column => $newSessionId), array($column => $oldSessionId));
        }
    }


    /**
     * Checks if user is correctly logged in. Also checks session timeout
     *
     * @return boolean If user is authorized
     */
    public function sCheckUser()
    {
        if (Enlight()->Events()->notifyUntil(
            'Shopware_Modules_Admin_CheckUser_Start',
            array('subject' => $this))
        ) {
            return false;
        }

        if (empty($this->sSYSTEM->_SESSION["sUserMail"])
            || empty($this->sSYSTEM->_SESSION["sUserPassword"])
            || empty($this->sSYSTEM->_SESSION["sUserId"])
        ) {
            unset($this->sSYSTEM->_SESSION["sUserMail"]);
            unset($this->sSYSTEM->_SESSION["sUserPassword"]);
            unset($this->sSYSTEM->_SESSION["sUserId"]);

            return false;
        }

        $sql = "
            SELECT * FROM s_user
            WHERE password = ? AND email = ? AND id = ?
            AND UNIX_TIMESTAMP(lastlogin) >= (UNIX_TIMESTAMP(now())-?)
        ";

        $timeOut = $this->sSYSTEM->sCONFIG['sUSERTIMEOUT'];
        $timeOut = !empty($timeOut) ? $timeOut : 7200;

        $getUser = $this->db->fetchRow(
            $sql,
            array(
                $this->sSYSTEM->_SESSION["sUserPassword"],
                $this->sSYSTEM->_SESSION["sUserMail"],
                $this->sSYSTEM->_SESSION["sUserId"],
                $timeOut
            )
        );
        $getUser = $getUser ? : array();

        $getUser = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_CheckUser_FilterGetUser',
            $getUser,
            array('subject' => $this, 'sql' => $sql, 'session' => $this->sSYSTEM->_SESSION)
        );

        if (!empty($getUser["id"])) {
            $this->sSYSTEM->sUSERGROUPDATA = $this->db->fetchRow(
                "SELECT * FROM s_core_customergroups WHERE groupkey = ?",
                array($getUser["customergroup"])
            );
            $this->sSYSTEM->sUSERGROUPDATA = $this->sSYSTEM->sUSERGROUPDATA ? : array();

            if ($this->sSYSTEM->sUSERGROUPDATA["mode"]) {
                $this->sSYSTEM->sUSERGROUP = "EK";
            } else {
                $this->sSYSTEM->sUSERGROUP = $getUser["customergroup"];
            }
            $this->sSYSTEM->sUSERGROUP = $getUser["customergroup"];

            $this->sSYSTEM->_SESSION["sUserGroup"] = $this->sSYSTEM->sUSERGROUP;
            $this->sSYSTEM->_SESSION["sUserGroupData"] = $this->sSYSTEM->sUSERGROUPDATA;

            $updateTime = $this->db->query(
                "UPDATE s_user SET lastlogin=NOW(), sessionID = ? WHERE id = ?",
                array($this->sSYSTEM->sSESSION_ID, $getUser["id"])
            );
            Enlight()->Events()->notify(
                'Shopware_Modules_Admin_CheckUser_Successful',
                array('subject' => $this, 'session' => $this->sSYSTEM->_SESSION, 'user' => $getUser)
            );

            return true;
        } else {
            unset($this->sSYSTEM->_SESSION["sUserMail"]);
            unset($this->sSYSTEM->_SESSION["sUserPassword"]);
            unset($this->sSYSTEM->_SESSION["sUserId"]);
            Enlight()->Events()->notify(
                'Shopware_Modules_Admin_CheckUser_Failure',
                array('subject' => $this, 'session' => $this->sSYSTEM->_SESSION, 'user' => $getUser)
            );

            return false;
        }
    }

    /**
     * Loads translations for countries. If no argument is provided,
     * all translations for current locale are returned, otherwise
     * returns the provided country's translation
     * Used internally in sAdmin
     *
     * @param array|string $country Optional array containing country data
     * for translation
     * @return array Translated country/ies data
     */
    public function sGetCountryTranslation($country = "")
    {
        // Load translation
        $sql = "
            SELECT objectdata FROM s_core_translations
            WHERE objecttype = 'config_countries' AND objectlanguage = ?
        ";

        $param = array($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);
        $getTranslation = $this->db->fetchRow(
            $sql,
            $param
        );
        $getTranslation = $getTranslation ? : array();

        if ($getTranslation["objectdata"]) {
            $object = unserialize($getTranslation["objectdata"]);
        }

        if (!$country) {
            return $object;
        }

        // Pass (possible) translation to country
        if ($object[$country["id"]]["countryname"]) {
            $country["countryname"] = $object[$country["id"]]["countryname"];
        }
        if ($object[$country["id"]]["notice"]) {
            $country["notice"] = $object[$country["id"]]["notice"];
        }

        if ($object[$country["id"]]["active"]) {
            $country["active"] = $object[$country["id"]]["active"];
        }

        return $country;
    }

    /**
     * Loads the translation for shipping methods. If no argument is provided,
     * all translations for current locale are returned, otherwise
     * returns the provided shipping methods translation
     * Used internally in sAdmin
     *
     * @param array|string $dispatch Optional array containing shipping method
     * data for translation
     * @return array Translated shipping method(s) data
     */
    public function sGetDispatchTranslation($dispatch = "")
    {
        // Load Translation
        $sql = "
            SELECT objectdata FROM s_core_translations
            WHERE objecttype='config_dispatch' AND objectlanguage = ?
        ";
        $params = array($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);
        $getTranslation = $this->db->fetchRow(
            $sql,
            $params
        );
        $getTranslation = $getTranslation ? : array();

        if ($getTranslation["objectdata"]) {
            $object = unserialize($getTranslation["objectdata"]);
        }

        if (!$dispatch) {
            return $object;
        }

        // Pass (possible) translation to country
        if ($object[$dispatch["id"]]["dispatch_name"]) {
            $dispatch["name"] = $object[$dispatch["id"]]["dispatch_name"];
        }
        if ($object[$dispatch["id"]]["dispatch_description"]) {
            $dispatch["description"] = $object[$dispatch["id"]]["dispatch_description"];
        }
        if ($object[$dispatch["id"]]["dispatch_status_link"]) {
            $dispatch["status_link"] = $object[$dispatch["id"]]["dispatch_status_link"];
        }

        return $dispatch;
    }

    /**
     * Loads the translation for payment means. If no argument is provided,
     * all translations for current locale are returned, otherwise
     * returns the provided payment means translation
     * Used internally in sAdmin
     *
     * @param array|string $payment Optional array containing payment mean
     * data for translation
     * @return array Translated payment mean(s) data
     */
    public function sGetPaymentTranslation($payment = "")
    {
        // Load Translation
        $sql = "
            SELECT objectdata FROM s_core_translations
            WHERE objecttype='config_payment' AND objectlanguage = ?
        ";
        $params = array($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);

        $getTranslation = $this->db->fetchRow(
            $sql,
            $params
        );
        $getTranslation = $getTranslation ? : array();

        if (!empty($getTranslation["objectdata"])) {
            $object = unserialize($getTranslation["objectdata"]);
        }

        if (!$payment) {
            return $object;
        }

        // Pass (possible) translation to payment
        if (!empty($object[$payment["id"]]["description"])) {
            $payment["description"] = $object[$payment["id"]]["description"];
        }
        if (!empty($object[$payment["id"]]["additionalDescription"])) {
            $payment["additionaldescription"] = $object[$payment["id"]]["additionalDescription"];
        }

        return $payment;
    }

    /**
     * Get translations for country states in the current shop language
     * Also includes fallback translations
     * Used internally in sAdmin
     *
     * @return array States translations
     */
    public function sGetCountryStateTranslation()
    {
        if (Shopware()->Shop()->get('skipbackend')) {
            return array();
        }
        $language = Shopware()->Shop()->get('isocode');
        $fallback = Shopware()->Shop()->get('fallback');

        $sql = "
            SELECT objectdata FROM s_core_translations
            WHERE objecttype = 'config_country_states'
            AND objectkey = 1
            AND objectlanguage = ?
        ";
        $translation = $this->db->fetchOne(
            $sql, array($language)
        );

        if (!empty($translation)) {
            $translation = unserialize($translation);
        } else {
            $translation = array();
        }

        if (!empty($fallback)) {
            $sql = "
                SELECT objectdata FROM s_core_translations
                WHERE objecttype = 'config_country_states'
                AND objectkey = 1
                AND objectlanguage = ?
            ";
            $translationFallback = $this->db->fetchOne(
                $sql, array($fallback)
            );
            if (!empty($translationFallback)) {
                $translationFallback = unserialize($translationFallback);
                $translation += $translationFallback;
            }
        }
        return $translation;
    }

    /**
     * Get list of currently active countries. Includes states and translations
     *
     * @return array Country list
     */
    public function sGetCountryList()
    {
        $countryList = $this->db->fetchAll(
            "SELECT * FROM s_core_countries WHERE active = 1 ORDER BY position, countryname ASC"
        );

        $countryTranslations = $this->sGetCountryTranslation();
        $stateTranslations = $this->sGetCountryStateTranslation();

        foreach ($countryList as $key => $country) {

            if (isset($countryTranslations[$country["id"]]["active"])) {
                if (!$countryTranslations[$country["id"]]["active"]) {
                    unset($countryList[$key]);
                    continue;
                }
            }

            $countryList[$key]["states"] = array();
            if (!empty($country["display_state_in_registration"])) {
                // Get country states
                $states = $this->db->fetchAssoc("
                    SELECT * FROM s_core_countries_states
                    WHERE countryID = ? AND active = 1
                    ORDER BY position, name ASC
                ", array($country["id"]));

                foreach ($states as $stateId => $state) {
                    if (isset($stateTranslations[$stateId])) {
                        $states[$stateId] = array_merge($state, $stateTranslations[$stateId]);
                    }
                }
                $countryList[$key]["states"] = $states;
            }
            if (!empty($countryTranslations[$country["id"]]["countryname"])) {
                $countryList[$key]["countryname"] = $countryTranslations[$country["id"]]["countryname"];
            }
            if (!empty($countryTranslations[$country["id"]]["notice"])) {
                $countryList[$key]["notice"] = $countryTranslations[$country["id"]]["notice"];
            }

            if ($countryList[$key]["id"] == $this->sSYSTEM->_POST['country']
                || $countryList[$key]["id"] == $this->sSYSTEM->_POST['countryID']
            ) {
                $countryList[$key]["flag"] = true;
            } else {
                $countryList[$key]["flag"] = false;
            }
        }

        $countryList = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_GetCountries_FilterResult',
            $countryList,
            array('subject' => $this)
        );

        return $countryList;
    }


    /**
     * Stores user data in database.
     * Used internally in sAdmin during the registration process
     *
     * @param array $userObject  Array with all information from the registration process
     * @return int Created user id
     */
    public function sSaveRegisterMainData($userObject)
    {
        // Support for merchants
        if ($userObject["billing"]["sValidation"]) {
            $sMerchant = $userObject["billing"]["sValidation"];
        } else {
            $sMerchant = "";
        }

        if (empty($this->sSYSTEM->sCONFIG["sDefaultCustomerGroup"])) {
            $this->sSYSTEM->sCONFIG["sDefaultCustomerGroup"] = "EK";
        }
        $referer = $this->sSYSTEM->_SESSION['sReferer'];

        if (!empty($this->sSYSTEM->_SESSION['sPartner'])) {
            $sql = 'SELECT id FROM s_emarketing_partner WHERE idcode = ?';
            $partner = (int) $this->db->fetchOne(
                $sql,
                array($this->sSYSTEM->_SESSION['sPartner'])
            );
        }

        $data = array(
            $userObject["auth"]["password"],
            $userObject["auth"]["email"],
            $userObject["payment"]["object"]["id"],
            $userObject["auth"]["accountmode"],
            empty($sMerchant) ? "" : $sMerchant,
            $this->sSYSTEM->sSESSION_ID,
            empty($partner) ? "" : $partner,
            $this->sSYSTEM->sCONFIG["sDefaultCustomerGroup"],
            $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"],
            $this->subshopId,
            empty($referer) ? "" : $referer,
            $userObject["auth"]["encoderName"],
        );
        $sql = '
            INSERT INTO s_user
            (
                password, email, paymentID, active, accountmode,
                validation, firstlogin,sessionID, affiliate, customergroup,
                language, subshopID, referer, encoder
            )
            VALUES (?,?,?,1,?,?,NOW(),?,?,?,?,?,?,?)
        ';

        list($sql, $data) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_SaveRegisterMainData_FilterSql',
            array($sql, $data),
            array('subject' => $this)
        );

        $saveUserData = $this->db->query($sql, $data);
        Enlight()->Events()->notify(
            'Shopware_Modules_Admin_SaveRegisterMainData_Return',
            array('subject' => $this,'insertObject' => $saveUserData)
        );

        $userId = $this->db->lastInsertId();

        $sql = "
            INSERT INTO s_user_attributes (userID) VALUES (?)
        ";
        $data = array($userId);

        list($sql,$data) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_SaveRegisterMainDataAttributes_FilterSql',
            array($sql, $data),
            array('subject' => $this)
        );
        $saveAttributeData = $this->db->query($sql, $data);

        Enlight()->Events()->notify(
            'Shopware_Modules_Admin_SaveRegisterMainDataAttributes_Return',
            array('subject' => $this, 'insertObject' => $saveAttributeData)
        );

        return $userId;
    }

    /**
     * Adds user's email to the mailing list
     * Used during registration
     *
     * @param array $userObject Array with all information from the registration process
     */
    public function sSaveRegisterNewsletter($userObject)
    {
        // Check for duplicates
        $checkDuplicate = $this->db->fetchRow("
            SELECT id FROM s_campaigns_mailaddresses WHERE email = ?",
            array($userObject["auth"]["email"])
        );

        if (empty($checkDuplicate["id"])) {
            $this->db->query(
                "INSERT INTO s_campaigns_mailaddresses (customer, groupID, email) VALUES (1, 0, ?)",
                array($userObject["auth"]["email"])
            );
        }
    }

    /**
     * Save user billing address.
     * Used internally in sAdmin during the registration process
     *
     * @param int $userID User id (s_user.id) from sSaveRegisterMain
     * @param array $userObject Array with all information from the registration process
     * @return int Created billing address id
     */
    public function sSaveRegisterBilling($userID, $userObject)
    {
        if ($userObject["billing"]["birthmonth"] == "-") {
            unset($userObject["billing"]["birthmonth"]);
        }
        if ($userObject["billing"]["birthday"] == "--") {
            unset($userObject["billing"]["birthday"]);
        }
        if ($userObject["billing"]["birthyear"] == "----") {
            unset($userObject["billing"]["birthyear"]);
        }

        if (!empty($userObject["billing"]["birthmonth"]) &&
            !empty($userObject["billing"]["birthday"]) &&
            !empty($userObject["billing"]["birthyear"])
        ) {
            $date = $userObject["billing"]["birthyear"] . "-" . $userObject["billing"]["birthmonth"]
                . "-" . $userObject["billing"]["birthday"];

            $date = date("Y-m-d", strtotime($date));
        } else {
            $date = "0000-00-00";
        }
        $userObject = $userObject["billing"];
        $data = array(
            $userID,
            empty($userObject["company"]) ? "" : $userObject["company"],
            empty($userObject["department"]) ? "" : $userObject["department"],
            empty($userObject["salutation"]) ? "" : $userObject["salutation"],
            $userObject["firstname"],
            $userObject["lastname"],
            $userObject["street"],
            $userObject["streetnumber"],
            $userObject["zipcode"],
            $userObject["city"],
            empty($userObject["phone"]) ? "" : $userObject["phone"],
            empty($userObject["fax"]) ? "" : $userObject["fax"],
            $userObject["country"],
            empty($userObject["stateID"]) ? 0 : $userObject["stateID"] ,
            empty($userObject["ustid"]) ? "" : $userObject["ustid"],
            $date
        );

        $sqlBilling = "INSERT INTO s_user_billingaddress
            (userID, company, department, salutation, firstname, lastname,
            street, streetnumber, zipcode, city,phone,
            fax, countryID, stateID, ustid, birthday)
            VALUES
            (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        // Trying to insert
        list($sqlBilling,$data) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_SaveRegisterBilling_FilterSql',
            array($sqlBilling,$data),
            array('subject' => $this)
        );

        $saveUserData = $this->db->query($sqlBilling,$data);
        Enlight()->Events()->notify(
            'Shopware_Modules_Admin_SaveRegisterBilling_Return',
            array('subject' => $this, 'insertObject' => $saveUserData)
        );

        //new attribute tables.
        $billingID = $this->db->lastInsertId();
        $attributeData = array(
            $billingID,
            empty($userObject["text1"]) ? "" : $userObject["text1"],
            empty($userObject["text2"]) ? "" : $userObject["text2"],
            empty($userObject["text3"]) ? "" : $userObject["text3"],
            empty($userObject["text4"]) ? "" : $userObject["text4"],
            empty($userObject["text5"]) ? "" : $userObject["text5"],
            empty($userObject["text6"]) ? "" : $userObject["text6"],
        );
        $sqlAttribute = "INSERT INTO s_user_billingaddress_attributes
                 (billingID, text1, text2, text3, text4, text5, text6)
                 VALUES
                 (?,?,?,?,?,?,?)";

        list($sqlAttribute,$attributeData) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_SaveRegisterBillingAttributes_FilterSql',
            array($sqlAttribute,$attributeData),
            array('subject' => $this)
        );
        $saveAttributeData = $this->db->query($sqlAttribute,$attributeData);
        Enlight()->Events()->notify(
            'Shopware_Modules_Admin_SaveRegisterBillingAttributes_Return',
            array('subject' => $this,'insertObject' => $saveAttributeData)
        );

        return $billingID;
    }

    /**
     * Save user shipping address.
     * Used internally in sAdmin during the registration process
     *
     * @param int $userID user id (s_user.id) from sSaveRegisterMain
     * @param array $userObject Array with all information from the registration process
     * @return int Created shipping address id
     */
    public function sSaveRegisterShipping($userID, $userObject)
    {
        $sqlShipping = "INSERT INTO s_user_shippingaddress
            (userID, company, department, salutation, firstname, lastname,
            street, streetnumber, zipcode, city, countryID, stateID)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";

        $sqlShipping = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_SaveRegisterShipping_FilterSql',
            $sqlShipping,
            array('subject' => $this, 'user' => $userObject, 'id' => $userID)
        );

        $shippingParams = array(
            $userID,
            $userObject["shipping"]["company"],
            $userObject["shipping"]["department"],
            $userObject["shipping"]["salutation"],
            $userObject["shipping"]["firstname"],
            $userObject["shipping"]["lastname"],
            $userObject["shipping"]["street"],
            $userObject["shipping"]["streetnumber"],
            $userObject["shipping"]["zipcode"],
            $userObject["shipping"]["city"],
            $userObject["shipping"]["country"],
            $userObject["shipping"]["stateID"]
        );
        // Trying to insert
        $saveUserData = $this->db->query($sqlShipping, $shippingParams);
        Enlight()->Events()->notify(
            'Shopware_Modules_Admin_SaveRegisterShipping_Return',
            array('subject' => $this, 'insertObject' => $saveUserData)
        );

        //new attribute table
        $shippingId = $this->db->lastInsertId();
        $sqlAttributes = "INSERT INTO s_user_shippingaddress_attributes
                 (shippingID, text1, text2, text3, text4, text5, text6)
                 VALUES
                 (?, ?, ?, ?, ?, ?, ?)";

        $sqlAttributes = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_SaveRegisterShippingAttributes_FilterSql',
            $sqlAttributes,
            array('subject' => $this, 'user' => $userObject, 'id' => $userID)
        );
        $attributeParams = array(
            $shippingId,
            $userObject["shipping"]["text1"],
            $userObject["shipping"]["text2"],
            $userObject["shipping"]["text3"],
            $userObject["shipping"]["text4"],
            $userObject["shipping"]["text5"],
            $userObject["shipping"]["text6"]
        );
        $saveAttributeData = $this->db->query($sqlAttributes, $attributeParams);
        Enlight()->Events()->notify(
            'Shopware_Modules_Admin_SaveRegisterShippingAttributes_Return',
            array('subject' => $this, 'insertObject' => $saveAttributeData)
        );

        return $shippingId;
    }

    /**
     * Send email with registration confirmation
     * Used internally in sAdmin during the registration process
     *
     * @param string $email Recipient email address
     * @return null|false False if stopped, null otherwise
     */
    public function sSaveRegisterSendConfirmation($email)
    {
        if (Enlight()->Events()->notifyUntil(
            'Shopware_Modules_Admin_SaveRegisterSendConfirmation_Start',
            array('subject' => $this,'email' => $email))
        ) {
            return false;
        }

        $context = array(
            'sMAIL'     => $email,
            'sShop'     => Shopware()->Config()->ShopName,
            'sShopURL'  => 'http://' . Shopware()->Config()->BasePath,
            'sConfig'   => Shopware()->Config(),
        );


        $namespace = Shopware()->Snippets()->getNamespace('frontend/account/index');
        foreach ($this->sSYSTEM->_SESSION["sRegister"]["billing"] as $key => $value) {
            if ($key == "salutation") {
                $value = ($value == "ms") ? $namespace->get('AccountSalutationMrs', 'Ms') : $namespace->get('AccountSalutationMr', 'Mr');
            }

            $context[$key] = $value;
        }

        $mail = Shopware()->TemplateMail()->createMail('sREGISTERCONFIRMATION', $context);
        $mail->addTo($email);

        if (!empty($this->sSYSTEM->sCONFIG["sSEND_CONFIRM_MAIL"])) {
            $mail->addBcc($this->sSYSTEM->sCONFIG['sMAIL']);
        }

        Enlight()->Events()->notify(
            'Shopware_Modules_Admin_SaveRegisterSendConfirmation_BeforeSend',
            array('subject' => $this,'mail' => $mail)
        );

        $mail->send();
    }

    /**
     * Main registration function used by the Register controller
     * Calls all previously defined helper functions to save user data
     *
     * @return boolean If the operation was successful
     */
    public function sSaveRegister()
    {
        if (Enlight()->Events()->notifyUntil(
            'Shopware_Modules_Admin_SaveRegister_Start',
            array('subject' => $this))
        ) {
            return false;
        }
        if (!$this->sSYSTEM->_SESSION["sRegisterFinished"]) {
            if (empty($this->sSYSTEM->_SESSION["sRegister"]["payment"]["object"]["id"])) {
                $register = $this->sSYSTEM->_SESSION["sRegister"];
                $register["payment"]["object"]["id"] = $this->sSYSTEM->sCONFIG['sDEFAULTPAYMENT'];
                $this->sSYSTEM->_SESSION["sRegister"] = $register;
            }

            $neededFields = array(
                "auth" => array(
                    "email",
                    "password"
                ),
                "billing" => array(
                    "salutation", "firstname",
                    "lastname", "street",
                    "streetnumber", "zipcode",
                    "city", "country"
                ),
                "payment" => array(
                    "object" => array("id")
                )
            );

            $neededFields = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_SaveRegister_FilterNeededFields',
                $neededFields,
                array('subject' => $this)
            );

            // Check for needed fields
            foreach ($neededFields as $sectionKey => $sectionFields) {
                foreach ($neededFields[$sectionKey] as $fieldKey => $fieldValue) {
                    if (is_array($fieldValue)) {

                        $objKey = $fieldValue[0];

                        if (empty($this->sSYSTEM->_SESSION["sRegister"][$sectionKey][$fieldKey][$objKey])) {
                            $errorFields[] = $sectionKey."#1($sectionKey)($fieldKey)($objKey)->".$fieldValue;
                        }
                    } else {
                        if (empty($this->sSYSTEM->_SESSION["sRegister"][$sectionKey][$fieldValue])) {
                            $errorFields[] = $sectionKey."#2->".$fieldValue;
                        }
                    }
                }
            }

            $errorFields = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_SaveRegister_FilterErrors',
                $errorFields,
                array('subject' => $this)
            );

            // Check for errors
            if (count($errorFields)) {
                if (!$_COOKIE["SHOPWARESID"]) {
                    $noCookies = "NO SESSION-COOKIE";
                }
                $this->sSYSTEM->E_CORE_WARNING("sSaveRegister #00","Fields are missing $noCookies - ".$this->sSYSTEM->sSESSION_ID." - ".print_r($errorFields,true));
                die ("Session Lost - Bitte aktivieren Sie Cookies in Ihrem Browser!");
                return false;
            } else {
                $userObject = $this->sSYSTEM->_SESSION["sRegister"];

                if (!$userObject["payment"]["object"]["id"]) {
                    $userObject["payment"]["object"]["id"] = $this->sSYSTEM->sCONFIG['sPAYMENTDEFAULT'];
                }

                // Save main user data
                $userID = $this->sSaveRegisterMainData($userObject);

                if ($this->db->getErrorMessage() || !$userID) {
                    $this->sSYSTEM->E_CORE_WARNING("sSaveRegister #01","Could not save data".$this->db->getErrorMessage().print_r($userObject));
                    die("sSaveRegister #01"."Could not save data".$this->db->getErrorMessage());
                }

                if ($userObject["auth"]["receiveNewsletter"]) {
                    $this->sSaveRegisterNewsletter($userObject);
                }

                // Save user billing address
                $userBillingID = $this->sSaveRegisterBilling($userID,$userObject);

                if ($this->db->getErrorMessage() || !$userBillingID) {
                    $this->sSYSTEM->E_CORE_WARNING("sSaveRegister #02","Could not save data (billing-adress)".$this->db->getErrorMessage().print_r($userObject,true));
                    die("Could not save data (billing-adress)".$this->db->getErrorMessage());
                }


                if ($this->sSYSTEM->sCONFIG['sSHOPWAREMANAGEDCUSTOMERNUMBERS']) {
                    if (!Enlight()->Events()->notifyUntil(
                        'Shopware_Modules_Admin_SaveRegister_GetCustomerNumber', 
                        array('subject' => $this,'id' => $userID))
                    ) {
                        $sql = "
                            UPDATE
                              s_order_number, s_user_billingaddress
                            SET
                              s_order_number.number = s_order_number.number+1,
                              s_user_billingaddress.customernumber = s_order_number.number
                            WHERE s_order_number.name = 'user'
                            AND s_user_billingaddress.userID = ?
                        ";
                        $this->db->query($sql, array($userID));
                    }
                }

                // Save user shipping address
                if (count($userObject["shipping"])) {
                    $userShippingID = $this->sSaveRegisterShipping($userID, $userObject);
                    if ($this->db->getErrorMessage() || !$userShippingID) {
                        $this->sSYSTEM->E_CORE_WARNING("sSaveRegister #02","Could not save data (shipping-address)".$this->db->getErrorMessage().print_r($userObject,true));
                        return false;
                    }
                }

                $uMail = $userObject["auth"]["email"];
                $uPass = $userObject["auth"]["password"];

                if ($userObject["auth"]["accountmode"] < 1) {
                    $this->sSaveRegisterSendConfirmation($uMail);
                    $this->sSYSTEM->_SESSION["sOneTimeAccount"] = false;
                } else {
                    $this->sSYSTEM->_SESSION["sOneTimeAccount"] = true;
                }

                // Save referer where user comes from
                if (!empty($this->sSYSTEM->_SESSION['sReferer'])) {
                    $referer = addslashes($this->sSYSTEM->_SESSION['sReferer']);
                    $sql = "
                        INSERT INTO
                            s_emarketing_referer (userID, referer, date)
                        VALUES (
                            ?, ?, NOW()
                    );";
                    $this->db->query($sql, array($userID, $referer));
                }

                $this->sSYSTEM->_POST["email"] = $uMail;
                $this->sSYSTEM->_POST["passwordMD5"] = $uPass;

                // Login user
                $chkUserLogin = $this->sLogin(true);

                // The user is now registered
                $this->sSYSTEM->_SESSION["sRegisterFinished"] = true;

                Enlight()->Events()->notify(
                    'Shopware_Modules_Admin_SaveRegister_Successful',
                    array(
                        'subject' => $this,
                        'id' => $userID,
                        'billingID' => $userBillingID,
                        'shippingID' => $userShippingID
                    )
                );

                // Garbage
                unset($this->sSYSTEM->_SESSION['sRegister']);
            }
        } else {
            $this->sSYSTEM->_POST["email"] = $this->sSYSTEM->_SESSION['sUserMail'];
            $this->sSYSTEM->_POST["passwordMD5"] = $this->sSYSTEM->_SESSION['sUserPassword'];
            $chkUserLogin = $this->sLogin($this->sSYSTEM->_SESSION["sOneTimeAccount"]);
        }
        return true;
    }

    /**
     * Get purchased instant downloads for the current user
     * Used in Account controller to display download available to the user
     * @param int $destinationPage
     * @param int $perPage
     * @return array Data from orders who contains instant downloads
     */
    public function sGetDownloads($destinationPage = 1, $perPage = 10)
    {
        $getOrders = $this->db->fetchAll(
            "SELECT
                id, ordernumber, invoice_amount, invoice_amount_net,
                invoice_shipping, invoice_shipping_net,
                DATE_FORMAT(ordertime, '%d.%m.%Y %H:%i') AS datum,
                status, cleared, comment
            FROM s_order WHERE userID = ? AND s_order.status >= 0
            ORDER BY ordertime DESC LIMIT 500",
            array($this->sSYSTEM->_SESSION["sUserId"])
        );

        foreach ($getOrders as $orderKey => $orderValue) {

            if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"])
                || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
            ) {
                $getOrders[$orderKey]["invoice_amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_amount_net"]);
                $getOrders[$orderKey]["invoice_shipping"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_shipping_net"]);
            } else {
                $getOrders[$orderKey]["invoice_amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_amount"]);
                $getOrders[$orderKey]["invoice_shipping"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_shipping"]);
            }

            $getOrderDetails = $this->db->fetchAll("
              SELECT * FROM s_order_details WHERE orderID = {$orderValue["id"]}
            ");

            if (!count($getOrderDetails)) {
                unset($getOrders[$orderKey]);
            } else {
                $foundESD = false;
                foreach ($getOrderDetails as $orderDetailsKey => $orderDetailsValue) {
                    $getOrderDetails[$orderDetailsKey]["amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(round($orderDetailsValue["price"] * $orderDetailsValue["quantity"],2));
                    $getOrderDetails[$orderDetailsKey]["price"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderDetailsValue["price"]);

                    // Check for serial
                    if ($getOrderDetails[$orderDetailsKey]["esdarticle"]) {
                        $foundESD = true;
                        $numbers = array();
                        $getSerial = $this->db->fetchAll("
                        SELECT serialnumber FROM s_articles_esd_serials, s_order_esd WHERE userID=".$this->sSYSTEM->_SESSION["sUserId"]."
                        AND orderID={$orderValue["id"]} AND orderdetailsID={$orderDetailsValue["id"]}
                        AND s_order_esd.serialID=s_articles_esd_serials.id
                        ");
                        foreach ($getSerial as $serial) {
                            $numbers[] = $serial["serialnumber"];
                        }
                        $getOrderDetails[$orderDetailsKey]["serial"] =  implode(",", $numbers);
                        // Building download link
                        $getOrderDetails[$orderDetailsKey]["esdLink"] = $this->sSYSTEM->sCONFIG["sBASEFILE"].'?sViewport=account&sAction=download&esdID='.$orderDetailsValue['id'];
                    } else {
                        unset($getOrderDetails[$orderDetailsKey]);
                    }
                }
                if (!empty($foundESD)) {
                    $getOrders[$orderKey]["details"] = $getOrderDetails;
                } else {
                    unset($getOrders[$orderKey]);
                }
            }
        }

        $getOrders = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_GetDownloads_FilterResult',
            $getOrders,
            array('subject' => $this,'id' => $this->sSYSTEM->_SESSION["sUserId"])
        );

        // Make Array with page-structure to render in template
        $numberOfPages = ceil(count($getOrders) / $perPage);
        $offset = ($destinationPage - 1) * $perPage;
        $orderData["orderData"] = array_slice($getOrders, $offset, $perPage, true);
        $orderData["numberOfPages"] = $numberOfPages;
        $orderData["pages"] = $this->getPagerStructure($destinationPage, $numberOfPages);

        return $orderData;

    }

    /**
     * Get all orders for the current user
     * Used in the user account in the Frontend
     * @param int $destinationPage
     * @param int $perPage
     * @return array Array with order data / positions
     */
    public function sGetOpenOrderData($destinationPage = 1, $perPage = 10)
    {
        $shop = Shopware()->Shop();
        $mainShop = $shop->getMain() !== null ? $shop->getMain() : $shop;

        $destinationPage = !empty($destinationPage) ? $destinationPage : 1;
        $limitStart = Shopware()->Db()->quote(($destinationPage - 1) * $perPage);
        $limitEnd = Shopware()->Db()->quote($perPage);

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS o.*, cu.templatechar as currency_html, DATE_FORMAT(ordertime, '%d.%m.%Y %H:%i') AS datum
            FROM s_order o
            LEFT JOIN s_core_currencies as cu
            ON o.currency = cu.currency
            WHERE userID = ? AND status != -1
            AND subshopID = ?
            ORDER BY ordertime DESC
            LIMIT $limitStart, $limitEnd
        ";
        $getOrders = $this->db->fetchAll(
            $sql,
            array($this->sSYSTEM->_SESSION["sUserId"], $mainShop->getId())
        );
        $foundOrdersCount = (int)Shopware()->Db()->fetchOne('SELECT FOUND_ROWS()');

        foreach ($getOrders as $orderKey => $orderValue) {

            $getOrders[$orderKey]["invoice_amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_amount"]);
            $getOrders[$orderKey]["invoice_shipping"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_shipping"]);


            $getOrderDetails = $this->db->fetchAll("
            SELECT * FROM s_order_details WHERE orderID={$orderValue["id"]} ORDER BY id ASC
            ");

            if (!count($getOrderDetails)) {
                unset($getOrders[$orderKey]);
            } else {
                $active = 1;

                foreach ($getOrderDetails as $orderDetailsKey => $orderDetailsValue) {
                    $getOrderDetails[$orderDetailsKey]["amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(round($orderDetailsValue["price"] * $orderDetailsValue["quantity"],2));
                    $getOrderDetails[$orderDetailsKey]["price"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderDetailsValue["price"]);

                    $tmpArticle = $this->sSYSTEM->sMODULES['sArticles']->sGetProductByOrdernumber($getOrderDetails[$orderDetailsKey]['articleordernumber']);

                    if (!empty($tmpArticle) && is_array($tmpArticle)) {

                        // Set article in activate state
                        $getOrderDetails[$orderDetailsKey]['active'] = 1;
                        if (!empty($tmpArticle['purchaseunit'])) {
                            $getOrderDetails[$orderDetailsKey]['purchaseunit'] = $tmpArticle['purchaseunit'];
                        }

                        if (!empty($tmpArticle['referenceunit'])) {
                            $getOrderDetails[$orderDetailsKey]['referenceunit'] = $tmpArticle['referenceunit'];
                        }

                        if (!empty($tmpArticle['referenceprice'])) {
                            $getOrderDetails[$orderDetailsKey]['referenceprice'] = $tmpArticle['referenceprice'];
                        }

                        if (!empty($tmpArticle['sUnit']) && is_array($tmpArticle['sUnit'])) {
                            $getOrderDetails[$orderDetailsKey]['sUnit'] = $tmpArticle['sUnit'];
                        }

                        if (!empty($tmpArticle['price'])) {
                            $getOrderDetails[$orderDetailsKey]['currentPrice'] = $tmpArticle['price'];
                        }

                        if (!empty($tmpArticle['pseudoprice'])) {
                            $getOrderDetails[$orderDetailsKey]['currentPseudoprice'] = $tmpArticle['pseudoprice'];
                        }

                        // Set article in deactivate state if it's an variant or configurator article
                        if ($tmpArticle['sVariantArticle'] === true || $tmpArticle['sConfigurator'] === true) {
                            $getOrderDetails[$orderDetailsKey]['active'] = 0;
                            $active = 0;
                        }
                    } else {
                        $getOrderDetails[$orderDetailsKey]['active'] = 0;
                        $active = 0;
                    }
                    /** GET ARTICLE DETAILS END */

                    // Check for serial
                    if ($getOrderDetails[$orderDetailsKey]["esdarticle"]) {
                        $numbers = array();
                        $sql = "
                        SELECT serialnumber FROM s_articles_esd_serials, s_order_esd WHERE userID = ?
                        AND orderID = ? AND orderdetailsID = ?
                        AND s_order_esd.serialID = s_articles_esd_serials.id
                        ";

                        $getSerial = $this->db->fetchAll(
                            $sql,
                            array(
                                $this->sSYSTEM->_SESSION["sUserId"],
                                $orderValue["id"],
                                $orderDetailsValue["id"]
                            )
                        );
                        foreach ($getSerial as $serial) {
                            $numbers[] = $serial["serialnumber"];
                        }
                        $getOrderDetails[$orderDetailsKey]["serial"] =  implode(",",$numbers);
                        // Building download-link
                        $getOrderDetails[$orderDetailsKey]["esdLink"] = $this->sSYSTEM->sCONFIG["sBASEFILE"].'?sViewport=account&sAction=download&esdID='.$orderDetailsValue['id'];
                        //$getOrderDetails[$orderDetailsKey]["esdLink"] = "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"]."/engine/core/php/loadesd.php?id=".$orderDetailsValue["id"];
                    }
                    // -- End of serial check
                }
                $getOrders[$orderKey]['activeBuyButton'] = $active;

                $getOrders[$orderKey]["details"] = $getOrderDetails;
            }
            $getOrders[$orderKey]["dispatch"] = $this->sGetPremiumDispatch($orderValue['dispatchID']);
        }

        $getOrders = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_GetOpenOrderData_FilterResult',
            $getOrders,
            array(
                'subject' => $this,
                'id' => $this->sSYSTEM->_SESSION["sUserId"],
                'subshopID' => $this->sSYSTEM->sSubShop["id"]
            )
        );

        $orderData["orderData"] = $getOrders;

        // Make Array with page-structure to render in template
        $numberOfPages = ceil($foundOrdersCount / $limitEnd);
        $orderData["numberOfPages"] = $numberOfPages;

        $orderData["pages"] = $this->getPagerStructure($destinationPage, $numberOfPages);
        return $orderData;
    }

    /**
     * Calculates and returns the pager structure for the frontend
     *
     * @param int $destinationPage
     * @param int $numberOfPages
     * @param array $additionalParams
     * @return array
     */
    public function getPagerStructure($destinationPage, $numberOfPages, $additionalParams = array())
    {
        $destinationPage = !empty($destinationPage) ? $destinationPage : 1;
        $pagesStructure = array();
        $baseFile = $this->sSYSTEM->sCONFIG['sBASEFILE'];
        if ($numberOfPages > 1) {
            for ($i = 1; $i <= $numberOfPages; $i++) {
                $pagesStructure["numbers"][$i]["markup"] = ($i == $destinationPage);
                $pagesStructure["numbers"][$i]["value"] = $i;
                $pagesStructure["numbers"][$i]["link"] = $baseFile . $this->sSYSTEM->sBuildLink(
                    $additionalParams + array("sPage" => $i),
                    false
                );
            }
            // Previous page
            if ($destinationPage != 1) {
                $pagesStructure["previous"] = $baseFile . $this->sSYSTEM->sBuildLink(
                    $additionalParams + array("sPage" => $destinationPage - 1),
                    false
                );
            } else {
                $pagesStructure["previous"] = null;
            }
            // Next page
            if ($destinationPage != $numberOfPages) {
                $pagesStructure["next"] = $baseFile . $this->sSYSTEM->sBuildLink(
                    $additionalParams + array("sPage" => $destinationPage + 1),
                    false
                );
            } else {
                $pagesStructure["next"] = null;
            }
        }
        return $pagesStructure;
    }

    /**
     * Get the current user's email address
     *
     * @return string Current user email address
     */
    public function sGetUserMailById()
    {
        $email = $this->db->fetchRow(
            "SELECT email FROM s_user WHERE id = ?",
            array($this->sSYSTEM->_SESSION["sUserId"])
        );

        return $email["email"];
    }

    /**
     * Get user id by his email address
     *
     * @param string $email Email address of the user
     * @return int The user id
     */
    public function sGetUserByMail($email)
    {
        $addScopeSql = "";
        if ($this->scopedRegistration == true) {
            $addScopeSql = "AND subshopID = ".$this->subshopId;
        }
        $getUserData = $this->db->fetchRow(
            "SELECT id FROM s_user WHERE email = ? AND accountmode != 1 $addScopeSql",
            array($email)
        );

        return $getUserData["id"];
    }

    /**
     * Get user first and last names by id
     *
     * @param int $id User id
     * @return array first name/last name
     */
    public function sGetUserNameById($id)
    {
        return $this->db->fetchRow(
            "SELECT firstname, lastname FROM s_user_billingaddress
            WHERE userID = ?",
            array($id)
        ) ? : array();
    }

    /**
     * Get all data from the current logged in user
     *
     * @return array|false User data, of false if interrupted
     */
    public function sGetUserData()
    {
        if (Enlight()->Events()->notifyUntil(
            'Shopware_Modules_Admin_GetUserData_Start',
            array('subject' => $this))
        ) {
            return false;
        }
        if (empty($this->sSYSTEM->_SESSION['sRegister'])) {
            $this->sSYSTEM->_SESSION['sRegister'] = array();
        }

        $userData = array();

        $countryQuery = "
          SELECT c.*, a.`name` AS countryarea
          FROM s_core_countries c
          LEFT JOIN s_core_countries_areas a
           ON a.id = c.areaID AND a.active = 1
          WHERE c.id = ?";

        // If user is logged in
        if (!empty($this->sSYSTEM->_SESSION["sUserId"])) {

            // 1.) Get billing address
            $sql = "SELECT * FROM s_user_billingaddress
                    WHERE userID = ?";

            $billing = $this->db->fetchRow(
                $sql,
                array($this->sSYSTEM->_SESSION["sUserId"])
            );
            $billing = $billing ? : array();
            $attributes = $this->getUserBillingAddressAttributes($this->sSYSTEM->_SESSION["sUserId"]);
            $userData["billingaddress"] = array_merge($attributes, $billing);

            if (empty($userData["billingaddress"]['customernumber'])
                && $this->sSYSTEM->sCONFIG['sSHOPWAREMANAGEDCUSTOMERNUMBERS']
            ) {
                $sql = "
                    UPDATE `s_order_number`,`s_user_billingaddress`
                    SET `s_order_number`.`number`=`s_order_number`.`number`+1,
                    `s_user_billingaddress`.`customernumber`=`s_order_number`.`number`+1
                    WHERE `s_order_number`.`name` ='user'
                    AND `s_user_billingaddress`.`userID`=?";

                $this->db->query($sql, array($this->sSYSTEM->_SESSION["sUserId"]));
            }

            // 2.) Advanced info
            // Query country information
            $userData["additional"]["country"] =  $this->db->fetchRow(
                "SELECT * FROM s_core_countries WHERE id = ?",
                array($userData["billingaddress"]["countryID"])
            );
            $userData["additional"]["country"] = $userData["additional"]["country"] ? : array();
            // State selection
            $userData["additional"]["state"] =  $this->db->fetchRow(
                "SELECT * FROM s_core_countries_states WHERE id=?",
                array($userData["billingaddress"]["stateID"])
            );
            $userData["additional"]["state"] = $userData["additional"]["state"] ? : array();

            $userData["additional"]["country"] = $this->sGetCountryTranslation($userData["additional"]["country"]);

            $additional = $this->db->fetchRow(
                "SELECT * FROM s_user WHERE id=?",
                array($this->sSYSTEM->_SESSION["sUserId"])
            );
            $additional = $additional ? : array();
            $attributes = $this->getUserAttributes($this->sSYSTEM->_SESSION["sUserId"]);
            $userData["additional"]["user"] = array_merge($attributes, $additional);

            // Newsletter properties
            $newsletter = $this->db->fetchRow(
                "SELECT id FROM s_campaigns_mailaddresses WHERE email = ?",
                array($userData["additional"]["user"]["email"])
            );

            $userData["additional"]["user"]["newsletter"] = $newsletter["id"] ? 1 : 0;

            // 3.) Get shipping address
            $shipping = $this->db->fetchRow(
                "SELECT * FROM s_user_shippingaddress WHERE userID=?",
                array($this->sSYSTEM->_SESSION["sUserId"])
            );
            $shipping = $shipping ? : array();
            $attributes = $this->getUserShippingAddressAttributes($this->sSYSTEM->_SESSION["sUserId"]);
            $userData["shippingaddress"]= array_merge($attributes, $shipping);

            // If shipping address is not available, billing address is coeval the shipping address
            if (!isset($userData["shippingaddress"]["firstname"])) {
                $userData["shippingaddress"] = $userData["billingaddress"];
                $userData["shippingaddress"]["eqalBilling"] = true;
            } else {
                if (($userData["shippingaddress"]["countryID"] != $userData["billingaddress"]["countryID"])
                    && empty($this->sSYSTEM->sCONFIG["sCOUNTRYSHIPPING"])
                ) {
                    $this->db->query(
                        "UPDATE s_user_shippingaddress SET countryID = ? WHERE id = ?",
                        array($userData["billingaddress"]["countryID"], $userData["shippingaddress"]["id"])
                    );
                    $userData["shippingaddress"]["countryID"] = $userData["billingaddress"]["countryID"];
                }
            }

            if (empty($userData["shippingaddress"]["countryID"])) {
                $targetCountryId = $userData["billingaddress"]["countryID"];
            } else {
                $targetCountryId = $userData["shippingaddress"]["countryID"];
            }

            $userData["additional"]["countryShipping"] = $this->db->fetchRow(
                $countryQuery,
                array($targetCountryId)
            );
            $userData["additional"]["countryShipping"] = $userData["additional"]["countryShipping"] ? : array();
            $userData["additional"]["countryShipping"] = $this->sGetCountryTranslation(
                $userData["additional"]["countryShipping"]
            );
            $this->sSYSTEM->_SESSION["sCountry"] = $userData["additional"]["countryShipping"]["id"];

            // State selection
            $userData["additional"]["stateShipping"] =  $this->db->fetchRow(
                "SELECT * FROM s_core_countries_states WHERE id=?",
                array($userData["shippingaddress"]["stateID"])
            );
            $userData["additional"]["stateShipping"] = $userData["additional"]["stateShipping"] ? : array();
            // Add stateId to session
            $this->sSYSTEM->_SESSION["sState"] = $userData["additional"]["stateShipping"]["id"];
            // Add areaId to session
            $this->sSYSTEM->_SESSION["sArea"] = $userData["additional"]["countryShipping"]["areaID"];
            $userData["additional"]["payment"] = $this->sGetPaymentMeanById(
                $userData["additional"]["user"]["paymentID"],
                $userData
            );
        } else {
            if ($this->sSYSTEM->_SESSION["sCountry"]
                && $this->sSYSTEM->_SESSION["sCountry"] != $this->sSYSTEM->_SESSION["sRegister"]["billing"]["country"]
            ) {
                $sRegister = $this->sSYSTEM->_SESSION['sRegister'];
                $sRegister['billing']['country']= intval($this->sSYSTEM->_SESSION["sCountry"]);
                $this->sSYSTEM->_SESSION["sRegister"] = $sRegister;
            }

            $userData["additional"]["country"] = $this->db->fetchRow(
                $countryQuery,
                array(intval($this->sSYSTEM->_SESSION["sRegister"]["billing"]["country"]))
            );
            $userData["additional"]["country"] = $userData["additional"]["country"] ? : array();
            $userData["additional"]["countryShipping"] = $userData["additional"]["country"];
            $userData["additional"]["stateShipping"]["id"] = !empty($this->sSYSTEM->_SESSION["sState"]) ? $this->sSYSTEM->_SESSION["sState"] : 0;


            /* todo@all Do we need a translation here? */
        }

        $userData = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_GetUserData_FilterResult',
            $userData,
            array('subject' => $this,'id' => $this->sSYSTEM->_SESSION["sUserId"])
        );

        return $userData;
    }

    /**
     * Returns the given user's billing address attributes
     *
     * @param $userId User id
     * @return array The given user's billing address attributes
     */
    private function getUserBillingAddressAttributes($userId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $attributes = $builder->select(array('attributes'))
            ->from('Shopware\Models\Attribute\CustomerBilling', 'attributes')
            ->innerJoin('attributes.customerBilling', 'billing')
            ->where('billing.customerId = :userId')
            ->setParameter('userId', $userId)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (!is_array($attributes)) {
            return array();
        } else {
            unset($attributes['id']);
            return $attributes;
        }
    }

    /**
     * Returns the given user's shipping address attributes
     *
     * @param $userId User id
     * @return array The given user's shipping address attributes
     */
    private function getUserShippingAddressAttributes($userId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $attributes = $builder->select(array('attributes'))
            ->from('Shopware\Models\Attribute\CustomerShipping', 'attributes')
            ->innerJoin('attributes.customerShipping', 'shipping')
            ->where('shipping.customerId = :userId')
            ->setParameter('userId', $userId)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (!is_array($attributes)) {
            return array();
        } else {
            unset($attributes['id']);
            return $attributes;
        }
    }

    /**
     * Returns the given user's attributes
     *
     * @param $userId User id
     * @return array The given user's attributes
     */
    private function getUserAttributes($userId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $attributes = $builder->select(array('attributes'))
            ->from('Shopware\Models\Attribute\Customer', 'attributes')
            ->where('attributes.customerId = :userId')
            ->setParameter('userId', $userId)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (!is_array($attributes)) {
            return array();
        } else {
            unset($attributes['id']);
            return $attributes;
        }
    }

    /**
     * Shopware Risk Management
     *
     * @param int $paymentID Payment mean id (s_core_paymentmeans.id)
     * @param array $basket Current shopping cart
     * @param array $user User data
     * @return boolean If customer is a risk customer
     */
    public function sManageRisks($paymentID, $basket, $user)
    {
        // Get all assigned rules
        $queryRules = $this->db->fetchAll("
            SELECT rule1, value1, rule2, value2
            FROM s_core_rulesets
            WHERE paymentID = ?
            ORDER BY id ASC
        ", array($paymentID));

        if (empty($queryRules)) {
            return false;
        }

        // Get Basket
        if (empty($basket)) {
            $session = Shopware()->Session();
            $basket = array(
                'content' => $session->sBasketQuantity,
                'AmountNumeric' => $session->sBasketAmount
            );
        }

        foreach ($queryRules as $rule) {
            if ($rule["rule1"] && !$rule["rule2"]) {
                $rule["rule1"] = "sRisk".$rule["rule1"];
                if ($this->$rule["rule1"]($user, $basket, $rule["value1"])) {
                    return true;
                }
            } elseif ($rule["rule1"] && $rule["rule2"]) {
                $rule["rule1"] = "sRisk".$rule["rule1"];
                $rule["rule2"] = "sRisk".$rule["rule2"];
                if ($this->$rule["rule1"]($user, $basket, $rule["value1"])
                    && $this->$rule["rule2"]($user, $basket, $rule["value2"])
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Risk management - Order value greater then
     * 
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskORDERVALUEMORE($user, $order, $value)
    {
        $basketValue = $order["AmountNumeric"];

        if ($this->sSYSTEM->sCurrency["factor"]) {
            $basketValue /= $this->sSYSTEM->sCurrency["factor"];
        }

        return ($basketValue >= $value);
    }

    /**
     * Risk management - Order value less then
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskORDERVALUELESS($user, $order, $value)
    {
        $basketValue = $order["AmountNumeric"];

        if ($this->sSYSTEM->sCurrency["factor"]) {
            $basketValue /= $this->sSYSTEM->sCurrency["factor"];
        }

        return ($basketValue <= $value);
    }

    /**
     * Risk management Customer group matches value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskCUSTOMERGROUPIS($user, $order, $value)
    {
        return ($user["additional"]["user"]["customergroup"] == $value);
    }

    /**
     * Risk management Customer group doesn't match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskCUSTOMERGROUPISNOT($user, $order, $value)
    {
        return ($user["additional"]["user"]["customergroup"] != $value);
    }

    /**
     * Risk management - Shipping or billing zip code match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskZIPCODE($user, $order, $value)
    {
        if ($value == "-1") {
            $value = "";
        }
        return ($user["shippingaddress"]["zipcode"] == $value || $user["billingaddress"]["zipcode"] == $value);
    }

    /**
     * Risk management - Country zone matches value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskZONEIS($user, $order, $value)
    {
        return ($user["additional"]["countryShipping"]["countryarea"] == $value);
    }

    /**
     * Risk management - Country zone doesn't match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskZONEISNOT($user, $order, $value)
    {
        return ($user["additional"]["countryShipping"]["countryarea"] != $value);
    }

    /**
     * Risk management - Country matches value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskLANDIS($user, $order, $value)
    {
        if (preg_match("/$value/", $user["additional"]["countryShipping"]["countryiso"])) {
            return true;
        }
        return ($user["additional"]["countryShipping"]["countryiso"] == $value);
    }

    /**
     * Risk management - Country doesn't match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskLANDISNOT($user, $order, $value)
    {
        if (!preg_match("/$value/", $user["additional"]["countryShipping"]["countryiso"])) {
            return true;
        }

        return ($user["additional"]["countryShipping"]["countryiso"] != $value);
    }


    /**
     * Risk management - Customer is new
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskNEWCUSTOMER($user, $order, $value)
    {
        return (
            $user["additional"]["user"]["firstlogin"] == date("Y-m-d")
            || !$user["additional"]["user"]["firstlogin"]
        );
    }

    /**
     * Risk management - Order has more then value positions
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskORDERPOSITIONSMORE($user, $order, $value)
    {
        return (
            (is_array($order["content"]) && count($order["content"]) >= $value)
            || $order["content"] >= $value
        );
    }

    /**
     * Risk management - Article attribute x from basket - positions is y
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskATTRIS($user, $order, $value)
    {
        if (!empty($order["content"])) {

            $value = explode("|",$value);
            if (!empty($value[0]) && isset($value[1])) {
                $number = (int) str_ireplace('attr', '', $value[0]);

                $sql = "
                SELECT s_articles_attributes.id
                FROM s_order_basket, s_articles_attributes, s_articles_details
                WHERE s_order_basket.sessionID = ?
                AND s_order_basket.modus = 0
                AND (
                    s_order_basket.ordernumber = s_articles_details.ordernumber
                    OR (s_order_basket.articleID = s_articles_details.articleID AND s_articles_details.kind = 1)
                )
                AND s_articles_details.id = s_articles_attributes.articledetailsID
                AND s_articles_attributes.attr{$number} = ?
                LIMIT 1
                ";

                $checkArticle = $this->db->fetchOne(
                    $sql,
                    array($this->sSYSTEM->sSESSION_ID, $value[1])
                );
                if ($checkArticle) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * Risk management - article attribute x from basket is not y
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskATTRISNOT($user, $order, $value)
    {
        if (!empty($order["content"])) {

            $value = explode("|",$value);
            if (!empty($value[0]) && isset($value[1])) {
                $number = (int) str_ireplace('attr', '', $value[0]);

                $sql = "
                SELECT s_articles_attributes.id
                FROM s_order_basket, s_articles_attributes, s_articles_details
                WHERE
                s_order_basket.sessionID=?
                AND s_order_basket.modus=0
                AND (
                s_order_basket.ordernumber = s_articles_details.ordernumber
                OR (s_order_basket.articleID = s_articles_details.articleID AND s_articles_details.kind = 1)
                )
                AND s_articles_details.id = s_articles_attributes.articledetailsID
                AND s_articles_attributes.attr{$number}!= ?
                LIMIT 1
                ";
                $checkArticle = $this->db->fetchOne(
                    $sql,
                    array(
                        $this->sSYSTEM->sSESSION_ID,
                        $value[1]
                    )
                );


                if ($checkArticle) {
                    return true;
                } else {
                    return false;
                }

            } else {
                return false;
            }

        }
    }

    /**
     * Risk management - customer had payment problems in past
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskINKASSO($user, $order, $value)
    {
        if ($this->sSYSTEM->_SESSION["sUserId"]) {
            $checkOrder = $this->db->fetchRow("
                SELECT id FROM s_order
                WHERE cleared=16 AND userID=?",
                array($this->sSYSTEM->_SESSION["sUserId"])
            );
            return ($checkOrder && $checkOrder["id"]);
        } else {
            return false;
        }
    }

    /**
     * Risk management - Last order less x days
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskLASTORDERLESS($user, $order, $value)
    {
        // A order from previous x days must exists
        if ($this->sSYSTEM->_SESSION["sUserId"]) {
            $value = (int) $value;
            $sql = "
            SELECT id FROM s_order WHERE userID=?
            AND TO_DAYS(ordertime) <= (TO_DAYS(now())-$value) LIMIT 1
            ";
            $checkOrder = $this->db->fetchRow(
                $sql,
                array(
                    $this->sSYSTEM->_SESSION["sUserId"]
                )
            );

            return (!$checkOrder || !$checkOrder["id"]);
        } else {
            return true;
        }
    }

    /**
     * Risk management - Articles from a certain category
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskARTICLESFROM($user, $order, $value)
    {
        $checkArticle = $this->db->fetchOne("
            SELECT s_articles_categories_ro.id as id
            FROM s_order_basket, s_articles_categories_ro
            WHERE s_order_basket.articleID = s_articles_categories_ro.articleID
            AND s_articles_categories_ro.categoryID = ?
            AND s_order_basket.sessionID=?
            AND s_order_basket.modus=0
        ", array($value, $this->sSYSTEM->sSESSION_ID));

        return (!empty($checkArticle));
    }

    /**
     * Risk management - Order value greater then
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskLASTORDERSLESS($user, $order, $value)
    {
        if ($this->sSYSTEM->_SESSION["sUserId"]) {
            $checkOrder = $this->db->fetchAll(
                "SELECT id FROM s_order
                  WHERE status != -1 AND status != 4 AND userID = ?",
                array($this->sSYSTEM->_SESSION["sUserId"])
            );
            if (count($checkOrder) <= $value) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /**
     * Risk management - Block if street contains pattern
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskPREGSTREET($user, $order, $value)
    {
        $value = strtolower($value);
        if (preg_match("/$value/",strtolower($user["shippingaddress"]["street"]))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Risk management - Block if billing address not equal to shipping address
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskDIFFER($user, $order, $value)
    {
        if (strtolower(trim($user["shippingaddress"]["street"]).trim($user["shippingaddress"]["streetnumber"])) != strtolower(trim($user["billingaddress"]["street"]).trim($user["billingaddress"]["streetnumber"]))) {
            return true;
        } elseif (trim($user["shippingaddress"]["zipcode"]) != trim($user["billingaddress"]["zipcode"])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Risk management - Block if customer number matches pattern
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskCUSTOMERNR($user, $order, $value)
    {
        if ($user["billingaddress"]["customernumber"] == $value && !empty($value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Risk management - Block if last name matches pattern
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskLASTNAME($user, $order, $value)
    {
        $value = strtolower($value);
        if (preg_match("/$value/",strtolower($user["shippingaddress"]["lastname"])) || preg_match("/$value/",strtolower($user["billingaddress"]["lastname"]))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Risk management -  Block if subshop id is x
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskSUBSHOP($user, $order, $value)
    {
        if ($this->sSYSTEM->sSubShop["id"]==$value) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Risk management -  Block if subshop id is not x
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskSUBSHOPNOT($user, $order, $value)
    {
        if ($this->sSYSTEM->sSubShop["id"]!=$value) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Risk management - Block if currency id is not x
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskCURRENCIESISOIS($user, $order, $value)
    {
        if (strtolower($this->sSYSTEM->sCurrency['currency']) == strtolower($value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Risk management - Block if currency id is x
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     * @return bool Rule validation result
     */
    public function sRiskCURRENCIESISOISNOT($user, $order, $value)
    {
        if (strtolower($this->sSYSTEM->sCurrency['currency']) != strtolower($value)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Subscribe / unsubscribe to mailing list
     * Used in the Newsletter frontend controller to manage subscriptions
     *
     * @param string $email Email address
     * @param boolean $unsubscribe If true, remove email address from mailing list
     * @param int $groupID Id of the mailing list group
     * @return array Array with the result of the operation
     */
    public function sNewsletterSubscription($email, $unsubscribe = false, $groupID = null)
    {
        if (empty($unsubscribe)) {
            $errorflag = array();

            $fields = array('newsletter');
            foreach ($fields as $field) {
                if (isset($this->sSYSTEM->_POST[$field]) && empty($this->sSYSTEM->_POST[$field])) {
                    $errorflag[$field] = true;
                }
            }
            if (!empty($errorflag)) {
                return array(
                    'code' => 5,
                    'message' => $this->snippetObject->get('ErrorFillIn','Please fill in all red fields'),
                    'sErrorFlag' => $errorflag
                );
            }
        }

        if (empty($groupID)) {
            $groupID = $this->sSYSTEM->sCONFIG["sNEWSLETTERDEFAULTGROUP"];
            $sql = '
                INSERT IGNORE INTO s_campaigns_groups (id, name)
                VALUES (?, ?)
            ';
            $this->db->query($sql, array($groupID, 'Newsletter-Empfänger'));
        }

        $email = trim(strtolower(stripslashes($email)));
        if(empty($email)) {
            return array(
                "code" => 6,
                "message" => $this->snippetObject->get('NewsletterFailureMail', 'Enter eMail address')
            );
        }
        $validator = new Zend_Validate_EmailAddress();
        $validator->getHostnameValidator()->setValidateTld(false);
        if (!$validator->isValid($email)) {
            return array("code" => 1, "message" => $this->snippetObject->get('NewsletterFailureInvalid', 'Enter valid eMail address'));
        }
        if (!$unsubscribe) {
            $sql = "SELECT * FROM s_campaigns_mailaddresses WHERE email = ?";
            $result = $this->db->query($sql, array($email));

            if ($result === false) {
                $result = array(
                    "code" => 10,
                    "message" => $this->snippetObject->get('UnknownError', 'Unknown error')
                );
            } elseif ($result->rowCount()) {
                $result = array(
                    "code" => 2,
                    "message" => $this->snippetObject->get('NewsletterFailureAlreadyRegistered','You already receive our newsletter')
                );
            } else {
                $customer = $this->db->fetchOne(
                    'SELECT id FROM s_user WHERE email = ? LIMIT 1',
                    array($email)
                );

                $sql = "INSERT INTO s_campaigns_mailaddresses (customer, `groupID`, email) VALUES(?, ?, ?)";
                $result = $this->db->query(
                    $sql,
                    array(
                        (int) !empty($customer),
                        $groupID,
                        $email
                    )
                );

                if($result === false) {
                    $result = array(
                        "code" => 10,
                        "message" => $this->snippetObject->get('UnknownError', 'Unknown error')
                    );
                } else {
                    $result = array(
                        "code" => 3,
                        "message" => $this->snippetObject->get('NewsletterSuccess', 'Thank you for receiving our newsletter')
                    );
                }
            }
        } else {
            $sql = "DELETE FROM s_campaigns_mailaddresses WHERE email = ?";
            $result1 = $this->db->query($sql, array($email));
            $result = $result1->rowCount();

            $sql = "UPDATE s_user SET newsletter = 0 WHERE email = ?";
            $result2 =$this->db->query($sql, array($email));
            $result += $result2->rowCount();

            if ($result1 === false || $result2 === false) {
                $result = array(
                    "code" => 10,
                    "message" => $this->snippetObject->get('UnknownError','Unknown error')
                );
            }
            elseif (empty($result)) {
                $result = array(
                    "code" => 4,
                    "message" => $this->snippetObject->get('NewsletterFailureNotFound', 'This mail address could not be found')
                );
            }
            else {
                $result = array(
                    "code" => 5,
                    "message" => $this->snippetObject->get('NewsletterMailDeleted', 'Your mail address was deleted')
                );
            }
        }

        if (!empty($result['code']) && in_array($result['code'], array(2, 3))) {
            $sql = '
                REPLACE INTO `s_campaigns_maildata` (`email`, `groupID`, `salutation`, `title`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `added`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ';
            $this->db->query($sql, array(
                $email,
                $groupID,
                $this->sSYSTEM->_POST['salutation'],
                $this->sSYSTEM->_POST['title'],
                $this->sSYSTEM->_POST['firstname'],
                $this->sSYSTEM->_POST['lastname'],
                $this->sSYSTEM->_POST['street'],
                $this->sSYSTEM->_POST['streetnumber'],
                $this->sSYSTEM->_POST['zipcode'],
                $this->sSYSTEM->_POST['city']
            ));
        } elseif (!empty($unsubscribe)) {
            $sql = 'DELETE FROM `s_campaigns_maildata` WHERE `email` = ? AND `groupID` = ?';
            $this->db->query($sql, array($email, $groupID));
        }

        return $result;
    }

    /**
     * Generate table with german holidays
     *
     * @return boolean
     */
    public function sCreateHolidaysTable()
    {
        if (!function_exists('easter_days')) {
            function easter_days($year)
            {
                $G = $year % 19;
                $C = (int) ($year / 100);
                $H = (int) ($C - (int) ($C / 4) - (int) ((8*$C+13) / 25) + 19*$G + 15) % 30;
                $I = (int) $H - (int) ($H / 28)*(1 - (int) ($H / 28)*(int) (29 / ($H + 1))*((int) (21 - $G) / 11));
                $J = ($year + (int) ($year/4) + $I + 2 - $C + (int) ($C/4)) % 7;
                $L = $I - $J;
                $m = 3 + (int) (($L + 40) / 44);
                $d = $L + 28 - 31 * ((int) ($m / 4));
                $E = mktime(0,0,0, $m, $d, $year)-mktime(0,0,0,3,21,$year);
                return intval(round($E/(60*60*24),0));
            }
        }
        $sql = "
            SELECT id, calculation, `date`
            FROM `s_premium_holidays`
            WHERE `date`<CURDATE()
        ";
        $holidays = $this->db->fetchAssoc($sql);
        if(empty($holidays)) {
            return true;
        }

        foreach ($holidays as $id => $holiday) {
            $calculation = $holiday['calculation'];
            $datestamp = strtotime($holiday['date']);
            $date = date('Y-m-d',$datestamp);
            $year = date('Y',$datestamp)+1;
            $easter_date = date('Y-m-d',mktime(0,0,0,3,21+easter_days($year),$year));

            $calculation = preg_replace("#DATE\('(\d+)[\-/](\d+)'\)#i","DATE(CONCAT(YEAR(),'-','\$1-\$2'))",$calculation);
            $calculation = str_replace("EASTERDATE()","'$easter_date'",$calculation);
            $calculation = str_replace("YEAR()","'$year'",$calculation);
            $calculation = str_replace("DATE()","'$date'",$calculation);
            $sql = "UPDATE s_premium_holidays SET `date`= $calculation WHERE id = $id";
            $this->db->query($sql);
        }
    }

    /**
     * Get country from its id or iso code
     * Used internally in sAdmin::sGetPremiumShippingcosts()
     *
     * @param int $country Country id or iso code
     * @return array|false Array with country information, including area, or false if empty argument
     */
    public function sGetCountry($country)
    {
        static $cache = array();
        if(empty($country)) {
            return false;
        }
        if(isset($cache[$country])) {
            return $cache[$country];
        }
        if (is_numeric($country)) {
            $sql = "c.id=".$country;
        } elseif (is_string($country)) {
            $sql = "c.countryiso=".$this->db->quote($country);
        } else {
            return false;
        }

        $sql = "
            SELECT c.id, c.id as countryID, countryname, countryiso,
                (SELECT name FROM s_core_countries_areas WHERE id = areaID ) AS countryarea,
                countryen, c.position, notice, c.shippingfree as shippingfree
            FROM s_core_countries c
            WHERE $sql
        ";
        $currencyFactor = empty($this->sSYSTEM->sCurrency["factor"]) ? 1 : $this->sSYSTEM->sCurrency["factor"];
        $cache[$country]["shippingfree"] = round($cache[$country]["shippingfree"]*$currencyFactor, 2);
        return $cache[$country] = $this->db->fetchRow($sql) ? : array();
    }

    /**
     * Get a specific payment
     * Used internally in sAdmin::sGetPremiumShippingcosts()
     *
     * @param int $payment Payment mean id or name
     * @return array|false Array with payment mean information, including area, or false if empty argument
     */
    public function sGetPaymentmean($payment)
    {
        static $cache = array();
        if (empty($payment)) {
            return false;
        }
        if (isset($cache[$payment])) {
            return $cache[$payment];
        }
        if (is_numeric($payment)) {
            $sql = "id=".$payment;
        } elseif (is_string($payment)) {
            $sql = "name=".$this->db->quote($payment);
        } else {
            return false;
        }

        $sql = "
            SELECT * FROM s_core_paymentmeans
            WHERE $sql
        ";
        $cache[$payment] = $this->db->fetchRow($sql) ? : array();

        $cache[$payment]["country_surcharge"] = array();
        if (!empty($cache[$payment]["surchargestring"])) {
            foreach (explode(";",$cache[$payment]["surchargestring"]) as $countrySurcharge) {
                list($key,$value) = explode(":",$countrySurcharge);
                $value = floatval(str_replace(",",".",$value));
                if (!empty($value)) {
                    $cache[$payment]["country_surcharge"][$key] = $value;
                }
            }
        }
        return $cache[$payment];
    }

    /**
     * Get dispatch data for basket
     * Used internally in sAdmin::sGetPremiumShippingcosts() and sAdmin::sGetPremiumDispatches()
     *
     * @param int $countryID Country id
     * @param int $paymentID Payment mean id
     * @param int $stateId Country state id
     * @return array|false Array with dispatch data for the basket, or false if no basket
     */
    public function sGetDispatchBasket($countryID = null, $paymentID = null, $stateId = null)
    {
        $sql_select = '';
        if (!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNGASKETSELECT'])) {
            $sql_select .= ', '.$this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNGASKETSELECT'];
        }
        $sql = 'SELECT id, calculation_sql FROM s_premium_dispatch WHERE active = 1 AND calculation = 3';
        $calculations = $this->db->fetchAssoc($sql);
        if(!empty($calculations)) {
            foreach ($calculations as $dispatchID => $calculation) {
                if(empty($calculation)) $calculation = $this->db->quote($calculation);
                $sql_select .= ', ('.$calculation.') as calculation_value_'.$dispatchID;
            }
        }
        if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])) {
            $amount = 'b.quantity*ROUND(CAST(b.price as DECIMAL(10,2))*(100+t.tax)/100,2)';
            $amount_net = 'b.quantity*CAST(b.price as DECIMAL(10,2))';
        } else {
            $amount = 'b.quantity*CAST(b.price as DECIMAL(10,2))';
            $amount_net = 'b.quantity*ROUND(CAST(b.price as DECIMAL(10,2))/(100+t.tax)*100,2)';
        }

        $sql = "
            SELECT
                MIN(d.instock>=b.quantity) as instock,
                MIN(d.instock>=(b.quantity+d.stockmin)) as stockmin,
                MIN(a.laststock) as laststock,
                SUM(d.weight*b.quantity) as weight,
                SUM(IF(a.id,b.quantity,0)) as count_article,
                MAX(b.shippingfree) as shippingfree,
                SUM(IF(b.modus=0,$amount/b.currencyFactor,0)) as amount,
                SUM(IF(b.modus=0,$amount_net/b.currencyFactor,0)) as amount_net,
                SUM(CAST(b.price as DECIMAL(10,2))*b.quantity) as amount_display,
                MAX(d.length) as `length`,
                MAX(d.height) as height,
                MAX(d.width) as width,
                u.id as userID
                $sql_select
            FROM s_order_basket b

            LEFT JOIN s_articles a
            ON b.articleID = a.id
            AND b.modus = 0
            AND b.esdarticle = 0

            LEFT JOIN s_articles_details d
            ON (d.ordernumber = b.ordernumber)
            AND d.articleID = a.id

            LEFT JOIN s_articles_attributes at
            ON at.articledetailsID = d.id

            LEFT JOIN s_core_tax t
            ON t.id = a.taxID

            LEFT JOIN s_user u
            ON u.id = ?
            AND u.active = 1

            LEFT JOIN s_user_billingaddress ub
            ON ub.userID = u.id

            LEFT JOIN s_user_shippingaddress us
            ON us.userID = u.id

            WHERE b.sessionID = ?

            GROUP BY b.sessionID
        ";

        $basket = $this->db->fetchRow(
            $sql,
            array(
                $this->sSYSTEM->_SESSION["sUserId"],
                empty($this->sSYSTEM->sSESSION_ID) ? session_id() : $this->sSYSTEM->sSESSION_ID
            )
        );
        if ($basket === false) {
            return false;
        }

        $basket["max_tax"] = $this->sSYSTEM->sMODULES['sBasket']->getMaxTax();

        if (!empty($paymentID)) {
            $paymentID = (int) $paymentID;
        } elseif (!empty($this->sSYSTEM->_SESSION['sUserId'])) {
            $user = $this->sGetUserData();
            $paymentID = (int) $user['additional']['payment']['id'];
        } elseif (!empty($this->sSYSTEM->_POST['sPayment'])) {
            $paymentID = (int) $this->sSYSTEM->_POST['sPayment'];
        } elseif (!empty($this->sSYSTEM->_SESSION['sPaymentID'])) {
            $paymentID = (int) $this->sSYSTEM->_SESSION['sPaymentID'];
        }

        $paymentMeans = $this->sGetPaymentMeans();
        $paymentIDs = array();
        foreach ($paymentMeans as $paymentMean) {
            $paymentIDs[] = $paymentMean['id'];
        }
        if (!in_array($paymentID, $paymentIDs)) {
            $paymentID = reset($paymentIDs);
        }

        if (empty($countryID) && !empty($user['additional']['countryShipping']['id'])) {
            $countryID = (int) $user['additional']['countryShipping']['id'];
        } else {
            $countryID = (int) $countryID;
        }

        if (!empty($user['additional']['stateShipping']['id'])) {
            $stateId = $user['additional']['stateShipping']['id'];
        }
        $sql = "
            SELECT main_id FROM s_core_shops WHERE id=".(int) $this->sSYSTEM->sSubShop['id']."
        ";
        $mainId = $this->db->fetchOne($sql);
        // MainId is null, so we use the current shop id
        if (is_null($mainId)) {
            $mainId = (int) $this->sSYSTEM->sSubShop['id'];
        }
        $basket['basketStateId'] = (int) $stateId;
        $basket['countryID'] = $countryID;
        $basket['paymentID'] = $paymentID;
        $basket['customergroupID'] = (int) $this->sSYSTEM->sUSERGROUPDATA['id'];
        $basket['multishopID'] = $mainId;
        $basket['sessionID'] = $this->sSYSTEM->sSESSION_ID;

        return $basket;
    }

    /**
     * Get premium dispatch method
     * Used internally, in sOrder and AboCommerce plugin
     *
     * @param int $dispatchID Dispatch method id
     * @return array|false Array with dispatch method data
     */
    public function sGetPremiumDispatch($dispatchID = null)
    {
        $sql = "
            SELECT d.id, `name`, d.description, calculation, status_link,
              surcharge_calculation, bind_shippingfree, shippingfree, tax_calculation,
              t.tax as tax_calculation_value
            FROM s_premium_dispatch d
            LEFT JOIN s_core_tax t
            ON t.id = d.tax_calculation
            WHERE active = 1
            AND d.id = ?
        ";
        $dispatch = $this->db->fetchRow($sql, array($dispatchID));
        if ($dispatch === false) {
            return false;
        }
        return $this->sGetDispatchTranslation($dispatch);
    }

    /**
     * Get dispatch methods
     *
     * @param int $countryID Country id
     * @param int $paymentID Payment mean id
     * @param int $stateId Country state id
     * @return array Shipping methods data
     */
    public function sGetPremiumDispatches($countryID = null, $paymentID = null, $stateId = null)
    {
        $this->sCreateHolidaysTable();

        $basket = $this->sGetDispatchBasket($countryID, $paymentID, $stateId);

        $sql = "
            SELECT id, bind_sql
            FROM s_premium_dispatch
            WHERE active=1 AND type IN (0)
            AND bind_sql IS NOT NULL AND bind_sql != ''
        ";
        $statements = $this->db->fetchAssoc($sql);

        if(empty($basket)) {
            return array();
        }

        $sql_where = "";
        foreach ($statements as $dispatchID => $statement) {
            $sql_where .= " AND ( d.id != $dispatchID OR ($statement)) ";
        }

        $sql_basket = array();
        foreach ($basket as $key => $value) {
            $sql_basket[] = $this->db->quote($value)." as `$key`";
        }
        $sql_basket = implode(', ',$sql_basket);

        $sql = "
            SELECT
                d.id as `key`,
                d.id, d.name,
                d.description,
                d.calculation,
                d.status_link,
                b.*
            FROM s_premium_dispatch d

            JOIN ( SELECT $sql_basket ) b
            JOIN s_premium_dispatch_countries dc
            ON d.id = dc.dispatchID
            AND dc.countryID=b.countryID
            JOIN s_premium_dispatch_paymentmeans dp
            ON d.id = dp.dispatchID
            AND dp.paymentID=b.paymentID
            LEFT JOIN s_premium_holidays h
            ON h.date = CURDATE()
            LEFT JOIN s_premium_dispatch_holidays dh
            ON d.id=dh.dispatchID
            AND h.id=dh.holidayID

            LEFT JOIN (
                SELECT dc.dispatchID
                FROM s_order_basket b
                JOIN s_articles_categories_ro ac
                ON ac.articleID=b.articleID
                JOIN s_premium_dispatch_categories dc
                ON dc.categoryID=ac.categoryID
                WHERE b.modus=0
                AND b.sessionID='{$this->sSYSTEM->sSESSION_ID}'
                GROUP BY dc.dispatchID
            ) as dk
            ON dk.dispatchID=d.id

            LEFT JOIN s_user u
            ON u.id=b.userID
            AND u.active=1

            LEFT JOIN s_user_billingaddress ub
            ON ub.userID=u.id

            LEFT JOIN s_user_shippingaddress us
            ON us.userID=u.id

            WHERE d.active=1
            AND (
                (bind_time_from IS NULL AND bind_time_to IS NULL)
            OR
                (IFNULL(bind_time_from,0) <= IFNULL(bind_time_to,86400) AND TIME_TO_SEC(DATE_FORMAT(NOW(),'%H:%i:00')) BETWEEN IFNULL(bind_time_from,0) AND IFNULL(bind_time_to,86400))
            OR
                (bind_time_from > bind_time_to AND TIME_TO_SEC(DATE_FORMAT(NOW(),'%H:%i:00')) NOT BETWEEN bind_time_to AND bind_time_from)
            )
            AND (
                (bind_weekday_from IS NULL AND bind_weekday_to IS NULL)
            OR
                (IFNULL(bind_weekday_from,1) <= IFNULL(bind_weekday_to,7) AND WEEKDAY(NOW())+1 BETWEEN IFNULL(bind_weekday_from,1) AND IFNULL(bind_weekday_to,7))
            OR
                (bind_weekday_from > bind_weekday_to AND WEEKDAY(NOW())+1 NOT BETWEEN bind_weekday_to AND bind_weekday_from)
            )
            AND (bind_weight_from IS NULL OR bind_weight_from <= b.weight)
            AND (bind_weight_to IS NULL OR bind_weight_to >= b.weight)
            AND (bind_price_from IS NULL OR bind_price_from <= b.amount)
            AND (bind_price_to IS NULL OR bind_price_to >= b.amount)
            AND (bind_instock=0 OR bind_instock IS NULL OR (bind_instock=1 AND b.instock) OR (bind_instock=2 AND b.stockmin))
            AND (bind_laststock=0 OR (bind_laststock=1 AND b.laststock))
            AND (bind_shippingfree!=1 OR NOT b.shippingfree)
            AND dh.holidayID IS NULL
            AND (d.multishopID IS NULL OR d.multishopID=b.multishopID)
            AND (d.customergroupID IS NULL OR d.customergroupID=b.customergroupID)
            AND dk.dispatchID IS NULL
            AND d.type IN (0)
            $sql_where
            GROUP BY d.id
            ORDER BY d.position, d.name
        ";

        $dispatches = $this->db->fetchAssoc($sql);
        if (empty($dispatches)) {
            $sql = "
                SELECT
                    d.id as `key`,
                    d.id, d.name,
                    d.description,
                    d.calculation,
                    d.status_link
                FROM s_premium_dispatch d

                WHERE d.active=1
                AND d.type=1
                GROUP BY d.id

                ORDER BY d.position, d.name
                LIMIT 1
            ";
            $dispatches = $this->db->fetchAssoc($sql);
        }

        $names = array();
        foreach ($dispatches as $dispatchID => $dispatch) {
            if(in_array($dispatch['name'],$names)) unset($dispatches[$dispatchID]);
            else $names[] = $dispatch['name'];
        }
        unset($names);

        $object = $this->sGetDispatchTranslation();
        foreach ($dispatches as &$v) {
            if (!empty($object[$v['id']]['dispatch_name'])) {
                $v['name'] = $object[$v['id']]['dispatch_name'];
            }
            if (!empty($object[$v['id']]['dispatch_description'])) {
                $v['description'] = $object[$v['id']]['dispatch_description'];
            }
        }
        return $dispatches;
    }

    /**
     * Get dispatch surcharge value for current basket and shipping method
     * Used internally in sAdmin::sGetPremiumShippingcosts()
     *
     * @param $basket
     * @param $type
     * @return array|false
     */
    public function sGetPremiumDispatchSurcharge($basket, $type = 2)
    {
        if (empty($basket)) {
            return false;
        }
        $type = (int) $type;

        $sql = '
            SELECT id, bind_sql
            FROM s_premium_dispatch
            WHERE active = 1 AND type = ?
            AND bind_sql IS NOT NULL
        ';
        $statements = $this->db->fetchAssoc($sql, array($type));
        $sql_where = '';
        foreach ($statements as $dispatchID => $statement) {
            $sql_where .= "
            AND ( d.id!=$dispatchID OR ($statement))
            ";
        }
        $sql_basket = array();
        foreach ($basket as $key => $value) {
            $sql_basket[] = $this->db->quote($value)." as `$key`";
        }
        $sql_basket = implode(', ',$sql_basket);

        $sql = "
            SELECT d.id, d.calculation
            FROM s_premium_dispatch d

            JOIN ( SELECT $sql_basket ) b
            JOIN s_premium_dispatch_countries dc
            ON d.id = dc.dispatchID
            AND dc.countryID=b.countryID
            JOIN s_premium_dispatch_paymentmeans dp
            ON d.id = dp.dispatchID
            AND dp.paymentID=b.paymentID
            LEFT JOIN s_premium_holidays h
            ON h.date = CURDATE()
            LEFT JOIN s_premium_dispatch_holidays dh
            ON d.id=dh.dispatchID
            AND h.id=dh.holidayID

            LEFT JOIN (
                SELECT dc.dispatchID
                FROM s_order_basket b
                JOIN s_articles_categories_ro ac
                ON ac.articleID=b.articleID
                JOIN s_premium_dispatch_categories dc
                ON dc.categoryID=ac.categoryID
                WHERE b.modus=0
                AND b.sessionID='{$this->sSYSTEM->sSESSION_ID}'
                GROUP BY dc.dispatchID
            ) as dk
            ON dk.dispatchID=d.id

            LEFT JOIN s_user u
            ON u.id=b.userID
            AND u.active=1

            LEFT JOIN s_user_billingaddress ub
            ON ub.userID=u.id

            LEFT JOIN s_user_shippingaddress us
            ON us.userID=u.id

            WHERE d.active=1
            AND (
                (bind_time_from IS NULL AND bind_time_to IS NULL)
            OR
                (IFNULL(bind_time_from,0) <= IFNULL(bind_time_to,86400) AND TIME_TO_SEC(DATE_FORMAT(NOW(),'%H:%i:00')) BETWEEN IFNULL(bind_time_from,0) AND IFNULL(bind_time_to,86400))
            OR
                (bind_time_from > bind_time_to AND TIME_TO_SEC(DATE_FORMAT(NOW(),'%H:%i:00')) NOT BETWEEN bind_time_to AND bind_time_from)
            )
            AND (
                (bind_weekday_from IS NULL AND bind_weekday_to IS NULL)
            OR
                (IFNULL(bind_weekday_from,1) <= IFNULL(bind_weekday_to,7) AND REPLACE(WEEKDAY(NOW()),0,6)+1 BETWEEN IFNULL(bind_weekday_from,1) AND IFNULL(bind_weekday_to,7))
            OR
                (bind_weekday_from > bind_weekday_to AND REPLACE(WEEKDAY(NOW()),0,6)+1 NOT BETWEEN bind_weekday_to AND bind_weekday_from)
            )
            AND (bind_weight_from IS NULL OR bind_weight_from <= b.weight)
            AND (bind_weight_to IS NULL OR bind_weight_to >= b.weight)
            AND (bind_price_from IS NULL OR bind_price_from <= b.amount)
            AND (bind_price_to IS NULL OR bind_price_to >= b.amount)
            AND (bind_instock=0 OR bind_instock IS NULL OR (bind_instock=1 AND b.instock) OR (bind_instock=2 AND b.stockmin))
            AND (bind_laststock=0 OR (bind_laststock=1 AND b.laststock))
            AND (bind_shippingfree=2 OR NOT b.shippingfree)
            AND dh.holidayID IS NULL
            AND (d.multishopID IS NULL OR d.multishopID=b.multishopID)
            AND (d.customergroupID IS NULL OR d.customergroupID=b.customergroupID)
            AND dk.dispatchID IS NULL
            AND d.type = $type
            AND (d.shippingfree IS NULL OR d.shippingfree > b.amount)
            $sql_where
            GROUP BY d.id
        ";
        $dispatches = $this->db->fetchAll($sql);
        $surcharge = 0;
        if (!empty($dispatches)) {
            foreach ($dispatches as $dispatch) {
                if(empty($dispatch['calculation'])) {
                    $from = round($basket['weight'],3);
                } elseif ($dispatch['calculation']==1) {
                    if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"])
                        || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
                    ) {
                        $from = round($basket['amount_net'], 2);
                    } else {
                        $from = round($basket['amount'], 2);
                    }
                } elseif($dispatch['calculation'] == 2) {
                    $from = round($basket['count_article']);
                } elseif ($dispatch['calculation'] == 3) {
                    $from = round($basket['calculation_value_'.$dispatch['id']]);
                } else {
                    continue;
                }
                $sql = "
                    SELECT `value` , `factor`
                    FROM `s_premium_shippingcosts`
                    WHERE `from` <= $from
                    AND `dispatchID` = {$dispatch['id']}
                    ORDER BY `from` DESC
                    LIMIT 1
                ";
                $result = $this->db->fetchRow($sql);

                if($result === false) {
                    continue;
                }
                $surcharge += $result['value'];
                if (!empty($result['factor'])) {
                    //die($result["factor"].">".$from);
                    $surcharge +=  $result['factor']/100*$from;
                }
            }
        }
        return $surcharge;
    }

    /**
     * Get shipping costs
     * Used in sBasket and Checkout controller
     *
     * @param array $country Array with a single country details
     * @return array|false Array with shipping costs data, or false on failure
     */
    public function sGetPremiumShippingcosts($country = null)
    {
        $currencyFactor = empty($this->sSYSTEM->sCurrency['factor']) ? 1 : $this->sSYSTEM->sCurrency['factor'];

        $discount_tax = empty($this->sSYSTEM->sCONFIG['sDISCOUNTTAX']) ? 0 : (float) str_replace(',','.',$this->sSYSTEM->sCONFIG['sDISCOUNTTAX']);

        // Determinate tax automatically
        if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])) {
            $discount_tax = $this->sSYSTEM->sMODULES['sBasket']->getMaxTax();
        }

        $surcharge_ordernumber = isset($this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGEABSOLUTENUMBER']) ? $this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGEABSOLUTENUMBER'] : 'PAYMENTSURCHARGEABSOLUTENUMBER';
        $surcharge_name = isset($this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEABSOLUTE"]) ? $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEABSOLUTE"] : 'Zuschlag für Zahlungsart';
        $discount_ordernumber = isset($this->sSYSTEM->sCONFIG['sSHIPPINGDISCOUNTNUMBER']) ? $this->sSYSTEM->sCONFIG['sSHIPPINGDISCOUNTNUMBER'] : 'SHIPPINGDISCOUNT';
        $discount_name = isset($this->sSYSTEM->sCONFIG["sSHIPPINGDISCOUNTNAME"]) ? $this->sSYSTEM->sCONFIG["sSHIPPINGDISCOUNTNAME"] : 'Warenkorbrabatt';
        $percent_ordernumber = isset($this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']) ? $this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']: "PAYMENTSURCHARGE";
        $discount_basket_ordernumber = isset($this->sSYSTEM->sCONFIG['sDISCOUNTNUMBER']) ? $this->sSYSTEM->sCONFIG['sDISCOUNTNUMBER']: 'DISCOUNT';
        $discount_basket_name = isset($this->sSYSTEM->sCONFIG['sDISCOUNTNAME']) ? $this->sSYSTEM->sCONFIG['sDISCOUNTNAME']: 'Warenkorbrabatt';

        $sql = 'DELETE FROM s_order_basket WHERE sessionID=? AND modus IN (3, 4) AND ordernumber IN (?, ?, ?, ?)';
        $this->db->query($sql, array(
            $this->sSYSTEM->sSESSION_ID,
            $surcharge_ordernumber,
            $discount_ordernumber,
            $percent_ordernumber,
            $discount_basket_ordernumber
        ));

        $basket = $this->sGetDispatchBasket(empty($country['id']) ? null : $country['id']);
        if(empty($basket)) {
            return false;
        }
        $country = $this->sGetCountry($basket['countryID']);
        if(empty($country)) {
            return false;
        }
        $payment = $this->sGetPaymentmean($basket['paymentID']);
        if(empty($payment)) {
            return false;
        }

        $sql = '
            SELECT SUM((CAST(price as DECIMAL(10,2))*quantity)/currencyFactor) as amount
            FROM s_order_basket
            WHERE sessionID=?
            GROUP BY sessionID
        ';
        $amount = $this->db->fetchOne($sql, array($this->sSYSTEM->sSESSION_ID));

        $sql = '
            SELECT basketdiscount
            FROM s_core_customergroups_discounts
            WHERE groupID=?
            AND basketdiscountstart<=?
            ORDER BY basketdiscountstart DESC
        ';
        $basket_discount = $this->db->fetchOne(
            $sql,
            array($this->sSYSTEM->sUSERGROUPDATA['id'], $amount)
        );

        if (!empty($basket_discount)) {

            $percent = $basket_discount;
            $basket_discount = round($basket_discount/100*($amount*$currencyFactor), 2);

            if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $basket_discount_net = $basket_discount;
            } else {
                $basket_discount_net = round($basket_discount/(100+$discount_tax)*100,2);
            }
            $tax_rate = $discount_tax;
            $basket_discount_net = $basket_discount_net *-1;
            $basket_discount = $basket_discount *-1;

            $sql = '
                INSERT INTO s_order_basket
                    (sessionID, articlename, articleID, ordernumber,
                    quantity, price, netprice, tax_rate, datum, modus, currencyFactor)
                VALUES
                    (?, ?, 0, ?, 1, ?, ?, ?, NOW(), 3, ?)
            ';
            $this->db->query(
                $sql,
                array(
                    $this->sSYSTEM->sSESSION_ID,
                    '- '.$percent.' % '.$discount_basket_name,
                    $discount_basket_ordernumber,
                    $basket_discount,
                    $basket_discount_net,
                    $tax_rate,
                    $currencyFactor
                )
            );
        }

        $discount = $this->sGetPremiumDispatchSurcharge($basket, 3);

        if (!empty($discount)) {
            $discount *= -$currencyFactor;

            if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $discount_net = $discount;
                //$tax_rate = 0;
            } else {
                $discount_net = round($discount/(100+$discount_tax)*100,2);
                //$tax_rate = $discount_tax;
            }
            $tax_rate = $discount_tax;
            $sql = '
                INSERT INTO s_order_basket
                    (sessionID, articlename, articleID, ordernumber,
                    quantity, price, netprice,tax_rate, datum, modus, currencyFactor)
                VALUES
                    (?, ?, 0, ?, 1, ?, ?, ?, NOW(), 4, ?)
            ';

            $this->db->query(
                $sql,
                array(
                    $this->sSYSTEM->sSESSION_ID,
                    $discount_name,
                    $discount_ordernumber,
                    $discount,
                    $discount_net,
                    $tax_rate,
                    $currencyFactor
                )
            );
        }

        $dispatch = $this->sGetPremiumDispatch((int) $this->sSYSTEM->_SESSION['sDispatch']);

        if (!empty($payment['country_surcharge'][$country['countryiso']])) {
            $payment['surcharge'] += $payment['country_surcharge'][$country['countryiso']];
        }
        $payment['surcharge'] = round($payment['surcharge']*$currencyFactor,2);

        if (!empty($payment['surcharge']) && (empty($dispatch) || $dispatch['surcharge_calculation'] == 3)) {
            $surcharge = round($payment['surcharge'], 2);
            $payment['surcharge'] = 0;
            if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $surcharge_net = $surcharge;
                //$tax_rate = 0;
            } else {
                $surcharge_net = round($surcharge/(100+$discount_tax)*100,2);
            }

            $tax_rate = $discount_tax;
            $sql = '
                INSERT INTO s_order_basket
                    (sessionID, articlename, articleID, ordernumber, quantity,
                    price, netprice, tax_rate, datum, modus, currencyFactor)
                VALUES
                    (?, ?, 0, ?, 1, ?, ?, ?,NOW(), 4, ?)
            ';
            $this->db->query(
                $sql,
                array(
                    $this->sSYSTEM->sSESSION_ID,
                    $surcharge_name,
                    $surcharge_ordernumber,
                    $surcharge,
                    $surcharge_net,
                    $tax_rate,
                    $currencyFactor
                )
            );
        }
        if (!empty($payment['debit_percent']) && (empty($dispatch) || $dispatch['surcharge_calculation']!=2)) {
            $sql = 'SELECT SUM(quantity*price) as amount FROM s_order_basket WHERE sessionID=? GROUP BY sessionID';
            $amount = $this->db->fetchOne(
                $sql,
                array($this->sSYSTEM->sSESSION_ID)
            );

            $percent = round($amount / 100 * $payment['debit_percent'], 2);

            if ($percent>0) {
                $percent_name = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEADD"];
            } else {
                $percent_name = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEDEV"];
            }

            if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $percent_net = $percent;
                //$tax_rate = 0;
            } else {
                $percent_net = round($percent/(100+$discount_tax)*100,2);

            }
            $tax_rate = $discount_tax;
            $sql = '
                INSERT INTO s_order_basket
                    (sessionID, articlename, articleID, ordernumber, quantity,
                    price, netprice, tax_rate, datum, modus, currencyFactor)
                VALUES
                    (?, ?, 0, ?, 1, ?, ?, ?, NOW(), 4, ?)
            ';
            $this->db->query(
                $sql,
                array(
                    $this->sSYSTEM->sSESSION_ID,
                    $percent_name,
                    $percent_ordernumber,
                    $percent,
                    $percent_net,
                    $tax_rate,
                    $currencyFactor
                )
            );
        }

        if (empty($dispatch)) {
            return array('brutto'=>0, 'netto'=>0);
        }

        if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])) {
            $dispatch['shippingfree'] = round($dispatch['shippingfree']/(100+$discount_tax)*100,2);
        } else {
            $dispatch['shippingfree'] = $dispatch['shippingfree'];
        }

        if ((!empty($dispatch['shippingfree']) && $dispatch['shippingfree'] <= $basket['amount_display'])
            || empty($basket['count_article'])
            || (!empty($basket['shippingfree']) && empty($dispatch['bind_shippingfree']))
        ) {
            if (empty($dispatch['surcharge_calculation']) && !empty($payment['surcharge']))
                return array(
                    'brutto' => $payment['surcharge'],
                    'netto' => round($payment['surcharge']*100/(100+$this->sSYSTEM->sCONFIG['sTAXSHIPPING']),2)
                );
            else {
                return array('brutto' => 0, 'netto' => 0);
            }
        }

        if (empty($dispatch['calculation'])) {
            $from = round($basket['weight'],3);
        } elseif ($dispatch['calculation']==1) {
            $from = round($basket['amount'],2);
        } elseif ($dispatch['calculation']==2) {
            $from = round($basket['count_article']);
        } elseif ($dispatch['calculation']==3) {
            $from = round($basket['calculation_value_'.$dispatch['id']],2);
        } else {
            return false;
        }
        $sql = "
            SELECT `value` , `factor`
            FROM `s_premium_shippingcosts`
            WHERE `from` <= $from
            AND `dispatchID` = {$dispatch['id']}
            ORDER BY `from` DESC
            LIMIT 1
        ";
        $result = $this->db->fetchRow($sql);
        if ($result === false) {
            return false;
        }

        if (!empty($dispatch['shippingfree'])) {
            $result['shippingfree'] = round($dispatch['shippingfree']*$currencyFactor,2);
            $difference = round(($dispatch['shippingfree']-$basket['amount_display'])*$currencyFactor,2);
            $result['difference'] = array(
                "float" => $difference,
                "formated" => $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($difference)
            );
        }
        $result['brutto'] = $result['value'];
        if (!empty($result['factor'])) {
            $result['brutto'] +=  $result['factor']/100*$from;
        }
        $result['surcharge'] = $this->sGetPremiumDispatchSurcharge($basket);
        if (!empty($result['surcharge'])) {
            $result['brutto'] +=  $result['surcharge'];
        }
        $result['brutto'] *= $currencyFactor;
        $result['brutto'] = round($result['brutto'],2);
        if (!empty($payment['surcharge'])
            && $dispatch['surcharge_calculation'] != 2
            && (empty($basket['shippingfree']) || empty($dispatch['surcharge_calculation']))
        ) {
            $result['surcharge'] = $payment['surcharge'];
            $result['brutto'] += $result['surcharge'];
        }
        if ($result['brutto'] < 0) {
            return array('brutto' => 0, 'netto' => 0);
        }
        if(empty($dispatch['tax_calculation'])) {
            $result['tax'] = $basket['max_tax'];
        } else {
            $result['tax'] = $dispatch['tax_calculation_value'];
        }
        $result['tax'] = (float) $result['tax'];
        $result['netto'] = round($result['brutto']*100/(100+$result['tax']),2);

        return $result;
    }
}
