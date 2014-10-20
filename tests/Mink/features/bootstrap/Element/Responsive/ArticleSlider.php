<?php

namespace Element\Responsive;

class ArticleSlider extends \Element\Emotion\ArticleSlider
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion--element.article-slider-element');

    public $cssLocator = array(
        'slideImage' => 'div.article-slider--item a.product--image > .image--element > span:nth-of-type(2)',
        'slideContainer' => 'div.article-slider--item a.product--image > .image--element',
        'slideLink' => 'div.article-slider--item a.product--image',
        'slideName' => 'div.article-slider--item a.product--title'
    );

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('slideImage');
        $elements = \Helper::findElements($this, $locators, null, true);

        $images = array();

        foreach ($elements['slideImage'] as $image) {
            $images[] = array($image->getAttribute('data-src'));
        }

        return $images;
    }

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $locators = array('slideContainer', 'slideLink', 'slideName');
        $elements = \Helper::findElements($this, $locators, null, true);

        $names = array();

        foreach ($elements['slideContainer'] as $key => $container) {
            $names[] = array(
                $container->getAttribute('data-alt'),
                $elements['slideLink'][$key]->getAttribute('title'),
                $elements['slideName'][$key]->getAttribute('title'),
            );
        }

        return $names;
    }
}