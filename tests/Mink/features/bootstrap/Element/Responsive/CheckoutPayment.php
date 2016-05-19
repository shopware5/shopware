<?php

namespace Shopware\Tests\Mink\Element\Responsive;

use Shopware\Tests\Mink\Helper;

/**
 * Element: CheckoutPayment
 * Location: Payment box on checkout confirm page
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class CheckoutPayment extends \Shopware\Tests\Mink\Element\Emotion\CheckoutPayment
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.payment--panel'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'currentMethod' => 'span.payment--description'
        ];
    }

    public function getPaymentMethodProperty()
    {
        $element = Helper::findElements($this, ['currentMethod']);

        return $element['currentMethod']->getText();
    }
}
