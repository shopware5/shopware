<?php

namespace Element\Responsive;

/**
 * Element: BlogArticle
 * Location: Emotion element for blog articles
 *
 * Available retrievable properties (per blog article):
 * - image (string, e.g. "beach1503f8532d4648.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - alt (string, e.g. "foo")
 * - title (string, e.g. "bar")
 */
class BlogArticle extends \Element\Emotion\BlogArticle
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--blog'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'article' => '.blog--entry',
            'articleTitle' => '.blog--title',
            'articleLink' => '.blog--image',
            'articleText' => '.blog--description'
        ];
    }
}
