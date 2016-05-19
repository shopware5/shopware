<?php

namespace Shopware\Tests\Mink\Element\Responsive;

use Shopware\Tests\Mink\Helper;

/**
 * Element: Article
 * Location: Emotion element for products
 *
 * Available retrievable properties:
 * - name (string, e.g. "All Natural - Lemon Honey Soap")
 * - text (string, e.g. "Ichilominus Fultus ordior, ora Sterilis qua Se sum cum Conspicio sed Eo at ver oportet, ..."
 * - price (float, e.g. "11,40 €")
 * - link (string, e.g. "/sommerwelten/beauty-und-care/218/all-natural-lemon-honey-soap")
 *
 * Currently not retrievable properties:
 * - image (string)
 */
class Article extends \Shopware\Tests\Mink\Element\Emotion\Article
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--product'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => '.product--title',
            'link' => '.product--image',
            'price' => '.product--price'
        ];
    }

    /**
     * @return string
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['name', 'link']);

        $names = [
            $elements['name']->getText(),
            $elements['name']->getAttribute('title'),
            $elements['link']->getAttribute('title')
        ];

        return Helper::getUnique($names);
    }

    /**
     * @return string
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['image']);

        return $elements['image']->getAttribute('src');
    }

    /**
     * @return string
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['name', 'link']);

        $links = [
            $elements['name']->getAttribute('href'),
            $elements['link']->getAttribute('href')
        ];

        return Helper::getUnique($links);
    }
}
