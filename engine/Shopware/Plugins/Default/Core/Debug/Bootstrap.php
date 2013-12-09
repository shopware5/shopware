<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * Debug extension to support various writer.
 *
 * The log extension sets the log resource available.
 * It supports various writer, for example firebug, database tables and log files.
 * In additionally the Enlight_Extensions_Log_Bootstrap support to log the ip and the user agents.
 */
class Shopware_Plugins_Core_Debug_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Enlight_Components_Log Contains an instance of the Enlight_Components_Log
     */
    protected $log;

	/**
	 * Plugin install method
     * @return bool
     */
	public function install()
	{
        //todo@hl Change to Enlight_Controller_Front_RouteStartup event. Add block ip support
		$this->subscribeEvent(
			'Enlight_Controller_Front_StartDispatch',
			'onStartDispatch'
		);

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(array('name' => 'Core'));
        $form->setParent($parent);
        $form->setElement('text', 'AllowIP', array('label' => 'Auf IP beschränken', 'value' => ''));

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
	    // Check for ip-address
        if (!empty($_SERVER["REMOTE_ADDR"])
          && !empty($this->Config()->AllowIP)
          && strpos($this->Config()->AllowIP, $_SERVER["REMOTE_ADDR"])===false){
            return;
        }

        if($this->Log() === null){
            return;
        }

        if(!empty($_SERVER['HTTP_USER_AGENT'])
          && strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP/')!==false) {
            $writer = new Zend_Log_Writer_Firebug();
            $this->Log()->addWriter($writer);
        }

        /** @var $errorHandler Enlight_Extensions_ErrorHandler_Bootstrap  */
        $errorHandler = $this->Collection()->ErrorHandler();
        $errorHandler->setEnabledLog(true);
        $errorHandler->registerErrorHandler(E_ALL | E_STRICT);

        $event = new Enlight_Event_EventHandler(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            array($this, 'onDispatchLoopShutdown')
        );
        Shopware()->Events()->registerListener($event);

        $event = new Enlight_Event_EventHandler(
            'Enlight_Plugins_ViewRenderer_PreRender',
            array($this, 'onAfterRenderView')
        );
        Shopware()->Events()->registerListener($event);
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
            $rows[] = $this->encode(array_values($error));
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
        $template_name = $this->encode($template_name, 30);

        $template_vars = (array) $template->getTemplateVars();
        unset($template_vars['smarty']);
        if (!empty($template_vars)) {
            $rows = array(array('spec', 'value'));
            foreach ($template_vars as $template_spec => $template_var) {
                $template_var = $this->encode($template_var);
                $rows[] = array($template_spec, $template_var);
            }
            $table = array('Template Vars > ' . $template_name . ' (' . (count($template_vars)) . ')', $rows);
            try {
                $this->Log()->table($table);
            } catch(Exception $e) {
                die((string) $e);
            }
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
            $rows[] = $this->encode(array(
                $exception->getCode(),
                get_class($exception),
                $exception->getMessage(),
                $exception->getLine(),
                $exception->getFile(),
                explode("\n", $exception->getTraceAsString())
            ));
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
     * @param   int $length
     * @return  array|string
     */
    public function encode($data, $length = 250)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $data[$this->encode($key)] = $this->encode($value);
            }
        } elseif (is_string($data)) {
            if (strlen($data) > $length) {
                $data = substr($data, 0, $length - 3) . '...';
            }
            //$data = utf8_encode($data);
        } elseif ($data instanceof ArrayObject) {
            /** @var $data ArrayObject */
            $data = $this->encode($data->getArrayCopy());
        } elseif ($data instanceof Zend_Config) {
            /** @var $data Zend_Config */
            $data = $this->encode($data->toArray());
        } elseif (method_exists($data, '__toArray') || $data instanceof stdClass) {
            $data = $this->encode((array) $data);
        } elseif(is_object($data)) {
            $data = $data instanceof Enlight_Hook_Proxy ? get_parent_class($data) : get_class($data);
        } else {
            $data = (string) $data;
        }
        return $data;
    }
}
