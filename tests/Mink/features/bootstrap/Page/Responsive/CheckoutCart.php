<?php
namespace Page\Responsive;

class CheckoutCart extends \Page\Emotion\CheckoutCart
{
    public $cssLocator = array(
        'aggregationAmounts' => 'ul.aggregation--list',
        'total' => 'li.entry--total > div.entry--value',
        'sum' => 'li.entry--sum > div.entry--value',
        'shipping' => 'li.entry--shipping > div.entry--value',
        'sumWithoutVat' => 'li.entry--totalnet > div.entry--value',
        'taxValue' => 'li.entry--taxes:nth-of-type(%d) > div.entry--value',
        'taxRate' => 'li.entry--taxes:nth-of-type(%d) > div.entry--label',
        'addVoucher' => array(
            'input' => 'div.add-voucher--panel input.add-voucher--field',
            'submit' => 'div.add-voucher--panel button.add-voucher--button'
        ),
        'addArticle' => array(
            'input' => 'form.add-product--form > input.add-product--field',
            'submit' => 'form.add-product--form > button.add-product--button'
        ),
        'removeVoucher' => 'div.table--row.row--voucher a.btn'
    );

    public $namedSelectors = array(
        'checkout' => array('de' => 'Zur Kasse',   'en' => 'Checkout')
    );

    protected $taxesPositionFirst = 5;
    public $cartPositionFirst = 1;
}
