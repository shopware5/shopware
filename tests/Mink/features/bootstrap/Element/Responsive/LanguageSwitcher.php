<?php

namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;

class LanguageSwitcher extends \Emotion\LanguageSwitcher
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.top-bar--language select.language--select');
}