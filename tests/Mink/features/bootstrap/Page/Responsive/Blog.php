<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Shopware\Tests\Mink\Element\Emotion\BlogComment;
use Shopware\Tests\Mink\Helper;

class Blog extends \Shopware\Tests\Mink\Page\Emotion\Blog
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'commentForm' => 'div.blog--comments-form > form',
            'articleRating' => 'div.blog--box-metadata .product--rating > meta',
            'articleRatingCount' => 'div.blog--box-metadata .blog--metadata-comments > a'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function checkRating(BlogComment $blogComments, $average)
    {
        $elements = Helper::findElements($this, ['articleRating', 'articleRatingCount']);

        $check = [
            'articleRating' => [$elements['articleRating']->getAttribute('content'), $average],
            'articleRatingCount' => [$elements['articleRatingCount']->getText(), count($blogComments)]
        ];

        $check = Helper::floatArray($check);
        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the rating! (%s: "%s" instead of "%s")', $result, $check[$result][0], $check[$result][1]);
            Helper::throwException($message);
        }
    }
}
