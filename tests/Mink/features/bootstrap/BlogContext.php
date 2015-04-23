<?php

use Page\Emotion\Blog;
use Element\MultipleElement;
use Element\Emotion\BlogBox;
use Behat\Gherkin\Node\TableNode;

require_once 'SubContext.php';

class BlogContext extends SubContext
{
    /**
     * @Given /^I am on the blog category (?P<categoryId>\d+)$/
     * @Given /^I go to the blog category (?P<categoryId>\d+)$/
     */
    public function iAmOnTheBlogCategory($categoryId)
    {
        $this->getPage('Blog')->open(array('categoryId' => $categoryId));
    }

    /**
     * @Given /^I click to read the blog article on position (\d+)$/
     */
    public function iClickToReadTheBlogArticleOnPosition($position)
    {
        /** @var Blog $page */
        $page = $this->getPage('Blog');
        $language = Helper::getCurrentLanguage($page);

        /** @var MultipleElement $blogBoxes */
        $blogBoxes = $this->getElement('BlogBox');
        $blogBoxes->setParent($page);

        /** @var BlogBox $blogBox */
        $blogBox = $blogBoxes->setInstance($position);
        Helper::clickNamedLink($blogBox, 'readMore', $language);
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
        $this->getContainer()->get('db')->exec($sql);
        $this->getSession()->reload();
    }
}
