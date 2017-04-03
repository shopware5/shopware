<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;

/**
 * Element: ArticleEvaluation
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ArticleEvaluation extends BlogComment
{
    /** @var array $selector */
    protected $selector = ['css' => 'div.review--entry:not(.is--answer)'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'author' => 'span.content--field:nth-of-type(2)',
            'date' => 'span.content--field:nth-of-type(3)',
            'stars' => 'span.product--rating > meta:nth-of-type(1)',
            'headline' => 'h4.content--title',
            'comment' => 'p.review--content',
            'answer' => 'div + div.is--answer',
        ];
    }

    /**
     * Returns the star rating
     *
     * @return float
     */
    public function getStarsProperty()
    {
        $elements = Helper::findElements($this, ['stars']);

        return floatval($elements['stars']->getAttribute('content')) * 2;
    }

    /**
     * Returns the shop owners answer to customers evaluation
     *
     * @return string
     */
    public function getAnswerProperty()
    {
        $elements = Helper::findElements($this, ['answer'], false);

        return ($elements['answer']) ? $elements['answer']->getText() : '';
    }
}
