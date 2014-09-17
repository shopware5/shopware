<?php
namespace Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Blog extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/blog/index/sCategory/{categoryId}';
}
