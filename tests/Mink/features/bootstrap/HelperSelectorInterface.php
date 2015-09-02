<?php

namespace Shopware\Tests\Mink;

/**
 * Interface: HelperSelectorInterface
 *
 * Required to use Helper class
 */
interface HelperSelectorInterface
{
    /**
     * Returns an array of all css selectors of the element/page
     *
     * Example:
     * return [
     *  'image' = 'a > img',
     *  'link' = 'a',
     *  'text' = 'p'
     * ]
     *
     * @return string[]
     */
    public function getCssSelectors();

    /**
     * Returns an array of all named selectors of the element/page
     *
     * Example:
     * return [
     *  'submit' = ['de' = 'Absenden',     'en' = 'Submit'],
     *  'reset'  = ['de' = 'Zurücksetzen', 'en' = 'Reset']
     * ]
     *
     * @return array[]
     */
    public function getNamedSelectors();
}
