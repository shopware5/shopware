<?php

use Behat\Behat\Context\Step;
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
     * @Given /^I should see (\d+) blog articles$/
     */
    public function iShouldSeeBlogArticles($count)
    {
        $this->getPage('Blog')->countArticles($count);
    }


}