<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Shopware\Tests\Mink\Helper;

class CheckoutCart extends \Shopware\Tests\Mink\Page\Emotion\CheckoutCart
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
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
            'shippingPaymentForm' => 'form.payment'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'checkout' => array('de' => 'Zur Kasse',   'en' => 'Checkout'),
            'sum' => array('de' => 'Summe:', 'en' => 'Proceed to checkout'),
            'shipping' => array('de' => 'Versandkosten:', 'en' => 'Proceed to checkout'),
            'total' => array('de' => 'Gesamtsumme:', 'en' => 'Proceed to checkout'),
            'sumWithoutVat' => array('de' => 'Gesamtsumme ohne MwSt.:', 'en' => 'Proceed to checkout'),
            'tax' => array('de' => 'zzgl. %d.00'. html_entity_decode('&nbsp;') . '%% MwSt.:', 'en' => 'Proceed to checkout'),
            'changePaymentButton'   => array('de' => 'Weiter',                      'en' => 'Next'),
        );
    }

    protected $taxesPositionFirst = 5;
    public $cartPositionFirst = 1;

    /**
     * Changes the payment method
     * @param array   $data
     */
    public function changePaymentMethod($data = array())
    {
        $data[0]['field'] = 'payment';
        $this->changeShippingMethod($data);
    }

    /**
     * Changes the shipping method
     * @param array $data
     */
    public function changeShippingMethod($data = array())
    {
        Helper::fillForm($this, 'shippingPaymentForm', $data);
        Helper::pressNamedButton($this, 'changePaymentButton');
    }
}
