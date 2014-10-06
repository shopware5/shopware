<?php
namespace Page\Emotion;

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Driver\SahiDriver;
use Behat\Mink\Element\NodeElement;
use Element\Emotion\ArticleEvaluation;
use Element\MultipleElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;

class Detail extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/detail/index/sArticle/{articleId}';

    public $cssLocator = array(
        'productRating' => 'div#detailbox_middle > div.detail_comments > .star',
        'productRatingCount' => 'div#detailbox_middle > div.detail_comments > .comment_numbers',
        'productEvaluationAverage' => 'div#comments > div.overview_rating > .star',
        'productEvaluationCount' => 'div#comments > div.overview_rating > span',
        'configuratorForm' => 'div#buybox > form',
        'notificationForm' => 'form#sendArticleNotification',
        'voteForm' => 'div#comments > form'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'voteFormSubmit' => array('de' => 'Speichern',                'en' => 'Save')
    );

    protected $configuratorTypes = array(
        'table' => 'basketform',
        'standard' => 'upprice_config',
        'select' => 'config_select'
    );

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    public function verifyPage()
    {
        if (!$this->hasButton('In den Warenkorb')) {
            \Helper::throwException('Detail page has no basket button');
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
     * Go to the previous or next article
     * @param $direction
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function goToNeighbor($direction)
    {
        $link = $this->find('css', 'a.article_' . $direction);

        if (empty($link)) {
            $message = sprintf('Detail page has no %s button', $direction);
            \Helper::throwException($message);
        }

        $link->click();
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

        $locators = array_keys(current($evaluations));

        foreach ($evaluations as $key => $evaluation)
        {
            /** @var ArticleEvaluation $articleEvaluation */
            $articleEvaluation = $articleEvaluations->setInstance($key + 1);

            $result = \Helper::compareArrays($articleEvaluation->getProperties($locators), $evaluation);

            if($result !== true) {
                $message = sprintf(
                    'The evaluations are different in "%s" ("%s" is not included in "%s")',
                    $result['key'],
                    $result['value2'],
                    $result['value']
                );
                \Helper::throwException($message);
            }
        }
    }

    protected function checkRating(MultipleElement $articleEvaluations, $average)
    {
        $locators = array('productRating', 'productRatingCount', 'productEvaluationAverage', 'productEvaluationCount');

        $elements = \Helper::findElements($this, $locators);

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

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the evaluation! (%s: "%s" instead of %s)', $result, $check[$result][0], $check[$result][1]);
            \Helper::throwException($message);
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
            $locators = array('configuratorForm');
            $element = \Helper::findElements($this, $locators);

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
            \Helper::throwException($message);
        }

        $options = $group->findAll('css', 'option');

        foreach ($options as $option) {
            if ($option->getText() == $configuratorOption) {
                $message = sprintf('Configurator option %s founded but should not', $configuratorOption);
                \Helper::throwException($message);
            }
        }
    }

    /**
     * @param array $data
     */
    public function writeEvaluation(array $data)
    {
        \Helper::fillForm($this, 'voteForm', $data);
        \Helper::pressNamedButton($this, 'voteFormSubmit');
    }
}
