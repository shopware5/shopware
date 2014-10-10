<?php
namespace Page\Responsive;

class Blog extends \Page\Emotion\Blog
{
    public $cssLocator = array(
        'commentForm' => 'div.blog--comments-form > form'
    );
}
