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
     * @param array $blogBoxes
     * @param int $count
     */
    public function countArticles($blogBoxes, $count = 0)
    {
        if ($count !== count($blogBoxes)) {
            $message = sprintf('There are %d blog articles (should be %d)', count($blogBoxes), $count);
            \Helper::throwException($message);
        }
    }
}