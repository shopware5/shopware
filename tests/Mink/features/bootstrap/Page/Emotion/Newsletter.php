<?php
namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page,
    Behat\Behat\Context\Step;

class Newsletter extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/newsletter';

    public $cssLocator = array(
        'newsletterForm' => 'form#letterForm'
    );

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    public function verifyPage()
    {
        $locators = array('newsletterForm');
        \Helper::findElements($this, $locators);
    }
}
