<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
        Behat\Behat\Context\Step;

class Listing extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/listing/?sPage={sPage}&sTemplate={sTemplate}&sPerPage={sPerPage}&sSort={sSort}';

    public function openListing($params)
    {
        $parameters = array();

        foreach ($params as $param) {
            $parameters[$param['parameter']] = $param['value'];
        }

        if (empty($parameters['sPage'])) {
            $parameters['sPage'] = 1;
        }

        if (empty($parameters['sTemplate'])) {
            $parameters['sTemplate'] = 'table';
        }

        if (empty($parameters['sPerPage'])) {
            $parameters['sPerPage'] = 12;
        }

        if (empty($parameters['sSort'])) {
            $parameters['sSort'] = 1;
        }

        $this->open($parameters);
    }

    public function checkPrice($position, $price2)
    {
        $price = $this->find('css', 'div.listing div.artbox:nth-of-type(' . $position . ') p.price');
        $price = $price->getText();

        $price = explode(' ', $price);
        $price = $price[1];

        $price = $this->toPrice($price);
        $price2 = $this->toPrice($price2);

        if ($price !== $price2) {
            $message = sprintf(
                    'The price of article on position %s (%s €) is different from %s €!',
                    $position,
                    $price,
                    $price2
            );
            throw new ResponseTextException($message, $this->getSession());
        }
    }

    private function toPrice($price)
    {
        $price = str_replace('.', '', $price); //Tausenderpunkte entfernen
        $price = str_replace(',', '.', $price); //Punkt statt Komma
        $price = floatval($price);

        return $price;
    }
}
