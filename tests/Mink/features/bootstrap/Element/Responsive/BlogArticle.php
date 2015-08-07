<?php

namespace Shopware\Tests\Mink\Element\Responsive;

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
class BlogArticle extends \Shopware\Tests\Mink\Element\Emotion\BlogArticle
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--blog'];

    /**
     * @inheritdoc
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
