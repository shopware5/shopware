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

use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Shop\Locale;
use Shopware\Models\User\Role;

class Shopware_Controllers_Backend_Login extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Loads auth and script renderer resource
     */
    public function init()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'login',
            'logout',
            'getLocales',
            'getLoginStatus',
        ];
    }

    /**
     * Do authentication and return result in json-format
     * Check if account is blocked
     */
    public function loginAction()
    {
        $username = $this->Request()->get('username');
        $password = $this->Request()->get('password');

        if (empty($username) || empty($password)) {
            $this->View()->assign(['success' => false]);

            return;
        }

        /** @var Shopware_Components_Auth $auth */
        $auth = Shopware()->Container()->get('auth');
        $result = $auth->login($username, $password);
        $user = $auth->getIdentity();
        if (!empty($user->roleID)) {
            $user->role = Shopware()->Models()->find(
                Role::class,
                $user->roleID
            );
        }
        if ($user && ($locale = $this->Request()->get('locale')) !== null) {
            $user->locale = Shopware()->Models()->getRepository(
                Locale::class
            )->find($locale);
        }
        if (!isset($user->locale) && !empty($user->localeID)) {
            $user->locale = Shopware()->Models()->find(
                Locale::class,
                $user->localeID
            );
        }
        if ($user && !isset($user->locale)) {
            $user->locale = Shopware()->Models()->getRepository(
                Locale::class
            )->find($this->getPlugin()->getDefaultLocale());
        }

        $messages = $result->getMessages();
        /* @var Zend_Date $lockedUntil */
        if (isset($messages['lockedUntil'])) {
            $lockedUntil = isset($messages['lockedUntil']) ? $messages['lockedUntil'] : null;
            $lockedUntil = $lockedUntil->toString(Zend_Date::ISO_8601);
        }

        $this->View()->assign([
            'success' => $result->isValid(),
            'user' => $result->getIdentity(),
            'locale' => isset($user->locale) ? $user->locale->toString() : null,
            'lockedUntil' => isset($lockedUntil) ? $lockedUntil : null,
        ]);
    }

    /**
     * On logout destroy session and redirect to auth controller
     */
    public function logoutAction()
    {
        Shopware()->Container()->get('auth')->clearIdentity();
        $this->redirect('backend');
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * @return \Shopware_Plugins_Backend_Auth_Bootstrap
     */
    public function getPlugin()
    {
        return Shopware()->Plugins()->Backend()->Auth();
    }

    /**
     * Gets the available backend locales and returns them in an ExtJS
     * friendly format
     *
     * Note that this function returns sample data to build up the module.
     */
    public function getLocalesAction()
    {
        $current = Shopware()->Container()->get('locale');
        $locales = $this->getPlugin()->getLocales();
        $locales = Shopware()->Db()->quote($locales);
        $sql = 'SELECT id, locale FROM s_core_locales WHERE id IN (' . $locales . ')';
        $locales = Shopware()->Db()->fetchPairs($sql);

        $data = [];
        foreach ($locales as $id => $locale) {
            list($l, $t) = explode('_', $locale);
            $l = $current::getTranslation($l, 'language', $current);
            $t = $current::getTranslation($t, 'territory', $current);
            $data[] = [
                'id' => $id,
                'name' => "$l ($t)",
            ];
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Gets the current login status of the user.
     */
    public function getLoginStatusAction()
    {
        $refresh = null;
        $auth = Shopware()->Container()->get('auth');
        if ($auth->hasIdentity()) {
            $refresh = $auth->refresh();
        }
        if ($auth->hasIdentity()) {
            $messages = $refresh->getMessages();
            $this->View()->assign([
                'success' => true,
                'message' => $messages[0],
            ]);
        } else {
            $auth->clearIdentity();
            $this->View()->assign([
                'success' => false,
            ]);
        }
    }

    public function validatePasswordAction()
    {
        /** @var Shopware_Components_Auth $auth */
        $auth = Shopware()->Container()->get('auth');
        $username = $auth->getIdentity()->username;
        $password = $this->Request()->get('password');

        if (empty($username) || empty($password)) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $result = $auth->isPasswordValid($username, $password);

        if ($this->container->get('backendsession')->offsetExists('passwordVerified')) {
            $this->container->get('backendsession')->offsetUnset('passwordVerified');
        }

        /*
         * Set a flag in the backend session indicating that the password has been successfully verified
         */
        if ($result) {
            $this->container->get('backendsession')->offsetSet('passwordVerified', true);
        }

        $this->View()->assign('success', $result);
    }
}
