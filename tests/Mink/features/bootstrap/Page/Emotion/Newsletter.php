<?php
namespace Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Newsletter extends Page implements \HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/newsletter';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'newsletterForm' => 'form#letterForm'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'newsletterFormSubmit' => array('de' => 'Speichern', 'en' => 'Save')
        );
    }

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
