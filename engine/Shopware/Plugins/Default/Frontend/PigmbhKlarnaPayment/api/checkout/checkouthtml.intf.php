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
 * This interface provides methods to supply checkout page specific HTML.<br>
 * Can be used to insert device identification, fraud prevention,<br>
 * client side validation code into the checkout page.
 *
 * @ignore    Do not show in PHPDoc.
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
abstract class CheckoutHTML {

    /**
     * Creates a session ID used for e.g. client identification and fraud prevention.
     *
     * This method creates a 40 character long integer.
     * The first 30 numbers is microtime + random numbers.
     * The last 10 numbers is the eid zero-padded.
     *
     * All random functions are automatically seeded as of PHP 4.2.0.
     *
     * E.g. for eid 1004 output could be:
     * 1624100001298454658880354228080000001004
     *
     * @param  int     $eid
     * @return string  A integer with a string length of 40.
     */
    public static function getSessionID($eid) {
        $eid = strval($eid);
        while(strlen($eid) < 10) {
            $eid = "0" . $eid; //Zero-pad the eid.
        }

        $sid = str_replace(array(' ', ',', '.'), '', microtime());
        $sid[0] = rand(1,9); //Make sure we always have a non-zero first.

        //microtime + rand = 30 numbers in length
        while(strlen($sid) < 30) {
            //rand is automatically seeded as of PHP 4.2.0
            $sid .= rand(0,9999);
        }
        $sid = substr($sid, 0, 30);
        $sid .= $eid;

        return $sid;
    }

    /**
     * Initializes this object, this method is always called before {@link CheckoutHTML::toHTML()}.
     * This method is used in {@link Klarna::addTransaction()}, {@link Klarna::reserveAmount()} and in {@link Klarna::checkoutHTML()}
     *
     * @param  Klarna   $klarna  The API instance
     * @param  int      $eid
     * @return void
     */
    abstract public function init($klarna, $eid);

    /**
     * This returns the HTML code for this object,
     * which will be used in the checkout page.
     *
     * @return string HTML
     */
    abstract public function toHTML();

    /**
     * This function is used to clear any stored values (in SESSION, COOKIE or similar)
     * which are required to be unique between purchases.
     *
     * @return void
     */
    abstract public function clear();
}
