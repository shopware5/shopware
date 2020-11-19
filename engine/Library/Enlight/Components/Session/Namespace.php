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

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Enlight session namespace component.
 *
 * The Enlight_Components_Session_Namespace extends the Symfony Session with an easy array access.
 *
 *
 * @license     http://enlight.de/license     New BSD License
 */
class Enlight_Components_Session_Namespace extends Session implements ArrayAccess
{
    /**
     * Legacy wrapper
     *
     * @param string $name
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Legacy wrapper
     *
     * @param string $name
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Legacy wrapper
     *
     * @param string $name
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }

    /**
     * Legacy wrapper
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        if (!$this->has($name)) {
            return false;
        }

        if ($this->get($name) === null) {
            return false;
        }

        return true;
    }

    /**
     * Whether an offset exists
     *
     * @param mixed $key a key to check for
     *
     * @return bool returns true on success or false on failure
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Unset the given offset.
     *
     * @param string $key key to unset
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $key the offset to retrieve
     *
     * @return mixed can return all value types
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Offset to set
     *
     * @param mixed $key   the offset to assign the value to
     * @param mixed $value the value to set
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Clear session
     *
     * @deprecated since 5.7, and will be removed with 5.9. Use clear instead.
     */
    public function unsetAll()
    {
        trigger_error('Enlight_Components_Session_Namespace::unsetAll is deprecated since 5.7 and will be removed with 5.9. Use Enlight_Components_Session_Namespace::clear instead', E_USER_DEPRECATED);
        return $this->clear();
    }

    public function clear()
    {
        parent::clear();
        $this->set('sessionId', $this->getId());
    }

    public function start()
    {
        // Generally `start()` is called by Shopware's session factories
        // - Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceBackendSession()
        // - Shopware\Components\DependencyInjection\Bridge\Session::createSession()
        // In case the session is started somewhere else we need to ensure no other session is active. The same logic as
        // in Shopware's factories is used here.
        // This behaviour is analogue to Zend: https://github.com/shopware/shopware/blob/cbc212ca4642878cac62193d3a2f41e08f4849a2/engine/Library/Zend/Session.php#L413
        $container = Shopware()->Container();
        self::ensureFrontendSessionClosed($container);
        self::ensureBackendSessionClosed($container);

        // When the session cookie is present but no session id is currently set, set its value as session id as
        // otherwise a new session id is generated when a session was active before. This is required to resume a
        // previously active session after switching back from another session. This behaviour is analogue to Zend:
        // https://github.com/shopware/shopware/blob/cbc212ca4642878cac62193d3a2f41e08f4849a2/engine/Library/Zend/Session.php#L421-L423
        // The session id can be empty on first start or when it has been set to an empty string, for example by
        // `ensureSessionClosed()`.
        if (!$this->getId() && ini_get('session.use_cookies') === '1' && !empty($_COOKIE[$this->getName()])) {
            $this->setId($_COOKIE[$this->getName()]);
        }

        return parent::start();
    }

    public static function ensureFrontendSessionClosed($container)
    {
        self::ensureSessionClosed($container, 'session');
    }

    public static function ensureBackendSessionClosed($container)
    {
        self::ensureSessionClosed($container, 'backendsession');
    }

    /**
     * Saves the session and ensures it can be reused.
     *
     * This method can be used to ensure other sessions are closed before starting a new session, because the other
     * sessions would use the session id of the new session and thus write their data into the wrong session.
     *
     * @param $container
     * @param $sessionServiceName
     */
    private static function ensureSessionClosed($container, $sessionServiceName)
    {
        $session = $container->initialized($sessionServiceName) ? $container->get($sessionServiceName) : null;
        if ($session && $session->isStarted()) {
            $session->save();
            // The empty session id signals that upon starting a session the session cookie is used.
            $session->setId('');
        }
    }
}
