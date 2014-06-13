<?php

namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class SearchForm extends Element
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#searchcontainer');

    public $cssLocator = array(
        'searchInput' => 'input#searchfield',
        'searchButton' => 'input#submit_search_btn'
    );

    /**
     * @param string $keywords
     *
     * @return Page
     */
    public function searchFor($searchTerm)
    {
        $element = \Helper::findElements($this);

        $element['searchInput']->setValue($searchTerm);
        $element['searchButton']->press();
    }

    /**
     * Search the given term using live search
     * @param $searchTerm
     */
    public function receiveSearchResultsFor($searchTerm)
    {
        $elements = array('searchInput');
        $element = \Helper::findElements($this, $elements);

        $element['searchInput']->setValue($searchTerm);
        $this->getSession()->wait(5000, "$('ul.searchresult').children().length > 0");
    }
}