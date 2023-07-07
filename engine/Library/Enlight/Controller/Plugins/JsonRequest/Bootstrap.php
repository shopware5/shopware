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
     * @var string|null
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
     *
     * @return void
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
     * @return void
     */
    public function onPreDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $subject */
        $subject = $args->get('subject');
        $request = $subject->Request();

        // Parses the json input data, if the content type is correct
        $contentType = $request->getHeader('Content-Type');
        $input = $request->getRawBody();
        if (
            $this->parseInput === true
            && \is_string($contentType)
            && str_starts_with($contentType, 'application/json')
            && \is_string($input)
        ) {
            if ($input !== '') {
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
        if (\count($this->parseParams)) {
            foreach ($this->parseParams as $Param) {
                $value = $request->getParam($Param);
                if ($value !== null) {
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
     * @return $this
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
     * @return $this
     */
    public function setParseParams($parseParams = [])
    {
        $this->parseParams = (array) $parseParams;

        return $this;
    }

    /**
     * @param string $padding
     *
     * @return $this
     */
    public function setPadding($padding = '1')
    {
        $this->padding = $padding;

        return $this;
    }

    /**
     * Returns the Value set by setPadding()
     *
     * @return string|null
     */
    public function getPadding()
    {
        return $this->padding;
    }
}
