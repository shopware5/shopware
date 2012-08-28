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
 * XML storage class for KlarnaPClass
 *
 * This class is an XML implementation of the PCStorage interface.
 *
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
class XMLStorage extends PCStorage {

    /**
     * The internal XML document.
     *
     * @ignore Do not show in PHPDoc.
     * @var DOMDocument
     */
    protected $dom;

    /**
     * XML version for the DOM document.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $version = '1.0';

    /**
     * Encoding for the DOM document.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $encoding = 'ISO-8859-1';

    /**
     * Class constructor
     * @ignore Does nothing.
     */
    public function __construct() {
        $this->dom = new DOMDocument($this->version, $this->encoding);
        $this->dom->formatOutput = true;
        $this->dom->preserveWhiteSpace = false;
    }

    /**
     * Checks if the file is writeable, readable or if the directory is.
     *
     * @param  string $xmlFile URI to XML file.
     * @throws Exception
     * @return void
     */
    protected function checkURI($xmlFile) {
        //If file doesn't exist, check the directory.
        if(!file_exists($xmlFile)) {
            $xmlFile = dirname($xmlFile);
        }

        if(!is_writable($xmlFile)) {
            throw new Exception("Unable to write to $xmlFile!");
        }

        if(!is_readable($xmlFile)) {
            throw new Exception("Unable to read $xmlFile!");
        }
    }

    /**
     * Class destructor
     * @ignore Does nothing.
     */
    public function __destruct() {

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
            if(!@$this->dom->load($uri)) {
                throw new Exception("Failed to parse $uri!");
            }

            $xpath = new DOMXpath($this->dom);
            foreach($xpath->query('/klarna/estore') as $estore) {
                $eid = $estore->getAttribute('id');

                foreach($xpath->query('pclass', $estore) as $node) {
                    $pclass = new KlarnaPClass();
                    $pclass->setId($node->getAttribute('pid'));
                    $pclass->setType($node->getAttribute('type'));
                    $pclass->setEid($eid);
                    $pclass->setDescription($xpath->query('description', $node)->item(0)->textContent);
                    $pclass->setMonths($xpath->query('months', $node)->item(0)->textContent);
                    $pclass->setStartFee($xpath->query('startfee', $node)->item(0)->textContent);
                    $pclass->setInvoiceFee($xpath->query('invoicefee', $node)->item(0)->textContent);
                    $pclass->setInterestRate($xpath->query('interestrate', $node)->item(0)->textContent);
                    $pclass->setMinAmount($xpath->query('minamount', $node)->item(0)->textContent);
                    $pclass->setCountry($xpath->query('country', $node)->item(0)->textContent);
                    $pclass->setExpire($xpath->query('expire', $node)->item(0)->textContent);

                    $this->addPClass($pclass);
                }
            }
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " .$e->getMessage());
        }
    }

    /**
     * Creates DOMElement for all fields for specified PClass.
     *
     * @ignore Do not show in PHPDoc.
     * @param  KlarnaPClass $pclass
     * @return array Array of DOMElements.
     */
    protected function createFields($pclass) {
        $fields = array();

        //This is to prevent HTMLEntities to be converted to the real character.
        $fields[] = $this->dom->createElement('description');
        end($fields)->appendChild($this->dom->createTextNode($pclass->getDescription()));
        $fields[] = $this->dom->createElement('months', $pclass->getMonths());
        $fields[] = $this->dom->createElement('startfee', $pclass->getStartFee());
        $fields[] = $this->dom->createElement('invoicefee', $pclass->getInvoiceFee());
        $fields[] = $this->dom->createElement('interestrate', $pclass->getInterestRate());
        $fields[] = $this->dom->createElement('minamount', $pclass->getMinAmount());
        $fields[] = $this->dom->createElement('country', $pclass->getCountry());
        $fields[] = $this->dom->createElement('expire', $pclass->getExpire());

        return $fields;
    }

    /**
     * @see PCStorage::save()
     */
    public function save($uri) {
        try {
            $this->checkURI($uri);

            //Reset DOMDocument.
            if(!$this->dom->loadXML("<?xml version='$this->version' encoding='$this->encoding'?"."><klarna/>")) {
                throw new Exception('Failed to load initial XML.');
            }

            ksort($this->pclasses, SORT_NUMERIC);
            $xpath = new DOMXpath($this->dom);
            foreach($this->pclasses as $eid => $pclasses) {
                $estore = $xpath->query('/klarna/estore[@id="'.$eid.'"]');
                if($estore === false || $estore->length === 0) {
                    //No estore with matching eid, create it.
                    $estore = $this->dom->createElement('estore');
                    $estore->setAttribute('id', $eid);
                    $this->dom->documentElement->appendChild($estore);
                }
                else {
                    $estore = $estore->item(0);
                }

                foreach($pclasses as $pclass) {
                    if($eid != $pclass->getEid()) {
                        continue; //This should never occur.
                    }

                    $pnode = $this->dom->createElement('pclass');

                    foreach($this->createFields($pclass) as $field) {
                        $pnode->appendChild($field);
                    }
                    $pnode->setAttribute('pid', $pclass->getId());
                    $pnode->setAttribute('type', $pclass->getType());

                    $estore->appendChild($pnode);
                }
            }

            if(!$this->dom->save($uri)) {
                throw new Exception('Failed to save XML document!');
            }
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * This uses unlink (delete) to clear the pclasses!
     *
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
            throw new KlarnaException("Error in " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
