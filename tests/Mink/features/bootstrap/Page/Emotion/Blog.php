<?php
namespace Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Blog extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/blog/index/sCategory/{categoryId}';

    public $cssLocator = array(
        'commentForm' => 'form.comments'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'commentFormSubmit' => array('de' => 'Speichern',                'en' => 'Save')
    );

    /**
     * @param array $data
     */
    public function writeComment(array $data)
    {
        \Helper::fillForm($this, 'commentForm', $data);
        \Helper::pressNamedButton($this, 'commentFormSubmit');
    }
}
