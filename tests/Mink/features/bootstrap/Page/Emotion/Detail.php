<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Driver\SahiDriver;
use Behat\Mink\Element\NodeElement;

use Shopware\Tests\Mink\Element\MultipleElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper as MinkHelper;
use Shopware\Tests\Mink\HelperSelectorInterface;
use Symfony\Component\Console\Helper\Helper;

class Detail extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/detail/index/sArticle/{articleId}';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'productRating' => 'div#detailbox_middle > div.detail_comments > .star',
            'productRatingCount' => 'div#detailbox_middle > div.detail_comments > .comment_numbers',
            'productEvaluationAverage' => 'div#comments > div.overview_rating > .star',
            'productEvaluationCount' => 'div#comments > div.overview_rating > span',
            'configuratorForm' => 'div#buybox > form',
            'notificationForm' => 'form#sendArticleNotification',
            'voteForm' => 'div#comments > form'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'notificationFormSubmit' => array('de' => 'Eintragen', 'en' => 'Enter'),
            'voteFormSubmit'         => array('de' => 'Speichern', 'en' => 'Save')
        );
    }

    protected $configuratorTypes = array(
        'table' => 'configurator--form',
        'standard' => 'upprice_config',
        'select' => 'config_select'
    );

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    public function verifyPage()
    {
        if (!$this->hasButton('In den Warenkorb')) {
            MinkHelper::throwException('Detail page has no basket button');
        }
    }

    /**
     * Puts the current article <quantity> times to basket
     * @param int $quantity
     */
    public function toBasket($quantity = 1)
    {
        $this->selectFieldOption('sQuantity', $quantity);
        $this->pressButton('In den Warenkorb');

        if ($this->getSession()->getDriver() instanceof SahiDriver) {
            $this->clickLink('Warenkorb anzeigen');
        }
    }

    /**
     * Checks the evaluations of the current article
     * @param MultipleElement $articleEvaluations
     * @param $average
     * @param array $evaluations
     * @throws \Exception
     */
    public function checkEvaluations(MultipleElement $articleEvaluations, $average, array $evaluations)
    {
        $this->checkRating($articleEvaluations, $average);

        $evaluations = MinkHelper::floatArray($evaluations, ['stars']);
        $result = MinkHelper::assertElements($evaluations, $articleEvaluations);

        if($result === true) {
            return;
        }

        $messages = array('The following $evaluations are wrong:');
        foreach ($result as $evaluation) {
            $messages[] = sprintf(
                '%s - Bewertung: %s (%s is "%s", should be "%s")',
                $evaluation['properties']['author'],
                $evaluation['properties']['stars'],
                $evaluation['result']['key'],
                $evaluation['result']['value'],
                $evaluation['result']['value2']
            );
        }
        MinkHelper::throwException($messages);
    }

    /**
     * @param MultipleElement $articleEvaluations
     * @param $average
     * @throws \Exception
     */
    protected function checkRating(MultipleElement $articleEvaluations, $average)
    {
        $locators = array('productRating', 'productRatingCount', 'productEvaluationAverage', 'productEvaluationCount');

        $elements = MinkHelper::findElements($this, $locators);

        $check = array();

        foreach($elements as $locator => $element)
        {
            switch($locator) {
                case 'productRating':
                case 'productEvaluationAverage':
                    $check[$locator] = array($element->getAttribute('class'), $average);
                    break;

                case 'productRatingCount':
                case 'productEvaluationCount':
                    $check[$locator] = array($element->getText(), count($articleEvaluations));
                    break;
            }
        }

        $result = MinkHelper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the evaluation! (%s: "%s" instead of %s)', $result, $check[$result][0], $check[$result][1]);
            MinkHelper::throwException($message);
        }
    }

    /**
     * Helper function how to read the evaluation from the evaluation element
     * @param  NodeElement $element
     * @return string
     */
    protected function getEvaluation($element)
    {
        return (string) $element->getAttribute('class');
    }

    /**
     * Sets the configuration of a configurator article
     * @param array $configuration
     */
    public function configure($configuration)
    {
        $configuratorType = '';

        if ($this->getSession()->getDriver() instanceof GoutteDriver) {
            $element = MinkHelper::findElements($this, ['configuratorForm']);

            $configuratorClass = $element['configuratorForm']->getAttribute('class');
            $configuratorType = array_search($configuratorClass, $this->configuratorTypes);
        }

        foreach ($configuration as $group) {
            $field = sprintf('group[%d]', $group['groupId']);
            $this->selectFieldOption($field, $group['value']);

            if ($configuratorType === 'select') {
                $this->pressButton('recalc');
            }
        }

        if ($configuratorType === 'select') {
            return;
        }

        if ($this->getSession()->getDriver() instanceof GoutteDriver) {
            $this->pressButton('recalc');
        }
    }

    public function canNotSelectConfiguratorOption($configuratorOption, $configuratorGroup)
    {
        $group = $this->findField($configuratorGroup);

        if (empty($group)) {
            $message = sprintf('Configurator group "%s" was not found!', $configuratorGroup);
            MinkHelper::throwException($message);
        }

        $options = $group->findAll('css', 'option');

        foreach ($options as $option) {
            if ($option->getText() == $configuratorOption) {
                $message = sprintf('Configurator option %s founded but should not', $configuratorOption);
                MinkHelper::throwException($message);
            }
        }
    }

    /**
     * Writes an evaluation
     * @param array $data
     */
    public function writeEvaluation(array $data)
    {
        MinkHelper::fillForm($this, 'voteForm', $data);
        MinkHelper::pressNamedButton($this, 'voteFormSubmit');
    }

    /**
     * Checks a select box
     * @param string $select        Name of the select box
     * @param string $min           First option
     * @param string $max           Last option
     * @param integer $graduation   Steps between each options
     * @throws \Exception
     */
    public function checkSelect($select, $min, $max, $graduation)
    {
        $selectBox = $this->findField($select);

        if (empty($selectBox)) {
            $message = sprintf('Select box "%s" was not found!', $select);
            MinkHelper::throwException($message);
        }

        $options = $selectBox->findAll('css', 'option');

        $errors = array();
        $optionText = $options[0]->getText();
        $parts = explode(' ', $optionText, 2);
        $value = $parts[0];
        $unit = isset($parts[1]) ? ' '.$parts[1] : '';

        if($optionText !== $min){
            $errors[] = sprintf('The first option of "%s" is "%s"! (should be "%s")', $select, $optionText, $min);
        }

        /** @var NodeElement $option */
        while ($option = next($options)) {
            $optionText = $option->getText();
            $value += $graduation;

            if($optionText !== $value.$unit){
                $errors[] = sprintf('There is the invalid option "%s" in "%s"! ("%s" expected)', $optionText, $select, $value.$unit);
            }
        }

        if($optionText !== $max){
            $errors[] = sprintf('The last option of "%s" is "%s"! (should be "%s")', $select, $value, $max);
        }

        if(!empty($errors)) {
            MinkHelper::throwException($errors);
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

        MinkHelper::fillForm($this, 'notificationForm', $data);
        MinkHelper::pressNamedButton($this, 'notificationFormSubmit');
    }
}
