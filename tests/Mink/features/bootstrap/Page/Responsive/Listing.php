<?php
namespace Page\Responsive;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;
use Symfony\Component\Console\Helper\Helper;

class Listing extends \Page\Emotion\Listing
{
    public $cssLocator = array(
        'view' => array(
            'table' => 'a.action--link.link--table-view',
            'list' => 'a.action--link.link--list-view'),
        'active' => '.is--active',
        'filterFilterProperties' => 'div.filter--container > form li.filter-panel--option input[type=checkbox]',
        'filterShowResults' => 'div.filter--container > form > div.filter--actions > button[type=submit]',
        'listingBox' => 'div.listing--container'
    );

    /**
     * @throws \Behat\Mink\Exception\ElementException
     * @throws \Exception
     */
    protected function resetFilters()
    {
        $locators = array('filterFilterProperties');
        $elements = \Helper::findElements($this, $locators, null, true);
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
        $locators = array('filterShowResults');
        $elements = \Helper::findElements($this, $locators);
        /** @var NodeElement $showResults */
        $showResults = $elements['filterShowResults'];
        $showResults->press();
    }
}
