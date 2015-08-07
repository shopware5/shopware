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
            'filterFilterProperties' => 'div.filter--container > form li.filter-panel--option input[type=checkbox]',
            'filterShowResults' => 'div.filter--container > form > div.filter--actions > button[type=submit]',
            'listingBox' => 'div.listing--container'
        ];
    }

    /**
     * @inheritdoc
     */
    public function verifyPage()
    {
        if(Helper::hasNamedLink($this, 'moreProducts')) {
            return;
        }

        $errors = [];

        if(!$this->hasLink('Filtern')) {
            $errors[] = '- There is no filter link!';
        }

        if(!$this->hasSelect('o')) {
            $errors[] = '- There is no order select!';
        }

        if(!$errors) {
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
        $elements = Helper::findAllOfElements($this, ['filterFilterProperties']);
        /** @var NodeElement $property */
        foreach($elements['filterFilterProperties'] as $property) {
            if($property->isChecked()) {
                $property->uncheck();
            }
        }

        $this->pressShowResults();
    }

    /**
     * @inheritdoc
     */
    public function filter(FilterGroup $filterGroups, array $properties)
    {
        $this->clickLink('Filtern');
        $this->getSession()->wait(5000, "$('ul.searchresult').children().length > 500");

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
        $elements = Helper::findElements($this, ['filterShowResults']);
        /** @var NodeElement $showResults */
        $showResults = $elements['filterShowResults'];
        $showResults->press();
    }
}
