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
 * @ignore  Do not show in PHPDoc.
 * @package KlarnaAPI
 */

/**
 * ThreatMetrix is a fraud prevention and device identification software.
 *
 * @ignore    Do not show in PHPDoc.
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
class ThreatMetrix extends CheckoutHTML {

    /**
     * The ID used in conjunction with the Klarna API.
     *
     * @var int
     */
    const ID = 'dev_id_1';

    /**
     * ThreatMetrix organizational ID.
     *
     * @var string
     */
    protected $orgID = 'qicrzsu4';

    /**
     * Session ID for the client.
     *
     * @var string
     */
    protected $sessionID;

    /**
     * Hostname used to access ThreatMetrix.
     *
     * @var string
     */
    protected $host = 'h.online-metrix.net';

    /**
     * Protocol used to access ThreatMetrix.
     *
     * @var string
     */
    protected $proto = 'https';

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
     * @see CheckoutHTML::toHTML()
     * @param  Klarna   $klarna  The API instance
     * @param  int      $eid
     * @return void
     */
    public function init($klarna, $eid) {
        if(!is_int($eid)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': eid is not an integer!');
        }
        if(isset($_SESSION)) {
            if(!isset($_SESSION[self::ID]) || strlen($_SESSION[self::ID]) < 40) {
                $_SESSION[self::ID] = parent::getSessionID($eid);
                $this->sessionID = $_SESSION[self::ID];
            }
            else {
                $this->sessionID = $_SESSION[self::ID];
            }
        }
        else {
            $this->sessionID = parent::getSessionID($eid);
        }

        $klarna->setSessionID(self::ID, $this->sessionID);
    }

    /**
     * @see CheckoutHTML::clear()
     * @return void
     */
    public function clear() {
        if(isset($_SESSION) && isset($_SESSION[self::ID])) {
            $_SESSION[self::ID] = null;
            unset($_SESSION[self::ID]);
        }
    }

    /**
     * @see CheckoutHTML::toHTML()
     */
    public function toHTML() {
        return "<p style=\"display: none; background:url($this->proto://$this->host/fp/clear.png?org_id=$this->orgID&session_id=$this->sessionID&m=1)\"></p>
        <script src=\"$this->proto://$this->host/fp/check.js?org_id=$this->orgID&session_id=$this->sessionID\" type=\"text/javascript\"></script>
        <img src=\"$this->proto://$this->host/fp/clear.png?org_id=$this->orgID&session_id=$this->sessionID&m=2\" alt=\"\" >
        <object type=\"application/x-shockwave-flash\" style=\"display: none\" data=\"$this->proto://$this->host/fp/fp.swf?org_id=$this->orgID&session_id=$this->sessionID\" width=\"1\" height=\"1\" id=\"obj_id\">
            <param name=\"movie\" value=\"$this->proto://$this->host/fp/fp.swf?org_id=$this->orgID&session_id=$this->sessionID\" />
            <div></div>
        </object>";
    }
}
