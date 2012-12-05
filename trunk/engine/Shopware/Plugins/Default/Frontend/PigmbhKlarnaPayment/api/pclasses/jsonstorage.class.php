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
 * Include the {@link PCStorage} interface.
 */
require_once('storage.intf.php');

/**
 * JSON storage class for KlarnaPClass
 *
 * This class is an JSON implementation of the PCStorage interface.
 *
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
class JSONStorage extends PCStorage {

    /**
     * Class constructor
     */
    public function __construct() {
    }

    /**
     * Class destructor
     */
    public function __destruct() {
    }

    /**
     * Checks if the file is writeable, readable or if the directory is.
     *
     * @ignore Do not show in PHPDoc.
     * @param  string $jsonFile
     * @throws error
     * @return void
     */
    protected function checkURI($jsonFile) {
        //If file doesn't exist, check the directory.
        if(!file_exists($jsonFile)) {
            $jsonFile = dirname($jsonFile);
        }

        if(!is_writable($jsonFile)) {
            throw new Exception("Unable to write to $jsonFile!");
        }

        if(!is_readable($jsonFile)) {
            throw new Exception("Unable to read $jsonFile!");
        }
    }

    /**
     * @see PCStorage::clear()
     */
    public function clear($uri) {
        try {
            $this->checkURI($uri);
            unset($this->pclasses);
            if(file_exists($uri)) {
                unlink($uri);
            }
        }
        catch(Exception $e) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage());
        }
    }

    /**
     * @see PCStorage::load()
     */
    public function load($uri) {
        try {
            $this->checkURI($uri);
            if(!file_exists($uri)) {
                //Do not fail, if file doesn't exist.
                return;
            }
            $arr = json_decode(file_get_contents($uri), true);
            if(count($arr) > 0)  {
                foreach($arr as $pclasses) {
                    if(count($pclasses) > 0) {
                        foreach($pclasses as $pclass) {
                            $this->addPClass(new KlarnaPClass($pclass));
                        }
                    }
                }
            }
        }
        catch(Exception $e) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage());
        }
    }

    /**
     * @see PCStorage::save()
     */
    public function save($uri) {
        try {
            $this->checkURI($uri);
            $output = array();
            foreach($this->pclasses as $eid => $pclasses) {
                foreach($pclasses as $pclass) {
                    if(!isset($output[$eid])) {
                        $output[$eid] = array();
                    }
                    $output[$eid][] = $pclass->toArray();
                }
            }
            if(count($this->pclasses) > 0) {
                file_put_contents($uri, json_encode($output));
            }
            else {
                file_put_contents($uri, "");
            }

        }
        catch(Exception $e) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': ' . $e->getMessage());
        }
    }
}
