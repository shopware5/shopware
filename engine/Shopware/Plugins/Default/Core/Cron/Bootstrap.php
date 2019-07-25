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

class Shopware_Plugins_Core_Cron_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Cron',
            'onGetControllerPath'
        );

        $this->createForm();

        return true;
    }

    public function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
        return $this->Path() . 'Cron.php';
    }

    /**
     * Secure cron actions according to system settings
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return bool If cron action is authorized
     */
    public function authorizeCronAction($request)
    {
        // If called using CLI, always execute the cron tasks
        if (PHP_SAPI === 'cli') {
            return true;
        }

        // At least one of the security policies is enabled.
        // If at least one of them validates, cron tasks will be executed
        $cronSecureAllowedKey = $this->Config()->get('cronSecureAllowedKey');
        $cronSecureAllowedIp = $this->Config()->get('cronSecureAllowedIp');
        $cronSecureByAccount = $this->Config()->get('cronSecureByAccount');

        // No security policy specified, accept all requests
        if (empty($cronSecureAllowedKey) && empty($cronSecureAllowedIp) && !$cronSecureByAccount) {
            return true;
        }

        // Validate key
        if (!empty($cronSecureAllowedKey)) {
            $urlKey = $request->getParam('key');

            if (strcmp($cronSecureAllowedKey, $urlKey) == 0) {
                return true;
            }
        }

        // Validate ip
        if (!empty($cronSecureAllowedIp)) {
            $requestIp = $request->getServer('REMOTE_ADDR');

            if (in_array($requestIp, explode(';', $cronSecureAllowedIp))) {
                return true;
            }
        }

        // Validate user auth
        if ($cronSecureByAccount) {
            if (Shopware()->Container()->get('auth')->hasIdentity() === true) {
                return true;
            }
        }

        return false;
    }

    private function createForm()
    {
        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(['name' => 'Other']);
        $form->setParent($parent);
        $form->setName('CronSecurity');
        $form->setLabel('Cron-Sicherheit');

        $form->setElement('text', 'cronSecureAllowedKey', [
            'label' => 'Gültiger Schlüssel',
            'description' => 'Hinterlegen Sie hier einen Key zum Ausführen der Cronjobs.',
            'required' => false,
            'value' => '',
        ]);
        $form->setElement('text', 'cronSecureAllowedIp', [
            'label' => 'Zulässige IP(s)',
            'description' => 'Nur angegebene IP-Adressen können die Cron Anfragen auslösen. Mehrere IP-Adressen müssen durch ein \';\' getrennt werden.',
            'required' => false,
            'value' => '',
        ]);
        $form->setElement('boolean', 'cronSecureByAccount', [
            'label' => 'Durch Benutzerkonto absichern',
            'description' => 'Es werden nur Anfragen von authentifizierten Backend Benutzern akzeptiert',
            'value' => false,
        ]);

        $this->addFormTranslations(
            [
                'en_GB' => [
                    'plugin_form' => [
                        'label' => 'Cron security',
                    ],
                    'cronSecureAllowedKey' => [
                        'label' => 'Allowed key',
                        'description' => 'If provided, cron requests will be executed if the inserted value is provided as \'key\' in the request',
                    ],
                    'cronSecureAllowedIp' => [
                        'label' => 'Allowed IP(s)',
                        'description' => 'If provided, cron requests will be executed if triggered from the given IP address(es). Use \';\' to separate multiple addresses.',
                    ],
                    'cronSecureByAccount' => [
                        'label' => 'Secure using account',
                        'description' => 'If set, requests received from authenticated backend users will be accepted',
                    ],
                ],
            ]
        );
    }
}
