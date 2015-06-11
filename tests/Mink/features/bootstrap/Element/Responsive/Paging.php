<?php

namespace Element\Responsive;

class Paging extends \Element\Emotion\Paging
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.listing--paging');

    public function getCssSelectors()
    {
        return array(
            'previous' => 'a.pagination--link.paging--prev',
            'next' => 'a.pagination--link.paging--next'
        );
    }
}
