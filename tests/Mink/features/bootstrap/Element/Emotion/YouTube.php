<?php

namespace Element\Emotion;

use Element\MultipleElement;

/**
 * Element: YouTube
 * Location: Emotion element for Youtube videos
 *
 * Available retrievable properties:
 * - code (string, e.g. "RVz71XsJIEA")
 */
class YouTube extends MultipleElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion-element > div.youtube-element'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'code' => 'iframe'
        ];
    }

    /**
     * Returns the video code
     * @return array
     */
    public function getCodeProperty()
    {
        $elements = \Helper::findElements($this, ['code']);
        return $elements['code']->getAttribute('src');
    }
}