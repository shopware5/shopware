<?php
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

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ResponseTextException;
use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Price\Group;
use Shopware\Tests\Mink\Element\CartPosition;
use Shopware\Tests\Mink\Element\CheckoutAddressBox;
use Shopware\Tests\Mink\Element\CheckoutAddressBoxModal;
use Shopware\Tests\Mink\Element\MultipleElement;
use Shopware\Tests\Mink\Page\CheckoutCart;

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

        /** @var \Shopware\Tests\Mink\Page\Account $page */
        $page = $this->getPage('Account');

        $page->open();
        Helper::clickNamedLink($page, 'myOrdersLink');

        /** @var \Shopware\Tests\Mink\Element\AccountOrder $order */
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
                'value' => $shipping,
            ],
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
            Helper::throwException('Article with number "' . $articleNumber . '" was not found.');
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
                'discount' => $grantedDiscount,
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
                'parentId' => $parentCategory['total'] ? $parentCategory['data'][0]['id'] : null,
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
                            'price' => $row['price'],
                        ],
                    ],
                ],
                'supplierId' => $manufacturer['id'],
                'categories' => [$category],
                'images' => [
                    ['link' => 'http://assets.shopware.com/sw_logo_white.png'],
                ],
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
                'name' => $row['name'],
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
                    'taxInput' => $row['taxInput'],
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
            Helper::throwException('Article with number "' . $articleNumber . '" was not found.');
        }

        $article = $articleDetail->getArticle();
        $article->setPriceGroupActive(false);
        $article->setPriceGroup(null);

        $modelManager->flush();
    }

    /**
     * @When /^I click the link "([^"]*)" in the address box with title "([^"]*)"$/
     */
    public function iClickTheLinkInTheAddressBoxWithTitle($linkName, $title)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');

        /** @var MultipleElement $checkoutAddressBoxes */
        $checkoutAddressBoxes = $this->getMultipleElement($page, 'CheckoutAddressBox');

        /** @var CheckoutAddressBox $box */
        foreach ($checkoutAddressBoxes as $box) {
            if ($box->hasTitle($title)) {
                Helper::clickNamedLink($box, $linkName);

                return;
            }
        }
    }

    /**
     * @When /^I click on the link "([^"]*)"$/
     */
    public function iClickOnTheLink($linkName)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');

        /** @var MultipleElement $checkoutModalAddressSelections */
        $checkoutModalAddressSelections = $this->getMultipleElement($page, 'CheckoutModalAddressSelection');

        Helper::assertElementCount($checkoutModalAddressSelections, 1);

        Helper::clickNamedLink($checkoutModalAddressSelections->current(), $linkName);
    }

    /**
     * @When /^I create the address:$/
     */
    public function iCreateTheAddress(TableNode $table)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');
        $data = $table->getHash();

        $page->createArbitraryAddress($data);
    }

    /**
     * @Then /^I should see appear "([^"]*)"$/
     */
    public function iShouldSeeAppear($text)
    {
        $this->spin(function ($context) use ($text) {
            try {
                $this->getMink()->assertSession($this->getSession())->pageTextContains(str_replace('\\"', '"', $text));

                return true;
            } catch (ResponseTextException $e) {
                // NOOP
            }

            return false;
        });
    }

    /**
     * Based on Behat's own example
     *
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     *
     * @param $lambda
     * @param int $wait
     *
     * @throws \Exception
     */
    public function spin($lambda, $wait = 60)
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime) {
            try {
                if ($lambda($this)) {
                    return;
                }
            } catch (\Exception $e) {
                // do nothing
            }

            usleep(250000);
        }

        throw new \Exception("Spin function timed out after {$wait} seconds");
    }

    /**
     * @When /^I wait for "([^"]*)" seconds$/
     */
    public function iWaitForSeconds($seconds)
    {
        $wait = 0;
        while ($wait < $seconds) {
            sleep(1);
            ++$wait;
        }
    }

    /**
     * @Given /^there should be a modal addressbox "([^"]*)"$/
     */
    public function thereShouldBeAModalAddressbox($address)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        /** @var MultipleElement $checkoutAddressBoxes */
        $checkoutAddressBoxes = $this->getMultipleElement($page, 'CheckoutAddressBoxModal');

        /** @var CheckoutAddressBoxModal $box */
        foreach ($checkoutAddressBoxes as $box) {
            if ($box->containsAdress($testAddress)) {
                return;
            }
        }

        $message = sprintf('Given address not found! (%s)', $address);
        Helper::throwException($message);
    }

    /**
     * @When /^I click "([^"]*)" on modal addressbox "([^"]*)"$/
     */
    public function iClickOnModalAddressbox($buttonName, $address)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        /** @var MultipleElement $checkoutAddressBoxes */
        $checkoutAddressBoxes = $this->getMultipleElement($page, 'CheckoutAddressBoxModal');

        /** @var CheckoutAddressBoxModal $box */
        foreach ($checkoutAddressBoxes as $box) {
            if ($box->containsAdress($testAddress)) {
                Helper::pressNamedButton($box, $buttonName);

                return;
            }
        }

        $message = sprintf('Expected address not found! (%s)', $address);
        Helper::throwException($message);
    }

    /**
     * @Then /^I should see appear "([^"]*)" in addressbox "([^"]*)" after "([^"]*)" disappeared$/
     */
    public function iShouldSeeAppearInAddressboxAfterDisappeared($address, $title, $titleToDisappear)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');

        $testAddress = array_values(array_filter(explode(', ', $address)));
        if (empty(trim($testAddress[0]))) {
            $testAddress = array_shift($testAddress);
        }

        $this->spin(function ($context) use ($titleToDisappear) {
            try {
                $this->getMink()->assertSession($this->getSession())->pageTextNotContains(str_replace('\\"', '"', $titleToDisappear));

                return true;
            } catch (ResponseTextException $e) {
                // NOOP
            }

            return false;
        });

        /** @var MultipleElement $checkoutAddressBoxes */
        $checkoutAddressBoxes = $this->getMultipleElement($page, 'CheckoutAddressBox');

        /** @var CheckoutAddressBox $box */
        foreach ($checkoutAddressBoxes as $box) {
            if ($box->hasTitle($title) && $box->containsAdress($testAddress)) {
                return;
            }
        }

        $message = sprintf('Expected to find address as %s! (%s)', $title, $address);
        Helper::throwException($message);
    }

    /**
     * @When /^I change the address:$/
     */
    public function iChangeTheAddress(TableNode $table)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');
        $data = $table->getHash();

        $page->changeModalAddress($data);
    }

    /**
     * @Given /^I set "([^"]*)" as default after "([^"]*)" disappeared$/
     */
    public function iSetAsDefaultAfterDisappeared($address, $titleToDisappear)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        $this->spin(function ($context) use ($titleToDisappear) {
            try {
                $this->getMink()->assertSession($this->getSession())->pageTextNotContains(str_replace('\\"', '"', $titleToDisappear));

                return true;
            } catch (ResponseTextException $e) {
                // NOOP
            }

            return false;
        });

        /** @var MultipleElement $checkoutAddressBoxes */
        $checkoutAddressBoxes = $this->getMultipleElement($page, 'CheckoutAddressBox');

        /** @var CheckoutAddressBox $box */
        foreach ($checkoutAddressBoxes as $box) {
            if ($box->hasTitle('Zahlung und Versand') === false && $box->containsAdress($testAddress)) {
                $box->checkField('set_as_default_billing');

                return;
            }
        }

        $message = sprintf('Expected address not found! (%s)', $address);
        Helper::throwException($message);
    }

    /**
     * @Then /^the "([^"]*)" addressbox must contain "([^"]*)"$/
     */
    public function theAddressboxMustContain($title, $address)
    {
        /** @var \Shopware\Tests\Mink\Page\CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        /** @var MultipleElement $checkoutAddressBoxes */
        $checkoutAddressBoxes = $this->getMultipleElement($page, 'CheckoutAddressBox');

        /** @var CheckoutAddressBox $box */
        foreach ($checkoutAddressBoxes as $box) {
            if ($box->hasTitle($title) && $box->containsAdress($testAddress)) {
                return true;
            }
        }

        $message = sprintf('Expected to find address "%s" as "%s", but didn\'t.', $address, $title);
        Helper::throwException($message);
    }

    /**
     * @When /^I click "([^"]*)" to add the article to the cart$/
     */
    public function iClickToAddTheArticleToTheCart($locator)
    {
        /** @var \Shopware\Tests\Mink\Page\Detail $page */
        $page = $this->getPage('Detail');
        Helper::pressNamedButton($page, $locator);
    }

    /**
     * @Given /^I open the cart page$/
     */
    public function iOpenTheCartPage()
    {
        $this->getPage('CheckoutCart')->open();
    }

    /**
     * @Then /^I open the order confirmation page$/
     */
    public function iOpenTheOrderConfirmationPage()
    {
        $this->getPage('CheckoutConfirm')->open();
    }

    /**
     * @BeforeScenario @taxation
     */
    public function addCustomTaxation()
    {
        /** @var Connection $dbal */
        $dbal = $this->getService('dbal_connection');
        $sql = <<<'EOD'
            INSERT INTO s_core_tax_rules (areaID, countryID, stateID, groupID, customer_groupID, tax, name, active)
            VALUES (3, 23, null, 1, 1, 33, 'Austria', 1)
EOD;
        $dbal->query($sql);
    }

    /**
     * @AfterScenario @taxation
     */
    public function removeCustomTaxation()
    {
        /** @var Connection $dbal */
        $dbal = $this->getService('dbal_connection');
        $sql = <<<'EOD'
            DELETE FROM s_core_tax_rules
            WHERE name = 'Austria'
EOD;
        $dbal->query($sql);
    }

    /**
     * @BeforeScenario @dispatchsurcharge
     */
    public function addCustomDispatchSurcharge()
    {
        /** @var Connection $dbal */
        $dbal = $this->getService('dbal_connection');
        $sql = <<<"EOD"
            INSERT INTO `s_premium_dispatch` (`id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql`)
            VALUES
	        (NULL, 'Sonderaufschlag', 2, '', '', 1, 0, 1, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'IFNULL(us.zipcode,ub.zipcode) = \'48624\'', '', NULL);
EOD;
        $dbal->query($sql);

        $sql = <<<'EOD'
            SET @dispatchId = (SELECT id FROM `s_premium_dispatch` WHERE `name` = 'Sonderaufschlag');
        
            INSERT INTO `s_premium_dispatch_countries` (`dispatchID`, `countryID`)
            VALUES (@dispatchId, 2);
EOD;
        $dbal->query($sql);

        $sql = <<<'EOD'
            INSERT INTO `s_premium_dispatch_paymentmeans` (`dispatchID`, `paymentID`)
            VALUES (@dispatchId, 5);
EOD;
        $dbal->query($sql);

        $sql = <<<'EOD'
            INSERT INTO `s_premium_shippingcosts` (`id`, `from`, `value`, `factor`, `dispatchID`)
            VALUES (null, '0.000', '150.00', '0.00', @dispatchId);
EOD;
        $dbal->query($sql);
    }

    /**
     * @AfterScenario @dispatchsurcharge
     */
    public function removeCustomDispatchSurcharge()
    {
        /** @var Connection $dbal */
        $dbal = $this->getService('dbal_connection');
        $sql = <<<'EOD'
            SET @dispatchId = (SELECT id FROM `s_premium_dispatch` WHERE `name` = 'Sonderaufschlag');
            
            DELETE FROM `s_premium_dispatch`
            WHERE name = 'Sonderaufschlag'
EOD;
        $dbal->query($sql);

        $sql = <<<'EOD'
            DELETE FROM `s_premium_dispatch_countries`
            WHERE dispatchID = '@dispatchId'
EOD;
        $dbal->query($sql);

        $sql = <<<'EOD'
            DELETE FROM `s_premium_dispatch_paymentmeans`
            WHERE dispatchID = '@dispatchId'
EOD;
        $dbal->query($sql);
    }

    /**
     * @BeforeFeature @checkoutadressmanagement
     */
    public static function createUserForCheckoutAddressManagementTest()
    {
        /** @var Connection $dbal */
        $dbal = Shopware()->Container()->get('dbal_connection');
        $sql = <<<'EOD'
INSERT INTO `s_user`
(`password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`, `default_billing_address_id`, `default_shipping_address_id`, `title`, `salutation`, `firstname`, `lastname`, `birthday`, `customernumber`)
VALUES
('a256a310bc1e5db755fd392c524028a8','md5','checkout@adressmanagement.localhost','1','0','','5','2011-11-23','2012-01-04 14:12:05','','0','','0','EK','0','1','1','',NULL,'','0',NULL,'1','3',NULL,'mr','Max','Mustermann',NULL,'20001');
EOD;
        $dbal->query($sql);

        $userIdQuery = $dbal->query('SELECT LAST_INSERT_ID() AS \'userId\'');
        $userId = $userIdQuery->fetchColumn();

        $sql = <<<EOD
INSERT INTO `s_user_addresses`
(`user_id`, `company`, `department`, `salutation`, `title`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `country_id`, `state_id`, `ustid`, `phone`, `additional_address_line1`, `additional_address_line2`)
VALUES
('$userId','Muster GmbH',NULL,'mr',NULL,'Max','Mustermann','Musterstr. 55','55555','Musterhausen','2','3',NULL,'05555 / 555555',NULL,NULL);
EOD;
        $dbal->query($sql);

        $shippingAddressIdQuery = $dbal->query('SELECT LAST_INSERT_ID() AS \'addressId\'');
        $shippingAddressId = $shippingAddressIdQuery->fetchColumn();

        $sql = <<<EOD
INSERT INTO `s_user_addresses`
(`user_id`, `company`, `department`, `salutation`, `title`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `country_id`, `state_id`, `ustid`, `phone`, `additional_address_line1`, `additional_address_line2`)
VALUES
('$userId','shopware AG',NULL,'mr',NULL,'Max','Mustermann','Mustermannstraße 92','48624','Schöppingen','2',NULL,NULL,NULL,NULL,NULL);
EOD;
        $dbal->query($sql);

        $billingAddressIdQuery = $dbal->query('SELECT LAST_INSERT_ID() AS \'addressId\'');
        $billingAddressId = $billingAddressIdQuery->fetchColumn();

        $sql = <<<EOD
UPDATE `s_user`
SET
`default_billing_address_id` = $billingAddressId,
`default_shipping_address_id` = $shippingAddressId
WHERE
`email` = 'checkout@adressmanagement.localhost';
EOD;
        $dbal->query($sql);

        $sql = <<<EOD
INSERT INTO `s_user_billingaddress`
(`userID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `ustid`, `additional_address_line1`, `additional_address_line2`, `title`)
VALUES
('$userId','Muster GmbH','','mr','Max','Mustermann','Musterstr. 55','55555','Musterhausen','05555 / 555555','2','3','',NULL,NULL,NULL);
EOD;
        $dbal->query($sql);
        $sql = <<<EOD
INSERT INTO `s_user_shippingaddress`
(`userID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `countryID`, `stateID`, `additional_address_line1`, `additional_address_line2`, `title`)
VALUES
('$userId','shopware AG','','mr','Max','Mustermann','Mustermannstraße 92','48624','Schöppingen','2',NULL,'','',NULL);
EOD;
        $dbal->query($sql);
    }

    /**
     * @BeforeScenario @paymentsurcharge
     */
    public function addCustomPaymentSurcharge()
    {
        /** @var Connection $dbal */
        $dbal = $this->getService('dbal_connection');
        $sql = <<<'EOD'
            UPDATE s_core_paymentmeans SET debit_percent = 10 WHERE id = 5;
EOD;
        $dbal->query($sql);
    }

    /**
     * @AfterScenario @paymentsurcharge
     */
    public function removeCustomPaymentSurcharge()
    {
        /** @var Connection $dbal */
        $dbal = $this->getService('dbal_connection');
        $sql = <<<'EOD'
            UPDATE s_core_paymentmeans SET debit_percent = 0 WHERE id = 5;
EOD;
        $dbal->query($sql);
    }
}
