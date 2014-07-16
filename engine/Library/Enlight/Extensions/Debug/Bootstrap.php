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
 * @package    Enlight_Extensions
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Enlight debug extension to log exceptions and display the logged exceptions.
 *
 * The Enlight_Extensions_Debug_Bootstrap allows to debug the enlight application.
 * It logs the exceptions into the log and allows to output them into the console or write them into the database
 * or into log files.
 *
 * @category   Enlight
 * @package    Enlight_Extensions
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Extensions_Debug_Bootstrap extends Enlight_Plugin_Bootstrap_Config
{
    /**
     * @var Enlight_Components_Log Contains an instance of the Enlight_Components_Log
     */
    protected $log;

    /**
     * Plugin install method.
     * Subscribes the Enlight_Controller_Front_StartDispatch event to register the error handler,
     * the Enlight_Controller_Front_DispatchLoopShutdown event to log the error handler and the exception,
     * the Enlight_Plugins_ViewRenderer_PostRender event to log the template.
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            'onDispatchLoopShutdown'
        );
        $this->subscribeEvent(
            'Enlight_Plugins_ViewRenderer_PostRender',
            'onAfterRenderView'
        );

        return true;
    }

    /**
     * Setter method for the log property. If no log are given the log resource of the
     * Enlight_Plugin_Namespace will be used.
     *
     * @param Enlight_Components_Log|Zend_Log $log
     */
    public function setLog(Zend_Log $log = null)
    {
        if ($log === null) {
            $log = $this->Collection()->Log()->Resource();
        }
        $this->log = $log;
    }

    /**
     * Getter method of the log property. If the log isn't set the log property will be initial over the
     * setLog method, which will use the log resource of the Enlight_Plugin_Namespace
     *
     * @return Enlight_Components_Log
     */
    public function Log()
    {
        if ($this->log === null) {
            $this->setLog();
        }
        return $this->log;
    }
    /**
     * Listener method of the Enlight_Controller_Front_StartDispatch event.
     * Registers the error handler of the Enlight_Plugin_Namespace.
     *
     * @param   Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        /*
        $request = $args->getSubject()->Request();

        if ($request->getClientIp(false)
          && !empty($config->allowIp)
          && strpos($config->allowIp, $request->getClientIp(false))===false){
            return;
        }
        */

        /** @var $errorHandler Enlight_Extensions_ErrorHandler_Bootstrap  */
        $errorHandler = $this->Collection()->ErrorHandler();
        $errorHandler->setEnabledLog(true);
        $errorHandler->registerErrorHandler(E_ALL | E_STRICT);
    }

    /**
     * Listener method of the Enlight_Plugins_ViewRenderer_PostRender event.
     * Logs the template of the given Enlight_Event_EventArgs.
     *
     * @param   Enlight_Event_EventArgs $args
     */
    public function onAfterRenderView(Enlight_Event_EventArgs $args)
    {
        $template = $args->getTemplate();
        $this->logTemplate($template);
    }

    /**
     * Listener method of the Enlight_Controller_Front_DispatchLoopShutdown event.
     * Logs the error handler and the response of the Enlight_Event_EventArgs.
     *
     * @param   Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
    {
        if (!$this->Log()) {
            return;
        }

        //$template = $this->Application()->Template();
        //$this->logTemplate($template);

        $errorHandler = $this->Collection()->ErrorHandler();
        $this->logError($errorHandler);

        $response = $args->getSubject()->Response();
        $this->logException($response);
    }

    /**
     * Iterate all logged errors of the given error handler and write them
     * into the internal log object.
     *
     * @param   Enlight_Extensions_ErrorHandler_Bootstrap $errorHandler
     */
    public function logError($errorHandler)
    {
        $errors = $errorHandler->getErrorLog();
        if (empty($errors)) {
            return;
        }

        $counts = array();
        foreach ($errors as $errorKey => $error) {
            $counts[$errorKey] = $error['count'];
        }
        array_multisort($counts, SORT_NUMERIC, SORT_DESC, $errors);

        $rows = array();
        foreach ($errors as $error) {
            if (!$rows) {
                $rows[] = array_keys($error);
            }
            $rows[] = array_values($error);
        }
        $table = array('Error Log (' . count($errors) . ')', $rows);

        $this->Log()->table($table);
    }

    /**
     * Iterate all template and config variables of the given template object and write them
     * into the internal log object.
     *
     * @param   Enlight_Template_Default|Enlight_Template_Manager $template
     */
    public function logTemplate($template)
    {
        $template_name = isset($template->template_resource) ? $template->template_resource : 'Global';
        $template_name = $this->encode($template_name);

        $template_vars = (array) $template->getTemplateVars();
        unset($template_vars['smarty']);
        if (!empty($template_vars)) {
            $rows = array(array('spec', 'value'));
            foreach ($template_vars as $template_spec => $template_var) {
                $template_var = $this->encode($template_var);
                $rows[] = array($template_spec, $template_var);
            }
            $table = array('Template Vars > ' . $template_name . ' (' . (count($template_vars)) . ')', $rows);

            $this->Log()->table($table);
        }

        $config_vars = (array) $template->getConfigVars();
        if (!empty($config_vars)) {
            $rows = array(array('spec', 'value'));
            foreach ($config_vars as $config_spec => $config_var) {
                $rows[] = array($config_spec, $config_var);
            }
            $table = array('Config Vars > ' . $template_name . ' (' . (count($config_vars)) . ')', $rows);
            $this->Log()->table($table);
        }
    }

    /**
     * Iterate all exceptions of the given response object and write them into internal log object.
     *
     * @param   Enlight_Controller_Response_ResponseHttp $response
     */
    public function logException($response)
    {
        $exceptions = $response->getException();
        if (empty($exceptions)) {
            return;
        }

        $rows = array(array('code', 'name', 'message', 'line', 'file', 'trace'));
        /** @var $exception Exception */
        foreach ($exceptions as $exception) {
            $rows[] = array(
                $exception->getCode(),
                get_class($exception),
                $exception->getMessage(),
                $exception->getLine(),
                $exception->getFile(),
                explode("\n", $exception->getTraceAsString())
            );
        }
        $table = array('Exception Log (' . count($exceptions) . ')', $rows);
        $this->log->table($table);

        foreach ($exceptions as $exception) {
            $this->log->err((string) $exception);
        }
    }

    /**
     * Encode data method
     *
     * @param   $data
     * @return  array|string
     */
    public function encode($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $data[$this->encode($key)] = $this->encode($value);
            }
        } elseif (is_string($data)) {
            if (strlen($data) > 250) {
                $data = substr($data, 0, 250) . '...';
            }
            $data = utf8_encode($data);
        } elseif ($data instanceof ArrayObject) {
            /** @var $data ArrayObject */
            $data = $this->encode($data->getArrayCopy());
        } elseif ($data instanceof Zend_Config) {
            /** @var $data Zend_Config */
            $data = $this->encode($data->toArray());
        } elseif (is_object($data)) {
            $data = get_class($data);
        }
        return $data;
    }
}
