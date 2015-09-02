<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Shopware\Tests\Mink\Element\Emotion\AddressBox;
use Shopware\Tests\Mink\Helper;

class Account extends \Shopware\Tests\Mink\Page\Emotion\Account
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'identifierDashboard' => 'div.content--wrapper > div.account--content',
            'identifierRegister' => 'div.content--wrapper > div.register--content',
            'payment' => 'div.account--payment.account--box strong',
            'logout' => 'div.account--menu-container a.link--logout',
            'registrationForm' => 'form.register--form',
            'billingForm' => 'div.account--billing-form > form',
            'shippingForm' => 'div.account--shipping-form > form',
            'paymentForm' => 'div.account--payment-form > form',
            'passwordForm' => 'div.account--password > form',
            'emailForm' => 'div.account--email > form',
            'esdDownloads' => '.downloads--table-header ~ .panel--tr',
            'esdDownloadName' => '.download--name'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'registerButton'        => array('de' => 'Neuer Kunde',             'en' => 'New customer'),
            'sendButton'            => array('de' => 'Weiter',                  'en' => 'Continue'),
            'changePaymentButton'   => array('de' => 'Ändern',                  'en' => 'Change'),
            'changeBillingButton'   => array('de' => 'Ändern',                  'en' => 'Change'),
            'changeShippingButton'  => array('de' => 'Ändern',                  'en' => 'Change'),
            'changePasswordButton'  => array('de' => 'Passwort ändern',         'en' => ''),
            'changeEmailButton'     => array('de' => 'E-Mail ändern',           'en' => ''),
            'myOrdersLink'          => array('de' => 'Meine Bestellungen',      'en' => 'My orders'),
            'myEsdDownloads'        => array('de' => 'Meine Sofortdownloads',   'en' => 'My instant downloads'),
            'logoutLink'            => array('de' => 'Abmelden',                'en' => 'Logout')
        );
    }

    protected $identifiers = array('identifierDashboard', 'identifierRegister');

    /**
     * @param $data
     */
    public function register($data)
    {
        $this->verifyPage();

        Helper::fillForm($this, 'registrationForm', $data);
        Helper::pressNamedButton($this, 'sendButton');
    }

    /**
     * @param AddressBox $addresses
     * @param string $name
     */
    public function chooseAddress(AddressBox $addresses, $name)
    {
        $this->searchAddress($addresses, $name);
    }
}
