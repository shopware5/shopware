<?php

namespace Shopware\Tests\Mink\Element\Responsive;

use Shopware\Tests\Mink\Helper;

/**
 * Element: NotePosition
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class NotePosition extends \Shopware\Tests\Mink\Element\Emotion\NotePosition
{
    /** @var array $selector */
    protected $selector = ['css' => 'div.note--item'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'a.note--title',
            'supplier' => 'div.note--supplier',
            'number' => 'div.note--ordernumber',
            'thumbnailLink' => 'a.note--image-link',
            'thumbnailImage' => 'a.note--image-link > img',
            'price' => 'div.note--price',
            'detailLink' => 'a.note--title'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'remove'  => ['de' => 'Löschen',       'en' => 'Delete'],
            'compare' => ['de' => 'Vergleichen',   'en' => 'Compare']
        ];
    }

    /**
     * @return string
     */
    public function getImageProperty()
    {
        $element = Helper::findElements($this, ['thumbnailImage']);

        return $element['thumbnailImage']->getAttribute('srcset');
    }
}
