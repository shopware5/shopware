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
     * Checks the sum of the cart
     * @param string $sum
     */
    public function checkSum($sum)
    {
        $this->assertSum($sum, '#aggregation p.textright');
    }

    /**
     * Checks the shipping costs
     * @param string $costs
     */
    public function checkShippingCosts($costs)
    {
        $this->assertSum($costs, '#aggregation div:nth-of-type(1) p.textright');
    }

    /**
     * Checks the total sum of the cart
     * @param string $sum
     */
    public function checkTotalSum($sum)
    {
        $this->assertSum($sum, '#aggregation div.totalamount p.textright');
    }

    /**
     * Checks the sum of the cart without vat
     * @param string $sum
     */
    public function checkSumWithoutVat($sum)
    {
        $this->assertSum($sum, '#aggregation div.tax p.textright');
    }

    /**
     * Checks the vat
     * @param string $vat
     */
    public function checkVat($vat)
    {
        $this->assertSum($vat, '#aggregation div:nth-of-type(4) p.textright');
    }

    /**
     * Helper class to check a price
     * @param string $sum
     * @param string $locator
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    private function assertSum($sum, $locator)
    {
        $total = $this->getPrice($locator);
        $sum = $this->toPrice($sum);

        if ($total != $sum) {
            $message = sprintf('The sum (%s €) is different from %s €!', $total, $sum);
            throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Helper function to get a price from the cart
     * @param string $locator
     * @return float
     */
    private function getPrice($locator)
    {
        $price = $this->find('css', $locator);
        $price = $price->getText();

        $price = $this->toPrice($price);

        return $price;
    }

    /**
     * Helper function to validate a price
     * @param string $price
     * @return float
     */
    private function toPrice($price)
    {
        $price = str_replace('.', '', $price); //Tausenderpunkte entfernen
        $price = str_replace(',', '.', $price); //Punkt statt Komma
        $price = floatval($price);

        return $price;
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
