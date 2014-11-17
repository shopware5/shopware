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

namespace Shopware\Plugin\Debug\Components;

use Shopware\Components\Logger;

/**
 * @category  Shopware
 * @package   Shopware\Plugin\Debug\Components
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class TemplateVarCollector implements CollectorInterface
{
    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    protected $results = array();

    /**
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(\Enlight_Event_EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return void
     */
    public function start()
    {
        $event = new \Enlight_Event_EventHandler(
            'Enlight_Plugins_ViewRenderer_PreRender',
            array($this, 'onAfterRenderView')
        );
        $this->eventManager->registerListener($event);
    }

    /**
     * @param Logger $log
     * @return mixed
     */
    public function logResults(Logger $log)
    {
        foreach ($this->results as $result) {
            $log->table($result);
        }
    }

    /**
     * Listener method of the Enlight_Plugins_ViewRenderer_PostRender event.
     * Logs the template of the given Enlight_Event_EventArgs.
     *
     * @param   \Enlight_Event_EventArgs $args
     */
    public function onAfterRenderView(\Enlight_Event_EventArgs $args)
    {
        $template = $args->getTemplate();

        $this->logTemplate($template);
    }

    /**
     * Iterate all template and config variables of the given template object and write them
     * into the internal log object.
     *
     * @param \Enlight_Template_Default|\Enlight_Template_Manager $template
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
                $this->results[] = $table;
            } catch (Exception $e) {
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
            $this->results[] = $table;
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
        } elseif ($data instanceof \ArrayObject) {
            /** @var $data \ArrayObject */
            $data = $this->encode($data->getArrayCopy());
        } elseif ($data instanceof \Zend_Config) {
            /** @var $data \Zend_Config */
            $data = $this->encode($data->toArray());
        } elseif (method_exists($data, '__toArray') || $data instanceof \stdClass) {
            $data = $this->encode((array) $data);
        } elseif (is_object($data)) {
            $data = $data instanceof \Enlight_Hook_Proxy ? get_parent_class($data) : get_class($data);
        } else {
            $data = (string) $data;
        }

        return $data;
    }
}
