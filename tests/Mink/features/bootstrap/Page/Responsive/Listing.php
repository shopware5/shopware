<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Element\Emotion\FilterGroup;
use Shopware\Tests\Mink\Helper;

class Listing extends \Shopware\Tests\Mink\Page\Emotion\Listing
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'viewTable' => 'a.action--link.link--table-view',
            'viewList' => 'a.action--link.link--list-view',
            'active' => '.is--active',
            'filterActiveProperties' => '.filter--active:not([data-filter-param=reset])',
            'filterShowResults' => 'div.filter--container > form > div.filter--actions > button[type=submit]',
            'listingBox' => 'div.listing--container'
        ];
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    protected function resetFilters()
    {
        $elements = Helper::findAllOfElements($this, ['filterActiveProperties'], false);
        $activeProperties = array_reverse($elements['filterActiveProperties']);

        /** @var NodeElement $property */
        foreach ($activeProperties as $property) {
            $property->click();
        }
    }

    /**
     * @inheritdoc
     */
    public function filter(FilterGroup $filterGroups, array $properties)
    {
        $this->clickLink('Filtern');
        $this->resetFilters();
        $this->setFilters($filterGroups, $properties);
        $this->pressShowResults();
    }

    /**
     * Submits the filters
     * @throws \Exception
     */
    private function pressShowResults()
    {
        $this->getSession()->wait(5000, "$('.filter--btn-apply:not(.is--loading):not([disabled=disabled])').length > 0");
        $elements = Helper::findElements($this, ['filterShowResults']);
        /** @var NodeElement $showResults */
        $showResults = $elements['filterShowResults'];
        $showResults->press();
    }
}
