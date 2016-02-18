<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Shopware\Tests\Mink\Element\Emotion\AddressBox;
use Shopware\Tests\Mink\Helper;

class Account extends \Shopware\Tests\Mink\Page\Emotion\Account
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'loginButton'           => ['de' => 'Anmelden',                 'en' => 'Login'],
            'forgotPasswordLink'    => ['de' => 'Passwort vergessen?',      'en' => 'Forgot your password?'],
            'sendButton'            => ['de' => 'Weiter',                   'en' => 'Continue'],

            'myAccountLink'         => ['de' => 'Mein Konto',               'en' => 'My account'],
            'myOrdersLink'          => ['de' => 'Meine Bestellungen',       'en' => 'My orders'],
            'myEsdDownloadsLink'    => ['de' => 'Meine Sofortdownloads',    'en' => 'My instant downloads'],
            'changeBillingLink'     => ['de' => 'Rechnungsadresse ändern',  'en' => 'Change billing address'],
            'changeShippingLink'    => ['de' => 'Lieferadresse ändern',     'en' => 'Change shipping address'],
            'changePaymentLink'     => ['de' => 'Zahlungsart ändern',       'en' => 'Change payment method'],
            'noteLink'              => ['de' => 'Merkzettel',               'en' => 'Wish list'],
            'logoutLink'            => ['de' => 'Abmelden',                 'en' => 'Logout'],

            'changePaymentButton'   => ['de' => 'Ändern',                   'en' => 'Change'],
            'changeBillingButton'   => ['de' => 'Ändern',                   'en' => 'Change'],
            'changeShippingButton'  => ['de' => 'Ändern',                   'en' => 'Change'],
            'changePasswordButton'  => ['de' => 'Passwort ändern',          'en' => 'Change password'],
            'changeEmailButton'     => ['de' => 'E-Mail ändern',            'en' => 'Change email']
        ];
    }

    /**
     * @inheritdoc
     */
    protected function verifyPageLogin()
    {
        return (
            parent::verifyPageLogin() &&
            parent::verifyPageRegister()
        );
    }

    /**
     * @inheritdoc
     */
    protected function verifyPageRegister()
    {
        return $this->verifyPageLogin();
    }

    /**
     * @inheritdoc
     */
    public function register(array $data)
    {
        $this->verifyPage();

        Helper::fillForm($this, 'registrationForm', $data);
        Helper::pressNamedButton($this, 'sendButton');
    }

    /**
     * @inheritdoc
     */
    public function chooseAddress(AddressBox $addresses, $name)
    {
        $this->searchAddress($addresses, $name);
    }
}
