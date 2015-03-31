<?php

namespace Element\Emotion;

require_once 'tests/Mink/features/bootstrap/Element/Emotion/CartPosition.php';

class BlogBox extends CartPosition
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.blogbox');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array();
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'readMore'   => array('de' => 'Mehr lesen', 'en' => 'Read more')
        );
    }
}
