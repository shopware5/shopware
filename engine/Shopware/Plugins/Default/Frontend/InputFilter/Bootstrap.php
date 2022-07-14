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

use voku\helper\AntiXSS;

/**
 * Shopware InputFilter Plugin
 */
class Shopware_Plugins_Frontend_InputFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public const ALLOWED_ATTRIBUTES_KEY = 'allowedAttributes';
    public const ALLOWED_HTML_TAGS_KEY = 'allowedHtmlTags';

    /**
     * @var string
     */
    public $sqlRegex = 's_core_|s_order_|s_user|benchmark.*\(|(?:insert|replace).+into|update.+set|(?:delete|select).+from|(?:alter|rename|create|drop|truncate).+(?:database|table|procedure)|union.+select|prepare.+from.+execute|select.+into\s+(outfile|dumpfile)';

    /**
     * @var string
     */
    public $xssRegex = 'javascript:|src\s*=|\bon[a-z]+\s*=|style\s*=|\bdata-\w+(?!\.)\b\s?=?';

    /**
     * @var array<string, array<string>>
     */
    public array $stripTagsWhiteList = [
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

    /**
     * @var array<string, array<string, array<string, array<string>>>>
     *
     * usage:
     *
     * 'frontend/account/login' => [
     *      'password' => [
     *           self::ALLOWED_ATTRIBUTES_KEY => [],
     *           self::ALLOWED_HTML_TAGS_KEY => []
     *       ]
     *   ]
     */
    public array $allowanceList = [];

    /**
     * @var string
     */
    public $rfiRegex = '\.\./|\\0';

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
     * @return void
     */
    public function onRouteShutdown(Enlight_Controller_EventArgs $args)
    {
        /** @var Enlight_Controller_Request_RequestHttp $request */
        $request = $args->getRequest();
        $config = $this->Config();

        if ($request->getModuleName() === 'backend' || $request->getModuleName() === 'api') {
            return;
        }

        $stripTagsConf = $config->get('strip_tags');

        foreach (['sCategory', 'sContent', 'sCustom'] as $parameter) {
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

        $regex = '#' . implode('|', $regex) . '#msi';

        $userParams = $request->getUserParams();
        $process = [
            &$_GET, &$_POST, &$_COOKIE, &$_REQUEST, &$_SERVER, &$userParams,
        ];

        $route = strtolower(
            implode(
                '/',
                [$request->getModuleName(), $request->getControllerName(), $request->getActionName()]
            )
        );

        $stripTagsWhiteList = \array_key_exists($route, $this->stripTagsWhiteList) ? $this->stripTagsWhiteList[$route] : [];
        $allowanceList = \array_key_exists($route, $this->allowanceList) ? $this->allowanceList[$route] : [];
        foreach ($process as $key => $val) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                $stripTags = \in_array($k, $stripTagsWhiteList) ? false : $stripTagsConf;
                $allowedHtmlTags = \array_key_exists($k, $allowanceList) ? $allowanceList[$k][self::ALLOWED_HTML_TAGS_KEY] : [];
                $allowedAttributes = \array_key_exists($k, $allowanceList) ? $allowanceList[$k][self::ALLOWED_ATTRIBUTES_KEY] : [];

                if (\is_string($k)) {
                    $filteredKey = self::filterValue($k, $regex, $stripTags, $allowedHtmlTags, $allowedAttributes);
                } else {
                    $filteredKey = $k;
                }

                if ($filteredKey === '' || $filteredKey === null) {
                    continue;
                }

                if (\is_array($v)) {
                    $process[$key][$filteredKey] = self::filterArrayValue($v, $regex, $stripTags, $allowedHtmlTags, $allowedAttributes);
                    continue;
                }

                if (\is_string($v)) {
                    $process[$key][$filteredKey] = self::filterValue($v, $regex, $stripTags, $allowedHtmlTags, $allowedAttributes);
                    continue;
                }

                $process[$key][$filteredKey] = $v;
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
     * @param string        $value
     * @param string        $regex
     * @param bool          $stripTags
     * @param array<string> $allowedHtmlTags
     * @param array<string> $allowedAttributes
     *
     * @return string|null
     */
    public static function filterValue($value, $regex, $stripTags = true, array $allowedHtmlTags = [], array $allowedAttributes = [])
    {
        if (empty($value)) {
            return $value;
        }

        if ($stripTags) {
            $value = strip_tags($value);
        }

        if (preg_match($regex, $value)) {
            return null;
        }

        $antiXss = new AntiXSS();
        $antiXss->removeEvilAttributes($allowedAttributes);
        $antiXss->removeEvilHtmlTags($allowedHtmlTags);
        $value = $antiXss->xss_clean($value);

        return \str_replace(['&lt;', '&gt;'], ['<', '>'], $value);
    }

    /**
     * @param array<string|int, mixed> $value
     * @param array<string>            $allowedHtmlTags
     * @param array<string>            $allowedAttributes
     *
     * @return array<string|int, mixed>|null
     */
    public static function filterArrayValue(array $value, string $regex, bool $stripTags = true, array $allowedHtmlTags = [], array $allowedAttributes = []): ?array
    {
        $newReturn = [];
        foreach ($value as $valueKey => $valueValue) {
            if (\is_int($valueKey)) {
                $filteredKey = $valueKey;
            } else {
                $filteredKey = self::filterValue($valueKey, $regex, $stripTags, $allowedHtmlTags, $allowedAttributes);
            }

            if ($filteredKey === '' || $filteredKey === null) {
                continue;
            }

            $filteredValue = $valueValue;

            if (\is_array($valueValue)) {
                $filteredValue = self::filterArrayValue($valueValue, $regex, $stripTags);
            }

            if (\is_string($valueValue)) {
                $filteredValue = self::filterValue($valueValue, $regex, $stripTags, $allowedHtmlTags, $allowedAttributes);
            }

            $newReturn[$filteredKey] = $filteredValue;
        }

        return $newReturn;
    }

    public function getCapabilities()
    {
        return [
            'install' => false,
            'enable' => true,
            'update' => true,
        ];
    }
}
