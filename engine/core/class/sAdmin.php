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
     * Shopware session object.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * Request wrapper object
     *
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * Shopware password encoder.
     * Injected over the class constructor
     *
     * @var \Shopware\Components\Password\Manager
     */
    private $passwordEncoder;

    /**
     * sBasket core class instance
     *
     * @var sBasket
     */
    private $basketModule;

    /**
     * sArticle core class instance
     *
     * @var sArticle
     */
    private $articleModule;

    /**
     * sSystem core class instance (deprecated)
     *
     * @var sSystem
     */
    private $systemModule;

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

    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db                 = null,
        Shopware_Components_Config              $config             = null,
        Enlight_Components_Session_Namespace    $session            = null,
        Enlight_Controller_Front                $front              = null,
        \Shopware\Components\Password\Manager   $passwordEncoder    = null,
        sBasket                                 $basketModule       = null,
        sArticle                                $articleModule      = null,
        sSystem                                 $systemModule       = null
    )
    {
        $this->db = $db ? : Shopware()->Db();
        $this->config = $config ? : Shopware()->Config();
        $this->session = $session ? : Shopware()->Session();
        $this->front = $front ? : Shopware()->Front();
        $this->passwordEncoder = $passwordEncoder ? : Shopware()->PasswordEncoder();
        $this->basketModule = $basketModule ? : Shopware()->Modules()->Basket();
        $this->articleModule = $articleModule ? : Shopware()->Modules()->Articles();
        $this->systemModule = $systemModule ? : Shopware()->System();

        $this->snippetObject = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages');
        $shop = Shopware()->Shop()->getMain() !== null ? Shopware()->Shop()->getMain() : Shopware()->Shop();
        $this->scopedRegistration = $shop->getCustomerScope();
        $this->subshopId = $shop->getId();
    }

    /**
     * Checks vat id with webservice
     *
     * @deprecated See PT-1829
     * @return array Associative array with success / error codes
     */
    public function sValidateVat()
    {
        $vatCheckEnabled = $this->config->get('sVATCHECKENDABLED');
        if (empty($vatCheckEnabled)) {
            return array();
        }
        $vatCheckRequired = $this->config->get('sVATCHECKREQUIRED');
        $postUstId = $this->front->Request()->getPost("ustid");
        if (empty($postUstId) && empty($vatCheckRequired)) {
            return array();
        }

        $messages = array();
        $ustId = preg_replace('#[^0-9A-Z\+\*\.]#', '', strtoupper($postUstId));

        $vatCheckAdvancedNumber = $this->config->get('sVATCHECKADVANCEDNUMBER');

        $country = $this->db->fetchOne(
            'SELECT countryiso FROM s_core_countries WHERE id=?',
            array($this->front->Request()->getPost('country'))
        );

        $vat = null;
        $matchResult = preg_match("#^([A-Z]{2})([0-9A-Z+*.]{2,12})$#", $ustId, $vat);

        if (empty($postUstId)) {
            $messages[] = $this->snippetObject->get('VatFailureEmpty', 'Please enter a vat id');
        } elseif (empty($ustId) || !$matchResult) {
            $messages[] = $this->snippetObject->get('VatFailureInvalid', 'The vat id entered is invalid');
        } elseif (empty($country) || $country != $vat[1]) {
            $field_names = explode(',', $this->snippetObject->get(
                'VatFailureErrorFields',
                'Company,City,Zip,Street,Country'
            ));
            $field_name = isset($field_names[4]) ? $field_names[4] : 'Land';
            $messages[] = sprintf($this->snippetObject->get(
                'VatFailureErrorField',
                'The field %s does not match to the vat id entered'
            ), $field_name);
        } elseif ($country == 'DE') {

        } elseif (!empty($vatCheckAdvancedNumber)) {
            $messages = $this->checkAdvancedVatNumber($vat);

            /**
             * This portion of code is dead, but should be useful in the future
             * It validates Vat Id on an european service.
             */
//        } elseif (false && class_exists('SoapClient')) {
//            $url = 'http://ec.europa.eu/taxation_customs/vies/services/checkVatService.wsdl';
//            if (!file_get_contents($url)) {
//                $messages[] = sprintf($this->snippetObject->get('VatFailureUnknownError', 'An unknown error occurs while checking your vat id. Error code %d'), 11);
//            } else {
//                $client = new SoapClient($url, array('exceptions' => 0, 'connection_timeout' => 5));
//                $response = $client->checkVat(array('countryCode' => $vat[1], 'vatNumber' => $vat[2]));
//            }
//            if (is_soap_fault($response)) {
//                $messages[] = sprintf($this->snippetObject->get(
//                    'VatFailureUnknownError',
//                    'An unknown error occurs while checking your vat id. Error code %d'
//                ), 12);
//                $vatCheckDebug = $this->config->get('sVATCHECKDEBUG');
//                if (!empty($vatCheckDebug)) {
//                    $messages[] = "SOAP-error: (errorcode: {$response->faultcode}, errormsg: {$response->faultstring})";
//                }
//            } elseif (empty($response->valid)) {
//                $messages[] = $this->snippetObject->get('VatFailureInvalid', 'The vat id entered is invalid');
//            }
        } else {
            $messages[] = sprintf($this->snippetObject->get(
                'VatFailureUnknownError',
                'An unknown error occurs while checking your vat id. Error code %d'
            ), 20);
        }

        $vatCheckRequired = $this->config->get('sVATCHECKREQUIRED');
        if (!empty($messages) && empty($vatCheckRequired)) {
            $messages[] = $this->snippetObject->get('VatFailureErrorInfo', '');
        }
        $messages = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_CheckTaxID_MessagesFilter',
            $messages,
            array('subject' => $this, "post" => $this->front->Request()->getPost())
        );
        return $messages;
    }

    /**
     * Uses a german web service to check vat id
     * Helper for sAdmin::sValidateVat
     * Include in PT-1829
     *
     * @param $vat array Vat Id, separated in 2 letter prefix + number
     * @return array Resulting messages
     */
    private function checkAdvancedVatNumber($vat)
    {
        $messages = array();
        $vatCheckAdvancedNumber = $this->config->get('sVATCHECKADVANCEDNUMBER');
        $vatCheckConfirmation = $this->config->get('sVATCHECKCONFIRMATION');
        $vatCheckAdvanced = $this->config->get('sVATCHECKADVANCED');
        $vatCheckAdvancedCountries = $this->config->get('sVATCHECKADVANCEDCOUNTRIES');
        $vatCheckNoService = $this->config->get('sVATCHECKNOSERVICE');

        $data = array(
            'UstId_1' => $vatCheckAdvancedNumber,
            'UstId_2' => $vat[1] . $vat[2],
            'Firmenname' => '',
            'Ort' => '',
            'PLZ' => '',
            'Strasse' => '',
            'Druck' => empty($vatCheckConfirmation) ? 'nein' : 'ja'
        );

        if (!empty($vatCheckAdvanced)
            && strpos($vatCheckAdvancedCountries, $vat[1]) !== false
        ) {
            $data['Firmenname'] = $this->front->Request()->getPost('company');
            $data['Ort'] = $this->front->Request()->getPost('city');
            $data['PLZ'] = $this->front->Request()->getPost('zipcode');
            $data['Strasse'] = $this->front->Request()->getPost('street') . ' ' . $this->front->Request()->getPost('streetnumber');
        }

        $apiRequest = 'http://evatr.bff-online.de/evatrRPC?';
        $apiRequest .= http_build_query($data, '', '&');

        $context = stream_context_create(array('http' => array(
            'method' => 'GET',
            'header' => 'Content-Type: text/html; charset=utf-8',
            'timeout' => 5,
            'user_agent' => 'Shopware/' . $this->config->get('sVERSION')
        )));
        $response = @file_get_contents($apiRequest, false, $context);

        $reg = '#<param>\s*<value><array><data>\s*<value><string>([^<]*)</string></value>\s*<value><string>([^<]*)</string></value>\s*</data></array></value>\s*</param>#msi';
        if (!empty($response) && preg_match_all($reg, $response, $matches)) {
            $response = array_combine($matches[1], $matches[2]);
            $messages = $this->sCheckVatResponse($response);
        } elseif (empty($vatCheckNoService)) {
            $messages[] = sprintf($this->snippetObject->get(
                'VatFailureUnknownError',
                'An unknown error occurs while checking your vat id. Error code %d'
            ), 10);
        }
        return $messages;
    }

    /**
     * Handles the response from the german VAT id validation
     * Helper for sAdmin::sValidateVat()
     * Include in PT-1829
     *
     * @param array $response The response from the validation webservice
     * @return array List of errors found by the remote service
     */
    private function sCheckVatResponse($response)
    {
        $vatCheckNoService = $this->config->get('sVATCHECKNOSERVICE');
        if (!empty($vatCheckNoService)) {
            if (in_array($response['ErrorCode'], array(999, 205, 218, 208, 217, 219))) {
                return array();
            }
        }
        $vatCheckDebug = $this->config->get('sVATCHECKDEBUG');
        if (!empty($vatCheckDebug)) {
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
            $field_names = explode(',', $this->snippetObject->get(
                'VatFailureErrorFields',
                'Company,City,Zip,Street,Country'
            ));
            foreach ($fields as $key => $field) {
                if (isset($response[$field])
                    && strpos($this->config->get('sVATCHECKVALIDRESPONSE'), $response[$field]) === false
                ) {
                    $name = isset($field_names[$key]) ? $field_names[$key] : $field;
                    $result[] = sprintf($this->snippetObject->get(
                        'VatFailureErrorField',
                        'The field %s does not match to the vat id entered'), $name
                    );
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

        $data = $this->db->fetchRow(
            'SELECT * FROM s_core_paymentmeans WHERE id = ?',
            array($id)
        ) ? : array();

        if ($this->basketModule->sCheckForESD()) {
            $sEsd = true;
        }

        if (!count($user)) {
            $user = array();
        }

        $basket = $this->basketModule->sBASKET;

        // Check for risk management
        // If rules match, reset to default payment mean if this payment mean was not
        // set by shop owner

        // Hide payment means which are not active
        if (!$data["active"] && $data["id"] != $user["additional"]["user"]["paymentpreset"]) {
            $resetPayment = $this->config->get('sPAYMENTDEFAULT');
        }

        // If esd - order, hide payment means which
        // are not available for esd
        if (!$data["esdactive"] && $sEsd) {
            $resetPayment = $this->config->get('sPAYMENTDEFAULT');
        }

        // Check additional rules
        if ($this->sManageRisks($data["id"], $basket, $user)
            && $data["id"] != $user["additional"]["user"]["paymentpreset"]
        ) {
            $resetPayment = $this->config->get('sPAYMENTDEFAULT');
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
                $this->systemModule->sSubShop['id'],
                $user['additional']['countryShipping']['id'],
                $id
            ));
            if (empty($active)) {
                $resetPayment = $this->config->get('sPAYMENTDEFAULT');
            }
        }

        if ($resetPayment && $user["additional"]["user"]["id"]) {
            $this->db->update(
                's_user',
                array('paymentID' => $resetPayment),
                array('id = ?' => $user["additional"]["user"]["id"])
            );
            $data = $this->db->fetchRow(
                'SELECT * FROM s_core_paymentmeans WHERE id = ?',
                array($resetPayment)
            ) ? : array();
        }

        // Get translation
        $data = $this->sGetPaymentTranslation($data);

        $data = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_GetPaymentMeanById_DataFilter',
            $data,
            array('subject' => $this, "id" => $id, "user" => $user)
        );

        return $data;
    }

    /**
     * Get all available payments
     *
     * @return array Payments data
     */
    public function sGetPaymentMeans()
    {
        $basket = $this->basketModule->sBASKET;

        $user = $this->sGetUserData();

        $sEsd = $this->basketModule->sCheckForESD();

        $countryID = (int) $user['additional']['countryShipping']['id'];
        $subShopID = (int) $this->systemModule->sSubShop['id'];
        if (empty($countryID)) {
            $countryID = $this->db->fetchOne("
            SELECT id FROM s_core_countries ORDER BY position ASC LIMIT 1
            ");
        }
        $sql = "
            SELECT p.*
            FROM s_core_paymentmeans p

            LEFT JOIN s_core_paymentmeans_subshops ps
            ON ps.subshopID = $subShopID
            AND ps.paymentID = p.id

            LEFT JOIN s_core_paymentmeans_countries pc
            ON pc.countryID = $countryID
            AND pc.paymentID=p.id

            WHERE (ps.paymentID IS NOT NULL OR (SELECT paymentID FROM s_core_paymentmeans_subshops WHERE paymentID=p.id LIMIT 1) IS NULL)
            AND (pc.paymentID IS NOT NULL OR (SELECT paymentID FROM s_core_paymentmeans_countries WHERE paymentID=p.id LIMIT 1) IS NULL)

            ORDER BY position, name
        ";

        $getPaymentMeans = $this->db->fetchAll($sql);

        if ($getPaymentMeans === false) {
            $getPaymentMeans = $this->db->fetchAll('SELECT * FROM s_core_paymentmeans ORDER BY position, name');
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
            $fallBackPayment = $this->db->fetchRow(
                'SELECT * FROM s_core_paymentmeans WHERE id =?',
                array($this->config->offsetGet('paymentdefault'))
            );
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
     * @throws Enlight_Exception If no payment classes were loaded
     * @return ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod
     *      The payment mean handling class instance
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
            throw new Enlight_Exception("sValidateStep3 #02: Payment classes dir not loaded");
        }

        $sPaymentObject = new $class();

        if (!$sPaymentObject) {
            throw new Enlight_Exception("sValidateStep3 #02: Payment class not found");
        } else {
            return $sPaymentObject;
        }
    }

    /**
     * Last step of the registration - validate all user fields that exists in session and
     * stores the data into database
     *
     * @throws Enlight_Exception If no payment mean is set in POST
     * @return array Payment data
     */
    public function sValidateStep3()
    {
        $paymentId = $this->front->Request()->getPost('sPayment');
        if (empty($paymentId)) {
            throw new Enlight_Exception("sValidateStep3 #00: No payment id");
        }

        $user = $this->sGetUserData();
        $paymentData = $this->sGetPaymentMeanById($paymentId, $user);

        if (!count($paymentData)) {
            throw new Enlight_Exception("sValidateStep3 #01: Could not load paymentmean");
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
     * @throws Enlight_Exception On database error
     * @return boolean If operation was successful
     */
    public function sUpdateBilling()
    {
        $postData = $this->front->Request()->getPost();

        // Convert multiple birthday fields into a single value
        if (!empty($postData['birthmonth']) && !empty($postData['birthday']) && !empty($postData['birthyear'])) {
            $postData['birthday'] = mktime(
                0,0,0,
                (int) $postData['birthmonth'],
                (int) $postData['birthday'],
                (int) $postData['birthyear']
            );
            if ($postData['birthday'] > 0) {
                $postData['birthday'] = date('Y-m-d', $postData['birthday']);
            } else {
                $postData['birthday'] = '0000-00-00';
            }
        } else {
            unset($postData['birthday']);
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
            if (isset($postData[$field])) {
                $data[$field] = $postData[$field];
            }
        }

        $data["countryID"] = $postData["country"];

        $where = array(
            'userID='.(int) $this->session->offsetGet('sUserId')
        );

        list($data, $where) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_UpdateBilling_FilterSql',
            array($data, $where),
            array(
                'subject' => $this,
                "id" => $this->session->offsetGet('sUserId'),
                "user" => $postData
            )
        );

        $this->db->update('s_user_billingaddress', $data, $where);

        if ($this->db->getErrorMessage()) {
            throw new Enlight_Exception("sUpdateBilling #01: Could not save data (billing address)".$this->db->getErrorMessage());
        }

        //new attribute tables.
        $data = array(
            "text1" => $postData['text1'],
            "text2" => $postData['text2'],
            "text3" => $postData['text3'],
            "text4" => $postData['text4'],
            "text5" => $postData['text5'],
            "text6" => $postData['text6'],
        );

        $billingId = $this->db->fetchOne(
            'SELECT id FROM s_user_billingaddress WHERE userID = ?',
            array((int) $this->session->offsetGet('sUserId'))
        );
        $where = array(" billingID = " . $billingId);

        list($data, $where) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_UpdateBillingAttributes_FilterSql',
            array($data, $where),
            array(
                'subject' => $this,
                "id" => $this->session->offsetGet('sUserId'),
                "user" => $postData
            )
        );

        $this->db->update('s_user_billingaddress_attributes', $data, $where);
        $this->front->Request()->setPost($postData);

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
            // Delete email address from database
            $this->db->delete(
                's_campaigns_mailaddresses',
                array('email = ?' => $email)
            );
        } else {
            // Check if mail address is already subscribed, return
            if ($this->db->fetchOne(
                'SELECT id FROM s_campaigns_mailaddresses WHERE email = ?',
                array($email))
            ) {
                return false;
            }

            $optInNewsletter = $this->config->get('optinnewsletter');
            if ($optInNewsletter) {
                $hash = md5(uniqid(rand()));
                $data = serialize(array("newsletter"=>$email,"subscribeToNewsletter"=>true));

                $link = Shopware()->Front()->Router()->assemble(array(
                        'sViewport' => 'newsletter',
                        'action' => 'index',
                        'sConfirmation' => $hash
                    )
                );

                $this->sendMail($email, 'sOPTINNEWSLETTER', $link);

                $this->db->insert(
                    's_core_optin',
                    array(
                        'datum' => new Zend_Date(),
                        'hash' => $hash,
                        'data' => $data
                    )
                );
                return true;
            }

            $groupID = $this->config->get('sNEWSLETTERDEFAULTGROUP');
            if (!$groupID) {
                $groupID = "0";
            }
            // Insert email into database
            if (!empty($customer)) {
                $this->db->insert(
                    's_campaigns_mailaddresses',
                    array('customer' => 1, 'email' => $email)
                );
            } else {
                $this->db->insert(
                    's_campaigns_mailaddresses',
                    array('groupID' => $groupID, 'email' => $email)
                );
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
        $userId = $this->session->offsetGet('sUserId');
        if (empty($userId)) {
            return false;
        }

        $type = $type == 'shipping' ? 'shipping' : 'billing';

        $sql = '
            SELECT
                MD5(CONCAT(company, department, salutation, firstname, lastname, street, streetnumber, zipcode, city, countryID)) as hash,
                company, department, salutation, firstname, lastname,
                street, streetnumber, zipcode, city, countryID as country, countryID, countryname
            FROM s_order_'.$type.'address AS a
            LEFT JOIN s_core_countries co
            ON a.countryID=co.id
            WHERE a.userID = ?
            GROUP BY hash
            ORDER BY MAX(a.id) DESC
        ';

        $addresses = $this->db->fetchAll($sql, array($userId));

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
     * @throws Enlight_Exception On database error
     * @return boolean If operation was successful
     */
    public function sUpdateShipping()
    {
        $userId = (int) $this->session->offsetGet('sUserId');
        if (empty($userId)) {
            return false;
        }

        $postData = $this->front->Request()->getPost();

        $shippingID = $this->db->fetchOne(
            'SELECT id FROM s_user_shippingaddress WHERE userID = ?',
            array($userId)
        );

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
            if (isset($postData[$field])) {
                $data[$field] = $postData[$field];
            }
        }
        $data["countryID"] = isset($postData["country"]) ? $postData["country"] : 0;

        list($data) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_UpdateShipping_FilterSql',
            array($data), array(
                'subject' => $this,
                "id" => $this->session->offsetGet('sUserId'),
                "user" => $postData
            )
        );

        if (empty($shippingID)) {
            $data["userID"] = $userId;
            $this->db->insert('s_user_shippingaddress', $data);

            $shippingID = $this->db->lastInsertId('s_user_shippingaddress');
            $attributeData = array(
                'shippingID' => $shippingID,
                'text1' => $postData['text1'],
                'text2' => $postData['text2'],
                'text3' => $postData['text3'],
                'text4' => $postData['text4'],
                'text5' => $postData['text5'],
                'text6' => $postData['text6']
            );
            list($attributeData) = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_UpdateShippingAttributes_FilterSql',
                array($attributeData),
                array(
                    'subject' => $this,
                    "id" => $userId,
                    "user" => $postData
                )
            );
            $this->db->insert('s_user_shippingaddress_attributes', $attributeData);
        } else {
            $where = array('id='.(int) $shippingID);
            $this->db->update('s_user_shippingaddress', $data, $where);

            $attributeData = array(
                'text1' => $postData['text1'],
                'text2' => $postData['text2'],
                'text3' => $postData['text3'],
                'text4' => $postData['text4'],
                'text5' => $postData['text5'],
                'text6' => $postData['text6']
            );
            $where = array('shippingID='.(int) $shippingID);
            list($attributeData) = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_UpdateShippingAttributes_FilterSql',
                array($attributeData),
                array(
                    'subject' => $this,
                    "id" => $this->session->offsetGet('sUserId'),
                    "user" => $postData
                )
            );
            $this->db->update('s_user_shippingaddress_attributes', $attributeData, $where);
        }

        if ($this->db->getErrorMessage()) {
            throw new Enlight_Exception("sUpdateShipping #01: Could not save data (billing address)".$this->db->getErrorMessage());
        }
        return true;
    }

    /**
     * Updates the payment mean of the user
     * Used in the Frontend Account controller
     *
     * @throws Enlight_Exception On database error
     * @return boolean If operation was successful
     */
    public function sUpdatePayment()
    {
        $userId = $this->session->offsetGet('sUserId');
        if (empty($userId)) {
            return false;
        }
        $sqlPayment = "
        UPDATE s_user SET paymentID = ? WHERE id = ?";

        $sqlPayment = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_UpdatePayment_FilterSql',
            $sqlPayment,
            array(
                'subject' => $this,
                "id" => $userId
            )
        );

        $this->db->query(
            $sqlPayment,
            array(
                $this->front->Request()->getPost('sPayment'),
                $userId
            )
        );

        if ($this->db->getErrorMessage()) {
            throw new Enlight_Exception("sUpdatePayment #01: Could not save data (payment)".$this->db->getErrorMessage());
        }
        return true;
    }

    /**
     * Update user's email address and password
     * Used in the Frontend Account controller
     *
     * @throws Enlight_Exception On database error
     * @return boolean If operation was successful
     */
    public function sUpdateAccount()
    {
        $postData = $this->front->Request()->getPost();
        $userId = $this->session->offsetGet('sUserId');

        $email = strtolower($postData["email"]);

        $password = $postData["password"];
        $passwordConfirmation = $postData["passwordConfirmation"];

        if ($password && $passwordConfirmation) {
            $encoderName = $this->passwordEncoder->getDefaultPasswordEncoderName();
            $password = $this->passwordEncoder->encodePassword($password, $encoderName);

            $this->session->offsetSet('sUserMail', $email);
            $this->session->offsetSet('sUserPassword', $password);
            $sqlAccount = 'UPDATE s_user SET email = ?, password = ?, encoder = ? WHERE id = ?';
            $sqlAccount = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_UpdateAccount_FilterPasswordSql',
                $sqlAccount,
                array(
                    'email' => $email,
                    'password' => $password,
                    'encoder' => $encoderName,
                    'subject' => $this,
                    'id' => $userId
                )
            );

            $this->db->query(
                $sqlAccount,
                array($email, $password, $encoderName, $userId)
            );
        } else {
            $this->session->offsetSet('sUserMail', $email);
            $sqlAccount = 'UPDATE s_user SET email=? WHERE id=?';
            $sqlAccount = Enlight()->Events()->filter(
                'Shopware_Modules_Admin_UpdateAccount_FilterEmailSql',
                $sqlAccount,
                array(
                    'email' => $email,
                    'password' => $password,
                    'subject' => $this,
                    'id' => $userId
                )
            );

            $this->db->query(
                $sqlAccount,
                array($email, $userId)
            );
        }

        if ($this->db->getErrorMessage()) {
            throw new Enlight_Exception("sUpdateAccount #01: Could not save data (account)".$this->db->getErrorMessage());
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

        list($sErrorMessages, $sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_ValidateStep2_FilterStart',
            array($sErrorMessages, $sErrorFlag),
            array(
                'edit' => $edit,
                'rules' => $rules,
                'subject' => $this,
                'post' => $this->front->Request()->getPost()
            )
        );

        $postData = $this->front->Request()->getPost();

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
            // Some error occurred
            $sErrorMessages[] = $this->snippetObject->get('ErrorFillIn', 'Please fill in all red fields');
        }

        if (isset($rules['ustid'])) {
            $sVatMessages = $this->sValidateVat();
            if (!empty($sVatMessages)) {
                $sErrorFlag["ustid"] = true;
                $sErrorMessages = array_merge($sErrorMessages, $sVatMessages);
            }
        }

        if (!$edit) {
            $register = $this->session->offsetGet('sRegister');
            if (!count($sErrorMessages)) {
                foreach ($rules as $ruleKey => $ruleValue) {
                    $register['billing'][$ruleKey] = $postData[$ruleKey];
                }
            } else {
                foreach ($rules as $ruleKey => $ruleValue) {
                    unset($register["billing"][$ruleKey]);
                }
            }
            $this->session->offsetSet('sRegister', $register);
        }
        list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_ValidateStep2_FilterResult',
            array($sErrorMessages,$sErrorFlag),
            array(
                'edit' => $edit,
                'rules' => $rules,
                'subject' => $this,
                'post' => $this->front->Request()->getPost()
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
        $postData = $this->front->Request()->getPost();

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
                if (preg_match("/SHIPPING/", $ruleKey)) {
                    $clearedRuleKey = str_replace("SHIPPING", "", $ruleKey);
                    $postData[$clearedRuleKey] = $postData[$ruleKey];
                    $rules[$clearedRuleKey] = $rules[$ruleKey];
                    unset($rules[$ruleKey]);
                }
                // --
            }
        }

        if (count($sErrorFlag)) {
            // Some error occurred
            $sErrorMessages[] = $this->snippetObject->get('ErrorFillIn', 'Please fill in all red fields');
        }

        $register = $this->session->offsetGet('sRegister');
        if (!$edit) {
            if (!count($sErrorMessages)) {
                foreach ($rules as $ruleKey => $ruleValue) {
                    $registerSession["shipping"][$ruleKey] = $postData[$ruleKey];
                }
            } else {
                foreach ($rules as $ruleKey => $ruleValue) {
                    unset($register["shipping"][$ruleKey]);
                }
            }
        }
        $this->session->offsetSet('sRegister', $register);

        list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_ValidateStep2Shipping_FilterResult',
            array($sErrorMessages, $sErrorFlag),
            array(
                'edit' => $edit,
                'rules' => $rules,
                'subject' => $this,
                'post' => $this->front->Request()->getPost()
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
        $postData = $this->front->Request()->getPost();
        $encoderName =  $this->passwordEncoder->getDefaultPasswordEncoderName();

        if (isset($postData["emailConfirmation"]) || isset($postData["email"])) {
            $postData["email"] = strtolower(trim($postData["email"]));
            // Check email

            $validator = new Zend_Validate_EmailAddress();
			$validator->getHostnameValidator()->setValidateTld(false);
            
			if (empty($postData["email"]) || !$validator->isValid($postData["email"])) {
                $sErrorFlag["email"] = true;
                $sErrorMessages[] = $this->snippetObject->get('MailFailure', 'Please enter a valid mail address');
            }

            // Check email confirmation if needed
            if (isset($postData["emailConfirmation"])) {
                $postData["emailConfirmation"] = strtolower(trim($postData["emailConfirmation"]));
                if ($postData["email"] != $postData["emailConfirmation"]) {
                    $sErrorFlag["emailConfirmation"] = true;
                    $sErrorMessages[] = $this->snippetObject->get('MailFailureNotEqual', 'The mail addresses entered are not equal');
                }
            }
        } elseif ($edit && empty($postData["email"])) {
            $userEmail = $this->session->offsetGet('sUserMail');
            if ($userEmail) {
                $this->front->Request()->setPost('email', $userEmail);
            }
            $postData["email"] = $userEmail;
        }

        $register = $this->session->offsetGet('sRegister');
        if (empty($register)) {
            $this->session->offsetSet('sRegister', array());
        }

        // Check password if account should be created
        if (!$postData["skipLogin"] || $edit) {
            if ($edit && (!$postData["password"] && !$postData["passwordConfirmation"])) {

            } else {
                if (strlen(trim($postData["password"])) == 0
                    || !$postData["password"]
                    || !$postData["passwordConfirmation"]
                    || (strlen($postData["password"]) < $this->config->get('sMINPASSWORD'))
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
            $register["auth"]["accountmode"] = "0"; // Setting account mode to ACCOUNT
        } else {
            // Enforce the creation of an md5 hashed password for anonymous accounts
            $postData["password"] = md5(uniqid(rand()));
            $encoderName = 'md5';


            $register["auth"]["accountmode"] = "1";  // Setting account mode to NO_ACCOUNT
        }
        $this->session->offsetSet('sRegister', $register);

        // Check current password
        $accountPasswordCheck = $this->config->offsetGet('accountPasswordCheck');
        if ($edit && !empty($accountPasswordCheck)) {
            $password = $postData["currentPassword"];
            $current = $this->session->offsetGet('sUserPassword');
            $snippet = Shopware()->Snippets()->getNamespace("frontend");
            if (empty($password) || !$this->passwordEncoder->isPasswordValid($password, $current, $encoderName)) {
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
        if (isset($postData["email"]) && ($postData["email"] != $this->session->offsetGet('sUserMail'))) {
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
            $register = $this->session->offsetGet('sRegister');

            if (!count($sErrorFlag) && !count($sErrorMessages)) {
                $register['auth']["email"] = $postData["email"];
                // Receive Newsletter yes / no
                $register['auth']["receiveNewsletter"] = $postData["receiveNewsletter"];
                if ($postData["password"]) {
                    $register['auth']["encoderName"] = $encoderName;
                    $register['auth']["password"] = $this->passwordEncoder->encodePassword($postData["password"], $encoderName);
                } else {
                    unset($register['auth']["password"]);
                    unset($register['auth']["encoderName"]);
                }
            } else {
                unset ($register['auth']["email"]);
                unset ($register['auth']["password"]);
                unset ($register['auth']["encoderName"]);
            }

            $this->session->offsetSet('sRegister', $register);
        }

        list($sErrorMessages, $sErrorFlag) = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_ValidateStep1_FilterResult',
            array($sErrorMessages, $sErrorFlag),
            array('edit' => $edit, 'subject' => $this, "post" => $this->front->Request()->getPost())
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
                'post'              => $this->front->Request()->getPost()
            )
        )) {
            return false;
        }

        // If fields are not set, markup these fields
        $email = strtolower($this->front->Request()->getPost('email'));
        if (empty($email)) {
            $sErrorFlag['email'] = true;
        }

        // If password is already md5-decrypted or the parameter $ignoreAccountMode is set, use it directly
        if ($ignoreAccountMode && $this->front->Request()->getPost('passwordMD5')) {
            $password = $this->front->Request()->getPost('passwordMD5');
            $isPreHashed = true;
        } else {
            $password = $this->front->Request()->getPost('password');
            $isPreHashed = false;
        }

        if (empty($password)) {
            $sErrorFlag["password"] = true;
        }

        if (!empty($sErrorFlag)) {
            $sErrorMessages[] = $this->snippetObject->get('LoginFailure', 'Wrong email or password');
            $this->session->offsetUnset('sUserMail');
            $this->session->offsetUnset('sUserPassword');
            $this->session->offsetUnset('sUserId');
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

            $isValidLogin = $this->passwordEncoder->isPasswordValid($plaintext, $hash, $encoderName);
        }

        if ($isValidLogin) {
            $this->regenerateSessionId();

            $this->db->query(
                "UPDATE s_user SET lastlogin=NOW(),failedlogins = 0, lockeduntil = NULL, sessionID=? WHERE id=?",
                array($this->systemModule->sSESSION_ID, $getUser["id"])
            );

            Enlight()->Events()->notify(
                'Shopware_Modules_Admin_Login_Successful',
                array('subject' => $this, 'email' => $email, 'password' => $password, 'user' => $getUser)
            );

            $newHash = '';
            $liveMigration = $this->config->offsetGet('liveMigration');
            $defaultEncoderName = $this->passwordEncoder->getDefaultPasswordEncoderName();

            // Do not allow live migration when the password is prehashed
            if ($liveMigration && !$isPreHashed && $encoderName !== $defaultEncoderName) {
                $newHash = $this->passwordEncoder->encodePassword($plaintext, $defaultEncoderName);
                $encoderName = $defaultEncoderName;
            }

            if (empty($newHash)) {
                $newHash = $this->passwordEncoder->reencodePassword($plaintext, $hash, $encoderName);
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

            $this->session->offsetSet('sUserMail', $email);
            $this->session->offsetSet('sUserPassword', $hash);
            $this->session->offsetSet('sUserId', $getUser["id"]);

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

            $this->session->offsetUnset('sUserMail');
            $this->session->offsetUnset('sUserPassword');
            $this->session->offsetUnset('sUserId');
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

        $this->systemModule->sSESSION_ID = $newSessionId;
        $this->session->offsetSet('sessionId', $newSessionId);
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

        foreach ($sessions as $tableName => $column) {
            $this->db->update(
                $tableName,
                array($column => $newSessionId),
                $column.' = '.$this->db->quote($oldSessionId));
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

        $userId = $this->session->offsetGet('sUserId');
        $userMail = $this->session->offsetGet('sUserMail');
        $userPassword = $this->session->offsetGet('sUserPassword');

        if (empty($userMail)
            || empty($userPassword)
            || empty($userId)
        ) {
            $this->session->offsetUnset('sUserMail');
            $this->session->offsetUnset('sUserPassword');
            $this->session->offsetUnset('sUserId');

            return false;
        }

        $sql = "
            SELECT * FROM s_user
            WHERE password = ? AND email = ? AND id = ?
            AND UNIX_TIMESTAMP(lastlogin) >= (UNIX_TIMESTAMP(now())-?)
        ";

        $timeOut = $this->config->get('sUSERTIMEOUT');
        $timeOut = !empty($timeOut) ? $timeOut : 7200;

        $getUser = $this->db->fetchRow(
            $sql,
            array(
                $userPassword,
                $userMail,
                $userId,
                $timeOut
            )
        );
        $getUser = $getUser ? : array();

        $getUser = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_CheckUser_FilterGetUser',
            $getUser,
            array('subject' => $this, 'sql' => $sql, 'session' => $this->session)
        );

        if (!empty($getUser["id"])) {
            $this->systemModule->sUSERGROUPDATA = $this->db->fetchRow(
                "SELECT * FROM s_core_customergroups WHERE groupkey = ?",
                array($getUser["customergroup"])
            );
            $this->systemModule->sUSERGROUPDATA = $this->systemModule->sUSERGROUPDATA ? : array();

            if ($this->systemModule->sUSERGROUPDATA["mode"]) {
                $this->systemModule->sUSERGROUP = "EK";
            } else {
                $this->systemModule->sUSERGROUP = $getUser["customergroup"];
            }
            $this->systemModule->sUSERGROUP = $getUser["customergroup"];

            $this->session->offsetSet('sUserGroup', $this->systemModule->sUSERGROUP);
            $this->session->offsetSet('sUserGroupData', $this->systemModule->sUSERGROUPDATA);

            $this->db->query(
                "UPDATE s_user SET lastlogin=NOW(), sessionID = ? WHERE id = ?",
                array($this->systemModule->sSESSION_ID, $getUser["id"])
            );
            Enlight()->Events()->notify(
                'Shopware_Modules_Admin_CheckUser_Successful',
                array('subject' => $this, 'session' => $this->session, 'user' => $getUser)
            );

            return true;
        } else {
            $this->session->offsetUnset('sUserMail');
            $this->session->offsetUnset('sUserPassword');
            $this->session->offsetUnset('sUserId');
            Enlight()->Events()->notify(
                'Shopware_Modules_Admin_CheckUser_Failure',
                array('subject' => $this, 'session' => $this->session, 'user' => $getUser)
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

        $param = array($this->systemModule->sLanguageData[$this->systemModule->sLanguage]["isocode"]);
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
        $params = array($this->systemModule->sLanguageData[$this->systemModule->sLanguage]["isocode"]);
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
        $params = array($this->systemModule->sLanguageData[$this->systemModule->sLanguage]["isocode"]);

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

            if ($countryList[$key]["id"] == $this->front->Request()->getPost('country')
                || $countryList[$key]["id"] == $this->front->Request()->getPost('countryID')
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
        $sMerchant = $userObject["billing"]["sValidation"] ? : "";

        $defaultCustomerGroup = $this->config->get('sDefaultCustomerGroup');
        if (empty($defaultCustomerGroup)) {
            $this->config->set('sDefaultCustomerGroup', 'EK');
        }
        $referer = $this->session->offsetGet('sReferer');

        $partnerId = $this->session->offsetGet('sPartner');
        if (!empty($partnerId)) {
            $sql = 'SELECT id FROM s_emarketing_partner WHERE idcode = ?';
            $partner = (int) $this->db->fetchOne(
                $sql,
                array($partnerId)
            );
        }

        $data = array(
            $userObject["auth"]["password"],
            $userObject["auth"]["email"],
            $userObject["payment"]["object"]["id"],
            $userObject["auth"]["accountmode"],
            empty($sMerchant) ? "" : $sMerchant,
            $this->systemModule->sSESSION_ID,
            empty($partner) ? "" : $partner,
            $this->config->get('sDefaultCustomerGroup'),
            $this->systemModule->sLanguageData[$this->systemModule->sLanguage]["isocode"],
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
            'sShop'     => $this->config->get('ShopName'),
            'sShopURL'  => 'http://' . $this->config->get('BasePath'),
            'sConfig'   => $this->config,
        );


        $namespace = Shopware()->Snippets()->getNamespace('frontend/account/index');
        $register = $this->session->offsetGet('sRegister');
        foreach ($register["billing"] as $key => $value) {
            if ($key == "salutation") {
                $value = ($value == "ms") ? $namespace->get('AccountSalutationMrs', 'Ms') : $namespace->get('AccountSalutationMr', 'Mr');
            }

            $context[$key] = $value;
        }

        $mail = Shopware()->TemplateMail()->createMail('sREGISTERCONFIRMATION', $context);
        $mail->addTo($email);

        $sendConfirmationEmail = $this->config->get('sSEND_CONFIRM_MAIL');
        if (!empty($sendConfirmationEmail)) {
            $mail->addBcc($this->config->get('sMAIL'));
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
     * @throws Enlight_Exception On database errors
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
        if (!$this->session->offsetGet('sRegisterFinished')) {
            $register = $this->session->offsetGet('sRegister');
            if (empty($register["payment"]["object"]["id"])) {
                $register["payment"]["object"]["id"] = $this->config->get('sDEFAULTPAYMENT');
                $this->session->offsetSet('sRegister', $register);
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

                        if (empty($register[$sectionKey][$fieldKey][$objKey])) {
                            $errorFields[] = $sectionKey."#1($sectionKey)($fieldKey)($objKey)->".$fieldValue;
                        }
                    } else {
                        if (empty($register[$sectionKey][$fieldValue])) {
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
                throw new Enlight_Exception("sSaveRegister #00: Fields are missing $noCookies - ".$this->systemModule->sSESSION_ID." - ".print_r($errorFields,true));
            } else {
                $userObject = $register;

                if (!$userObject["payment"]["object"]["id"]) {
                    $userObject["payment"]["object"]["id"] = $this->config->get('sPAYMENTDEFAULT');
                }

                // Save main user data
                $userID = $this->sSaveRegisterMainData($userObject);

                if ($this->db->getErrorMessage() || !$userID) {
                    throw new Enlight_Exception("sSaveRegister #01: Could not save data".$this->db->getErrorMessage().print_r($userObject));
                }

                if ($userObject["auth"]["receiveNewsletter"]) {
                    $this->sSaveRegisterNewsletter($userObject);
                }

                // Save user billing address
                $userBillingID = $this->sSaveRegisterBilling($userID,$userObject);

                if ($this->db->getErrorMessage() || !$userBillingID) {
                    throw new Enlight_Exception("sSaveRegister #02: Could not save data (billing-adress)".$this->db->getErrorMessage().print_r($userObject,true));
                }


                if ($this->config->get('sSHOPWAREMANAGEDCUSTOMERNUMBERS')) {
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
                        throw new Enlight_Exception("sSaveRegister #02: Could not save data (shipping-address)".$this->db->getErrorMessage().print_r($userObject,true));
                    }
                }

                $uMail = $userObject["auth"]["email"];
                $uPass = $userObject["auth"]["password"];

                if ($userObject["auth"]["accountmode"] < 1) {
                    $this->sSaveRegisterSendConfirmation($uMail);
                    $this->session->offsetSet('sOneTimeAccount', false);
                } else {
                    $this->session->offsetSet('sOneTimeAccount', true);
                }

                // Save referer where user comes from
                $referer = $this->session->offsetGet('sReferer');
                if (!empty($referer)) {
                    $referer = addslashes($referer);
                    $sql = "
                        INSERT INTO
                            s_emarketing_referer (userID, referer, date)
                        VALUES (
                            ?, ?, NOW()
                    );";
                    $this->db->query($sql, array($userID, $referer));
                }

                $this->front->Request()->setPost('email', $uMail);
                $this->front->Request()->setPost('passwordMD5', $uPass);

                // Login user
                $this->sLogin(true);

                // The user is now registered
                $this->session->offsetSet('sRegisterFinished', true);

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
                $this->session->offsetUnset('sRegister');
            }
        } else {
            $this->front->Request()->setPost('email', $this->session->offsetGet('sUserMail'));
            $this->front->Request()->setPost('passwordMD5', $this->session->offsetGet('sUserPassword'));
            $this->sLogin($this->session->offsetGet('sOneTimeAccount'));
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
            array($this->session->offsetGet('sUserId'))
        );

        foreach ($getOrders as $orderKey => $orderValue) {

            if (($this->config->get('sARTICLESOUTPUTNETTO') && !$this->systemModule->sUSERGROUPDATA["tax"])
                || (!$this->systemModule->sUSERGROUPDATA["tax"] && $this->systemModule->sUSERGROUPDATA["id"])
            ) {
                $getOrders[$orderKey]["invoice_amount"] = $this->articleModule->sFormatPrice($orderValue["invoice_amount_net"]);
                $getOrders[$orderKey]["invoice_shipping"] = $this->articleModule->sFormatPrice($orderValue["invoice_shipping_net"]);
            } else {
                $getOrders[$orderKey]["invoice_amount"] = $this->articleModule->sFormatPrice($orderValue["invoice_amount"]);
                $getOrders[$orderKey]["invoice_shipping"] = $this->articleModule->sFormatPrice($orderValue["invoice_shipping"]);
            }

            $getOrderDetails = $this->db->fetchAll("
              SELECT * FROM s_order_details WHERE orderID = {$orderValue["id"]}
            ");

            if (!count($getOrderDetails)) {
                unset($getOrders[$orderKey]);
            } else {
                $foundESD = false;
                foreach ($getOrderDetails as $orderDetailsKey => $orderDetailsValue) {
                    $getOrderDetails[$orderDetailsKey]["amount"] = $this->articleModule->sFormatPrice(round($orderDetailsValue["price"] * $orderDetailsValue["quantity"],2));
                    $getOrderDetails[$orderDetailsKey]["price"] = $this->articleModule->sFormatPrice($orderDetailsValue["price"]);

                    // Check for serial
                    if ($getOrderDetails[$orderDetailsKey]["esdarticle"]) {
                        $foundESD = true;
                        $numbers = array();
                        $getSerial = $this->db->fetchAll("
                        SELECT serialnumber FROM s_articles_esd_serials, s_order_esd WHERE userID=".$this->session->offsetGet('sUserId')."
                        AND orderID={$orderValue["id"]} AND orderdetailsID={$orderDetailsValue["id"]}
                        AND s_order_esd.serialID=s_articles_esd_serials.id
                        ");
                        foreach ($getSerial as $serial) {
                            $numbers[] = $serial["serialnumber"];
                        }
                        $getOrderDetails[$orderDetailsKey]["serial"] =  implode(",", $numbers);
                        // Building download link
                        $getOrderDetails[$orderDetailsKey]["esdLink"] = $this->config->get('sBASEFILE').'?sViewport=account&sAction=download&esdID='.$orderDetailsValue['id'];
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
            array('subject' => $this,'id' => $this->session->offsetGet('sUserId'))
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
            array($this->session->offsetGet('sUserId'), $mainShop->getId())
        );
        $foundOrdersCount = (int)Shopware()->Db()->fetchOne('SELECT FOUND_ROWS()');

        foreach ($getOrders as $orderKey => $orderValue) {

            $getOrders[$orderKey]["invoice_amount"] = $this->articleModule->sFormatPrice($orderValue["invoice_amount"]);
            $getOrders[$orderKey]["invoice_shipping"] = $this->articleModule->sFormatPrice($orderValue["invoice_shipping"]);


            $getOrderDetails = $this->db->fetchAll("
            SELECT * FROM s_order_details WHERE orderID={$orderValue["id"]} ORDER BY id ASC
            ");

            if (!count($getOrderDetails)) {
                unset($getOrders[$orderKey]);
            } else {
                $active = 1;

                foreach ($getOrderDetails as $orderDetailsKey => $orderDetailsValue) {
                    $getOrderDetails[$orderDetailsKey]["amount"] = $this->articleModule->sFormatPrice(round($orderDetailsValue["price"] * $orderDetailsValue["quantity"],2));
                    $getOrderDetails[$orderDetailsKey]["price"] = $this->articleModule->sFormatPrice($orderDetailsValue["price"]);

                    $tmpArticle = $this->articleModule->sGetProductByOrdernumber($getOrderDetails[$orderDetailsKey]['articleordernumber']);

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
                                $this->session->offsetGet('sUserId'),
                                $orderValue["id"],
                                $orderDetailsValue["id"]
                            )
                        );
                        foreach ($getSerial as $serial) {
                            $numbers[] = $serial["serialnumber"];
                        }
                        $getOrderDetails[$orderDetailsKey]["serial"] =  implode(",",$numbers);
                        // Building download-link
                        $getOrderDetails[$orderDetailsKey]["esdLink"] = $this->config->get('sBASEFILE').'?sViewport=account&sAction=download&esdID='.$orderDetailsValue['id'];
                        //$getOrderDetails[$orderDetailsKey]["esdLink"] = "http://".$this->config->get('sBASEPATH')."/engine/core/php/loadesd.php?id=".$orderDetailsValue["id"];
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
                'id' => $this->session->offsetGet('sUserId'),
                'subshopID' => $this->systemModule->sSubShop["id"]
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
            array($this->session->offsetGet('sUserId'))
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
        $register = $this->session->offsetGet('sRegister');
        if (empty($register)) {
            $this->session->offsetSet('sRegister', array());
        }

        $userData = array();

        $countryQuery = "
          SELECT c.*, a.`name` AS countryarea
          FROM s_core_countries c
          LEFT JOIN s_core_countries_areas a
           ON a.id = c.areaID AND a.active = 1
          WHERE c.id = ?";

        // If user is logged in
        $userId = $this->session->offsetGet('sUserId');
        if (!empty($userId)) {

            // 1.) Get billing address
            $sql = "SELECT * FROM s_user_billingaddress
                    WHERE userID = ?";

            $billing = $this->db->fetchRow(
                $sql,
                array($userId)
            );
            $billing = $billing ? : array();
            $attributes = $this->getUserBillingAddressAttributes($userId);
            $userData["billingaddress"] = array_merge($attributes, $billing);

            if (empty($userData["billingaddress"]['customernumber'])
                && $this->config->get('sSHOPWAREMANAGEDCUSTOMERNUMBERS')
            ) {
                $sql = "
                    UPDATE `s_order_number`,`s_user_billingaddress`
                    SET `s_order_number`.`number`=`s_order_number`.`number`+1,
                    `s_user_billingaddress`.`customernumber`=`s_order_number`.`number`+1
                    WHERE `s_order_number`.`name` ='user'
                    AND `s_user_billingaddress`.`userID`=?";

                $this->db->query($sql, array($userId));
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
                array($userId)
            );
            $additional = $additional ? : array();
            $attributes = $this->getUserAttributes($userId);
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
                array($userId)
            );
            $shipping = $shipping ? : array();
            $attributes = $this->getUserShippingAddressAttributes($userId);
            $userData["shippingaddress"]= array_merge($attributes, $shipping);

            // If shipping address is not available, billing address is coeval the shipping address
            $countryShipping = $this->config->get('sCOUNTRYSHIPPING');
            if (!isset($userData["shippingaddress"]["firstname"])) {
                $userData["shippingaddress"] = $userData["billingaddress"];
                $userData["shippingaddress"]["eqalBilling"] = true;
            } else {
                if (($userData["shippingaddress"]["countryID"] != $userData["billingaddress"]["countryID"])
                    && empty($countryShipping)
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
            $this->session->offsetSet('sCountry', $userData["additional"]["countryShipping"]["id"]);

            // State selection
            $userData["additional"]["stateShipping"] =  $this->db->fetchRow(
                "SELECT * FROM s_core_countries_states WHERE id=?",
                array($userData["shippingaddress"]["stateID"])
            );
            $userData["additional"]["stateShipping"] = $userData["additional"]["stateShipping"] ? : array();
            // Add stateId to session
            $this->session->offsetSet('sState', $userData["additional"]["stateShipping"]["id"]);
            // Add areaId to session
            $this->session->offsetSet('sArea', $userData["additional"]["countryShipping"]["areaID"]);
            $userData["additional"]["payment"] = $this->sGetPaymentMeanById(
                $userData["additional"]["user"]["paymentID"],
                $userData
            );
        } else {
            $register = $this->session->offsetGet('sRegister');
            if ($this->session->offsetGet('sCountry')
                && $this->session->offsetGet('sCountry') != $register["billing"]["country"]
            ) {
                $register['billing']['country'] = intval($this->session->offsetGet('sCountry'));
                $this->session->offsetSet('sRegister', $register);
            }

            $userData["additional"]["country"] = $this->db->fetchRow(
                $countryQuery,
                array(intval($register["billing"]["country"]))
            );
            $userData["additional"]["country"] = $userData["additional"]["country"] ? : array();
            $userData["additional"]["countryShipping"] = $userData["additional"]["country"];
            $state = $this->session->offsetGet('sState');
            $userData["additional"]["stateShipping"]["id"] = !empty($state) ? $state : 0;
        }

        $userData = Enlight()->Events()->filter(
            'Shopware_Modules_Admin_GetUserData_FilterResult',
            $userData,
            array('subject' => $this,'id' => $this->session->offsetGet('sUserId'))
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
            $session = $this->session;
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

        if ($this->systemModule->sCurrency["factor"]) {
            $basketValue /= $this->systemModule->sCurrency["factor"];
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

        if ($this->systemModule->sCurrency["factor"]) {
            $basketValue /= $this->systemModule->sCurrency["factor"];
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
                    array($this->systemModule->sSESSION_ID, $value[1])
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
                        $this->systemModule->sSESSION_ID,
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
        if ($this->session->offsetGet('sUserId')) {
            $checkOrder = $this->db->fetchRow("
                SELECT id FROM s_order
                WHERE cleared=16 AND userID=?",
                array($this->session->offsetGet('sUserId'))
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
        if ($this->session->offsetGet('sUserId')) {
            $value = (int) $value;
            $sql = "
            SELECT id FROM s_order WHERE userID=?
            AND TO_DAYS(ordertime) <= (TO_DAYS(now())-$value) LIMIT 1
            ";
            $checkOrder = $this->db->fetchRow(
                $sql,
                array(
                    $this->session->offsetGet('sUserId')
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
        ", array($value, $this->systemModule->sSESSION_ID));

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
        if ($this->session->offsetGet('sUserId')) {
            $checkOrder = $this->db->fetchAll(
                "SELECT id FROM s_order
                  WHERE status != -1 AND status != 4 AND userID = ?",
                array($this->session->offsetGet('sUserId'))
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
        if ($this->systemModule->sSubShop["id"]==$value) {
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
        if ($this->systemModule->sSubShop["id"]!=$value) {
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
        if (strtolower($this->systemModule->sCurrency['currency']) == strtolower($value)) {
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
        if (strtolower($this->systemModule->sCurrency['currency']) != strtolower($value)) {
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
                $fieldData = $this->front->Request()->getPost($field);
                if (isset($fieldData) && empty($fieldData)) {
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
            $groupID = $this->config->get('sNEWSLETTERDEFAULTGROUP');
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
                $this->front->Request()->getPost('salutation'),
                $this->front->Request()->getPost('title'),
                $this->front->Request()->getPost('firstname'),
                $this->front->Request()->getPost('lastname'),
                $this->front->Request()->getPost('street'),
                $this->front->Request()->getPost('streetnumber'),
                $this->front->Request()->getPost('zipcode'),
                $this->front->Request()->getPost('city')
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
        $currencyFactor = empty($this->systemModule->sCurrency["factor"]) ? 1 : $this->systemModule->sCurrency["factor"];
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
        $premiumShippingBsketSelect = $this->config->get('sPREMIUMSHIPPIUNGASKETSELECT');
        if (!empty($premiumShippingBsketSelect)) {
            $sql_select .= ', '.$premiumShippingBsketSelect;
        }
        $sql = 'SELECT id, calculation_sql FROM s_premium_dispatch WHERE active = 1 AND calculation = 3';
        $calculations = $this->db->fetchAssoc($sql);
        if(!empty($calculations)) {
            foreach ($calculations as $dispatchID => $calculation) {
                if(empty($calculation)) $calculation = $this->db->quote($calculation);
                $sql_select .= ', ('.$calculation.') as calculation_value_'.$dispatchID;
            }
        }
        if (empty($this->systemModule->sUSERGROUPDATA["tax"]) && !empty($this->systemModule->sUSERGROUPDATA["id"])) {
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
                $this->session->offsetGet('sUserId'),
                empty($this->systemModule->sSESSION_ID) ? session_id() : $this->systemModule->sSESSION_ID
            )
        );
        if ($basket === false) {
            return false;
        }

        $basket["max_tax"] = $this->basketModule->getMaxTax();
        $userId = $this->session->offsetGet('sUserId');
        $postPaymentId = $this->front->Request()->getPost('sPayment');
        $sessionPaymentId = $this->session->offsetGet('sPaymentID');

        if (!empty($paymentID)) {
            $paymentID = (int) $paymentID;
        } elseif (!empty($userId)) {
            $user = $this->sGetUserData();
            $paymentID = (int) $user['additional']['payment']['id'];
        } elseif (!empty($postPaymentId)) {
            $paymentID = (int) $postPaymentId;
        } elseif (!empty($sessionPaymentId)) {
            $paymentID = (int) $sessionPaymentId;
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
            SELECT main_id FROM s_core_shops WHERE id=".(int) $this->systemModule->sSubShop['id']."
        ";
        $mainId = $this->db->fetchOne($sql);
        // MainId is null, so we use the current shop id
        if (is_null($mainId)) {
            $mainId = (int) $this->systemModule->sSubShop['id'];
        }
        $basket['basketStateId'] = (int) $stateId;
        $basket['countryID'] = $countryID;
        $basket['paymentID'] = $paymentID;
        $basket['customergroupID'] = (int) $this->systemModule->sUSERGROUPDATA['id'];
        $basket['multishopID'] = $mainId;
        $basket['sessionID'] = $this->systemModule->sSESSION_ID;

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
                AND b.sessionID='{$this->systemModule->sSESSION_ID}'
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
                AND b.sessionID='{$this->systemModule->sSESSION_ID}'
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
                    if (($this->config->get('sARTICLESOUTPUTNETTO') && !$this->systemModule->sUSERGROUPDATA["tax"])
                        || (!$this->systemModule->sUSERGROUPDATA["tax"] && $this->systemModule->sUSERGROUPDATA["id"])
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
        $currencyFactor = empty($this->systemModule->sCurrency['factor']) ? 1 : $this->systemModule->sCurrency['factor'];

        $discount_tax = $this->config->get('sDISCOUNTTAX');
        $discount_tax = empty($discount_tax) ? 0 : (float) str_replace(',', '.', $discount_tax);

        // Determinate tax automatically
        $taxAutoMode = $this->config->get('sTAXAUTOMODE');
        if (!empty($taxAutoMode)) {
            $discount_tax = $this->basketModule->getMaxTax();
        }

        $surcharge_ordernumber = $this->config->get('sPAYMENTSURCHARGEABSOLUTENUMBER', 'PAYMENTSURCHARGEABSOLUTENUMBER');
        $surcharge_name = $this->config->get('sPAYMENTSURCHARGEABSOLUTE', 'Zuschlag für Zahlungsart');
        $discount_ordernumber = $this->config->get('sSHIPPINGDISCOUNTNUMBER', 'SHIPPINGDISCOUNT');
        $discount_name = $this->config->get('sSHIPPINGDISCOUNTNAME', 'Warenkorbrabatt');
        $percent_ordernumber = $this->config->get('sPAYMENTSURCHARGENUMBER', "PAYMENTSURCHARGE");
        $discount_basket_ordernumber = $this->config->get('sDISCOUNTNUMBER', 'DISCOUNT');
        $discount_basket_name = $this->config->get('sDISCOUNTNAME', 'Warenkorbrabatt');

        $sql = 'DELETE FROM s_order_basket WHERE sessionID=? AND modus IN (3, 4) AND ordernumber IN (?, ?, ?, ?)';
        $this->db->query($sql, array(
            $this->systemModule->sSESSION_ID,
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
        $amount = $this->db->fetchOne($sql, array($this->systemModule->sSESSION_ID));

        $sql = '
            SELECT basketdiscount
            FROM s_core_customergroups_discounts
            WHERE groupID=?
            AND basketdiscountstart<=?
            ORDER BY basketdiscountstart DESC
        ';
        $basket_discount = $this->db->fetchOne(
            $sql,
            array($this->systemModule->sUSERGROUPDATA['id'], $amount)
        );

        if (!empty($basket_discount)) {

            $percent = $basket_discount;
            $basket_discount = round($basket_discount/100*($amount*$currencyFactor), 2);

            if (empty($this->systemModule->sUSERGROUPDATA["tax"]) && !empty($this->systemModule->sUSERGROUPDATA["id"])) {
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
                    $this->systemModule->sSESSION_ID,
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

            if (empty($this->systemModule->sUSERGROUPDATA["tax"]) && !empty($this->systemModule->sUSERGROUPDATA["id"])) {
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
                    $this->systemModule->sSESSION_ID,
                    $discount_name,
                    $discount_ordernumber,
                    $discount,
                    $discount_net,
                    $tax_rate,
                    $currencyFactor
                )
            );
        }

        $dispatch = $this->sGetPremiumDispatch((int) $this->session->offsetGet('sDispatch'));

        if (!empty($payment['country_surcharge'][$country['countryiso']])) {
            $payment['surcharge'] += $payment['country_surcharge'][$country['countryiso']];
        }
        $payment['surcharge'] = round($payment['surcharge']*$currencyFactor,2);

        if (!empty($payment['surcharge']) && (empty($dispatch) || $dispatch['surcharge_calculation'] == 3)) {
            $surcharge = round($payment['surcharge'], 2);
            $payment['surcharge'] = 0;
            if (empty($this->systemModule->sUSERGROUPDATA["tax"]) && !empty($this->systemModule->sUSERGROUPDATA["id"])) {
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
                    $this->systemModule->sSESSION_ID,
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
                array($this->systemModule->sSESSION_ID)
            );

            $percent = round($amount / 100 * $payment['debit_percent'], 2);

            if ($percent>0) {
                $percent_name = $this->config->get('sPAYMENTSURCHARGEADD');
            } else {
                $percent_name = $this->config->get('sPAYMENTSURCHARGEDEV');
            }

            if (empty($this->systemModule->sUSERGROUPDATA["tax"]) && !empty($this->systemModule->sUSERGROUPDATA["id"])) {
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
                    $this->systemModule->sSESSION_ID,
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

        if (empty($this->systemModule->sUSERGROUPDATA["tax"]) && !empty($this->systemModule->sUSERGROUPDATA["id"])) {
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
                    'netto' => round($payment['surcharge']*100/(100+$this->config->get('sTAXSHIPPING')),2)
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
                "formated" => $this->articleModule->sFormatPrice($difference)
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
