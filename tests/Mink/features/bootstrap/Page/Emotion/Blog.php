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
}