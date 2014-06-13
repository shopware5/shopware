<?php

namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class SearchForm extends \Emotion\SearchForm
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'form.main-search--form');

    public $cssLocator = array(
        'searchInput' => 'input.main-search--field',
        'searchButton' => 'input.main-search--button'
    );
}