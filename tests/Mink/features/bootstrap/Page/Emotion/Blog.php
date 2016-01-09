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
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'commentForm' => 'form.comments',
            'articleRating' => '.blogdetail_header .star',
            'articleRatingCount' => '.blogdetail_header a',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'rssFeed' => ['de' => 'RSS-Feed', 'en' => 'RSS-Feed'],
            'atomFeed' => ['de' => 'Atom-Feed', 'en' => 'Atom-Feed'],
            'commentFormSubmit' => ['de' => 'Speichern', 'en' => 'Save'],
            'writeCommentButton' => ['de' => 'Kommentar schreiben', 'en' => 'Write a comment']
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     * @throws \Exception
     */
    public function verifyPage()
    {
        if (Helper::hasNamedLinks($this, ['rssFeed', 'atomFeed']) == true) {
            return;
        }

        $message = ['You are not on blog page!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
        Helper::throwException($message);
    }

    /**
     * Fills out the comment form and submits it
     * @param array $data
     */
    public function writeComment(array $data)
    {
        $writeCommentLink = $this->getSession()
            ->getPage()
            ->find("css", ".blog--comments-form a.btn--create-entry");

        if ($writeCommentLink) {
            $writeCommentLink->click();
        }

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

        if ($result === true) {
            return;
        }

        $messages = ['The following comments are wrong:'];
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
     * Helper function to check the rating of a blog comment
     * @param BlogComment $blogComments
     * @param $average
     * @throws \Exception
     */
    protected function checkRating(BlogComment $blogComments, $average)
    {
        $elements = Helper::findElements($this, ['articleRating', 'articleRatingCount']);

        $check = [
            'articleRating' => [$elements['articleRating']->getAttribute('class'), $average],
            'articleRatingCount' => [$elements['articleRatingCount']->getText(), count($blogComments)]
        ];

        $check = Helper::floatArray($check);
        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the rating! (%s: "%s" instead of "%s")', $result, $check[$result][0], $check[$result][1]);
            Helper::throwException($message);
        }
    }
}
