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
 * @subpackage InputFilter
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware InputFilter Plugin
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Frontend_InputFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public $sql_regex = 's_core_|s_order_|benchmark.*\(|insert.+into|update.+set|(?:delete|select).+from|drop.+(?:table|database)|truncate.+table|union.+select';
    public $xss_regex = 'javascript:|src\s*=|on[a-z]+\s*=|style\s*=';
    public $rfi_regex = '\.\./|\\0|2\.2250738585072011e-308';

    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_RouteShutdown',
            'onRouteShutdown',
            -100
        );

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(array('name' => 'Core'));
        $form->setParent($parent);
        $form->setElement('checkbox', 'sql_protection', array('label' => 'SQL-Injection-Schutz aktivieren', 'value' => true));
        $form->setElement('textarea', 'sql_regex', array('label' => 'SQL-Injection-Filter', 'value' => $this->sql_regex));
        $form->setElement('checkbox', 'xss_protection', array('label' => 'XSS-Schutz aktivieren', 'value' => true));
        $form->setElement('textarea', 'xss_regex', array('label' => 'XSS-Filter', 'value' => $this->xss_regex));
        $form->setElement('checkbox', 'rfi_protection', array('label' => 'RemoteFileInclusion-Schutz aktivieren', 'value' => true));
        $form->setElement('textarea', 'rfi_regex', array('label' => 'RemoteFileInclusion-Filter', 'value' => $this->rfi_regex));

        return true;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onRouteShutdown(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();
        if ($request->getModuleName() == 'backend') {
            return;
        }

        $intVars = array('sCategory', 'sContent', 'sCustom');
        foreach ($intVars as $parameter) {
            if (!empty($_GET[$parameter])) {
                $_GET[$parameter] = (int)$_GET[$parameter];
            }
            if (!empty($_POST[$parameter])) {
                $_POST[$parameter] = (int)$_POST[$parameter];
            }
        }

        $config = $this->Config();

        $regex = array();
        if (!empty($config->sql_protection) && !empty($config->sql_regex)) {
            $regex[] = $config->sql_regex;
        }
        if (!empty($config->xss_protection) && !empty($config->xss_regex)) {
            $regex[] = $config->xss_regex;
        }
        if (!empty($config->rfi_protection) && !empty($config->rfi_regex)) {
            $regex[] = $config->rfi_regex;
        }

        if (empty($regex)) {
            return;
        }

        $regex = '#' . implode('|', $regex) . '#msi';

        $userParams = $request->getUserParams();
        $process = array(
            &$_GET, &$_POST, &$_COOKIE, &$_REQUEST, &$_SERVER, &$userParams
        );
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][self::filterValue($k, $regex)] = $v;
                    $process[] = &$process[$key][self::filterValue($k, $regex)];
                } else {
                    $process[$key][self::filterValue($k, $regex)] = self::filterValue($v, $regex);
                }
            }
        }
        unset($process);
        $request->setParams($userParams);
    }

    /**
     * Filter value by regex
     *
     * @param string $value
     * @param string $regex
     * @return string
     */
    public static function filterValue($value, $regex)
    {
        if (!empty($value)) {
            $value = strip_tags($value);
            if (preg_match($regex, $value)) {
                $value = null;
            }
        }
        return $value;
    }

    /**
     * Returns plugin capabilities
     *
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => true,
            'update' => true
        );
    }
}
