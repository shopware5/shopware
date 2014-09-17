<?php

namespace Element\Emotion;

use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class YouTube extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.youtube-element');

    public $cssLocator = array(
        'code' => 'iframe'
    );

    /**
     * @return array
     */
    public function getCodesToCheck()
    {
        $elements = \Helper::findElements($this);

        return array(
            'code' => $elements['code']->getAttribute('src')
        );
    }
}