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

namespace Shopware\Tests\Mink\Page;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Element\ArticleBox;
use Shopware\Tests\Mink\Element\FilterGroup;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

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
            ($autoPage) ? ['sPage' => 1] : [],
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function filter(FilterGroup $filterGroups, array $properties)
    {
        $this->clickLink('Filtern');

        $this->spin(function () {
            $elements = Helper::findElements($this, ['filterShowResults']);
            /** @var NodeElement $showResults */
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
        $elements = array_filter(Helper::findElements($this, ['viewTable', 'viewList'], false));

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
                ($negation) ? '' : ' not',
                ($negation) ? ' not' : ''
            );
            Helper::throwException([$message]);
        }
    }

    /**
     * Checks the properties of a product box
     *
     * @throws \Exception
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
     * Based on Behat's own example
     *
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     *
     * @param callable $lambda
     * @param int      $wait
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function spin($lambda, $wait = 60)
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {
                // do nothing
            }

            usleep(250000);
        }

        throw new \Exception("Spin function timed out after {$wait} seconds");
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
        /** @var NodeElement $showResults */
        $showResults = $elements['filterShowResults'];

        $activeProperties[0]->click();
        $this->spin(function () use ($showResults) {
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
     * @throws \Exception
     */
    protected function setFilters(FilterGroup $filterGroups, array $properties)
    {
        $elements = Helper::findElements($this, ['filterShowResults']);
        /** @var NodeElement $showResults */
        $showResults = $elements['filterShowResults'];

        foreach ($properties as $property) {
            $found = false;

            /** @var FilterGroup $filterGroup */
            foreach ($filterGroups as $filterGroup) {
                $filterGroupName = rtrim($filterGroup->getText(), ' +');

                if ($filterGroupName === $property['filter']) {
                    $found = true;
                    $success = $filterGroup->setProperty($property['value']);

                    if (!$success) {
                        $message = sprintf('The value "%s" was not found for filter "%s"!', $property['value'], $property['filter']);
                        Helper::throwException($message);
                    }

                    $this->spin(function () use ($showResults) {
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
     * Based on Behat's own example
     *
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     *
     * @param callable $lambda
     * @param int      $wait
     *
     * @return bool
     */
    protected function spinWithNoException($lambda, $wait = 60)
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {
                // do nothing
            }

            usleep(250000);
        }

        return false;
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
     * @throws \Exception
     */
    private function pressShowResults()
    {
        $elements = Helper::findElements($this, ['filterShowResults']);
        /** @var NodeElement $showResults */
        $showResults = $elements['filterShowResults'];
        $this->spin(function () use ($showResults) {
            if (!$showResults->hasClass('is--loading')) {
                return true;
            }

            return false;
        });
        $showResults->press();
    }
}
