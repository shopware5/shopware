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

namespace Shopware\Tests\Mink\Tests\Frontend\Homepage\bootstrap;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use RuntimeException;
use Shopware\Tests\Mink\Page\Frontend\Article\Elements\Article;
use Shopware\Tests\Mink\Page\Frontend\Article\Elements\ArticleSlider;
use Shopware\Tests\Mink\Page\Frontend\Blog\Elements\BlogArticle;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\Banner;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\CategoryTeaser;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\CompareColumn;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\ManufacturerSlider;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\YouTube;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Homepage;
use Shopware\Tests\Mink\Page\Frontend\Newsletter\Newsletter;
use Shopware\Tests\Mink\Page\Helper\Elements\BannerSlider;
use Shopware\Tests\Mink\Page\Helper\Elements\HeaderCart;
use Shopware\Tests\Mink\Tests\General\Helpers\FeatureContext;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class ShopwareContext extends SubContext
{
    protected FeatureContext $featureContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();
        if (!$environment instanceof InitializedContextEnvironment) {
            Helper::throwException('Scope has unexpected environment');
        }

        $this->featureContext = $environment->getContext(FeatureContext::class);
    }

    /**
     * @When /^I search for "(?P<searchTerm>[^"]*)"$/
     */
    public function iSearchFor(string $searchTerm): void
    {
        $this->getPage(Homepage::class)->searchFor($searchTerm);
    }

    /**
     * @When /^I received the search-results for "(?P<searchTerm>[^"]*)"$/
     */
    public function iReceivedTheSearchResultsFor(string $searchTerm): void
    {
        $this->getPage(Homepage::class)->receiveSearchResultsFor($searchTerm);
    }

    /**
     * @Then /^I should see the no results message for keyword "([^"]*)"$/
     */
    public function iShouldSeeTheNoResultsMessageForKeyword(): void
    {
        $this->getPage(Homepage::class)->receiveNoResultsMessageForKeyword();
    }

    /**
     * @When /^I change the currency to "(?P<currency>[^"]*)"$/
     */
    public function iChangeTheCurrencyTo(string $currency): void
    {
        $this->getPage(Homepage::class)->changeCurrency($currency);
    }

    /**
     * @Then /^the comparison should contain the following products:$/
     */
    public function theComparisonShouldContainTheFollowingProducts(TableNode $items): void
    {
        $page = $this->getPage(Homepage::class);

        $compareColumns = $this->getMultipleElement($page, CompareColumn::class);

        $page->checkComparisonProducts($compareColumns, $items->getHash());
    }

    /**
     * @Then /^the cart should contain (?P<quantity>\d+) articles with a value of "(?P<amount>[^"]*)"$/
     */
    public function theCartShouldContainArticlesWithAValueOf(int $quantity, string $amount): void
    {
        $this->getElement(HeaderCart::class)->checkCart($quantity, $amount);
    }

    /**
     * @When /^I subscribe to the newsletter with "(?P<email>[^"]*)"$/
     * @When /^I subscribe to the newsletter with "(?P<email>[^"]*)" :$/
     */
    public function iSubscribeToTheNewsletterWith(string $email, ?TableNode $additionalData = null): void
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller']);
        if (!\is_array($pageInfo)) {
            Helper::throwException('Could not get page info');
        }
        $pageName = ucfirst($pageInfo['controller']);
        $data = [
            [
                'field' => 'newsletter',
                'value' => $email,
            ],
        ];

        if ($pageName === 'Index') {
            $pageName = Homepage::class;
        } elseif ($pageName === 'Newsletter') {
            $pageName = Newsletter::class;
            if ($additionalData) {
                $data = array_merge($data, $additionalData->getHash());
            }
        } else {
            Helper::throwException('Wrong page for subscribing to newsletter');
        }

        $page = $this->getPage($pageName);
        $page->subscribeNewsletter($data);
    }

    /**
     * @When /^I unsubscribe the newsletter$/
     * @When /^I unsubscribe the newsletter with "(?P<email>[^"]*)"$/
     */
    public function iUnsubscribeTheNewsletter(?string $email = null): void
    {
        $data = [];

        if ($email) {
            $data = [
                [
                    'field' => 'newsletter',
                    'value' => $email,
                ],
            ];
        }

        $this->getPage(Newsletter::class)->unsubscribeNewsletter($data);
    }

    /**
     * @When /^I click the link in my latest email$/
     * @When /^I click the links in my latest (\d+) emails$/
     */
    public function iConfirmTheLinkInTheEmail(int $limit = 1): void
    {
        $sql = 'SELECT `type`, `hash` FROM `s_core_optin` ORDER BY `id` DESC LIMIT ' . $limit;
        $hashes = $this->getService('db')->fetchAll($sql);

        $session = $this->getSession();
        $link = $session->getCurrentUrl();

        foreach ($hashes as $optin) {
            if ($optin['type'] === 'swPassword') {
                $mask = '%saccount/resetPassword/hash/%s';
                $link = $this->getPage(Homepage::class)->getShopUrl();

                $confirmationLink = sprintf($mask, $link, $optin['hash']);
                $session->visit($confirmationLink);

                return;
            }
        }

        $query = parse_url($link, PHP_URL_QUERY);
        $anchor = strpos($link, '#');

        if ($anchor) {
            $link = substr($link, 0, $anchor);
        }

        // Blogartikel-Bewertung
        if (empty($query)) {
            $mask = '%s/sConfirmation/%s';
        } else {
            parse_str($query, $args);

            switch ($args['action']) {
                // Artikel-Benachrichtigungen
                case 'notify':
                    $mask = '%s&sNotificationConfirmation=%s&sNotify=1&action=notifyConfirm';
                    break;

                    // Artikel-Bewertungen
                default:
                    $mask = '%s&sConfirmation=%s';
                    break;
            }
        }

        foreach ($hashes as $optin) {
            $confirmationLink = sprintf($mask, $link, $optin['hash']);
            $session->visit($confirmationLink);
        }
    }

    /**
     * @When /^I enable the config "([^"]*)"$/
     */
    public function iEnableTheConfig(string $configName): void
    {
        $this->theConfigValueOfIs($configName, true);
    }

    /**
     * @When /^I disable the config "([^"]*)"$/
     */
    public function iDisableTheConfig(string $configName): void
    {
        $this->theConfigValueOfIs($configName, false);
    }

    /**
     * @When /^the config value of "([^"]*)" is (\d+)$/
     * @When /^the config value of "([^"]*)" is "([^"]*)"$/
     *
     * @param bool|int|string $value
     */
    public function theConfigValueOfIs(string $configName, $value): void
    {
        $this->featureContext->changeConfigValue($configName, $value);
    }

    /**
     * @When the emotion world has loaded
     */
    public function theEmotionWorldHasLoaded(): void
    {
        $this->getSession()->wait(15000, "$($(':plugin-swEmotionLoader').get(0)).data('plugin_swEmotionLoader').isLoading == false");
    }

    /**
     * @Given /^I should see a banner with image "(?P<image>[^"]*)"$/
     * @Given /^I should see a banner with image "(?P<image>[^"]*)" to "(?P<link>[^"]*)"$/
     */
    public function iShouldSeeABanner(string $image, ?string $link = null): void
    {
        $this->iShouldSeeABannerOnPositionWithImage(1, $image, $link);
    }

    /**
     * @Given /^I should see a banner on position (\d+) with image "([^"]*)"$/
     * @Given /^I should see a banner on position (\d+) with image "([^"]*)" to "([^"]*)"$/
     */
    public function iShouldSeeABannerOnPositionWithImage(int $position, string $image, ?string $link = null): void
    {
        $page = $this->getPage(Homepage::class);

        $banner = $this->getMultipleElement($page, Banner::class, $position);
        $page->checkLinkedBanner($banner, $image, $link);
    }

    /**
     * @Given /^I should see a banner with image "(?P<image>[^"]*)" and mapping:$/
     */
    public function iShouldSeeABannerWithMapping(string $image, TableNode $mapping): void
    {
        $this->iShouldSeeABannerOnPositionWithImageAndMapping(1, $image, $mapping);
    }

    /**
     * @Given /^I should see a banner on position (\d+) with image "([^"]*)" and mapping:$/
     */
    public function iShouldSeeABannerOnPositionWithImageAndMapping(int $position, string $image, TableNode $mapping): void
    {
        $page = $this->getPage(Homepage::class);

        $banner = $this->getMultipleElement($page, Banner::class, $position);
        $mapping = $mapping->getHash();

        $page->checkMappedBanner($banner, $image, $mapping);
    }

    /**
     * @Given /^the product box on position (\d+) should have the following properties:$/
     */
    public function iShouldSeeAnArticle(int $position, TableNode $data): void
    {
        $page = $this->getPage(Homepage::class);

        $article = $this->getMultipleElement($page, Article::class, $position);

        $page->checkArticle($article, $data->getHash());
    }

    /**
     * @Given /^the category teaser on position (\d+) for "(?P<name>[^"]*)" should have the image "(?P<image>[^"]*)" and link to "(?P<link>[^"]*)"$/
     */
    public function iShouldSeeACategoryTeaserWithImageTo(int $position, string $name, string $image, string $link): void
    {
        $page = $this->getPage(Homepage::class);

        $teaser = $this->getMultipleElement($page, CategoryTeaser::class, $position);

        $page->checkCategoryTeaser($teaser, $name, $image, $link);
    }

    /**
     * @Given /^I should see some blog articles:$/
     */
    public function iShouldSeeSomeBlogArticles(TableNode $articles): void
    {
        $page = $this->getPage(Homepage::class);

        $blogArticle = $this->getMultipleElement($page, BlogArticle::class, 1);

        $articles = $articles->getHash();

        $page->checkBlogArticles($blogArticle, $articles);
    }

    /**
     * @Then /^I should see a banner slider:$/
     */
    public function iShouldSeeABannerSlider(TableNode $slides): void
    {
        $page = $this->getPage(Homepage::class);

        $slider = $this->getMultipleElement($page, BannerSlider::class, 1);

        $page->checkSlider($slider, $slides->getHash());
    }

    /**
     * @Given /^I should see a YouTube-Video "(?P<code>[^"]*)"$/
     */
    public function iShouldSeeAYoutubeVideo(string $code): void
    {
        $page = $this->getPage(Homepage::class);

        $youtube = $this->getMultipleElement($page, YouTube::class, 1);

        $page->checkYoutubeVideo($youtube, $code);
    }

    /**
     * @Then /^I should see a manufacturer slider:$/
     */
    public function iShouldSeeAManufacturerSlider(TableNode $manufacturers): void
    {
        $page = $this->getPage(Homepage::class);

        $slider = $this->getMultipleElement($page, ManufacturerSlider::class, 1);

        $page->checkManufacturerSlider($slider, $manufacturers->getHash());
    }

    /**
     * @Then /^I should see an article slider:$/
     */
    public function iShouldSeeAnArticleSlider(TableNode $productTable): void
    {
        $page = $this->getPage(Homepage::class);

        $slider = $this->getMultipleElement($page, ArticleSlider::class, 1);

        $products = Helper::floatArray($productTable->getHash(), ['price']);

        $page->checkSlider($slider, $products);
    }

    /**
     * @Then /^I the language should be "([^"]*)"$/
     */
    public function iTheLanguageShouldBe(string $language): void
    {
        Helper::setCurrentLanguage($language);
    }

    /**
     * @When I scroll to the bottom of the page
     */
    public function iScrollToTheBottomOfThePage(): void
    {
        $this->getDriver()->executeScript('window.scrollTo(0, document.body.scrollHeight);');
    }

    /**
     * @When /^I click "([^"]*)"$/
     */
    public function iClick(string $selector): void
    {
        $element = $this->getSession()->getPage()->findField($selector);
        if (!$element instanceof NodeElement) {
            Helper::throwException(sprintf('Could not find element with selector "%s"', $selector));
        }
        $element->click();
    }

    /**
     * @When /^I follow "(?P<link>(?:[^"]|\\")*)" on Account menu$/
     */
    public function clickLinkInAccount(string $link): void
    {
        $element = $this->getSession()->getPage()->findAll('xpath', sprintf('//*[contains(concat(" ",normalize-space(@class)," ")," account--menu ")]//li//a[contains(text(),\'%s\')]', $link));

        if (!isset($element[1])) {
            throw new RuntimeException(sprintf('Cannot find element with name "%s"', $link));
        }

        $element[1]->click();
    }

    /**
     * @When /^Wait until ajax requests are done$/
     */
    public function waitForAjaxRequestsDone(): void
    {
        $session = $this->getSession();
        $this->getSession()->getPage()->waitFor(2000, static function () use ($session) {
            return $session->evaluateScript('return jQuery.active == 0') === true;
        });
    }
}
