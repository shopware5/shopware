<?php

namespace Element\Emotion;

class AccountShipping extends AccountBilling
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.shipping > div.inner_container');

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'otherButton'  => array('de' => 'Andere wählen',            'en' => 'Select other'),
            'changeButton' => array('de' => 'Lieferadresse ändern',     'en' => 'Change shipping address')
        );
    }
}
