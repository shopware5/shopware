<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page,
    Behat\Mink\Exception\ResponseTextException,
    Behat\Behat\Context\Step;


class CheckoutCart extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/cart';

    public function assertSum($sum, $selector)
    {
        $total = $this->getPrice($selector);
        $sum   = $this->toPrice($sum);

        if ($total != $sum) {
            $message = sprintf('The sum (%s €) is different from %s €!', $total, $sum);
            throw new ResponseTextException($message, $this->getSession());
        }
    }

    private function getPrice($cssSelector)
    {
        $price = $this->find('css', $cssSelector);
        $price = $price->getText();

        $price = $this->toPrice($price);

        return $price;
    }

    private function toPrice($price)
    {
        $price = str_replace('.', '',  $price); //Tausenderpunkte entfernen
        $price = str_replace(',', '.', $price); //Punkt statt Komma
        $price = floatval($price);

        return $price;
    }

    public function proceedToCheckout()
    {
        $this->checkField('sAGB');
        $this->pressButton('basketButton');
    }

    public function addVoucher($voucher)
    {
        $this->open();

        $this->fillField('basket_add_voucher', $voucher);

        $button = $this->find('css', 'div.vouchers input.box_send');
        $button->press();
    }

    public function addArticle($article)
    {
        $this->open();

        $this->fillField('basket_add_article', $article);

        $button = $this->find('css', 'div.add_article input.box_send');
        $button->press();
    }

    public function removeVoucher()
    {
        $link = $this->find('css', 'div.table_row.voucher a.del');
        $link->click();
    }

    public function removeArticle($position)
    {
        $classes = array(
            'cart' => 'div.table_row:nth-of-type('.($position + 3).') form a.del',
            'note' => 'div.table_row:nth-of-type('.($position + 1).') a.delete'
        );

        $button = $this->find('css', implode(', ', $classes));
        $button->click();
    }
}
