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

    public $namedSelectors = array(
        'newsletterFormSubmit' => array('de' => 'Speichern',                'en' => 'Save')
    );

    /**
     * @param array $data
     */
    public function subscribeNewsletter(array $data)
    {
        $mode = array(
            array(
                'field' => 'subscribeToNewsletter',
                'value' => 1
            )
        );

        $data = array_merge($data, $mode);

        \Helper::fillForm($this, 'newsletterForm', $data);
        \Helper::pressNamedButton($this, 'newsletterFormSubmit');
    }

    /**
     * @param array $data
     */
    public function unsubscribeNewsletter(array $data)
    {
        $mode = array(
            array(
                'field' => 'subscribeToNewsletter',
                'value' => -1
            )
        );

        $data = array_merge($data, $mode);

        \Helper::fillForm($this, 'newsletterForm', $data);
        \Helper::pressNamedButton($this, 'newsletterFormSubmit');
    }
}
