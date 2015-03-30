<?php

use Page\Emotion\CheckoutCart;
use Element\MultipleElement;
use Element\Emotion\ArticleBox;
use Behat\Gherkin\Node\TableNode;

require_once 'SubContext.php';

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
        $language = Helper::getCurrentLanguage($page);

        /** @var MultipleElement $cartPositions */
        $cartPositions = $this->getElement('CartPosition');
        $cartPositions->setParent($page);

        /** @var ArticleBox $cartPosition */
        $cartPosition = $cartPositions->setInstance($position);
        Helper::clickNamedLink($cartPosition, 'remove', $language);
    }

    /**
     * @Given /^my finished order should look like this:$/
     */
    public function myFinishedOrderShouldLookLikeThis(TableNode $positions)
    {
        $orderNumber = $this->getPage('CheckoutConfirm')->getOrderNumber();
        $values = $positions->getHash();

        /** @var \Page\Emotion\Account $page */
        $page = $this->getPage('Account');
        $language = Helper::getCurrentLanguage($page);

        $page->open();
        Helper::clickNamedLink($page, 'myOrdersLink', $language);

        /** @var \Element\Emotion\AccountOrder $order */
        $order = $this->getElement('AccountOrder');
        $order->setParent($page);

        $this->getPage('Account')->checkOrder($order, $orderNumber, $values);
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

    /**
     * @When /^I change the shipping method to (?P<shippingId>\d+)$/
     */
    public function iChangeTheShippingMethodTo($shipping)
    {
        $data = array(
            array(
                'field' => 'sDispatch',
                'value' => $shipping
            )
        );

        $this->getPage('CheckoutConfirm')->changeShippingMethod($data);
    }
}
