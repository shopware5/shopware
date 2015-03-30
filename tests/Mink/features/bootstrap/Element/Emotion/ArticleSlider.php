<?php

namespace Element\Emotion;

require_once 'tests/Mink/features/bootstrap/Element/Emotion/BannerSlider.php';

class ArticleSlider extends BannerSlider implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.article-slider-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'slideImage' => 'a.article-thumb-wrapper > img',
            'slideLink' => 'a.article-thumb-wrapper',
            'slideName' => 'a.title',
            'slidePrice' => 'p.price'
        );
    }

    /**
     * @return array
     */
    public function getLinksToCheck()
    {
        $locators = array('slideLink', 'slideName');
        $elements = \Helper::findAllOfElements($this, $locators);

        $links = array();

        foreach ($elements['slideLink'] as $key => $link) {
            $links[] = array(
                $link->getAttribute('href'),
                $elements['slideName'][$key]->getAttribute('href')
            );
        }

        return $links;
    }

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $locators = array('slideImage', 'slideLink', 'slideName');
        $elements = \Helper::findAllOfElements($this, $locators);

        $names = array();

        foreach ($elements['slideImage'] as $key => $image) {
            $names[] = array(
                $image->getAttribute('title'),
                $elements['slideLink'][$key]->getAttribute('title'),
                $elements['slideName'][$key]->getText(),
                $elements['slideName'][$key]->getAttribute('title'),
            );
        }

        return $names;
    }

    /**
     * @return array
     */
    public function getPricesToCheck()
    {
        $locators = array('slidePrice');
        $elements = \Helper::findAllOfElements($this, $locators);

        $prices = array();

        foreach ($elements['slidePrice'] as $price) {
            $prices[] = array(
                \Helper::toFloat($price->getText())
            );
        }

        return $prices;
    }
}