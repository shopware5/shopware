<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use \Shopware\Components\Session\SessionInterface;

class Shopware_Components_Auth_Storage_Session implements Zend_Auth_Storage_Interface
{
    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'Auth';

    /**
     * Object to proxy $_SESSION storage
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $member;

    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param SessionInterface $session
     * @param  mixed $member
     */
    public function __construct(SessionInterface $session, $member = self::MEMBER_DEFAULT)
    {
        $this->session = $session;
        $this->member = $member;
    }

    /**
     * Returns the name of the session object member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return !$this->session->has($this->member);
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return mixed
     */
    public function read()
    {
        return $this->session->get($this->member);
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->session->set($this->member, $contents);
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return void
     */
    public function clear()
    {
        $this->session->remove($this->member);
    }
}
