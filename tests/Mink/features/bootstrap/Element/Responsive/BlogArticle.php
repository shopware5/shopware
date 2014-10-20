<?php

namespace Element\Responsive;

class BlogArticle extends \Element\Emotion\BlogArticle
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion--element.blog-element');

    public $cssLocator = array(
        'title' => 'a.blog--title',
        'link' => 'a.blog--image',
        'text' => 'p.blog--desc'
    );
}