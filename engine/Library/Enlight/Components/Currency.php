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
 * @package    Enlight_Currency
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Class for handling currency notations.
 *
 * Besides the ability to deal with multiple languages ​​to Enlightenment also offers the ability to handle multiple currencies.
 * This brings the Enlight_Components_Currency component.
 * This extends from the Zend_Currency component, but it extends to an ID.
 * This makes it easy to access a particular currency representation.
 *
 * @category   Enlight
 * @package    Enlight_Currency
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Currency extends Zend_Currency
{
    /**
     * Unique id of the currency class
     * @var int
     */
    protected $id;

    /**
     * Returns the locale id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the formating options of the localized currency string
     * If no parameter is passed, the standard setting of the
     * actual set locale will be used
     *
     * @param  array $options (Optional) Options to set
     * @return Zend_Currency
     */
    public function setFormat(array $options = array())
    {
        if (isset($options['id'])) {
            $this->id = (int) $options['id'];
        }
        return parent::setFormat($options);
    }
}
