<?php

namespace Shopware\Tests\Mink\Element\Responsive;

use Shopware\Tests\Mink\Helper;

/**
 * Element: BlogComment
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class BlogComment extends \Shopware\Tests\Mink\Element\Emotion\BlogComment
{
    /** @var array $selector */
    protected $selector = ['css' => 'ul.comments--list > li.list--entry'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'author' => '.author--name',
            'date' => '.date--creation',
            'stars' => '.product--rating > .icon--star',
            'half-star' => '.product--rating > .icon--star-half',
            'headline' => '.content--headline',
            'comment' => '.content--comment'
        ];
    }

    /**
     * @return float
     */
    public function getStarsProperty()
    {
        $elements = Helper::findAllOfElements($this, ['stars', 'half-star'], false);
        return 2 * (count($elements['stars']) + 0.5 * count($elements['half-star']));
    }
}
