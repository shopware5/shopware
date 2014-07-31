<?php

namespace Responsive;

class LanguageSwitcher extends \Emotion\LanguageSwitcher
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.top-bar--language select.language--select');
}
