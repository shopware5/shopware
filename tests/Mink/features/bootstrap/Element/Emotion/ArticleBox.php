<?php

namespace Element\Emotion;

require_once 'tests/Mink/features/bootstrap/Element/Emotion/CartPosition.php';

class ArticleBox extends CartPosition
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.artbox');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'name' => 'div.inner > a:nth-of-type(2)',
            'price' => 'p.price'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'order'   => array('de' => 'Jetzt bestellen', 'en' => 'Order now'),
            'compare' => array('de' => 'Vergleichen',     'en' => 'Compare'),
            'details' => array('de' => 'Zum Produkt',     'en' => 'See details')
        );
    }

    /**
     * @return float
     */
    public function getPriceProperty()
    {
        $price = $this->getProperty('price');
        return \Helper::floatValue($price);
    }
}
