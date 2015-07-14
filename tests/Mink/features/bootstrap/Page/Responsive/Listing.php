<?php
namespace Page\Responsive;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

class Listing extends \Page\Emotion\Listing
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'viewTable' => 'a.action--link.link--table-view',
            'viewList' => 'a.action--link.link--list-view',
            'active' => '.is--active',
            'filterFilterProperties' => 'div.filter--container > form li.filter-panel--option input[type=checkbox]',
            'filterShowResults' => 'div.filter--container > form > div.filter--actions > button[type=submit]',
            'listingBox' => 'div.listing--container'
        );
    }

    /**
     * @throws \Behat\Mink\Exception\ElementException
     * @throws \Exception
     */
    protected function resetFilters()
    {
        $elements = \Helper::findAllOfElements($this, ['filterFilterProperties']);
        /** @var NodeElement $property */
        foreach($elements['filterFilterProperties'] as $property) {
            if($property->isChecked()) {
                $property->uncheck();
            }
        }

        $this->pressShowResults();
    }

    /**
     * @param MultipleElement $filterGroups
     * @param array $properties
     */
    protected function setFilters(MultipleElement $filterGroups, $properties)
    {
        parent::setFilters($filterGroups, $properties);
        $this->pressShowResults();
    }

    /**
     * @throws \Exception
     */
    private function pressShowResults()
    {
        $elements = \Helper::findElements($this, ['filterShowResults']);
        /** @var NodeElement $showResults */
        $showResults = $elements['filterShowResults'];
        $showResults->press();
    }
}
