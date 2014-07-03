<?php

namespace Emotion;

require_once('tests/Mink/features/bootstrap/Element/TextInputForm.php');

class SearchForm extends \TextInputForm
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#searchcontainer');

    public $cssLocator = array(
        'textInput' => 'input#searchfield',
        'submitButton' => 'input#submit_search_btn'
    );

    /**
     * Search the given term using live search
     * @param $searchTerm
     */
    public function receiveSearchResultsFor($searchTerm)
    {
        $elements = array('textInput');
        $element = \Helper::findElements($this, $elements);

        $element['textInput']->setValue($searchTerm);
        $this->getSession()->wait(5000, "$('ul.searchresult').children().length > 0");
    }
}