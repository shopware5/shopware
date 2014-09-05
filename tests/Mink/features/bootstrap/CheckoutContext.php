<?php

use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class CheckoutContext extends SubContext
{
    /**
     * @Given /^the total sum should be "([^"]*)"$/
     * @Given /^the total sum should be "([^"]*)" when shipping costs are "([^"]*)"$/
     * @Given /^the total sum should be "([^"]*)" when shipping costs are "([^"]*)" and VAT is:$/
     */
    public function theSumsShouldBe($sum, $shippingCosts = null, TableNode $vat = null)
    {
        if (isset($vat)) {
            $vat = $vat->getHash();
        }

        $this->getPage('CheckoutCart')->checkSums($sum, $shippingCosts, $vat);
    }

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
        /** @var \Emotion\CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');
        $language = Helper::getCurrentLanguage($page);

        /** @var MultipleElement $articleBoxes */
        $cartPositions = $this->getElement('CartPosition');
        $cartPositions->setParent($page);

        /** @var \Emotion\ArticleBox $articleBox */
        $cartPosition = $cartPositions->setInstance($position);
        $cartPosition->clickActionLink('remove', $language);
    }

    /**
     * @Given /^my finished order should look like this:$/
     */
    public function myFinishedOrderShouldLookLikeThis(TableNode $positions)
    {
        $orderNumber = $this->getPage('CheckoutConfirm')->getOrderNumber();
        $values = $positions->getHash();

        $this->getPage('Account')->checkOrder($orderNumber, $values);
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
     * @When /^I proceed to checkout$/
     */
    public function iProceedToCheckout()
    {
        $this->getPage('CheckoutConfirm')->proceedToCheckout();
    }
}
