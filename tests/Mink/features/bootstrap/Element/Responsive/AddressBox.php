<?php

namespace Element\Responsive;

class AddressBox extends \Element\Emotion\AddressBox
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.address--container .panel');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'title' => '.panel--title'
        );
    }
}
