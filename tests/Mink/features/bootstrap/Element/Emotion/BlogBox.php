<?php

namespace Emotion;

require_once('tests/Mink/features/bootstrap/Element/Emotion/CartPosition.php');

class BlogBox extends CartPosition
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.blogbox');

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'readMore'   => array('de' => 'Mehr lesen',   'en' => 'Read more')
    );
}