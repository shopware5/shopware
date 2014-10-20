<?php

namespace Element\Responsive;

class CategoryTeaser extends \Element\Emotion\CategoryTeaser
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion--element.category-teaser-element');

    public $cssLocator = array(
        'name' => 'a.box--title',
        'image' => 'a.box--image > img',
        'link' => 'a.box--image'
    );

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $elements = \Helper::findElements($this);

        return array(
            $elements['name']->getText(),
            $elements['name']->getAttribute('title'),
            $elements['image']->getAttribute('alt'),
            $elements['link']->getAttribute('title')
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
            'image' => $elements['image']->getAttribute('src')
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