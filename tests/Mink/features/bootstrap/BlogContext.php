<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Element\BlogBox;
use Shopware\Tests\Mink\Page\Blog;

class BlogContext extends SubContext
{
    /**
     * @Given /^I am on the blog category (?P<categoryId>\d+)$/
     * @Given /^I go to the blog category (?P<categoryId>\d+)$/
     */
    public function iAmOnTheBlogCategory($categoryId)
    {
        $this->getPage('Blog')->open(['categoryId' => $categoryId]);
    }

    /**
     * @Given /^I click to read the blog article on position (\d+)$/
     */
    public function iClickToReadTheBlogArticleOnPosition($position)
    {
        /** @var Blog $page */
        $page = $this->getPage('Blog');

        /** @var BlogBox $blogBox */
        $blogBox = $this->getMultipleElement($page, 'BlogBox', $position);
        Helper::clickNamedLink($blogBox, 'readMore');
    }

    /**
     * @When /^I write a comment:$/
     */
    public function iWriteAComment(TableNode $data)
    {
        $this->getPage('Blog')->writeComment($data->getHash());
    }

    /**
     * @When /^the shop owner activates my latest comment$/
     */
    public function theShopOwnerActivateMyLatestComment()
    {
        $sql = 'UPDATE `s_blog_comments` SET `active`= 1 ORDER BY id DESC LIMIT 1';
        $this->getService('db')->exec($sql);
        $this->getSession()->reload();
    }

    /**
     * @Then /^I should see an average evaluation of (\d+) from following comments:$/
     */
    public function iShouldSeeAnAverageEvaluationOfFromFollowingComments($average, TableNode $comments)
    {
        /** @var \Shopware\Tests\Mink\Page\Blog $page */
        $page = $this->getPage('Blog');

        /** @var \Shopware\Tests\Mink\Element\MultipleElement $blogComments */
        $blogComments = $this->getMultipleElement($page, 'BlogComment');

        $page->checkComments($blogComments, $average, $comments->getHash());
    }
}
