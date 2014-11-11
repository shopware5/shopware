<?php

namespace Element\Responsive;

class ArticleBox extends \Element\Emotion\ArticleBox
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.product--box.box--basic');

    public $cssLocator = array(
        'name' => 'div.product--info > a:product--title',
        'price' => 'div.product--price > .price--default'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'compare'  => array('de' => 'Vergleichen',  'en' => 'Compare'),
        'remember' => array('de' => 'Merken',       'en' => 'Remember')
    );
}
