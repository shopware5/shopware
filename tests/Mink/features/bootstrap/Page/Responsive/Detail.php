<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Behat\Mink\Driver\Selenium2Driver;
use Shopware\Tests\Mink\Element\Emotion\ArticleEvaluation;
use Shopware\Tests\Mink\Helper;

class Detail extends \Shopware\Tests\Mink\Page\Emotion\Detail
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'productRating' => 'div.product--rating-container .product--rating > meta',
            'productRatingCount' => 'div.product--rating-container .product--rating > span',
            'configuratorForm' => 'form.configurator--form',
            'notificationForm' => 'form.notification--form',
            'notificationSubmit' => '.notification--button',
            'voteForm' => 'form.review--form'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'notificationFormSubmit' => ['de' => 'Eintragen', 'en' => 'Enter'],
            'voteFormSubmit' => ['de' => 'Speichern', 'en' => 'Save'],
            'inquiryLink' => ['de' => 'Fragen zum Artikel?', 'en' => 'Do you have any questions concerning this product?'],
            'compareLink' => ['de' => 'Vergleichen', 'en' => 'Compare'],
            'rememberLink' => ['de' => 'Merken', 'en' => 'Remember'],
            'commentLink' => ['de' => 'Bewerten', 'en' => 'Comment'],
        ];
    }

    /**
     * @var string[]
     */
    protected $configuratorTypes = [
        'table' => 'configurator--form',
        'standard' => 'configurator--form upprice--form',
        'select' => 'configurator--form selection--form'
    ];

    /**
     * Puts the current article <quantity> times to basket
     * @param int $quantity
     */
    public function toBasket($quantity = 1)
    {
        $this->fillField('sQuantity', $quantity);
        $this->pressButton('In den Warenkorb');

        if ($this->getDriver() instanceof Selenium2Driver) {
            $this->clickLink('Warenkorb anzeigen');
        }
    }

    /**
     * @param ArticleEvaluation $articleEvaluations
     * @param $average
     * @throws \Exception
     */
    protected function checkRating(ArticleEvaluation $articleEvaluations, $average)
    {
        $elements = Helper::findElements($this, ['productRating', 'productRatingCount']);
        $check = [
            'productRating' => [$elements['productRating']->getAttribute('content'), $average],
            'productRatingCount' => [$elements['productRatingCount']->getText(), count($articleEvaluations)]
        ];

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
        $data = [
            [
                'field' => 'sNotificationEmail',
                'value' => $email
            ]
        ];

        Helper::fillForm($this, 'notificationForm', $data);

        $elements = Helper::findElements($this, ['notificationSubmit']);
        $elements['notificationSubmit']->press();
    }
}
