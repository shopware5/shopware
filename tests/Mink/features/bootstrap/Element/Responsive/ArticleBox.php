<?php

namespace Element\Responsive;

class ArticleBox extends \Element\Emotion\ArticleBox
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.product--box.box--basic');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'name' => 'div.product--info > a:product--title',
            'price' => 'div.product--price > .price--default'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'compare'  => array('de' => 'Vergleichen',  'en' => 'Compare'),
            'remember' => array('de' => 'Merken',       'en' => 'Remember')
        );
    }
}
