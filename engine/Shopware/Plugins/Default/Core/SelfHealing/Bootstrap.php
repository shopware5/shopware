<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */


class Shopware_Plugins_Core_SelfHealing_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Constant to control a re dispatch
     */
    const RE_DISPATCH_COMMAND = 1;

    /**
     * Contains the original error handler
     * @var callback
     */
    protected $_origErrorHandler = null;

    /**
     * Flag if an error handler registered
     * @var boolean
     */
    protected $_registeredErrorHandler = false;

    /**
     * Class property which contains the enlight http response object
     * @var Enlight_Controller_Response_ResponseHttp
     */
    protected $response = null;

    /**
     * Class property which contains the enlight http request object
     * @var Enlight_Controller_Request_RequestHttp
     */
    protected $request = null;

    /**
     * Class property which contains the current Enlight Front Controller
     * @var Enlight_Controller_Front
     */
    protected $subject = null;

    static public $isDone;

    /**
     * Standard plugin install function.
     * Creates an event listener for the follwing events:
     * <pre>
     *      Enlight_Controller_Front_PostDispatch
     *          ==>
     *      Enlight_Controller_Front_StartDispatch
     *          ==>
     * </pre>
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_PostDispatch',
            'onPostDispatch',
            -9999
        );

        $this->subscribeEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch',
            -9999
        );

        return true;
    }

    /**
     * Enligh event listener function of the Enlight_Controller_Front_StartDispatch event.
     * Fired when the Enlight_Controller_Front start the dispatch process.
     * This event listener is used to register the own error handler.
     * The error handler capture all errors (NOT EXCEPTIONS!) and try to fix this errors.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch($args)
    {
        $this->subject =  $args->getSubject();
        $this->request = new Enlight_Controller_Request_RequestHttp();
        $this->response = new Enlight_Controller_Response_ResponseHttp();
        $this->registerErrorHandler(E_ALL | E_STRICT);
    }

    /**
     * The registerErrorHandler function is the callback function of the new error handler.
     * This function routs the php error to our own error handler function named "errorHandler"
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param int $errorLevel
     * @return \Shopware_Plugins_Core_SelfHealing_Bootstrap
     */
    public function registerErrorHandler($errorLevel = E_ALL)
    {
        // Only register once.  Avoids loop issues if it gets registered twice.
        if ($this->_registeredErrorHandler) {
            return $this;
        }

        $this->_origErrorHandler = set_error_handler(array($this, 'errorHandler'), $errorLevel);

        $this->_registeredErrorHandler = true;

        return $this;
    }

    /**
     * The errorHandler function is the capture function of all php errors.
     * This function validates the different error messages and tries to fix them.
     * For example:
     * If the key word "__CG__ShopwareModels" is set, the function regenerates all model proxies
     * and redirect to the last request url.
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @param array  $errcontext
     *
     * @throws Exception
     * @return boolean
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (strpos($errstr, '__CG__ShopwareModels')) {
            try {
                Shopware()->Models()->regenerateProxies();
            }
            catch (Exception $e) {
                if (strpos($e->getMessage(), 'Shopware\Models\Attribute') !== false) {
                    try {
                        $this->generateModels();
                    } catch (Exception $e) {
                        throw $e;
                    }
                    try {
                        Shopware()->Models()->regenerateProxies();
                    } catch (Exception $e) {
                        throw $e;
                    }
                }
            }

            if ($this->request && $this->response) {
                $this->response->setRedirect(
                    $this->request->getRequestUri()
                );
                $this->response->sendResponse();
            }
        } else if ($this->_origErrorHandler !== null) {
            return call_user_func($this->_origErrorHandler, $errno, $errstr, $errfile, $errline, $errcontext);
        }

        return true;
    }

    /**
     * The onPostDispatch function is an enlight event listener function of the Enlight_Controller_Front_PostDispatch
     * event.
     * This event is used to capture all shopware exceptions.
     * The function captures the different exceptions and tries to fix them.
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onPostDispatch(Enlight_Event_EventArgs $arguments)
    {
        /**@var $response Enlight_Controller_Response_ResponseHttp*/
        $response = $arguments->getResponse();

        if ($response->isException()) {
            $exceptions = $response->getException();

            $results = array();
            /**@var $exception Exception */
            foreach($exceptions as $exception) {

                $results[] = $this->exceptionHandling($exception);
            }

            foreach($results as $result) {
                if ($result === self::RE_DISPATCH_COMMAND) {
                    $this->response->setRedirect(
                        $this->request->getRequestUri()
                    );
                    $this->response->sendResponse();
                    $this->response->sendHeaders();
                }
            }
        }
    }

    /**
     * Internal helper function which handles a single exception.
     * This function tries to fix the passed exception and returns the following command action.
     * For example:
     *  If the exception message contains the key word "Shopware\Models\Attribute" the function
     *  regenerate all shopware attribute models and returns the RE_DISPATCH_COMMAND constant
     *  which is the command to redirect to the last request url.
     *
     *
     * @param $exception Exception
     * @return int
     */
    private function exceptionHandling($exception)
    {
        if (strpos($exception->getMessage(), 'Shopware\Models\Attribute') !== false) {
            if (strpos($exception->getMessage(), 'does not exist') !== false ||
                strpos($exception->getMessage(), 'cannot be found') !== false) {

                $this->generateModels();

                return self::RE_DISPATCH_COMMAND;
            }
        }
    }

    /**
     * Internal helper function which regenerates all shopware attribute model.
     */
    private function generateModels()
    {
        /**@var $generator \Shopware\Components\Model\Generator*/
        $generator = new \Shopware\Components\Model\Generator();

        $generator->setPath(
            Shopware()->AppPath('Models_Attribute')
        );

        $generator->setModelPath(
            Shopware()->AppPath('Models')
        );

        $generator->setSchemaManager(
            $this->getSchemaManager()
        );

        $generator->generateAttributeModels(array());
    }

    /**
     * Helper function to create an own database schema manager to remove
     * all dependencies to the existing shopware models and meta data caches.
     * @return Doctrine\DBAL\Doctrine\DBAL\Connection
     */
    private function getSchemaManager()
    {
        /**@var $connection \Doctrine\DBAL\Connection*/
        $connection = \Doctrine\DBAL\DriverManager::getConnection(
            array('pdo' => Shopware()->Db()->getConnection())
        );
        return $connection->getSchemaManager();
    }

}

