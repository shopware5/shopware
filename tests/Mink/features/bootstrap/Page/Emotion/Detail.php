<?php
namespace Emotion;

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Driver\SahiDriver;
use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Context\Step;

class Detail extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/detail/index/sArticle/{articleId}';

    public $cssLocator = array(
        'productRating' => 'div#detailbox_middle > div.detail_comments',
        'productReviews' => 'div#comments',
        'productRatingAverage' => '.star',
        'commentRating' => '.star',
        'commentNumber' => 'span.comment_numbers',
        'commentBlock' => 'div.comment_block',
        'commentAuthor' => 'strong.author span.name',
        'commentDate' => 'span.date',
        'commentTitle' => 'div.right_container > h3',
        'commentText' => 'div.right_container > p',
        'commentAnswer' => 'div.right_container',
        'configuratorForm' => 'div#buybox > form'
    );

    protected $configuratorTypes = array(
        'basketform' => 'table',
        'upprice_config' => 'standard',
        'config_select' => 'select'
    );

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    public function verifyPage()
    {
        if (!$this->hasButton('In den Warenkorb')) {
            throw new \Exception('Detail page has no basket button');
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
            throw new ResponseTextException($message, $this->getSession());
        }

        $link->click();
    }

    /**
     * Checks the evaluations of the current article
     * @param integer $average
     * @param array $evaluations
     */
    public function checkEvaluations($average, $evaluations)
    {
        $testEvaluations = array(
            'average' => $average,
            'evaluations' => $evaluations
        );

        $locators = array('productRating', 'productReviews');
        $elements = \Helper::findElements($this, $locators);

        $rating = $elements['productRating'];
        $reviews = $elements['productReviews'];

        $locators = array('productRatingAverage', 'commentNumber');
        $elements = \Helper::findElements($rating, $locators, $this->cssLocator);

        preg_match("/\d+/",$elements['commentNumber']->getText(),$commentNumber);
        $commentNumber = intval($commentNumber[0]);

        if($commentNumber !== count($evaluations))
        {
            $message = sprintf(
                'There is a difference to the number of evaluations of the article (should be %d, but is %d)',
                count($evaluations),
                $commentNumber
            );
            throw new ResponseTextException($message, $this->getSession());
        }

        $readEvaluations = array(
            'average' => $this->getEvaluation($elements['productRatingAverage']),
            'evaluations' => array()
        );

        $locators = array('commentBlock');
        $elements = \Helper::findElements($reviews, $locators, $this->cssLocator, true);

        $comments = $elements['commentBlock'];

        for($i=0; $i<count($comments); $i++)
        {
            $locators = array('commentRating', 'commentAuthor', 'commentDate', 'commentTitle', 'commentText');
            $elements = \Helper::findElements($comments[$i], $locators, $this->cssLocator);

            $i++;

            $locators = array('commentAnswer');
            $elements2 = \Helper::findElements($comments[$i], $locators, $this->cssLocator);

            $evaluation = array(
                'author' => $elements['commentAuthor']->getText(),
                'evaluation' => $this->getEvaluation($elements['commentRating']),
                'title' => $elements['commentTitle']->getText(),
                'text' => $elements['commentText']->getText(),
                'comment' => $elements2['commentAnswer']->getText()
            );

            $readEvaluations['evaluations'][] = $evaluation;
        }

        $result = \Helper::compareArrays($readEvaluations, $testEvaluations);

        if ($result === true) {
            return;
        }

        $message = "An error occurred.";

        switch($result['error'])
        {
            case 'keyNotExists':
                $message = sprintf('The key "%s" fails in test data! (Keys exist: %s)',
                    $result['key'],
                    implode(', ', array_keys($evaluations[0]))
                );
                break;

            case 'comparisonFailed':
                $message = sprintf('The evaluations are different in "%s" ("%s" is not included in "%s")',
                    $result['key'],
                    $result['value'],
                    $result['value2']
                );
                break;
        }

        throw new ResponseTextException($message, $this->getSession());
    }

    /**
     * Helper function how to read the evaluation from the evaluation element
     * @param NodeElement $element
     * @return string
     */
    protected function getEvaluation($element)
    {
        return (string)$element->getAttribute('class');
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
            $configuratorType = $this->configuratorTypes[$configuratorClass];
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
        $form = $this->find(
            'css',
            'form.config_select'
        );

        $group = $form->findField(
            'group[7]'
        );

        $options = $group->findAll(
            'css',
            'option'
        );

        foreach($options as $option) {
            if ($option->getText() == $configuratorOption) {
                throw new Exception(
                    sprintf('Configurator option %s founded but should not', $configuratorOption)
                );
            }
        }
    }
}
