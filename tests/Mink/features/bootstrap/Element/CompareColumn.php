<?php

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;

/**
 * Element: CompareColumn
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class CompareColumn extends MultipleElement implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'ul.compare--group-list:not(.list--head)'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'thumbnailImage'    => 'li.entry--picture > a img',
            'thumbnailLink'     => 'li.entry--picture > a',
            'name'              => 'li.entry--name > a.link--name',
            'detailsButton'     => 'li.entry--name > a.btn--product',
            'stars'             => 'li.entry--voting meta:nth-of-type(1)',
            'description'       => 'li.entry--description',
            'price'             => 'li.entry--price > .price--normal'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'details'  => ['de' => 'Zum Produkt',   'en' => 'View product']
        ];
    }

    /**
     * Returns the image source path
     * @return string
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['thumbnailImage']);

        return $elements['thumbnailImage']->getAttribute('srcset');
    }

    /**
     * Returns the name
     * @return string
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['thumbnailImage', 'thumbnailLink', 'name', 'detailsButton']);

        $names = array(
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('alt'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleName' => $elements['name']->getText(),
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleDetailsButtonTitle' => $elements['detailsButton']->getAttribute('title')
        );

        return Helper::getUnique($names);
    }

    /**
     * Returns the star ranking
     * @return string
     */
    public function getRankingProperty()
    {
        $elements = Helper::findElements($this, ['stars']);

        $ranking = $elements['stars']->getAttribute('content');

        return ($ranking) ? $ranking : '0';
    }

    /**
     * Returns the link to the product
     * @return string
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['thumbnailLink', 'name', 'detailsButton']);

        $links = array(
            'articleThumbnailLink' => $elements['thumbnailLink']->getAttribute('href'),
            'articleNameLink' => $elements['name']->getAttribute('href'),
            'articleDetailsButtonLink' => $elements['detailsButton']->getAttribute('href')
        );

        return Helper::getUnique($links);
    }
}
