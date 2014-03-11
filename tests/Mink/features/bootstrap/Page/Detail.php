<?php

use Behat\Mink\Driver\SahiDriver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Context\Step;

class Detail extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/detail/index/sArticle/{articleId}';

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    protected function verifyPage()
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
     * @throws Behat\Mink\Exception\ResponseTextException
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
        $elements = array();
        $check = array();

        $locator = 'div#comments ';

        $elements['div-average'] = $this->find('css', $locator . 'div.overview_rating div.star');
        $elements['div-count-evaluations'] = $this->find('css', $locator . 'div.overview_rating');

        $check[] = array($elements['div-average']->getAttribute('class'), $average);
        $check[] = array($elements['div-count-evaluations']->getText(), (string)count($evaluations));

        $locator .= 'div.comment_block';

        $comments = $this->findAll('css', $locator . '.no_border');

        if (count($comments) !== count($evaluations)) {
            $message = sprintf(
                'There is a difference to the number of evaluations of the article (should be %d, but is %d)',
                count($evaluations),
                count($comments)
            );
            throw new ResponseTextException($message, $this->getSession());
        }

        foreach ($comments as $key => $comment) {
            $elements = array();
            $offset = 2 * $key + 2;

            $elements['div-stars'] = $this->find(
                'css',
                sprintf('%s:nth-of-type(%d) div.star', $locator, $offset + 1)
            );
            $elements['strong-author'] = $this->find(
                'css',
                sprintf('%s:nth-of-type(%d) strong.author', $locator, $offset + 1)
            );
            $elements['h3-title'] = $this->find(
                'css',
                sprintf('%s:nth-of-type(%d) div.right_container h3', $locator, $offset + 1)
            );
            $elements['p-text'] = $this->find(
                'css',
                sprintf('%s:nth-of-type(%d) div.right_container p', $locator, $offset + 1)
            );
            $elements['div-answer'] = $this->find(
                'css',
                sprintf('%s:nth-of-type(%d) div.right_container', $locator, $offset + 2)
            );

            $check[] = array($elements['div-stars']->getAttribute('class'), $evaluations[$key]['evaluation']);
            $check[] = array($elements['strong-author']->getText(), $evaluations[$key]['author']);
            $check[] = array($elements['h3-title']->getText(), $evaluations[$key]['title']);
            $check[] = array($elements['p-text']->getText(), $evaluations[$key]['text']);
            $check[] = array($elements['div-answer']->getText(), $evaluations[$key]['comment']);
        }

        if (!$this->checkArray($check)) {
            $message = sprintf('The evaluations are different');
            throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Helper function to check each row of an array. If each second sub-element of a row is in its first, check is true
     * @param array $check
     * @return bool
     */
    private function checkArray($check)
    {
        foreach ($check as $compare) {
            if ($compare[0] === $compare[1]) {
                continue;
            }

            if (strpos($compare[0], $compare[1]) === false) {
                return false;
            }
        }

        return true;
    }
}
