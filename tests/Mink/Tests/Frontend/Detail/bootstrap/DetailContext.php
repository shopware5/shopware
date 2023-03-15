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

namespace Shopware\Tests\Mink\Tests\Frontend\Detail\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Tests\Mink\Page\Frontend\Article\Elements\ArticleEvaluation;
use Shopware\Tests\Mink\Page\Frontend\Detail\Detail;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class DetailContext extends SubContext
{
    /**
     * @Given /^I am on the detail page for article (?P<productId>\d+)$/
     *
     * @When /^I go to the detail page for article (?P<productId>\d+)$/
     */
    public function iAmOnTheDetailPageForArticle(int $productId): void
    {
        $this->getPage(Detail::class)->open(['articleId' => $productId, 'number' => '']);
    }

    /**
     * @Given /^I am on the detail page for variant "(?P<number>[^"]*)" of article (?P<productId>\d+)$/
     *
     * @When /^I go to the detail page for variant "(?P<number>[^"]*)" of article (?P<productId>\d+)$/
     */
    public function iAmOnTheDetailPageForVariantOfArticle(string $number, int $productId): void
    {
        $this->getPage(Detail::class)->open(['articleId' => $productId, 'number' => $number]);
    }

    /**
     * @When /^I put the article into the basket$/
     * @When /^I put the article "(?P<quantity>[^"]*)" times into the basket$/
     */
    public function iPutTheArticleTimesIntoTheBasket(int $quantity = 1): void
    {
        $page = $this->getPage(Detail::class);
        $page->addToBasket($quantity);
    }

    /**
     * @Given /^I should see an average customer evaluation of (?P<average>\d+) from following evaluations:$/
     */
    public function iShouldSeeAnAverageCustomerEvaluationOfFromFollowingEvaluations(string $average, TableNode $evaluations): void
    {
        $page = $this->getPage(Detail::class);

        $articleEvaluations = $this->getMultipleElement($page, ArticleEvaluation::class);
        $evaluations = $evaluations->getHash();

        $page->checkEvaluations($articleEvaluations, $average, $evaluations);
    }

    /**
     * @When /^I choose the following article configuration:$/
     */
    public function iChooseTheFollowingArticleConfiguration(TableNode $configuration): void
    {
        $configuration = $configuration->getHash();

        $this->getPage(Detail::class)->configure($configuration);
    }

    /**
     * @Then /^I can not select "([^"]*)" from "([^"]*)"$/
     */
    public function iCanNotSelectFrom(string $configuratorOption, string $configuratorGroup): void
    {
        $this->getPage(Detail::class)->canNotSelectConfiguratorOption($configuratorOption, $configuratorGroup);
    }

    /**
     * @When /^I write an evaluation:$/
     */
    public function iWriteAnEvaluation(TableNode $data): void
    {
        $this->getPage(Detail::class)->writeEvaluation($data->getHash());
    }

    /**
     * @When /^the shop owner activates my latest evaluation$/
     * @When /^the shop owner activates my latest (\d+) evaluations$/
     */
    public function theShopOwnerActivateMyLatestEvaluation(int $limit = 1): void
    {
        $sql = 'UPDATE `s_articles_vote` SET `active`= 1 ORDER BY id DESC LIMIT ' . $limit;
        $this->getService(Connection::class)->executeStatement($sql);
    }

    /**
     * @Given /^I can select every (\d+)\. option of "([^"]*)" from "([^"]*)" to "([^"]*)"$/
     */
    public function iCanSelectEveryOptionOfFromTo(int $graduation, string $select, string $min, string $max): void
    {
        $this->getPage(Detail::class)->checkSelect($select, $min, $max, $graduation);
    }

    /**
     * @When /^I submit the notification form with "([^"]*)"$/
     */
    public function iSubmitTheNotificationFormWith(string $email): void
    {
        $this->getPage(Detail::class)->submitNotification($email);
    }

    /**
     * @When /^I open the evaluation form$/
     */
    public function iOpenTheEvaluationForm(): void
    {
        $page = $this->getPage(Detail::class);
        $page->openEvaluationSection();
    }

    /**
     * @Given /^The notification plugin is activated$/
     */
    public function theNotificationPluginIsActivated(): void
    {
        $pluginManager = $this->getService(InstallerService::class);
        $plugin = $pluginManager->getPluginByName('Notification');
        $pluginManager->activatePlugin($plugin);
    }

    /**
     * @Given /^The notification plugin is deactivated$/
     */
    public function theNotificationPluginIsDeactivated(): void
    {
        $pluginManager = $this->getService(InstallerService::class);
        $plugin = $pluginManager->getPluginByName('Notification');
        $pluginManager->deactivatePlugin($plugin);
    }

    /**
     * @When /^I open the comparison menu$/
     */
    public function iOpenTheComparisonMenu(): void
    {
        $page = $this->getPage(Detail::class);
        $openMenu = $page->find('css', '.entry--compare');

        if (!($openMenu instanceof NodeElement)) {
            throw new ElementNotFoundException($this->getDriver(), null, 'css', '.entry--compare');
        }
        $openMenu->click();

        Helper::waitForOverlay($this->getSession()->getPage());
    }

    /**
     * @When I compare the current product
     */
    public function iCompareTheCurrentProduct(): void
    {
        $page = $this->getPage(Detail::class);
        $doCompare = $page->find('css', '.action--compare');

        if (!($doCompare instanceof NodeElement)) {
            throw new ElementNotFoundException($this->getDriver(), null, 'css', '.action--compare');
        }
        $doCompare->click();

        Helper::waitForOverlay($this->getSession()->getPage());
    }

    /**
     * @When I start the comparison
     */
    public function iStartTheComparison(): void
    {
        $page = $this->getPage(Detail::class);
        $startComparison = $page->find('css', '.btn--compare-start');

        if (!($startComparison instanceof NodeElement)) {
            throw new ElementNotFoundException($this->getDriver(), null, 'css', '.btn--compare-start');
        }

        $startComparison->click();
        $startComparison->waitFor(4, function () use ($startComparison) {
            return $startComparison->find('css', '.btn--product') !== null;
        });
    }

    /**
     * @When I press the button to go the product details
     */
    public function iPressButtonForProductDetails(): void
    {
        $page = $this->getPage(Detail::class);
        $goDetails = $page->find('css', '.btn--product');

        if (!($goDetails instanceof NodeElement)) {
            throw new ElementNotFoundException($this->getDriver(), null, 'css', '.btn--product');
        }

        $goDetails->click();
    }

    /**
     * @Then I should be at the detail page of compared product
     */
    public function iShouldSeeDetailPage(): void
    {
        $page = $this->getPage(Detail::class);

        $page->waitFor(4, function () use ($page) {
            return $page->find('xpath', '//*a[text()="Artikel mit Bewertung"]') === null;
        });
    }
}
