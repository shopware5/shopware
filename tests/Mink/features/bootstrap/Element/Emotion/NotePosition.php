<?php

namespace Emotion;

class NotePosition extends CartPosition
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.table_row');

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'remove'  => array('de' => 'Löschen',       'en' => 'Delete'),
        'order'   => array('de' => 'Kaufen',        'en' => 'Purchase'),
        'compare' => array('de' => 'Vergleichen',   'en' => 'Compare'),
        'details' => array('de' => 'Zum Produkt',   'en' => 'View product')
    );

    protected $cssLocators = array(
        'a-thumb' => 'a.thumb_image',
        'img' => 'img',
        'a-zoom' => 'a.zoom_picture',
        'a-title' => 'a.title',
        'div-supplier' => 'div.supplier',
        'p-number' => 'p.ordernumber',
        'p-desc' => 'p.desc',
        'strong-price' => 'strong.price',
        'a-detail' => 'a.detail'
    );
}