<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use Shopware\Tests\Mink\Element\Emotion\BlogComment;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Blog extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/blog/index/sCategory/{categoryId}';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'commentForm' => 'form.comments'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'commentFormSubmit' => array('de' => 'Speichern', 'en' => 'Save')
        );
    }

    /**
     * @param array $data
     */
    public function writeComment(array $data)
    {
        Helper::fillForm($this, 'commentForm', $data);
        Helper::pressNamedButton($this, 'commentFormSubmit');
    }

    /**
     * Checks the evaluations of the current article
     * @param BlogComment $blogComments
     * @param $average
     * @param array $comments
     * @throws \Exception
     */
    public function checkComments(BlogComment $blogComments, $average, array $comments)
    {
        $this->checkRating($blogComments, $average);

        $comments = Helper::floatArray($comments, ['stars']);
        $result = Helper::assertElements($comments, $blogComments);

        if($result === true) {
            return;
        }

        $messages = array('The following comments are wrong:');
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
        Helper::throwException($messages);
    }

    /**
     * @param BlogComment $blogComments
     * @param $average
     * @throws \Exception
     */
    protected function checkRating(BlogComment $blogComments, $average)
    {
//        $locators = array('productRating', 'productRatingCount', 'productEvaluationAverage', 'productEvaluationCount');
//
//        $elements = Helper::findElements($this, $locators);
//
//        $check = array();
//
//        foreach($elements as $locator => $element)
//        {
//            switch($locator) {
//                case 'productRating':
//                case 'productEvaluationAverage':
//                    $check[$locator] = array($element->getAttribute('class'), $average);
//                    break;
//
//                case 'productRatingCount':
//                case 'productEvaluationCount':
//                    $check[$locator] = array($element->getText(), count($articleEvaluations));
//                    break;
//            }
//        }
//
//        $result = Helper::checkArray($check);
//
//        if ($result !== true) {
//            $message = sprintf('There was a different value of the evaluation! (%s: "%s" instead of %s)', $result, $check[$result][0], $check[$result][1]);
//            Helper::throwException($message);
//        }
    }
}
