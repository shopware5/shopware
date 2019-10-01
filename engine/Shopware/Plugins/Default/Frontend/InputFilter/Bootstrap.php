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

/**
 * Shopware InputFilter Plugin
 */
class Shopware_Plugins_Frontend_InputFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public $sqlRegex = 's_core_|s_order_|s_user|benchmark.*\(|(?:insert|replace).+into|update.+set|(?:delete|select).+from|(?:alter|rename|create|drop|truncate).+(?:database|table|procedure)|union.+select|prepare.+from.+execute|select.+into\s+(outfile|dumpfile)';

    public $xssRegex = 'javascript:|src\s*=|\bon[a-z]+\s*=|style\s*=|\bdata-\w+(?!\.)\b\s?=?';

    public $rfiRegex = '\.\./|\\0';

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
        /** @var \Shopware\Models\Config\Form $parent */
        $parent = $this->Forms()->findOneBy(['name' => 'Core']);
        $form->setParent($parent);

        $form->setElement('boolean', 'sql_protection', ['label' => 'SQL-Injection-Schutz aktivieren', 'value' => true]);
        $form->setElement('boolean', 'xss_protection', ['label' => 'XSS-Schutz aktivieren', 'value' => true]);
        $form->setElement('boolean', 'rfi_protection', ['label' => 'RemoteFileInclusion-Schutz aktivieren', 'value' => true]);
        $form->setElement('boolean', 'strip_tags', ['label' => 'Global strip_tags verwenden', 'value' => true]);
        $form->setElement('textarea', 'own_filter', ['label' => 'Eigener Filter', 'value' => null]);

        return true;
    }

    /**
     * Event listener method
     */
    public function onRouteShutdown(Enlight_Controller_EventArgs $args)
    {
        /** @var Enlight_Controller_Request_RequestHttp $request */
        $request = $args->getRequest();
        $config = $this->Config();

        if ($request->getModuleName() === 'backend' || $request->getModuleName() === 'api') {
            return;
        }

        $stripTagsConf = $config->strip_tags;

        $intVars = ['sCategory', 'sContent', 'sCustom'];
        foreach ($intVars as $parameter) {
            if (!empty($_GET[$parameter])) {
                $_GET[$parameter] = (int) $_GET[$parameter];
            }
            if (!empty($_POST[$parameter])) {
                $_POST[$parameter] = (int) $_POST[$parameter];
            }
        }

        $regex = [];
        if (!empty($config->sql_protection)) {
            $regex[] = $this->sqlRegex;
        }
        if (!empty($config->xss_protection)) {
            $regex[] = $this->xssRegex;
        }
        if (!empty($config->rfi_protection)) {
            $regex[] = $this->rfiRegex;
        }
        if (!empty($config->own_filter)) {
            $regex[] = $config->own_filter;
        }

        if (empty($regex)) {
            return;
        }

        $regex = '#' . implode('|', $regex) . '#msi';

        $userParams = $request->getUserParams();
        $process = [
            &$_GET, &$_POST, &$_COOKIE, &$_REQUEST, &$_SERVER, &$userParams,
        ];

        $whiteList = [
            'frontend/account/login' => [
                'password',
            ],
            'frontend/account/savepassword' => [
                'password',
                'passwordConfirmation',
                'currentPassword',
            ],
            'frontend/register/ajax_validate_email' => [
                'password',
            ],
            'frontend/register/ajax_validate_password' => [
                'password',
            ],
            'frontend/register/saveregister' => [
                'password',
            ],
            'frontend/account/resetpassword' => [
                'password',
                'passwordConfirmation',
            ],
            'frontend/account/saveemail' => [
                'currentPassword',
            ],
        ];

        $route = strtolower(
            implode('/',
                [$request->getModuleName(), $request->getControllerName(), $request->getActionName()]
            )
        );

        $whiteList = array_key_exists($route, $whiteList) ? $whiteList[$route] : [];

        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                $stripTags = in_array($k, $whiteList) ? false : $stripTagsConf;
                if (is_array($v)) {
                    $process[$key][self::filterValue($k, $regex, $stripTags)] = $v;
                    $process[] = &$process[$key][self::filterValue($k, $regex, $stripTags)];
                } else {
                    $process[$key][self::filterValue($k, $regex, $stripTags)] = self::filterValue($v, $regex, $stripTags);
                }
            }
        }

        unset($process);
        $request->query->replace($_GET);
        $request->request->replace($_POST);
        $request->cookies->replace($_COOKIE);
        $request->server->replace($_SERVER);
        $request->setParams($userParams);
    }

    /**
     * Filter value by regex
     *
     * @param string $value
     * @param string $regex
     * @param bool   $stripTags
     *
     * @return string
     */
    public static function filterValue($value, $regex, $stripTags = true)
    {
        if (!empty($value)) {
            if ($stripTags) {
                $value = strip_tags($value);
            }
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
        return [
            'install' => false,
            'enable' => true,
            'update' => true,
        ];
    }
}
