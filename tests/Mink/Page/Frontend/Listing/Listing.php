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

namespace Shopware\Tests\Mink\Page\Frontend\Listing;

use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Frontend\Article\Elements\ArticleBox;
use Shopware\Tests\Mink\Page\Helper\Elements\FilterGroup;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

class Listing extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $basePath = '/listing/index/sCategory/{sCategory}';

    /**
     * @var string
     */
    protected $path = '';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'viewTable' => 'a.action--link.link--table-view',
            'viewList' => 'a.action--link.link--list-view',
            'active' => '.is--active',
            'filterActiveProperties' => '.filter--active:not([data-filter-param=reset])',
            'filterShowResults' => 'div.filter--container > form > div.filter--actions-bottom > button[type=submit]',
            'listingBox' => 'div.listing--container',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'moreProducts' => ['de' => 'Weitere Artikel in dieser Kategorie', 'en' => 'More articles in this category'],
        ];
    }

    /**
     * Opens the listing page
     *
     * @param bool $autoPage
     */
    public function openListing(array $params, $autoPage = true)
    {
        $parameters = array_merge(
            ['sCategory' => 3],
            $autoPage ? ['sPage' => 1] : [],
            Helper::convertTableHashToArray($params, 'parameter')
        );

        $categoryId = array_shift($parameters);
        $this->path = $this->basePath . '?' . http_build_query($parameters);
        $parameters['sCategory'] = $categoryId;

        $this->open($parameters);
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @throws Exception
     */
    public function verifyPage()
    {
        if (Helper::hasNamedLink($this, 'moreProducts')) {
            return;
        }

        $errors = [];

        if (!$this->hasLink('Filtern')) {
            $errors[] = '- There is no filter link!';
        }

        if (!$this->hasSelect('o')) {
            $errors[] = '- There is no order select!';
        }

        if (!$errors) {
            return;
        }

        $message = ['You are not on a listing:'];
        $message = array_merge($message, $errors);
        $message[] = 'Current URL: ' . $this->getSession()->getCurrentUrl();
        Helper::throwException($message);
    }

    /**
     * Sets the article filter
     *
     * @throws Exception
     */
    public function filter(FilterGroup $filterGroups, array $properties)
    {
        $this->clickLink('Filtern');

        Helper::spin(function () {
            $elements = Helper::findElements($this, ['filterShowResults']);
            $showResults = $elements['filterShowResults'];
            if ($showResults->isVisible()) {
                return true;
            }

            return false;
        });

        $this->resetFilters();
        if ($properties) {
            $this->setFilters($filterGroups, $properties);
        }
        $this->pressShowResults();
    }

    /**
     * Checks the view method of the listing. Only $view has to be active
     *
     * @param string $view
     */
    public function checkView($view)
    {
        $elements = Helper::findElements($this, ['viewTable', 'viewList'], false);

        if (key($elements) !== $view) {
            $message = sprintf('"%s" is active! (should be "%s")', key($elements), $view);
            Helper::throwException($message);
        }
    }

    /**
     * Checks, whether an article is in the listing or not, is $negation is true, it checks whether an article is NOT in the listing
     *
     * @param string $name
     * @param bool   $negation
     */
    public function checkListing($name, $negation = false)
    {
        $result = $this->isArticleInListing($name);

        if ($negation) {
            $result = !$result;
        }

        if (!$result) {
            $message = sprintf(
                'The article "%s" is%s in the listing, but should%s.',
                $name,
                $negation ? '' : ' not',
                $negation ? ' not' : ''
            );
            Helper::throwException([$message]);
        }
    }

    /**
     * Checks the properties of a product box
     *
     * @throws Exception
     */
    public function checkArticleBox(ArticleBox $articleBox, array $properties)
    {
        $properties = Helper::floatArray($properties, ['price']);
        $result = Helper::assertElementProperties($articleBox, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The %s is "%s" (should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * Resets all filters
     */
    protected function resetFilters()
    {
        $elements = Helper::findAllOfElements($this, ['filterActiveProperties'], false);
        $activeProperties = array_reverse($elements['filterActiveProperties']);
        if (empty($activeProperties)) {
            return;
        }
        $elements = Helper::findElements($this, ['filterShowResults']);
        $showResults = $elements['filterShowResults'];

        $activeProperties[0]->click();
        Helper::spin(function () use ($showResults) {
            if (!$showResults->hasClass('is--loading')) {
                usleep(100);

                return true;
            }

            return false;
        });
        $this->resetFilters();
    }

    /**
     * Sets the filters
     *
     * @throws Exception
     */
    protected function setFilters(FilterGroup $filterGroups, array $properties)
    {
        $elements = Helper::findElements($this, ['filterShowResults']);
        $showResults = $elements['filterShowResults'];

        foreach ($properties as $property) {
            $found = false;

            foreach ($filterGroups as $filterGroup) {
                $filterGroupName = rtrim($filterGroup->getText(), ' +');

                if ($filterGroupName === $property['filter']) {
                    $found = true;
                    $success = $filterGroup->setProperty($property['value']);

                    if (!$success) {
                        $message = sprintf('The value "%s" was not found for filter "%s"!', $property['value'], $property['filter']);
                        Helper::throwException($message);
                    }

                    Helper::spin(function () use ($showResults) {
                        if (!$showResults->hasClass('is--loading')) {
                            return true;
                        }

                        return false;
                    });

                    break;
                }
            }

            if (!$found) {
                $message = sprintf('The filter "%s" was not found!', $property['filter']);
                Helper::throwException($message);
            }
        }
    }

    /**
     * Checks, if a product is in the listing
     *
     * @param string $name
     *
     * @return bool
     */
    private function isArticleInListing($name)
    {
        $elements = Helper::findElements($this, ['listingBox']);
        $listingBox = $elements['listingBox'];

        return $listingBox->hasLink($name);
    }

    /**
     * Submits the filters
     *
     * @throws Exception
     */
    private function pressShowResults()
    {
        $elements = Helper::findElements($this, ['filterShowResults']);
        $showResults = $elements['filterShowResults'];
        Helper::spin(function () use ($showResults) {
            if (!$showResults->hasClass('is--loading')) {
                return true;
            }

            return false;
        });
        $showResults->press();
    }
}
