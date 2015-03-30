<?php

namespace Element\Emotion;

use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class YouTube extends MultipleElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.youtube-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'code' => 'iframe'
        );
    }

    /**
     * @return array
     */
    public function getCodesToCheck()
    {
        $locator = array('code');
        $elements = \Helper::findElements($this, $locator);

        return array(
            'code' => $elements['code']->getAttribute('src')
        );
    }
}