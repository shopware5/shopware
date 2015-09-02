<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Element: LanguageSwitcher
 * Location: Language switcher on top of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class LanguageSwitcher extends Element
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#topbar > div.topbar_lang select.lang_select');

    public $cssLocators = [
        'languages' => 'option'
    ];

    /**
     * Returns the current language
     * Use this only for asserts. If you only need the current language, use Helper::getCurrentLanguage().
     * @return string
     * @deprecated
     */
    public function getCurrentLanguage()
    {
        $languageKeys = array(1 => 'de', 2 => 'en');

        $languages = $this->findAll('css', $this->cssLocators['languages']);

        foreach ($languages as $language) {
            if ($language->getAttribute('selected')) {
                return $languageKeys[$language->getAttribute('value')];
            }
        }

        return 'de';
    }

    /**
     * Changes the language
     * @param string $language
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function setLanguage($language)
    {
        $this->selectOption($language);
    }

}
