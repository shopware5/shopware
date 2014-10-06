<?php

namespace Element\Responsive;

use Behat\Mink\Element\NodeElement;

class BlogComment extends \Element\Emotion\BlogComment
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.blog--comments-entry-inner');

    /** @var array $namedSelectors */
    protected $cssLocator = array(
        'author' => 'div.blog--comments-entry-left > .comments--author',
        'date' => 'div.blog--comments-entry-left > .comments--date',
        'stars' => 'div.blog--comments-entry-left .product--rating > meta:nth-of-type(2)',
        'headline' => 'div.blog--comments-entry-right > .blog--comments-entry-headline',
        'comment' => 'div.blog--comments-entry-right > .blog--comments-entry-text'
    );

    /**
     * @param NodeElement $element
     * @return string
     */
    protected function getStars(NodeElement $element)
    {
        return $element->getAttribute('content');
    }
}
