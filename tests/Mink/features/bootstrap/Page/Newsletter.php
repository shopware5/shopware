<?php
namespace  Shopware\Tests\Mink\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Newsletter extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/newsletter';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'newsletterForm' => 'div.newsletter--form > form'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'newsletterFormSubmit' => ['de' => 'Speichern', 'en' => 'Save']
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     * @throws \Exception
     */
    public function verifyPage()
    {
        $errors = [];

        if (!$this->hasSelect('subscribeToNewsletter')) {
            $errors[] = '- There is no newsletter subscription select!';
        }

        if (!$this->hasField('newsletter')) {
            $errors[] = '- There is no email field!';
        }

        if (!$this->hasSelect('salutation')) {
            $errors[] = '- There is no salutation select!';
        }

        if (!$this->hasField('firstname')) {
            $errors[] = '- There is no firstname field!';
        }

        if (!$this->hasField('lastname')) {
            $errors[] = '- There is no lastname field!';
        }

        if (!$this->hasField('street')) {
            $errors[] = '- There is no street field!';
        }

        if (!$this->hasField('zipcode')) {
            $errors[] = '- There is no zip code field!';
        }

        if (!$this->hasField('city')) {
            $errors[] = '- There is no city field!';
        }

        if (!$errors) {
            return;
        }

        $message = ['You are not on the newsletter page:'];
        $message = array_merge($message, $errors);
        $message[] = 'Current URL: ' . $this->getSession()->getCurrentUrl();
        Helper::throwException($message);
    }

    /**
     * Subscribes to the newsletter
     * @param array $data
     */
    public function subscribeNewsletter(array $data)
    {
        $mode = [
            [
                'field' => 'subscribeToNewsletter',
                'value' => 1
            ]
        ];

        $data = array_merge($data, $mode);

        Helper::fillForm($this, 'newsletterForm', $data);
        Helper::pressNamedButton($this, 'newsletterFormSubmit');
    }

    /**
     * Unsubscribes from the newsletter
     * @param array $data
     */
    public function unsubscribeNewsletter(array $data)
    {
        $mode = [
            [
                'field' => 'subscribeToNewsletter',
                'value' => -1
            ]
        ];

        $data = array_merge($data, $mode);

        Helper::fillForm($this, 'newsletterForm', $data);
        Helper::pressNamedButton($this, 'newsletterFormSubmit');
    }
}
