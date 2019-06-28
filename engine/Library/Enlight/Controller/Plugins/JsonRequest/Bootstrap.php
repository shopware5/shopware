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
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * With this plugin can be decoded in the json data request.
 * This plugin can be activated in the init method of the plugin.
 *
 * <code>
 * $this->Front()->Plugins()->JsonRequest()
 *     ->setParseInput()
 *     ->setParseParams(array('group', 'sort'));
 * </code>
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_Plugins_JsonRequest_Bootstrap extends Enlight_Plugin_Bootstrap_Default
{
    /**
     * @var bool
     */
    protected $padding;

    /**
     * @var bool
     */
    protected $parseInput;

    /**
     * @var array
     */
    protected $parseParams = [];

    /**
     * Init this plugin. This Plugin should run before the dispatching process.
     */
    public function init()
    {
        if ($this->Collection() === null) {
            return;
        }
        $event = new Enlight_Event_Handler_Default('Enlight_Controller_Action_PreDispatch', [
            $this, 'onPreDispatch',
        ]);
        $this->Application()->Events()->registerListener($event);
    }

    /**
     * Called from the event manager before the dispatch process.
     * Parse the json input data, when it was activated.
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @return bool
     */
    public function onPreDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $subject */
        $subject = $args->get('subject');
        $request = $subject->Request();

        // Parses the json input data, if the content type is correct
        if (
            $this->parseInput === true
            && ($contentType = $request->getHeader('Content-Type')) !== false
            && strpos($contentType, 'application/json') === 0
            && ($input = $request->getRawBody()) !== false
        ) {
            if ($input != '') {
                $input = Zend_Json::decode($input);
            } else {
                $input = null;
            }

            if ($this->padding !== null && isset($input[0])) {
                $request->setPost($this->padding, $input);
            } else {
                foreach ((array) $input as $key => $value) {
                    if ($value !== null) {
                        $request->setPost($key, $value);
                    }
                }
            }
        }

        // Parse the json Params
        if (count($this->parseParams)) {
            foreach ($this->parseParams as $Param) {
                if (($value = $request->getParam($Param)) !== null) {
                    $value = Zend_Json::decode($value);
                    $request->setParam($Param, $value);
                }
            }
        }

        // Rests the configuration for the next dispatch
        $this->parseInput = false;
        $this->parseParams = [];
        $this->padding = null;
    }

    /**
     * Enables parsing the json input in pre-dispatch.
     * Reads the data from PHP-stream "php://input".
     *
     * @param bool $parseInput
     *
     * @return Enlight_Controller_Plugins_JsonRequest_Bootstrap
     */
    public function setParseInput($parseInput = true)
    {
        $this->parseInput = (bool) $parseInput;

        return $this;
    }

    /**
     * Enables parsing the json params in pre-dispatch.
     * Reads the data from post or get params.
     *
     * @param array $parseParams
     *
     * @return Enlight_Controller_Plugins_JsonRequest_Bootstrap
     */
    public function setParseParams($parseParams = [])
    {
        $this->parseParams = (array) $parseParams;

        return $this;
    }

    /*
    * @param bool $padding
    * @return Enlight_Controller_Plugins_Json_Bootstrap
    */
    public function setPadding($padding = true)
    {
        $this->padding = $padding;

        return $this;
    }

    /**
     * Returns the Value set by setPadding()
     *
     * @return string
     */
    public function getPadding()
    {
        return $this->padding;
    }
}
