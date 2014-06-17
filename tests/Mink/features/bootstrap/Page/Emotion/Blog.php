<?php
namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;

class Blog extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/blog/index/sCategory/{categoryId}';

    /**
     * Counts the blog articles
     * If the number is not equal to $count, the helper function will throw an exception $message.
     * @param int $count
     */
    public function countArticles($count = 0)
    {
        $result = \Helper::countElements($this, 'div.blogbox', $count);

        if ($result !== true) {
            $message = sprintf('There are %d blog articles (should be %d)', $result, $count);
            \Helper::throwException(array($message));
        }
    }
}