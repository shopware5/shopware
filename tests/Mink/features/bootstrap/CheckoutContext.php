<?php

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\Emotion\CheckoutCart;
use Shopware\Tests\Mink\Element\Emotion\CartPosition;
use Behat\Gherkin\Node\TableNode;

class CheckoutContext extends SubContext
{
    /**
     * @When /^I add the voucher "(?P<code>[^"]*)" to my basket$/
     */
    public function iAddTheVoucherToMyBasket($voucher)
    {
        $this->getPage('CheckoutCart')->addVoucher($voucher);
    }

    /**
     * @When /^I remove the voucher$/
     */
    public function iRemoveTheVoucher()
    {
        $this->getPage('CheckoutCart')->removeVoucher();
    }

    /**
     * @When /^I add the article "(?P<articleNr>[^"]*)" to my basket$/
     */
    public function iAddTheArticleToMyBasket($article)
    {
        $this->getPage('CheckoutCart')->addArticle($article);
    }

    /**
     * @When /^I remove the article on position (?P<num>\d+)$/
     */
    public function iRemoveTheArticleOnPosition($position)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');

        /** @var CartPosition $cartPosition */
        $cartPosition = $this->getMultipleElement($page, 'CartPosition', $position);
        $page->removeProduct($cartPosition);
    }

    /**
     * @Given /^my finished order should look like this:$/
     */
    public function myFinishedOrderShouldLookLikeThis(TableNode $positions)
    {
        $orderNumber = $this->getPage('CheckoutConfirm')->getOrderNumber();
        $values = $positions->getHash();

        /** @var \Shopware\Tests\Mink\Page\Emotion\Account $page */
        $page = $this->getPage('Account');
        $language = Helper::getCurrentLanguage($page);

        $page->open();
        Helper::clickNamedLink($page, 'myOrdersLink', $language);

        /** @var \Shopware\Tests\Mink\Element\Emotion\AccountOrder $order */
        $order = $this->getMultipleElement($page, 'AccountOrder');
        $page->checkOrder($order, $orderNumber, $values);
    }

    /**
     * @Given /^the aggregations should look like this:$/
     */
    public function theAggregationsShouldLookLikeThis(TableNode $aggregations)
    {
        $aggregations = $aggregations->getHash();
        $this->getPage('CheckoutCart')->checkAggregation($aggregations);
    }

    /**
     * @When /^I proceed to order confirmation$/
     */
    public function iProceedToOrderConfirmation()
    {
        $this->getPage('CheckoutCart')->proceedToOrderConfirmation();
    }

    /**
     * @Given /^I proceed to order confirmation with email "([^"]*)" and password "([^"]*)"$/
     */
    public function iProceedToOrderConfirmationWithEmailAndPassword($email, $password)
    {
        $this->getPage('CheckoutCart')->proceedToOrderConfirmationWithLogin($email, $password);
    }

    /**
     * @Given /^I proceed to checkout as:$/
     */
    public function iProceedToCheckoutAs(TableNode $table)
    {
        $this->getPage('CheckoutCart')->proceedToOrderConfirmationWithRegistration($table->getHash());
    }

    /**
     * @When /^I proceed to checkout$/
     */
    public function iProceedToCheckout()
    {
        $this->getPage('CheckoutConfirm')->proceedToCheckout();
    }

    /**
     * @When /^I change the shipping method to (?P<shippingId>\d+)$/
     */
    public function iChangeTheShippingMethodTo($shipping)
    {
        $data = [
            [
                'field' => 'sDispatch',
                'value' => $shipping
            ]
        ];

        $this->getPage('CheckoutConfirm')->changeShippingMethod($data);
    }

    /**
     * @Given /^the cart contains the following products:$/
     */
    public function theCartContainsTheFollowingProducts(TableNode $items)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');
        $page->fillCartWithProducts($items->getHash());
        $page->open();
        $this->theCartShouldContainTheFollowingProducts($items);
    }

    /**
     * @Then /^the cart should contain the following products:$/
     */
    public function theCartShouldContainTheFollowingProducts(TableNode $items)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');

        /** @var CartPosition $cartPositions */
        $cartPositions = $this->getMultipleElement($page, 'CartPosition');
        $page->checkCartProducts($cartPositions, $items->getHash());
    }
}
