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
 * @package    Enlight_Locale
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Base class for localization extends the zend locale class with an unique id.
 *
 * The Enlight_Components_Locale is to facilitate the development of internationalized applications.
 * Using this component it is possible the appearance of the application to adapt to local conditions.
 * Whether it is the presentation of prizes or the representation of a date.
 * The Zend_Locale component has been extended so that a configuration of an ID can be set,
 * this is achieved that the specific configuration can be conveniently stored and managed
 *
 * @category   Enlight
 * @package    Enlight_Locale
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Locale extends Zend_Locale
{
    /**
     * Unique id for the locale class.
     * @var int
     */
    protected $id;

    /**
     * Returns locale id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets a new locale
     *
     * @param  string|array|Zend_Locale $locale (Optional) New locale to set
     * @return Enlight_Components_Locale
     */
    public function setLocale($locale = null)
    {
        if (is_array($locale)) {
            $this->id = isset($locale['id']) ? (int) $locale['id'] : null;
            $locale = isset($locale['locale']) ? $locale['locale'] : null;
        }
        parent::setLocale($locale);
        return $this;
    }
}
