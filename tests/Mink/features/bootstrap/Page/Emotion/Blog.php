<?php
namespace Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Blog extends Page implements \HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/blog/index/sCategory/{categoryId}';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'commentForm' => 'form.comments'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'commentFormSubmit' => array('de' => 'Speichern', 'en' => 'Save')
        );
    }

    /**
     * @param array $data
     */
    public function writeComment(array $data)
    {
        \Helper::fillForm($this, 'commentForm', $data);
        \Helper::pressNamedButton($this, 'commentFormSubmit');
    }
}
