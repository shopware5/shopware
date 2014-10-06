<?php
namespace Page\Responsive;

use Behat\Mink\Driver\SahiDriver;
use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

class Detail extends \Page\Emotion\Detail
{
    public $cssLocator = array(
        'productRating' => 'div.product--rating-container .product--rating > meta',
        'productRatingCount' => 'div.product--rating-container .product--rating > span',
        'configuratorForm' => 'div.product--buybox > div.buybox--inner > form',
        'notificationForm' => 'form.notification--form',
        'voteForm' => 'form.review--form'
    );

    protected $configuratorTypes = array(
        'table' => 'buybox--form',
        'standard' => 'configurator--form upprice--form',
        'select' => 'configurator--form selection--form'
    );

    /**
     * Helper function how to read the evaluation from the evaluation element
     * @param  NodeElement $element
     * @return string
     */
    protected function getEvaluation($element)
    {
        $evaluation = $element->getAttribute('content');
        $evaluation = floatval($evaluation);
        $evaluation*= 2;

        return (string) $evaluation;
    }

    /**
     * Puts the current article <quantity> times to basket
     * @param int $quantity
     */
    public function toBasket($quantity = 1)
    {
        $this->fillField('sQuantity', $quantity);
        $this->pressButton('In den Warenkorb');

        if ($this->getSession()->getDriver() instanceof SahiDriver) {
            $this->clickLink('Warenkorb anzeigen');
        }
    }

    protected function checkRating(MultipleElement $articleEvaluations, $average)
    {
        $locators = array('productRating', 'productRatingCount');

        $elements = \Helper::findElements($this, $locators);

        $check = array();

        foreach($elements as $locator => $element)
        {
            switch($locator) {
                case 'productRating':
                    $rating = $element->getAttribute('content');
                    $rating = floatval($rating);
                    $check[$locator] = array($rating * 2, $average);
                    break;

                case 'productRatingCount':
                    $check[$locator] = array($element->getText(), count($articleEvaluations));
                    break;
            }
        }

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the evaluation! (%s: "%s" instead of %s)', $result, $check[$result][0], $check[$result][1]);
            \Helper::throwException($message);
        }
    }
}
