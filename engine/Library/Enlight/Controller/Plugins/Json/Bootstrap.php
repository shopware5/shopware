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
 * Controller of the enlight json plugin
 *
 * The Enlight_Controller_Plugins_Json_Bootstrap is a default plugin to pass the view data as json.
 * It supports the rendering of the view as json object and capsules the output to an javascript function
 * known as jsonp.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_Plugins_Json_Bootstrap extends Enlight_Plugin_Bootstrap_Default
{
    /**
     * Source encoding needed to convert to UTF-8
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Flag which indicates if the whole HTML output should be converted to JSON or
     * just the data provided by smarties data container
     *
     * @var bool
     */
    protected $renderer;

    /**
     * Should the JSON object be encapsulated into a javascript function
     *
     * @var string
     */
    protected $padding;

    /**
     * Should format date time object to json.
     *
     * @var bool
     */
    protected $formatDateTime = true;

    /**
     * Initializes this plugin. This Plugin should run after the ViewRenderer Plugin
     */
    public function init()
    {
        if ($this->Collection() === null) {
            return;
        }
        $event = new Enlight_Event_Handler_Default(
            'Enlight_Controller_Action_PostDispatch',
            [$this, 'onPostDispatch'],
            500
        );
        $this->Application()->Events()->registerListener($event);
    }

    /**
     * Called from the Event Manager after the dispatch process
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @return bool
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var $controller Enlight_Controller_Action $controller */
        $subject = $args->get('subject');
        $response = $subject->Response();
        $request = $subject->Request();

        if (!$request->isDispatched()) {
            return;
        }

        // If the attribute padding is a boolean true
        if ($this->padding === true) {
            $this->padding = $request->getParam('callback');
            $this->padding = preg_replace('#[^0-9a-z_]+#i', '', (string) $this->padding);
        }

        // decide if we should render the data or the whole page
        if ($this->renderer === true) {
            $content = $subject->View()->getAssign();
        } elseif (!empty($this->padding)) {
            $content = $response->getBody();
        } else {
            return;
        }

        // Convert content to json
        $content = $this->convertToJson($content);

        if (!empty($this->padding)) {
            $response->setHeader('Content-type', 'text/javascript', true);
            $response->setBody($this->addPadding($content, $this->padding));
        } elseif ($this->renderer === true) {
            $response->setHeader('Content-type', 'application/json', true);
            $response->setBody($content);
        }

        $this->padding = null;
        $this->encoding = 'UTF-8';
        $this->renderer = null;
    }

    /**
     * Sometimes it is necessary to pad an JSON object into a javascript function. If this behaviour is needed
     * this method can be called with a true value as parameter to enable the padding mode.
     * If this mode is active the system takes the name found in the GET parameter 'callback' as the javascript function
     * name.
     *
     * @param bool $padding
     *
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

    /**
     * The method can be used to determine if the raw output will be transformed to JSON
     * or just the data assigned to the current view.
     *
     * @param bool $renderer
     *
     * @return Enlight_Controller_Plugins_Json_Bootstrap
     */
    public function setRenderer($renderer = true)
    {
        $this->renderer = (bool) $renderer;

        if ($this->renderer === true) {
            /** @var Enlight_Controller_Plugins_ViewRenderer_Bootstrap $viewRenderer */
            $viewRenderer = $this->Collection()->get('ViewRenderer');
            // Disable the default renderer
            $viewRenderer->setNoRender(true);
        }

        return $this;
    }

    /**
     * Returns the boolean field set by setRenderer
     *
     * @return bool
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Sets the source encoding used to convert the current data to UTF-8
     *
     * @param string $encoding
     *
     * @return \Enlight_Controller_Plugins_Json_Bootstrap
     */
    public function setEncoding($encoding)
    {
        $this->encoding = (string) $encoding;

        return $this;
    }

    /**
     * Returns the encoding which has been set with setEncoding()
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Converts data to json
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function convertToJson($data)
    {
        if ($this->encoding !== 'UTF-8') {
            $this->convertToUtf8($data, $this->encoding);
        }
        if ($this->formatDateTime === true && is_array($data)) {
            array_walk_recursive($data, [$this, 'convertDateTime']);
            $data = Zend_Json::encode($data);
            $data = preg_replace('/"Date\((-?\d+)\)"/', 'new Date($1)', $data);
        } else {
            $data = Zend_Json::encode($data);
        }

        return $data;
    }

    /**
     * Converts date time objects
     *
     * @param DateTime $value
     * @param mixed    $key
     */
    protected static function convertDateTime(&$value, $key)
    {
        if ($value instanceof DateTime) {
            $value = 'Date(' . (int) $value->getTimestamp() . '000)';
        }
    }

    /**
     * Converts a non UTF-8 string into an UTF-8 string
     *
     * @param string|array $data
     * @param string       $encoding
     *
     * @return string|array
     */
    protected function convertToUtf8($data, $encoding)
    {
        if (is_string($data)) {
            $data = mb_convert_encoding($data, 'UTF-8', $encoding);
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$this->convertToUtf8($key, $encoding)] = $this->convertToUtf8($value, $encoding);
            }
        }

        return $data;
    }

    /**
     * Embedded a JSON object into a callback function
     *
     * @param string $data
     * @param string $callback
     *
     * @return string
     */
    protected function addPadding($data, $callback)
    {
        return $callback . '(' . $data . ');';
    }
}
