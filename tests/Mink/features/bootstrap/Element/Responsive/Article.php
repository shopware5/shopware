<?php

namespace Element\Responsive;

class Article extends \Element\Emotion\Article
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion--element.article-element');

    public $cssLocator = array(
        'name' => 'a.product--title',
        'link' => 'a.box--image',
        'container' =>  'a.box--image > .image--element',
        'image' => 'a.box--image > .image--element > span:nth-of-type(2)',
        'price' => 'div.product--price > .price--default'
    );

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $locators = array('name', 'link', 'container');
        $elements = \Helper::findElements($this, $locators);

        return array(
            $elements['name']->getText(),
            $elements['name']->getAttribute('title'),
            $elements['link']->getAttribute('title'),
            $elements['container']->getAttribute('data-alt')
        );
    }

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('image');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'image' => $elements['image']->getAttribute('data-src')
        );
    }

    /**
     * @return array
     */
    public function getLinksToCheck()
    {
        $locators = array('name', 'link');
        $elements = \Helper::findElements($this, $locators);

        return array(
            $elements['name']->getAttribute('href'),
            $elements['link']->getAttribute('href')
        );
    }
}