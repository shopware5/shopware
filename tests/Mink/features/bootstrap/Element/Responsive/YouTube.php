<?php

namespace Element\Responsive;

/**
 * Element: YouTube
 * Location: Emotion element for Youtube videos
 *
 * Available retrievable properties:
 * - code (string, e.g. "RVz71XsJIEA")
 */
class YouTube extends \Element\Emotion\YouTube
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--element.youtube-element'];
}