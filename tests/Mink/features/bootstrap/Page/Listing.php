<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
        Behat\Behat\Context\Step;

class Listing extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/listing/index/sCategory/{sCategory}/sSupplier/{sSupplier}?sPage={sPage}&sTemplate={sTemplate}&sPerPage={sPerPage}&sSort={sSort}';

    /**
     * @param $params
     */
    public function openListing($params)
    {
        $parameters = array();

        foreach ($params as $param) {
            $parameters[$param['parameter']] = $param['value'];
        }

        if (empty($parameters['sCategory'])) {
            $parameters['sCategory'] = 3;
        }

        if (empty($parameters['sPage'])) {
            $parameters['sPage'] = 1;
        }

        $this->open($parameters);
    }

    /**
     * @param $position
     * @param $price2
     * @throws Behat\Mink\Exception\ResponseTextException
     */
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

    /**
     * @param $price
     * @return float|mixed
     */
    private function toPrice($price)
    {
        $price = str_replace('.', '', $price); //Tausenderpunkte entfernen
        $price = str_replace(',', '.', $price); //Punkt statt Komma
        $price = floatval($price);

        return $price;
    }

    /**
     * @param $properties
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function filter($properties)
    {
        $filterClass = 'div.filter_properties div div';

        //Reset all filters
        $showAllLinks = array_reverse($this->findAll('css', $filterClass . ' li.close a'));
        foreach ($showAllLinks as $showAllLink) {
            $showAllLink->click();
        }

        //Set new filters
        $filter = $this->findAll('css', $filterClass);

        foreach ($properties as $propertyKey => $property) {
            $offset = $propertyKey * 2;
            $found = false;

            for ($i = 0; $i < count($filter); $i += 2) {
                $filterName = $filter[$i]->getText();

                if (strpos($filterName, $property['filter']) !== false) {
                    $values = $this->findAll('css', sprintf('%s:nth-of-type(%d) a', $filterClass, $i + 2 + $offset));

                    foreach ($values as $key => $value) {
                        $valueName = $value->getText();

                        if (empty($valueName)) {
                            $valueName = $this->find(
                                    'css',
                                    sprintf('%s:nth-of-type(%d) li:nth-of-type(%d) img', $filterClass, $i + 2, $key + 1)
                            );
                            $valueName = $valueName->getAttribute('alt');
                        }

                        if (strpos($valueName, $property['value']) !== false) {
                            $value->click();

                            $found = true;
                            unset($filter[$i], $filter[$i + 1]);
                            $filter = array_values($filter);
                            break;
                        }

                        if ($value == end($values)) {
                            $message = sprintf(
                                    'The value "%s" was not found for filter "%s"!',
                                    $property['value'],
                                    $property['filter']
                            );
                            throw new ResponseTextException($message, $this->getSession());
                        }
                    }
                }

                if ($found) {
                    break;
                }
            }

            if (!$found) {
                $message = sprintf('The filter "%s" was not found!', $property['filter']);
                throw new ResponseTextException($message, $this->getSession());
            }
        }
    }

    /**
     * @param $count
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function countArticles($count)
    {
        $articles = $this->findAll('css', 'div.artbox');

        if (count($articles) != $count) {
            $message = sprintf('There are %d articles in the listing (should be %d)', count($articles), $count);
            throw new ResponseTextException($message, $this->getSession());
        }
    }
}
