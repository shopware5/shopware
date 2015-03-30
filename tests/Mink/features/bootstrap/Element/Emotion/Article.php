<?php

namespace Element\Emotion;

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
    public function getNamesToCheck()
    {
        $locators = array('name', 'link', 'more');
        $elements = \Helper::findElements($this, $locators);

        return array(
            $elements['name']->getText(),
            $elements['name']->getAttribute('title'),
            $elements['link']->getAttribute('title'),
            $elements['more']->getAttribute('title')
        );
    }

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('link');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'image' => $elements['link']->getAttribute('style')
        );
    }

    /**
     * @return array
     */
    public function getLinksToCheck()
    {
        $locators = array('name', 'link', 'more');
        $elements = \Helper::findElements($this, $locators);

        return array(
            $elements['name']->getAttribute('href'),
            $elements['link']->getAttribute('href'),
            $elements['more']->getAttribute('href')
        );
    }

    /**
     * @return array
     */
    public function getTextsToCheck()
    {
        $locators = array('text');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'text' => $elements['text']->getText()
        );
    }

    /**
     * @return array
     */
    public function getPricesToCheck()
    {
        $locators = array('price');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'price' => \Helper::toFloat($elements['price']->getText())
        );
    }
}