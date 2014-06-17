<?php

namespace Emotion;

require_once('tests/Mink/features/bootstrap/Element/MultipleElement.php');

class CartPosition extends \MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.table_row');

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'remove'  => array('de' => 'Löschen',   'en' => 'Delete')
    );

    /**
     * @param string $name
     * @param string $language
     */
    public function clickActionLink($name, $language)
    {
        $this->clickLink($this->namedSelectors[$name][$language]);
    }
}