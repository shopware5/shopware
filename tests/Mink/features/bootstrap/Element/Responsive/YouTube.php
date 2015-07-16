<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: YouTube
 * Location: Emotion element for Youtube videos
 *
 * Available retrievable properties:
 * - code (string, e.g. "RVz71XsJIEA")
 */
class YouTube extends \Shopware\Tests\Mink\Element\Emotion\YouTube
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--element.youtube-element'];
}
