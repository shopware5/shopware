<?php

namespace Element\Responsive;

/**
 * Element: LanguageSwitcher
 * Location: Language switcher on top of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class LanguageSwitcher extends \Element\Emotion\LanguageSwitcher
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.top-bar--language select.language--select'];
}
