<?php
namespace Responsive;

use Behat\Mink\Driver\SahiDriver;
use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Context\Step;

class Detail extends \Emotion\Detail
{
    public $cssLocator = array(
        'productRating' => 'div.product--rating-container > a.product--rating-link',
        'productReviews' => 'div.content--product-reviews',
        'productRatingAverage' => 'meta',
        'commentRating' => 'meta:nth-of-type(2)',
        'commentNumber' => 'span',
        'commentBlock' => 'div.review--entry.block-group',
        'commentAuthor' => 'div.entry--author.block > span.content--field',
        'commentDate' => 'div.entry--author.block > span.content--field',
        'commentTitle' => 'div.entry--content.block > h4',
        'commentText' => 'div.entry--content.block > p',
        'commentAnswer' => 'div.entry--content.block > p',
        'configuratorForm' => 'div.product--buybox > div.buybox--inner > form'
    );

    protected $configuratorTypes = array(
        'buybox--form' => 'table',
        'confgurator--form upprice--form' => 'standard',
        'confgurator--form selection--form' => 'select'
    );

    /**
     * Helper function how to read the evaluation from the evaluation element
     * @param NodeElement $element
     * @return string
     */
    protected function getEvaluation($element)
    {
        $evaluation = $element->getAttribute('content');
        $evaluation = floatval($evaluation);
        $evaluation*= 2;

        return (string)$evaluation;
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
}
