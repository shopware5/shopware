<?php

namespace Element\Emotion;

require_once 'tests/Mink/features/bootstrap/HelperSelectorInterface.php';

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class AccountBilling extends Element implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.billing > div.inner_container');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'addressData' => 'p'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'otherButton'  => array('de' => 'Andere wählen',            'en' => 'Select other'),
            'changeButton' => array('de' => 'Rechnungsadresse ändern',  'en' => 'Change billing address')
        );
    }

    public function checkAddress($testAddress)
    {
        $testAddress = explode(', ', $testAddress);
        $testAddress = array_filter($testAddress);
        $testAddress = array_values($testAddress);

        $locators = array('addressData');
        $elements = \Helper::findAllOfElements($this, $locators);

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
        \Helper::throwException($message);
    }
}
