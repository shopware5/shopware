<?php

namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Paging extends \Emotion\Paging
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.listing--paging');

    public $cssLocator = array(
        'previous' => 'a.pagination--link.paging--prev',
        'next' => 'a.pagination--link.paging--next'
    );
}