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
 * KlarnaPClass Storage interface
 *
 * This class provides an interface with which to save the PClasses easily.
 *
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
abstract class PCStorage {

    /**
     * An array of KlarnaPClasses.
     *
     * @ignore Do not show in PHPDoc.
     * @var array
     */
    protected $pclasses;

    /**
     * Adds a PClass to the storage.
     *
     * @param KlarnaPClass $pclass PClass object.
     * @throws KlarnaException
     * @return void
     */
    public function addPClass($pclass) {
        if($pclass instanceof KlarnaPClass) {
            if(!isset($this->pclasses) || !is_array($this->pclasses)) {
                $this->pclasses = array();
            }
            if($pclass->getDescription() === null || $pclass->getType() === null) {
                //Something went wrong, do not save these!
                return;
            }
            if(!isset($this->pclasses[$pclass->getEid()])) {
                $this->pclasses[$pclass->getEid()] = array();
            }
            $this->pclasses[$pclass->getEid()][$pclass->getId()] = $pclass;
        }
        else {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Supplied pclass object is not an KlarnaPClass instance!');
        }
    }

    /**
     * Gets the PClass by ID.
     *
     * @param  int  $id       PClass ID.
     * @param  int  $eid      Merchant ID.
     * @param  int  $country  {@link KlarnaCountry Country} constant.
     * @throws KlarnaException
     * @return KlarnaPClass
     */
    public function getPClass($id, $eid, $country) {
        if(!is_int($id)) {
            throw new Exception('Supplied ID is not an integer!');
        }

        if(!is_array($this->pclasses)) {
            throw new Exception('No match for that eid!');
        }

        if(!isset($this->pclasses[$eid]) || !is_array($this->pclasses[$eid])) {
            throw new Exception('No match for that eid!');
        }

        if(!isset($this->pclasses[$eid][$id]) || !$this->pclasses[$eid][$id]->isValid()) {
            throw new Exception('No such pclass available!');
        }

        if($this->pclasses[$eid][$id]->getCountry() !== $country) {
            throw new Exception('You cannot use this pclass with set country!');
        }

        return $this->pclasses[$eid][$id];
    }

    /**
     * Returns an array of KlarnaPClasses, keyed with pclass ID.
     * If type is specified, only that type will be returned.
     *
     * <b>Types available</b>:<br>
     * {@link KlarnaPClass::ACCOUNT}<br>
     * {@link KlarnaPClass::CAMPAIGN}<br>
     * {@link KlarnaPClass::SPECIAL}<br>
     * {@link KlarnaPClass::DELAY}<br>
     * {@link KlarnaPClass::MOBILE}<br>
     *
     * @param  int   $eid     Merchant ID.
     * @param  int   $country {@link KlarnaCountry Country} constant.
     * @param  int   $type    PClass type identifier.
     * @throws KlarnaException
     * @return array An array of {@link KlarnaPClass PClasses}.
     */
    public function getPClasses($eid, $country, $type = null) {
        if(!is_int($country)) {
            throw new Exception('You need to specify a country!');
        }

        $tmp = false;
        if(is_array($this->pclasses)) {
            $tmp = array();
            foreach($this->pclasses as $eid => $pclasses) {
                $tmp[$eid] = array();
                foreach($pclasses as $pclass) {
                    if(!$pclass->isValid()) {
                        continue; //Pclass invalid, skip it.
                    }
                    if($pclass->getEid() === $eid && $pclass->getCountry() === $country) {
                        if($pclass->getType() === $type || $type === null) {
                            $tmp[$eid][$pclass->getId()] = $pclass;
                        }
                    }
                }
            }
        }

        return $tmp;
    }

    /**
     * Loads the PClasses and calls {@link self::addPClass()} to store them in runtime.
     * URI can be location to a file, or a db prefixed table.
     *
     * @param  string $uri  URI to stored PClasses.
     * @throws KlarnaException|Exception
     * @return void
     */
    abstract public function load($uri);

    /**
     * Takes the internal PClass array and stores it.
     * URI can be location to a file, or a db prefixed table.
     *
     * @param  string  $uri  URI to stored PClasses.
     * @throws KlarnaException|Exception
     * @return void
     */
    abstract public function save($uri);

    /**
     * Removes the internally stored pclasses.
     *
     * @param  string  $uri  URI to stored PClasses.
     * @throws KlarnaException|Exception
     * @return void
     */
    abstract public function clear($uri);
}
