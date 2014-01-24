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
 * Enlight form submit button component.
 *
 * The Enlight_Components_Form_Element_Submit extends the zend submit element and adds the css class "actions" to the
 * wrapper decorator.
 *
 * @category   Enlight
 * @package    Enlight_Form
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Form_Element_Submit extends Zend_Form_Element_Submit
{
    /**
     * @var string class name for the element (default: 'btn primary')
     */
    public $class = 'btn primary';

    /**
     * Default decorators
     *
     * Uses only 'Submit' and 'DtDdWrapper' decorators by default.
     *
     * @return Zend_Form_Element_Submit
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Tooltip')
                 ->addDecorator('ViewHelper')
                 ->addDecorator('Wrapper', array('class' => 'actions'));
        }
        return $this;
    }
}
