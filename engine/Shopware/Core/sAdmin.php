<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Bundle\StoreFrontBundle;
use Shopware\Components\NumberRangeIncrementerInterface;
use Shopware\Components\Validator\EmailValidatorInterface;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

/**
 * Shopware Class that handles several
 * functions around customer / order related things
 */
class sAdmin
{
    /**
     * Check if current active shop has own registration
     *
     * @var bool s_core_shops.customer_scope
     */
    public $scopedRegistration;

    /**
     * Id of current active shop
     *
     * @var int s_core_shops.id
     */
    public $subshopId;

    /**
     * Pointer to sSystem object
     * Used for legacy purposes
     *
     * @var sSystem
     *
     * @deprecated
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
     * Event manager which is used for the event system of shopware.
     * Injected over the class constructor
     *
     * @var Enlight_Event_EventManager
     */
    private $eventManager;

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
     * The snippet manager
     *
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var StoreFrontBundle\Service\ContextServiceInterface
     */
    private $contextService;

    /**
     * Module manager for core class instances
     *
     * @var Shopware_Components_Modules
     */
    private $moduleManager;

    /**
     * Email address validator
     *
     * @var EmailValidatorInterface
     */
    private $emailValidator;

    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    /**
     * @var NumberRangeIncrementerInterface
     */
    private $numberRangeIncrementer;

    /**
     * @var Shopware\Bundle\AttributeBundle\Service\DataLoader
     */
    private $attributeLoader;

    /**
     * @var Shopware\Bundle\AttributeBundle\Service\DataPersister
     */
    private $attributePersister;

    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql          $db = null,
        Enlight_Event_EventManager                       $eventManager = null,
        Shopware_Components_Config                       $config = null,
        Enlight_Components_Session_Namespace             $session = null,
        Enlight_Controller_Front                         $front = null,
        \Shopware\Components\Password\Manager            $passwordEncoder = null,
        Shopware_Components_Snippet_Manager              $snippetManager = null,
        Shopware_Components_Modules                      $moduleManager = null,
        sSystem                                          $systemModule = null,
        StoreFrontBundle\Service\ContextServiceInterface $contextService = null,
        EmailValidatorInterface                          $emailValidator = null,
        AddressServiceInterface                          $addressService = null,
        NumberRangeIncrementerInterface                  $numberRangeIncrementer = null
    ) {
        $this->db = $db ?: Shopware()->Db();
        $this->eventManager = $eventManager ?: Shopware()->Events();
        $this->config = $config ?: Shopware()->Config();
        $this->session = $session ?: Shopware()->Session();
        $this->front = $front ?: Shopware()->Front();
        $this->passwordEncoder = $passwordEncoder ?: Shopware()->PasswordEncoder();
        $this->snippetManager = $snippetManager ?: Shopware()->Snippets();
        $this->moduleManager = $moduleManager ?: Shopware()->Modules();
        $this->sSYSTEM = $systemModule ?: Shopware()->System();

        $mainShop = Shopware()->Shop()->getMain() !== null ? Shopware()->Shop()->getMain() : Shopware()->Shop();
        $this->scopedRegistration = $mainShop->getCustomerScope();

        $this->contextService = $contextService ?: Shopware()->Container()->get('shopware_storefront.context_service');
        $this->emailValidator = $emailValidator ?: Shopware()->Container()->get('validator.email');
        $this->subshopId = $this->contextService->getShopContext()->getShop()->getParentId();
        $this->addressService = $addressService ?: Shopware()->Container()->get('shopware_account.address_service');
        $this->attributeLoader = Shopware()->Container()->get('shopware_attribute.data_loader');
        $this->attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');
        $this->numberRangeIncrementer = $numberRangeIncrementer ?: Shopware()->Container()->get('shopware.number_range_incrementer');
    }

    /**
     * Get data from a certain payment mean
     * If user data is provided, the current user payment
     * mean is validated against current country, risk management, etc
     * and reset to default if necessary.
     *
     * Used in several places to get the payment mean data
     *
     * @param int        $id   Payment mean id
     * @param array|bool $user Array with user data (sGetUserData)
     *
     * @return array Payment data
     */
    public function sGetPaymentMeanById($id, $user = false)
    {
        $id = intval($id);
        $resetPayment = false;

        $data = $this->db->fetchRow(
            'SELECT * FROM s_core_paymentmeans WHERE id = ?',
            [$id]
        ) ?: [];

        $sEsd = $this->moduleManager->Basket()->sCheckForESD();
        $isMobile = ($this->front->Request()->getDeviceType() == 'mobile');

        if (!count($user)) {
            $user = [];
        }

        // Check for risk management
        // If rules match, reset to default payment mean if this payment mean was not
        // set by shop owner

        // Hide payment means which are not active
        if (!$data['active'] && $data['id'] != $user['additional']['user']['paymentpreset']) {
            $resetPayment = $this->config->get('sPAYMENTDEFAULT');
        }

        // If esd - order, hide payment means which
        // are not available for esd
        if (!$data['esdactive'] && $sEsd) {
            $resetPayment = $this->config->get('sPAYMENTDEFAULT');
        }

        // Handle blocking for smartphones
        if (!empty($data['mobile_inactive']) && $isMobile) {
            $resetPayment = $this->config->get('sPAYMENTDEFAULT');
        }

        // Check additional rules
        if ($this->sManageRisks($data['id'], null, $user)
            && $data['id'] != $user['additional']['user']['paymentpreset']
        ) {
            $resetPayment = $this->config->get('sPAYMENTDEFAULT');
        }

        if (!empty($user['additional']['countryShipping']['id'])) {
            $sql = '
                SELECT 1
                FROM s_core_paymentmeans p

                LEFT JOIN s_core_paymentmeans_subshops ps
                ON ps.subshopID = ?
                AND ps.paymentID = p.id

                LEFT JOIN s_core_paymentmeans_countries pc
                ON pc.countryID = ?
                AND pc.paymentID=p.id

                WHERE (ps.paymentID IS NOT NULL OR (
                  SELECT paymentID FROM s_core_paymentmeans_subshops WHERE paymentID=p.id LIMIT 1
                ) IS NULL)
                AND (pc.paymentID IS NOT NULL OR (
                  SELECT paymentID FROM s_core_paymentmeans_countries WHERE paymentID=p.id LIMIT 1
                ) IS NULL)

                AND id = ?
            ';

            $active = $this->db->fetchOne(
                $sql,
                [
                    $this->contextService->getShopContext()->getShop()->getId(),
                    $user['additional']['countryShipping']['id'],
                    $id,
                ]
            );
            if (empty($active)) {
                $resetPayment = $this->config->get('sPAYMENTDEFAULT');
            }
        }

        if ($resetPayment && $user['additional']['user']['id']) {
            $this->eventManager->notify(
                'Shopware_Modules_Admin_Payment_Fallback',
                $data,
                ['userId' => $user['additional']['user']['id'], 'paymentId' => $resetPayment]
            );

            $this->db->update(
                's_user',
                ['paymentID' => $resetPayment],
                ['id = ?' => $user['additional']['user']['id']]
            );
            $data = $this->db->fetchRow(
                'SELECT * FROM s_core_paymentmeans WHERE id = ?',
                [$resetPayment]
            ) ?: [];
        }

        // Get translation
        $data = $this->sGetPaymentTranslation($data);

        $data = $this->eventManager->filter(
            'Shopware_Modules_Admin_GetPaymentMeanById_DataFilter',
            $data,
            ['subject' => $this, 'id' => $id, 'user' => $user]
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
        $isMobile = ($this->front->Request()->getDeviceType() == 'mobile');

        $user = $this->sGetUserData();

        $sEsd = $this->moduleManager->Basket()->sCheckForESD();

        $countryID = (int) $user['additional']['countryShipping']['id'];
        $subShopID = (int) $this->contextService->getShopContext()->getShop()->getId();
        if (empty($countryID)) {
            $countryID = $this->db->fetchOne(
                'SELECT id FROM s_core_countries ORDER BY position ASC LIMIT 1'
            );
        }
        $sql = '
            SELECT p.*
            FROM s_core_paymentmeans p

            LEFT JOIN s_core_paymentmeans_subshops ps
            ON ps.subshopID = ?
            AND ps.paymentID = p.id

            LEFT JOIN s_core_paymentmeans_countries pc
            ON pc.countryID = ?
            AND pc.paymentID = p.id

            WHERE
              (
                ps.paymentID IS NOT NULL
                OR (
                  SELECT paymentID
                  FROM s_core_paymentmeans_subshops
                  WHERE paymentID = p.id LIMIT 1
                ) IS NULL
              )
            AND
              (
                pc.paymentID IS NOT NULL
                OR (
                  SELECT paymentID
                  FROM s_core_paymentmeans_countries
                  WHERE paymentID = p.id LIMIT 1
                ) IS NULL
              )

            ORDER BY position, name
        ';

        $getPaymentMeans = $this->db->fetchAll(
            $sql,
            [
                $subShopID,
                $countryID,
            ]
        );

        if ($getPaymentMeans === false) {
            $getPaymentMeans = $this->db->fetchAll(
                'SELECT * FROM s_core_paymentmeans ORDER BY position, name'
            );
        }

        foreach ($getPaymentMeans as $payKey => $payValue) {
            // Hide payment means which are not active
            if (empty($payValue['active']) && $payValue['id'] != $user['additional']['user']['paymentpreset']) {
                unset($getPaymentMeans[$payKey]);
                continue;
            }

            // If esd order, hide payment mean, which
            // are not accessible for esd
            if (empty($payValue['esdactive']) && $sEsd) {
                unset($getPaymentMeans[$payKey]);
                continue;
            }

            // Handle blocking for smartphones
            if (!empty($payValue['mobile_inactive']) && $isMobile) {
                unset($getPaymentMeans[$payKey]);
                continue;
            }

            // Check additional rules
            if ($this->sManageRisks($payValue['id'], null, $user)
                && $payValue['id'] != $user['additional']['user']['paymentpreset']
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
                'SELECT * FROM s_core_paymentmeans WHERE id = ?',
                [$this->config->offsetGet('paymentdefault')]
            );
            $fallBackPayment = $fallBackPayment ?: [];

            $getPaymentMeans[] = $this->sGetPaymentTranslation($fallBackPayment);
        }

        $getPaymentMeans = $this->eventManager->filter(
            'Shopware_Modules_Admin_GetPaymentMeans_DataFilter',
            $getPaymentMeans,
            ['subject' => $this]
        );

        return $getPaymentMeans;
    }

    /**
     * Loads the system class of the specified payment mean
     *
     * @param array $paymentData Array with payment data
     *
     * @throws Enlight_Exception If no payment classes were loaded
     *
     * @return ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod
     *                                                                    The payment mean handling class instance
     */
    public function sInitiatePaymentClass($paymentData)
    {
        $dirs = [];

        if (substr($paymentData['class'], -strlen('.php')) === '.php') {
            $index = substr($paymentData['class'], 0, strpos($paymentData['class'], '.php'));
        } else {
            $index = $paymentData['class'];
        }

        $dirs = $this->eventManager->filter(
            'Shopware_Modules_Admin_InitiatePaymentClass_AddClass',
            $dirs,
            ['subject' => $this]
        );

        $class = array_key_exists($index, $dirs) ? $dirs[$index] : $dirs['default'];
        if (!$class) {
            throw new Enlight_Exception('sValidateStep3 #02: Payment classes dir not loaded');
        }

        $sPaymentObject = new $class();

        if (!$sPaymentObject) {
            throw new Enlight_Exception('sValidateStep3 #02: Payment class not found');
        }

        return $sPaymentObject;
    }

    /**
     * Last step of the registration - validate all user fields that exists in session and
     * stores the data into database
     *
     * @throws Enlight_Exception If no payment mean is set in POST
     *
     * @return array Payment data
     */
    public function sValidateStep3()
    {
        $paymentId = $this->front->Request()->getPost('sPayment');
        if (empty($paymentId)) {
            throw new Enlight_Exception('sValidateStep3 #00: No payment id');
        }

        $user = $this->sGetUserData();
        $paymentData = $this->sGetPaymentMeanById($paymentId, $user);

        if (!count($paymentData)) {
            throw new Enlight_Exception('sValidateStep3 #01: Could not load paymentmean');
        }
            // Include management class and check input data
            if (!empty($paymentData['class'])) {
                $sPaymentObject = $this->sInitiatePaymentClass($paymentData);
                $requestData = $this->front->Request()->getParams();
                $checkPayment = $sPaymentObject->validate($requestData);
            }

        return [
                'checkPayment' => $checkPayment,
                'paymentData' => $paymentData,
                'sProcessed' => true,
                'sPaymentObject' => &$sPaymentObject,
            ];
    }

    /**
     * Add or remove an email address from the mailing list
     *
     * @param bool   $status   True if insert, false if remove
     * @param string $email    Email address
     * @param bool   $customer If email address belongs to a customer
     *
     * @return bool If operation was successful
     */
    public function sUpdateNewsletter($status, $email, $customer = false)
    {
        if (!$status) {
            // Delete email address from database
            $this->db->delete(
                's_campaigns_mailaddresses',
                ['email = ?' => $email]
            );
        } else {
            // Check if mail address is already subscribed, return
            if ($this->db->fetchOne(
                'SELECT id FROM s_campaigns_mailaddresses WHERE email = ?',
                [$email])
            ) {
                return false;
            }

            $optInNewsletter = $this->config->get('optinnewsletter');
            if ($optInNewsletter) {
                $hash = md5(uniqid(rand()));
                $data = serialize(['newsletter' => $email, 'subscribeToNewsletter' => true]);

                $link = $this->front->Router()->assemble([
                        'sViewport' => 'newsletter',
                        'action' => 'index',
                        'sConfirmation' => $hash,
                    ]
                );

                $this->sendMail($email, 'sOPTINNEWSLETTER', $link);

                $this->db->insert(
                    's_core_optin',
                    [
                        'datum' => new Zend_Date(),
                        'hash' => $hash,
                        'data' => $data,
                    ]
                );

                return true;
            }

            $groupID = $this->config->get('sNEWSLETTERDEFAULTGROUP');
            if (!$groupID) {
                $groupID = '0';
            }
            // Insert email into database
            if (!empty($customer)) {
                $this->db->insert(
                    's_campaigns_mailaddresses',
                    ['customer' => 1, 'email' => $email, 'added' => $this->getCurrentDateFormatted()]
                );
            } else {
                $this->db->insert(
                    's_campaigns_mailaddresses',
                    ['groupID' => $groupID, 'email' => $email, 'added' => $this->getCurrentDateFormatted()]
                );
            }
        }

        return true;
    }

    /**
     * Updates the payment mean of the user
     * Used in the Frontend Account controller
     *
     * @param null $paymentId
     *
     * @throws Enlight_Exception On database error
     *
     * @return bool If operation was successful
     */
    public function sUpdatePayment($paymentId = null)
    {
        $userId = $this->session->offsetGet('sUserId');
        if (empty($userId)) {
            return false;
        }
        $sqlPayment = 'UPDATE s_user SET paymentID = ? WHERE id = ?';

        $sqlPayment = $this->eventManager->filter(
            'Shopware_Modules_Admin_UpdatePayment_FilterSql',
            $sqlPayment,
            [
                'subject' => $this,
                'id' => $userId,
            ]
        );

        $this->db->query(
            $sqlPayment,
            [
                $paymentId ?: $this->front->Request()->getPost('sPayment'),
                $userId,
            ]
        );

        if ($this->db->getErrorMessage()) {
            throw new Enlight_Exception(
                'sUpdatePayment #01: Could not save data (payment)'
                . $this->db->getErrorMessage()
            );
        }

        return true;
    }

    public function logout()
    {
        $this->moduleManager->Basket()->clearBasket();

        Shopware()->Session()->unsetAll();
        $this->regenerateSessionId();
        Shopware()->Container()->get('shopware.csrftoken_validator')->invalidateToken($this->front->Response());
    }

    /**
     * Attempt to login a user in the frontend
     * Used for login and registration in frontend, also for user impersonation
     * from backend
     *
     * @param bool $ignoreAccountMode Allows customers who have chosen
     *                                the fast registration, one-time login after registration
     *
     * @throws Exception If no password encoder is specified
     *
     * @return array|false Array with errors that may have occurred, or false if
     *                     the process is interrupted by an event
     */
    public function sLogin($ignoreAccountMode = false)
    {
        if ($this->eventManager->notifyUntil(
            'Shopware_Modules_Admin_Login_Start',
            [
                'subject' => $this,
                'ignoreAccountMode' => $ignoreAccountMode,
                'post' => $this->front->Request()->getPost(),
            ]
        )) {
            return false;
        }

        // If fields are not set, markup these fields
        $email = strtolower($this->front->Request()->getPost('email'));
        if (empty($email)) {
            $sErrorFlag['email'] = true;
        }

        // If password is already md5 decrypted or the parameter $ignoreAccountMode is set, use it directly
        if ($ignoreAccountMode && $this->front->Request()->getPost('passwordMD5')) {
            $password = $this->front->Request()->getPost('passwordMD5');
            $isPreHashed = true;
        } else {
            $password = $this->front->Request()->getPost('password');
            $isPreHashed = false;
        }

        if (empty($password)) {
            $sErrorFlag['password'] = true;
        }

        if (!empty($sErrorFlag)) {
            $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/account/internalMessages')
                ->get('LoginFailure', 'Wrong email or password');
            $this->session->offsetUnset('sUserMail');
            $this->session->offsetUnset('sUserPassword');
            $this->session->offsetUnset('sUserId');
        }

        if (count($sErrorMessages)) {
            list($sErrorMessages, $sErrorFlag) = $this->eventManager->filter(
                'Shopware_Modules_Admin_Login_FilterResult',
                [$sErrorMessages, $sErrorFlag],
                ['subject' => $this, 'email' => null, 'password' => null, 'error' => $sErrorMessages]
            );

            return ['sErrorFlag' => $sErrorFlag, 'sErrorMessages' => $sErrorMessages];
        }

        $addScopeSql = '';
        if ($this->scopedRegistration == true) {
            $addScopeSql = $this->db->quoteInto(' AND subshopID = ? ', $this->subshopId);
        }

        // When working with a prehashed password, we need to limit the getUser query by password,
        // as there might be multiple users with the same mail address (accountmode = 1).
        $preHashedSql = '';
        if ($isPreHashed) {
            $preHashedSql = $this->db->quoteInto(' AND password = ? ', $password);
        }

        if ($ignoreAccountMode) {
            $sql = '
                SELECT id, customergroup, password, encoder
                FROM s_user WHERE email = ? AND active=1
                AND (lockeduntil < now() OR lockeduntil IS NULL) '
                . $addScopeSql
                . $preHashedSql;
        } else {
            $sql = '
                SELECT id, customergroup, password, encoder
                FROM s_user
                WHERE email = ? AND active=1 AND accountmode != 1
                AND (lockeduntil < now() OR lockeduntil IS NULL) '
                . $addScopeSql;
        }

        $getUser = $this->db->fetchRow($sql, [$email]) ?: [];

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

            $hash = $getUser['password'];
            $plaintext = $password;
            $password = $hash;

            $isValidLogin = $this->passwordEncoder->isPasswordValid($plaintext, $hash, $encoderName);
        }

        if ($isValidLogin) {
            $this->loginUser($getUser, $email, $password, $isPreHashed, $encoderName, $plaintext, $hash);
        } else {
            $sErrorMessages = $this->failedLoginUser($addScopeSql, $email, $sErrorMessages, $password);
        }

        list($sErrorMessages, $sErrorFlag) = $this->eventManager->filter(
            'Shopware_Modules_Admin_Login_FilterResult',
            [$sErrorMessages, $sErrorFlag],
            ['subject' => $this, 'email' => $email, 'password' => $password, 'error' => $sErrorMessages]
        );

        return ['sErrorFlag' => $sErrorFlag, 'sErrorMessages' => $sErrorMessages];
    }

    /**
     * Checks if user is correctly logged in. Also checks session timeout
     *
     * @return bool If user is authorized
     */
    public function sCheckUser()
    {
        if ($this->eventManager->notifyUntil(
            'Shopware_Modules_Admin_CheckUser_Start',
            ['subject' => $this])
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

        $sql = '
            SELECT * FROM s_user
            WHERE password = ? AND email = ? AND id = ?
            AND UNIX_TIMESTAMP(lastlogin) >= (UNIX_TIMESTAMP(now())-?)
        ';

        $timeOut = $this->config->get('sUSERTIMEOUT');
        $timeOut = !empty($timeOut) ? $timeOut : 7200;

        $getUser = $this->db->fetchRow(
            $sql,
            [
                $userPassword,
                $userMail,
                $userId,
                $timeOut,
            ]
        );
        $getUser = $getUser ?: [];

        $getUser = $this->eventManager->filter(
            'Shopware_Modules_Admin_CheckUser_FilterGetUser',
            $getUser,
            ['subject' => $this, 'sql' => $sql, 'session' => $this->session]
        );

        if (!empty($getUser['id'])) {
            $this->sSYSTEM->sUSERGROUPDATA = $this->db->fetchRow(
                'SELECT * FROM s_core_customergroups WHERE groupkey = ?',
                [$getUser['customergroup']]
            );
            $this->sSYSTEM->sUSERGROUPDATA = $this->sSYSTEM->sUSERGROUPDATA ?: [];

            if ($this->sSYSTEM->sUSERGROUPDATA['mode']) {
                $this->sSYSTEM->sUSERGROUP = 'EK';
            } else {
                $this->sSYSTEM->sUSERGROUP = $getUser['customergroup'];
            }
            $this->sSYSTEM->sUSERGROUP = $getUser['customergroup'];

            $this->session->offsetSet('sUserGroup', $this->sSYSTEM->sUSERGROUP);
            $this->session->offsetSet('sUserGroupData', $this->sSYSTEM->sUSERGROUPDATA);

            $this->db->query(
                'UPDATE s_user SET lastlogin = NOW(), sessionID = ? WHERE id = ?',
                [$this->session->offsetGet('sessionId'), $getUser['id']]
            );
            $this->eventManager->notify(
                'Shopware_Modules_Admin_CheckUser_Successful',
                ['subject' => $this, 'session' => $this->session, 'user' => $getUser]
            );

            return true;
        }
        $this->session->offsetUnset('sUserMail');
        $this->session->offsetUnset('sUserPassword');
        $this->session->offsetUnset('sUserId');
        $this->eventManager->notify(
                'Shopware_Modules_Admin_CheckUser_Failure',
                ['subject' => $this, 'session' => $this->session, 'user' => $getUser]
            );

        return false;
    }

    /**
     * Loads translations for countries. If no argument is provided,
     * all translations for current locale are returned, otherwise
     * returns the provided country's translation
     * Used internally in sAdmin
     *
     * @param array|string $country Optional array containing country data
     *                              for translation
     *
     * @return array Translated country/ies data
     */
    public function sGetCountryTranslation($country = '')
    {
        $languageId = $this->contextService->getShopContext()->getShop()->getId();
        $fallbackId = $this->contextService->getShopContext()->getShop()->getFallbackId();

        $translator = new Shopware_Components_Translation();
        $translationData = $translator->readBatchWithFallback($languageId, $fallbackId, 'config_countries');

        if (!$country) {
            return $translationData;
        }

        // Pass (possible) translation to country
        if ($translationData[$country['id']]['countryname']) {
            $country['countryname'] = $translationData[$country['id']]['countryname'];
        }
        if ($translationData[$country['id']]['notice']) {
            $country['notice'] = $translationData[$country['id']]['notice'];
        }

        if ($translationData[$country['id']]['active']) {
            $country['active'] = $translationData[$country['id']]['active'];
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
     *                               data for translation
     *
     * @return array Translated shipping method(s) data
     */
    public function sGetDispatchTranslation($dispatch = '')
    {
        $languageId = $this->contextService->getShopContext()->getShop()->getId();
        $fallbackId = $this->contextService->getShopContext()->getShop()->getFallbackId();

        $translator = new Shopware_Components_Translation();
        $translationData = $translator->readBatchWithFallback($languageId, $fallbackId, 'config_dispatch');

        if (!$dispatch) {
            return $translationData;
        }

        // Pass (possible) translation to country
        if ($translationData[$dispatch['id']]['dispatch_name']) {
            $dispatch['name'] = $translationData[$dispatch['id']]['dispatch_name'];
        }
        if ($translationData[$dispatch['id']]['dispatch_description']) {
            $dispatch['description'] = $translationData[$dispatch['id']]['dispatch_description'];
        }
        if ($translationData[$dispatch['id']]['dispatch_status_link']) {
            $dispatch['status_link'] = $translationData[$dispatch['id']]['dispatch_status_link'];
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
     *                              data for translation
     *
     * @return array Translated payment mean(s) data
     */
    public function sGetPaymentTranslation($payment = '')
    {
        $languageId = $this->contextService->getShopContext()->getShop()->getId();
        $fallbackId = $this->contextService->getShopContext()->getShop()->getFallbackId();

        $translator = new Shopware_Components_Translation();
        $translationData = $translator->readBatchWithFallback($languageId, $fallbackId, 'config_payment');

        if (!$payment) {
            return $translationData;
        }

        // Pass (possible) translation to payment
        if (!empty($translationData[$payment['id']]['description'])) {
            $payment['description'] = $translationData[$payment['id']]['description'];
        }
        if (!empty($translationData[$payment['id']]['additionalDescription'])) {
            $payment['additionaldescription'] = $translationData[$payment['id']]['additionalDescription'];
        }

        return $payment;
    }

    /**
     * Get translations for country states in the current shop language
     * Also includes fallback translations
     * Used internally in sAdmin
     *
     * @param null $state
     *
     * @return array States translations
     */
    public function sGetCountryStateTranslation($state = null)
    {
        if (Shopware()->Shop()->get('skipbackend')) {
            return empty($state) ? [] : $state;
        }

        $languageId = $this->contextService->getShopContext()->getShop()->getId();
        $fallbackId = $this->contextService->getShopContext()->getShop()->getFallbackId();

        $translator = new Shopware_Components_Translation();
        $translationData = $translator->readBatchWithFallback($languageId, $fallbackId, 'config_country_states');

        if (empty($state)) {
            return $translationData;
        }

        if ($translationData[$state['id']]) {
            $state['statename'] = $translationData[$state['id']]['name'];
        }

        return $state;
    }

    /**
     * Get list of currently active countries. Includes states and translations
     *
     * @return array Country list
     */
    public function sGetCountryList()
    {
        $countryList = $this->db->fetchAll(
            'SELECT * FROM s_core_countries ORDER BY position, countryname ASC'
        );

        $countryTranslations = $this->sGetCountryTranslation();
        $stateTranslations = $this->sGetCountryStateTranslation();

        foreach ($countryList as $key => $country) {
            if (isset($countryTranslations[$country['id']]['active'])) {
                if (!$countryTranslations[$country['id']]['active']) {
                    unset($countryList[$key]);
                    continue;
                }
            } else {
                // Use main config when nothing is set for subshop or if current is main shop (isocode 1)
                if (!$country['active']) {
                    unset($countryList[$key]);
                    continue;
                }
            }

            $countryList[$key]['states'] = [];
            if (!empty($country['display_state_in_registration'])) {
                // Get country states
                $states = $this->db->fetchAssoc('
                    SELECT * FROM s_core_countries_states
                    WHERE countryID = ? AND active = 1
                    ORDER BY position, name ASC
                ', [$country['id']]);

                foreach ($states as $stateId => $state) {
                    if (isset($stateTranslations[$stateId])) {
                        $states[$stateId] = array_merge($state, $stateTranslations[$stateId]);
                    }
                }
                $countryList[$key]['states'] = $states;
            }
            if (!empty($countryTranslations[$country['id']]['countryname'])) {
                $countryList[$key]['countryname'] = $countryTranslations[$country['id']]['countryname'];
            }
            if (!empty($countryTranslations[$country['id']]['notice'])) {
                $countryList[$key]['notice'] = $countryTranslations[$country['id']]['notice'];
            }

            $countryList[$key]['flag'] =
                ($countryList[$key]['id'] == $this->front->Request()->getPost('country')
                || $countryList[$key]['id'] == $this->front->Request()->getPost('countryID')
            );
        }

        $countryList = $this->eventManager->filter(
            'Shopware_Modules_Admin_GetCountries_FilterResult',
            $countryList,
            ['subject' => $this]
        );

        return $countryList;
    }

    /**
     * Send email with registration confirmation
     * Used internally in sAdmin during the registration process
     *
     * @param string $email Recipient email address
     *
     * @return null|false False if stopped, null otherwise
     */
    public function sSaveRegisterSendConfirmation($email)
    {
        if ($this->eventManager->notifyUntil(
            'Shopware_Modules_Admin_SaveRegisterSendConfirmation_Start',
            ['subject' => $this, 'email' => $email])
        ) {
            return false;
        }

        if (!$this->config->get('sendRegisterConfirmation')) {
            return false;
        }

        $context = [
            'sMAIL' => $email,
            'sShop' => $this->config->get('ShopName'),
            'sShopURL' => 'http://' . $this->config->get('BasePath'),
            'sConfig' => $this->config,
        ];

        $namespace = $this->snippetManager->getNamespace('frontend/salutation');
        $register = $this->session->offsetGet('sRegister');
        foreach ($register['billing'] as $key => $value) {
            if ($key == 'salutation') {
                $value = $namespace->get($value);
            }

            $context[$key] = $value;
        }

        if (array_key_exists('password', $context)) {
            unset($context['password']);
        }

        $mail = Shopware()->TemplateMail()->createMail('sREGISTERCONFIRMATION', $context);
        $mail->addTo($email);

        $sendConfirmationEmail = $this->config->get('sSEND_CONFIRM_MAIL');
        if (!empty($sendConfirmationEmail)) {
            $mail->addBcc($this->config->get('sMAIL'));
        }

        $this->eventManager->notify(
            'Shopware_Modules_Admin_SaveRegisterSendConfirmation_BeforeSend',
            ['subject' => $this, 'mail' => $mail]
        );

        $mail->send();
    }

    /**
     * Get purchased instant downloads for the current user
     * Used in Account controller to display download available to the user
     *
     * @param int $destinationPage
     * @param int $perPage
     *
     * @return array Data from orders who contains instant downloads
     */
    public function sGetDownloads($destinationPage = 1, $perPage = 10)
    {
        $userId = $this->session->offsetGet('sUserId');
        $getOrders = $this->db->fetchAll(
            "SELECT
                id, ordernumber, invoice_amount, invoice_amount_net,
                invoice_shipping, invoice_shipping_net,
                DATE_FORMAT(ordertime, '%d.%m.%Y %H:%i') AS datum,
                status, cleared, comment
            FROM s_order WHERE userID = ? AND s_order.status >= 0
            ORDER BY ordertime DESC LIMIT 500",
            [$userId]
        );

        foreach ($getOrders as $orderKey => $orderValue) {
            if (($this->config->get('sARTICLESOUTPUTNETTO') && !$this->sSYSTEM->sUSERGROUPDATA['tax'])
                || (!$this->sSYSTEM->sUSERGROUPDATA['tax'] && $this->sSYSTEM->sUSERGROUPDATA['id'])
            ) {
                $getOrders[$orderKey]['invoice_amount'] = $this->moduleManager->Articles()
                    ->sFormatPrice($orderValue['invoice_amount_net']);
                $getOrders[$orderKey]['invoice_shipping'] = $this->moduleManager->Articles()
                    ->sFormatPrice($orderValue['invoice_shipping_net']);
            } else {
                $getOrders[$orderKey]['invoice_amount'] = $this->moduleManager->Articles()
                    ->sFormatPrice($orderValue['invoice_amount']);
                $getOrders[$orderKey]['invoice_shipping'] = $this->moduleManager->Articles()
                    ->sFormatPrice($orderValue['invoice_shipping']);
            }

            $getOrderDetails = $this->db->fetchAll(
                'SELECT * FROM s_order_details WHERE orderID = ?',
                [$orderValue['id']]
            );

            if (!count($getOrderDetails)) {
                unset($getOrders[$orderKey]);
            } else {
                $foundESD = false;
                foreach ($getOrderDetails as $orderDetailsKey => $orderDetailsValue) {
                    $getOrderDetails[$orderDetailsKey]['amount'] = $this->moduleManager->Articles()
                        ->sFormatPrice(round($orderDetailsValue['price'] * $orderDetailsValue['quantity'], 2));
                    $getOrderDetails[$orderDetailsKey]['price'] = $this->moduleManager->Articles()
                        ->sFormatPrice($orderDetailsValue['price']);

                    // Check for serial
                    if ($getOrderDetails[$orderDetailsKey]['esdarticle']) {
                        $foundESD = true;
                        $numbers = [];
                        $getSerial = $this->db->fetchAll(
                            'SELECT serialnumber FROM s_articles_esd_serials, s_order_esd
                            WHERE userID = ?
                            AND orderID = ?
                            AND orderdetailsID = ?
                            AND s_order_esd.serialID = s_articles_esd_serials.id',
                            [
                                $userId,
                                $orderValue['id'],
                                $orderDetailsValue['id'],
                            ]
                        );
                        foreach ($getSerial as $serial) {
                            $numbers[] = $serial['serialnumber'];
                        }
                        $getOrderDetails[$orderDetailsKey]['serial'] = implode(', ', $numbers);
                        // Building download link
                        $getOrderDetails[$orderDetailsKey]['esdLink'] = $this->config->get('sBASEFILE')
                            . '?sViewport=account&sAction=download&esdID='
                            . $orderDetailsValue['id'];
                    } else {
                        unset($getOrderDetails[$orderDetailsKey]);
                    }
                }
                if (!empty($foundESD)) {
                    $getOrders[$orderKey]['details'] = $getOrderDetails;
                } else {
                    unset($getOrders[$orderKey]);
                }
            }
        }

        $getOrders = $this->eventManager->filter(
            'Shopware_Modules_Admin_GetDownloads_FilterResult',
            $getOrders,
            ['subject' => $this, 'id' => $userId]
        );

        if ($perPage != 0) {
            // Make Array with page-structure to render in template
            $numberOfPages = ceil(count($getOrders) / $perPage);
        } else {
            $numberOfPages = 0;
        }
        $offset = ($destinationPage - 1) * $perPage;
        $orderData['orderData'] = array_slice($getOrders, $offset, $perPage, true);
        $orderData['numberOfPages'] = $numberOfPages;
        $orderData['pages'] = $this->getPagerStructure($destinationPage, $numberOfPages);

        return $orderData;
    }

    /**
     * Get all orders for the current user
     * Used in the user account in the Frontend
     *
     * @param int $destinationPage
     * @param int $perPage
     *
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
            SELECT SQL_CALC_FOUND_ROWS o.*, cu.templatechar as currency_html, cu.symbol_position as currency_position, DATE_FORMAT(ordertime, '%d.%m.%Y %H:%i') AS datum
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
            [
                $this->session->offsetGet('sUserId'),
                $mainShop->getId(),
            ]
        );
        $foundOrdersCount = (int) Shopware()->Db()->fetchOne('SELECT FOUND_ROWS()');

        foreach ($getOrders as $orderKey => $orderValue) {
            $getOrders[$orderKey]['invoice_amount'] = $this->moduleManager->Articles()
                ->sFormatPrice($orderValue['invoice_amount']);
            $getOrders[$orderKey]['invoice_amount_net'] = $this->moduleManager->Articles()
                ->sFormatPrice($orderValue['invoice_amount_net']);
            $getOrders[$orderKey]['invoice_shipping'] = $this->moduleManager->Articles()
                ->sFormatPrice($orderValue['invoice_shipping']);

            $getOrders = $this->processOpenOrderDetails($orderValue, $getOrders, $orderKey);
            $getOrders[$orderKey]['dispatch'] = $this->sGetPremiumDispatch($orderValue['dispatchID']);
        }

        $getOrders = $this->eventManager->filter(
            'Shopware_Modules_Admin_GetOpenOrderData_FilterResult',
            $getOrders,
            [
                'subject' => $this,
                'id' => $this->session->offsetGet('sUserId'),
                'subshopID' => $this->contextService->getShopContext()->getShop()->getId(),
            ]
        );

        $orderData['orderData'] = $getOrders;

        if ($limitEnd != 0) {
            // Make Array with page structure to render in template
            $numberOfPages = ceil($foundOrdersCount / $limitEnd);
        } else {
            $numberOfPages = 0;
        }
        $orderData['numberOfPages'] = $numberOfPages;

        $orderData['pages'] = $this->getPagerStructure($destinationPage, $numberOfPages);

        return $orderData;
    }

    /**
     * Calculates and returns the pager structure for the frontend
     *
     * @param int   $destinationPage
     * @param int   $numberOfPages
     * @param array $additionalParams
     *
     * @return array
     */
    public function getPagerStructure($destinationPage, $numberOfPages, $additionalParams = [])
    {
        $destinationPage = !empty($destinationPage) ? $destinationPage : 1;
        $pagesStructure = [];
        $baseFile = $this->config->get('sBASEFILE');
        if ($numberOfPages > 1) {
            for ($i = 1; $i <= $numberOfPages; ++$i) {
                $pagesStructure['numbers'][$i]['markup'] = ($i == $destinationPage);
                $pagesStructure['numbers'][$i]['value'] = $i;
                $pagesStructure['numbers'][$i]['link'] = $baseFile . $this->moduleManager->Core()->sBuildLink(
                    $additionalParams + ['sPage' => $i]
                );
            }
            // Previous page
            if ($destinationPage != 1) {
                $pagesStructure['previous'] = $baseFile . $this->moduleManager->Core()->sBuildLink(
                    $additionalParams + ['sPage' => $destinationPage - 1]
                );
            } else {
                $pagesStructure['previous'] = null;
            }
            // Next page
            if ($destinationPage != $numberOfPages) {
                $pagesStructure['next'] = $baseFile . $this->moduleManager->Core()->sBuildLink(
                    $additionalParams + ['sPage' => $destinationPage + 1],
                    false
                );
            } else {
                $pagesStructure['next'] = null;
            }
        }

        return $pagesStructure;
    }

    /**
     * Get the current user's email address
     *
     * @return string|null Current user email address, or null if none found
     */
    public function sGetUserMailById()
    {
        return $this->db->fetchOne(
            'SELECT email FROM s_user WHERE id = ?',
            [$this->session->offsetGet('sUserId')]
        ) ?: null;
    }

    /**
     * Get user id by his email address
     *
     * @param string $email Email address of the user
     *
     * @return int|null The user id, or null if none found
     */
    public function sGetUserByMail($email)
    {
        $addScopeSql = '';
        if ($this->scopedRegistration == true) {
            $addScopeSql = $this->db->quoteInto('AND subshopID = ?', $this->subshopId);
        }

        return $this->db->fetchOne(
            "SELECT id FROM s_user WHERE email = ? AND accountmode != 1 $addScopeSql",
            [$email]
        ) ?: null;
    }

    /**
     * Get user first and last names by id
     *
     * @param int $id User id
     *
     * @return array first name/last name
     */
    public function sGetUserNameById($id)
    {
        return $this->db->fetchRow('SELECT firstname, lastname FROM s_user WHERE id = ?', [$id]) ?: [];
    }

    /**
     * Get all data from the current logged in user
     *
     * @return array|false User data, of false if interrupted
     */
    public function sGetUserData()
    {
        if ($this->eventManager->notifyUntil(
            'Shopware_Modules_Admin_GetUserData_Start',
            ['subject' => $this])
        ) {
            return false;
        }
        $register = $this->session->offsetGet('sRegister');
        if (empty($register)) {
            $this->session->offsetSet('sRegister', []);
        }

        $userData = [];

        $countryQuery =
          'SELECT c.*, a.name AS countryarea
          FROM s_core_countries c
          LEFT JOIN s_core_countries_areas a
           ON a.id = c.areaID AND a.active = 1
          WHERE c.id = ?';

        // If user is logged in
        $userId = $this->session->offsetGet('sUserId');
        if (!empty($userId)) {
            $userData = $this->getUserBillingData($userId, $userData);

            $userData = $this->getUserCountryData($userData, $userId);

            $newsletter = $this->db->fetchRow(
                'SELECT id FROM s_campaigns_mailaddresses WHERE email = ?',
                [$userData['additional']['user']['email']]
            );

            $userData['additional']['user']['newsletter'] = $newsletter['id'] ? 1 : 0;

            $userData = $this->getUserShippingData($userId, $userData, $countryQuery);
            $userData = $this->overwriteBillingAddress($userData);
            $userData = $this->overwriteShippingAddress($userData);

            $userData['additional']['payment'] = $this->sGetPaymentMeanById(
                $userData['additional']['user']['paymentID'],
                $userData
            );
        } else {
            // No user logged in
            $register = $this->session->offsetGet('sRegister');
            if ($this->session->offsetGet('sCountry')
                && $this->session->offsetGet('sCountry') != $register['billing']['country']
            ) {
                $register['billing']['country'] = intval($this->session->offsetGet('sCountry'));
                $this->session->offsetSet('sRegister', $register);
            }

            $userData['additional']['country'] = $this->db->fetchRow(
                $countryQuery,
                [intval($register['billing']['country'])]
            );
            $userData['additional']['country'] = $userData['additional']['country'] ?: [];
            $userData['additional']['countryShipping'] = $userData['additional']['country'];
            $state = $this->session->offsetGet('sState');
            $userData['additional']['stateShipping']['id'] = !empty($state) ? $state : 0;
        }

        $userData = $this->eventManager->filter(
            'Shopware_Modules_Admin_GetUserData_FilterResult',
            $userData,
            ['subject' => $this, 'id' => $this->session->offsetGet('sUserId')]
        );

        return $userData;
    }

    /**
     * Shopware Risk Management
     *
     * @param int   $paymentID Payment mean id (s_core_paymentmeans.id)
     * @param array $basket    Current shopping cart
     * @param array $user      User data
     *
     * @return bool If customer is a risk customer
     */
    public function sManageRisks($paymentID, $basket, $user)
    {
        // Get all assigned rules
        $queryRules = $this->db->fetchAll('
            SELECT rule1, value1, rule2, value2
            FROM s_core_rulesets
            WHERE paymentID = ?
            ORDER BY id ASC
        ', [$paymentID]);

        if (empty($queryRules)) {
            return false;
        }

        // Get Basket
        if (empty($basket)) {
            $basket = [
                'content' => $this->session->offsetGet('sBasketQuantity'),
                'AmountNumeric' => $this->session->offsetGet('sBasketAmount'),
            ];
        }

        foreach ($queryRules as $rule) {
            if ($rule['rule1'] && !$rule['rule2']) {
                $rule['rule1'] = 'sRisk' . $rule['rule1'];
                if ($this->executeRiskRule($rule['rule1'], $user, $basket, $rule['value1'])) {
                    return true;
                }
            } elseif ($rule['rule1'] && $rule['rule2']) {
                $rule['rule1'] = 'sRisk' . $rule['rule1'];
                $rule['rule2'] = 'sRisk' . $rule['rule2'];
                if ($this->executeRiskRule($rule['rule1'], $user, $basket, $rule['value1'])
                    && $this->executeRiskRule($rule['rule2'], $user, $basket, $rule['value2'])
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * function to execute risk rules
     *
     * @param string $rule
     * @param array  $user
     * @param array  $basket
     * @param string $value
     *
     * @return bool
     */
    public function executeRiskRule($rule, $user, $basket, $value)
    {
        if ($event = $this->eventManager->notifyUntil(
            'Shopware_Modules_Admin_Execute_Risk_Rule_' . $rule,
            [
                'rule' => $rule,
                'user' => $user,
                'basket' => $basket,
                'value' => $value,
            ]
        )) {
            return $event->getReturn();
        }

        return $this->$rule($user, $basket, $value);
    }

    /**
     * Risk management - Order value greater then
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskORDERVALUEMORE($user, $order, $value)
    {
        $basketValue = $order['AmountNumeric'];

        if ($this->sSYSTEM->sCurrency['factor']) {
            $basketValue /= $this->sSYSTEM->sCurrency['factor'];
        }

        return $basketValue >= $value;
    }

    /**
     * Risk management - Order value less then
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskORDERVALUELESS($user, $order, $value)
    {
        $basketValue = $order['AmountNumeric'];

        if ($this->sSYSTEM->sCurrency['factor']) {
            $basketValue /= $this->sSYSTEM->sCurrency['factor'];
        }

        return $basketValue <= $value;
    }

    /**
     * Risk management Customer group matches value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskCUSTOMERGROUPIS($user, $order, $value)
    {
        return $user['additional']['user']['customergroup'] == $value;
    }

    /**
     * Risk management Customer group doesn't match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskCUSTOMERGROUPISNOT($user, $order, $value)
    {
        return $user['additional']['user']['customergroup'] != $value;
    }

    /**
     * Risk management - Shipping zip code match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskZIPCODE($user, $order, $value)
    {
        if ($value == '-1') {
            $value = '';
        }

        return $user['shippingaddress']['zipcode'] == $value;
    }

    /**
     * Risk management - Billing zip code match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskBILLINGZIPCODE($user, $order, $value)
    {
        if ($value == '-1') {
            $value = '';
        }

        return $user['billingaddress']['zipcode'] == $value;
    }

    /**
     * Risk management - Country zone matches value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskZONEIS($user, $order, $value)
    {
        return $user['additional']['countryShipping']['countryarea'] == $value;
    }

    /**
     * Risk management - Country zone doesn't match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskZONEISNOT($user, $order, $value)
    {
        return $user['additional']['countryShipping']['countryarea'] != $value;
    }

    /**
     * Risk management - Billing Country zone matches value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskBILLINGZONEIS($user, $order, $value)
    {
        return $user['additional']['country']['countryarea'] == $value;
    }

    /**
     * Risk management - Billing Country zone doesn't match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskBILLINGZONEISNOT($user, $order, $value)
    {
        return $user['additional']['country']['countryarea'] != $value;
    }

    /**
     * Risk management - Country matches value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskLANDIS($user, $order, $value)
    {
        if (preg_match("/$value/", $user['additional']['countryShipping']['countryiso'])) {
            return true;
        }

        return $user['additional']['countryShipping']['countryiso'] == $value;
    }

    /**
     * Risk management - Country doesn't match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskLANDISNOT($user, $order, $value)
    {
        if (!preg_match("/$value/", $user['additional']['countryShipping']['countryiso'])) {
            return true;
        }

        return $user['additional']['countryShipping']['countryiso'] != $value;
    }

    /**
     * Risk management - Billing Country matches value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskBILLINGLANDIS($user, $order, $value)
    {
        if (preg_match("/$value/", $user['additional']['country']['countryiso'])) {
            return true;
        }

        return $user['additional']['country']['countryiso'] == $value;
    }

    /**
     * Risk management - Billing Country doesn't match value
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskBILLINGLANDISNOT($user, $order, $value)
    {
        if (!preg_match("/$value/", $user['additional']['country']['countryiso'])) {
            return true;
        }

        return $user['additional']['country']['countryiso'] != $value;
    }

    /**
     * Risk management - Customer is new
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskNEWCUSTOMER($user, $order, $value)
    {
        return
            $user['additional']['user']['firstlogin'] == date('Y-m-d')
            || !$user['additional']['user']['firstlogin']
        ;
    }

    /**
     * Risk management - Order has more then value positions
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskORDERPOSITIONSMORE($user, $order, $value)
    {
        return
            is_array($order['content']) ? count($order['content']) : $order['content'] >= $value
        ;
    }

    /**
     * Risk management - Article attribute x from basket - positions is y
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskATTRIS($user, $order, $value)
    {
        if (!empty($order['content'])) {
            $value = explode('|', $value);
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
                    [$this->session->offsetGet('sessionId'), $value[1]]
                );

                return (bool) $checkArticle;
            }

            return false;
        }
    }

    /**
     * Risk management - article attribute x from basket is not y
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskATTRISNOT($user, $order, $value)
    {
        if (!empty($order['content'])) {
            $value = explode('|', $value);
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
                    [
                        $this->session->offsetGet('sessionId'),
                        $value[1],
                    ]
                );

                return (bool) $checkArticle;
            }

            return false;
        }
    }

    /**
     * Risk management
     * Check if at least one order of the customer has a payment status 13
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskDUNNINGLEVELONE($user, $order, $value)
    {
        return $this->riskCheckClearedLevel(13);
    }

    /**
     * Risk management
     * Check if at least one order of the customer has a payment status 14
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskDUNNINGLEVELTWO($user, $order, $value)
    {
        return $this->riskCheckClearedLevel(14);
    }

    /**
     * Risk management
     * Check if at least one order of the customer has a payment status 15
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskDUNNINGLEVELTHREE($user, $order, $value)
    {
        return $this->riskCheckClearedLevel(15);
    }

    /**
     * Risk management
     * Check if at least one order of the customer has a payment status 16 (Encashment)
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskINKASSO($user, $order, $value)
    {
        return $this->riskCheckClearedLevel(16);
    }

    /**
     * Risk management - Last order less x days
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskLASTORDERLESS($user, $order, $value)
    {
        // A order from previous x days must exists
        if ($this->session->offsetGet('sUserId')) {
            $value = (int) $value;
            $checkOrder = $this->db->fetchRow(
                "SELECT id
                FROM s_order
                WHERE userID = ?
                AND TO_DAYS(ordertime) <= (TO_DAYS(now())-$value) LIMIT 1",
                [
                    $this->session->offsetGet('sUserId'),
                ]
            );

            return !$checkOrder || !$checkOrder['id'];
        }

        return true;
    }

    /**
     * Risk management - Articles from a certain category
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskARTICLESFROM($user, $order, $value)
    {
        $checkArticle = $this->db->fetchOne('
            SELECT s_articles_categories_ro.id as id
            FROM s_order_basket, s_articles_categories_ro
            WHERE s_order_basket.articleID = s_articles_categories_ro.articleID
            AND s_articles_categories_ro.categoryID = ?
            AND s_order_basket.sessionID = ?
            AND s_order_basket.modus = 0
        ', [$value, $this->session->offsetGet('sessionId')]);

        return !empty($checkArticle);
    }

    /**
     * Risk management - Order value greater then
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskLASTORDERSLESS($user, $order, $value)
    {
        if ($this->session->offsetGet('sUserId')) {
            $checkOrder = $this->db->fetchAll(
                'SELECT id FROM s_order
                  WHERE status != -1 AND status != 4 AND userID = ?',
                [$this->session->offsetGet('sUserId')]
            );

            return count($checkOrder) <= $value;
        }

        return true;
    }

    /**
     * Risk management - Block if street contains pattern
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskPREGSTREET($user, $order, $value)
    {
        $value = strtolower($value);

        return (bool) preg_match(
            "/$value/",
            strtolower($user['shippingaddress']['street'])
        );
    }

    /**
     * Risk management - Block if street contains pattern
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskPREGBILLINGSTREET($user, $order, $value)
    {
        $value = strtolower($value);

        return (bool) preg_match(
            "/$value/",
            strtolower($user['billingaddress']['street'])
        );
    }

    /**
     * Risk management - Block if billing address not equal to shipping address
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskDIFFER($user, $order, $value)
    {
        // Compare street and zipcode.
        // Return true if any of them doesn't match.
        return
            (
                strtolower(
                    trim($user['shippingaddress']['street'])
                ) != strtolower(
                    trim($user['billingaddress']['street'])
                )
            ) || (
                trim($user['shippingaddress']['zipcode'])
                != trim($user['billingaddress']['zipcode'])
            )
        ;
    }

    /**
     * Risk management - Block if customer number matches pattern
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskCUSTOMERNR($user, $order, $value)
    {
        return $user['additional']['user']['customernumber'] == $value && !empty($value);
    }

    /**
     * Risk management - Block if last name matches pattern
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskLASTNAME($user, $order, $value)
    {
        $value = strtolower($value);

        return
            preg_match("/$value/", strtolower($user['shippingaddress']['lastname']))
            || preg_match("/$value/", strtolower($user['billingaddress']['lastname']))
        ;
    }

    /**
     * Risk management -  Block if subshop id is x
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskSUBSHOP($user, $order, $value)
    {
        return $this->contextService->getShopContext()->getShop()->getId() == $value;
    }

    /**
     * Risk management -  Block if subshop id is not x
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskSUBSHOPNOT($user, $order, $value)
    {
        return $this->contextService->getShopContext()->getShop()->getId() != $value;
    }

    /**
     * Risk management - Block if currency id is not x
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskCURRENCIESISOIS($user, $order, $value)
    {
        return strtolower($this->sSYSTEM->sCurrency['currency']) == strtolower($value);
    }

    /**
     * Risk management - Block if currency id is x
     *
     * @param  $user User data
     * @param  $order Order data
     * @param  $value Value to compare against
     *
     * @return bool Rule validation result
     */
    public function sRiskCURRENCIESISOISNOT($user, $order, $value)
    {
        return strtolower($this->sSYSTEM->sCurrency['currency']) != strtolower($value);
    }

    /**
     * Subscribe / unsubscribe to mailing list
     * Used in the Newsletter frontend controller to manage subscriptions
     *
     * @param string $email       Email address
     * @param bool   $unsubscribe If true, remove email address from mailing list
     * @param int    $groupID     Id of the mailing list group
     *
     * @return array Array with the result of the operation
     */
    public function sNewsletterSubscription($email, $unsubscribe = false, $groupID = null)
    {
        if (empty($unsubscribe)) {
            $errorFlag = [];

            $fields = ['newsletter'];
            foreach ($fields as $field) {
                $fieldData = $this->front->Request()->getPost($field);
                if (isset($fieldData) && empty($fieldData)) {
                    $errorFlag[$field] = true;
                }
            }
            if (!empty($errorFlag)) {
                return [
                    'code' => 5,
                    'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                            ->get('ErrorFillIn', 'Please fill in all red fields'),
                    'sErrorFlag' => $errorFlag,
                ];
            }
        }

        if (empty($groupID)) {
            $groupID = $this->config->get('sNEWSLETTERDEFAULTGROUP');
            $sql = '
                INSERT IGNORE INTO s_campaigns_groups (id, name)
                VALUES (?, ?)
            ';
            $this->db->query($sql, [$groupID, 'Newsletter-Empfänger']);
        }

        $email = trim(strtolower(stripslashes($email)));
        if (empty($email)) {
            return [
                'code' => 6,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureMail', 'Enter eMail address'),
            ];
        }
        if (!$this->emailValidator->isValid($email)) {
            return [
                'code' => 1,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureInvalid', 'Enter valid eMail address'),
            ];
        }
        if (!$unsubscribe) {
            $result = $this->subscribeNewsletter($email, $groupID);
        } else {
            $deleteResult = $this->db->delete('s_campaigns_mailaddresses', ['email = ?' => $email]);
            $updateResult = $this->db->update('s_user', ['newsletter' => 0], ['email = ?' => $email]);

            if ($deleteResult == 0 && $updateResult == 0) {
                $result = [
                    'code' => 4,
                    'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                            ->get('NewsletterFailureNotFound', 'This mail address could not be found'),
                ];
            } else {
                $result = [
                    'code' => 5,
                    'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                            ->get('NewsletterMailDeleted', 'Your mail address was deleted'),
                ];
            }
        }

        if (!empty($result['code']) && in_array($result['code'], [2, 3])) {
            $sql = '
                REPLACE INTO s_campaigns_maildata (
                  email, groupID, salutation, title, firstname,
                  lastname, street, zipcode, city, added
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ';
            $this->db->query($sql, [
                $email,
                $groupID,
                $this->front->Request()->getPost('salutation'),
                $this->front->Request()->getPost('title'),
                $this->front->Request()->getPost('firstname'),
                $this->front->Request()->getPost('lastname'),
                $this->front->Request()->getPost('street'),
                $this->front->Request()->getPost('zipcode'),
                $this->front->Request()->getPost('city'),
            ]);
        } elseif (!empty($unsubscribe)) {
            $this->db->delete('s_campaigns_maildata', ['email = ?' => $email, 'groupID = ?' => $groupID]);
        }

        return $result;
    }

    /**
     * Generate table with german holidays
     *
     * @return bool
     */
    public function sCreateHolidaysTable()
    {
        /** @var \Shopware\Components\HolidayTableUpdater $updater */
        $updater = Shopware()->Container()->get('shopware.holiday_table_updater');
        $updater->update();

        return true;
    }

    /**
     * Get country from its id or iso code
     * Used internally in sAdmin::sGetPremiumShippingcosts()
     *
     * @param int $country Country id or iso code
     *
     * @return array|false Array with country information, including area, or false if empty argument
     */
    public function sGetCountry($country)
    {
        static $cache = [];
        if (empty($country)) {
            return false;
        }
        if (isset($cache[$country])) {
            return $cache[$country];
        }

        if (is_numeric($country)) {
            $sql = $this->db->quoteInto('c.id = ?', $country);
        } elseif (is_string($country)) {
            $sql = $this->db->quoteInto('c.countryiso = ?', $country);
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
        $currencyFactor = empty($this->sSYSTEM->sCurrency['factor']) ? 1 : $this->sSYSTEM->sCurrency['factor'];
        $cache[$country]['shippingfree'] = round($cache[$country]['shippingfree'] * $currencyFactor, 2);

        return $cache[$country] = $this->db->fetchRow($sql) ?: [];
    }

    /**
     * Get a specific payment
     * Used internally in sAdmin::sGetPremiumShippingcosts()
     *
     * @param int $payment Payment mean id or name
     *
     * @return array|false Array with payment mean information, including area, or false if empty argument
     */
    public function sGetPaymentMean($payment)
    {
        static $cache = [];
        if (empty($payment)) {
            return false;
        }
        if (isset($cache[$payment])) {
            return $cache[$payment];
        }
        if (is_numeric($payment)) {
            $sql = $this->db->quoteInto('id = ?', $payment);
        } elseif (is_string($payment)) {
            $sql = $this->db->quoteInto('name = ?', $payment);
        } else {
            return false;
        }

        $sql = "
            SELECT * FROM s_core_paymentmeans
            WHERE $sql
        ";
        $cache[$payment] = $this->db->fetchRow($sql) ?: [];

        $cache[$payment]['country_surcharge'] = [];
        if (!empty($cache[$payment]['surchargestring'])) {
            foreach (explode(';', $cache[$payment]['surchargestring']) as $countrySurcharge) {
                list($key, $value) = explode(':', $countrySurcharge);
                $value = floatval(str_replace(',', '.', $value));
                if (!empty($value)) {
                    $cache[$payment]['country_surcharge'][$key] = $value;
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
     * @param int $stateId   Country state id
     *
     * @return array|false Array with dispatch data for the basket, or false if no basket
     */
    public function sGetDispatchBasket($countryID = null, $paymentID = null, $stateId = null)
    {
        $sql_select = '';
        $premiumShippingBasketSelect = $this->config->get('sPREMIUMSHIPPIUNGASKETSELECT');
        if (!empty($premiumShippingBasketSelect)) {
            $sql_select .= ', ' . $premiumShippingBasketSelect;
        }
        $calculations = $this->db->fetchPairs(
            'SELECT id, calculation_sql
            FROM s_premium_dispatch
            WHERE active = 1 AND calculation = 3'
        );
        if (!empty($calculations)) {
            foreach ($calculations as $dispatchID => $calculation) {
                if (empty($calculation)) {
                    $calculation = $this->db->quote($calculation);
                }
                $sql_select .= ', (' . $calculation . ') as calculation_value_' . $dispatchID;
            }
        }
        if (empty($this->sSYSTEM->sUSERGROUPDATA['tax']) && !empty($this->sSYSTEM->sUSERGROUPDATA['id'])) {
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
            ON u.id = :userId
            AND u.active = 1

            LEFT JOIN s_user_addresses as ub
                ON ub.user_id = u.id
                AND ub.id = :billingAddressId
              
            LEFT JOIN s_user_addresses as us
                ON us.user_id = u.id
                AND us.id = :shippingAddressId
                
            WHERE b.sessionID = :sessionId

            GROUP BY b.sessionID
        ";

        $userId = $this->session->offsetGet('sUserId');
        $sessionId = $this->session->offsetGet('sessionId');
        $basket = $this->db->fetchRow(
            $sql,
            [
                'userId' => $userId,
                'sessionId' => empty($sessionId) ? session_id() : $sessionId,
                'billingAddressId' => $this->getBillingAddressId(),
                'shippingAddressId' => $this->getShippingAddressId(),
            ]
        );
        if ($basket === false) {
            return false;
        }

        $basket['max_tax'] = $this->moduleManager->Basket()->getMaxTax();

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
        $paymentIDs = [];
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
        $mainId = $this->db->fetchOne(
            'SELECT main_id FROM s_core_shops WHERE id = ?',
            [(int) $this->contextService->getShopContext()->getShop()->getId()]
        );
        // Main id is null, so we use the current shop id
        if (is_null($mainId)) {
            $mainId = (int) $this->contextService->getShopContext()->getShop()->getId();
        }
        $basket['basketStateId'] = (int) $stateId;
        $basket['countryID'] = $countryID;
        $basket['paymentID'] = $paymentID;
        $basket['customergroupID'] = (int) $this->sSYSTEM->sUSERGROUPDATA['id'];
        $basket['multishopID'] = $mainId;
        $basket['sessionID'] = $sessionId;

        return $basket;
    }

    /**
     * Get premium dispatch method
     * Used internally, in sOrder and AboCommerce plugin
     *
     * @param int $dispatchID Dispatch method id
     *
     * @return array|false Array with dispatch method data
     */
    public function sGetPremiumDispatch($dispatchID = null)
    {
        $sql = '
            SELECT d.id, `name`, d.description, calculation, status_link,
              surcharge_calculation, bind_shippingfree, shippingfree, tax_calculation,
              t.tax as tax_calculation_value
            FROM s_premium_dispatch d
            LEFT JOIN s_core_tax t
            ON t.id = d.tax_calculation
            WHERE active = 1
            AND d.id = ?
        ';
        $dispatch = $this->db->fetchRow($sql, [$dispatchID]);
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
     * @param int $stateId   Country state id
     *
     * @return array Shipping methods data
     */
    public function sGetPremiumDispatches($countryID = null, $paymentID = null, $stateId = null)
    {
        $this->sCreateHolidaysTable();

        $basket = $this->sGetDispatchBasket($countryID, $paymentID, $stateId);

        $statements = $this->db->fetchPairs("
            SELECT id, bind_sql
            FROM s_premium_dispatch
            WHERE active = 1 AND type IN (0)
            AND bind_sql IS NOT NULL AND bind_sql != ''
        ");

        if (empty($basket)) {
            return [];
        }

        $sql_where = '';
        foreach ($statements as $dispatchID => $statement) {
            $sql_where .= " AND ( d.id != $dispatchID OR ($statement)) ";
        }

        $sql_basket = [];
        foreach ($basket as $key => $value) {
            $sql_basket[] = $this->db->quote($value) . " as `$key`";
        }
        $sql_basket = implode(', ', $sql_basket);

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
                AND b.sessionID='{$this->session->offsetGet('sessionId')}'
                GROUP BY dc.dispatchID
            ) as dk
            ON dk.dispatchID=d.id

            LEFT JOIN s_user u
            ON u.id=b.userID
            AND u.active=1

            LEFT JOIN s_user_addresses as ub
                ON ub.user_id = u.id
                AND ub.id = :billingAddressId
              
            LEFT JOIN s_user_addresses as us
                ON us.user_id = u.id
                AND us.id = :shippingAddressId

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

        $dispatches = $this->db->fetchAssoc(
            $sql,
            [
                'billingAddressId' => $this->getBillingAddressId(),
                'shippingAddressId' => $this->getShippingAddressId(),
            ]
        );

        if (empty($dispatches)) {
            $sql = '
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
            ';
            $dispatches = $this->db->fetchAssoc($sql);
        }

        $names = [];
        foreach ($dispatches as $dispatchID => $dispatch) {
            if (in_array($dispatch['name'], $names)) {
                unset($dispatches[$dispatchID]);
            } else {
                $names[] = $dispatch['name'];
            }
        }
        unset($names);

        $object = $this->sGetDispatchTranslation();
        foreach ($dispatches as &$dispatch) {
            if (!empty($object[$dispatch['id']]['dispatch_name'])) {
                $dispatch['name'] = $object[$dispatch['id']]['dispatch_name'];
            }
            if (!empty($object[$dispatch['id']]['dispatch_description'])) {
                $dispatch['description'] = $object[$dispatch['id']]['dispatch_description'];
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
     *
     * @return array|false
     */
    public function sGetPremiumDispatchSurcharge($basket, $type = 2)
    {
        if (empty($basket)) {
            return false;
        }
        $type = (int) $type;

        $statements = $this->db->fetchPairs('
                SELECT id, bind_sql
                FROM s_premium_dispatch
                WHERE active = 1 AND type = ?
                AND bind_sql IS NOT NULL
            ',
            [$type]
        );

        $sql_where = '';
        foreach ($statements as $dispatchID => $statement) {
            $sql_where .= "
            AND ( d.id!=$dispatchID OR ($statement))
            ";
        }
        $sql_basket = [];
        foreach ($basket as $key => $value) {
            $sql_basket[] = $this->db->quote($value) . " as `$key`";
        }
        $sql_basket = implode(', ', $sql_basket);

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
                AND b.sessionID='{$this->session->offsetGet('sessionId')}'
                GROUP BY dc.dispatchID
            ) as dk
            ON dk.dispatchID=d.id

            LEFT JOIN s_user u
            ON u.id=b.userID
            AND u.active=1

            LEFT JOIN s_user_addresses as ub
                ON ub.user_id = u.id
                AND ub.id = :billingAddressId
              
            LEFT JOIN s_user_addresses as us
                ON us.user_id = u.id
                AND us.id = :shippingAddressId

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

        return $this->calculateDispatchSurcharge(
            $basket,
            $this->db->fetchAll(
                $sql,
                [
                    'billingAddressId' => $this->getBillingAddressId(),
                    'shippingAddressId' => $this->getShippingAddressId(),
                ]
            )
        );
    }

    /**
     * Get shipping costs
     * Used in sBasket and Checkout controller
     *
     * @param array $country Array with a single country details
     *
     * @return array|false Array with shipping costs data, or false on failure
     */
    public function sGetPremiumShippingcosts($country = null)
    {
        $currencyFactor = empty($this->sSYSTEM->sCurrency['factor']) ? 1 : $this->sSYSTEM->sCurrency['factor'];

        // Determinate tax automatically
        $taxAutoMode = $this->config->get('sTAXAUTOMODE');
        if (!empty($taxAutoMode)) {
            $discount_tax = $this->moduleManager->Basket()->getMaxTax();
        } else {
            $discount_tax = $this->config->get('sDISCOUNTTAX');
            $discount_tax = empty($discount_tax) ? 0 : (float) str_replace(',', '.', $discount_tax);
        }

        $surcharge_ordernumber = $this->config->get('sPAYMENTSURCHARGEABSOLUTENUMBER', 'PAYMENTSURCHARGEABSOLUTENUMBER');
        $discount_basket_ordernumber = $this->config->get('sDISCOUNTNUMBER', 'DISCOUNT');
        $discount_ordernumber = $this->config->get('sSHIPPINGDISCOUNTNUMBER', 'SHIPPINGDISCOUNT');
        $percent_ordernumber = $this->config->get('sPAYMENTSURCHARGENUMBER', 'PAYMENTSURCHARGE');

        $this->db->delete('s_order_basket', [
            'sessionID = ?' => $this->session->offsetGet('sessionId'),
            'modus IN (?)' => [3, 4],
            'ordernumber IN (?)' => [
                $surcharge_ordernumber,
                $discount_ordernumber,
                $percent_ordernumber,
                $discount_basket_ordernumber,
            ],
        ]);

        $basket = $this->sGetDispatchBasket(empty($country['id']) ? null : $country['id']);
        if (empty($basket) || $basket['count_article'] == 0) {
            return false;
        }
        $country = $this->sGetCountry($basket['countryID']);
        if (empty($country)) {
            return false;
        }
        $payment = $this->sGetPaymentMean($basket['paymentID']);
        if (empty($payment)) {
            return false;
        }

        $amount = $this->db->fetchOne('
                SELECT SUM((CAST(price as DECIMAL(10,2))*quantity)/currencyFactor) as amount
                FROM s_order_basket
                WHERE sessionID = ?
                GROUP BY sessionID
            ',
            [$this->session->offsetGet('sessionId')]
        );

        $this->handleBasketDiscount(
            $amount,
            $currencyFactor,
            $discount_tax
        );

        $this->handleDispatchDiscount(
            $basket,
            $currencyFactor,
            $discount_tax
        );

        $dispatch = $this->sGetPremiumDispatch((int) $this->session->offsetGet('sDispatch'));

        $payment = $this->handlePaymentMeanSurcharge(
            $country,
            $payment,
            $currencyFactor,
            $dispatch,
            $discount_tax
        );

        if (empty($dispatch)) {
            return ['brutto' => 0, 'netto' => 0];
        }

        if (empty($this->sSYSTEM->sUSERGROUPDATA['tax']) && !empty($this->sSYSTEM->sUSERGROUPDATA['id'])) {
            $dispatch['shippingfree'] = round($dispatch['shippingfree'] / (100 + $discount_tax) * 100, 2);
        }

        if ((!empty($dispatch['shippingfree']) && $dispatch['shippingfree'] <= $basket['amount_display'])
            || empty($basket['count_article'])
            || (!empty($basket['shippingfree']) && empty($dispatch['bind_shippingfree']))
        ) {
            if (empty($dispatch['surcharge_calculation']) && !empty($payment['surcharge'])) {
                return [
                    'brutto' => $payment['surcharge'],
                    'netto' => round($payment['surcharge'] * 100 / (100 + $this->config->get('sTAXSHIPPING')), 2),
                ];
            }

            return ['brutto' => 0, 'netto' => 0];
        }

        if (empty($dispatch['calculation'])) {
            $from = round($basket['weight'], 3);
        } elseif ($dispatch['calculation'] == 1) {
            $from = round($basket['amount'], 2);
        } elseif ($dispatch['calculation'] == 2) {
            $from = round($basket['count_article']);
        } elseif ($dispatch['calculation'] == 3) {
            $from = round($basket['calculation_value_' . $dispatch['id']], 2);
        } else {
            return false;
        }
        $result = $this->db->fetchRow('
            SELECT `value` , `factor`
            FROM `s_premium_shippingcosts`
            WHERE `from` <= ?
            AND `dispatchID` = ?
            ORDER BY `from` DESC
            LIMIT 1',
            [$from, $dispatch['id']]
        );
        if ($result === false) {
            return false;
        }

        if (!empty($dispatch['shippingfree'])) {
            $result['shippingfree'] = round($dispatch['shippingfree'] * $currencyFactor, 2);
            $difference = round(($dispatch['shippingfree'] - $basket['amount_display']) * $currencyFactor, 2);
            $result['difference'] = [
                'float' => $difference,
                'formated' => $this->moduleManager->Articles()->sFormatPrice($difference),
            ];
        }
        $result['brutto'] = $result['value'];
        if (!empty($result['factor'])) {
            $result['brutto'] += $result['factor'] / 100 * $from;
        }
        $result['surcharge'] = $this->sGetPremiumDispatchSurcharge($basket);
        if (!empty($result['surcharge'])) {
            $result['brutto'] += $result['surcharge'];
        }
        $result['brutto'] *= $currencyFactor;
        $result['brutto'] = round($result['brutto'], 2);
        if (!empty($payment['surcharge'])
            && $dispatch['surcharge_calculation'] != 2
            && (empty($basket['shippingfree']) || empty($dispatch['surcharge_calculation']))
        ) {
            $result['surcharge'] = $payment['surcharge'];
            $result['brutto'] += $result['surcharge'];
        }
        if ($result['brutto'] < 0) {
            return ['brutto' => 0, 'netto' => 0];
        }
        if (empty($dispatch['tax_calculation'])) {
            $result['tax'] = $basket['max_tax'];
        } else {
            $result['tax'] = $dispatch['tax_calculation_value'];
        }
        $result['tax'] = (float) $result['tax'];
        $result['netto'] = round($result['brutto'] * 100 / (100 + $result['tax']), 2);

        return $result;
    }

    /**
     * Helper function for sLogin
     * Called when provided user data is correct
     * Logs in the user
     *
     * @param $getUser
     * @param $email
     * @param $password
     * @param $isPreHashed
     * @param $encoderName
     * @param $plaintext
     * @param $hash
     */
    protected function loginUser($getUser, $email, $password, $isPreHashed, $encoderName, $plaintext, $hash)
    {
        $this->regenerateSessionId();

        $this->db->update(
            's_user',
            [
                'lastlogin' => new Zend_Date(),
                'failedlogins' => 0,
                'lockeduntil' => null,
                'sessionID' => $this->session->offsetGet('sessionId'),
            ],
            [
                'id = ?' => $getUser['id'],
            ]
        );

        $this->eventManager->notify(
            'Shopware_Modules_Admin_Login_Successful',
            ['subject' => $this, 'email' => $email, 'password' => $password, 'user' => $getUser]
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
                [
                    'password' => $hash,
                    'encoder' => $encoderName,
                ],
                'id = ' . $userId
            );
        }

        $this->session->offsetSet('sUserMail', $email);
        $this->session->offsetSet('sUserPassword', $hash);
        $this->session->offsetSet('sUserId', $getUser['id']);

        $this->sCheckUser();
    }

    /**
     * Sends a mail to the given recipient with a given template.
     * If the opt in parameter is set, the sConfirmLink variable will be filled by the opt in link.
     *
     * @param $recipient
     * @param $template
     * @param string $optIn
     */
    private function sendMail($recipient, $template, $optIn = '')
    {
        $context = [];

        if (!empty($optIn)) {
            $context['sConfirmLink'] = $optIn;
        }

        $mail = Shopware()->TemplateMail()->createMail($template, $context);
        $mail->addTo($recipient);
        $mail->send();
    }

    /**
     * Regenerates session id and updates references in the db
     * Used internally by sAdmin::sLogin
     */
    private function regenerateSessionId()
    {
        $oldSessionId = session_id();
        session_regenerate_id(true);
        $newSessionId = session_id();

        // close and restart session to make sure the db session handler writes updates.
        session_write_close();
        session_start();

        $this->sSYSTEM->sSESSION_ID = $newSessionId;
        $this->session->offsetSet('sessionId', $newSessionId);
        Shopware()->Container()->reset('SessionId');
        Shopware()->Container()->set('SessionId', $newSessionId);

        $this->eventManager->notify(
            'Shopware_Modules_Admin_Regenerate_Session_Id',
            [
                'subject' => $this,
                'oldSessionId' => $oldSessionId,
                'newSessionId' => $newSessionId,
            ]
        );

        $sessions = [
            's_order_basket' => 'sessionID',
            's_user' => 'sessionID',
            's_order_comparisons' => 'sessionID',
        ];

        foreach ($sessions as $tableName => $column) {
            $this->db->update(
                $tableName,
                [$column => $newSessionId],
                $column . ' = ' . $this->db->quote($oldSessionId));
        }
    }

    /**
     * Overwrite sUserData['billingaddress'] with chosen address
     *
     * @param array $userData
     *
     * @return array
     */
    private function overwriteBillingAddress(array $userData)
    {
        // temporarily overwrite billing address
        if (!$this->session->offsetGet('checkoutBillingAddressId') || Shopware()->Front()->Request()->getControllerName() !== 'checkout') {
            return $userData;
        }

        $addressRepository = Shopware()->Models()->getRepository(Address::class);
        $addressId = $this->session->offsetGet('checkoutBillingAddressId');

        try {
            $legacyAddress = $this->convertToLegacyAddressArray(
                $addressRepository->getOneByUser($addressId, $this->session->offsetGet('sUserId'))
            );

            $userData['billingaddress'] = array_merge($userData['billingaddress'], $legacyAddress);
            $userData = $this->completeUserCountryData($userData);
        } catch (\Exception $ex) {
            // no need to overwrite default billing address
            $this->session->offsetUnset('checkoutBillingAddressId');
        }

        return $userData;
    }

    /**
     * Overwrite sUserData['shippingaddress'] with chosen address
     *
     * @param array $userData
     *
     * @return array
     */
    private function overwriteShippingAddress(array $userData)
    {
        // temporarily overwrite shipping address
        if (!$this->session->offsetGet('checkoutShippingAddressId') || Shopware()->Front()->Request()->getControllerName() !== 'checkout') {
            return $userData;
        }

        $addressRepository = Shopware()->Models()->getRepository(Address::class);
        $addressId = $this->session->offsetGet('checkoutShippingAddressId');

        try {
            $legacyAddress = $this->convertToLegacyAddressArray(
                $addressRepository->getOneByUser($addressId, $this->session->offsetGet('sUserId'))
            );

            $userData['shippingaddress'] = array_merge($userData['shippingaddress'], $legacyAddress);
            $userData = $this->completeUserCountryData($userData, true);
        } catch (\Exception $ex) {
            // no need to overwrite default shipping address
            $this->session->offsetUnset('checkoutShippingAddressId');
        }

        return $userData;
    }

    /**
     * Converts an address to the array key structure of a legacy billing or shipping address
     *
     * @param Address $address
     *
     * @return array
     */
    private function convertToLegacyAddressArray(Address $address)
    {
        $output = Shopware()->Models()->toArray($address);

        $output = array_merge($output, [
            'id' => $address->getId(),
            'userID' => $address->getCustomer()->getId(),
            'company' => $address->getCompany(),
            'department' => $address->getDepartment(),
            'salutation' => $address->getSalutation(),
            'title' => $address->getTitle(),
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getStreet(),
            'zipcode' => $address->getZipcode(),
            'city' => $address->getCity(),
            'phone' => $address->getPhone(),
            'countryID' => $address->getCountry()->getId(),
            'stateID' => $address->getState() ? $address->getState()->getId() : null,
            'ustid' => $address->getVatId(),
            'additional_address_line1' => $address->getAdditionalAddressLine1(),
            'additional_address_line2' => $address->getAdditionalAddressLine2(),
            'attributes' => [],
        ]);

        if ($address->getAttribute()) {
            $data = Shopware()->Models()->toArray($address->getAttribute());

            $output['attributes'] = $data;
        }

        return $output;
    }

    /**
     * @param array $userData
     * @param bool  $isShippingAddress changes keys in sUserData
     *
     * @return array
     */
    private function completeUserCountryData(array $userData, $isShippingAddress = false)
    {
        $sql = <<<'SQL'
SELECT c.*, a.name AS countryarea
FROM s_core_countries c
LEFT JOIN s_core_countries_areas a ON a.id = c.areaID AND a.active = 1
WHERE c.id = ?
SQL;

        $addressKey = $isShippingAddress ? 'shippingaddress' : 'billingaddress';
        $countryKey = $isShippingAddress ? 'countryShipping' : 'country';
        $stateKey = $isShippingAddress ? 'stateShipping' : 'state';

        $userData['additional'][$countryKey] = Shopware()->Container()->get('dbal_connection')
            ->executeQuery($sql, [$userData[$addressKey]['countryID']])
            ->fetch(\PDO::FETCH_ASSOC);

        $userData['additional'][$stateKey] = Shopware()->Container()->get('dbal_connection')
            ->executeQuery('SELECT *, name as statename FROM s_core_countries_states WHERE id = ?', [$userData[$addressKey]['stateID']])
            ->fetch(\PDO::FETCH_ASSOC);

        // get translations
        $userData['additional'][$countryKey] = $this->sGetCountryTranslation($userData['additional'][$countryKey]);
        $userData['additional'][$stateKey] = $this->sGetCountryStateTranslation($userData['additional'][$stateKey]);

        // session
        if ($isShippingAddress) {
            $this->session->offsetSet('sCountry', $userData['additional'][$countryKey]['id']);
            $this->session->offsetSet('sState', $userData['additional'][$stateKey]['id']);
            $this->session->offsetSet('sArea', $userData['additional'][$countryKey]['areaID']);
        }

        return $userData;
    }

    /**
     * Helper function for sLogin
     * Called when provided user data is incorrect
     * Handles account lockdown detection and brute force protection
     *
     * @param $addScopeSql
     * @param $email
     * @param $sErrorMessages
     * @param $password
     *
     * @return array
     */
    private function failedLoginUser($addScopeSql, $email, $sErrorMessages, $password)
    {
        // Check if account is disabled
        $sql = 'SELECT id FROM s_user WHERE email=? AND active=0 ' . $addScopeSql;
        $getUser = $this->db->fetchOne($sql, [$email]);
        if ($getUser) {
            $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/account/internalMessages')
                ->get(
                    'LoginFailureActive',
                    'Your account is disabled. Please contact us.'
                );
        } else {
            $getLockedUntilTime = $this->db->fetchOne(
                'SELECT 1 FROM s_user WHERE email = ? AND lockeduntil > NOW()',
                [$email]
            );
            if (!empty($getLockedUntilTime)) {
                $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/account/internalMessages')
                    ->get(
                        'LoginFailureLocked',
                        'Too many failed logins. Your account was temporary deactivated.'
                    );
            } else {
                $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/account/internalMessages')
                    ->get('LoginFailure', 'Wrong email or password');
            }
        }

        // Prevent brute force login attempts
        if (!empty($email)) {
            $sql = '
                UPDATE s_user SET
                    failedlogins = failedlogins + 1,
                    lockeduntil = IF(
                        failedlogins > 4,
                        DATE_ADD(NOW(), INTERVAL (failedlogins + 1) * 30 SECOND),
                        NULL
                    )
                WHERE email = ? ' . $addScopeSql;
            $this->db->query($sql, [$email]);
        }

        $this->eventManager->notify(
            'Shopware_Modules_Admin_Login_Failure',
            ['subject' => $this, 'email' => $email, 'password' => $password, 'error' => $sErrorMessages]
        );

        $this->session->offsetUnset('sUserMail');
        $this->session->offsetUnset('sUserPassword');
        $this->session->offsetUnset('sUserId');

        return $sErrorMessages;
    }

    /**
     * Helper method for sAdmin::sGetOpenOrderData()
     *
     * @param $orderValue
     * @param $getOrders
     * @param $orderKey
     *
     * @return mixed
     */
    private function processOpenOrderDetails($orderValue, $getOrders, $orderKey)
    {
        $getOrderDetails = $this->db->fetchAll(
            'SELECT * FROM s_order_details WHERE orderID = ? ORDER BY id ASC',
            [$orderValue['id']]
        );

        if (!count($getOrderDetails)) {
            unset($getOrders[$orderKey]);

            return $getOrders;
        }

        $context = $this->contextService->getShopContext();
        $orderArticleOrderNumbers = array_column($getOrderDetails, 'articleordernumber');
        $listProducts = Shopware()->Container()->get('shopware_storefront.list_product_service')->getList($orderArticleOrderNumbers, $context);
        $listProducts = Shopware()->Container()->get('legacy_struct_converter')->convertListProductStructList($listProducts);

        foreach ($listProducts as &$listProduct) {
            $listProduct = array_merge($listProduct, $listProduct['prices'][0]);
        }

        foreach ($getOrderDetails as $orderDetailsKey => $orderDetailsValue) {
            $getOrderDetails[$orderDetailsKey]['amount'] = $this->moduleManager->Articles()
                ->sFormatPrice(round($orderDetailsValue['price'] * $orderDetailsValue['quantity'], 2));
            $getOrderDetails[$orderDetailsKey]['price'] = $this->moduleManager->Articles()
                ->sFormatPrice($orderDetailsValue['price']);
            $getOrderDetails[$orderDetailsKey]['active'] = 0;

            $tmpArticle = null;
            if (!empty($listProducts[$orderDetailsValue['articleordernumber']])) {
                $tmpArticle = $listProducts[$orderDetailsValue['articleordernumber']];
            }

            if (!empty($tmpArticle) && is_array($tmpArticle)) {
                // Set article in activate state
                $getOrderDetails[$orderDetailsKey]['active'] = 1;
                $getOrderDetails[$orderDetailsKey]['article'] = $tmpArticle;
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

                $getOrderDetails[$orderDetailsKey]['currentHas_pseudoprice'] = $tmpArticle['has_pseudoprice'];
            }

            // Check for serial
            if ($getOrderDetails[$orderDetailsKey]['esdarticle']) {
                $numbers = [];
                $getSerial = $this->db->fetchAll(
                    'SELECT serialnumber
                    FROM s_articles_esd_serials, s_order_esd
                    WHERE userID = ?
                    AND orderID = ?
                    AND orderdetailsID = ?
                    AND s_order_esd.serialID = s_articles_esd_serials.id',
                    [
                        $this->session->offsetGet('sUserId'),
                        $orderValue['id'],
                        $orderDetailsValue['id'],
                    ]
                );
                foreach ($getSerial as $serial) {
                    $numbers[] = $serial['serialnumber'];
                }
                $getOrderDetails[$orderDetailsKey]['serial'] = implode(',', $numbers);
                $getOrderDetails[$orderDetailsKey]['esdLink'] = $this->config->get('sBASEFILE')
                    . '?sViewport=account&sAction=download&esdID='
                    . $orderDetailsValue['id'];
            }
        }
        $getOrders[$orderKey]['activeBuyButton'] = 1;
        $getOrders[$orderKey]['details'] = $getOrderDetails;

        return $getOrders;
    }

    /**
     * Helper function for sAdmin::sGetUserData()
     * Gets user country data
     *
     * @param $userData
     * @param $userId
     *
     * @return array
     */
    private function getUserCountryData($userData, $userId)
    {
        // Query country information
        $userData['additional']['country'] = $this->db->fetchRow(
        'SELECT c.*, a.name AS countryarea
          FROM s_core_countries c
          LEFT JOIN s_core_countries_areas a
           ON a.id = c.areaID AND a.active = 1
          WHERE c.id = ?',
            [$userData['billingaddress']['countryID']]
        );

        $userData['additional']['country'] = $userData['additional']['country'] ?: [];
        // State selection
        $userData['additional']['state'] = $this->db->fetchRow(
            'SELECT *, name as statename FROM s_core_countries_states WHERE id = ?',
            [$userData['billingaddress']['stateID']]
        );
        $userData['additional']['state'] = $userData['additional']['state'] ?: [];

        $userData['additional']['country'] = $this->sGetCountryTranslation($userData['additional']['country']);
        $userData['additional']['state'] = $this->sGetCountryStateTranslation($userData['additional']['state']);

        $additional = $this->db->fetchRow(
            'SELECT * FROM s_user WHERE id = ?',
            [$userId]
        );
        $additional = $additional ?: [];
        $attributes = $this->attributeLoader->load('s_user_attributes', $userId) ?: [];
        $userData['additional']['user'] = array_merge($attributes, $additional);

        return $userData;
    }

    /**
     * Helper function for sAdmin::sGetUserData()
     * Gets user shipping data (address, payment)
     *
     * @param $userId
     * @param $userData
     * @param $countryQuery
     *
     * @return mixed
     */
    private function getUserShippingData($userId, $userData, $countryQuery)
    {
        $entityManager = Shopware()->Container()->get('models');
        $customer = $entityManager->find(Shopware\Models\Customer\Customer::class, $userId);
        $shipping = $this->convertToLegacyAddressArray($customer->getDefaultShippingAddress());
        $shipping['attributes'] = $this->attributeLoader->load('s_user_addresses_attributes', $shipping['id']) ?: [];
        $userData['shippingaddress'] = $shipping;

        if (!isset($userData['shippingaddress']['firstname'])) {
            $userData['shippingaddress'] = $userData['billingaddress'];
            $userData['shippingaddress']['eqalBilling'] = true;
        }

        if (empty($userData['shippingaddress']['countryID'])) {
            $targetCountryId = $userData['billingaddress']['countryID'];
        } else {
            $targetCountryId = $userData['shippingaddress']['countryID'];
        }

        $userData['additional']['countryShipping'] = $this->db->fetchRow(
            $countryQuery,
            [$targetCountryId]
        );
        $userData['additional']['countryShipping'] = $userData['additional']['countryShipping'] ?: [];
        $userData['additional']['countryShipping'] = $this->sGetCountryTranslation(
            $userData['additional']['countryShipping']
        );
        $this->session->offsetSet('sCountry', $userData['additional']['countryShipping']['id']);

        // State selection
        $userData['additional']['stateShipping'] = $this->db->fetchRow(
            'SELECT *, name as statename FROM s_core_countries_states WHERE id = ?',
            [$userData['shippingaddress']['stateID']]
        );
        $userData['additional']['stateShipping'] = $userData['additional']['stateShipping'] ?: [];
        $userData['additional']['stateShipping'] = $this->sGetCountryStateTranslation($userData['additional']['stateShipping']);
        // Add stateId to session
        $this->session->offsetSet('sState', $userData['additional']['stateShipping']['id']);
        // Add areaId to session
        $this->session->offsetSet('sArea', $userData['additional']['countryShipping']['areaID']);

        return $userData;
    }

    /**
     * Helper function for sAdmin::sGetUserData()
     * Gets user billing data
     *
     * @param int   $userId
     * @param array $userData
     *
     * @return array
     */
    private function getUserBillingData($userId, $userData)
    {
        $entityManager = Shopware()->Container()->get('models');
        $customer = $entityManager->find(Customer::class, $userId);
        $billing = $this->convertToLegacyAddressArray($customer->getDefaultBillingAddress());
        $billing['attributes'] = $this->attributeLoader->load('s_user_addresses_attributes', $billing['id']) ?: [];
        $userData['billingaddress'] = $billing;

        return $userData;
    }

    /**
     * Helper method for sAdmin::sNewsletterSubscription
     * Subscribes the provided email address to the newsletter group
     *
     * @param $email
     * @param $groupID
     *
     * @return array|int
     */
    private function subscribeNewsletter($email, $groupID)
    {
        $result = $this->db->fetchAll(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );

        if ($result === false) {
            $result = [
                'code' => 10,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('UnknownError', 'Unknown error'),
            ];

            return $result;
        } elseif (count($result) === 0) {
            $customer = $this->db->fetchOne(
                'SELECT id FROM s_user WHERE email = ? LIMIT 1',
                [$email]
            );

            $result = $this->db->insert(
                's_campaigns_mailaddresses',
                [
                    'customer' => (int) !empty($customer),
                    'groupID' => $groupID,
                    'email' => $email,
                    'added' => $this->getCurrentDateFormatted(),
                ]
            );

            if ($result === false) {
                $result = [
                    'code' => 10,
                    'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                            ->get('UnknownError', 'Unknown error'),
                ];

                return $result;
            }
        }

        $result = [
            'code' => 3,
            'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                ->get('NewsletterSuccess', 'Thank you for receiving our newsletter'),
        ];

        return $result;
    }

    /**
     * Helper method for sAdmin::sGetPremiumDispatchSurcharge()
     * Calculates the surcharge for the current basket and dispatches
     *
     * @param $basket
     * @param $dispatches
     *
     * @return float
     */
    private function calculateDispatchSurcharge($basket, $dispatches)
    {
        $surcharge = 0;

        if (empty($dispatches)) {
            return $surcharge;
        }

        foreach ($dispatches as $dispatch) {
            if (empty($dispatch['calculation'])) {
                $from = round($basket['weight'], 3);
            } elseif ($dispatch['calculation'] == 1) {
                if (($this->config->get('sARTICLESOUTPUTNETTO') && !$this->sSYSTEM->sUSERGROUPDATA['tax'])
                    || (!$this->sSYSTEM->sUSERGROUPDATA['tax'] && $this->sSYSTEM->sUSERGROUPDATA['id'])
                ) {
                    $from = round($basket['amount_net'], 2);
                } else {
                    $from = round($basket['amount'], 2);
                }
            } elseif ($dispatch['calculation'] == 2) {
                $from = round($basket['count_article']);
            } elseif ($dispatch['calculation'] == 3) {
                $from = round($basket['calculation_value_' . $dispatch['id']]);
            } else {
                continue;
            }
            $result = $this->db->fetchRow(
                'SELECT `value` , factor
                FROM s_premium_shippingcosts
                WHERE `from` <= ?
                AND dispatchID = ?
                ORDER BY `from` DESC
                LIMIT 1',
                [$from, $dispatch['id']]
            );

            if ($result === false) {
                continue;
            }
            $surcharge += $result['value'];
            if (!empty($result['factor'])) {
                //die($result["factor"].">".$from);
                $surcharge += $result['factor'] / 100 * $from;
            }
        }

        return $surcharge;
    }

    /**
     * Helper method for sAdmin::sGetPremiumShippingcosts()
     * Calculates basket discount
     *
     * @param $amount
     * @param $currencyFactor
     * @param $discount_tax
     */
    private function handleBasketDiscount($amount, $currencyFactor, $discount_tax)
    {
        $discount_basket_ordernumber = $this->config->get('sDISCOUNTNUMBER', 'DISCOUNT');
        $discount_basket_name = $this->snippetManager
            ->getNamespace('backend/static/discounts_surcharges')
            ->get('discount_name', 'Warenkorbrabatt');

        $basket_discount = $this->db->fetchOne(
            'SELECT basketdiscount
                FROM s_core_customergroups_discounts
                WHERE groupID = ?
                AND basketdiscountstart <= ?
                ORDER BY basketdiscountstart DESC',
            [$this->sSYSTEM->sUSERGROUPDATA['id'], $amount]
        );

        if (!empty($basket_discount)) {
            $percent = $basket_discount;
            $basket_discount = round($basket_discount / 100 * ($amount * $currencyFactor), 2);

            if (empty($this->sSYSTEM->sUSERGROUPDATA['tax']) && !empty($this->sSYSTEM->sUSERGROUPDATA['id'])) {
                $basket_discount_net = $basket_discount;
            } else {
                $basket_discount_net = round($basket_discount / (100 + $discount_tax) * 100, 2);
            }
            $tax_rate = $discount_tax;
            $basket_discount_net = $basket_discount_net * -1;
            $basket_discount = $basket_discount * -1;

            $this->db->insert(
                's_order_basket',
                [
                    'sessionID' => $this->session->offsetGet('sessionId'),
                    'articlename' => '- ' . $percent . ' % ' . $discount_basket_name,
                    'articleID' => 0,
                    'ordernumber' => $discount_basket_ordernumber,
                    'quantity' => 1,
                    'price' => $basket_discount,
                    'netprice' => $basket_discount_net,
                    'tax_rate' => $tax_rate,
                    'datum' => new Zend_Date(),
                    'modus' => 3,
                    'currencyFactor' => $currencyFactor,
                ]
            );
        }
    }

    /**
     * Helper method for sAdmin::sGetPremiumShippingcosts()
     * Calculates dispatch discount
     *
     * @param $basket
     * @param $currencyFactor
     * @param $discount_tax
     */
    private function handleDispatchDiscount($basket, $currencyFactor, $discount_tax)
    {
        $discount_ordernumber = $this->config->get('sSHIPPINGDISCOUNTNUMBER', 'SHIPPINGDISCOUNT');
        $discount_name = $this->snippetManager
            ->getNamespace('backend/static/discounts_surcharges')
            ->get('shipping_discount_name', 'Basket discount');

        $discount = $this->sGetPremiumDispatchSurcharge($basket, 3);

        if (!empty($discount)) {
            $discount *= -$currencyFactor;

            if (empty($this->sSYSTEM->sUSERGROUPDATA['tax']) && !empty($this->sSYSTEM->sUSERGROUPDATA['id'])) {
                $discount_net = $discount;
            } else {
                $discount_net = round($discount / (100 + $discount_tax) * 100, 2);
            }
            $tax_rate = $discount_tax;

            $this->db->insert(
                's_order_basket',
                [
                    'sessionID' => $this->session->offsetGet('sessionId'),
                    'articlename' => $discount_name,
                    'articleID' => 0,
                    'ordernumber' => $discount_ordernumber,
                    'quantity' => 1,
                    'price' => $discount,
                    'netprice' => $discount_net,
                    'tax_rate' => $tax_rate,
                    'datum' => new Zend_Date(),
                    'modus' => 4,
                    'currencyFactor' => $currencyFactor,
                ]
            );
        }
    }

    /**
     * Helper method for sAdmin::sGetPremiumShippingcosts()
     * Calculates payment mean surcharge
     *
     * @param $country
     * @param $payment
     * @param $currencyFactor
     * @param $dispatch
     * @param $discount_tax
     */
    private function handlePaymentMeanSurcharge($country, $payment, $currencyFactor, $dispatch, $discount_tax)
    {
        $surcharge_name = $this->snippetManager
            ->getNamespace('backend/static/discounts_surcharges')
            ->get('payment_surcharge_absolute', 'Surcharge for payment');
        $surcharge_ordernumber = $this->config->get('sPAYMENTSURCHARGEABSOLUTENUMBER', 'PAYMENTSURCHARGEABSOLUTENUMBER');
        $percent_ordernumber = $this->config->get('sPAYMENTSURCHARGENUMBER', 'PAYMENTSURCHARGE');

        // Country surcharge
        if (!empty($payment['country_surcharge'][$country['countryiso']])) {
            $payment['surcharge'] += $payment['country_surcharge'][$country['countryiso']];
        }
        $payment['surcharge'] = round($payment['surcharge'] * $currencyFactor, 2);

        // Fixed surcharge
        if (!empty($payment['surcharge']) && (empty($dispatch) || $dispatch['surcharge_calculation'] == 3)) {
            $surcharge = round($payment['surcharge'], 2);
            $payment['surcharge'] = 0;
            if (empty($this->sSYSTEM->sUSERGROUPDATA['tax']) && !empty($this->sSYSTEM->sUSERGROUPDATA['id'])) {
                $surcharge_net = $surcharge;
                //$tax_rate = 0;
            } else {
                $surcharge_net = round($surcharge / (100 + $discount_tax) * 100, 2);
            }

            $tax_rate = $discount_tax;
            $this->db->insert(
                's_order_basket',
                [
                    'sessionID' => $this->session->offsetGet('sessionId'),
                    'articlename' => $surcharge_name,
                    'articleID' => 0,
                    'ordernumber' => $surcharge_ordernumber,
                    'quantity' => 1,
                    'price' => $surcharge,
                    'netprice' => $surcharge_net,
                    'tax_rate' => $tax_rate,
                    'datum' => new Zend_Date(),
                    'modus' => 4,
                    'currencyFactor' => $currencyFactor,
                ]
            );
        }

        // Percentage surcharge
        if (!empty($payment['debit_percent']) && (empty($dispatch) || $dispatch['surcharge_calculation'] != 2)) {
            $amount = $this->db->fetchOne(
                'SELECT SUM(quantity*price) as amount
                FROM s_order_basket
                WHERE sessionID = ? GROUP BY sessionID',
                [$this->session->offsetGet('sessionId')]
            );

            $percent = round($amount / 100 * $payment['debit_percent'], 2);

            if ($percent > 0) {
                $percent_name = $this->snippetManager
                    ->getNamespace('backend/static/discounts_surcharges')
                    ->get('payment_surcharge_add');
            } else {
                $percent_name = $this->snippetManager
                    ->getNamespace('backend/static/discounts_surcharges')
                    ->get('payment_surcharge_dev');
            }

            if (empty($this->sSYSTEM->sUSERGROUPDATA['tax']) && !empty($this->sSYSTEM->sUSERGROUPDATA['id'])) {
                $percent_net = $percent;
            } else {
                $percent_net = round($percent / (100 + $discount_tax) * 100, 2);
            }

            $tax_rate = $discount_tax;
            $this->db->insert(
                's_order_basket',
                [
                    'sessionID' => $this->session->offsetGet('sessionId'),
                    'articlename' => $percent_name,
                    'articleID' => 0,
                    'ordernumber' => $percent_ordernumber,
                    'quantity' => 1,
                    'price' => $percent,
                    'netprice' => $percent_net,
                    'tax_rate' => $tax_rate,
                    'datum' => new Zend_Date(),
                    'modus' => 4,
                    'currencyFactor' => $currencyFactor,
                ]
            );
        }

        return $payment;
    }

    /**
     * Convenience function to check if there is at least one order with the
     * provided cleared status.
     *
     * @param int $cleared
     *
     * @return bool
     */
    private function riskCheckClearedLevel($cleared)
    {
        if (!$this->session->offsetGet('sUserId')) {
            return false;
        }

        $checkOrder = $this->db->fetchRow('
            SELECT id FROM s_order
            WHERE cleared = ? AND userID = ?',
            [
                $cleared,
                $this->session->offsetGet('sUserId'),
            ]
        );

        return $checkOrder && $checkOrder['id'];
    }

    /**
     * Helper function to return the current date formatted
     *
     * @param string $format
     *
     * @return string
     */
    private function getCurrentDateFormatted($format = 'Y-m-d H:i:s')
    {
        $date = new DateTime();

        return $date->format($format);
    }

    /**
     * @return int
     */
    private function getBillingAddressId()
    {
        if ($this->session->offsetGet('checkoutBillingAddressId')) {
            return (int) $this->session->offsetGet('checkoutBillingAddressId');
        }
        if (!$this->session->offsetGet('sUserId')) {
            return 0;
        }
        $dbal = Shopware()->Container()->get('dbal_connection');

        return (int) $dbal->fetchColumn('
            SELECT default_billing_address_id 
            FROM s_user WHERE id = :id
            ',
            ['id' => $this->session->offsetGet('sUserId')]
        );
    }

    /**
     * @return int
     */
    private function getShippingAddressId()
    {
        if ($this->session->offsetGet('checkoutShippingAddressId')) {
            return (int) $this->session->offsetGet('checkoutShippingAddressId');
        }
        if (!$this->session->offsetGet('sUserId')) {
            return 0;
        }
        $dbal = Shopware()->Container()->get('dbal_connection');

        return (int) $dbal->fetchColumn('
            SELECT default_shipping_address_id 
            FROM s_user WHERE id = :id
            ',
            ['id' => $this->session->offsetGet('sUserId')]
        );
    }
}
