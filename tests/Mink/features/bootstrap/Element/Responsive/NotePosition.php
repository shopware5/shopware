<?php

namespace Element\Responsive;

class NotePosition extends \Element\Emotion\NotePosition
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.note--item');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'name' => 'a.note--title',
            'supplier' => 'div.note--supplier',
            'number' => 'div.note--ordernumber',
            'thumbnailLink' => 'a.note--image-link',
            'thumbnailImage' => 'a.note--image-link > img',
            'price' => 'div.note--price',
            'detailLink' => 'a.note--title'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'remove'  => array('de' => 'Löschen',       'en' => 'Delete'),
            'compare' => array('de' => 'Vergleichen',   'en' => 'Compare')
        );
    }

    /**
     * @return string
     */
    public function getImageProperty()
    {
        $locators = array('thumbnailImage');
        $element = \Helper::findElements($this, $locators);

        return $element['thumbnailImage']->getAttribute('srcset');
    }
}
