<?php
require_once(dirname(__FILE__).'/Application.php');

/**
 * Enlight
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Enlight
 */
if (!class_exists('Enlight')) {
    class Enlight extends Enlight_Application
    {
        /**
         * Constructor
         */
        public function __construct($environment, $options = null)
        {
            Enlight($this);
            parent::__construct($environment, $options);
        }
    }
}
/**
 * Enlight
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Enlight
 * @return Enlight
 */
function Enlight($newInstance=null)
{
    static $instance;
    if (isset($newInstance)) {
        $oldInstance = $instance;
        $instance = $newInstance;
        return $oldInstance;
    } elseif (!isset($instance)) {
        $instance = Enlight::Instance();
    }
    return $instance;
}
