<?php

namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use  Behat\Mink\Exception\ResponseTextException;

class AccountBilling extends Element
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.billing > div.inner_container');

    public $cssLocator = array(
        'addressData' => 'p'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'otherButton'  => array('de' => 'Andere wählen',            'en' => 'Select other'),
        'changeButton' => array('de' => 'Rechnungsadresse ändern',  'en' => 'Change billing address')
    );

    public function checkAddress($testAddress)
    {
        $testAddress = explode(', ', $testAddress);
        $testAddress = array_filter($testAddress);
        $testAddress = array_values($testAddress);

        $locators = array('addressData');
        $elements = \Helper::findElements($this, $locators, null, true);

        $address = array();

        foreach ($elements['addressData'] as $data) {

            $part = $data->getHtml();
            $parts = explode('<br />', $part);
            foreach ($parts as &$part) {
                $part = strip_tags($part);
                $part = str_replace(array(chr(0x0009), '  '), ' ', $part);
                $part = str_replace(array(chr(0x0009), '  '), ' ', $part);
                $part = trim($part);
            }
            unset($part);

            $address = array_merge($address, $parts);
        }

        $result = \Helper::compareArrays($address, $testAddress);

        if ($result === true) {
            return;
        }

        $message = sprintf('The addresses are different! ("%s" not was found in "%s")', $result['value2'], $result['value']);
        throw new ResponseTextException($message, $this->getSession());
    }
}
