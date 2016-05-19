<?php

namespace Shopware\Tests\Mink\Element\Responsive;

use Shopware\Tests\Mink\Helper;

/**
 * Element: CompareColumn
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class CompareColumn extends \Shopware\Tests\Mink\Element\Emotion\CompareColumn
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
     * @return string
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['thumbnailImage']);

        return $elements['thumbnailImage']->getAttribute('srcset');
    }

    /**
     * @return string
     */
    public function getRankingProperty()
    {
        $elements = Helper::findElements($this, ['stars']);

        $ranking = $elements['stars']->getAttribute('content');

        return ($ranking) ? $ranking : '0';
    }
}
