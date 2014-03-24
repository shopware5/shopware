<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
    Behat\Behat\Context\Step;

class CheckoutCart extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/cart';

    /**
     * Checks the sum, shipping costs, total sum, sum without vat and vat of the cart.
     * @param string $totalSum
     * @param string|null $shippingCosts
     * @param array $vat
     */
    public function checkSums($totalSum, $shippingCosts = null, $vat = array())
    {
        $prices = $this->getPage('Helper')->toFloat(array('total' => $totalSum, 'shipping' => $shippingCosts));

        $elements = array();
        $elements['total'] = $this->find('css', '#aggregation div.totalamount p.textright');

        $check = array();
        $check[] = $this->getPage('Helper')->toFloat(array($elements['total']->getText(), $prices['total']));

        if($shippingCosts !== null)
        {
            $elements['sum'] = $this->find('css', '#aggregation p.textright');
            $elements['shipping'] = $this->find('css', '#aggregation div:nth-of-type(1) p.textright');

            $check[] = $this->getPage('Helper')->toFloat(
                array($elements['sum']->getText(), $prices['total'] - $prices['shipping'])
            );
            $check[] = $this->getPage('Helper')->toFloat(
                array($elements['shipping']->getText(), $prices['shipping'])
            );
        }

        if (!empty($vat)) {
            $totalVat = 0;

            $elements['sumWithoutVat'] = $this->find('css', '#aggregation div.tax p.textright');

            foreach ($vat as $key => $field) {
                $elements['vat-percent' . $key] = $this->find(
                    'css',
                    sprintf('#aggregation_left div:nth-of-type(%d) span.frontend_checkout_cart_footer', $key + 4)
                );
                $elements['vat-value' . $key] = $this->find(
                    'css',
                    sprintf('#aggregation div:nth-of-type(%d) p.textright', $key + 4)
                );

                $field = $this->getPage('Helper')->toFloat($field);

                $check[] = array($elements['vat-percent' . $key]->getText(), $field['percent']);
                $check[] = $this->getPage('Helper')->toFloat(
                    array($elements['vat-value' . $key]->getText(), $field['value'])
                );

                $totalVat += $field['value'];
            }

            $check[] = $this->getPage('Helper')->toFloat(
                array($elements['sumWithoutVat']->getText(), $prices['total'] - $totalVat)
            );
        }

        $result = $this->getPage('Helper')->checkArray($check);

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
     * @throws Behat\Mink\Exception\ResponseTextException
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
     * @throws Behat\Mink\Exception\ResponseTextException
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
