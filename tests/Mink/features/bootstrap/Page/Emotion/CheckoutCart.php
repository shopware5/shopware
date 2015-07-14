<?php
namespace Page\Emotion;

use Element\Emotion\CartPosition;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class CheckoutCart extends Page implements \HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/cart';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
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
            'aggregationValues' => '#aggregation > *'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'checkout' => array('de' => 'Zur Kasse gehen!', 'en' => 'Proceed to checkout'),
            'sum' => array('de' => 'Summe', 'en' => 'Proceed to checkout'),
            'shipping' => array('de' => 'Versandkosten', 'en' => 'Proceed to checkout'),
            'total' => array('de' => 'Gesamtsumme', 'en' => 'Proceed to checkout'),
            'sumWithoutVat' => array('de' => 'Gesamtsumme ohne MwSt.:', 'en' => 'Proceed to checkout'),
            'tax' => array('de' => 'zzgl. %d.00'. html_entity_decode('&nbsp;') . '%% MwSt.:', 'en' => 'Proceed to checkout'),
        );
    }

//    protected $taxesPositionFirst = 4;
//    public $cartPositionFirst = 3;

    /**
     * @param $aggregation
     * @throws \Exception
     */
    public function checkAggregation($aggregation)
    {
        $locators = array('aggregationLabels', 'aggregationValues');
        $elements = \Helper::findAllOfElements($this, $locators);
        $lang = \Helper::getCurrentLanguage($this);
        $check = array();

        foreach($aggregation as $property) {
            $key = $this->getAggregationPosition($elements['aggregationLabels'], $property['label'], $lang);

            $check[$property['label']] = \Helper::floatArray(
                array(
                    $property['value'],
                    $elements['aggregationValues'][$key]->getText()
                )
            );

            unset($elements['aggregationLabels'][$key]);
            unset($elements['aggregationValues'][$key]);
        }

        $result = \Helper::checkArray($check);

        if($result !== true) {
            $message = sprintf(
                'The value of "%s" is "%s"! (should be "%s")',
                $result,
                $check[$result][1],
                $check[$result][0]
            );

            \Helper::throwException($message);
        }
    }

    /**
     * @param string $key
     * @param string $language
     * @return string
     * @throws \Behat\Behat\Exception\PendingException
     */
    private function getLabel($key, $language)
    {
        $labels = $this->getNamedSelectors();

        if(strpos($key, '%') !== false) {
            $taxRate = intval($key);
            return sprintf($labels['tax'][$language], $taxRate);
        }

        if(isset($labels[$key][$language])) {
            return $labels[$key][$language];
        }

        $message = sprintf('Label "%s" is not defined for language key "%s"', $key, $language);
        \Helper::throwException($message, \Helper::EXCEPTION_PENDING);
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
        } while($key <= $lastKey);

        $message = sprintf('Label "%s" does not exist on the page! ("%s")', $labelKey, $givenLabel);
        \Helper::throwException($message);
    }

    /**
     * Adds a voucher to the cart
     * @param string $voucher
     */
    public function addVoucher($voucher)
    {
        $locators = array('addVoucherInput', 'addVoucherSubmit');
        $elements = \Helper::findElements($this, $locators);

        $elements['addVoucherInput']->setValue($voucher);
        $elements['addVoucherSubmit']->press();
    }

    /**
     * Adds an article to the cart
     * @param string $article
     */
    public function addArticle($article)
    {
        $locators = array('addArticleInput', 'addArticleSubmit');
        $elements = \Helper::findElements($this, $locators);

        $elements['addArticleInput']->setValue($article);
        $elements['addArticleSubmit']->press();
    }

    /**
     * Remove a product from the cart
     * @param CartPosition $item
     * @param string $language
     */
    public function removeProduct(CartPosition $item, $language = '')
    {
        if(empty($language)) {
            $language = \Helper::getCurrentLanguage($this);
        }

        \Helper::clickNamedLink($item, 'remove', $language);
    }

    /**
     * Remove the voucher from the cart
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function removeVoucher()
    {
        $locator = array('removeVoucher');
        $elements = \Helper::findElements($this, $locator);

        $elements['removeVoucher']->click();
    }

    /**
     * @param CartPosition $items
     */
    public function emptyCart(CartPosition $items)
    {
        $language = \Helper::getCurrentLanguage($this);

        /** @var CartPosition $item */
        foreach($items as $item) {
            $this->removeProduct($item, $language);
        }
    }

    /**
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
        if(count($cartPositions) !== count($items)) {
            $message = sprintf(
                'There are %d products in the cart! (should be %d)',
                count($cartPositions),
                count($items)
            );
            \Helper::throwException($message);
        }

        $items = \Helper::floatArray($items, ['quantity', 'itemPrice', 'sum']);
        $result = \Helper::assertElements($items, $cartPositions);

        if($result !== true) {
            $messages = array('The following articles are wrong:');
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
            \Helper::throwException($messages);
        }
    }

    /**
     * @param string $language
     * @return bool
     */
    public function verifyPage($language = '')
    {
        return \Helper::hasNamedLink($this, 'checkout', $language);
    }

    /**
     * Proceeds to the confirmation page
     */
    public function proceedToOrderConfirmation()
    {
        $language = \Helper::getCurrentLanguage($this);

        if($this->verifyPage($language)) {
            \Helper::clickNamedLink($this, 'checkout', $language);
        }

        $this->getPage('CheckoutConfirm')->verifyPage($language);
    }

    /**
     * Proceeds to the confirmation page with login
     * @param string $eMail
     * @param string $password
     */
    public function proceedToOrderConfirmationWithLogin($eMail, $password)
    {
        $language = \Helper::getCurrentLanguage($this);

        if($this->verifyPage($language)) {
            \Helper::clickNamedLink($this, 'checkout', $language);
        }

        $this->getPage('Account')->login($eMail, $password);
        $this->getPage('CheckoutConfirm')->verifyPage($language);
    }

    /**
     * Proceeds to the confirmation page with registration
     * @param array $data
     */
    public function proceedToOrderConfirmationWithRegistration(array $data)
    {
        $language = \Helper::getCurrentLanguage($this);

        if($this->verifyPage($language)) {
            \Helper::clickNamedLink($this, 'checkout', $language);
        }

        $this->getPage('Account')->register($data);
    }
}
