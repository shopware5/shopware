<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Mink\Page\Frontend\Homepage;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\WebAssert;
use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Frontend\Article\Elements\Article;
use Shopware\Tests\Mink\Page\Frontend\Blog\Elements\BlogArticle;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\Banner;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\CategoryTeaser;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\CompareColumn;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\SearchForm;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\SliderElement;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\YouTube;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

class Homepage extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = '/';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'newsletterForm' => 'form.newsletter--form',
            'newsletterFormSubmit' => 'form.newsletter--form button[type="submit"]',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @throws Exception
     */
    public function verifyPage()
    {
        $info = Helper::getPageInfo($this->getSession(), ['controller']);

        if ($info['controller'] === 'index') {
            return;
        }

        $message = ['You are not on the homepage!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
        Helper::throwException($message);
    }

    /**
     * Searches the given term in the shop
     */
    public function searchFor(string $searchTerm): void
    {
        $data = [
            [
                'field' => 'sSearch',
                'value' => $searchTerm,
            ],
        ];

        $searchForm = $this->getElement(SearchForm::class);
        Helper::fillForm($searchForm, 'searchForm', $data);
        Helper::pressNamedButton($searchForm, 'searchButton');
        $this->verifyResponse();
    }

    /**
     * Search the given term using live search
     */
    public function receiveSearchResultsFor(string $searchTerm): void
    {
        $data = [
            [
                'field' => 'sSearch',
                'value' => $searchTerm,
            ],
        ];

        $searchForm = $this->getElement(SearchForm::class);
        Helper::fillForm($searchForm, 'searchForm', $data);
        $this->getSession()->wait(250000, "$('ul.results--list').children().length > 0");
    }

    public function receiveNoResultsMessageForKeyword(): void
    {
        // $keyword gets ignored in responsive template
        $assert = new WebAssert($this->getSession());
        $assert->pageTextContains('Leider wurden zu Ihrer Suchanfrage keine Artikel gefunden');
    }

    /**
     * Changes the currency
     *
     * @throws ElementNotFoundException
     */
    public function changeCurrency(string $currency): void
    {
        if (!$this->getDriver() instanceof Selenium2Driver) {
            Helper::throwException('Changing the currency in Responsive template requires Javascript!');
        }

        $valid = ['EUR' => '€ EUR', 'USD' => '$ USD'];
        $this->selectFieldOption('__currency', $valid[$currency]);
    }

    public function subscribeNewsletter(array $data)
    {
        Helper::fillForm($this, 'newsletterForm', $data);

        $elements = Helper::findElements($this, ['newsletterFormSubmit']);
        $elements['newsletterFormSubmit']->press();
    }

    /**
     * Checks the product comparison
     * Available properties are: image, name, ranking, description, price, link
     */
    public function checkComparisonProducts(CompareColumn $compareColumns, array $items)
    {
        Helper::assertElementCount($compareColumns, \count($items));
        $result = Helper::searchElements($items, $compareColumns);

        if ($result !== true) {
            $messages = ['The following articles were not found:'];
            foreach ($result as $product) {
                $messages[] = $product['name'];
            }
            Helper::throwException($messages);
        }
    }

    /**
     * Checks an emotion banner with or without link
     *
     * @param string      $image
     * @param string|null $link
     */
    public function checkLinkedBanner(Banner $banner, $image, $link = null)
    {
        $properties = [
            'image' => $image,
        ];

        if (!\is_null($link)) {
            $properties['link'] = $link;
        }

        $result = Helper::assertElementProperties($banner, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The banner %s is "%s" (should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * Checks an emotion banner with mapping
     *
     * @param string   $image
     * @param string[] $mapping
     */
    public function checkMappedBanner(Banner $banner, $image, array $mapping)
    {
        $this->checkLinkedBanner($banner, $image);

        $bannerMapping = $banner->getMapping();
        $result = Helper::compareArrays($bannerMapping, $mapping);

        if ($result === true) {
            return;
        }

        $message = [
            'The banner mappings are different!',
            'Given: ' . $result['value'],
            'Expected: ' . $result['value2'],
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion blog element
     *
     * @param array $articles
     *
     * @throws Exception
     */
    public function checkBlogArticles(BlogArticle $blogArticle, $articles)
    {
        $properties = array_keys(current($articles));

        $blogArticles = $blogArticle->getArticles($properties);

        $result = Helper::compareArrays($blogArticles, $articles);

        if ($result === true) {
            return;
        }

        $message = [
            sprintf('The slides have a different %s!', $result['key']),
            'Given: ' . $result['value'],
            'Expected: ' . $result['value2'],
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion Youtube element
     *
     * @param string $code
     *
     * @throws Exception
     */
    public function checkYoutubeVideo(YouTube $youtube, $code)
    {
        $result = Helper::assertElementProperties($youtube, ['code' => $code]);

        if ($result === true) {
            return;
        }

        $message = [
            'The YouTube video has a different code!',
            'Given: ' . $result['value'],
            'Expected: ' . $result['value2'],
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion slider element
     */
    public function checkSlider(SliderElement $slider, array $slides)
    {
        $properties = array_keys(current($slides));

        $sliderSlides = \array_slice($slider->getSlides($properties), 0, \count($slides));

        $result = Helper::compareArrays($sliderSlides, $slides);

        if ($result === true) {
            return;
        }

        $message = [
            sprintf('The slides have a different %s!', $result['key']),
            'Given: ' . print_r($result['value'], true),
            'Expected: ' . print_r($result['value2'], true),
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion manufacturer slider element
     */
    public function checkManufacturerSlider(SliderElement $slider, array $slides)
    {
        $properties = array_keys(current($slides));

        $sliderSlides = \array_slice($slider->getSlides($properties), 0, \count($slides));

        usort($sliderSlides, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        usort($slides, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $result = Helper::compareArrays($sliderSlides, $slides);

        if ($result === true) {
            return;
        }

        $message = [
            sprintf('The slides have a different %s!', $result['key']),
            'Given: ' . print_r($result['value'], true),
            'Expected: ' . print_r($result['value2'], true),
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion category teaser element
     *
     * @param string $name
     * @param string $image
     * @param string $link
     */
    public function checkCategoryTeaser(CategoryTeaser $teaser, $name, $image, $link)
    {
        $properties = [
            'name' => $name,
            'image' => $image,
            'link' => $link,
        ];

        $result = Helper::assertElementProperties($teaser, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The category teaser %s is "%s" (should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * Checks an emotion article element
     */
    public function checkArticle(Article $article, array $data)
    {
        $properties = Helper::convertTableHashToArray($data);
        $properties = Helper::floatArray($properties, ['price']);

        $result = Helper::assertElementProperties($article, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The article %s is "%s" (should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * Returns the shop url
     *
     * @return string
     */
    public function getShopUrl()
    {
        return $this->getUrl();
    }
}
