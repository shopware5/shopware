<?php

namespace Element\Emotion;

require_once 'tests/Mink/features/bootstrap/HelperSelectorInterface.php';

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class AccountPayment extends Element implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#selected_payment > div.inner_container');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'currentMethod' => 'p'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'changeButton' => array('de' => 'Zahlungsart ändern', 'en' => 'Change payment method')
        );
    }

    public function getCurrentMethodsToCheck()
    {
        $locators = array('currentMethod');
        $elements = \Helper::findElements($this, $locators);

        $currentMethod = $elements['currentMethod']->getText();
        $currentMethod = str_word_count($currentMethod, 1);

        return array(
            'currentMethod' => $currentMethod[0]
        );
    }
}
