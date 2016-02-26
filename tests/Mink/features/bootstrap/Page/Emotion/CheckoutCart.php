<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use Shopware\Tests\Mink\Element\Emotion\CartPosition;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;

class CheckoutCart extends Page implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/cart';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'aggregationAmounts' => 'div#aggregation',
            'sum' => 'div#aggregation > p.textright',
            'shipping' => 'div#aggregation > div:nth-of-type(1)',
            'total' => 'div#aggregation > div.totalamount',
            'sumWithoutVat' => 'div#aggregation > div.tax',
            'taxValue' => 'div#aggregation > div:nth-of-type(%d)',
            'taxRate' => 'div#aggregation_left > div:nth-of-type(%d)',
            'addVoucherInput' => 'div.vouchers input.text',
            'addVoucherSubmit' => 'div.vouchers input.box_send',
            'addArticleInput' => 'div.add_article input.ordernum',
            'addArticleSubmit' => 'div.add_article input.box_send',
            'removeVoucher' => 'div.table_row.voucher a.del',
            'aggregationLabels' => '#aggregation_left > *',
            'aggregationValues' => '#aggregation > *',
            'articleDeleteButtons' => 'div.table_row a.del'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'checkout' => ['de' => 'Zur Kasse gehen!', 'en' => 'Proceed to checkout'],
            'sum' => ['de' => 'Summe', 'en' => 'Proceed to checkout'],
            'shipping' => ['de' => 'Versandkosten', 'en' => 'Proceed to checkout'],
            'total' => ['de' => 'Gesamtsumme', 'en' => 'Proceed to checkout'],
            'sumWithoutVat' => ['de' => 'Gesamtsumme ohne MwSt.:', 'en' => 'Proceed to checkout'],
            'tax' => ['de' => 'zzgl. %d.00'. html_entity_decode('&nbsp;') . '%% MwSt.:', 'en' => 'Proceed to checkout'],
        ];
    }

    /**
     * Checks the aggregation
     * @param $aggregation
     * @throws \Exception
     */
    public function checkAggregation($aggregation)
    {
        $elements = Helper::findAllOfElements($this, ['aggregationLabels', 'aggregationValues']);
        $lang = Helper::getCurrentLanguage();
        $check = [];

        foreach ($aggregation as $property) {
            $key = $this->getAggregationPosition($elements['aggregationLabels'], $property['label'], $lang);

            $check[$property['label']] = Helper::floatArray([
                $property['value'],
                $elements['aggregationValues'][$key]->getText()
            ]);

            unset($elements['aggregationLabels'][$key]);
            unset($elements['aggregationValues'][$key]);
        }

        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The value of "%s" is "%s"! (should be "%s")',
                $result,
                $check[$result][1],
                $check[$result][0]
            );

            Helper::throwException($message);
        }
    }

    /**
     * @param string $key
     * @param string $language
     * @return string
     */
    private function getLabel($key, $language)
    {
        $labels = $this->getNamedSelectors();

        if (strpos($key, '%') !== false) {
            $taxRate = intval($key);
            return sprintf($labels['tax'][$language], $taxRate);
        }

        if (isset($labels[$key][$language])) {
            return $labels[$key][$language];
        }

        $message = sprintf('Label "%s" is not defined for language key "%s"', $key, $language);
        Helper::throwException($message, Helper::EXCEPTION_PENDING);
    }

    /**
     * @param array $labels
     * @param string $labelKey
     * @param string $language
     * @return int
     * @throws \Exception
     */
    private function getAggregationPosition(array $labels, $labelKey, $language)
    {
        $givenLabel = $this->getLabel($labelKey, $language);

        $key = 0;
        $lastKey = max(array_keys($labels));

        do {
            if (array_key_exists($key, $labels)) {
                $readLabel = $labels[$key]->getText();

                if ($givenLabel === $readLabel) {
                    return $key;
                }
            }

            $key++;
        } while ($key <= $lastKey);

        $message = sprintf('Label "%s" does not exist on the page! ("%s")', $labelKey, $givenLabel);
        Helper::throwException($message);
    }

    /**
     * Adds a voucher to the cart
     * @param string $voucher
     */
    public function addVoucher($voucher)
    {
        $elements = Helper::findElements($this, ['addVoucherInput', 'addVoucherSubmit']);

        $elements['addVoucherInput']->setValue($voucher);
        $elements['addVoucherSubmit']->press();
    }

    /**
     * Adds an article to the cart
     * @param string $article
     */
    public function addArticle($article)
    {
        $elements = Helper::findElements($this, ['addArticleInput', 'addArticleSubmit']);

        $elements['addArticleInput']->setValue($article);
        $elements['addArticleSubmit']->press();
    }

    /**
     * Remove a product from the cart
     * @param CartPosition $item
     */
    public function removeProduct(CartPosition $item)
    {
        Helper::clickNamedLink($item, 'remove');
    }

    /**
     * Remove the voucher from the cart
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function removeVoucher()
    {
        $elements = Helper::findElements($this, ['removeVoucher']);
        $elements['removeVoucher']->click();
    }

    /**
     * Removes all products from the cart
     * @param CartPosition $items
     */
    public function emptyCart(CartPosition $items)
    {
        /** @var CartPosition $item */
        foreach ($items as $item) {
            $this->removeProduct($item);
        }
    }

    /**
     * Fills the cart with products
     * @param array $items
     */
    public function fillCartWithProducts(array $items)
    {
        $originalPath = $this->path;

        foreach ($items as $item) {
            $this->path = sprintf('/checkout/addArticle/sAdd/%s/sQuantity/%d', $item['number'], $item['quantity']);
            $this->open();
        }

        $this->path = $originalPath;
    }

    /**
     * Checks the cart positions
     * Available properties are: number (required), name (required), quantity, itemPrice, sum
     * @param CartPosition $cartPositions
     * @param array $items
     */
    public function checkCartProducts(CartPosition $cartPositions, array $items)
    {
        Helper::assertElementCount($cartPositions, count($items));
        $items = Helper::floatArray($items, ['quantity', 'itemPrice', 'sum']);
        $result = Helper::assertElements($items, $cartPositions);

        if ($result !== true) {
            $messages = ['The following articles are wrong:'];
            foreach ($result as $product) {
                $messages[] = sprintf(
                    '%s - %s (%s is "%s", should be "%s")',
                    $product['properties']['number'],
                    $product['properties']['name'],
                    $product['result']['key'],
                    $product['result']['value'],
                    $product['result']['value2']
                );
            }
            Helper::throwException($messages);
        }
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     * @return bool
     * @throws \Exception
     */
    public function verifyPage()
    {
        try {
            $assert = new WebAssert($this->getSession());
            $assert->pageTextContains('1 Ihr Warenkorb 2 Ihre Adresse 3 Prüfen und Bestellen');
        } catch (ResponseTextException $e) {
            $message = ['You are not on the cart!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
            Helper::throwException($message);
        }

        return Helper::hasNamedLink($this, 'checkout');
    }

    /**
     * Proceeds to the confirmation page
     */
    public function proceedToOrderConfirmation()
    {
        if ($this->verifyPage()) {
            Helper::clickNamedLink($this, 'checkout');
        }

        $this->getPage('CheckoutConfirm')->verifyPage();
    }

    /**
     * Proceeds to the confirmation page with login
     * @param string $eMail
     * @param string $password
     */
    public function proceedToOrderConfirmationWithLogin($eMail, $password)
    {
        if ($this->verifyPage()) {
            $locatorArray = $this->getNamedSelectors();
            $parent = Helper::getContentBlock($this);
            $language = Helper::getCurrentLanguage();
            $link = $parent->findLink($locatorArray['checkout'][$language]);

            if ($this->getDriver() instanceof Selenium2Driver) {
                $this->getSession()->visit($link->getAttribute('href'));
            } else {
                $link->click();
            }
        }

        $this->getPage('Account')->login($eMail, $password);
        $this->getPage('CheckoutConfirm')->verifyPage();
    }

    /**
     * Proceeds to the confirmation page with registration
     * @param array $data
     */
    public function proceedToOrderConfirmationWithRegistration(array $data)
    {
        if ($this->verifyPage()) {
            Helper::clickNamedLink($this, 'checkout');
        }

        $this->getPage('Account')->register($data);
    }

    public function resetCart()
    {
        $originalPath = $this->path;

        try {
            $elements = Helper::findElements($this, ['articleDeleteButtons']);

            foreach ($elements as $element) {
                $this->path = $element->getAttribute('href');
                $this->open();
            }
        } catch (\Exception $ex) {
        }

        $this->path = $originalPath;
        $this->open();
    }
}
