<?php

namespace Element\Responsive;

class NotePosition extends \Element\Emotion\NotePosition
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.note--item');

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'remove'  => array('de' => 'Löschen',       'en' => 'Delete'),
        'compare' => array('de' => 'Vergleichen',   'en' => 'Compare')
    );

    public $cssLocator = array(
        'a-thumb' => 'a.note--image-link',
        'img' => 'img',
        'a-title' => 'a.note--title',
        'div-supplier' => 'div.note--supplier',
        'p-number' => 'div.note--ordernumber',
        'strong-price' => 'div.note--price',
        'a-detail' => 'a.note--title'
    );
}
