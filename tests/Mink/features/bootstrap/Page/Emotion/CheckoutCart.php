<?php
namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
    Behat\Behat\Context\Step;

class CheckoutCart extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/cart';

    public $cssLocator = array(
        'aggregationAmounts' => 'div#aggregation',
        'totalAmount' => 'div > div.totalamount',
        'cartAmount' => 'div > p.textright',
        'shippingCosts' => 'div > div:nth-of-type(1)',
        'sumWithoutVat' => 'div > div.tax',
        'taxValue' => 'div#aggregation > div:nth-of-type(%d)',
        'taxRate' => 'div#aggregation_left > div:nth-of-type(%d)'
    );

    /**
     * Checks the sum, shipping costs, total sum, sum without vat and vat of the cart.
     * @param string $totalSum
     * @param string|null $shippingCosts
     * @param array $vat
     */
    public function checkSums($totalAmount, $shippingCosts = null, $vats = array())
    {
        $locators = array('aggregationAmounts');
        $elements = \Helper::findElements($this, $locators);

        $aggregation = $elements['aggregationAmounts'];
        $prices = array('totalAmount' => $totalAmount);
        $locators = array('totalAmount');

        if($shippingCosts !== null) {
            $prices['shippingCosts'] = $shippingCosts;
            $locators = array_merge($locators, array('cartAmount', 'shippingCosts'));
        }

        $prices = \Helper::toFloat($prices);
        $elements = \Helper::findElements($aggregation, $locators, $this->cssLocator);

        $check = array();
        $check[] = \Helper::toFloat(array($elements['totalAmount']->getText(), $prices['totalAmount']));

        if($shippingCosts !== null) {
            $prices['cartAmount'] = $prices['totalAmount'] - $prices['shippingCosts'];
            $check[] = \Helper::toFloat(array($elements['cartAmount']->getText(), $prices['cartAmount']));
            $check[] = \Helper::toFloat(array($elements['shippingCosts']->getText(), $prices['shippingCosts']));
        }

        if(!empty($vats)) {
            $prices['sumWithoutVat'] = $prices['totalAmount'];

            foreach ($vats as $key => $vat) {
                $vat = \Helper::toFloat($vat);
                $prices['sumWithoutVat'] -= $vat['value'];

                $locators = array(
                    'taxValue' => $key + 4,
                    'taxRate' => $key + 4,
                );

                $elements = \Helper::findElements($this, $locators);

                $check[] = \Helper::toFloat(array($elements['taxValue']->getText(), $vat['value']));
                $check[] = \Helper::toFloat(array($elements['taxRate']->getText(), $vat['percent']));
            }

            $locators = array('sumWithoutVat');
            $elements = \Helper::findElements($aggregation, $locators, $this->cssLocator);

            $check[] = \Helper::toFloat(array($elements['sumWithoutVat']->getText(), $prices['sumWithoutVat']));
        }

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('The value on cart (%s) is deviant from %s!', $check[$result][0], $check[$result][1]);
            throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Adds a voucher to the cart
     * @param string $voucher
     */
    public function addVoucher($voucher)
    {
        $this->open();

        $this->fillField('basket_add_voucher', $voucher);

        $button = $this->find('css', 'div.vouchers input.box_send');
        $button->press();
    }

    /**
     * Adds an article to the cart
     * @param string $article
     */
    public function addArticle($article)
    {
        $this->open();

        $this->fillField('basket_add_article', $article);

        $button = $this->find('css', 'div.add_article input.box_send');
        $button->press();
    }

    /**
     * Remove the voucher from the cart
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function removeVoucher()
    {
        $link = $this->find('css', 'div.table_row.voucher a.del');

        if (empty($link)) {
            $message = 'Cart page has no voucher';
            throw new ResponseTextException($message, $this->getSession());
        }

        $link->click();
    }

    /**
     * Remove the article of the given position from the cart
     * @param integer $position
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function removeArticle($position)
    {
        $locator = 'div.table_row:nth-of-type(' . ($position + 3) . ') form a.del';
        $link = $this->find('css', $locator);

        if (empty($link)) {
            $message = sprintf('Cart page has no article on position %d', $position);
            throw new ResponseTextException($message, $this->getSession());
        }

        $link->click();
    }
}
