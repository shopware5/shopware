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
 * @package    Enlight_Site
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Forms the interface to the location, currency and template, for easy configuration and localization of websites specific properties.
 *
 * The Enlight_Components_Site handles the location, currency, template, host and resources of a single website.
 * With the Enlight_Components_Site it is possible to operate Subshop eCommerce.
 * When creating a new site component the passed options array contains the different settings and resources.
 * For the special option array keys look at the constructor function.
 *
 * @category   Enlight
 * @package    Enlight_Site
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Site
{
    /**
     * @var array Contains all site properties which can be set in the class constructor or in the setter method.
     */
    protected $properties = array();

    /**
     * @var array Contains all site resources which can be set in the class constructor or in the setter method.
     */
    protected $resources = array();

    /**
     * When creating a new site component the passed options array could contains the
     * different settings and resources with the following keys:<br>
     * <b>id</b> - The numerical identifier of the site.
     * This is for example used for switching to determine which site of a configuration may be one other<br>
     * <b>name</b> - A name that describes the site. For example 'My Shop - Germany' or 'My Shop U.S.'<br>
     * <b>locale</b> - Expects an instance of the Enlight_Components_Locale class, which keeping
     * all locale settings for a specific site<br>
     * <b>currency</b> - a currency Enlight component can be stored under this key.<br>
     * <b>localeswitch</b> - Determines which can be changed on locale settings.
     * For example: 1 | 2 | 4 Would this mean to change the locale settings with IDs 1, 2 or 4 are expected.<br>
     * <b>currencyswitch</b> - Sets may be exchanged for the currency settings.
     * For example: 1 | 2 This should be changed back and forth between the two currencies settings 1 and 2.<br>
     * <b>siteswitch</b> - Determines which may be changed on site configurations.
     * For example: 1 This must not be changed.<br>
     * <b>host</b> - the domain name that applies to this site. This makes it possible to automatically
     * switch to a particular site if a particular domain is called.<br>
     * <b>template</b> - The configuration of the template engine. Thus, the different
     * sites get different look easily and comfortably.<br>
     *
     * @param null|array|Enlight_Config $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Enlight_Config) {
            $options = $options->toArray();
        }
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Sets the options
     *
     * @param   array $options
     * @return  Enlight_Components_Site
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            $key = strtolower($key);
            switch ($key) {
                case 'id':
                    $this->properties[$key] = (int) $option;
                    break;
                case 'name':
                    $this->properties[$key] = (string) $option;
                    break;
                case 'locale':
                    $this->setLocale($option);
                    break;
                case 'currency':
                    $this->setCurrency($option);
                    break;
                case 'locale' . 'switch':
                case 'currency' . 'switch':
                case 'site' . 'switch':
                    if (!is_array($option)) {
                        $option = explode('|', $option);
                    }
                    $this->properties[$key] = $option;
                    break;
                case 'host':
                    $this->setHost($option);
                    break;
                case 'template':
                    $this->setTemplate($option);
                    break;
                default:
                    $this->properties[$key] = $option;
                    break;
            }
        }
        return $this;
    }

    /**
     * Sets a property by name.
     *
     * @param   $property
     * @param   $value
     * @return  Enlight_Components_Site
     */
    public function set($property, $value)
    {
        $method = 'set' . self::normalizePropertyName($property);

        if ($method != 'setOptions' && method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $property = strtolower($property);
            $this->properties[$property] = $value;
        }

        return $this;
    }

    /**
     * Returns a property by name.
     *
     * @param   $property
     * @return  mixes
     */
    public function get($property)
    {
        $property = strtolower($property);
        if (isset($this->properties[$property])) {
            return $this->properties[$property];
        }
        return null;
    }

    /**
     * Set site host method
     *
     * @param   int|string $host
     * @return  Enlight_Components_Site
     */
    public function setHost($host = null)
    {
        if ($host === null && isset($this->properties['host'])) {
            $host = $this->properties['host'];
        }
        if (empty($host) && isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }
        $this->properties['host'] = trim($host);
        return $this;
    }

    /**
     * Sets / loads the locale instance.
     *
     * @param   int|string|Zend_Locale $locale
     * @return  Enlight_Components_Site
     */
    public function setLocale($locale = null)
    {
        if ($locale === null && isset($this->properties['locale'])) {
            $locale = $this->properties['locale'];
        } elseif ($locale !== null) {
            $this->properties['locale'] = $locale;
        }
        $this->resources['locale'] = new Enlight_Components_Locale($locale);
        unset($this->properties['currency']);
        return $this;
    }

    /**
     * Sets / loads the currency instance.
     *
     * @param   null $currency
     * @return  Enlight_Components_Site
     */
    public function setCurrency($currency = null)
    {
        if ($currency === null && isset($this->properties['currency'])) {
            $currency = $this->properties['currency'];
        } elseif ($currency !== null) {
            $this->properties['currency'] = $currency;
        }
        $this->resources['currency'] = new Enlight_Components_Currency($currency, $this->Locale());
        return $this;
    }

    /**
     * Setter method for the template resource. The template resource must contains
     * a compile_id and a template directory.
     *
     * @param   null|string|array $template
     */
    public function setTemplate($template = null)
    {
        if ($template === null && isset($this->properties['template'])) {
            $template = $this->properties['template'];
        } elseif ($template !== null) {
            $this->properties['template'] = $template;
        }

        if (is_string($template)) {
            $template = array('template_dir' => $template);
        }
        $template = (array) $template;
        if (!isset($template['compile_id'])) {
            $template['compile_id'] = $this->getName() . '|' . $this->Locale()->toString();
        }

        $this->resources['template'] = new Enlight_Template_Manager($template);
    }

    /**
     * Standard getter function for the id property
     * @return  int
     */
    public function getId()
    {
        return isset($this->properties['id']) ? $this->properties['id'] : null;
    }

    /**
     * Standard getter function for the name property
     * @return  string
     */
    public function getName()
    {
        if (isset($this->properties['name'])) {
            return $this->properties['name'];
        } else {
            return 'site' . $this->getId();
        }
    }

    /**
     * Standard getter function for the host property
     *
     * @return  string
     */
    public function getHost()
    {
        if (!isset($this->properties['host'])) {
            $this->setHost();
        }
        return $this->properties['host'];
    }

    /**
     * Returns shop locale
     *
     * @return  Zend_Locale
     */
    public function Locale()
    {
        if (!isset($this->resources['locale'])) {
            $this->setLocale();
        }
        return $this->resources['locale'];
    }

    /**
     * Returns shop currency
     *
     * @return  Zend_Currency
     */
    public function Currency()
    {
        if (!isset($this->resources['currency'])) {
            $this->setCurrency();
        }
        return $this->resources['currency'];
    }

    /**
     * Getter method for the template resource.
     * @return Enlight_Template_Manager
     */
    public function Template()
    {
        if (!isset($this->resources['template'])) {
            $this->setTemplate();
        }
        return $this->resources['template'];
    }

    /**
     * Standard getter function for the site resources
     *
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Sleep instance method
     *
     * @return array
     */
    public function __sleep()
    {
        return array('properties');
    }

    /**
     * Wakeup instance method
     */
    public function __wakeup()
    {
    }

    /**
     * Normalizes a property name
     *
     * @param  string $property  property name to normalize
     * @return string            normalized property name
     */
    protected static function normalizePropertyName($property)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
    }
}
