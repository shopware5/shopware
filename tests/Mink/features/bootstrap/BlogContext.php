<?php

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
        /** @var \Emotion\Blog $page */
        $page = $this->getPage('Blog');
        $language = Helper::getCurrentLanguage($page);

        /** @var MultipleElement $blogBoxes */
        $blogBoxes = $this->getElement('BlogBox');
        $blogBoxes->setParent($page);

        /** @var \Emotion\BlogBox $blogBox */
        $blogBox = $blogBoxes->setInstance($position);
        $blogBox->clickActionLink('readMore', $language);
    }

}
