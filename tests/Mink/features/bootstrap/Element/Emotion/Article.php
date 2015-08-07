<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use Shopware\Tests\Mink\Element\MultipleElement;
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
class Article extends MultipleElement implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion-element > div.article-element'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'a.title',
            'link' => 'a.artbox_thumb',
            'text' => 'p.desc',
            'price' => 'span.price',
            'more' => 'a.more'
        ];
    }

    /**
     * @return string
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['name', 'link', 'more']);

        $names = [
            $elements['name']->getText(),
            $elements['name']->getAttribute('title'),
            $elements['link']->getAttribute('title'),
            $elements['more']->getAttribute('title')
        ];

        return Helper::getUnique($names);
    }

    /**
     * @return string
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['link']);

        return $elements['link']->getAttribute('style');
    }

    /**
     * @return string
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['name', 'link', 'more']);

        $links = [
            $elements['name']->getAttribute('href'),
            $elements['link']->getAttribute('href'),
            $elements['more']->getAttribute('href')
        ];

        return Helper::getUnique($links);
    }

    /**
     * @return float
     */
    public function getPriceProperty()
    {
        $elements = Helper::findElements($this, ['price']);

        return Helper::floatValue($elements['price']->getText());
    }
}
