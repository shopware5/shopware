<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Shopware\Tests\Mink\Helper;

class CheckoutCart extends \Shopware\Tests\Mink\Page\Emotion\CheckoutCart
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'aggregationAmounts' => 'ul.aggregation--list',
            'sum' => 'li.entry--sum > div.entry--value',
            'shipping' => 'li.entry--shipping > div.entry--value',
            'total' => 'li.entry--total > div.entry--value',
            'sumWithoutVat' => 'li.entry--totalnet > div.entry--value',
            'taxValue' => 'li.entry--taxes:nth-of-type(%d) > div.entry--value',
            'taxRate' => 'li.entry--taxes:nth-of-type(%d) > div.entry--label',
            'addVoucherInput' => 'div.add-voucher--panel input.add-voucher--field',
            'addVoucherSubmit' => 'div.add-voucher--panel button.add-voucher--button',
            'addArticleInput' => 'form.add-product--form > input.add-product--field',
            'addArticleSubmit' => 'form.add-product--form > button.add-product--button',
            'removeVoucher' => 'div.row--voucher a.btn',
            'aggregationLabels' => 'ul.aggregation--list .entry--label',
            'aggregationValues' => 'ul.aggregation--list .entry--value',
            'shippingPaymentForm' => 'form.payment',
            'articleDeleteButtons' => '.column--actions-link[title="Löschen"]'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'checkout' => ['de' => 'Zur Kasse',   'en' => 'Checkout'],
            'sum' => ['de' => 'Summe:', 'en' => 'Proceed to checkout'],
            'shipping' => ['de' => 'Versandkosten:', 'en' => 'Proceed to checkout'],
            'total' => ['de' => 'Gesamtsumme:', 'en' => 'Proceed to checkout'],
            'sumWithoutVat' => ['de' => 'Gesamtsumme ohne MwSt.:', 'en' => 'Proceed to checkout'],
            'tax' => ['de' => 'zzgl. %d.00'. html_entity_decode('&nbsp;') . '%% MwSt.:', 'en' => 'Proceed to checkout'],
            'changePaymentButton'   => ['de' => 'Weiter', 'en' => 'Next'],
        ];
    }

    /**
     * @param string $language
     * @return bool
     * @throws \Exception
     */
    public function verifyPage($language = '')
    {
        $info = Helper::getPageInfo($this->getSession(), ['controller', 'action']);

        if (($info['controller'] === 'checkout') && ($info['action'] === 'cart')) {
            return Helper::hasNamedLink($this, 'checkout', $language);
        }

        $message = ['You are not on the cart!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
        Helper::throwException($message);

        return false;
    }

    /**
     * Changes the payment method
     * @param array   $data
     */
    public function changePaymentMethod($data = [])
    {
        $data[0]['field'] = 'payment';
        $this->changeShippingMethod($data);
    }

    /**
     * Changes the shipping method
     * @param array $data
     */
    public function changeShippingMethod($data = [])
    {
        Helper::fillForm($this, 'shippingPaymentForm', $data);
        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     * Proceeds to the confirmation page with login
     * @param string $eMail
     * @param string $password
     */
    public function proceedToOrderConfirmationWithLogin($eMail, $password)
    {
        if ($this->verifyPage()) {
            Helper::clickNamedLink($this, 'checkout');
        }

        $this->getPage('Account')->login($eMail, $password);
        $this->getPage('CheckoutConfirm')->verifyPage();
    }
}
