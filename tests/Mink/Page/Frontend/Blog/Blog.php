<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Mink\Page\Frontend\Blog;

use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Frontend\Blog\Elements\BlogComment;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

class Blog extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = '/blog/index/sCategory/{categoryId}';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'commentForm' => 'div.blog--comments-form > form',
            'articleRating' => 'div.blog--box-metadata .product--rating > meta',
            'articleRatingCount' => 'div.blog--box-metadata .blog--metadata-comments > a',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'rssFeed' => ['de' => 'RSS-Feed', 'en' => 'RSS-Feed'],
            'atomFeed' => ['de' => 'Atom-Feed', 'en' => 'Atom-Feed'],
            'commentFormSubmit' => ['de' => 'Speichern', 'en' => 'Save'],
            'writeCommentButton' => ['de' => 'Kommentar schreiben', 'en' => 'Write a comment'],
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @throws Exception
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
     */
    public function writeComment(array $data)
    {
        $this->openCommentSection();

        Helper::fillForm($this, 'commentForm', $data);

        Helper::pressNamedButton($this, 'commentFormSubmit');
    }

    /**
     * Checks the evaluations of the current article
     *
     * @throws Exception
     */
    public function checkComments(BlogComment $blogComments, string $average, array $comments)
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

    public function openCommentSection()
    {
        $writeCommentLink = $this->getSession()
            ->getPage()
            ->find('css', '.blog--comments-form a.btn--create-entry');

        if ($writeCommentLink) {
            $writeCommentLink->click();
        }
    }

    /**
     * Helper function to check the rating of a blog comment
     *
     * @throws Exception
     */
    protected function checkRating(BlogComment $blogComments, string $average): void
    {
        $elements = Helper::findElements($this, ['articleRating', 'articleRatingCount']);

        $check = [
            'articleRating' => [$elements['articleRating']->getAttribute('content'), $average],
            'articleRatingCount' => [$elements['articleRatingCount']->getText(), \count($blogComments)],
        ];

        $check = Helper::floatArray($check);
        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the rating! (%s: "%s" instead of "%s")', $result, $check[$result][0], $check[$result][1]);
            Helper::throwException($message);
        }
    }
}
