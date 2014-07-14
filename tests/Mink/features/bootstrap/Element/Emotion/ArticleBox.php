<?php

namespace Emotion;

require_once('tests/Mink/features/bootstrap/Element/Emotion/CartPosition.php');

class ArticleBox extends CartPosition
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.artbox');

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'order'   => array('de' => 'Jetzt bestellen',   'en' => 'Order now'),
        'compare' => array('de' => 'Vergleichen',       'en' => 'Compare'),
        'details' => array('de' => 'Zum Produkt',       'en' => 'See details')
    );

    public $cssLocator = array(
        'name' => 'div.inner > a:nth-of-type(2)',
        'price' => 'p.price'
    );

    /**
     * @param array $properties
     */
    public function checkProperties($properties)
    {
        $check = array();

        $locators = array_column($properties, 'property');
        $elements = \Helper::findElements($this, $locators);

        foreach($properties as $row)
        {
            $element = $elements[$row['property']];

            $comparison = array($element->getText(), $row['value']);

            if($row['property'] === 'price') {
                $comparison = \Helper::toFloat($comparison);
            }

            $check[$row['property']] = $comparison;
        }

        $result = \Helper::checkArray($check);

        if($result !== true) {
            $message = sprintf(
                'The %s of the article on position %d is "%s" (should be "%s")',
                $result,
                $this->position,
                $check[$result][0],
                $check[$result][1]
            );
            \Helper::throwException(array($message));
        }

//        var_dump('Konnte lesen: Article ' . $this->position);
    }
}