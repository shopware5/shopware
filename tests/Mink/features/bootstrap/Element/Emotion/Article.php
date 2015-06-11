<?php

namespace Element\Emotion;

use Symfony\Component\Console\Helper\Helper;

require_once 'tests/Mink/features/bootstrap/Element/Emotion/CategoryTeaser.php';

class Article extends CategoryTeaser implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.article-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'name' => 'a.title',
            'link' => 'a.artbox_thumb',
            'text' => 'p.desc',
            'price' => 'span.price',
            'more' => 'a.more'
        );
    }

    /**
     * @return array
     */
    public function getNameProperty()
    {
        $elements = \Helper::findElements($this, ['name', 'link', 'more']);

        $names = [
            $elements['name']->getText(),
            $elements['name']->getAttribute('title'),
            $elements['link']->getAttribute('title'),
            $elements['more']->getAttribute('title')
        ];

        return \Helper::getUnique($names);
    }

    /**
     * @return array
     */
    public function getImageProperty()
    {
        $elements = \Helper::findElements($this, ['link']);
        return $elements['link']->getAttribute('style');
    }

    /**
     * @return array
     */
    public function getLinkProperty()
    {
        $elements = \Helper::findElements($this, ['name', 'link', 'more']);

        $links = [
            $elements['name']->getAttribute('href'),
            $elements['link']->getAttribute('href'),
            $elements['more']->getAttribute('href')
        ];

        return \Helper::getUnique($links);
    }

    /**
     * @return array
     */
    public function getTextProperty()
    {
        $elements = \Helper::findElements($this, ['text']);
        return $elements['text']->getText();
    }

    /**
     * @return array
     */
    public function getPriceProperty()
    {
        $elements = \Helper::findElements($this, ['price']);
        return \Helper::floatValue($elements['price']->getText());
    }
}