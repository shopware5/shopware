<?php

namespace Element\Responsive;

class SearchForm extends \Element\Emotion\SearchForm
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'form.main-search--form');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'searchForm' => '.main-search--form',
        );
    }
}