<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use Shopware\Tests\Mink\Element\Emotion\ArticleBox;
use Shopware\Tests\Mink\Element\MultipleElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Listing extends Page implements HelperSelectorInterface
{
    /**
     * @var string $basePath
     */
    protected $basePath = '/listing/index/sCategory/{sCategory}';

    /**
     * @var string $path
     */
    protected $path = '';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'viewTable' => 'a.table-view.active',
            'viewList' => 'a.list-view.active',
            'active' => '.active',
            'filterCloseLinks' => 'div.filter_properties > div > div.slideContainer > ul > li.close > a',
            'listingBox' => 'div.listing'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array();
    }

    protected $viewSwitchCount = 2;

    /**
     * Opens the listing page
     * @param array $params
     * @param bool $autoPage
     */
    public function openListing($params, $autoPage = true)
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
     * Sets the article filter
     * @param MultipleElement $filterGroups
     * @param $properties
     * @throws \Exception
     */
    public function filter(MultipleElement $filterGroups, $properties)
    {
        $this->resetFilters();
        $this->setFilters($filterGroups, $properties);
    }

    /**
     * @throws \Exception
     */
    protected function resetFilters()
    {
        $locators = array('filterCloseLinks');
        $elements = Helper::findAllOfElements($this, $locators, false);

        if (empty($elements['filterCloseLinks'])) {
            return;
        }

        $closeLinks = array_reverse($elements['filterCloseLinks']);
        foreach ($closeLinks as $closeLink) {
            $closeLink->click();
        }
    }

    /**
     * @param MultipleElement $filterGroups
     * @param $properties
     * @throws \Exception
     */
    protected function setFilters(MultipleElement $filterGroups, $properties)
    {
        foreach ($properties as $property) {
            $found = false;

            foreach ($filterGroups as $filterGroup) {
                $filterGroupName = rtrim($filterGroup->getText(), ' +');

                if ($filterGroupName === $property['filter']) {
                    $found = true;
                    $success = $filterGroup->setProperty($property['value']);

                    if (!$success) {
                        $message = sprintf('The value "%s" was not found for filter "%s"!', $property['value'],
                            $property['filter']);
                        Helper::throwException($message);
                    }

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
     * Checks the view method of the listing. Only $view has to be active
     * @param $view
     */
    public function checkView($view)
    {
        $views = array('viewTable', 'viewList');
        $elements = Helper::findElements($this, $views, false);
        $elements = array_filter($elements);

        if (key($elements) !== $view) {
            $message = sprintf('"%s" is active! (should be "%s")', key($elements), $view);
            Helper::throwException($message);
        }
    }

    /**
     * Checks, whether an article is in the listing or not, is $negation is true, it checks whether an article is NOT in the listing
     *
     * @param string $name
     * @param bool $negation
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
            Helper::throwException(array($message));
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isArticleInListing($name)
    {
        $locator = array('listingBox');
        $elements = Helper::findElements($this, $locator);

        /** @var Element $listingBox */
        $listingBox = $elements['listingBox'];

        return $listingBox->hasLink($name);
    }

    /**
     * @param ArticleBox $articleBox
     * @param array $properties
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
}
