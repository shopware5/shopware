<?php

namespace Element\Emotion;

use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class AddressBox extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.select_billing');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'title' => '.bold'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'chooseButton'  => array('de' => 'Auswählen',   'en' => 'Select')
        );
    }
}
