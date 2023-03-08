<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Mink\Page\Frontend\Checkout;

use Behat\Mink\Exception\ResponseTextException;
use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Frontend\Account\Account;
use Shopware\Tests\Mink\Page\Frontend\Checkout\Elements\CartPositionProduct;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

class CheckoutCart extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = '/checkout/cart';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors(): array
    {
        return [
            'aggregationAmounts' => 'ul.aggregation--list',
            'sum' => 'li.entry--sum > div.entry--value',
            'shipping' => 'li.entry--shipping > div.entry--value',
            'total' => 'li.entry--total > div.entry--value',
            'sumWithoutVat' => 'li.entry--totalnet > div.entry--value',
            'taxValue' => 'li.entry--taxes:nth-of-type(%d) > div.entry--value',
            'taxRate' => 'li.entry--taxes:nth-of-type(%d) > div.entry--label',
            'addVoucherInput' => 'div.add-voucher--panel input.add-voucher--field',
            'addVoucherSubmit' => 'div.add-voucher--panel button.add-voucher--button',
            'addArticleInput' => 'form.add-product--form > input.add-product--field',
            'addArticleSubmit' => 'form.add-product--form > button.add-product--button',
            'removeVoucher' => 'div.row--voucher .column--actions-link',
            'aggregationLabels' => 'ul.aggregation--list .entry--label',
            'aggregationValues' => 'ul.aggregation--list .entry--value',
            'shippingPaymentForm' => 'form.payment',
            'articleDeleteButtons' => '.column--actions-link[title="Löschen"]',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors(): array
    {
        return [
            'checkout' => ['de' => 'Zur Kasse', 'en' => 'Checkout'],
            'sum' => ['de' => 'Summe:', 'en' => 'Sum:'],
            'shipping' => ['de' => 'Versandkosten:', 'en' => 'Shipping costs:'],
            'total' => ['de' => 'Gesamtsumme:', 'en' => 'Total amount:'],
            'sumWithoutVat' => ['de' => 'Gesamtsumme ohne MwSt.:', 'en' => 'Total amount without VAT:'],
            'tax' => ['de' => 'zzgl. %d %% MwSt.:', 'en' => 'Plus %d %% VAT:'],
            'changePaymentButton' => ['de' => 'Weiter', 'en' => 'Next:'],
        ];
    }

    /**
     * Checks the aggregation
     *
     * @throws Exception
     */
    public function checkAggregation(array $aggregation): void
    {
        $elements = Helper::findAllOfElements($this, ['aggregationLabels', 'aggregationValues']);
        $lang = Helper::getCurrentLanguage();
        $check = [];

        foreach ($aggregation as $property) {
            $key = $this->getAggregationPosition($elements['aggregationLabels'], $property['label'], $lang);

            $check[$property['label']] = Helper::floatArray([
                $property['value'],
                $elements['aggregationValues'][$key]->getText(),
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
     * Adds a voucher to the cart
     */
    public function addVoucher(string $voucher): void
    {
        $elements = Helper::findElements($this, ['addVoucherInput', 'addVoucherSubmit']);

        $elements['addVoucherInput']->setValue($voucher);
        $elements['addVoucherSubmit']->press();
    }

    /**
     * Adds an article to the cart
     */
    public function addProduct(string $product): void
    {
        $elements = Helper::findElements($this, ['addArticleInput', 'addArticleSubmit']);

        $elements['addArticleInput']->setValue($product);
        $elements['addArticleSubmit']->press();
    }

    /**
     * Remove a product from the cart
     */
    public function removeProduct(CartPositionProduct $item): void
    {
        Helper::pressNamedButton($item, 'remove');
    }

    /**
     * Remove the voucher from the cart
     *
     * @throws ResponseTextException
     */
    public function removeVoucher(): void
    {
        $elements = Helper::findElements($this, ['removeVoucher']);
        $elements['removeVoucher']->click();
    }

    /**
     * Removes all products from the cart
     */
    public function emptyCart(CartPositionProduct $items): void
    {
        foreach ($items as $item) {
            $this->removeProduct($item);
        }
    }

    /**
     * Fills the cart with products
     */
    public function fillCartWithProducts(array $items): void
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
     */
    public function checkCartProducts(CartPositionProduct $cartPositions, array $items): void
    {
        Helper::assertElementCount($cartPositions, \count($items));
        $items = Helper::floatArray($items, ['itemPrice', 'sum']);
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
     *
     * @throws Exception
     */
    public function verifyPage(string $language = ''): bool
    {
        $info = Helper::getPageInfo($this->getSession(), ['controller', 'action']);

        if (\is_array($info) && ($info['controller'] === 'checkout') && ($info['action'] === 'cart')) {
            return Helper::hasNamedLink($this, 'checkout', $language);
        }

        $message = ['You are not on the cart!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
        Helper::throwException($message);
    }

    /**
     * Proceeds to the confirmation page
     */
    public function proceedToOrderConfirmation(): void
    {
        if ($this->verifyPage()) {
            Helper::clickNamedLink($this, 'checkout');
        }

        $this->getPage(CheckoutConfirm::class)->verifyPage();
    }

    /**
     * Proceeds to the confirmation page with login
     */
    public function proceedToOrderConfirmationWithLogin(string $eMail, string $password): void
    {
        if ($this->verifyPage()) {
            Helper::clickNamedLink($this, 'checkout');
        }

        $this->getPage(Account::class)->login($eMail, $password);
        $this->getPage(CheckoutConfirm::class)->verifyPage();
    }

    /**
     * Proceeds to the confirmation page with registration
     */
    public function proceedToOrderConfirmationWithRegistration(array $data): void
    {
        if ($this->verifyPage()) {
            Helper::clickNamedLink($this, 'checkout');
        }

        $this->getPage(Account::class)->register($data);
    }

    /**
     * Changes the payment method
     */
    public function changePaymentMethod(array $data = []): void
    {
        $data[0]['field'] = 'payment';
        $this->changeShippingMethod($data);
    }

    /**
     * Changes the shipping method
     */
    public function changeShippingMethod(array $data = []): void
    {
        Helper::fillForm($this, 'shippingPaymentForm', $data, true);

        Helper::waitForOverlay($this);

        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    public function resetCart(): void
    {
        $originalPath = $this->path;

        try {
            $elements = Helper::findElements($this, ['articleDeleteButtons']);

            foreach ($elements as $element) {
                $this->path = $element->getAttribute('href');
                $this->open();
            }
        } catch (Exception $ex) {
        }

        $this->path = $originalPath;
        $this->open();
    }

    protected function verify(array $urlParameters): void
    {
        $this->verifyResponse();
        $this->verifyPage();
    }

    private function getLabel(string $key, string $language): string
    {
        $labels = $this->getNamedSelectors();

        if (str_contains($key, '%')) {
            $taxRate = (float) $key;

            return sprintf($labels['tax'][$language], $taxRate);
        }

        if (isset($labels[$key][$language])) {
            return $labels[$key][$language];
        }

        $message = sprintf('Label "%s" is not defined for language key "%s"', $key, $language);
        Helper::throwException($message, Helper::EXCEPTION_PENDING);
    }

    /**
     * @throws Exception
     */
    private function getAggregationPosition(array $labels, string $labelKey, string $language): int
    {
        $givenLabel = $this->getLabel($labelKey, $language);

        foreach ($labels as $key => $label) {
            if ($givenLabel === $label->getText()) {
                return $key;
            }
        }

        $message = sprintf('Label "%s" does not exist on the page! ("%s")', $labelKey, $givenLabel);
        Helper::throwException($message);
    }
}
