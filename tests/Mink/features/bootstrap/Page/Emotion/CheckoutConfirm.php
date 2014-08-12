<?php
namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class CheckoutConfirm extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/confirm';

    public $cssLocator = array(
        'pageIdentifier'  => 'div#confirm',
        'deliveryForm' => 'form.payment',
        'proceedCheckoutForm' => 'div.additional_footer > form',
        'orderNumber' => 'div#finished > div.orderdetails > p'
    );

    public function verifyPage()
    {
        $locators = array('pageIdentifier');
        $elements = \Helper::findElements($this, $locators, $this->cssLocator, false, false);

        if (!empty($elements['pageIdentifier'])) {
            return;
        }

        $message = array('You are not on CheckoutConfirm page!', 'Current URL: '.$this->getSession()->getCurrentUrl());
        \Helper::throwException($message);
    }

    public function getOrderNumber()
    {
        $locators = array('orderNumber');
        $elements = \Helper::findElements($this, $locators);

        $orderDetails = $elements['orderNumber']->getText();

        preg_match("/\d+/",$orderDetails,$orderNumber);
        $orderNumber = intval($orderNumber[0]);

        return $orderNumber;
    }
}
