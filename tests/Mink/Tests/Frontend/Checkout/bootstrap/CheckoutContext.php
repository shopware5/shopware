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

namespace Shopware\Tests\Mink\Tests\Frontend\Checkout\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ResponseTextException;
use Doctrine\DBAL\Connection;
use Exception;
use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Api\Resource\Category;
use Shopware\Components\Api\Resource\CustomerGroup;
use Shopware\Components\Api\Resource\Manufacturer;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail as ProductVariant;
use Shopware\Models\Price\Discount;
use Shopware\Models\Price\Group;
use Shopware\Tests\Mink\Page\Frontend\Account\Account;
use Shopware\Tests\Mink\Page\Frontend\Account\Elements\AccountOrder;
use Shopware\Tests\Mink\Page\Frontend\Checkout\CheckoutCart;
use Shopware\Tests\Mink\Page\Frontend\Checkout\CheckoutConfirm;
use Shopware\Tests\Mink\Page\Frontend\Checkout\Elements\CartPositionProduct;
use Shopware\Tests\Mink\Page\Frontend\Checkout\Elements\CheckoutAddressBox;
use Shopware\Tests\Mink\Page\Frontend\Checkout\Elements\CheckoutAddressBoxModal;
use Shopware\Tests\Mink\Page\Frontend\Checkout\Elements\CheckoutModalAddressSelection;
use Shopware\Tests\Mink\Page\Frontend\Detail\Detail;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class CheckoutContext extends SubContext
{
    /**
     * @When /^I add the voucher "(?P<code>[^"]*)" to my basket$/
     */
    public function iAddTheVoucherToMyBasket(string $voucher): void
    {
        $this->getPage(CheckoutCart::class)->addVoucher($voucher);
    }

    /**
     * @When /^I remove the voucher$/
     */
    public function iRemoveTheVoucher(): void
    {
        $this->getPage(CheckoutCart::class)->removeVoucher();
    }

    /**
     * @When /^I add the article "(?P<product>[^"]*)" to my basket$/
     */
    public function iAddTheArticleToMyBasket(string $product): void
    {
        $this->getPage(CheckoutCart::class)->addProduct($product);
    }

    /**
     * @When /^I add the article "(?P<productNumber>[^"]*)" to my basket over HTTP GET$/
     */
    public function iAddTheArticleToMyBasketOverHttpGet(string $productNumber): void
    {
        try {
            $page = $this->getPage(CheckoutCart::class);
            $page->resetCart();
            $page->fillCartWithProducts([['number' => $productNumber, 'quantity' => 1]]);
        } catch (Exception $unexpectedPageException) {
        }
    }

    /**
     * @When /^I remove the article on position (?P<num>\d+)$/
     */
    public function iRemoveTheArticleOnPosition(int $position): void
    {
        $page = $this->getPage(CheckoutCart::class);

        $cartPosition = $this->getMultipleElement($page, CartPositionProduct::class, $position);
        $page->removeProduct($cartPosition);
    }

    /**
     * @Given /^my finished order should look like this:$/
     */
    public function myFinishedOrderShouldLookLikeThis(TableNode $positions): void
    {
        $orderNumber = $this->getPage(CheckoutConfirm::class)->getOrderNumber();
        $values = $positions->getHash();

        $page = $this->getPage(Account::class);

        $page->open();
        Helper::clickNamedLink($page, 'myOrdersLink');

        $order = $this->getMultipleElement($page, AccountOrder::class);
        $page->checkOrder($order, $orderNumber, $values);
    }

    /**
     * @Given /^the aggregations should look like this:$/
     */
    public function theAggregationsShouldLookLikeThis(TableNode $aggregations): void
    {
        $aggregations = $aggregations->getHash();
        $this->getPage(CheckoutCart::class)->checkAggregation($aggregations);
    }

    /**
     * @When /^I proceed to order confirmation$/
     */
    public function iProceedToOrderConfirmation(): void
    {
        $this->getPage(CheckoutCart::class)->proceedToOrderConfirmation();
    }

    /**
     * @Given /^I proceed to order confirmation with email "([^"]*)" and password "([^"]*)"$/
     */
    public function iProceedToOrderConfirmationWithEmailAndPassword(string $email, string $password): void
    {
        $this->getPage(CheckoutCart::class)->proceedToOrderConfirmationWithLogin($email, $password);
    }

    /**
     * @Given /^I proceed to checkout as:$/
     */
    public function iProceedToCheckoutAs(TableNode $table): void
    {
        $this->getPage(CheckoutCart::class)->proceedToOrderConfirmationWithRegistration($table->getHash());
    }

    /**
     * @When /^I proceed to checkout$/
     */
    public function iProceedToCheckout(): void
    {
        $this->getPage(CheckoutConfirm::class)->proceedToCheckout();
    }

    /**
     * @When /^I change the shipping method to (?P<shippingId>\d+)$/
     */
    public function iChangeTheShippingMethodTo(int $shippingId): void
    {
        $data = [
            [
                'field' => 'sDispatch',
                'value' => $shippingId,
            ],
        ];

        $this->getPage(CheckoutConfirm::class)->changeShippingMethod($data);
    }

    /**
     * @Given /^the cart contains the following products:$/
     */
    public function theCartContainsTheFollowingProducts(TableNode $items): void
    {
        $detailPage = $this->getPage(Detail::class);

        foreach ($items->getIterator() as $row) {
            $detailPage->open(['articleId' => $row['articleId'], 'number' => $row['number']]);
            $detailPage->addToBasket((int) $row['quantity']);
        }
    }

    /**
     * @Then /^the cart should contain the following products:$/
     */
    public function theCartShouldContainTheFollowingProducts(TableNode $items): void
    {
        $page = $this->getPage(CheckoutCart::class);

        $cartPositions = $this->getMultipleElement($page, CartPositionProduct::class);
        $page->checkCartProducts($cartPositions, $items->getHash());
    }

    /**
     * @Given /^The article "(?P<productNumber>[^"]*)" is assigned to the price group "([^"]*)"$/
     */
    public function theArticleIsAssignedToThePriceGroup(string $productNumber, string $priceGroupName): void
    {
        $modelManager = $this->getService(ModelManager::class);

        $priceGroup = $modelManager->getRepository(Group::class)->findOneBy(['name' => $priceGroupName]);
        $productVariant = $modelManager->getRepository(ProductVariant::class)->findOneBy(['number' => $productNumber]);

        if ($productVariant === null) {
            Helper::throwException(sprintf('Product with number "%s" was not found.', $productNumber));
        }

        $product = $productVariant->getArticle();
        $product->setPriceGroupActive(true);
        $product->setPriceGroup($priceGroup);

        $modelManager->flush();
    }

    /**
     * @Given /^A price group named "([^"]*)" that grants "([^"]*)" discount$/
     */
    public function aPriceGroupNamedThatGrantsDiscount(string $priceGroupName, string $grantedDiscount): void
    {
        $modelManager = $this->getService(ModelManager::class);

        $priceGroup = $modelManager->getRepository(Group::class)->findOneBy(['name' => $priceGroupName]);
        if ($priceGroup === null) {
            $priceGroup = new Group();
            $priceGroup->setName($priceGroupName);
        }

        $grantedDiscount = (int) preg_replace('/\D/', '', $grantedDiscount);

        $discount = $modelManager->getRepository(Discount::class)
            ->findOneBy([
                'customerGroupId' => 1,
                'start' => 1,
                'discount' => $grantedDiscount,
            ]);

        if ($discount === null) {
            $discount = new Discount();
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
    public function thereIsACategoryDefined(TableNode $table): void
    {
        $categories = $table->getHash();

        $categoryResource = new Category();
        $categoryResource->setManager($this->getService(ModelManager::class));

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
    public function theFollowingProductExist(TableNode $table): void
    {
        $products = $table->getHash();

        $resource = new Article();
        $resource->setManager($this->getService(ModelManager::class));

        $categoryResource = new Category();
        $categoryResource->setManager($this->getService(ModelManager::class));

        $manufacturerResource = new Manufacturer();
        $manufacturerResource->setManager($this->getService(ModelManager::class));

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
                    ['link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC'],
                ],
            ];

            try {
                $resource->updateByNumber($row['number'], $data);
            } catch (Exception $ex) {
                $resource->create($data);
            }
        }
    }

    /**
     * @Given /^the manufacturer exist:$/
     */
    public function theManufacturerExist(TableNode $table): void
    {
        $manufactures = $table->getHash();

        $manufacturerResource = new Manufacturer();
        $manufacturerResource->setManager($this->getService(ModelManager::class));

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
    public function theCustomerGroupExist(TableNode $table): void
    {
        $groups = $table->getHash();

        $groupResource = new CustomerGroup();
        $groupResource->setManager($this->getService(ModelManager::class));

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
     * @Given /^The article "(?P<productNumber>[^"]*)" has no active price group$/
     */
    public function theArticleHasNoActivePriceGroup(string $productNumber): void
    {
        $modelManager = $this->getService(ModelManager::class);

        $productVariant = $modelManager->getRepository(ProductVariant::class)->findOneBy(['number' => $productNumber]);

        if ($productVariant === null) {
            Helper::throwException('Product with number "' . $productNumber . '" was not found.');
        }

        $product = $productVariant->getArticle();
        $product->setPriceGroupActive(false);
        $product->setPriceGroup(null);

        $modelManager->flush();
    }

    /**
     * @When /^I click the link "([^"]*)" in the address box with title "([^"]*)"$/
     */
    public function iClickTheLinkInTheAddressBoxWithTitle(string $linkName, string $title): void
    {
        $page = $this->getPage(CheckoutConfirm::class);

        $checkoutAddressBoxes = $this->getMultipleElement($page, CheckoutAddressBox::class);

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
    public function iClickOnTheLink(string $linkName): void
    {
        $page = $this->getPage(CheckoutConfirm::class);

        $checkoutModalAddressSelections = $this->getMultipleElement($page, CheckoutModalAddressSelection::class);

        Helper::assertElementCount($checkoutModalAddressSelections, 1);

        Helper::clickNamedLink($checkoutModalAddressSelections->current(), $linkName);
    }

    /**
     * @When /^I create the address:$/
     */
    public function iCreateTheAddress(TableNode $table): void
    {
        $page = $this->getPage(CheckoutConfirm::class);
        $data = $table->getHash();

        $page->createArbitraryAddress($data);
    }

    /**
     * @Then /^I should see appear "([^"]*)"$/
     */
    public function iShouldSeeAppear(string $text): void
    {
        $this->spin(function () use ($text) {
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
     * @throws Exception
     */
    public function spin(callable $lambda, int $wait = 60): void
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime) {
            try {
                if ($lambda($this)) {
                    return;
                }
            } catch (Exception $e) {
                // do nothing
            }

            usleep(250000);
        }

        throw new Exception("Spin function timed out after {$wait} seconds");
    }

    /**
     * @When /^I wait for "([^"]*)" seconds$/
     */
    public function iWaitForSeconds(int $seconds): void
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
    public function thereShouldBeAModalAddressbox(string $address): void
    {
        $page = $this->getPage(CheckoutConfirm::class);

        $testAddress = array_values(array_filter(explode(', ', $address)));

        $checkoutAddressBoxes = $this->getMultipleElement($page, CheckoutAddressBoxModal::class);

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
    public function iClickOnModalAddressbox(string $buttonName, string $address): void
    {
        $page = $this->getPage(CheckoutConfirm::class);

        $testAddress = array_values(array_filter(explode(', ', $address)));

        $checkoutAddressBoxes = $this->getMultipleElement($page, CheckoutAddressBoxModal::class);

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
    public function iShouldSeeAppearInAddressboxAfterDisappeared(string $address, string $title, string $titleToDisappear): void
    {
        $page = $this->getPage(CheckoutConfirm::class);

        $testAddress = array_values(array_filter(explode(', ', $address)));
        if (empty(trim($testAddress[0]))) {
            $testAddress = array_shift($testAddress);
        }
        if (!\is_array($testAddress)) {
            Helper::throwException('Invalid address given');
        }

        $this->spin(function () use ($titleToDisappear) {
            try {
                $this->getMink()->assertSession($this->getSession())->pageTextNotContains(str_replace('\\"', '"', $titleToDisappear));

                return true;
            } catch (ResponseTextException $e) {
                // NOOP
            }

            return false;
        });

        $checkoutAddressBoxes = $this->getMultipleElement($page, CheckoutAddressBox::class);

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
    public function iChangeTheAddress(TableNode $table): void
    {
        $page = $this->getPage(CheckoutConfirm::class);
        $data = $table->getHash();

        $page->changeModalAddress($data);
    }

    /**
     * @Given /^I set "([^"]*)" as default after "([^"]*)" disappeared$/
     */
    public function iSetAsDefaultAfterDisappeared(string $address, string $titleToDisappear): void
    {
        $page = $this->getPage(CheckoutConfirm::class);

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

        $checkoutAddressBoxes = $this->getMultipleElement($page, CheckoutAddressBox::class);

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
    public function theAddressboxMustContain(string $title, string $address): bool
    {
        $page = $this->getPage(CheckoutConfirm::class);

        $testAddress = array_values(array_filter(explode(', ', $address)));

        $checkoutAddressBoxes = $this->getMultipleElement($page, CheckoutAddressBox::class);

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
    public function iClickToAddTheArticleToTheCart(string $locator): void
    {
        $page = $this->getPage(Detail::class);
        Helper::pressNamedButton($page, $locator);
    }

    /**
     * @Given /^I open the cart page$/
     */
    public function iOpenTheCartPage(): void
    {
        $this->getPage(CheckoutCart::class)->open();
    }

    /**
     * @Then /^I open the order confirmation page$/
     */
    public function iOpenTheOrderConfirmationPage(): void
    {
        $this->getPage(CheckoutConfirm::class)->open();
    }

    /**
     * @BeforeScenario @taxation
     */
    public function addCustomTaxation(): void
    {
        $dbal = $this->getService(Connection::class);
        $sql = <<<'EOD'
            INSERT INTO s_core_tax_rules (areaID, countryID, stateID, groupID, customer_groupID, tax, name, active)
            VALUES (3, 23, null, 1, 1, 33, 'Austria', 1)
EOD;
        $dbal->query($sql);
    }

    /**
     * @AfterScenario @taxation
     */
    public function removeCustomTaxation(): void
    {
        $dbal = $this->getService(Connection::class);
        $sql = <<<'EOD'
            DELETE FROM s_core_tax_rules
            WHERE name = 'Austria'
EOD;
        $dbal->query($sql);
    }

    /**
     * @BeforeScenario @dispatchsurcharge
     */
    public function addCustomDispatchSurcharge(): void
    {
        $dbal = $this->getService(Connection::class);
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
    public function removeCustomDispatchSurcharge(): void
    {
        $dbal = $this->getService(Connection::class);
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
    public static function createUserForCheckoutAddressManagementTest(): void
    {
        $dbal = Shopware()->Container()->get(Connection::class);
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
    }

    /**
     * @BeforeScenario @paymentsurcharge
     */
    public function addCustomPaymentSurcharge(): void
    {
        $dbal = $this->getService(Connection::class);
        $sql = <<<'EOD'
            UPDATE s_core_paymentmeans SET debit_percent = 10 WHERE id = 5;
EOD;
        $dbal->query($sql);
    }

    /**
     * @AfterScenario @paymentsurcharge
     */
    public function removeCustomPaymentSurcharge(): void
    {
        $dbal = $this->getService(Connection::class);
        $sql = <<<'EOD'
            UPDATE s_core_paymentmeans SET debit_percent = 0 WHERE id = 5;
EOD;
        $dbal->query($sql);
    }

    /**
     * @Given I checkout using GET
     */
    public function iCheckoutUsingGet(string $path = '/checkout/finish'): void
    {
        $this->getSession()->executeScript(sprintf('window.location.href = \'%s\'', $path));
    }
}
