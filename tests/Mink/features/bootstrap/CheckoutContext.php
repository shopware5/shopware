<?php

namespace Shopware\Tests\Mink;

use Behat\Behat\Tester\Exception\PendingException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Price\Group;
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

        $page->open();
        Helper::clickNamedLink($page, 'myOrdersLink');

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
        $page->resetCart();
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

    /**
     * @Given /^The article "(?P<articleNr>[^"]*)" is assigned to the price group "([^"]*)"$/
     */
    public function theArticleIsAssignedToThePriceGroup($articleNumber, $priceGroupName)
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->getService('models');

        $priceGroup = $modelManager->getRepository('Shopware\Models\Price\Group')->findOneBy(['name' => $priceGroupName]);
        $articleDetail = $modelManager->getRepository('Shopware\Models\Article\Detail')->findOneBy(['number' => $articleNumber]);

        if (!$articleDetail) {
            Helper::throwException('Article with number "'.$articleNumber.'" was not found.');
        }

        $article = $articleDetail->getArticle();
        $article->setPriceGroupActive(true);
        $article->setPriceGroup($priceGroup);

        $modelManager->flush();
    }

    /**
     * @Given /^A price group named "([^"]*)" that grants "([^"]*)" discount$/
     */
    public function aPriceGroupNamedThatGrantsDiscount($priceGroupName, $grantedDiscount)
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->getService('models');

        $priceGroup = $modelManager->getRepository('Shopware\Models\Price\Group')->findOneBy(['name' => $priceGroupName]);
        if (!$priceGroup) {
            $priceGroup = new \Shopware\Models\Price\Group();
            $priceGroup->setName($priceGroupName);
        }

        $grantedDiscount = (int) preg_replace('/\D/', '', $grantedDiscount);

        $discount = $modelManager->getRepository('Shopware\Models\Price\Discount')
            ->findOneBy([
                'customerGroupId' => 1,
                'start' => 1,
                'discount' => $grantedDiscount
            ]);

        if (!$discount) {
            $discount = new \Shopware\Models\Price\Discount();
            $discount->setCustomerGroupId(1);
            $discount->setStart(1);
            $discount->setGroup($priceGroup);
        }

        $discount->setDiscount($grantedDiscount);

        $modelManager->persist($priceGroup);
        $modelManager->persist($discount);
        $modelManager->flush();
    }

    /**
     * @Given /^there is a category defined:$/
     */
    public function thereIsACategoryDefined(TableNode $table)
    {
        $categories = $table->getHash();

        $categoryResource = new \Shopware\Components\Api\Resource\Category();
        $categoryResource->setManager($this->getService('models'));

        foreach ($categories as $row) {
            $category = $categoryResource->getList(0, 1, [['property' => 'name', 'value' => $row['name']]]);
            if ($category['total']) {
                continue;
            }

            $parentCategory = $categoryResource->getList(0, 1, [['property' => 'name', 'value' => $row['parentName']]]);

            $data = [
                'name' => $row['name'],
                'parentId' => $parentCategory['total'] ? $parentCategory['data'][0]['id'] : null
            ];

            $categoryResource->create($data);
        }
    }

    /**
     * @Given /^the following product exist:$/
     */
    public function theFollowingProductExist(TableNode $table)
    {
        $products = $table->getHash();

        $resource = new \Shopware\Components\Api\Resource\Article();
        $resource->setManager($this->getService('models'));

        $categoryResource = new \Shopware\Components\Api\Resource\Category();
        $categoryResource->setManager($this->getService('models'));

        $manufacturerResource = new \Shopware\Components\Api\Resource\Manufacturer();
        $manufacturerResource->setManager($this->getService('models'));

        foreach ($products as $row) {
            $category = $categoryResource->getList(0, 1, [['property' => 'c.name', 'value' => $row['category']]])['data'][0];
            $manufacturer = $manufacturerResource->getList(0, 1, [['property' => 'supplier.name', 'value' => $row['manufacturer']]])['data'][0];

            $data = [
                'name' => $row['name'],
                'taxId' => 1,
                'active' => 1,
                'mainDetail' => [
                    'number' => $row['number'],
                    'active' => 1,
                    'shippingFree' => (bool) $row['shippingFree'],
                    'prices' => [
                        [
                            'customerGroupKey' => $row['customergroup'],
                            'from' => 1,
                            'price' => $row['price']
                        ]
                    ]
                ],
                'supplierId' => $manufacturer['id'],
                'categories' => [$category],
                'images' => [
                    ['link' => 'http://assets.shopware.com/sw_logo_white.png']
                ]
            ];

            try {
                $resource->updateByNumber($row['number'], $data);
            } catch (\Exception $ex) {
                $resource->create($data);
            }
        }
    }

    /**
     * @Given /^the manufacturer exist:$/
     */
    public function theManufacturerExist(TableNode $table)
    {
        $manufactures = $table->getHash();

        $manufacturerResource = new \Shopware\Components\Api\Resource\Manufacturer();
        $manufacturerResource->setManager($this->getService('models'));

        foreach ($manufactures as $row) {
            $manufacturer = $manufacturerResource->getList(0, 1, [['property' => 'supplier.name', 'value' => $row['name']]]);

            if ($manufacturer['total']) {
                continue;
            }

            $manufacturerResource->create([
                'name' => $row['name']
            ]);
        }
    }

    /**
     * @Given /^the customer group exist:$/
     */
    public function theCustomerGroupExist(TableNode $table)
    {
        $groups = $table->getHash();

        $groupResource = new \Shopware\Components\Api\Resource\CustomerGroup();
        $groupResource->setManager($this->getService('models'));

        foreach ($groups as $row) {
            $group = $groupResource->getList(0, 1, [['property' => 'key', 'value' => $row['key']]]);

            if ($group['total']) {
                $groupResource->update($group['data'][0]['id'], ['taxInput' => $row['taxInput']]);
            } else {
                $groupResource->create([
                    'key' => $row['key'],
                    'name' => $row['key'],
                    'taxInput' => $row['taxInput']
                ]);
            }
        }
    }

    /**
     * @Given /^The article "(?P<articleNr>[^"]*)" has no active price group$/
     */
    public function theArticleHasNoActivePriceGroup($articleNumber)
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->getService('models');

        $articleDetail = $modelManager->getRepository('Shopware\Models\Article\Detail')->findOneBy(['number' => $articleNumber]);

        if (!$articleDetail) {
            Helper::throwException('Article with number "'.$articleNumber.'" was not found.');
        }

        $article = $articleDetail->getArticle();
        $article->setPriceGroupActive(false);
        $article->setPriceGroup(null);

        $modelManager->flush();
    }
}
