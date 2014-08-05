<?php

namespace Emotion;

use  Behat\Mink\Exception\ResponseTextException;

class AccountShipping extends AccountBilling
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.shipping > div.inner_container');

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'otherButton'  => array('de' => 'Andere wählen',            'en' => 'Select other'),
        'changeButton' => array('de' => 'Lieferadresse ändern',     'en' => 'Change shipping address')
    );
}
