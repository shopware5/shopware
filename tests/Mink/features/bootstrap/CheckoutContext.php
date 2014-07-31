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
        $language = $this->getElement('LanguageSwitcher')->getCurrentLanguage();

        /** @var \Emotion\CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');
        $offset = $page->cartPositionFirst;

        /** @var MultipleElement $articleBoxes */
        $cartPositions = $this->getElement('CartPosition');
        $cartPositions->setParent($page);

        /** @var \Emotion\ArticleBox $articleBox */
        $cartPosition = $cartPositions->setInstance($position);
        $cartPosition->clickActionLink('remove', $language);
    }

    /**
     * @Given /^I log in as "(?P<email>[^"]*)" with password "(?P<password>[^"]*)" on checkout$/
     */
    public function iLogInAsWithPasswordOnCheckout($email, $password)
    {
        $this->getPage('CheckoutConfirm')->login($email, $password);
    }

    /**
     * @Then /^I change my billing address on confirm page:$/
     */
    public function iChangeMyBillingAddressOnConfirmPage(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('CheckoutConfirm')->changeBilling($values);
    }

    /**
     * @Then /^I change my shipping address on confirm page:$/
     */
    public function iChangeMyShippingAddressOnConfirmPage(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('CheckoutConfirm')->changeShipping($values);
    }

    /**
     * @When /^I change my delivery to "([^"]*)"$/
     */
    public function iChangeMyDeliveryTo($value)
    {
        $this->getPage('CheckoutConfirm')->changeDelivery($value);
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
}
