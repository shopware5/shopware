<?php

namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class LanguageSwitcher extends Element
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#topbar > div.topbar_lang select.lang_select');

    public $cssLocators = array(
        'languages' => 'option'
    );

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

    public function setLanguage($language)
    {
        $this->selectOption($language);
    }

}
