<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Form
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Error component for the enlight form decorator.
 *
 * The Enlight_Components_Form_Decorator_Errors extends the zend form
 * decorator error class with enlight specified css classes.
 *
 * @category   Enlight
 * @package    Enlight_Form
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Form_Decorator_Errors extends Zend_Form_Decorator_Errors
{
    /**
     * Decorator options
     * @var array
     */
    protected $_options = array('class' => 'help-inline-block error');
}
