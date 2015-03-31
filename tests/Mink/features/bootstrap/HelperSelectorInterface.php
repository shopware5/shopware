<?php

interface HelperSelectorInterface
{
    /**
     * Returns an array of all css selectors of the element/page
     *
     * Example:
     * return array (
     *  'image' = 'a > img',
     *  'link' = 'a',
     *  'text' = 'p'
     * )
     *
     * @return array
     */
    public function getCssSelectors();

    /**
     * Returns an array of all named selectors of the element/page
     *
     * Example:
     * return array (
     *  'submit' = array('de' = 'Absenden',     'en' = 'Submit'),
     *  'reset'  = array('de' = 'Zurücksetzen', 'en' = 'Reset')
     * )
     *
     * @return array
     */
    public function getNamedSelectors();
}