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
        'total' => 'div > div.totalamount',
        'sum' => 'div > p.textright',
        'shipping' => 'div > div:nth-of-type(1)',
        'sumWithoutVat' => 'div > div.tax',
        'taxValue' => 'div#aggregation > div:nth-of-type(%d)',
        'taxRate' => 'div#aggregation_left > div:nth-of-type(%d)',
        'addArticle' => array(
            'input' => 'div.add_article input.ordernum',
            'submit' => 'div.add_article input.box_send'
        )
    );

    public $namedSelectors = array(
        'checkout' => array('de' => 'Zur Kasse gehen!',   'en' => 'Proceed to checkout')
    );

    protected $taxesPositionFirst = 4;
    public $cartPositionFirst = 3;



    /**
     * Checks the sum, shipping costs, total sum, sum without vat and vat of the cart.
     * @param string $totalSum
     * @param string|null $shippingCosts
     * @param array $vat
     */
    public function checkSums($totalAmount, $shippingCosts = null, $vats = array())
    {
//        $this->open();

        $locators = array('aggregationAmounts');
        $elements = \Helper::findElements($this, $locators);

        $aggregation = $elements['aggregationAmounts'];
        $prices = array('total' => $totalAmount);
        $locators = array('total');

        if($shippingCosts !== null) {
            $prices['shipping'] = $shippingCosts;
            $locators = array_merge($locators, array('sum', 'shipping'));
        }

        $prices = \Helper::toFloat($prices);
        $elements = \Helper::findElements($aggregation, $locators, $this->cssLocator);

        $check = array();
        $check[] = \Helper::toFloat(array($elements['total']->getText(), $prices['total']));

        if($shippingCosts !== null) {
            $prices['sum'] = $prices['total'] - $prices['shipping'];
            $check[] = \Helper::toFloat(array($elements['sum']->getText(), $prices['sum']));
            $check[] = \Helper::toFloat(array($elements['shipping']->getText(), $prices['shipping']));
        }

        if(!empty($vats)) {
            $prices['sumWithoutVat'] = $prices['total'];

            foreach ($vats as $key => $vat) {
                $vat = \Helper::toFloat($vat);
                $prices['sumWithoutVat'] -= $vat['value'];

                $locators = array(
                    'taxValue' => $key + $this->taxesPositionFirst,
                    'taxRate' => $key + $this->taxesPositionFirst,
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

        $elements = \Helper::findElements($this, $this->cssLocator['addArticle'], $this->cssLocator['addArticle']);

        $elements['input']->setValue($article);
        $elements['submit']->press();
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
     * @param array $aggregations
     */
    public function checkAggregation($aggregations)
    {
        $locators = array('aggregationAmounts');
        $elements = \Helper::findElements($this, $locators);

        $aggregation = $elements['aggregationAmounts'];

        $locators = array_column($aggregations, 'aggregation');
        $values = array_column($aggregations, 'value');

        $taxLocators = array();
        $taxValues = array();

        foreach ($locators as $key => $locator) {
            $tax = floatval($locator);

            if (empty($tax)) {
                continue;
            }

            $taxKey = count($taxLocators) + $this->taxesPositionFirst;

            $taxLocators[] = array(
                'taxRate' => $taxKey,
                'taxValue' => $taxKey
            );

            $taxValues[] = array(
                'taxRate' => $tax,
                'taxValue' => $values[$key]
            );

            unset($locators[$key]);
            unset($values[$key]);
        }

        $elements = \Helper::findElements($aggregation, $locators, $this->cssLocator);
        $values = array_combine($locators, $values);

        $check = array();
        foreach ($elements as $key => $element) {
            $check[$key] = \Helper::toFloat(array($element->getText(), $values[$key]));
        }

        foreach ($taxLocators as $key => $locator) {
            $elements = \Helper::findElements($this, $locator);
            $check['taxRate_' . $taxValues[$key]['taxRate']] = \Helper::toFloat(
                array($elements['taxRate']->getText(), $taxValues[$key]['taxRate'])
            );
            $check['taxValue_' . $taxValues[$key]['taxRate']] = \Helper::toFloat(
                array($elements['taxValue']->getText(), $taxValues[$key]['taxValue'])
            );
        }

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The value "%s" on cart (%s) is deviant from %s!',
                $result,
                $check[$result][0],
                $check[$result][1]
            );
            \Helper::throwException(array($message));
        }
    }
}
