<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Behat\Mink\Driver\SahiDriver;
use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Element\MultipleElement;
use Shopware\Tests\Mink\Helper;

class Detail extends \Shopware\Tests\Mink\Page\Emotion\Detail
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'productRating' => 'div.product--rating-container .product--rating > meta',
            'productRatingCount' => 'div.product--rating-container .product--rating > span',
            'configuratorForm' => 'form.configurator--form',
            'notificationForm' => 'form.notification--form',
            'notificationSubmit' => '.notification--button',
            'voteForm' => 'form.review--form'
        );
    }

    protected $configuratorTypes = array(
        'table' => 'configurator--form',
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

        $elements = Helper::findElements($this, $locators);

        $check = array();

        foreach($elements as $locator => $element)
        {
            switch($locator) {
                case 'productRating':
                    $rating = $element->getAttribute('content');
                    $rating = floatval($rating);
                    $check[$locator] = array($rating, $average);
                    break;

                case 'productRatingCount':
                    $check[$locator] = array($element->getText(), count($articleEvaluations));
                    break;
            }
        }

        $check = Helper::floatArray($check);
        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the evaluation! (%s: "%s" instead of %s)', $result, $check[$result][0], $check[$result][1]);
            Helper::throwException($message);
        }
    }

    /**
     * Fills the notification form and submits it
     * @param string $email
     */
    public function submitNotification($email)
    {
        $data = array(
            array(
                'field' => 'sNotificationEmail',
                'value' => $email
            )
        );

        Helper::fillForm($this, 'notificationForm', $data);

        $locators = array('notificationSubmit');
        $elements = Helper::findElements($this, $locators);
        $elements['notificationSubmit']->press();
    }
}
