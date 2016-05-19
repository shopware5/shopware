<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;

/**
 * Element: CheckoutPayment
 * Location: Payment box on checkout confirm page
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class CheckoutPayment extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.payment-display');

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'currentMethod' => 'p'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'changeButton' => ['de' => 'Ändern', 'en' => 'Change']
        ];
    }

    /**
     * Returns the current payment method
     * @return string
     */
    public function getPaymentMethodProperty()
    {
        $element = Helper::findElements($this, ['currentMethod']);

        $currentMethod = $element['currentMethod']->getText();
        $currentMethod = str_word_count($currentMethod, 1);

        return $currentMethod[0];
    }
}
