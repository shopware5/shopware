<?php

namespace Element\Responsive;

class NotePosition extends \Element\Emotion\NotePosition
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.note--item');

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'remove'  => array('de' => 'Löschen',       'en' => 'Delete'),
        'order'   => array('de' => 'Kaufen',        'en' => 'Purchase'),
        'compare' => array('de' => 'Vergleichen',   'en' => 'Compare'),
        'details' => array('de' => 'Zum Produkt',   'en' => 'View product')
    );

    public $cssLocator = array(
        'a-thumb' => 'a.note--image-link',
        'img' => 'img',
        'a-zoom' => 'a.note--zoom',
        'a-title' => 'a.note--title',
        'div-supplier' => 'div.note--supplier',
        'p-number' => 'div.note--ordernumber',
        'strong-price' => 'div.note--price',
        'a-detail' => 'a.action--details'
    );
}
