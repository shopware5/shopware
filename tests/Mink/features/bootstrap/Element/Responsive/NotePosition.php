<?php

namespace Responsive;

class NotePosition extends \Emotion\NotePosition
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.table_row');

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