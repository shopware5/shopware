<?php
namespace Page\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\Emotion\AccountOrder;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Account extends Page implements \HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/account';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'identifierDashboard' => 'div#content > div > div.account',
            'identifierLogin' => 'div#login',
            'identifierRegister' => 'div#content > div > div.register',
            'payment' => 'div#selected_payment strong',
            'logout' => 'div.adminbox a.logout',
            'registrationForm' => 'div.register > form',
            'billingForm' => 'div.change_billing > form',
            'shippingForm' => 'div.change_shipping > form',
            'paymentForm' => 'div.change_payment > form',
            'passwordForm' => 'div.password > form',
            'emailForm' => 'div.email > form',
            'esdDownloads' => 'div.downloads div.table_row',
            'esdDownloadName' => '.grid_7 > strong'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'registerButton'        => array('de' => 'Neuer Kunde',               'en' => 'New customer'),
            'sendButton'            => array('de' => 'Registrierung abschließen', 'en' => 'Complete registration'),
            'changePaymentButton'   => array('de' => 'Ändern',                    'en' => 'Change'),
            'changeShippingButton'  => array('de' => 'Ändern',                    'en' => 'Change'),
            'changePasswordButton'  => array('de' => 'Passwort ändern',           'en' => 'Change password'),
            'changeEmailButton'     => array('de' => 'E-Mail ändern',             'en' => 'Change email'),
            'myOrdersLink'          => array('de' => 'Meine Bestellungen',        'en' => 'My orders'),
            'myEsdDownloads'        => array('de' => 'Meine Sofortdownloads',     'en' => 'My instant downloads'),
            'logoutLink'            => array('de' => 'Abmelden Logout',           'en' => 'Logout')
        );
    }

    protected $identifiers = array('identifierDashboard', 'identifierLogin', 'identifierRegister');

    /**
     * Logins a user
     * @param string $email
     * @param string $password
     */
    public function login($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);

        $this->pressButton('Anmelden');
    }

    /**
     * Check if the user was successfully logged in
     */
    public function verifyLogin($username)
    {
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains(
            'Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen'
        );
        $assert->pageTextContains('Willkommen, '.$username);
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     * @param string|null $action
     * @return string
     */
    public function verifyPage($action = null)
    {
        $locators = $this->identifiers;
        $elements = \Helper::findElements($this, $locators, false);
        $elements = array_filter($elements);

        if (empty($elements)) {
            $message = array('You are not on Account page!', 'Current URL: ' . $this->getSession()->getCurrentUrl());
            \Helper::throwException($message);
        }

        if (!$action) {
            return true;
        }

        if (array_key_exists($action, $elements)) {
            return true;
        }

        return key($elements);
    }

    /**
     * Logout a customer (important by using the Sahi driver)
     * @return bool
     */
    public function logout()
    {
        if($this->verifyPage('identifierDashboard') === true) {
            \Helper::clickNamedLink($this, 'logoutLink');
            return true;
        }

        return false;
    }

    /**
     * Changes the password of the user
     * @param string $currentPassword
     * @param string $password
     * @param string $passwordConfirmation
     */
    public function changePassword($currentPassword, $password, $passwordConfirmation = null)
    {
        $data = array(
            array(
                'field' => 'currentPassword',
                'value' => $currentPassword
            ),
            array(
                'field' => 'password',
                'value' => $password
            ),
            array(
                'field' => 'passwordConfirmation',
                'value' => ($passwordConfirmation !== null) ? $passwordConfirmation : $password
            )
        );

        \Helper::fillForm($this, 'passwordForm', $data);
        \Helper::pressNamedButton($this, 'changePasswordButton');
    }

    /**
     * Changes the email address of the user
     * @param string $password
     * @param string $email
     * @param string $emailConfirmation
     */
    public function changeEmail($password, $email, $emailConfirmation = null)
    {
        $data = array(
            array(
                'field' => 'emailPassword',
                'value' => $password
            ),
            array(
                'field' => 'email',
                'value' => $email
            ),
            array(
                'field' => 'emailConfirmation',
                'value' => ($emailConfirmation !== null) ? $emailConfirmation : $email
            )
        );

        \Helper::fillForm($this, 'emailForm', $data);
        \Helper::pressNamedButton($this, 'changeEmailButton');
    }

    /**
     * Changes the billing address of the user
     * @param array $values
     */
    public function changeBilling($values)
    {
        \Helper::fillForm($this, 'billingForm', $values);
        \Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     * Changes the shipping address of the user
     * @param array $values
     */
    public function changeShippingAddress($values)
    {
        \Helper::fillForm($this, 'shippingForm', $values);
        \Helper::pressNamedButton($this, 'changePaymentButton');
    }

    public function checkPayment($payment)
    {
        $locators = array('payment');
        $elements = \Helper::findElements($this, $locators);

        if (strcmp($elements['payment']->getText(), $payment) !== 0) {
            $message = sprintf('The current payment method is %s! (should be %s)', $elements['payment']->getText(), $payment);
            \Helper::throwException($message);
        }
    }

    /**
     * Changes the payment method
     * @param array   $data
     */
    public function changePaymentMethod($data = array())
    {
        $element = $this->getElement('AccountPayment');
        $language = \Helper::getCurrentLanguage($this);
        \Helper::clickNamedLink($element, 'changeButton', $language);

        \Helper::fillForm($this, 'paymentForm', $data);
        \Helper::pressNamedButton($this, 'changePaymentButton', $language);
    }

    /**
     * @param AccountOrder $order
     * @param $orderNumber
     * @param $articles
     * @throws \Behat\Behat\Exception\PendingException
     * @throws \Exception
     */
    public function checkOrder($order, $orderNumber, $articles)
    {
        $date = $this->checkOrderDate($order);
        $this->checkOrderNumber($order, $orderNumber);
        $this->checkOrderPositions($order, $articles);
        $this->checkEsdArticles($date, $articles);
    }

    /**
     * Checks the dates of an order in the account. On success the date will be returned.
     * @param AccountOrder $order
     * @return string
     * @throws \Exception
     */
    private function checkOrderDate(AccountOrder $order) {
        $dates = \Helper::getValuesToCheck($order, 'date');
        $dates = array_unique($dates);
        if (count($dates) > 1) {
            $message = sprintf("There are different dates in the order!\r\n%s", implode("\r\n", $dates));
            \Helper::throwException($message);
        }

        return $dates['orderDate'];
    }

    /**
     * @param AccountOrder $order
     * @param $number
     */
    private function checkOrderNumber(AccountOrder $order, $number)
    {
        /** @var Homepage $homepage */
        $homepage = $this->getPage('Homepage');

        $data = array(
            array(
                'position' => 'number',
                'content' => $number
            )
        );

        $homepage->assertElementContent($order, $data);
    }

    /**
     * @param AccountOrder $order
     * @param array $articles
     * @throws \Exception
     */
    private function checkOrderPositions(AccountOrder $order, $articles)
    {
        $positions = $order->getPositions(array('product', 'quantity', 'price', 'sum'));

        $data = array();

        foreach($articles as $key => $article) {
            $data[$key] = \Helper::toFloat(array(
                'quantity' => $article['quantity'],
                'price' => $article['price'],
                'sum' => $article['sum']
            ));

            $data[$key]['product'] = $article['product'];
        }

        $result = \Helper::compareArrays($positions, $data);

        if ($result === true) {
            return;
        }

        $message = sprintf('The %s of a position is different! (is "%s", should be "%s")', $result['key'], $result['value'], $result['value2']);
        \Helper::throwException($message);
    }

    /**
     * @param string $date
     * @param array $articles
     * @throws \Exception
     */
    private function checkEsdArticles($date, $articles)
    {
        $esd = array();

        foreach($articles as $key => $article) {
            if (empty($article['esd'])) {
                continue;
            }

            $esd[] = $article['product'];
        }

        if(empty($esd)) {
            return;
        }

        $language = \Helper::getCurrentLanguage($this);
        \Helper::clickNamedLink($this, 'myEsdDownloads', $language);

        $locators = array('esdDownloads');
        $elements = \Helper::findAllOfElements($this, $locators);
        $locator = \Helper::getRequiredSelector($this, 'esdDownloadName');
        $downloads = array();

        /** @var NodeElement $esdDownload */
        foreach($elements['esdDownloads'] as $esdDownload) {
            if (strpos($esdDownload->getText(), $date) !== false) {
                $downloads[] = $this->find('css', $locator)->getText();
            }
        }

        foreach($esd as $givenEsd) {
            foreach($downloads as $download) {
                if ($givenEsd === $download) {
                    break;
                }

                if($download === end($downloads)) {
                    $message = sprintf('ESD-Article "%s" not found in account!', $givenEsd);
                    \Helper::throwException($message);
                }
            }
        }
    }

    /**
     * @param string $type
     * @param string $address
     */
    public function checkAddress($type, $address)
    {
        $this->open();

        $type = strtolower($type);
        $type = ucfirst($type);

        $this->getElement('Account'.$type)->checkAddress($address);
    }

    /**
     * @param array $data
     */
    public function register($data)
    {
        if ($this->verifyPage('identifierLogin') === true) {
            \Helper::pressNamedButton($this, 'registerButton');
        }

        \Helper::fillForm($this, 'registrationForm', $data);
        \Helper::pressNamedButton($this, 'sendButton');
    }
}
