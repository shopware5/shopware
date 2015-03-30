<?php

namespace Element\Responsive;

class CompareColumn extends \Element\Emotion\CompareColumn
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'ul.compare--group-list:not(.list--head)');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'thumbnailImage'    => 'li.entry--picture > a img',
            'thumbnailLink'     => 'li.entry--picture > a',
            'name'              => 'li.entry--name > a.link--name',
            'detailsButton'     => 'li.entry--name > a.btn--product',
            'stars'             => 'li.entry--voting meta:nth-of-type(2)',
            'description'       => 'li.entry--description',
            'price'             => 'li.entry--price > .price--normal'
        );
    }

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('thumbnailImage');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('srcset')
        );
    }

    /**
     * @return array
     */
    public function getRankingsToCheck()
    {
        $locators = array('stars');
        $elements = \Helper::findElements($this, $locators);

        $ranking = $elements['stars']->getAttribute('content');

        return array(
            'articleRanking' => ($ranking) ? $ranking : 0
        );
    }
}
