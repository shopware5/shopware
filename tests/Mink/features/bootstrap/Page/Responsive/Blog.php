<?php
namespace Page\Responsive;

class Blog extends \Page\Emotion\Blog
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