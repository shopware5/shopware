<?php
/**
 *  Copyright 2010 KLARNA AB. All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without modification, are
 *  permitted provided that the following conditions are met:
 *
 *     1. Redistributions of source code must retain the above copyright notice, this list of
 *        conditions and the following disclaimer.
 *
 *     2. Redistributions in binary form must reproduce the above copyright notice, this list
 *        of conditions and the following disclaimer in the documentation and/or other materials
 *        provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY KLARNA AB "AS IS" AND ANY EXPRESS OR IMPLIED
 *  WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 *  FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KLARNA AB OR
 *  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 *  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 *  ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  The views and conclusions contained in the software and documentation are those of the
 *  authors and should not be interpreted as representing official policies, either expressed
 *  or implied, of KLARNA AB.
 *
 * @package KlarnaAPI
 */

/**
 * Configuration class for the Klarna instance.
 *
 * KlarnaConfig stores added fields in JSON, it also prepends.<br>
 * Loads/saves specified file, or default file, if {@link KlarnaConfig::$store} is set to true.<br>
 *
 * You add settings using the ArrayAccess:<br>
 * $arr['field'] = $val or $arr->offsetSet('field', $val);<br>
 *
 * Available settings are:<br>
 * eid         - Merchant ID (int)
 * secret      - Shared secret (string)
 * country     - Country constant or code  (int|string)
 * language    - Language constant or code (int|string)
 * currency    - Currency constant or code (int|string)
 * mode        - Klarna::BETA or Klarna::LIVE
 * ssl         - Use HTTPS or HTTP. (bool)
 * candice     - Status reporting to Klarna, to detect erroneous integrations, etc. (bool)
 * pcStorage   - Storage module, e.g. 'json'
 * pcURI       - URI to where the PClasses are stored, e.g. '/srv/shop/pclasses.json'
 * xmlrpcDebug - XMLRPC debugging (bool)
 * debug       - Normal debugging (bool)
 *
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
class KlarnaConfig implements ArrayAccess {

    /**
     * An array containing all the options for this config.
     *
     * @ignore Do not show in PHPDoc.
     * @var array
     */
    protected $options;

    /**
     * If set to true, saves the config.
     *
     * @var bool
     */
    public static $store = true;

    /**
     * URI to the config file.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $file;

    /**
     * Class constructor
     *
     * Loads specified file, or default file, if {@link KlarnaConfig::$store} is set to true.
     *
     * @param  string  $file  URI to config file, e.g. ./config.json
     */
    public function __construct($file = null) {
        $this->options = array();
        if($file) {
            $this->file = $file;
            if(is_readable($this->file)) {
                $this->options = json_decode(file_get_contents($this->file), true);
            }
        }
    }

    /**
     * Clears the config.
     *
     * @return void
     */
    public function clear() {
        $this->options = array();
    }

    /**
     * Class destructor
     *
     * Saves specified file, or default file, if {@link KlarnaConfig::$store} is set to true.
     */
    public function __destruct() {
        if(self::$store && $this->file) {
            if((!file_exists($this->file) && is_writable(dirname($this->file))) || is_writable($this->file)) {
                file_put_contents($this->file, json_encode($this->options));
            }
        }
    }

    /**
     * Returns true whether the field exists.
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        if(isset($this->options[$offset])) {
            return true;
        }
        return false;
    }

    /**
     * Used to get the value of a field.
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        if(!$this->offsetExists($offset)) {
            return null;
        }
        return $this->options[$offset];
    }

    /**
     * Used to set a value to a field.
     *
     * @param  mixed $field
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->options[$offset] = $value;
    }

    /**
     * Removes the specified field.
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->options[$offset]);
    }
}
