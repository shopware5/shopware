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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateLicencesRequest;
use Shopware\Bundle\PluginInstallerBundle\Exception\AuthenticationException;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\Service\AccountManagerService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginLicenceService;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;

class Shopware_Controllers_Backend_UpdateWizard extends Shopware_Controllers_Backend_ExtJs
{
    public function indexAction()
    {
        /** @var Connection $connection */
        $connection = $this->get('dbal_connection');
        $sql = "INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
                VALUES (NULL, '0', 'updateWizardStarted', 'b:1;', '', '', 'checkbox', '0', '0', '1');";
        $connection->executeUpdate($sql);

        Shopware()->Container()->get('shopware.cache_manager')->clearConfigCache();
    }

    public function updateAction()
    {
        $pluginCheck = new \ShopwarePlugins\SwagUpdate\Components\PluginCheck($this->container);

        /** @var PluginLicenceService $service */
        $licenceService = $this->get('shopware_plugininstaller.plugin_licence_service');

        /** @var AccountManagerService $accountService */
        $accountService = $this->get('shopware_plugininstaller.account_manager_service');

        $request = new UpdateLicencesRequest(
            $this->getVersion(),
            $this->getLocale(),
            $accountService->getDomain(),
            $this->getAccessToken()
        );

        try {
            $result = $licenceService->updateLicences($request);
        } catch (Exception $e) {
            $this->handleException($e);

            return;
        }

        $plugins = $pluginCheck->checkInstalledPluginsAvailableForNewVersion($this->getVersion());

        $updatable = array_filter($plugins, function ($plugin) {
            return $plugin['updatable'];
        });

        $notUpdatable = array_filter($plugins, function ($plugin) {
            return $plugin['inStore'] == false;
        });

        $this->View()->assign([
            'success' => true,
            'result' => $result,
            'plugins' => array_values($plugins),
            'updatable' => array_values($updatable),
            'notUpdatable' => array_values($notUpdatable),
        ]);
    }

    /**
     * @return string
     */
    private function getLocale()
    {
        return $this->container->get('auth')->getIdentity()->locale->getLocale();
    }

    /**
     * @return string
     */
    private function getVersion()
    {
        return $this->container->getParameter('shopware.release.version');
    }

    /**
     * @return AccessTokenStruct|null
     */
    private function getAccessToken()
    {
        if (!$this->get('backendsession')->offsetExists('store_token')) {
            return null;
        }

        $allowedClassList = [
            AccessTokenStruct::class,
        ];

        return unserialize(
            $this->get('backendsession')->offsetGet('store_token'),
            ['allowed_classes' => $allowedClassList]
        );
    }

    private function handleException(Exception $e)
    {
        if (!($e instanceof StoreException)) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $message = $this->getExceptionMessage($e);
        if (empty($message)) {
            $message = $e->getMessage();
        }

        $this->View()->assign([
            'success' => false,
            'message' => $message,
            'authentication' => ($e instanceof AuthenticationException),
        ]);
    }

    /**
     * @return mixed|string
     */
    private function getExceptionMessage(StoreException $exception)
    {
        /** @var \Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->get('snippets')
            ->getNamespace('backend/plugin_manager/exceptions');

        if ($namespace->offsetExists($exception->getMessage())) {
            $snippet = $namespace->get($exception->getMessage());
        } else {
            $snippet = $exception->getMessage();
        }

        $snippet .= '<br><br>Error code: ' . $exception->getSbpCode();

        return $snippet;
    }
}
