<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: LanguageSwitcher
 * Location: Language switcher on top of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class LanguageSwitcher extends \Shopware\Tests\Mink\Element\Emotion\LanguageSwitcher
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.top-bar--language select.language--select'];
}
