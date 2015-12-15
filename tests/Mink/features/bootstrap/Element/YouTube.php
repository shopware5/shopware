<?php

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;

/**
 * Element: YouTube
 * Location: Emotion element for Youtube videos
 *
 * Available retrievable properties:
 * - code (string, e.g. "RVz71XsJIEA")
 */
class YouTube extends MultipleElement implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--element.youtube-element'];

    /**
     * @inheritdoc
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
        $elements = Helper::findElements($this, ['code']);
        return $elements['code']->getAttribute('src');
    }
}
