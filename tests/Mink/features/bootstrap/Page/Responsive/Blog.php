<?php
namespace Shopware\Tests\Mink\Page\Responsive;

class Blog extends \Shopware\Tests\Mink\Page\Emotion\Blog
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'commentForm' => 'div.blog--comments-form > form'
        );
    }
}
