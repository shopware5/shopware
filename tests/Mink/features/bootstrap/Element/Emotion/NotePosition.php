<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: NotePosition
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class NotePosition extends CartPosition implements HelperSelectorInterface
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.table_row');

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'a.title',
            'supplier' => 'div.supplier',
            'number' => 'p.ordernumber',
            'thumbnailLink' => 'a.thumb_image',
            'thumbnailImage' => 'a.thumb_image > img',
            'description' => 'p.desc',
            'price' => 'strong.price',
            'detailLink' => 'a.detail'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'remove' => ['de' => 'Löschen', 'en' => 'Delete'],
            'order' => ['de' => 'In den Warenkorb', 'en' => 'Add to cart'],
            'compare' => ['de' => 'Vergleichen', 'en' => 'Compare'],
            'details' => ['de' => 'Zum Produkt', 'en' => 'View product']
        ];
    }

    /**
     * Returns the product name
     * @return string
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['name', 'thumbnailLink', 'thumbnailImage', 'detailLink']);

        $names = array(
            'articleName' => $elements['name']->getText(),
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('alt'),
            'articleDetailLinkTitle' => $elements['detailLink']->getAttribute('title')
        );

        return Helper::getUnique($names);
    }

    /**
     * Returns the image source path
     * @return string
     */
    public function getImageProperty()
    {
        $element = Helper::findElements($this, ['thumbnailImage']);

        return $element['thumbnailImage']->getAttribute('src');
    }

    /**
     * Returns the link to the product
     * @return string
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['name', 'thumbnailLink', 'detailLink']);

        $names = array(
            'articleNameLink' => $elements['name']->getAttribute('href'),
            'articleThumbnailLink' => $elements['thumbnailLink']->getAttribute('href'),
            'articleDetailLink' => $elements['detailLink']->getAttribute('href')
        );

        return Helper::getUnique($names);
    }
}
